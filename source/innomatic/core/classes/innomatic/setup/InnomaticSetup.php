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
namespace Innomatic\Setup;

use \Innomatic\Core\InnomaticContainer;

if (\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getState() != \Innomatic\Core\InnomaticContainer::STATE_SETUP) {
    return;
}

class InnomaticSetup
{
    public static function setup_by_config_file($configFile = '', $echo = false)
    {
        $container = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');

        $successString = "[  \033[1;32mOK\033[0;39m  ]\n";
        $failureString = "[\033[1;31mFAILED\033[0;39m]\n";

        if (strlen($configFile) and file_exists($configFile)) {
            $configFileName = $configFile;
        } else $configFileName = $container->getHome().'core/conf/kickstart.ini';

        if (!file_exists($configFileName)) {
            if ($echo) echo $failureString;
            return false;
        }
        $log = $container->getLogger();

        $cfg = @parse_ini_file($configFileName);
        if ($echo) echo str_pad('System check: ', 60);
        if (!InnomaticSetup::checksystem('', $log)) {
        if ($echo) echo $failureString;
        return false;
        }

        if ($echo) echo $successString.str_pad('Edition setting: ', 60);

        if (!InnomaticSetup::setEdition(array('edition' => $cfg['PlatformEdition']))) {
            if ($echo) echo $failureString;
            return false;
        }

        if ($echo) echo $successString.str_pad('Files installation: ', 60);

        if (!InnomaticSetup::installfiles('', $log)) {
            if ($echo) echo $failureString;
            return false;
        }

        if ($echo) echo $successString.str_pad('Database drivers creation: ', 60);

        if (!InnomaticSetup::dataaccessdrivers('', $log)) {
            if ($echo) echo $failureString;
            return false;
        }

        if ($echo) echo $successString.str_pad('Innomatic database creation: ', 60);

        $dbArgs = array(
                                                 'dbtype' => $cfg['RootDatabaseType'],
                                                 'dbname' => $cfg['RootDatabaseName'],
                                                 'dbhost' => $cfg['RootDatabaseHost'],
                                                 'dbport' => $cfg['RootDatabasePort'],
                                                 'dbuser' => $cfg['RootDatabaseUser'],
                                                 'dbpass' => $cfg['RootDatabasePassword']
        );

        if (!InnomaticSetup::createdb($dbArgs, $log)) {
            if ($echo) echo $failureString;
            return false;
        }

        if ($echo) echo $successString.str_pad('Components initialization (may take some time): ', 60);

        $reg = \Innomatic\Util\Registry::instance();
        $dbArgs['dblog'] = $container->getHome().'core/log/innomatic_root_db.log';

        if (!InnomaticSetup::initializecomponents($dbArgs, $log)) {
            if ($echo) echo $failureString;
            return false;
        }

        if ($echo) echo $successString.str_pad('Root password: ', 60);

        $pwdArgs = $dbArgs;
        $pwdArgs['passworda'] = $cfg['RootPassword'];
        $pwdArgs['passwordb'] = $cfg['RootPassword'];

        if (!InnomaticSetup::setpassword($pwdArgs, $log)) {
            if ($echo) echo $failureString;
            return false;
        }

        if ($echo) echo $successString.str_pad('Network settings: ', 60);

        $hostArgs['innomatichost'] = $cfg['PlatformName'];
        $hostArgs['innomaticgroup'] = $cfg['PlatformGroup'];

        if (!InnomaticSetup::setinnomatichost($hostArgs, $log)) {
            if ($echo) echo $failureString;
            return false;
        }

        if ($echo) echo $successString.str_pad('Country setting: ', 60);

        $country_args['country'] = $cfg['RootCountry'];

        if (!InnomaticSetup::setcountry($country_args, $log)) {
            if ($echo) echo $failureString;
            return false;
        }

        if ($echo) echo $successString.str_pad('Language setting: ', 60);

        $lang_args['language'] = $cfg['RootLanguage'];

        if (!InnomaticSetup::setlanguage($lang_args, $log)) {
            if ($echo) echo $failureString;
            return false;
        }

        if ($echo) echo $successString.str_pad('Base URL setting: ', 60);

        if (!InnomaticSetup::setBaseUrl($cfg['InnomaticBaseUrl'])) {
            if ($echo) echo $failureString;
            return false;
        }

        if ($echo) echo $successString.str_pad('Clean up: ', 60);

        if (!InnomaticSetup::cleanup('', $log)) {
            if ($echo) echo $failureString;
            return false;
        }

        if ($echo) echo $successString.str_pad('Setup finish: ', 60);

        if (!InnomaticSetup::finish('', $log)) {
            if ($echo) echo $failureString;
            return false;
        }

        if ($echo) echo $successString.str_pad('Remotion of setup phases locks: ', 60);

        if (!InnomaticSetup::check_lock_files()) {
            if ($echo) echo $failureString;
            return false;
        }

        if ($echo) echo $successString.str_pad('Remotion of setup lock: ', 60);

        InnomaticSetup::remove_lock_files();
        if (!InnomaticSetup::remove_setup_lock_file()) {
            if ($echo) echo $failureString;
            return false;
        }

        echo $successString;
        return true;
    }

