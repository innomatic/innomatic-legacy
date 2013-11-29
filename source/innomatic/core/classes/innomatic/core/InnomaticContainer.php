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

namespace Innomatic\Core;

require_once('innomatic/webapp/WebAppContainer.php');

/**
 * Innomatic base container class.
 *
 * This class takes care of bootstrapping and shutting down the whole container,
 * the root, the domains and the Web Services interface.
 *
 * It holds the container current state, mode and interface.
 *
 * This class provided a custom PHP error handler for the container
 * applications.
 *
 * @copyright  1999-2012 Innoteam Srl
 * @license    http://www.innomatic.org/license/   BSD License
 * @version    Release: @package_version@
 * @link       http://www.innomatic.org
 * @since      Class available since Release 5.0
 * @package    Core
 */
class InnomaticContainer extends \Innomatic\Util\Singleton
{
    private $_home;
    /**
     * Complete path of the Innomatic configuration file.
     * @var string
     */
    private $_configurationFile;
    /**
     *
     * @var boolean
     */
    private $_bootstrapped = false;
    private $_rootStarted = false;
    private $_domainStarted = false;
    private $_pid;
    private $_state;
    private $_environment;
    private $_mode = InnomaticContainer::MODE_BASE;
    private $_interface = InnomaticContainer::INTERFACE_UNKNOWN;
    private $_edition = InnomaticContainer::EDITION_SAAS;
    private $_rootDb;
    /**
     * Root language
     *
     * @var string
     */
    private $_language;
    /**
     * Root country.
     *
     * @var string
     */
    private $_country;
    private $_config;
    private $_logger;
    /**
     * Stores the result object of a maintenance routine.
     *
     * @var mixed
     */
    private $_maintenanceResult;
    private $_loadTimer;
    private $_dbLoadTimer;
    private $_platformName;
    private $_platformGroup;
    private $_lockOverride = false;
    private $_baseUrl;
    private $_externalBaseUrl;

    private $_currentWebServicesProfile;
    private $_currentWebServicesUser;
    private $_currentWebServicesMethods = array();

    private $_currentDomain;
    private $_currentUser;

    // Innomatic platform/instance state

    const STATE_SETUP = 1;
    const STATE_DEBUG = 3;
    const STATE_PRODUCTION = 4;
    const STATE_UPGRADE = 5;
    const STATE_MAINTENANCE = 6;

    // Environment type

    const ENVIRONMENT_DEVELOPMENT = 1; // Local development or shared sandbox
    const ENVIRONMENT_INTEGRATION = 2; // Integration e.g. continuous integration
    const ENVIRONMENT_STAGING = 3; // Multiple UAT, QA, Demo, Training/Demo environments
    const ENVIRONMENT_PRODUCTION = 4; // Live

    // Output interface types

    const INTERFACE_UNKNOWN = 1;
    const INTERFACE_CONSOLE = 2;
    const INTERFACE_WEB = 3;
    const INTERFACE_WEBSERVICES = 4;
    const INTERFACE_GUI = 5;
    const INTERFACE_EXTERNAL = 6;

    // Mode types

    const MODE_BASE = 1;
    const MODE_ROOT = 2;
    const MODE_DOMAIN = 3;

    // Edition types

    const EDITION_SAAS = 1;
    const EDITION_ENTERPRISE = 2;

    // Deprecated

    const EDITION_ASP = 1;

    // Password result codes
    const SETROOTPASSWORD_NEW_PASSWORD_IS_EMPTY = -1;
    const SETROOTPASSWORD_UNABLE_TO_WRITE_NEW_PASSWORD = -2;
    const SETROOTPASSWORD_OLD_PASSWORD_IS_WRONG = -3;

