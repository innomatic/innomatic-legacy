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
namespace Innomatic\Core;

/**
 * @since 5.0.0 introduced
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 */
class InnomaticSettings
{
    private $configFile;
    private $configValues = array();
    private $opened = false;

    /*!
     @param configFile string - Innomatic configuration file full path.
     */
    public function __construct($configFile)
    {
        $this->configFile = $configFile;
        $this->refresh();
    }

    /**
     * Refreshes InnomaticSettings values, rereading the configuration file.
     *
     */
    public function refresh()
    {
        // Checks to see if the file is there
        if (file_exists($this->configFile)) {
            $fp = @fopen($this->configFile, 'r');
            if ($fp) {
                $this->opened = true;
                $this->configValues = array();

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
                        $this->configValues[trim($key)] = trim($value);
                    }
                }
                @fclose($fp);
            } else {
                throw new \Exception('Could not open '.$this->configFile);
            }
        } else {
            throw new \Exception(
                'Configuration file '
                . $this->configFile." doesn't exists"
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
            $this->configValues[$keyName]
        ) ? trim($this->configValues[$keyName]): '';
    }

    public function setVolatileKey($keyName, $value)
    {
        $this->configValues[$keyName] = $value;
    }
}
