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

require_once('innomatic/maintenance/MaintenanceTask.php');

class InnomaticCacheMaintenance extends MaintenanceTask {
    public $mApplicationSettings;
    public $mCleanCache;
    public $mCleanSessions;
    public $mCleanPidFiles;
    public $mCleanRootTempDirs;
    public $mCleanClipboard;

    public function __construct()
    {
        require_once('innomatic/application/ApplicationSettings.php');

        $this->mApplicationSettings = new ApplicationSettings(
            InnomaticContainer::instance('innomaticcontainer')->getDataAccess(),
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
            InnomaticContainer::instance('innomaticcontainer')->getHome()
            . 'WEB-INF/temp/cache'
           );
    }

    public function cleanCache()
    {
        require_once('innomatic/datatransfer/cache/CacheGarbageCollector.php');
        $gc = new CacheGarbageCollector();
        return $gc->EmptyCache();
    }

    public function getSessionsSize()
    {
        return $this->dirSize(
            InnomaticContainer::instance('innomaticcontainer')->getHome()
            . 'WEB-INF/temp/phpsessions'
       );
    }

    public function cleanSessions()
    {
        return $this->eraseDirContent(
            InnomaticContainer::instance('innomaticcontainer')->getHome()
            . 'WEB-INF/temp/phpsessions',
            session_id()
           );
    }

    public function getPidFilesSize()
    {
        return $this->dirSize(
            InnomaticContainer::instance('innomaticcontainer')->getHome()
            . 'WEB-INF/temp/pids'
       );
    }

    public public function cleanPidFiles()
    {
        require_once('innomatic/core/InnomaticContainer.php');
        $innomatic = InnomaticContainer::instance('innomaticcontainer');
        return $this->eraseDirContent(
            InnomaticContainer::instance('innomaticcontainer')->getHome().'WEB-INF/temp/pids',
            $innomatic->getPid()
           );
    }

    public function getRootTempDirsSize()
    {
        return $this->dirSize(InnomaticContainer::instance('innomaticcontainer')->getHome().'WEB-INF/temp/appinst');
    }

    public function cleanRootTempDirs()
    {
        return $this->eraseDirContent(InnomaticContainer::instance('innomaticcontainer')->getHome().'WEB-INF/temp/appinst');
    }

    public function getClipboardSize()
    {
        return $this->dirSize(InnomaticContainer::instance('innomaticcontainer')->getHome().'WEB-INF/temp/clipboard');
    }

    public function cleanClipboard()
    {
        return $this->eraseDirContent(InnomaticContainer::instance('innomaticcontainer')->getHome().'WEB-INF/temp/clipboard');
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
            require_once('innomatic/io/filesystem/DirectoryUtils.php');
            
            while (false !== ($filename = readdir($dirstream))) {
                if ($filename != '.' && $filename != '..' && $filename != $preserveFile)
                {
                    if (is_file($dir.'/'.$filename)) {
                        unlink($dir.'/'.$filename);
                    }

                    if (is_dir($dir.'/'.$filename)) {
                        DirectoryUtils::unlinkTree($dir.'/'.$filename);
                    }
                }
            }

            closedir($dirstream);
        }

        return true;
    }
}
