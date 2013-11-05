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

class Archive {
    /*! @var mFile string - Full path of archive file. */
    protected $mFile;
    /*! @var mFormat string - Archive format. */
    protected $mFormat;
    const FORMAT_TAR = 'tar';
    const FORMAT_TGZ = 'tgz';
    const FORMAT_ZIP = 'zip';

    /*!
     @function Archive
    
     @abstract Class constructor.
    
     @param arcFile string - Full path of archive file.
     @param arcFormat string - Archive format.
     */
    public function __construct($arcFile, $arcFormat) {
        $this->mFile = $arcFile;
        $this->mFormat = $arcFormat;
    }

    /*!
     @function Extract
    
     @abstract Extracts the archive.
    
     @param destinationDir string - Full path of the destination dir for the extracted files.
    
     @result TRUE if the archive has been successfully extracted.
     */
    public function Extract($destinationDir) {
        $result = FALSE;

        if (file_exists($destinationDir)) {
            $old_dir = getcwd();

            if (@chdir($destinationDir)) {
                switch ($this->mFormat) {
                    case self::FORMAT_TAR :
                    case self::FORMAT_TGZ :
                        $result = TRUE;
                        require_once('innomatic/io/archive/archivers/Tar.php');
                        $tar = new tar();
                        if ($tar->openTar($this->mFile)) {
                            if ($tar->numDirectories > 0) {
                                foreach ($tar->directories as $id => $information) {
                                    // Fix for a PHP 5 bug under Windows
                                    if (isset($_ENV['OS']) and strpos(strtolower($_ENV['OS']), 'windows') !== false) {
                                        $information['name'] = str_replace('/', '\\', $information['name']);
                                    }
                                    if (!file_exists($information['name'])) {
                                        @mkdir($destinationDir.'/'.$information['name'], 0755, true);
                                    }
                                }
                            }

                            if ($tar->numFiles > 0) {
                                foreach ($tar->files as $id => $information) {
                                    if (!file_exists($information['name'])) {
                                        if ($fp = @fopen($information['name'], 'wb')) {
                                            @fwrite($fp, $information['file']);
                                            @fclose($fp);
                                            $mode = substr($information['mode'], -5);
                                            @chmod($information['name'], octdec($mode));
                                        } else
                                            $result = FALSE;
                                    }
                                }
                            }
                        }

                        break;

                    case self::FORMAT_ZIP :
                        $result = true;
                        require_once('innomatic/io/archive/archivers/PclZip.php');
                        $zip = new PclZip($this->mFile);
                        $list = $zip->extract(PCLZIP_OPT_PATH, $destinationDir);
                        break;
                }
                @chdir($old_dir);
            }
        }

        return $result;
    }
}
