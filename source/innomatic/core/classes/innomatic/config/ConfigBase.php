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
namespace Innomatic\Config;

/*!
 @class ConfigBase

 @abstract File manipulation utilty, it transparently handles root files.
 */
class ConfigBase
{
    /*! @var mConfigFile string - Location of configuration file. */
    protected $configFile;
    /*! @var mConfigMode integer - Configuration file handling, ConfigBase::MODE_ROOT if it is a root file,
                                    ConfigBase::MODE_DIRECT if it can be directly written.
    */
    protected $configMode;
    protected $cron;
    protected $autoCommit;
    protected $application;
    protected $entry;

    const UPDATINGEXT = '.upd';
    const LOCKEXT = '.lck';
    const MODE_ROOT = 1;
    const MODE_DIRECT = 2;
    const POSITION_TOP = 1;
    const POSITION_BOTTOM = 2;

    /*!
     @function ConfigBase

     @abstract Class constructor.
     */
    public function __construct(
        $configFile,
        $configMode = ConfigBase::MODE_ROOT,
        $autoCommit = false,
        $application = '',
        $entry = ''
    ) {
        // Arguments check
        //
        if (strlen($configFile))
        $this->configFile = $configFile;
        else {
            
            $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
            $log->LogDie('innomatic.configman.configbase.configbase', 'No config file');
        }

        if (!strlen(InnomaticContainer::instance('innomaticcontainer')->getConfig()->value('RootCrontab'))) {
            $configMode = ConfigBase::MODE_DIRECT;
        }

        $this->configMode = $configMode;
        $this->autoCommit = $autoCommit;

        if ($this->autoCommit) {
            $this->application = $application;
            $this->entry = $entry;
            $this->cron = new \Innomatic\Process\Crontab($application);
        }
    }

    /*!
     @function ReadConfig

     @abstract Reads the file.

     @result File content.
     */
    public function readConfig()
    {
        $result = false;
        $src = $this->getSrcFile();
        $this->lockFile();

        if (file_exists($src)) {
            $result = file_get_contents($src);
            //$this->unlockfile();
        } else {
            
            $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
            $log->logEvent(
                'innomatic.configman.configbase.readconfig',
                'Unable to open configuration file',
                \Innomatic\Logging\Logger::ERROR
            );
        }
        $this->UnLockFile();

        return $result;
    }

    /*!
     @function WriteConfig

     @abstract Writes the file.
     */
    public function writeConfig($buffer)
    {
        $result = false;
        $this->LockFile();

        if ($fh = @fopen($this->getDestFile(), 'w')) {
            fwrite($fh, $buffer);
            fclose($fh);
        } else {
            
            $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
            $log->logEvent(
                'innomatic.configman.configbase.writeconfig',
                'Unable to open destination configuration file '.$this->getdestfile(),
                \Innomatic\Logging\Logger::ERROR
            );
        }
        $this->updateLock();
        $this->unLockFile();

        if ($this->autoCommit and $this->configMode == ConfigBase::MODE_ROOT) {
            $userUpd = InnomaticContainer::instance('innomaticcontainer')->getHome()
            .'core/bin/updater "'.md5($this->configFile).'.'.basename($this->configFile).'" "'
            .InnomaticContainer::instance('innomaticcontainer')->getHome()
            .'core/temp/" "'.$this->configFile.'"'."\n";
            $this->cron->addEntry($this->entry, $userUpd, \Innomatic\Process\Crontab::TYPE_TEMPORARY);
        }

        return $result;
    }

    /*!
     @function getSrcFile

     @abstract Returns the current source file name.
     */
    public function getSrcFile()
    {
        switch ($this->configMode) {
            case ConfigBase::MODE_ROOT :
                if (
                    file_exists(
                        InnomaticContainer::instance('innomaticcontainer')->getHome()
                        .'core/temp/'.md5($this->configFile).'.'
                        .basename($this->configFile).ConfigBase::UPDATINGEXT
                    )
                ) {
                    $sourceFile = InnomaticContainer::instance('innomaticcontainer')->getHome()
                    .'core/temp/'.md5($this->configFile).'.'.basename($this->configFile);
                } else {
                    $sourceFile = $this->configFile;
                }
                break;

            case ConfigBase::MODE_DIRECT :
                $sourceFile = $this->configFile;
                break;
        }

        return $sourceFile;
    }

    /*!
     @function getDestFile

     @abstract Returns the current destination file name.
     */
    public function getDestFile()
    {
        switch ($this->configMode) {
            case ConfigBase::MODE_ROOT :
                $destFile = InnomaticContainer::instance('innomaticcontainer')->getHome()
                .'core/temp/'.md5($this->configFile).'.'.basename($this->configFile);
                break;

            case ConfigBase::MODE_DIRECT :
                $destFile = $this->configFile;
                break;
        }

        return $destFile;
    }

    /*!
     @function LockFile

     @abstract Locks the file.
     */
    public function lockFile()
    {
        while (
            file_exists(
                InnomaticContainer::instance('innomaticcontainer')->getHome()
                .'core/temp/'.md5($this->configFile).'.'
                .basename($this->configFile).ConfigBase::LOCKEXT
            )
        ) {
            clearstatcache();
            sleep(1);
        }

        $result = @touch(
            InnomaticContainer::instance('innomaticcontainer')->getHome()
            .'core/temp/'.md5($this->configFile).'.'
            .basename($this->configFile).ConfigBase::LOCKEXT, time()
        );

        return $result;
    }

    /*!
     @function UnLockFile

     @abstract Unlocks the file.
     */
    public function unLockFile()
    {
        $result = file_exists(
            InnomaticContainer::instance('innomaticcontainer')->getHome()
            .'core/temp/'.md5($this->configFile).'.'
            .basename($this->configFile).ConfigBase::LOCKEXT
        )
        ? @unlink(
            InnomaticContainer::instance('innomaticcontainer')->getHome()
            .'core/temp/'.md5($this->configFile).'.'.basename($this->configFile).ConfigBase::LOCKEXT
        )
        : true;

        return $result;
    }

    /*!
     @function UpdateLock

     @abstract Creates the update lock file.
     */
    public function updateLock()
    {
        if ($this->configMode == ConfigBase::MODE_ROOT)
        @touch(
            InnomaticContainer::instance('innomaticcontainer')->getHome()
            .'core/temp/'.md5($this->configFile).'.'
            .basename($this->configFile).ConfigBase::UPDATINGEXT, time()
        );
    }
}