    public static function checksystem($eventData = '', $log = '')
    {
        $container = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');

        @touch($container->getHome().'core/temp/setup_systemchecked', time());
        if (file_exists($container->getHome().'core/temp/setup_checkingsystem')) @unlink($container->getHome().'core/temp/setup_checkingsystem');

        return true;
    }

    public static function installFiles($eventData = '', $log = '')
    {
        $container = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');

        InnomaticSetup::recursive_copy($container->getHome(), $container->getHome().'core/temp/innomatic');

        @touch($container->getHome().'core/temp/setup_filesinstalled', time());
        if (file_exists($container->getHome().'core/temp/setup_installingfiles')) @unlink($container->getHome().'core/temp/setup_installingfiles');

        return true;
    }

    public static function setEdition($eventData, $log = '')
    {
        $container = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');

        @touch($container->getHome().'core/temp/setup_editionset', time());
        if (file_exists($container->getHome().'core/temp/setup_settingedition')) @unlink($container->getHome().'core/temp/setup_settingedition');
        $innomaticcfg = new \Innomatic\Config\ConfigFile($container->getConfigurationFile());
        $innomaticcfg->setValue('PlatformEdition', $eventData['edition']);

        return true;
    }

    public static function dataaccessdrivers($eventData = '', $log = '')
    {
        $container = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');

        $daf = new \Innomatic\Dataaccess\DataAccessFactory();
        $daf->addDriver('mysql', 'MySQL 3.22+');
        $daf->addDriver('pgsql', 'PostgreSQL 7.0+');

        @touch($container->getHome().'core/temp/setup_dataaccessdriverscreated', time());
        if (file_exists($container->getHome().'core/temp/setup_creatingdataaccessdrivers')) {
            @unlink($container->getHome().'core/temp/setup_creatingdataaccessdrivers');
        }

        return true;
    }