    /**
     * Bootstraps the Innomatic container.
     *
     * @param string $home Complete path of the directory containing the
     * Innomatic webapp.
     * @param string $configuration Complete path of the Innomatic
     * configuration file.
     */
    public function bootstrap($home, $configuration)
    {
        if ($this->_bootstrapped) {
            return;
        }
        $this->_home = $home;

        // Reads the configuration
        $this->_configurationFile = $configuration;
        require_once('innomatic/core/InnomaticSettings.php');
        $this->_config = new InnomaticSettings($configuration);

        // *********************************************************************
        // PHP environment
        // *********************************************************************

        // PHP
        $timelimit = $this->_config->Value('PHPExecutionTimeLimit');
        if (!strlen($timelimit)) {
            $timelimit = 0;
        }
        set_time_limit($timelimit);
        ignore_user_abort(TRUE);
        //set_magic_quotes_runtime(0);

        // Adds global override classes folder to the include path.
        set_include_path(
            $this->_home . 'core/overrides/classes/'
            . PATH_SEPARATOR . get_include_path()
        );

        // *********************************************************************
        // Innomatic state, environment, mode, interface and edition
        // *********************************************************************

        // Waits until system is in upgrade phase
        if ($this->_lockOverride == false) {
            while (file_exists(
                $this->_home . 'core/temp/upgrading_system_lock'
            )) {
                $this->_state = InnomaticContainer::STATE_UPGRADE;
                clearstatcache();
                sleep(1);
            }
        }
        // Checks if system is in setup phase and sets the state
        if (file_exists($this->_home . 'core/temp/setup_lock')) {
            $this->_state = InnomaticContainer::STATE_SETUP;
            if (extension_loaded('APD')) {
                apd_set_session_trace(35);
            }
        } else {
            switch ($this->_config->Value('PlatformState')) {
                case 'debug':
                    $this->_state = InnomaticContainer::STATE_DEBUG;
                    if (extension_loaded('APD')) {
                        apd_set_session_trace(35);
                    }
                    break;
                case 'production':
                    $this->_state = InnomaticContainer::STATE_PRODUCTION;
                    break;
                default:
                    $this->_state = InnomaticContainer::STATE_PRODUCTION;
            }
        }

        // Environment
        switch ($this->_config->Value('PlatformEnvironment')) {
            case 'development':
                $this->_environment = InnomaticContainer::ENVIRONMENT_DEVELOPMENT;
                break;
            case 'integration':
                $this->_environment = InnomaticContainer::ENVIRONMENT_INTEGRATION;
                break;
            case 'staging':
                $this->_environment = InnomaticContainer::ENVIRONMENT_STAGING;
                break;
            case 'production':
                $this->_environment = InnomaticContainer::ENVIRONMENT_PRODUCTION;
                break;
            default:
                $this->_environment = InnomaticContainer::ENVIRONMENT_PRODUCTION;
        }

        // Interface
        //$this->interface = InnomaticContainer::INTERFACE_UNKNOWN;
        // Mode
        //$this->mode = InnomaticContainer::MODE_ROOT;
        // Edition
        if ($this->_config->Value('PlatformEdition') == 'enterprise') {
            $this->_edition = InnomaticContainer::EDITION_ENTERPRISE;
        }

        // *********************************************************************
        // Pid and shutdown function
        // *********************************************************************

        if ($this->_state != InnomaticContainer::STATE_SETUP) {
            $this->_pid = md5(microtime());
            if (!file_exists($this->_home . 'core/temp/pids/')) {
                @mkdir($this->_home . 'core/temp/pids/');
            }
            touch($this->_home . 'core/temp/pids/' . $this->_pid, time());
            register_shutdown_function(array($this, 'shutdown'));
        }

        // *********************************************************************
        // Innomatic platform name
        // *********************************************************************

        $this->_platformName = $this->_config->Value('PlatformName');
        $this->_platformGroup = $this->_config->Value('PlatformGroup');

        // *********************************************************************
        // Innomatic error handler
        // *********************************************************************

        set_error_handler(array($this, 'errorHandler'));

        // *********************************************************************
        // Innomatic root
        // *********************************************************************

        $this->_country = $this->_config->Value('RootCountry');
        $this->_language = $this->_config->Value('RootLanguage');

        require_once('innomatic/dataaccess/DataAccessFactory.php');

        if ($this->_state != InnomaticContainer::STATE_SETUP) {
            // Innomatic central database
            //
            require_once('innomatic/dataaccess/DataAccessSourceName.php');
            $dasnString = $this->_config->Value('RootDatabaseType') . '://'
            . $this->_config->Value('RootDatabaseUser') . ':'
            . $this->_config->Value('RootDatabasePassword') . '@'
            . $this->_config->Value('RootDatabaseHost') . ':'
            . $this->_config->Value('RootDatabasePort') . '/'
            . $this->_config->Value('RootDatabaseName') . '?'
            . 'logfile='
            . InnomaticContainer::instance('innomaticcontainer')->getHome()
            . 'core/log/innomatic_root_db.log';
            $this->_rootDb = DataAccessFactory::getDataAccess(
                new DataAccessSourceName($dasnString)
            );
            if (!$this->_rootDb->Connect()) {
                $this->abort('Database not connected');
            }
        }

        // *********************************************************************
        // Run time state and interface defined data
        // *********************************************************************

        // Debugger
        if ($this->_state == InnomaticContainer::STATE_DEBUG) {
            require_once('innomatic/debug/LoadTime.php');
            $this->_loadTimer = new LoadTime(
                LoadTime::LOADTIME_MODE_CONTINUOUS
            );
            $this->_loadTimer->Mark('start');
            $this->_dbLoadTimer = new LoadTime(
                LoadTime::LOADTIME_MODE_STARTSTOP
            );
        }

        // Security
        $securityReportsInterval = $this->_config->Value(
            'SecurityReportsInterval'
        );
        if ($securityReportsInterval > 0) {
            $lastSecurityReport = $this->_config->Value(
                'SecurityLastReportTime'
            );
            if (
                !$lastSecurityReport
                or $lastSecurityReport < (time() - (
                    $securityReportsInterval * 3600 * 24
                ))
            ) {
                require_once('innomatic/security/SecurityManager.php');
                $innomaticSecurity = new SecurityManager();
                $innomaticSecurity->SendReport();
                unset($innomaticSecurity);
            }
        }
        unset($securityReportsInterval);

        // Maintenance
        $maintenanceInterval = $this->_config->Value('MaintenanceInterval');
        if (
            $this->_state != InnomaticContainer::STATE_MAINTENANCE
            and $maintenanceInterval > 0
        ) {
            $lastMaintenance = $this->_config->Value(
                'MaintenanceLastExecutionTime'
            );
            if (
                !$lastMaintenance
                or $lastMaintenance < (time() - (
                    $maintenanceInterval * 3600 * 24
                ))
            ) {
                require_once('innomatic/maintenance/MaintenanceHandler.php');
                $innomaticMaintenance = new MaintenanceHandler();
                $innomaticMaintenance->DoMaintenance();
                $innomaticMaintenance->SendReport();
                unset($innomaticMaintenance);
            }
        }
        unset($maintenanceInterval);

        // *********************************************************************
        // Auto exec routines
        // *********************************************************************

        // Application reupdate check
        if (file_exists($this->_home . 'core/temp/appinst/reupdate')) {
            require_once('innomatic/application/Application.php');
            $tmpmod = new Application($this->_rootDb, '');
            $tmpmod->Install($this->_home . 'core/temp/appinst/reupdate');
            clearstatcache();
            if (file_exists($this->_home . 'core/temp/appinst/reupdate')) {
                unlink($this->_home . 'core/temp/appinst/reupdate');
            }
        }

        // Startup hook
        if ($this->_state != InnomaticContainer::STATE_SETUP) {
            require_once('innomatic/process/Hook.php');
            $hook = new Hook($this->_rootDb, 'innomatic', 'instance');
            $null = '';
            switch ($hook->CallHooks('startup', $null, '')) {
                case Hook::RESULT_ABORT :
                    $this->abort('Bootstrap aborted');
                    break;
            }
        }

        // Bootstrap end
        $this->_bootstrapped = true;
    }

