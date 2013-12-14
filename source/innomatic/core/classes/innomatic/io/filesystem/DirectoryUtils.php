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
namespace Innomatic\Io\Filesystem;

/**
 * @since 1.0
 * @author Alex Pagnoni <alex.pagnoni@innoteam.it>
 * @copyright Copyright 2000-2012 Innoteam Srl
 */
class DirectoryUtils
{
    public static function unlinkTree($dirname)
    {
        $result = true;
        if (file_exists($dirname)) {
            if ($dhandle = @opendir($dirname)) {
                while (false != ($file = @readdir($dhandle))) {
                    if ($file != '.' && $file != '..') {
                        if (is_file($dirname.'/'.$file))
                            $result = @unlink($dirname.'/'.$file);
                        else if (is_dir($dirname.'/'.$file)) $result = self::unlinkTree($dirname.'/'.$file);
                    }
                }
                @closedir($dhandle);
                @rmdir($dirname);
            }
        }
        return $result;
    }

    public static function dirCopy($from_path, $to_path)
    {
        $result = true;

        $this_path = getcwd();
        if (!is_dir($to_path)) {
            mkdir($to_path, 0775, true);
        }

        if (is_dir($from_path)) {
            chdir($from_path);
            $handle = opendir('.');
            while (($file = readdir($handle)) !== false) {
                if (($file != ".") && ($file != "..")) {
                    if (is_dir($file)) {
                        chdir($this_path);
                        $result = self::dirCopy($from_path.$file."/", $to_path.$file."/");
                        chdir($this_path);
                        chdir($from_path);
                    }
                    if (is_file($file)) {
                        chdir($this_path);
                        $result = copy($from_path.$file, $to_path.$file);
                        chdir($from_path);
                    }
                }
            }
            closedir($handle);
        }
        chdir($this_path);

        return $result;
    }

    /**
    * Return a context-relative path, beginning with a "/", that represents
    * the canonical version of the specified path after ".." and "." elements
    * are resolved out.  If the specified path attempts to go outside the
    * boundaries of the current context (i.e. too many ".." path elements are
    * present), return <code>null</code> instead.
    *
    * @param string $path
    * @access public
    * @return string
    */
    public static function normalize($path)
    {
        if (is_null($path)) {
            return null;
        }

        $normalized = $path;

        // replace backslashes '\' with a forward slash '/'
        if (strpos($normalized, '\\') !== false) {
            $normalized = str_replace('\\', '/', $normalized);
        }

        // make sure it begins with a '/'
        if (strpos($normalized, '/') !== 0) {
            $normalized = '/'.$normalized;
        }

        // if ends in directory command ('.' or '..'), append a '/'
        if (substr($normalized, -2) == '/.' || substr($normalized, -3) == '/..') {
            $normalized .= '/';
        }

        // replace references to current directory '/./' or repeated slashes '//'
        $normalized = preg_replace(';/\.?(?=/);', '', $normalized);

        // replace references to parent directory
        while (true) {
            $index = strpos($normalized, '/../');
            if ($index === false) {
                break;
            }
            // trying to go outside of context
            else if ($index === 0) {
                return null;
            } else {
                $index2 = strrpos(substr($normalized, 0, $index -1), '/');
                $normalized = substr($normalized, 0, $index2).substr($normalized, $index +3);
            }
        }

        return $normalized;
    }

    public static function mktree($strPath, $nPermission)
    {
        $strPathSeparator = "/";
        $strDirname = substr($strPath, 0, strrpos($strPath, $strPathSeparator));
        if (is_dir($strDirname)) {
            return true;
        }

        $arMake = array();
        array_unshift($arMake, $strDirname);
        do {
            $bStop = true;
            $nPos = strrpos($strDirname, $strPathSeparator);
            $strParent = substr($strDirname, 0, $nPos);
            if (!is_dir($strParent)) {
                $strDirname = $strParent;
                array_unshift($arMake, $strDirname);

                $bStop = false;
            }
        } while (!$bStop);

        if (count($arMake) > 0) {
            foreach ($arMake as $strDir) {
                mkdir($strDir, $nPermission);
            }
        }
        return true;
    }
}