    public static function createDb($eventData = '', $log = '')
    {
        $result = false;
        $reg = \Innomatic\Util\Registry::instance();
        $innomatic = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');

        $dasn_string = $eventData['dbtype'].'://'.
            $eventData['dbuser'].':'.
            $eventData['dbpass'].'@'.
            $eventData['dbhost'].':'.
            $eventData['dbport'].'/'.
            $eventData['dbname'].'?'.
            'logfile='.$innomatic->getHome().'core/log/innomatic_root_db.log';
        $tmpdb = \Innomatic\Dataaccess\DataAccessFactory::getDataAccess(new \Innomatic\Dataaccess\DataAccessSourceName($dasn_string));

        if ($tmpdb->Connect()) {
            $tmpdb->DropDB($eventData);
            $tmpdb->Close();
        }

        if ($tmpdb->CreateDB($eventData)) {
            if ($tmpdb->Connect()) {
                // Tables creation
                //
                $xmldb = new \Innomatic\Dataaccess\DataAccessXmlTable($tmpdb, \Innomatic\Dataaccess\DataAccessXmlTable::SQL_CREATE);
                if ($xmldb->load_DefFile($innomatic->getHome().'core/db/innomatic_root.xml')) {
                    if ($tmpdb->execute($xmldb->getSQL())) {
                        // Database configuration file creation
                        //
                        $fh = @fopen($innomatic->getHome().'core/conf/innomatic.ini', 'a');
                        if ($fh) {
                            fputs($fh, 'RootDatabaseType = '.$eventData['dbtype']."\n");
                            fputs($fh, 'RootDatabaseName = '.$eventData['dbname']."\n");
                            fputs($fh, 'RootDatabaseHost = '.$eventData['dbhost']."\n");
                            fputs($fh, 'RootDatabasePort = '.$eventData['dbport']."\n");
                            fputs($fh, 'RootDatabaseUser = '.$eventData['dbuser']."\n");
                            fputs($fh, 'RootDatabasePassword = '.$eventData['dbpass']."\n");
                            fputs($fh, 'RootDatabaseDebug = 0'."\n");
                            fclose($fh);

                            $result = true;

                            @touch($innomatic->getHome().'core/temp/setup_dbcreated', time());
                            if (file_exists($innomatic->getHome().'core/temp/setup_creatingdb')) @unlink($innomatic->getHome().'core/temp/setup_creatingdb');
                        } else $log->logevent('innomatic.root.main_php',
                                             'Unable to create root database configuration file during initialization', \Innomatic\Logging\Logger::ERROR);
                    } else $log->logevent('innomatic.root.main_php',
                                         'Unable to create root database tables during initialization', \Innomatic\Logging\Logger::ERROR);
                } else $log->logevent('innomatic.root.main_php',
                                     'Unable to open Innomatic structure file during initialization', \Innomatic\Logging\Logger::ERROR);
            } else $log->logevent('innomatic.root.main_php',
                                 'Unable to connect to root database during initialization', \Innomatic\Logging\Logger::ERROR);
        } else $log->logevent('innomatic.root.main_php',
                             'Unable to create root database during initialization: '.$tmpdb->getLastError(), \Innomatic\Logging\Logger::ERROR);

        return $result;
    }

    public static function initializeComponents($eventData = '', $log = '')
    {
        $result = false;

        $container = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');

        if (isset($eventData['dbname']) and strlen($eventData['dbname'])) {
            $args = $eventData;
        } else {
            $args['dbname'] = $container->getConfig()->value('RootDatabaseName');
            $args['dbhost'] = $container->getConfig()->value('RootDatabaseHost');
            $args['dbport'] = $container->getConfig()->value('RootDatabasePort');
            $args['dbuser'] = $container->getConfig()->value('RootDatabaseUser');
            $args['dbpass'] = $container->getConfig()->value('RootDatabasePassword');
            $args['dbtype'] = $container->getConfig()->value('RootDatabaseType');
            $args['dblog']  = $container->getHome().'core/log/innomatic_root_db.log';
        }
                    $dasn_string = $args['dbtype'].'://'.
                        $args['dbuser'].':'.
                        $args['dbpass'].'@'.
                        $args['dbhost'].':'.
                        $args['dbport'].'/'.
                        $args['dbname'].'?'.
                        'logfile='.$args['dblog'];
        $tmpdb = \Innomatic\Dataaccess\DataAccessFactory::getDataAccess(new \Innomatic\Dataaccess\DataAccessSourceName($dasn_string));
        if ($tmpdb->connect()) {
            // Components initialization
            //
            $innomaticmod = new \Innomatic\Application\Application($tmpdb);
            if ($innomaticmod->setup($container->getHome().'core/temp/innomatic/')) {
                $modreg = new \Innomatic\Application\ApplicationComponentRegister($tmpdb);
                $modreg->registerComponent('innomatic', 'configurationfile', 'innomatic.ini', '', \Innomatic\Application\ApplicationComponent::OVERRIDE_NONE);
                $modreg->registerComponent('innomatic', 'configurationfile', 'dataaccessdrivers.ini', '', \Innomatic\Application\ApplicationComponent::OVERRIDE_NONE);

                $result = true;

                @touch($container->getHome().'core/temp/setup_componentsinitialized', time());
                if (file_exists($container->getHome().'core/temp/setup_initializingcomponents')) {
                    @unlink($container->getHome().'core/temp/setup_initializingcomponents');
                }
            } else $log->logevent('innomatic.root.main_php',
                                'Unable to setup Innomatic during initialization', \Innomatic\Logging\Logger::ERROR);
        } else $log->logevent('innomatic.root.main_php',
                            'Unable to connect to root database during initialization', \Innomatic\Logging\Logger::ERROR);

        return $result;
    }

