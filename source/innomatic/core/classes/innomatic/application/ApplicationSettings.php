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
 */
namespace Innomatic\Application;

/**
 *
 * @since 5.0.0 introduced
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 */
class ApplicationSettings
{
    /* ! @var rootda DataAccess class - Innomatic database handler. */
    protected $rootda;
    /* ! @var appname string - Application name. */
    protected $appname;
    
    /**
     * @param appname string - Application name.
     */
    public function __construct($appname)
    {
        $this->rootda = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess();
        if ($appname) {
            $this->appname = $appname;
        } else {
            $log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();
            $log->logEvent('innomatic.applications.applicationsettings' . '.applicationsettings', 'Empty application name', \Innomatic\Logging\Logger::WARNING);
        }
    }
    
    /**
     * Sets a key value pair.
     * 
     * If a key with the same name already exists, it is updated with the given value.
     * 
     * @param key string - Key name
     * @param val string - Key value
     * @return boolean True if it's all right.
     */
    public function setKey($key, $val)
    {
        if ($this->rootda and ! empty($key)) {
            if ($this->checkkey($key) != false) {
                // A key with the same name already exists, it will be updated
                return $this->rootda->execute('UPDATE applications_settings SET value=' . $this->rootda->formatText($val) . ' WHERE appname=' . $this->rootda->formatText($this->appname) . ' AND keyname=' . $this->rootda->formatText($key));
            } else {
                // This is a new key
                return $this->rootda->execute('INSERT INTO applications_settings VALUES ( ' . $this->rootda->formatText($this->appname) . ',' . $this->rootda->formatText($key) . ',' . $this->rootda->formatText($val) . ')');
            }
        }
    }
    
    /**
     * The returned string may be an IP address, a port or any other value.
     * 
     * @param key string - Key name.
     * 
     * @return string String representing the value correspondent to the key.
     */
    public function getKey($key)
    {
        if ($this->rootda and ! empty($key)) {
            $mcquery = $this->rootda->execute('SELECT value FROM applications_settings WHERE appname=' . $this->rootda->formatText($this->appname) . ' AND keyname=' . $this->rootda->formatText($key));
            
            if ($mcquery) {
                if ($mcquery->getNumberRows() != 0) {
                    return $mcquery->getFields('value');
                }
            }
        }
    }
    
    /**
     * Removes a key.
     * 
     * @param key string - Key name.
     * @return boolean True if the key has been deleted.
     */
    public function delKey($key)
    {
        if ($this->rootda and ! empty($key)) {
            return $this->rootda->execute('DELETE FROM applications_settings WHERE appname=' . $this->rootda->formatText($this->appname) . ' AND keyname=' . $this->rootda->formatText($key));
        }
    }
    
    /**
     * Checks if a certain key has been set and returns id.
     * 
     * @param key string - Key name.
     * 
     * @return integer ID of the key.
     */
    public function checkKey($key)
    {
        if ($this->rootda and ! empty($key)) {
            $mcquery = $this->rootda->execute('SELECT * FROM applications_settings WHERE appname=' . $this->rootda->formatText($this->appname) . ' AND keyname=' . $this->rootda->formatText($key));
            if ($mcquery->getNumberRows() > 0) {
                return $mcquery;
            }
        }
        return false;
    }
}
