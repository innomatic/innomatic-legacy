<?php
/**
 * Innomatic
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2012 Innoteam Srl
 * @license    http://www.innomatic.org/license/   BSD License
 * @link       http://www.innomatic.org
 * @since      Class available since Release 5.0
*/
namespace Innomatic\Process;

/**
 * Questa classe fornisce un meccanismo di controllo delle risorse
 * basato sul concetto di semaforo.
 * @author Alex Pagnoni <alex.pagnoni@innoteam.it>
 * @since 3.5
 */
class Semaphore
{
    /**
     * Tipo di risorsa da controllare.
     * @var string
     * @access private
     */
    private $mResourceType;
    /**
     * Identificativo della risorsa da controllare.
     * @var string
     * @access private
     */
    private $mResource;
    const STATUS_GREEN = 'green';
    const STATUS_RED = 'red';

    /**
     * Costruisce la classe.
     * @param string $resourceType tipo di risorsa da controllare.
     * @param string $resource identificativo della risorsa da controllare.
     */
    public function __construct($resourceType, $resource)
    {
        $this->mResourceType = $resourceType;
        $this->mResource = $resource;
    }

    /**
     * Imposta il tipo di risorsa da controllare.
     * @param string $resourceType
     * @access public
     * @return void
     */
    public function setResourceType($resourceType)
    {
        $this->mResourceType = $resourceType;
    }

    /**
     * Restituisce il tipo di risorsa controllata.
     * @access public
     * @return string
     */
    public function getResourceType()
    {
        return $this->mResourceType;
    }

    /**
     * Imposta l'identificativo della risorsa da controllare.
     * @param string $resource
     * @access public
     * @return void
     */
    public function setResource($resource)
    {
        $this->mResource = $resource;
    }

    /**
     * Restituisce l'identificativo della risorsa controllata.
     * @access public
     * @return string
     */
    public function getResource()
    {
        return $this->mResource;
    }

    /**
     * Restituisce il path completo del file di lock della risorsa.
     * @access private
     * @return string
     */
    public function getFileName()
    {
        if ($this->mResourceType and $this->mResource) {
            return InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/semaphores/'.md5($this->mResourceType.'_'.$this->mResource).'.semaphore';
        }
        return '';
    }

    /**
     * Controlla in che stato � la risorsa.
     * @return string
     * @access public
     */
    public function checkStatus()
    {
        if ($this->mResource) {
            clearstatcache();
            if (file_exists($this->getFileName())) {
                return Semaphore::STATUS_RED;
            }
        }
        return Semaphore::STATUS_GREEN;
    }

    /**
     * Imposta lo stato della risorsa.
     * @param string $status
     * @access private
     * @return bool
     */
    public function setStatus($status)
    {
        if ($this->mResource) {
            switch ($status) {
                case Semaphore::STATUS_GREEN :
                    clearstatcache();
                    if (file_exists($this->getFileName())) {
                        unlink($this->getFileName());
                    }
                    return true;
                    break;

                case Semaphore::STATUS_RED :
                    clearstatcache();
                    if (!file_exists($this->getFileName())) {
                        if ($fh = fopen($this->getFileName(), 'w')) {
                            require_once('innomatic/core/InnomaticContainer.php');
                            $innomatic = InnomaticContainer::instance('innomaticcontainer');

                            fputs($fh, serialize(array('pid' => $innomatic->getPid(), 'time' => time(), 'resource' => $this->mResource)));
                            fclose($fh);
                        } else {
                            if (!file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/semaphores/')) {
                                mkdir(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/semaphores/');
                            }
                            return false;
                        }
                    }
                    return true;
                    break;
            }
        }
        return false;
    }

    /**
     * Imposta lo stato della risorsa come disponibile.
     * @access public
     * @return void
     */
    public function setGreen()
    {
        $this->setStatus(Semaphore::STATUS_GREEN);
    }

    /**
     * Imposta lo stato della risorsa come occupata.
     * @access public
     * @return void
     */
    public function setRed()
    {
        $this->setStatus(Semaphore::STATUS_RED);
    }

    /**
     * Restituisce il contenuto del semaforo.
     * Il contenuto � un array con le chiavi pid, time e resource.
     * @access public
     * @return array
     */
    public function getSemaphoreData()
    {
        clearstatcache();
        if ($this->mResource and file_exists($this->getFileName())) {
            if (file_exists($this->getFileName())) {
                return unserialize(file_get_contents($this->getFileName()));
            }
        }
        return array();
    }

    /**
     * Attende fino a che la risorsa non si libera.
     * @param integer $checkDelay intervallo in secondi di attesa tra ogni tentativo.
     * @param integer $maxDelay tempo in secondi opzionale dopo il quale il metodo restituisce il controllo.
     * @return bool
     * @access public
     */
    public function waitGreen($checkDelay = 1, $maxDelay = 0)
    {
        $result = false;
        if ($this->mResource) {
            if ($maxDelay) {
                $start = time();
            }
            $result = true;

            while (!($this->CheckStatus() == Semaphore::STATUS_GREEN)) {
                /*
                If delay exceeds the optional maximum, the function returns false and
                the user code should not execute the code that should be executed when
                the semaphore is green.
                */
                if ($maxDelay and time() > $start + $maxDelay) {
                    return false;
                }

                sleep($checkDelay);
            }
        }
        return $result;
    }
}