    public static function setPassword($eventData, $log = '')
    {
        $result = false;
        $container = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');

        // Password setting
        //
        if (strlen($eventData['passworda']) and ($eventData['passworda'] == $eventData['passwordb'])) {
            // Creates Innomatic root password file
            //
            $fh = @fopen($container->getHome().'core/conf/rootpasswd.ini', 'w');
            if ($fh) {
                fputs($fh, md5($eventData['passworda']));
                fclose($fh);
                if (@touch($container->getHome().'core/temp/setup_passwordset', time())) {
                    if (isset($eventData['dbname']) and strlen($eventData['dbname'])) {
                        $args = $eventData;
                    } else {
                        $args['dbname'] = $container->getConfig()->value('RootDatabaseName');
                        $args['dbhost'] = $container->getConfig()->value('RootDatabaseHost');
                        $args['dbport'] = $container->getConfig()->value('RootDatabasePort');
                        $args['dbuser'] = $container->getConfig()->value('RootDatabaseUser');
                        $args['dbpass'] = $container->getConfig()->value('RootDatabasePassword');
                        $args['dbtype'] = $container->getConfig()->value('RootDatabaseType');
                        $args['dblog']  = $container->getHome().'core/log/innomatic_root_db.log';
                    }

                    $dasn_string = $args['dbtype'].'://'.
                        $args['dbuser'].':'.
                        $args['dbpass'].'@'.
                        $args['dbhost'].':'.
                        $args['dbport'].'/'.
                        $args['dbname'].'?'.
                        'logfile='.$args['dblog'];
                    $root_db = \Innomatic\Dataaccess\DataAccessFactory::getDataAccess(new \Innomatic\Dataaccess\DataAccessSourceName($dasn_string));

                    if ($root_db->connect()) {
                        $modreg = new \Innomatic\Application\ApplicationComponentRegister($root_db);
                        $modreg->registerComponent('innomatic', 'configurationfile', 'rootpasswd.ini', '', \Innomatic\Application\ApplicationComponent::OVERRIDE_NONE);

                        $result = true;

                        if (file_exists($container->getHome().'core/temp/setup_settingpassword')) {
                            @unlink($container->getHome().'core/temp/setup_settingpassword');
                        }
                    } else $log->logevent('innomatic.root.main_php',
                                        'Unable to connect to Innomatic database during initialization', \Innomatic\Logging\Logger::ERROR);
                } else $log->logevent('innomatic.root.main_php',
                                    'Unable to create .passwordset lock file during initialization', \Innomatic\Logging\Logger::ERROR);
            } else $log->logevent('innomatic.root.main_php',
                                'Unable to create root password file', \Innomatic\Logging\Logger::ERROR);
        }

        return $result;
    }

    public static function setInnomaticHost($eventData, $log = '')
    {
        $container = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');

        @touch($container->getHome().'core/temp/setup_innomatichostset', time());
        if (file_exists($container->getHome().'core/temp/setup_settinginnomatichost')) {
            @unlink($container->getHome().'core/temp/setup_settinginnomatichost');
        }

        $innomaticcfg = new \Innomatic\Config\ConfigFile($container->getConfigurationFile());
        $innomaticcfg->setValue('PlatformName', $eventData['innomatichost']);
        $innomaticcfg->setValue('PlatformGroup', $eventData['innomaticgroup']);

        return true;
    }

    public static function setCountry($eventData, $log = '')
    {
        $container = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');

        @touch($container->getHome().'core/temp/setup_countryset', time());
        if (file_exists($container->getHome().'core/temp/setup_settingcountry')) {
            @unlink($container->getHome().'core/temp/setup_settingcountry');
        }
        $innomaticcfg = new \Innomatic\Config\ConfigFile($container->getConfigurationFile());
        $innomaticcfg->setValue('RootCountry', $eventData['country']);

        return true;
    }

