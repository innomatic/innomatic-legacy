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
namespace Innomatic\Php;

/**
 * This class checks PHP files for syntax errors.
 *
 * @author Alex Pagnoni <alex.pagnoni@innoteam.it>
 * @copyright Copyright 2012 Innoteam Srl
 * @since 1.0
 */
class PHPCodeChecker
{
    /**
     * Checks all the PHP files contained in a directory for syntax errors.
     *
     * If any error is found, the process stops itself and the method returns
     * false.
     *
     * @access public
     * @since 1.0
     * @return boolean
     */
    public static function checkDirectory($directory)
    {
        if (substr($directory, -1) != '/' and substr($directory, -1) != '\\') {
            $directory .= DIRECTORY_SEPARATOR;
        }
        $dh = opendir($directory);
        if ($dh) {
            while (($file = readdir($dh)) !== false) {
                if ($file != '.' and $file != '..') {
                    if (is_dir($directory.$file)) {
                        $result = self::checkDirectory(
                            $directory . $file . DIRECTORY_SEPARATOR
                        );
                        if (!$result) {
                            return false;
                        }
                    } else {
                        if (substr($directory . $file, -4) == '.php') {
                            $result = self::checkFile($directory . $file);
                            if (!$result) {
                                return false;
                            }
                        }
                    }
                }
            }
            closedir($dh);
        }
        return true;
    }

    /**
     * Check a PHP file for syntax errors using PHP cli with -l option.
     *
     * If any error is found, the method returns false.
     *
     * @access public
     * @since 1.0
     * @return boolean
     */
    public static function checkFile($file)
    {
        if (!file_exists($file)) {
            return null;
        }
        $result = exec('php -l "' . $file . '" 2>&1');
        if (strpos($result, 'No syntax errors detected') !== false) {
            return true;
        }
        return false;
    }
}