    /**
     * Starts the root mode.
     * @param string $userId Root username (currently not used, defaults to
     * "root").
     */
    public function startRoot($userId = 'root')
    {
        $this->setMode(InnomaticContainer::MODE_ROOT);

        if ($this->_rootStarted) {
            return;
        }

        $this->_rootStarted = true;
    }

    public function startDomain($domainId, $userId = '')
    {
        $result = false;
        $this->setMode(InnomaticContainer::MODE_DOMAIN);

        if (is_object($this->_currentDomain) or $this->_domainStarted) {
            // A domain has been already started
            return false;
        }

        require_once('innomatic/domain/Domain.php');
        $this->_currentDomain = new Domain($this->_rootDb, $domainId, null);

        if ($this->_currentDomain->isValid()) {
            // Check if domain is active
            //
            if (
                $this->getInterface() != InnomaticContainer::INTERFACE_WEB
                and $this->_currentDomain->domaindata['domainactive']
                == $this->_rootDb->fmtfalse
            ) {
                $this->abort('Domain disabled');
            }

            if (!$this->_currentDomain->getDataAccess()->isConnected()) {
                $adloc = new LocaleCatalog(
                    'innomatic::authentication',
                    $this->_language
                );
                InnomaticContainer::instance('innomaticcontainer')->abort(
                    $adloc->getStr('nodb')
                );
            }

            // Adds override classes folder to the include path.
            set_include_path(
                $this->_home . 'core/domains/'
                . $this->_currentDomain->getDomainId()
                . '/overrides/classes/'
                . PATH_SEPARATOR . get_include_path()
            );

            // User
            //
            // TODO check in Enterprise edition if the admin@domainid part is ok
            // $admin_username = 'admin'
            // .(InnomaticContainer::instance(
            //      'innomaticcontainer'
            // )->getEdition() == InnomaticContainer::EDITION_SAAS ? '@'.$domain
            // : '');
            require_once('innomatic/domain/user/User.php');
            $this->_currentUser = new User(
                $this->_currentDomain->domainserial,
                User::getUserIdByUsername(
                    strlen($userId) ? $userId : 'admin@' . $domainId
                )
            );

            $result = true;
        }
        $this->_domainStarted = $result;
        return $result;
    }

    public function stopDomain()
    {
        if ($this->_domainStarted) {
            if (InnomaticContainer::instance('innomaticcontainer')->getEdition() == InnomaticContainer::EDITION_SAAS) {
                $this->_currentDomain->getDataAccess()->close();
            }
            // TODO implement

            // Removes override classes folder from the include path
            set_include_path(
                str_replace(
                    $this->_home . 'core/domains/'
                    . $this->_currentDomain->getDomainId()
                    . '/overrides/classes/' . PATH_SEPARATOR,
                    '', get_include_path()
                )
            );

            $this->_domainStarted = false;
            $this->_currentDomain = null;
            $this->setMode(InnomaticContainer::MODE_ROOT);
        }
    }

    // TODO to be implemented
    public function startWebServices()
    {
    }

    public function startMaintenance()
    {
        $this->setState(InnomaticContainer::STATE_MAINTENANCE);
        $this->setInterface(InnomaticContainer::INTERFACE_CONSOLE);

        require_once('innomatic/maintenance/MaintenanceHandler.php');
        require_once('innomatic/process/Hook.php');

        $hook = new Hook($this->_rootDb, 'innomatic', 'instance');
        $null = null;
        switch ($hook->CallHooks('maintenance', $null, '')) {
            case Hook::RESULT_ABORT:
                InnomaticContainer::instance('innomaticcontainer')->abort(
                    'Maintenance aborted'
                );
                break;
        }

        $innomaticMnt = new MaintenanceHandler();
        $this->_maintenanceResult = $innomaticMnt->DoMaintenance();
        $innomaticMnt->SendReport($this->_maintenanceResult);
    }

