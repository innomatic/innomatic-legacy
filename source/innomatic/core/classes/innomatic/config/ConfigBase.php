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
 * @license    http://www.innomatic.io/license/ New BSD License
 * @link       http://www.innomatic.io
 */
namespace Innomatic\Config;

/**
 * @since 5.0.0 introduced
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 */
class ConfigBase
{
    protected $container;
    
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
        $this->container = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');
        // Arguments check
        //
        if (strlen($configFile))
        $this->configFile = $configFile;
        else {
            
            $log = $this->container->getLogger();
            $log->LogDie('innomatic.configman.configbase.configbase', 'No config file');
        }

        if (!strlen($this->container->getConfig()->value('RootCrontab'))) {
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
            
            $log = $this->container->getLogger();
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
            
            $log = $this->container->getLogger();
            $log->logEvent(
                'innomatic.configman.configbase.writeconfig',
                'Unable to open destination configuration file '.$this->getdestfile(),
                \Innomatic\Logging\Logger::ERROR
            );
        }
        $this->updateLock();
        $this->unLockFile();

        if ($this->autoCommit and $this->configMode == ConfigBase::MODE_ROOT) {
            $userUpd = $this->container->getHome()
            .'core/bin/updater "'.md5($this->configFile).'.'.basename($this->configFile).'" "'
            .$this->container->getHome()
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
                        $this->container->getHome()
                        .'core/temp/'.md5($this->configFile).'.'
                        .basename($this->configFile).ConfigBase::UPDATINGEXT
                    )
                ) {
                    $sourceFile = $this->container->getHome()
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
                $destFile = $this->container->getHome()
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
                $this->container->getHome()
                .'core/temp/'.md5($this->configFile).'.'
                .basename($this->configFile).ConfigBase::LOCKEXT
            )
        ) {
            clearstatcache();
            sleep(1);
        }

        $result = @touch(
            $this->container->getHome()
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
            $this->container->getHome()
            .'core/temp/'.md5($this->configFile).'.'
            .basename($this->configFile).ConfigBase::LOCKEXT
        )
        ? @unlink(
            $this->container->getHome()
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
            $this->container->getHome()
            .'core/temp/'.md5($this->configFile).'.'
            .basename($this->configFile).ConfigBase::UPDATINGEXT, time()
        );
    }
}
