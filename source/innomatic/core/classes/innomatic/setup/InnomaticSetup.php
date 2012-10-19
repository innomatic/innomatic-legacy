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

require_once('innomatic/core/InnomaticContainer.php');

if (InnomaticContainer::instance('innomaticcontainer')->getState() != InnomaticContainer::STATE_SETUP) {
    return;
}
require_once('innomatic/dataaccess/DataAccessFactory.php');
require_once('innomatic/dataaccess/DataAccessXmlTable.php');
//require_once('innomatic/db.DataAccessXmlTableParser');
require_once('innomatic/application/ApplicationDependencies.php');
require_once('innomatic/application/ApplicationSettings.php');
require_once('innomatic/application/ApplicationComponentRegister.php');
require_once('innomatic/application/ApplicationComponent.php');
require_once('innomatic/application/Application.php');

class InnomaticSetup {
    public static function setup_by_config_file($configFile = '', $echo = false) {
    $successString = "[  \033[1;32mOK\033[0;39m  ]\n";
    $failureString = "[\033[1;31mFAILED\033[0;39m]\n";

    require_once('innomatic/config/ConfigFile.php');

    if (strlen($configFile) and file_exists($configFile)) {
        $configFileName = $configFile;
    }
    else $configFileName = InnomaticContainer::instance('innomaticcontainer')->getHome().'core/conf/kickstart.ini';

    if (!file_exists($configFileName)) {
        if ($echo) echo $failureString;
        return false;
    }
    $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();

    $cfg = @parse_ini_file($configFileName);
    if ($echo) echo str_pad('System check: ', 60);
    if (!InnomaticSetup::checksystem('', $log)) {
    if ($echo) echo $failureString;
    return false;
    }

    if ($echo) echo $successString.str_pad('Files installation: ', 60);

    if (!InnomaticSetup::setedition(array('edition' => $cfg['PlatformEdition']))) {
    if ($echo) echo $failureString;
    return false;
    }

    if ($echo) echo $successString.str_pad('Edition setting: ', 60);

    if (!InnomaticSetup::installfiles('', $log)) {
    if ($echo) echo $failureString;
    return false;
    }

    if ($echo) echo $successString.str_pad('Database drivers creation: ', 60);

    if (!InnomaticSetup::dataaccessdrivers('', $log))
    {
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

    if (!InnomaticSetup::createdb($dbArgs, $log))
    {
    if ($echo) echo $failureString;
    return false;
    }

    if ($echo) echo $successString.str_pad('Components initialization (may take some time): ', 60);

    require_once('innomatic/util/Registry.php');
    $reg = Registry::instance();
    $dbArgs['dblog'] = InnomaticContainer::instance('innomaticcontainer')->getHome().'core/log/innomatic_root_db.log';

    if (!InnomaticSetup::initializecomponents($dbArgs, $log))
    {
    if ($echo) echo $failureString;
    return false;
    }

    if ($echo) echo $successString.str_pad('Root password: ', 60);

    $pwdArgs = $dbArgs;
    $pwdArgs['passworda'] = $cfg['RootPassword'];
    $pwdArgs['passwordb'] = $cfg['RootPassword'];

    if (!InnomaticSetup::setpassword($pwdArgs, $log))
    {
    if ($echo) echo $failureString;
    return false;
    }

    if ($echo) echo $successString.str_pad('Network settings: ', 60);

    $hostArgs['innomatichost'] = $cfg['PlatformName'];
    $hostArgs['innomaticgroup'] = $cfg['PlatformGroup'];

    if (!InnomaticSetup::setinnomatichost($hostArgs, $log))
    {            if ($echo) echo $failureString;
    return false;
    }

    if ($echo) echo $successString.str_pad('Country setting: ', 60);

    $country_args['country'] = $cfg['RootCountry'];

    if (!InnomaticSetup::setcountry($country_args, $log))
    {
    if ($echo) echo $failureString;
    return false;
    }

    if ($echo) echo $successString.str_pad('Language setting: ', 60);

    $lang_args['language'] = $cfg['RootLanguage'];

    if (!InnomaticSetup::setlanguage($lang_args, $log))
    {
    if ($echo) echo $failureString;
    return false;
    }

    if ($echo) echo $successString.str_pad('Clean up: ', 60);

    if (!InnomaticSetup::cleanup('', $log))
    {            if ($echo) echo $failureString;
    return false;
    }

    if ($echo) echo $successString.str_pad('Setup finish: ', 60);

    if (!InnomaticSetup::finish('', $log))
    {
    if ($echo) echo $failureString;
    return false;
    }

    if ($echo) echo $successString.str_pad('Remotion of setup phases locks: ', 60);

    if (!InnomaticSetup::check_lock_files())
    {
    if ($echo) echo $failureString;
    return false;
    }

    if ($echo) echo $successString.str_pad('Remotion of setup lock: ', 60);

    InnomaticSetup::remove_lock_files();
    if (!InnomaticSetup::remove_setup_lock_file())
    {
    if ($echo) echo $failureString;
    return false;
    }

    echo $successString;
    return true;

    return $result;
    }

    public static function checksystem($eventData = '', $log = '') {
        @touch(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_systemchecked', time());
        if (file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_checkingsystem')) @unlink(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_checkingsystem');

        return TRUE;
    }

    public static function installfiles($eventData = '', $log = '') {
        InnomaticSetup::recursive_copy(InnomaticContainer::instance('innomaticcontainer')->getHome(), InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/innomatic');
        
        @touch(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_filesinstalled', time());
        if (file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_installingfiles')) @unlink(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_installingfiles');

        return true;
    }

    public static function setedition($eventData, $log = '')
    {
        @touch(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_editionset', time());
        if (file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_settingedition')) @unlink(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_settingedition');
        require_once('innomatic/config/ConfigFile.php');
        $innomaticcfg = new ConfigFile(InnomaticContainer::instance('innomaticcontainer')->getConfigurationFile());
        $innomaticcfg->setValue('PlatformEdition', $eventData['edition']);
        // !!! $page->add(new HTMLScript('JavaScript1.2', 'parent.frames.menu.location.reload()', ''));

        return TRUE;
    }

    public static function dataaccessdrivers($eventData = '', $log = '')
    {
        $daf = new DataAccessFactory();
        $daf->addDriver('mysql', 'MySQL 3.22+');
        $daf->addDriver('pgsql', 'PostgreSQL 7.0+');

        @touch(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_dataaccessdriverscreated', time());
        if (file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_creatingdataaccessdrivers')) @unlink(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_creatingdataaccessdrivers');

        return TRUE;
    }

    public static function createdb($eventData = '', $log = '')
    {
        $result = FALSE;
        require_once('innomatic/util/Registry.php');
        $reg = Registry::instance();
        $innomatic = InnomaticContainer::instance('innomaticcontainer');        
        
        require_once('innomatic/dataaccess/DataAccessSourceName.php');
        $dasn_string = $eventData['dbtype'].'://'.
            $eventData['dbuser'].':'.
            $eventData['dbpass'].'@'.
            $eventData['dbhost'].':'.
            $eventData['dbport'].'/'.
            $eventData['dbname'].'?'.
            'logfile='.$innomatic->getHome().'core/log/innomatic_root_db.log';
        $tmpdb = DataAccessFactory::getDataAccess(new DataAccessSourceName($dasn_string));

        if ($tmpdb->Connect())
        {
            $tmpdb->DropDB($eventData);
            $tmpdb->Close();
        }

        if ($tmpdb->CreateDB($eventData))
        {
            if ($tmpdb->Connect())
            {
                // Tables creation
                //
                $xmldb = new DataAccessXmlTable($tmpdb, DataAccessXmlTable::SQL_CREATE);
                if ($xmldb->load_DefFile(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/db/innomatic_root.xml'))
                {
                    if ($tmpdb->execute($xmldb->getSQL()))
                    {
                        // Database configuration file creation
                        //
                        $fh = @fopen(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/conf/innomatic.ini', 'a');
                        if ($fh)
                        {
                            fputs($fh, 'RootDatabaseType = '.$eventData['dbtype']."\n");
                            fputs($fh, 'RootDatabaseName = '.$eventData['dbname']."\n");
                            fputs($fh, 'RootDatabaseHost = '.$eventData['dbhost']."\n");
                            fputs($fh, 'RootDatabasePort = '.$eventData['dbport']."\n");
                            fputs($fh, 'RootDatabaseUser = '.$eventData['dbuser']."\n");
                            fputs($fh, 'RootDatabasePassword = '.$eventData['dbpass']."\n");
                            fputs($fh, 'RootDatabaseDebug = 0'."\n");
                            fclose($fh);

                            $result = TRUE;

                            @touch(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_dbcreated', time());
                            if (file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_creatingdb')) @unlink(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_creatingdb');
                            // !!! $page->add(new HTMLScript('JavaScript1.2', 'parent.frames.menu.location.reload()', ''));
                        }
                        else $log->logevent('innomatic.root.main_php',
                                             'Unable to create root database configuration file during initialization', Logger::ERROR);
                    }
                    else $log->logevent('innomatic.root.main_php',
                                         'Unable to create root database tables during initialization', Logger::ERROR);
                }
                else $log->logevent('innomatic.root.main_php',
                                     'Unable to open Innomatic structure file during initialization', Logger::ERROR);
            }
            else $log->logevent('innomatic.root.main_php',
                                 'Unable to connect to root database during initialization', Logger::ERROR);
        }
        else $log->logevent('innomatic.root.main_php',
                             'Unable to create root database during initialization: '.$tmpdb->getLastError(), Logger::ERROR);

        return $result;
    }

    public static function initializecomponents($eventData = '', $log = '')
    {
        $result = false;

        if (isset($eventData['dbname']) and strlen($eventData['dbname'])) {
            $args = $eventData;
        } else {
            $args['dbname'] = InnomaticContainer::instance('innomaticcontainer')->getConfig()->value('RootDatabaseName');
            $args['dbhost'] = InnomaticContainer::instance('innomaticcontainer')->getConfig()->value('RootDatabaseHost');
            $args['dbport'] = InnomaticContainer::instance('innomaticcontainer')->getConfig()->value('RootDatabasePort');
            $args['dbuser'] = InnomaticContainer::instance('innomaticcontainer')->getConfig()->value('RootDatabaseUser');
            $args['dbpass'] = InnomaticContainer::instance('innomaticcontainer')->getConfig()->value('RootDatabasePassword');
            $args['dbtype'] = InnomaticContainer::instance('innomaticcontainer')->getConfig()->value('RootDatabaseType');
            $args['dblog']  = InnomaticContainer::instance('innomaticcontainer')->getHome().'core/log/innomatic_root_db.log';
        }
                            require_once('innomatic/dataaccess/DataAccessSourceName.php');
                    $dasn_string = $args['dbtype'].'://'.
                        $args['dbuser'].':'.
                        $args['dbpass'].'@'.
                        $args['dbhost'].':'.
                        $args['dbport'].'/'.
                        $args['dbname'].'?'.
                        'logfile='.$args['dblog'];
        $tmpdb = DataAccessFactory::getDataAccess(new DataAccessSourceName($dasn_string));
        if ($tmpdb->Connect())
        {
            // Components initialization
            //
            $innomaticmod = new Application($tmpdb);
            if ($innomaticmod->setup(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/innomatic/'))
            {
                $modreg = new ApplicationComponentRegister($tmpdb);
                $modreg->registerComponent('innomatic', 'configurationfile', 'innomatic.ini', '', ApplicationComponent::OVERRIDE_NONE);
                $modreg->registerComponent('innomatic', 'configurationfile', 'dataaccessdrivers.ini', '', ApplicationComponent::OVERRIDE_NONE);

                $result = TRUE;

                @touch(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_componentsinitialized', time());
                if (file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_initializingcomponents')) @unlink(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_initializingcomponents');
            }
            else $log->logevent('innomatic.root.main_php',
                                'Unable to setup Innomatic during initialization', Logger::ERROR);
        }
        else $log->logevent('innomatic.root.main_php',
                            'Unable to connect to root database during initialization', Logger::ERROR);

        return $result;
    }

    public static function setpassword($eventData, $log = '')
    {
        $result = FALSE;
        
        // Password setting
        //
        if (strlen($eventData['passworda']) and ($eventData['passworda'] == $eventData['passwordb']))
        {
            // Creates Innomatic root password file
            //
            $fh = @fopen(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/conf/rootpasswd.ini', 'w');
            if ($fh)
            {
                fputs($fh, md5($eventData['passworda']));
                fclose($fh);
                if (@touch(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_passwordset', time()))
                {
                    if (isset($eventData['dbname']) and strlen($eventData['dbname']))
                    {
                        $args = $eventData;
                    }
                    else
                    {
                        $args['dbname'] = InnomaticContainer::instance('innomaticcontainer')->getConfig()->value('RootDatabaseName');
                        $args['dbhost'] = InnomaticContainer::instance('innomaticcontainer')->getConfig()->value('RootDatabaseHost');
                        $args['dbport'] = InnomaticContainer::instance('innomaticcontainer')->getConfig()->value('RootDatabasePort');
                        $args['dbuser'] = InnomaticContainer::instance('innomaticcontainer')->getConfig()->value('RootDatabaseUser');
                        $args['dbpass'] = InnomaticContainer::instance('innomaticcontainer')->getConfig()->value('RootDatabasePassword');
                        $args['dbtype'] = InnomaticContainer::instance('innomaticcontainer')->getConfig()->value('RootDatabaseType');
                        $args['dblog']  = InnomaticContainer::instance('innomaticcontainer')->getHome().'core/log/innomatic_root_db.log';
                    }

                    require_once('innomatic/dataaccess/DataAccessSourceName.php');
                    $dasn_string = $args['dbtype'].'://'.
                        $args['dbuser'].':'.
                        $args['dbpass'].'@'.
                        $args['dbhost'].':'.
                        $args['dbport'].'/'.
                        $args['dbname'].'?'.
                        'logfile='.$args['dblog'];
                    $root_db = DataAccessFactory::getDataAccess(new DataAccessSourceName($dasn_string));
                    
                    if ($root_db->connect())
                    {
                        $modreg = new ApplicationComponentRegister($root_db);
                        $modreg->registerComponent('innomatic', 'configurationfile', 'rootpasswd.ini', '', ApplicationComponent::OVERRIDE_NONE);

                        $result = TRUE;

                        if (file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_settingpassword')) @unlink(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_settingpassword');
                    }
                    else $log->logevent('innomatic.root.main_php',
                                        'Unable to connect to Innomatic database during initialization', Logger::ERROR);
                }
                else $log->logevent('innomatic.root.main_php',
                                    'Unable to create .passwordset lock file during initialization', Logger::ERROR);
            }
            else $log->logevent('innomatic.root.main_php',
                                'Unable to create root password file', Logger::ERROR);
        }

        return $result;
    }

    public static function setinnomatichost($eventData, $log = '')
    {
        @touch(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_innomatichostset', time());
        if (file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_settinginnomatichost')) @unlink(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_settinginnomatichost');

        require_once('innomatic/config/ConfigFile.php');
        $innomaticcfg = new ConfigFile(InnomaticContainer::instance('innomaticcontainer')->getConfigurationFile());
        $innomaticcfg->setValue('PlatformName', $eventData['innomatichost']);
        $innomaticcfg->setValue('PlatformGroup', $eventData['innomaticgroup']);

        return TRUE;
    }

    public static function setcountry($eventData, $log = '')
    {
        @touch(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_countryset', time());
        if (file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_settingcountry')) @unlink(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_settingcountry');
        require_once('innomatic/config/ConfigFile.php');
        $innomaticcfg = new ConfigFile(InnomaticContainer::instance('innomaticcontainer')->getConfigurationFile());
        $innomaticcfg->setValue('RootCountry', $eventData['country']);

        return TRUE;
    }

    public static function setlanguage($eventData, $log = '')
    {
        @touch(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_languageset', time());
        if (file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_settinglanguage')) @unlink(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_settinglanguage');
        require_once('innomatic/config/ConfigFile.php');
        $innomaticcfg = new ConfigFile(InnomaticContainer::instance('innomaticcontainer')->getConfigurationFile());
        $innomaticcfg->setValue('RootLanguage', $eventData['language']);

        return TRUE;
    }
/*
    public static function appcentral($eventData, $log = '')
    {
        if (isset($eventData['appcentral']) and $eventData['appcentral'] == 'on')
        {
            require_once('innomatic/webservices/xmlrpc/XmlRpc_Client.php');
            
            $xmlrpc_client = new XmlRpc_Client(
                '/innomatic/webservices/',
                'appcentral.innomatic.org',
                80
               );
            
            $xmlrpc_message = new XmlRpcMsg(
                'appcentral-server.retrieve_appcentral_client'
               );

            $xmlrpc_resp = $xmlrpc_client->Send($xmlrpc_message);

            if ($xmlrpc_resp)
            {
                if (!$xmlrpc_resp->FaultCode())
                {
                    $xv = $xmlrpc_resp->Value();

                    $tmp_filename = InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/appinst/appcentral-client.tgz';

                    $fh = fopen($tmp_filename, 'wb');
                    if ($fh)
                    {
                        require_once('innomatic/application/Application.php');

                        fputs($fh, $xv->scalarVal());
                        fclose($fh);

                        unset($xv);
                        unset($xmlrpc_resp);

                        $args['dbname'] = InnomaticContainer::instance('innomaticcontainer')->getConfig()->value('RootDatabaseName');
                        $args['dbhost'] = InnomaticContainer::instance('innomaticcontainer')->getConfig()->value('RootDatabaseHost');
                        $args['dbport'] = InnomaticContainer::instance('innomaticcontainer')->getConfig()->value('RootDatabasePort');
                        $args['dbuser'] = InnomaticContainer::instance('innomaticcontainer')->getConfig()->value('RootDatabaseUser');
                        $args['dbpass'] = InnomaticContainer::instance('innomaticcontainer')->getConfig()->value('RootDatabasePassword');
                        $args['dbtype'] = InnomaticContainer::instance('innomaticcontainer')->getConfig()->value('RootDatabaseType');
                        $args['dblog']  = InnomaticContainer::instance('innomaticcontainer')->getHome().'core/log/innomatic_root_db.log';

                    require_once('innomatic/dataaccess/DataAccessSourceName.php');
                    $dasn_string = $args['dbtype'].'://'.
                        $args['dbuser'].':'.
                        $args['dbpass'].'@'.
                        $args['dbhost'].':'.
                        $args['dbport'].'/'.
                        $args['dbname'].'?'.
                        'logfile='.$args['dblog'];
                        $root_db = DataAccessFactory::getDataAccess(new DataAccessSourceName($dasn_string));
                        if ($root_db->connect())
                        {
                            $tmp_application = new Application($root_db, '');
                            $tmp_application->Install($tmp_filename);
                        
                            @touch(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_appcentralset', time());
                            if (file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_settingappcentral')) @unlink(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_settingappcentral');
                        }
                    }
                }
            }
        }
        else
        {
            @touch(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_appcentralset', time());
            if (file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_settingappcentral')) @unlink(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_settingappcentral');
        }
    }
    */
    
    public static function cleanup($eventData = '', $log = '') {
        $fh = @fopen(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/conf/innomatic.ini', 'a');
        if ($fh) {
            $req_url = InnomaticContainer::instance('innomaticcontainer')->getExternalBaseUrl();
            fputs($fh, 'InnomaticBaseUrl = '.$req_url."\n");
            fclose($fh);
        }
        require_once('innomatic/io/filesystem/DirectoryUtils.php');
        DirectoryUtils::unlinkTree(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/innomatic/');
        DirectoryUtils::unlinkTree(InnomaticContainer::instance('innomaticcontainer')->getHome().'setup');
        //@unlink(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/conf/kickstart.ini');
        //@unlink(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/bin/kickstart.php');
        
        @touch(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_cleanedup', time());
        if (file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_cleaningup')) {
            @unlink(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_cleaningup');
        }
        @touch(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_setupfinished', time());

        return TRUE;
    }

    public static function finish($eventData = '', $log = '')
    {
        //@unlink(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/classes/innomatic/setup/InnomaticSetup.php');
        @touch(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_done', time());
        $log->logEvent(
            'Innomatic',
            'Innomatic setup has been completed - Operating',
            Logger::NOTICE
        );

        return true;
    }

    public static function check_lock_files() {
        if (
            file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_systemchecked') and
            file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_filesinstalled') and
            file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_dataaccessdriverscreated') and
            file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_dbcreated') and
            file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_componentsinitialized') and
            file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_passwordset') and
            file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_innomatichostset') and
            file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_countryset') and
            file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_languageset') and
//            file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_appcentralset') and
            file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_editionset') and
            file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_cleanedup') and
            file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_done')
          )
      {
          return true;
      } else {
        return false;
      }
    }

    public static function remove_lock_files() {
        @unlink(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_systemchecked');
        @unlink(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_filesinstalled');
        @unlink(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_dataaccessdriverscreated');
        @unlink(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_dbcreated');
        @unlink(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_componentsinitialized');
        @unlink(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_passwordset');
        @unlink(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_innomatichostset');
        @unlink(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_countryset');
        @unlink(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_languageset');
//        @unlink(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_appcentralset');
        @unlink(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_editionset');
        @unlink(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_cleanedup');
        @unlink(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_setupfinished');
        @unlink(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_done');

        return true;
    }

    public static function remove_setup_lock_file() {
        if (file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_lock')) {
            return @unlink(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/setup_lock');
        }

        return false;
    }

    public static function check_log(&$container)
    {
        if (!file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/log/innomatic.log')) {
            return;
        }
        $log_content = file_get_contents(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/log/innomatic.log');

        $container->addChild(new WuiHorizBar('loghr'));
        $container->addChild(new WuiText("rootlog", array('disp' => 'action', "readonly" => "true", "value" => Wui::utf8_entities($log_content), "rows" => "20", "cols" => "80")), 0, 1);

        @unlink(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/log/innomatic.log');
    }
    
    public static function recursive_copy($source, $target) {
        if (is_dir($source)) {
            @mkdir($target);
            
            $d = dir($source);
            
            while (FALSE !== ($entry = $d->read())) 
            {
                if ($entry == '.' || $entry == '..' || $entry == 'temp')
                {
                    continue;
                }
                
                $Entry = $source . '/' . $entry;            
                if (is_dir($Entry))
                {
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
