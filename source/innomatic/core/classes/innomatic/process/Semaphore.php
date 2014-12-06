<?php
/**
 * Innomatic
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2014 Innomatic Company
 * @license    http://www.innomatic.io/license/ New BSD License
 * @link       http://www.innomatic.io
 * @since      Class available since Release 5.0
*/
namespace Innomatic\Process;

/**
 * Questa classe fornisce un meccanismo di controllo delle risorse
 * basato sul concetto di semaforo.
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 * @since 3.5
 */
class Semaphore
{
    /**
     * Tipo di risorsa da controllare.
     * @var string
     */
    protected $resourceType;
    /**
     * Identificativo della risorsa da controllare.
     * @var string
     */
    protected $resource;
    const STATUS_GREEN = 'green';
    const STATUS_RED = 'red';

    /**
     * Costruisce la classe.
     * @param string $resourceType tipo di risorsa da controllare.
     * @param string $resource identificativo della risorsa da controllare.
     */
    public function __construct($resourceType, $resource)
    {
        $this->resourceType = $resourceType;
        $this->resource = $resource;
    }

    /**
     * Imposta il tipo di risorsa da controllare.
     * @param string $resourceType
     * @return void
     */
    public function setResourceType($resourceType)
    {
        $this->resourceType = $resourceType;
    }

    /**
     * Restituisce il tipo di risorsa controllata.
     * @return string
     */
    public function getResourceType()
    {
        return $this->resourceType;
    }

    /**
     * Imposta l'identificativo della risorsa da controllare.
     * @param string $resource
     * @return void
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
    }

    /**
     * Restituisce l'identificativo della risorsa controllata.
     * @return string
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Restituisce il path completo del file di lock della risorsa.
     * @return string
     */
    public function getFileName()
    {
        if ($this->resourceType and $this->resource) {
            return \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome().'core/temp/semaphores/'.md5($this->resourceType.'_'.$this->resource).'.semaphore';
        }
        return '';
    }

    /**
     * Controlla in che stato è la risorsa.
     * @return string
     */
    public function checkStatus()
    {
        if ($this->resource) {
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
     * @return bool
     */
    public function setStatus($status)
    {
        if ($this->resource) {
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
                            $innomatic = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');

                            fputs($fh, serialize(array('pid' => $innomatic->getPid(), 'time' => time(), 'resource' => $this->resource)));
                            fclose($fh);
                        } else {
                            if (!file_exists($innomatic->getHome().'core/temp/semaphores/')) {
                                mkdir($innomatic->getHome().'core/temp/semaphores/');
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
     * @return void
     */
    public function setGreen()
    {
        $this->setStatus(Semaphore::STATUS_GREEN);
    }

    /**
     * Imposta lo stato della risorsa come occupata.
     * @return void
     */
    public function setRed()
    {
        $this->setStatus(Semaphore::STATUS_RED);
    }

    /**
     * Restituisce il contenuto del semaforo.
     * Il contenuto � un array con le chiavi pid, time e resource.
     * @return array
     */
    public function getSemaphoreData()
    {
        clearstatcache();
        if ($this->resource and file_exists($this->getFileName())) {
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
     */
    public function waitGreen($checkDelay = 1, $maxDelay = 0)
    {
        $result = false;
        if ($this->resource) {
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