    public static function setLanguage($eventData, $log = '')
    {
        $container = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');

        @touch($container->getHome().'core/temp/setup_languageset', time());
        if (file_exists($container->getHome().'core/temp/setup_settinglanguage')) {
            @unlink($container->getHome().'core/temp/setup_settinglanguage');
        }
        $innomaticcfg = new \Innomatic\Config\ConfigFile($container->getConfigurationFile());
        $innomaticcfg->setValue('RootLanguage', $eventData['language']);

        return true;
    }
/*
    public static function appcentral($eventData, $log = '')
    {
        $container = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');

        if (isset($eventData['appcentral']) and $eventData['appcentral'] == 'on') {
            $xmlrpcClient = new \Innomatic\Webservices\Xmlrpc\XmlRpcClient(
                '/webservices/',
                'stable.innomatic.org',
                80
               );

            $xmlrpc_message = new \Innomatic\Webservices\Xmlrpc\XmlRpcMsg(
                'appcentral-server.retrieve_appcentral_client'
               );

            $xmlrpc_resp = $xmlrpcClient->Send($xmlrpc_message);

            if ($xmlrpc_resp) {
                if (!$xmlrpc_resp->FaultCode()) {
                    $xv = $xmlrpc_resp->Value();

                    $tmp_filename = $container->getHome().'core/temp/appinst/appcentral-client.tgz';

                    $fh = fopen($tmp_filename, 'wb');
                    if ($fh) {
                        fputs($fh, $xv->scalarVal());
                        fclose($fh);

                        unset($xv);
                        unset($xmlrpc_resp);

                        $args['dbname'] = $container->getConfig()->value('RootDatabaseName');
                        $args['dbhost'] = $container->getConfig()->value('RootDatabaseHost');
                        $args['dbport'] = $container->getConfig()->value('RootDatabasePort');
                        $args['dbuser'] = $container->getConfig()->value('RootDatabaseUser');
                        $args['dbpass'] = $container->getConfig()->value('RootDatabasePassword');
                        $args['dbtype'] = $container->getConfig()->value('RootDatabaseType');
                        $args['dblog']  = $container->getHome().'core/log/innomatic_root_db.log';

                    $dasn_string = $args['dbtype'].'://'.
                        $args['dbuser'].':'.
                        $args['dbpass'].'@'.
                        $args['dbhost'].':'.
                        $args['dbport'].'/'.
                        $args['dbname'].'?'.
                        'logfile='.$args['dblog'];
                        $root_db = \Innomatic\Dataaccess\DataAccessFactory::getDataAccess(new \Innomatic\Dataaccess\DataAccessSourceName($dasn_string));
                        if ($root_db->connect()) {
                            $tmp_application = new \Innomatic\Application\Application($root_db, '');
                            $tmp_application->Install($tmp_filename);

                            @touch($container->getHome().'core/temp/setup_appcentralset', time());
                            if (file_exists($container->getHome().'core/temp/setup_settingappcentral')) @unlink($container->getHome().'core/temp/setup_settingappcentral');
                        }
                    }
                }
            }
        } else {
            @touch($container->getHome().'core/temp/setup_appcentralset', time());
            if (file_exists($container->getHome().'core/temp/setup_settingappcentral')) @unlink($container->getHome().'core/temp/setup_settingappcentral');
        }
    }
    */

    public static function setBaseUrl($url = '')
    {
        $container = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');

        $fh = @fopen($container->getHome().'core/conf/innomatic.ini', 'a');
        if ($fh) {
            if (strlen($url)) {
                $req_url = $url;
            } else {
                $req_url = $container->getExternalBaseUrl();
            }
            fputs($fh, 'InnomaticBaseUrl = '.$req_url."\n");
            fclose($fh);
        } else {
            return false;
        }

        return true;
    }

    public static function cleanup($eventData = '', $log = '')
    {
        $container = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');

        \Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree($container->getHome().'core/temp/innomatic/');
        \Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree($container->getHome().'setup');
        //@unlink($container->getHome().'core/conf/kickstart.ini');
        //@unlink($container->getHome().'core/bin/kickstart.php');

        @touch($container->getHome().'core/temp/setup_cleanedup', time());
        if (file_exists($container->getHome().'core/temp/setup_cleaningup')) {
            @unlink($container->getHome().'core/temp/setup_cleaningup');
        }
        @touch($container->getHome().'core/temp/setup_setupfinished', time());

        return true;
    }

