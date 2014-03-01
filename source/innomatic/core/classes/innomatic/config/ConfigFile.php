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
namespace Innomatic\Config;

/**
 * @since 5.0.0 introduced
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 */
class ConfigFile
{
    /*! @var mConfigFile string - Full path of the configuration file. */
    protected $configFile;
    /*! @var mConfigValues array - Array of all the key value pairs. */
    public $mConfigValues;
    /*! @var mOpened boolean - True if the given file exists and has been opened. */
    public $mOpened = false;

    public function __construct($fileName, $create = false)
    {
        // Checks to see if the file is there
        if (!$create and !file_exists($fileName)) {
        } else {
            if (!file_exists($fileName))
            $mode = 'w';
            else
            $mode = 'r';

            $fp = @fopen($fileName, $mode);
            if ($fp) {
                $this->configFile = $fileName;
                $this->mOpened = true;

                while ($fl = @fgets($fp)) {
                    $trimmedLine = trim($fl);

                    if (
                        (substr($trimmedLine, 0, 1) != '#')
                        and (substr($trimmedLine, 0, 1) != ';')
                        and (strpos($trimmedLine, '='))
                    ) {
                        $key = substr($trimmedLine, 0, (strpos($trimmedLine, '=')));
                        $value = substr($trimmedLine, (strpos($trimmedLine, '=') + 1));
                        $this->mConfigValues[trim($key)] = trim($value);
                    }
                }

                @fclose($fp);
            }
        }
    }

    /*!
     @function Value

     @abstract Returns the value of a key.

     @param keyName string - Key name.

     @result The value of the key, if it exists.
     */
    public function value($keyName)
    {
        if (isset($this->mConfigValues[$keyName]))
        return trim($this->mConfigValues[$keyName]);
        else
        return '';
    }

    /*!
     @function setValue

     @abstract Sets a value for a key.

     @param keyName string - Key name.
     @param value string - Key value.

     @result True if the key has been written.
     */
    public function setValue($keyName, $value)
    {
        $result = false;

        $fm = new FileMan();
        $fm->setFile($this->configFile);

        if ($fm->basefile) {
            $keys = $fm->searchInLine('^'.$keyName.' ');
            if (is_array($keys) and sizeof($keys)) {
                // Key found
                //
                reset($keys);
                $line = key($keys);

                $line ++;
                $result = $fm->replaceLine($line, $keyName.' = '.$value);
            } else {
                // :KLUDGE: Alex Pagnoni 010716: ugly
                //This should be replaced by a better regexp

                $keys = $fm->searchInLine('^'.$keyName.'=');
                if (is_array($keys) and sizeof($keys)) {
                    // Key found
                    //
                    reset($keys);
                    $line = key($keys);
                    $line ++;
                    $result = $fm->replaceLine($line, $keyName.' = '.$value);
                } else {
                    // Key not found
                    //
                    $result = $fm->writeEnd($keyName.' = '.$value);
                }
            }

            $this->mConfigValues[$keyName] = $value;
        }

        return $result;
    }

    /*!
     @function ValuesArray

     @abstract Returns the array of the values.

     @result An array with all the key value pairs in the configuration file.
     */
    public function valuesArray()
    {
        return $this->mConfigValues;
    }

    /*!
     @function Opened

     @abstract Returns true it the given file exists and has been opened.
     */
    public function opened()
    {
        return $this->mOpened;
    }
}