    /**
     * Halts Innomatic Container.
     *
     * This must be called by applications in place of exit(), in order to exit
     * in a clean mode.
     *
     * @param string $status
     */
    public function halt($status = '')
    {
        $rootContainer = \Innomatic\Core\RootContainer::instance('rootcontainer');
        $rootContainer->stop();
        exit($status);
    }

    public function shutdown()
    {
        if ($this->_state != InnomaticContainer::STATE_SETUP) {
            require_once('innomatic/process/Hook.php');
            if (is_object($this->_rootDb)) {
                $hook = new Hook($this->_rootDb, 'innomatic', 'instance');
                $null = '';
                switch ($hook->CallHooks('shutdown', $null, '')) {
                    case Hook::RESULT_ABORT:
                        $this->abort('Shutdown aborted');
                        break;
                }
            }
        }

        switch ($this->_state) {
            case InnomaticContainer::STATE_DEBUG:
                if (
                    is_object($this->_loadTimer)
                    and RootContainer::instance('rootcontainer')->isClean()
                    == true
                ) {
                    $this->_loadTimer->Mark('end');

                    $log = $this->getLogger();
                    $log->logEvent(
                        'innomatic',
                        'Profiler total time: '
                        . $this->_loadTimer->getTotalTime(),
                        \Innomatic\Logging\Logger::DEBUG
                    );

                    $fh = @fopen(
                        $this->_home . 'core/temp/pids/' . $this->_pid,
                        'w'
                    );
                    if ($fh) {
                        require_once('innomatic/debug/InnomaticDump.php');
                        $dump = InnomaticDump::instance('innomaticdump');
                        $dump->snapshot();
                        @fwrite($fh, serialize($dump));
                        @fclose($fh);
                    }
                }
                break;
        }

        if (!RootContainer::instance('rootcontainer')->isClean()) {
            if (is_object($this->_loadTimer)) {
                $this->_loadTimer->Mark('end');

                $log = $this->getLogger();
                $log->logEvent(
                    'innomatic',
                    'Profiler total time: '
                    . $this->_loadTimer->getTotalTime(),
                    \Innomatic\Logging\Logger::DEBUG
                );
            }

            $fh = @fopen(
                $this->_home . 'core/temp/pids/' . $this->_pid . '_coredump',
                'w'
            );
            if ($fh) {
                require_once('innomatic/debug/InnomaticDump.php');
                $dump = InnomaticDump::instance('innomaticdump');
                $dump->snapshot();
                @fwrite($fh, serialize($dump));
                @fclose($fh);
            }

        }

        if (
            !RootContainer::instance('rootcontainer')->isClean()
            or (
                $this->_state != InnomaticContainer::STATE_DEBUG
                and file_exists(
                    $this->_home . 'core/temp/pids/' . $this->_pid
                )
            )
        ) {
            @unlink($this->_home . 'core/temp/pids/' . $this->_pid);
        }

        exit();
    }

    public function abort($text, $forceInterface = '')
    {
        require_once('innomatic/wui/Wui.php');
        if (strlen($forceInterface)) {
            $interface = $forceInterface;
        } else {
            $interface = $this->_interface;
        }

        if ($interface == InnomaticContainer::INTERFACE_EXTERNAL) {
            /*
             if (
                 isset(
                     $GLOBALS['gEnv']['runtime']
                    ['external_interface_error_handler'])
                and function_exists(
            $GLOBALS['gEnv']['runtime']['external_interface_error_handler'])) {
             $func = $GLOBALS['gEnv']['runtime']
                ['external_interface_error_handler'];
             $func ($text);
             } else {
             */
            $interface = InnomaticContainer::INTERFACE_WEB;
            $this->_interface = InnomaticContainer::INTERFACE_WEB;
            //}
        }

        switch ($interface) {
            case InnomaticContainer::INTERFACE_GUI :
                // break was intentionally omitted
            case InnomaticContainer::INTERFACE_UNKNOWN :
                // break was intentionally omitted
            case InnomaticContainer::INTERFACE_WEBSERVICES :
                // break was intentionally omitted
            case InnomaticContainer::INTERFACE_EXTERNAL :
                break;
            case InnomaticContainer::INTERFACE_CONSOLE :
                echo "\n".$text."\n";
                break;
            case InnomaticContainer::INTERFACE_WEB :
                $tmpWui = Wui::instance('wui');
                $tmpWui->loadWidget('empty');
                //$tmp_elem = new WuiEmpty('empty');

                $dieImage = Wui::instance('wui')->getTheme()->mStyle['biglogo'];

                ?>
<html>
<head>
<basefont face="Verdana" />
<title>Innomatic</title>
<link rel="stylesheet" type="text/css"
    href="<?php echo Wui::instance('wui')->getTheme()->mStyle['css'];
                ?>">
</head>

<body bgcolor="white">

<table border="0" cellspacing="0" cellpadding="0" align="center"
    width="200" height="100%">
    <tr>
        <td height="50%">

    </tr>
    <tr>
        <td align="center" valign="middle"><a
            href="<?php echo InnomaticContainer::instance(
                'innomaticcontainer'
            )->getExternalBaseUrl();
            ?>"
            target="_top"><img src="<?php echo $dieImage;
                ?>"
            alt="Innomatic" border="0"></a></td>
    </tr>
    <tr>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td align="center"><?php echo $text;
        ?></td>
    </tr>
    <tr>
        <td height="50%">

    </tr>
</table>

</body>
</html>
        <?php break;
        }