    public static function finish($eventData = '', $log = '')
    {
        $container = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');

        //@unlink($container->getHome().'core/classes/innomatic/setup/InnomaticSetup.php');
        @touch($container->getHome().'core/temp/setup_done', time());
        $log->logEvent(
            'Innomatic',
            'Innomatic setup has been completed - Operating',
            \Innomatic\Logging\Logger::NOTICE
        );

        return true;
    }

    public static function check_lock_files()
    {
        $container = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');

        if (
            file_exists($container->getHome().'core/temp/setup_systemchecked') and
            file_exists($container->getHome().'core/temp/setup_filesinstalled') and
            file_exists($container->getHome().'core/temp/setup_dataaccessdriverscreated') and
            file_exists($container->getHome().'core/temp/setup_dbcreated') and
            file_exists($container->getHome().'core/temp/setup_componentsinitialized') and
            file_exists($container->getHome().'core/temp/setup_passwordset') and
            file_exists($container->getHome().'core/temp/setup_innomatichostset') and
            file_exists($container->getHome().'core/temp/setup_countryset') and
            file_exists($container->getHome().'core/temp/setup_languageset') and
//            file_exists($container->getHome().'core/temp/setup_appcentralset') and
            file_exists($container->getHome().'core/temp/setup_editionset') and
            file_exists($container->getHome().'core/temp/setup_cleanedup') and
            file_exists($container->getHome().'core/temp/setup_done')
          )
      {
          return true;
      } else {
        return false;
      }
    }

    public static function remove_lock_files()
    {
        $container = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');

        @unlink($container->getHome().'core/temp/setup_systemchecked');
        @unlink($container->getHome().'core/temp/setup_filesinstalled');
        @unlink($container->getHome().'core/temp/setup_dataaccessdriverscreated');
        @unlink($container->getHome().'core/temp/setup_dbcreated');
        @unlink($container->getHome().'core/temp/setup_componentsinitialized');
        @unlink($container->getHome().'core/temp/setup_passwordset');
        @unlink($container->getHome().'core/temp/setup_innomatichostset');
        @unlink($container->getHome().'core/temp/setup_countryset');
        @unlink($container->getHome().'core/temp/setup_languageset');
//        @unlink($container->getHome().'core/temp/setup_appcentralset');
        @unlink($container->getHome().'core/temp/setup_editionset');
        @unlink($container->getHome().'core/temp/setup_cleanedup');
        @unlink($container->getHome().'core/temp/setup_setupfinished');
        @unlink($container->getHome().'core/temp/setup_done');

        return true;
    }

    public static function remove_setup_lock_file()
    {
        $container = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');

        if (file_exists($container->getHome().'core/temp/setup_lock')) {
            return @unlink($container->getHome().'core/temp/setup_lock');
        }

        return false;
    }

    public static function check_log(&$container)
    {
        $innomatic = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');

        if (!file_exists($innomatic->getHome().'core/log/innomatic.log')) {
            return;
        }
        $log_content = file_get_contents($innomatic->getHome().'core/log/innomatic.log');

        $container->addChild(new WuiHorizBar('loghr'));
        $container->addChild(new WuiText("rootlog", array('disp' => 'action', "readonly" => "true", "value" => \Innomatic\Wui\Wui::utf8_entities($log_content), "rows" => "20", "cols" => "80")), 0, 1);

        @unlink($innomatic->getHome().'core/log/innomatic.log');
    }

    public static function recursive_copy($source, $target)
    {
        if (is_dir($source)) {
            @mkdir($target);

            $d = dir($source);

            while (false !== ($entry = $d->read())) {
                if ($entry == '.' || $entry == '..' || $entry == 'temp') {
                    continue;
                }

                $Entry = $source . '/' . $entry;
                if (is_dir($Entry)) {
                    InnomaticSetup::recursive_copy($Entry, $target . '/' . $entry);
                    continue;
                }
                copy($Entry, $target . '/' . $entry);
            }

            $d->close();
        } else {
            copy($source, $target);
        }
    }
}
