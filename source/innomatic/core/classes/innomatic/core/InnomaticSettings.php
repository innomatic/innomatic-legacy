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

class InnomaticSettings
{
    private $_configFile;
    private $_configValues = array();
    private $_opened = false;

    /*!
     @param configFile string - Innomatic configuration file full path.
     */
    public function __construct($configFile)
    {
        $this->_configFile = $configFile;
        $this->refresh();
    }
    
    /**
     * Refreshes InnomaticSettings values, rereading the configuration file.
     *
     */
    public function refresh()
    {
        // Checks to see if the file is there
        if (file_exists($this->_configFile)) {
            $fp = @fopen($this->_configFile, 'r');
            if ($fp) {
                $this->_opened = true;
                $this->_configValues = array();

                while ($fl = @fgets($fp)) {
                    $trimmedline = trim($fl);

                    if (
                        (substr($trimmedline, 0, 1) != '#')
                        and (substr($trimmedline, 0, 1) != ';')
                        and (strpos($trimmedline, '='))
                    ) {
                        $key = substr(
                            $trimmedline,
                            0,
                            (strpos($trimmedline, '='))
                        );
                        $value = substr(
                            $trimmedline,
                            (strpos($trimmedline, '=') + 1)
                        );
                        $this->_configValues[trim($key)] = trim($value);
                    }
                }
                @fclose($fp);
            } else {
                throw new Exception('Could not open '.$this->_configFile);
            }
        } else {
            throw new Exception(
                'Configuration file '
                . $this->_configFile." doesn't exists"
            );
        }
    }

    /*!
     @function Value
     @abstract Gets a configuration value.
     @discussion Returns the value of a given key from Innomatic configuration.
     @param keyName string - Configuration key.
     @result The value if the key, if any.
     */
    public function value($keyName)
    {
        return $this->getKey($keyName);
    }
    
    public function getKey($keyName)
    {
        return isset(
            $this->_configValues[$keyName]
        ) ? trim($this->_configValues[$keyName]): '';
    }
    
    public function setVolatileKey($keyName, $value)
    {
        $this->_configValues[$keyName] = $value;
    }
}