        $this->halt();
    }

    public function errorHandler(
        $errorType,
        $errorMessage,
        $errorFile,
        $errorLine,
        $errorContext
    )
    {
        $logError[E_ERROR]['log'] = true;
        $logError[E_ERROR]['die'] = true;

        $logError[E_WARNING]['log'] = false;
        $logError[E_WARNING]['die'] = false;

        $logError[E_PARSE]['log'] = true;
        $logError[E_PARSE]['die'] = false;

        $logError[E_NOTICE]['log'] = false;
        $logError[E_NOTICE]['die'] = false;

        $logError[E_CORE_ERROR]['log'] = true;
        $logError[E_CORE_ERROR]['die'] = true;

        $logError[E_CORE_WARNING]['log'] = false;
        $logError[E_CORE_WARNING]['die'] = false;

        $logError[E_COMPILE_ERROR]['log'] = true;
        $logError[E_COMPILE_ERROR]['die'] = true;

        $logError[E_COMPILE_WARNING]['log'] = false;
        $logError[E_COMPILE_WARNING]['die'] = false;

        $logError[E_USER_ERROR]['log'] = true;
        $logError[E_USER_ERROR]['die'] = true;

        $logError[E_USER_WARNING]['log'] = false;
        $logError[E_USER_WARNING]['die'] = false;

        $logError[E_USER_NOTICE]['log'] = false;
        $logError[E_USER_NOTICE]['die'] = false;

        if (InnomaticContainer::instance('innomaticcontainer')->getState() != InnomaticContainer::STATE_SETUP) {
            $phpLog = InnomaticContainer::instance(
                'innomaticcontainer'
            )->getHome() . 'core/log/php.log';
        } else {
            $phpLog = InnomaticContainer::instance(
                'innomaticcontainer'
            )->getHome() . 'core/log/innomatic.log';
        }

        switch ($this->_state) {
            case InnomaticContainer::STATE_DEBUG :
                $logError[E_NOTICE]['log'] = true;
                $logError[E_USER_NOTICE]['log'] = true;
                $logError[E_WARNING]['log'] = true;
                $logError[E_CORE_WARNING]['log'] = true;
                $logError[E_COMPILE_WARNING]['die'] = true;
                $logError[E_USER_WARNING]['log'] = true;
                break;

            case InnomaticContainer::STATE_SETUP :
                /* For debug purposes in setup procedure add these commands:
                 $log_err[E_NOTICE]['log'] = true;
                 $log_err[E_USER_NOTICE]['log'] = true;
                 */

                $logError[E_WARNING]['log'] = true;
                $logError[E_CORE_WARNING]['log'] = true;
                $logError[E_COMPILE_WARNING]['die'] = true;
                $logError[E_USER_WARNING]['log'] = true;
                break;

            case InnomaticContainer::STATE_PRODUCTION :
            case InnomaticContainer::STATE_UPGRADE :
                break;
        }

        switch ($errorType) {
            case E_ERROR:
                if ($logError[E_ERROR]['log']) {
                    require_once('innomatic/logging/Logger.php');
                    $log = new Logger($phpLog);
                    $log->logEvent(
                        'Innomatic error handler',
                        'PHP generated an ERROR at line '
                        . $errorLine
                        . ' of file '
                        . $errorFile
                        . '. The error message was: '
                        . $errorMessage,
                        \Innomatic\Logging\Logger::FAILURE
                    );
                }
                if ($logError[E_ERROR]['die']) {
                    $this->abort(
                        'A fatal error occured at line '
                        . $errorLine
                        . ' of file '
                        . $errorFile
                        . '. The error message was: '
                        . $errorMessage
                    );
                }
                break;

            case E_WARNING:
                if ($logError[E_WARNING]['log']) {
                    $log = new \Innomatic\Logging\Logger($phpLog);
                    $log->logEvent(
                        'Innomatic error handler',
                        'PHP generated a WARNING at line '
                        . $errorLine
                        . ' of file '
                        . $errorFile
                        . '. The error message was: '
                        . $errorMessage,
                        \Innomatic\Logging\Logger::WARNING
                    );
                }
                if ($logError[E_WARNING]['die']) {
                    $this->abort(
                        'A warning occured at line '
                        . $errorLine
                        . ' of file '
                        . $errorFile
                        . '. The error message was: '
                        . $errorMessage
                    );
                }
                break;

            case E_PARSE :
                if ($logError[E_PARSE]['log']) {
                    require_once('innomatic/logging/Logger.php');
                    $log = new Logger($phpLog);
                    $log->logEvent(
                        'Innomatic error handler',
                        'PHP generated a PARSE error at line '
                        .$errorLine
                        .' of file '
                        .$errorFile
                        .'. The error message was: '
                        .$errorMessage,
                        \Innomatic\Logging\Logger::ERROR
                    );
                }
                if ($logError[E_PARSE]['die'])
                $this->abort(
                    'A parse error occured at line '
                    .$errorLine
                    .' of file '
                    .$errorFile
                    .'. The error message was: '
                    .$errorMessage
                );
                break;

            case E_NOTICE :
                if ($logError[E_NOTICE]['log']) {
                    require_once('innomatic/logging/Logger.php');
                    $log = new Logger($phpLog);
                    $log->logEvent(
                        'Innomatic error handler',
                        'PHP generated a notice at line '
                        .$errorLine
                        .' of file '
                        .$errorFile
                        .'. The error message was: '
                        .$errorMessage,
                        \Innomatic\Logging\Logger::NOTICE
                    );
                }
                if ($logError[E_NOTICE]['die'])
                $this->abort(
                    'A notice occured at line '
                    .$errorLine
                    .' of file '
                    .$errorFile
                    .'. The error message was: '
                    .$errorMessage
                );
                break;

            case E_CORE_ERROR :
                if ($logError[E_CORE_ERROR]['log']) {
                    require_once('innomatic/logging/Logger.php');
                    $log = new Logger($phpLog);
                    $log->logEvent(
                        'Innomatic error handler',
                        'PHP generated a CORE ERROR at line '
                        .$errorLine
                        .' of file '
                        .$errorFile
                        .'. The error message was: '
                        .$errorMessage,
                        \Innomatic\Logging\Logger::ERROR
                    );
                }
                if ($logError[E_CORE_ERROR]['die'])
                $this->abort(
                    'A core error occured at line '
                    .$errorLine
                    .' of file '
                    .$errorFile
                    .'. The error message was: '
                    .$errorMessage
                );
                break;

            case E_CORE_WARNING :
                if ($logError[E_CORE_WARNING]['log']) {
                    require_once('innomatic/logging/Logger.php');
                    $log = new Logger($phpLog);
                    $log->logEvent(
                        'Innomatic error handler',
                        'PHP generated a CORE WARNING at line '
                        .$errorLine
                        .' of file '
                        .$errorFile
                        .'. The error message was: '
                        .$errorMessage,
                        \Innomatic\Logging\Logger::ERROR
                    );
                }
                if ($logError[E_CORE_WARNING]['die'])
                    $this->abort(
                        'A core warning occured at line '
                        .$errorLine
                        .' of file '
                        .$errorFile
                        .'. The error message was: '
                        .$errorMessage
                    );
                break;

            case E_COMPILE_ERROR :
                if ($logError[E_COMPILE_ERROR]['log']) {
                    require_once('innomatic/logging/Logger.php');
                    $log = new Logger($phpLog);
                    $log->logEvent(
                        'Innomatic error handler',
                        'PHP generated a COMPILE ERROR at line '
                        .$errorLine
                        .' of file '
                        .$errorFile
                        .'. The error message was: '
                        .$errorMessage,
                        \Innomatic\Logging\Logger::ERROR
                    );
                }
                if ($logError[E_COMPILE_ERROR]['die'])
                    $this->abort(
                        'A compile error occured at line '
                        .$errorLine
                        .' of file '
                        .$errorFile
                        .'. The error message was: '
                        .$errorMessage
                    );
                break;

            case E_COMPILE_WARNING :
                if ($logError[E_COMPILE_WARNING]['log']) {
                    require_once('innomatic/logging/Logger.php');
                    $log = new Logger($phpLog);
                    $log->logEvent(
                        'Innomatic error handler',
                        'PHP generated a COMPILE WARNING at line '
                        .$errorLine
                        .' of file '
                        .$errorFile
                        .'. The error message was: '
                        .$errorMessage,
                        \Innomatic\Logging\Logger::ERROR
                    );
                }
                if ($logError[E_COMPILE_WARNING]['die'])
                    $this->abort(
                        'A compile warning occured at line '
                        .$errorLine
                        .' of file '
                        .$errorFile
                        .'. The error message was: '
                        .$errorMessage
                    );
                break;

            case E_USER_ERROR :
                if ($logError[E_USER_ERROR]['log']) {
                    require_once('innomatic/logging/Logger.php');
                    $log = new Logger($phpLog);
                    $log->logEvent(
                        'Innomatic error handler',
                        'PHP generated an USER ERROR at line '
                        .$errorLine
                        .' of file '
                        .$errorFile
                        .'. The error message was: '
                        .$errorMessage,
                        \Innomatic\Logging\Logger::ERROR
                    );
                }
                if ($logError[E_USER_ERROR]['die'])
                    $this->abort(
                        'An user error occured at line '
                        .$errorLine
                        .' of file '
                        .$errorFile
                        .'. The error message was: '
                        .$errorMessage
                    );
                break;

            case E_USER_WARNING :
                if ($logError[E_USER_WARNING]['log']) {
                    require_once('innomatic/logging/Logger.php');
                    $log = new Logger($phpLog);
                    $log->logEvent(
                        'Innomatic error handler',
                        'PHP generated an USER WARNING at line '
                        .$errorLine
                        .' of file '
                        .$errorFile
                        .'. The error message was: '
                        .$errorMessage,
                        \Innomatic\Logging\Logger::ERROR
                    );
                }
                if ($logError[E_USER_WARNING]['die'])
                $this->abort(
                    'An user warning occured at line '
                    .$errorLine
                    .' of file '
                    .$errorFile
                    .'. The error message was: '
                    .$errorMessage
                );
                break;

            case E_USER_NOTICE :
                if ($logError[E_USER_NOTICE]['log']) {
                    require_once('innomatic/logging/Logger.php');
                    $log = new Logger($phpLog);
                    $log->logEvent(
                        'Innomatic error handler',
                        'PHP generated an USER NOTICE at line '
                        .$errorLine
                        .' of file '
                        .$errorFile
                        .'. The error message was: '
                        .$errorMessage,
                        \Innomatic\Logging\Logger::ERROR
                    );
                }
                if ($logError[E_USER_NOTICE]['die'])
                $this->abort(
                    'An user notice occured at line '
                    .$errorLine
                    .' of file '
                    .$errorFile
                    .'. The error message was: '
                    .$errorMessage
                );
                break;

            default :
                break;
        }
    }

    // Accessors

    public function getHome()
    {
        return $this->_home;
    }

    public function getConfigurationFile()
    {
        return $this->_configurationFile;
    }

    public function getDataAccess()
    {
        return $this->_rootDb;
    }

    public function getLanguage()
    {
        return $this->_language;
    }

    public function getCountry()
    {
        return $this->_country;
    }

    public function getConfig()
    {
        return $this->_config;
    }

    public function getLogger()
    {
        // Initialized the logger if not started
        if (!is_object($this->_logger)) {
            require_once('innomatic/logging/Logger.php');
            $this->_logger = new Logger(
                $this->_home . 'core/log/innomatic.log'
            );
        }
        return $this->_logger;
    }

    public function getPid()
    {
        return $this->_pid;
    }

    public function getState()
    {
        return $this->_state;
    }

    public function setState($state)
    {
        switch ($state) {
            case InnomaticContainer::STATE_SETUP:
                // break was intentionally omitted
            case InnomaticContainer::STATE_DEBUG:
                // break was intentionally omitted
            case InnomaticContainer::STATE_PRODUCTION:
                // break was intentionally omitted
            case InnomaticContainer::STATE_UPGRADE:
                // break was intentionally omitted
            case InnomaticContainer::STATE_MAINTENANCE:
                $this->_state = $state;
                break;
        }
    }

    public function getEnvironment()
    {
        return $this->_environment;
    }

    public function setEnvironment($environment)
    {
        switch ($environment) {
            case InnomaticContainer::ENVIRONMENT_DEVELOPMENT:
                // break was intentionally omitted
            case InnomaticContainer::ENVIRONMENT_INTEGRATION:
                // break was intentionally omitted
            case InnomaticContainer::ENVIRONMENT_PRODUCTION:
                // break was intentionally omitted
            case InnomaticContainer::ENVIRONMENT_STAGING:
                $this->_environment = $environment;
                break;
        }
    }

    public function getMode()
    {
        return $this->_mode;
    }

    public function setMode($mode)
    {
        // Mode Base cannot be set
        switch ($mode) {
            case InnomaticContainer::MODE_ROOT:
                // break was intentionally omitted
            case InnomaticContainer::MODE_DOMAIN:
                $this->_mode = $mode;
                break;
        }
    }

    public function getInterface()
    {
        return $this->_interface;
    }

    public function setInterface($interface)
    {
        switch ($interface) {
            case InnomaticContainer::INTERFACE_UNKNOWN:
                // break was intentionally omitted
            case InnomaticContainer::INTERFACE_CONSOLE:
                // break was intentionally omitted
            case InnomaticContainer::INTERFACE_WEB:
                // break was intentionally omitted
            case InnomaticContainer::INTERFACE_WEBSERVICES:
                // break was intentionally omitted
            case InnomaticContainer::INTERFACE_GUI:
                // break was intentionally omitted
            case InnomaticContainer::INTERFACE_EXTERNAL:
                $this->_interface = $interface;
                break;
        }
    }

    public function getEdition()
    {
        return $this->_edition;
    }

    public function getPlatformName()
    {
        return $this->_platformName;
    }

    public function getPlatformGroup()
    {
        return $this->_platformGroup;
    }

    public function &getMaintenanceResult()
    {
        return $this->_maintenanceResult;
    }

    public function getLoadTimer()
    {
        return $this->_loadTimer;
    }

    public function getDbLoadTimer()
    {
        return $this->_dbLoadTimer;
    }

    public function getLockOverride()
    {
        return $this->_lockOverride;
    }

    public function setLockOverride($status)
    {
        $this->_lockOverride = $status;
    }

    public function unlock()
    {
        // Erases all semaphores.
        $handle = opendir(
            InnomaticContainer::instance('innomaticcontainer')->getHome()
            . 'core/temp/semaphores'
        );
        if ($handle) {
            while (($file = readdir($handle)) !== false) {
                if ($file != '.' and $file != '..') {
                    @unlink(
                        InnomaticContainer::instance(
                            'innomaticcontainer'
                        )->getHome()
                        . 'core/temp/semaphores/' . $file
                    );
                }
            }
            closedir($handle);
        }

        // Erases system upgrading lock if it exists.
        if (
            file_exists(
                InnomaticContainer::instance('innomaticcontainer')->getHome()
                . 'core/temp/upgrading_system_lock'
            )
        ) {
            if (
                @unlink(
                    InnomaticContainer::instance(
                        'innomaticcontainer'
                    )->getHome()
                    . 'core/temp/upgrading_system_lock'
                )
            ) {
                require_once('innomatic/logging/Logger.php');
                $tmpLog = InnomaticContainer::instance(
                    'innomaticcontainer'
                )->getLogger();
                $tmpLog->logEvent(
                    'Innomatic',
                    'Innomatic has been unlocked.',
                    \Innomatic\Logging\Logger::NOTICE
                );

                $message = 'System unlocked.';
            } else {
                $message = 'Unable to unlock system.';
            }
        } else {
            $message = 'System was not locked.';
        }

        $this->abort($message);
    }

    public function setBaseUrl($url)
    {
        $this->_baseUrl = $url;
    }

    public function getBaseUrl($addController = true, $addHostname = false)
    {
        if ($addHostname) {
            $port = WebAppContainer::instance(
                'webappcontainer'
            )->getProcessor()->getRequest()->getServerPort();

            if (!strlen($port) or $port == 80) {
                $port = '';
            } else {
                $port = ':' . $port;
            }
        }

        return (
            $addHostname ? WebAppContainer::instance(
                'webappcontainer'
            )->getProcessor()->getRequest()->getScheme()
            . '://' . WebAppContainer::instance(
                'webappcontainer'
            )->getProcessor()->getRequest()->getServerName().$port : ''
        )
        . ( $addController ? $this->_baseUrl : (
            WebAppContainer::instance(
                'webappcontainer'
            )->getProcessor()->getRequest()->isUrlRewriteOn() ? $this->_baseUrl
            : substr($this->_baseUrl, 0, -10)
        ));
    }

    /**
     * Gets the Innomatic webapp base URL, similar to the Domain webapp URL
     * field. Since the innomatic webapp lacks the Domain webapp URL field and
     * the getBaseUrl() method determines the URL assuming that the active
     * webapp is innomatic itself, this method has been added in order to
     * obtain the innomatic webapp public URL.
     *
     * The base URL is automatically discovered during Innomatic setup and is
     * stored inside innomatic.ini configuration file in InnomaticBaseUrl key.
     *
     * @return string
     */
    public function getExternalBaseUrl()
    {
        // If already set, returns it.
        if (!empty($this->_externalBaseUrl)) {
            return $this->_externalBaseUrl;
        }

        // Not already set, so reads it from the configuration file.
        $this->_externalBaseUrl = $this->_config->value('InnomaticBaseUrl');

        // Should be empty only during setup phase.
        if (empty($this->_externalBaseUrl)) {
            $this->_externalBaseUrl = WebAppContainer::instance(
                'webappcontainer'
            )->getProcessor()->getRequest()->getRequestURL();
            // Checks if the URL contains the setup layout frames names
            // and strips them away.
            switch(substr($this->_externalBaseUrl, -5)) {
                case '/main':
                    // break was intentionally omitted
                case '/logo':
                    // break was intentionally omitted
                case '/menu':
                    $this->_externalBaseUrl = substr(
                        $this->_externalBaseUrl, 0, -4
                    );
                    break;
            }
        }
        return $this->_externalBaseUrl;
    }

    public static function setRootPassword($oldPassword, $newPassword)
    {
        $result = false;

        $fh = @fopen(
            InnomaticContainer::instance('innomaticcontainer')->getHome()
            . 'core/conf/rootpasswd.ini',
            'r'
        );
        if ($fh) {
            $cpassword = fgets($fh, 4096);
            fclose($fh);
        }

        if (md5($oldPassword) == $cpassword) {
            if (strlen($newPassword)) {
                $fh = @fopen(
                    InnomaticContainer::instance(
                        'innomaticcontainer'
                    )->getHome()
                    . 'core/conf/rootpasswd.ini',
                    'w'
                );
                if ($fh) {
                    require_once('innomatic/logging/Logger.php');
                    $log = InnomaticContainer::instance(
                        'innomaticcontainer'
                    )->getLogger();

                    fputs($fh, md5($newPassword));
                    fclose($fh);
                    $result = true;

                    $log->logEvent(
                        'Innomatic',
                        'Changed Innomatic root password',
                        \Innomatic\Logging\Logger::NOTICE
                    );
                } else {
                    $result = InnomaticContainer::SETROOTPASSWORD_UNABLE_TO_WRITE_NEW_PASSWORD;
                }
            } else {
                $result = InnomaticContainer::SETROOTPASSWORD_NEW_PASSWORD_IS_EMPTY;
            }
        } else {
            $result = InnomaticContainer::SETROOTPASSWORD_OLD_PASSWORD_IS_WRONG;
        }

        return $result;
    }

    // Web services methods

    public function setWebServicesUser($user)
    {
        $this->_currentWebServicesUser = $user;
    }

    public function getWebServicesUser()
    {
        return $this->_currentWebServicesUser;
    }

    public function setWebServicesProfile($profile)
    {
        $this->_currentWebServicesProfile = $profile;
    }

    public function getWebServicesProfile()
    {
        return $this->_currentWebServicesProfile;
    }

    public function setWebServicesMethods($methods)
    {
        $this->_currentWebServicesMethods = $methods;
    }

    public function getWebServicesMethods()
    {
        return $this->_currentWebServicesMethods;
    }

    // Domain / User methods

    public function getCurrentDomain()
    {
        return $this->_currentDomain;
    }

    public function getCurrentUser()
    {
        return $this->_currentUser;
    }

    public function isBootstrapped()
    {
        return $this->_bootstrapped;
    }

    public function isDomainStarted()
    {
        return $this->_domainStarted;
    }

    public function isRootStarted()
    {
        return $this->_rootStarted;
    }
}
