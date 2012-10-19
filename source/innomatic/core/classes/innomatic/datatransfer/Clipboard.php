<?php
/**
 * Innomatic
 *
 * LICENSE 
 * 
 * This source file is subject to the new BSD license that is bundled 
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2012 Innoteam S.r.l.
 * @license    http://www.innomatic.org/license/   BSD License
 * @link       http://www.innomatic.org
 * @since      Class available since Release 5.0
*/

/**
 * Classe che implementa un meccanismo per trasferire dati
 * tramite operazioni di copia/taglia/incolla.
 * @author Alex Pagnoni <alex.pagnoni@innoteam.it>
 * @since 1.0
 */
class Clipboard
{
    private $_type;
    private $_customType;
    private $_unit;
    private $_application;
    private $_domain;
    private $_user;
    private $_fileName;
    const TYPE_TEXT = 'text';
    const TYPE_RAW = 'raw';
    const TYPE_FILE = 'file';
    const TYPE_ARRAY = 'array';
    const TYPE_OBJECT = 'object';
    const TYPE_CUSTOM = 'custom';

    /**
     * Costruisce la classe della clipboard.
     * @param string $type tipo di dato da trattare.
     * @param string $customType tipo utente di dato da trattare se $type � impostato a Clipboard::TYPE_CUSTOM  
     * @param integer $unit unit� identificativa della clipboard da utilizzare a partire da 0
     * @param string $application nome del modulo. 
     * @param string $domain nome del sito.
     * @param string $user nome dell'utente. 
     */
    public function __construct(
        $type,
        $customType = '',
        $unit = 0,
        $application = '',
        $domain = '',
        $user = ''
    )
    {
        $this->_type = $type;
        if ($this->_type == Clipboard::TYPE_CUSTOM) {
            $this->_customType = $customType;
        }
        $this->_unit = $unit;
        $this->_application = $application;
        $this->_domain = $domain;
        $this->_user = $user;
        $this->_fileName = InnomaticContainer::instance(
            'innomaticcontainer'
        )->getHome() . 'WEB-INF/temp/clipboard/'
        . $this->_type . '_' . $this->_customType . '_' . $this->_unit
        . '_' . $this->_application . '_' . $this->_domain
        . '_' . $this->_user . '.clipboard';
    }

    /**
     * Controlla se la clipboard contiene dati validi.
     * @return bool
     * @access public
     */
    public function isValid()
    {
        clearstatcache();
        return file_exists($this->_fileName);
    }

    /**
     * Immagazzina un dato nella clipboard.
     * @param mixed $item dato da salvare.
     * @return bool
     * @access public
     * @see Clipboard::Retrieve()
     */
    public function store(&$item)
    {
        $result = false;
        require_once('innomatic/process/Semaphore.php');
        $sem = new Semaphore('clipboard', $this->_fileName);
        $sem->WaitGreen();
        $sem->setRed();

        $fh = fopen($this->_fileName, 'wb');
        if ($fh) {
            switch ($this->_type) {
                case Clipboard::TYPE_TEXT :
                case Clipboard::TYPE_RAW :
                    fwrite($fh, $item);
                    $result = true;
                    break;

                case Clipboard::TYPE_FILE :
                    fwrite(
                        $fh,
                        serialize(
                            array(
                                'filename' => $item,
                                'content' => file_get_contents($item)
                            )
                        )
                    );
                    $result = true;
                    break;

                case Clipboard::TYPE_OBJECT :
                case Clipboard::TYPE_ARRAY :
                case Clipboard::TYPE_CUSTOM :
                    fwrite($fh, serialize($item));
                    $result = true;
                    break;
            }
            fclose($fh);
            $sem->setGreen();
        }
        return $result;
    }

    /**
     * Estrae il contenuto della clipboard.
     * @return mixed
     * @access public
     * @see Clipboard::Store()
     */
    public function retrieve()
    {
        $result = '';
        require_once('innomatic/process/Semaphore.php');
        $sem = new Semaphore('clipboard', $this->_fileName);
        $sem->WaitGreen();

        if ($this->IsValid()) {
            $sem->setRed();
            if (file_exists($this->_fileName)) {
                switch ($this->_type) {
                    case Clipboard::TYPE_TEXT :
                        // this break was intentionally left blank
                    case Clipboard::TYPE_RAW :
                        $result = file_get_contents($this->_fileName);
                        break;

                    case Clipboard::TYPE_FILE :
                        // this break was intentionally left blank
                    case Clipboard::TYPE_OBJECT :
                        // this break was intentionally left blank
                    case Clipboard::TYPE_ARRAY :
                        // this break was intentionally left blank
                    case Clipboard::TYPE_CUSTOM :
                        $result = unserialize(
                            file_get_contents($this->_fileName)
                        );
                        break;
                }
                $sem->setGreen();
            }
        }
        return $result;
    }

    /**
     * Svuota il contenuto della clipboard.
     * @return bool
     * @access public
     */
    public function erase()
    {
        $result = false;
        if ($this->IsValid()) {
            require_once('innomatic/process/Semaphore.php');
            $sem = new Semaphore('clipboard', $this->_fileName);
            $sem->WaitGreen();
            $sem->setRed();
            $result = unlink($this->_fileName);
            $sem->setGreen();
        } else
            $result = true;
        return $result;
    }
    
    public function getType()
    {
        return $this->_type;
    }
    
    public function getCustomType()
    {
        return $this->_customType;
    }
    
    public function getUnit()
    {
        return $this->_unit;
    }
    
    public function getApplication()
    {
        return $this->_application;
    }

    public function getDomain()
    {
        return $this->_domain;
    }
    
    public function getFileName()
    {
        return $this->_fileName;
    }
}
