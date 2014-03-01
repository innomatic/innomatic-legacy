<?php
/**
 * Innomatic
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2014 Innoteam Srl
 * @license    http://www.innomatic.org/license/   BSD License
 * @link       http://www.innomatic.org
 */
namespace Innomatic\Application;

/**
 * @since 5.0.0 introduced
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 */
class ApplicationSettings
{
    /*! @var rootda DataAccess class - Innomatic database handler. */
    private $_rootda;
    /*! @var appname string - Application name. */
    private $_appname;

    /*!
     @param rootda DataAccess class - Innomatic database handler.
     @param appname string - Application name.
     */
    public function __construct($rootda, $appname)
    {
        $this->_rootda = $rootda;
        if ($appname) {
            $this->_appname = $appname;
        } else {
            
            $log = \Innomatic\Core\InnomaticContainer::instance(
                '\Innomatic\Core\InnomaticContainer'
            )->getLogger();
            $log->logEvent(
                'innomatic.applications.applicationsettings'
                .'.applicationsettings',
                'Empty application name', \Innomatic\Logging\Logger::WARNING
            );
        }
    }

    /*!
     @abstract Sets a key value pair.
     @discussion If a key with the same name already exists, it is updated with
     * the given value.
     @param key string - Key name.
     @param val string - Key value.
     @result True if it's all right.
     */
    public function setKey($key, $val)
    {
        if ($this->_rootda and !empty($key)) {
            if ($this->checkkey($key) != false) {
                // A key with the same name already exists, it will be updated
                //
                return $this->_rootda->execute(
                    'UPDATE applications_settings SET value='
                    . $this->_rootda->formatText($val)
                    . ' WHERE appname='
                    . $this->_rootda->formatText($this->_appname)
                    . ' AND keyname=' . $this->_rootda->formatText($key)
                );
            } else {
                // This is a new key
                //
                return $this->_rootda->execute(
                    'INSERT INTO applications_settings VALUES ( '
                    . $this->_rootda->formatText($this->_appname) . ','
                    . $this->_rootda->formatText($key) . ','
                    . $this->_rootda->formatText($val).')'
                );
            }
        }
    }

    /*!
     @discussion The returned string may be an IP address, a port or any other
     * value.
     @param key string - Key name.
     @result String representing the value correspondent to the key.
     */
    public function getKey($key)
    {
        if ($this->_rootda and !empty($key)) {
            $mcquery = $this->_rootda->execute(
                'SELECT value FROM applications_settings WHERE appname='
                . $this->_rootda->formatText($this->_appname)
                . ' AND keyname='.$this->_rootda->formatText($key)
            );

            if ($mcquery) {
                if ($mcquery->getNumberRows() != 0) {
                    return $mcquery->getFields('value');
                }
            }
        }
    }

    /*!
     @abstract Removes a key.
     @param key string - Key name.
     @result True if the key has been deleted.
     */
    public function delKey($key)
    {
        if ($this->_rootda and !empty($key)) {
            return $this->_rootda->execute(
                'DELETE FROM applications_settings WHERE appname='
                . $this->_rootda->formatText($this->_appname)
                . ' AND keyname='.$this->_rootda->formatText($key)
            );
        }
    }

    /*!
     @abstract Checks if a certain key has been set and returns id.
     @param key string - Key name.
     @result ID of the key.
     */
    public function checkKey($key)
    {
        if ($this->_rootda and !empty($key)) {
            $mcquery = $this->_rootda->execute(
                'SELECT * FROM applications_settings WHERE appname='
                . $this->_rootda->formatText($this->_appname)
                . ' AND keyname='.$this->_rootda->formatText($key)
            );
            if ($mcquery->getNumberRows() > 0)
                return $mcquery;
        }
        return false;
    }
}
