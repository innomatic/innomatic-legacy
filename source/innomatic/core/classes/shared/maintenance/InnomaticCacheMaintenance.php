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
 * @license    http://www.innomaticplatform.com/license/ New BSD License
 * @link       http://www.innomaticplatform.com
 * @since      Class available since Release 5.0
*/
namespace Shared\Maintenance;

class InnomaticCacheMaintenance extends \Innomatic\Maintenance\MaintenanceTask
{
    public $mApplicationSettings;
    public $mCleanCache;
    public $mCleanSessions;
    public $mCleanPidFiles;
    public $mCleanRootTempDirs;
    public $mCleanClipboard;

    public function __construct()
    {
        $this->mApplicationSettings = new \Innomatic\Application\ApplicationSettings(
            'innomatic'
           );

        $this->mCleanCache = $this->mApplicationSettings->getKey(
            'maintenance_cleancache'
       ) == '1' ? true : false;
        $this->mCleanSessions = $this->mApplicationSettings->getKey(
            'maintenance_cleansessions'
       ) == '1' ? true : false;
        $this->mCleanPidFiles = $this->mApplicationSettings->getKey(
            'maintenance_cleanpidfiles'
       ) == '1' ? true : false;
        $this->mCleanRootTempDirs = $this->mApplicationSettings->getKey(
            'maintenance_cleanroottempdirs'
       ) == '1' ? true : false;
        $this->mCleanClipboard = $this->mApplicationSettings->getKey(
            'maintenance_cleanclipboard'
       ) == '1' ? true : false;
    }

    public function setCleanCache(
        $clean
    )
    {
        $this->mApplicationSettings->setKey(
            'maintenance_cleancache',
            $clean ? '1' : '0'
           );
        $this->mCleanCache = $clean ? true : false;
    }

    public function getCleanCache()
    {
        return $this->mCleanCache;
    }

    public function setCleanSessions(
        $clean
    )
    {
        $this->mApplicationSettings->setKey(
            'maintenance_cleansessions',
            $clean ? '1' : '0'
           );
        $this->mCleanSessions = $clean ? true : false;
    }

    public function getCleanSessions()
    {
        return $this->mCleanSessions;
    }

    public function setCleanPidFiles(
        $clean
    )
    {
        $this->mApplicationSettings->setKey(
            'maintenance_cleanpidfiles',
            $clean ? '1' : '0'
           );
        $this->mCleanPidFiles = $clean ? true : false;
    }

    public function getCleanPidFiles()
    {
        return $this->mCleanPidFiles;
    }

    public function setCleanRootTempDirs(
        $clean
    )
    {
        $this->mApplicationSettings->setKey(
            'maintenance_cleanroottempdirs',
            $clean ? '1' : '0'
           );
        $this->mCleanRootTempDirs = $clean ? true : false;
    }

    public function getCleanRootTempDirs()
    {
        return $this->mCleanRootTempDirs;
    }

    public function setCleanClipboard(
        $clean
   )
    {
        $this->mApplicationSettings->setKey(
            'maintenance_cleanclipboard',
            $clean ? '1' : '0'
           );
        $this->mCleanClipboard = $clean ? true : false;
    }

    public function getCleanClipboard()
    {
        return $this->mCleanClipboard;
    }

    // ----- Cache, session, etc. -----

    public function getCacheSize()
    {
        return $this->dirSize(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome()
            . 'core/temp/cache'
           );
    }

    public function cleanCache()
    {
        $gc = new \Innomatic\Datatransfer\Cache\CacheGarbageCollector();
        return $gc->emptyCache();
    }

    public function getSessionsSize()
    {
        return $this->dirSize(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome()
            . 'core/temp/phpsessions'
       );
    }

    public function cleanSessions()
    {
        return $this->eraseDirContent(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome()
            . 'core/temp/phpsessions',
            session_id()
           );
    }

    public function getPidFilesSize()
    {
        return $this->dirSize(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome()
            . 'core/temp/pids'
       );
    }

    public function cleanPidFiles()
    {
        $innomatic = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');
        return $this->eraseDirContent(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome().'core/temp/pids',
            $innomatic->getPid()
        );
    }

    public function getRootTempDirsSize()
    {
        return $this->dirSize(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome().'core/temp/appinst');
    }

    public function cleanRootTempDirs()
    {
        return $this->eraseDirContent(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome().'core/temp/appinst');
    }

    public function getClipboardSize()
    {
        return $this->dirSize(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome().'core/temp/clipboard');
    }

    public function cleanClipboard()
    {
        return $this->eraseDirContent(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome().'core/temp/clipboard');
    }

    // ----- Facilities -----

    public function execute()
    {
        if ($this->mCleanCache) $this->CleanCache();
        if ($this->mCleanSessions) $this->CleanSessions();
        if ($this->mCleanPidFiles) $this->CleanPidFiles();
        if ($this->mCleanRootTempDirs) $this->CleanRootTempDirs();
        if ($this->mCleanClipboard) $this->CleanClipboard();

        return true;
    }

    public function getCleanableDiskSize()
    {
        $total = 0;

        $total += $this->getCacheSize();
        $total += $this->getSessionsSize();
        $total += $this->getPidFilesSize();
        $total += $this->getRootTempDirsSize();
        $total += $this->getClipboardSize();

        return $total;
    }

    private function dirSize(
        $dir
       )
    {
        $totalsize = 0;

        if ($dirstream = @opendir($dir)) {
            while (false !== ($filename = readdir($dirstream))) {
                if ($filename != '.' && $filename != '..') {
                    if (is_file($dir.'/'.$filename))
                        $totalsize += filesize($dir.'/'.$filename);

                    if (is_dir($dir.'/'.$filename))
                        $totalsize += $this->dirSize($dir.'/'.$filename);
                }
            }

            closedir($dirstream);
        }

        return $totalsize;
    }

    private function eraseDirContent(
        $dir,
        $preserveFile = ''
       )
    {
        $dirstream = @opendir($dir);
        if ($dirstream) {
            while (false !== ($filename = readdir($dirstream))) {
                if ($filename != '.' && $filename != '..' && $filename != $preserveFile) {
                    if (is_file($dir.'/'.$filename)) {
                        unlink($dir.'/'.$filename);
                    }

                    if (is_dir($dir.'/'.$filename)) {
                        \Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree($dir.'/'.$filename);
                    }
                }
            }

            closedir($dirstream);
        }

        return true;
    }
}
