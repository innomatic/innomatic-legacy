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
 */
namespace Innomatic\Core;

/**
 * Innomatic base container class.
 *
 * This class takes care of bootstrapping and shutting down the whole container,
 * the root, the tenants and the Web Services interface.
 *
 * It holds the container current state, mode and interface.
 *
 * This class provided a custom PHP error handler for the container
 * applications.
 *
 * @copyright  1999-2014 Innoteam Srl
 * @since      5.0.0 introduced
 * @package    Core
 */
class InnomaticContainer extends \Innomatic\Util\Singleton
{
    private $home;
    /**
     * Complete path of the Innomatic configuration file.
     * @var string
     */
    private $configurationFile;
    /**
     *
     * @var boolean
     */
    private $bootstrapped = false;
    private $rootStarted = false;
    private $tenantStarted = false;
    private $pid;
    private $state;
    private $environment;
    private $mode = \Innomatic\Core\InnomaticContainer::MODE_BASE;
    private $interface = \Innomatic\Core\InnomaticContainer::INTERFACE_UNKNOWN;
    private $edition = \Innomatic\Core\InnomaticContainer::EDITION_MULTITENANT;
    private $rootDb;
    /**
     * Root language
     *
     * @var string
     */
    private $language;
    /**
     * Root country.
     *
     * @var string
     */
    private $country;
    private $config;
    private $logger;
    /**
     * Stores the result object of a maintenance routine.
     *
     * @var mixed
     */
    private $maintenanceResult;
    private $loadTimer;
    private $dbLoadTimer;
    private $platformName;
    private $platformGroup;
    private $lockOverride = false;
    private $baseUrl;
    private $externalBaseUrl;

    private $currentWebServicesProfile;
    private $currentWebServicesUser;
    private $currentWebServicesMethods = array();

    private $currentPanelController;

    private $currentTenant;
    private $currentUser;

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

    const EDITION_MULTITENANT = 1;
    const EDITION_SINGLETENANT = 2;

    // Deprecated

    const EDITION_ASP = 1;
    const EDITION_SAAS = 1;
    const EDITION_ENTERPRISE = 2;

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
        if ($this->bootstrapped) {
            return;
        }
        $this->home = $home;

        // Reads the configuration
        $this->configurationFile = $configuration;
        $this->config = new InnomaticSettings($configuration);

        // *********************************************************************
        // PHP environment
        // *********************************************************************

        // PHP
        $timelimit = $this->config->value('PHPExecutionTimeLimit');
        if (!strlen($timelimit)) {
            $timelimit = 0;
        }
        set_time_limit($timelimit);
        ignore_user_abort(true);
        //set_magic_quotes_runtime(0);

        // Adds global override classes folder to the include path.
        set_include_path(
            $this->home . 'core/overrides/classes/'
            . PATH_SEPARATOR . get_include_path()
        );

        // *********************************************************************
        // Innomatic state, environment, mode, interface and edition
        // *********************************************************************

        // Waits until system is in upgrade phase
        if ($this->lockOverride == false) {
            while (file_exists(
                $this->home . 'core/temp/upgrading_system_lock'
            )) {
                $this->state = \Innomatic\Core\InnomaticContainer::STATE_UPGRADE;
                clearstatcache();
                sleep(1);
            }
        }
        // Checks if system is in setup phase and sets the state
        if (file_exists($this->home . 'core/temp/setup_lock')) {
            $this->state = \Innomatic\Core\InnomaticContainer::STATE_SETUP;
            if (extension_loaded('APD')) {
                apd_set_session_trace(35);
            }
        } else {
            switch ($this->config->value('PlatformState')) {
                case 'debug':
                    $this->state = \Innomatic\Core\InnomaticContainer::STATE_DEBUG;
                    if (extension_loaded('APD')) {
                        apd_set_session_trace(35);
                    }
                    break;
                case 'production':
                    $this->state = \Innomatic\Core\InnomaticContainer::STATE_PRODUCTION;
                    break;
                default:
                    $this->state = \Innomatic\Core\InnomaticContainer::STATE_PRODUCTION;
            }
        }

        // Environment
        switch ($this->config->value('PlatformEnvironment')) {
            case 'development':
                $this->environment = \Innomatic\Core\InnomaticContainer::ENVIRONMENT_DEVELOPMENT;
                break;
            case 'integration':
                $this->environment = \Innomatic\Core\InnomaticContainer::ENVIRONMENT_INTEGRATION;
                break;
            case 'staging':
                $this->environment = \Innomatic\Core\InnomaticContainer::ENVIRONMENT_STAGING;
                break;
            case 'production':
                $this->environment = \Innomatic\Core\InnomaticContainer::ENVIRONMENT_PRODUCTION;
                break;
            default:
                $this->environment = \Innomatic\Core\InnomaticContainer::ENVIRONMENT_PRODUCTION;
        }

        // Interface
        //$this->interface = \Innomatic\Core\InnomaticContainer::INTERFACE_UNKNOWN;
        // Mode
        //$this->mode = \Innomatic\Core\InnomaticContainer::MODE_ROOT;
        // Edition
        if ($this->config->value('PlatformEdition') == 'enterprise') {
            $this->edition = \Innomatic\Core\InnomaticContainer::EDITION_SINGLETENANT;
        }

        // *********************************************************************
        // Pid and shutdown function
        // *********************************************************************

        if ($this->state != \Innomatic\Core\InnomaticContainer::STATE_SETUP) {
            $this->pid = md5(microtime());
            if (!file_exists($this->home . 'core/temp/pids/')) {
                @mkdir($this->home . 'core/temp/pids/');
            }
            touch($this->home . 'core/temp/pids/' . $this->pid, time());
            register_shutdown_function(array($this, 'shutdown'));
        }

        // *********************************************************************
        // Innomatic platform name
        // *********************************************************************

        $this->platformName = $this->config->value('PlatformName');
        $this->platformGroup = $this->config->value('PlatformGroup');

        // *********************************************************************
        // Innomatic error handler
        // *********************************************************************

        //set_error_handler(array($this, 'errorHandler'));

        // *********************************************************************
        // Innomatic root
        // *********************************************************************

        $this->country = $this->config->value('RootCountry');
        $this->language = $this->config->value('RootLanguage');

        if ($this->state != \Innomatic\Core\InnomaticContainer::STATE_SETUP) {
            // Innomatic central database
            //
            $dasnString = $this->config->value('RootDatabaseType') . '://'
            . $this->config->value('RootDatabaseUser') . ':'
            . $this->config->value('RootDatabasePassword') . '@'
            . $this->config->value('RootDatabaseHost') . ':'
            . $this->config->value('RootDatabasePort') . '/'
            . $this->config->value('RootDatabaseName') . '?'
            . 'logfile='
            . $this->getHome()
            . 'core/log/innomatic_root_db.log';
            $this->rootDb = \Innomatic\Dataaccess\DataAccessFactory::getDataAccess(
                new \Innomatic\Dataaccess\DataAccessSourceName($dasnString)
            );
            if (!$this->rootDb->connect()) {
                $this->abort('Database not connected');
            }
        }

        // *********************************************************************
        // Run time state and interface defined data
        // *********************************************************************

        // Debugger
        if ($this->state == \Innomatic\Core\InnomaticContainer::STATE_DEBUG) {
            $this->loadTimer = new \Innomatic\Debug\LoadTime(
                LoadTime::LOADTIME_MODE_CONTINUOUS
            );
            $this->loadTimer->Mark('start');
            $this->dbLoadTimer = new \Innomatic\Debug\LoadTime(
                LoadTime::LOADTIME_MODE_STARTSTOP
            );
        }

        // Security
        $securityReportsInterval = $this->config->value(
            'SecurityReportsInterval'
        );
        if ($securityReportsInterval > 0) {
            $lastSecurityReport = $this->config->value(
                'SecurityLastReportTime'
            );
            if (!$lastSecurityReport or $lastSecurityReport < (time() - ($securityReportsInterval * 3600 * 24))) {
                $innomaticSecurity = new \Innomatic\Security\SecurityManager();
                $innomaticSecurity->sendReport();
                unset($innomaticSecurity);
            }
        }
        unset($securityReportsInterval);

        // Maintenance
        $maintenanceInterval = $this->config->value('MaintenanceInterval');
        if ($this->state != \Innomatic\Core\InnomaticContainer::STATE_MAINTENANCE and $maintenanceInterval > 0) {
            $lastMaintenance = $this->config->value(
                'MaintenanceLastExecutionTime'
            );
            if (
                !$lastMaintenance
                or $lastMaintenance < (time() - (
                    $maintenanceInterval * 3600 * 24
                ))
            ) {
                $innomaticMaintenance = new \Innomatic\Maintenance\MaintenanceHandler();
                $innomaticMaintenance->doMaintenance();
                $innomaticMaintenance->sendReport();
                unset($innomaticMaintenance);
            }
        }
        unset($maintenanceInterval);

        // *********************************************************************
        // Auto exec routines
        // *********************************************************************

        // Application reupdate check
        if (file_exists($this->home . 'core/temp/appinst/reupdate')) {
            $tmpmod = new \Innomatic\Application\Application($this->rootDb, '');
            $tmpmod->install($this->home . 'core/temp/appinst/reupdate');
            clearstatcache();
            if (file_exists($this->home . 'core/temp/appinst/reupdate')) {
                unlink($this->home . 'core/temp/appinst/reupdate');
            }
        }

        // Startup hook
        if ($this->state != \Innomatic\Core\InnomaticContainer::STATE_SETUP) {
            $hook = new \Innomatic\Process\Hook($this->rootDb, 'innomatic', 'instance');
            $null = '';
            switch ($hook->callHooks('startup', $null, '')) {
                case \Innomatic\Process\Hook::RESULT_ABORT :
                    $this->abort('Bootstrap aborted');
                    break;
            }
        }

        // Bootstrap end
        $this->bootstrapped = true;
    }

    /**
     * Starts the root mode.
     * @param string $userId Root username (currently not used, defaults to
     * "root").
     */
    public function startRoot($userId = 'root')
    {
        $this->setMode(\Innomatic\Core\InnomaticContainer::MODE_ROOT);

        if ($this->rootStarted) {
            return;
        }

        $this->rootStarted = true;
    }

    /* public startDomain($domainId, $userId = '') {{{ */
    /**
     * Starts a tenant.
     *
     * @deprecated Deprecated in favor of startTenant() method.
     * @see \Innomatic\Core\InnomaticContainer::startTenant()
     * @param string $tenantId Tenant identifier name.
     * @param string $userId User identifier name.
     * @access public
     * @return boolean True if the tenant has been started.
     */
    public function startDomain($domainId, $userId = '')
    {
        $this->startTenant($domainId, $userId);
    }
    /* }}} */

    /* public startTenant($tenantId, $userId = '') {{{ */
    /**
     * Starts a tenant.
     *
     * @param string $tenantId Tenant identifier name.
     * @param string $userId User identifier name.
     * @access public
     * @return boolean True if the tenant has been started.
     */
    public function startTenant($tenantId, $userId = '')
    {
        $result = false;
        $this->setMode(\Innomatic\Core\InnomaticContainer::MODE_DOMAIN);

        if (is_object($this->currentTenant) or $this->tenantStarted) {
            // A domain has been already started
            return false;
        }

        $this->currentTenant = new \Innomatic\Domain\Domain($this->rootDb, $domainId, null);

        if ($this->currentTenant->isValid()) {
            // Check if domain is active
            //
            if (
                $this->getInterface() != \Innomatic\Core\InnomaticContainer::INTERFACE_WEB
                and $this->currentTenant->domaindata['domainactive']
                == $this->rootDb->fmtfalse
            ) {
                $this->abort('Domain disabled');
            }

            if (!$this->currentTenant->getDataAccess()->isConnected()) {
                $adloc = new \Innomatic\Locale\LocaleCatalog(
                    'innomatic::authentication',
                    $this->language
                );
                $this->abort(
                    $adloc->getStr('nodb')
                );
            }

            // Adds override classes folder to the include path.
            set_include_path(
                $this->home . 'core/domains/'
                . $this->currentTenant->getDomainId()
                . '/overrides/classes/'
                . PATH_SEPARATOR . get_include_path()
            );

            // User
            //
            // TODO check in single tenant edition if the admin@domainid part is ok
            // $admin_username = 'admin'
            // .(\Innomatic\Core\InnomaticContainer::instance(
            //      '\Innomatic\Core\InnomaticContainer'
            // )->getEdition() == \Innomatic\Core\InnomaticContainer::EDITION_MULTITENANT ? '@'.$domain
            // : '');
            $this->currentUser = new \Innomatic\Domain\User\User(
                $this->currentTenant->domainserial,
                \Innomatic\Domain\User\User::getUserIdByUsername(
                    strlen($userId) ? $userId : 'admin@' . $domainId
                )
            );

            $result = true;
        }
        $this->tenantStarted = $result;
        return $result;
    }
    /* }}} */

    /* public stopDomain() {{{ */
    /**
     * Stops a tenant.
     *
     * @deprecated Deprecated in favor of stopTenant() method.
     * @see \Innomatic\Core\InnomaticContainer::stopTenant()
     * @access public
     * @return void
     */
    public function stopDomain()
    {
        $this->stopTenant();
    }
    /* }}} */

    /* public stopTenant() {{{ */
    /**
     * Stops a tenant.
     *
     * @access public
     * @return void
     */
    public function stopTenant()
    {
        if ($this->tenantStarted) {
            if ($this->getEdition() == \Innomatic\Core\InnomaticContainer::EDITION_MULTITENANT) {
                $this->currentTenant->getDataAccess()->close();
            }
            // @todo implement

            // Removes override classes folder from the include path
            set_include_path(
                str_replace(
                    $this->home . 'core/domains/'
                    . $this->currentTenant->getDomainId()
                    . '/overrides/classes/' . PATH_SEPARATOR,
                    '', get_include_path()
                )
            );

            $this->tenantStarted = false;
            $this->currentTenant = null;
            $this->setMode(\Innomatic\Core\InnomaticContainer::MODE_ROOT);
        }
    }
    /* }}} */

    /* public switchTenant($tenantName, $userName = '') {{{ */
    /**
     * Automatically closes the current tenant (if started) and starts a new tenant.
     *
     * Before switching, this method checks:
     * - if there is an already running tenant;
     * - if the given tenant and user names combination is the same of the
     *  current running one.
     *
     * @param string $tenantName internal name of the new tenant
     * @param string $userName optional username of the tenant user (if empty,
     * the tenant will be started with the tenant administrator user)
     * @return null or string with tenant name if previous tenant has been started already.
     */
    public function switchTenant($tenantName, $userName = '')
    {
        $prevDomainName = null;

        // Check if there an already started domain
        if ($this->tenantStarted) {
            $prevDomainName = $this->getCurrentTenant()->getDomainId();
            $prevUserName   = $this->getCurrentUser()->getUserName();

            if (
                $tenantName == $prevDomainName
                && (($userName == '' && substr($prevUserName, 0, 6) == 'admin@')
                    or ($userName == $prevUserName))
            ) {
                // The given tenant and user combination is the same of current
                // one, there's no need to switch
                return $prevDomainName;
            }
            $this->stopDomain();
        }

        $this->startDomain($tenantName, $userName);

        // Return the previous domain id name
        return $prevDomainName;
    }
    /* }}} */

    /* public switchDomain($tenantName, $userName = '') {{{ */
    /**
     * Automatically closes the current tenant (if started) and starts a new tenant.
     *
     * Before switching, this method checks:
     * - if there is an already running tenant;
     * - if the given tenant and user names combination is the same of the
     *  current running one.
     *
     * @deprecated Deprecated in favor of switchTenant() method.
     * @see \Innomatic\Core\InnomaticContainer::switchTenant()
     * @param string $tenantName internal name of the new tenant
     * @param string $userName optional username of the tenant user (if empty,
     * the tenant will be started with the tenant administrator user)
     * @return null or string with tenant name if previous tenant has been started already.
     */
    public function switchDomain($tenantName, $userName = '')
    {
        return $this->switchTenant($tenantName, $userName);
    }
    /* }}} */

    // TODO to be implemented
    public function startWebServices()
    {
    }

    public function startMaintenance()
    {
        $this->setState(\Innomatic\Core\InnomaticContainer::STATE_MAINTENANCE);
        $this->setInterface(\Innomatic\Core\InnomaticContainer::INTERFACE_CONSOLE);

        $hook = new \Innomatic\Process\Hook($this->rootDb, 'innomatic', 'instance');
        $null = null;
        switch ($hook->callHooks('maintenance', $null, '')) {
            case \Innomatic\Process\Hook::RESULT_ABORT:
                $this->abort(
                    'Maintenance aborted'
                );
                break;
        }

        $innomaticMnt = new \Innomatic\Maintenance\MaintenanceHandler();
        $this->maintenanceResult = $innomaticMnt->doMaintenance();
        $innomaticMnt->sendReport($this->maintenanceResult);
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
        $rootContainer = RootContainer::instance('\Innomatic\Core\RootContainer');
        $rootContainer->stop();
        exit($status);
    }

    public function shutdown()
    {
        if ($this->state != \Innomatic\Core\InnomaticContainer::STATE_SETUP) {
            if (is_object($this->rootDb)) {
                $hook = new \Innomatic\Process\Hook($this->rootDb, 'innomatic', 'instance');
                $null = '';
                switch ($hook->callHooks('shutdown', $null, '')) {
                    case \Innomatic\Process\Hook::RESULT_ABORT:
                        $this->abort('Shutdown aborted');
                        break;
                }
            }
        }

        switch ($this->state) {
            case \Innomatic\Core\InnomaticContainer::STATE_DEBUG:
                if (
                    is_object($this->loadTimer)
                    and RootContainer::instance('\Innomatic\Core\RootContainer')->isClean()
                    == true
                ) {
                    $this->loadTimer->Mark('end');

                    $log = $this->getLogger();
                    $log->logEvent(
                        'innomatic',
                        'Profiler total time: '
                        . $this->loadTimer->getTotalTime(),
                        \Innomatic\Logging\Logger::DEBUG
                    );

                    $fh = @fopen(
                        $this->home . 'core/temp/pids/' . $this->pid,
                        'w'
                    );
                    if ($fh) {
                        $dump = \Innomatic\Debug\InnomaticDump::instance('\Innomatic\Debug\InnomaticDump');
                        $dump->snapshot();
                        @fwrite($fh, serialize($dump));
                        @fclose($fh);
                    }
                }
                break;
        }

        if (!RootContainer::instance('\Innomatic\Core\RootContainer')->isClean()) {
            if (is_object($this->loadTimer)) {
                $this->loadTimer->Mark('end');

                $log = $this->getLogger();
                $log->logEvent(
                    'innomatic',
                    'Profiler total time: '
                    . $this->loadTimer->getTotalTime(),
                    \Innomatic\Logging\Logger::DEBUG
                );
            }

            $fh = @fopen(
                $this->home . 'core/temp/pids/' . $this->pid . '_coredump',
                'w'
            );
            if ($fh) {
                $dump = \Innomatic\Debug\InnomaticDump::instance('\Innomatic\Debug\InnomaticDump');
                $dump->snapshot();
                @fwrite($fh, serialize($dump));
                @fclose($fh);
            }

        }

        if (
            !RootContainer::instance('\Innomatic\Core\RootContainer')->isClean()
            or (
                $this->state != \Innomatic\Core\InnomaticContainer::STATE_DEBUG
                and file_exists(
                    $this->home . 'core/temp/pids/' . $this->pid
                )
            )
        ) {
            @unlink($this->home . 'core/temp/pids/' . $this->pid);
        }

        exit();
    }

    public function abort($text, $forceInterface = '')
    {
        if (strlen($forceInterface)) {
            $interface = $forceInterface;
        } else {
            $interface = $this->interface;
        }

        if ($interface == \Innomatic\Core\InnomaticContainer::INTERFACE_EXTERNAL) {
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
            $interface = \Innomatic\Core\InnomaticContainer::INTERFACE_WEB;
            $this->interface = \Innomatic\Core\InnomaticContainer::INTERFACE_WEB;
            //}
        }

        switch ($interface) {
            case \Innomatic\Core\InnomaticContainer::INTERFACE_GUI :
                // break was intentionally omitted
            case \Innomatic\Core\InnomaticContainer::INTERFACE_UNKNOWN :
                // break was intentionally omitted
            case \Innomatic\Core\InnomaticContainer::INTERFACE_WEBSERVICES :
                // break was intentionally omitted
            case \Innomatic\Core\InnomaticContainer::INTERFACE_EXTERNAL :
                break;
            case \Innomatic\Core\InnomaticContainer::INTERFACE_CONSOLE :
                echo "\n".$text."\n";
                break;
            case \Innomatic\Core\InnomaticContainer::INTERFACE_WEB :
                $tmpWui = \Innomatic\Wui\Wui::instance('\Innomatic\Wui\Wui');
                $tmpWui->loadWidget('empty');
                //$tmp_elem = new WuiEmpty('empty');

                $dieImage = \Innomatic\Wui\Wui::instance('\Innomatic\Wui\Wui')->getTheme()->mStyle['biglogo'];

                ?>
<html>
<head>
<basefont face="Verdana" />
<title>Innomatic</title>
<link rel="stylesheet" type="text/css"
    href="<?php echo \Innomatic\Wui\Wui::instance('\Innomatic\Wui\Wui')->getTheme()->mStyle['css'];
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
            href="<?php echo \Innomatic\Core\InnomaticContainer::instance(
                '\Innomatic\Core\InnomaticContainer'
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

        if ($this->getState() != \Innomatic\Core\InnomaticContainer::STATE_SETUP) {
            $phpLog = \Innomatic\Core\InnomaticContainer::instance(
                '\Innomatic\Core\InnomaticContainer'
            )->getHome() . 'core/log/php.log';
        } else {
            $phpLog = \Innomatic\Core\InnomaticContainer::instance(
                '\Innomatic\Core\InnomaticContainer'
            )->getHome() . 'core/log/innomatic.log';
        }

        switch ($this->state) {
            case \Innomatic\Core\InnomaticContainer::STATE_DEBUG :
                $logError[E_NOTICE]['log'] = true;
                $logError[E_USER_NOTICE]['log'] = true;
                $logError[E_WARNING]['log'] = true;
                $logError[E_CORE_WARNING]['log'] = true;
                $logError[E_COMPILE_WARNING]['die'] = true;
                $logError[E_USER_WARNING]['log'] = true;
                break;

            case \Innomatic\Core\InnomaticContainer::STATE_SETUP :
                /* For debug purposes in setup procedure add these commands:
                 $log_err[E_NOTICE]['log'] = true;
                 $log_err[E_USER_NOTICE]['log'] = true;
                 */

                $logError[E_WARNING]['log'] = true;
                $logError[E_CORE_WARNING]['log'] = true;
                $logError[E_COMPILE_WARNING]['die'] = true;
                $logError[E_USER_WARNING]['log'] = true;
                break;

            case \Innomatic\Core\InnomaticContainer::STATE_PRODUCTION :
            case \Innomatic\Core\InnomaticContainer::STATE_UPGRADE :
                break;
        }

        switch ($errorType) {
            case E_ERROR:
                if ($logError[E_ERROR]['log']) {

                    $log = new \Innomatic\Logging\Logger($phpLog);
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

                    $log = new \Innomatic\Logging\Logger($phpLog);
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

                    $log = new \Innomatic\Logging\Logger($phpLog);
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

                    $log = new \Innomatic\Logging\Logger($phpLog);
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

                    $log = new \Innomatic\Logging\Logger($phpLog);
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

                    $log = new \Innomatic\Logging\Logger($phpLog);
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

                    $log = new \Innomatic\Logging\Logger($phpLog);
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

                    $log = new \Innomatic\Logging\Logger($phpLog);
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

                    $log = new \Innomatic\Logging\Logger($phpLog);
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

                    $log = new \Innomatic\Logging\Logger($phpLog);
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
        return $this->home;
    }

    public function getConfigurationFile()
    {
        return $this->configurationFile;
    }

    public function getDataAccess()
    {
        return $this->rootDb;
    }

    public function getLanguage()
    {
        return $this->language;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getLogger()
    {
        // Initialized the logger if not started
        if (!is_object($this->logger)) {

            $this->logger = new \Innomatic\Logging\Logger(
                $this->home . 'core/log/innomatic.log'
            );
        }
        return $this->logger;
    }

    public function getPid()
    {
        return $this->pid;
    }

    public function getState()
    {
        return $this->state;
    }

    public function setState($state)
    {
        switch ($state) {
            case \Innomatic\Core\InnomaticContainer::STATE_SETUP:
                // break was intentionally omitted
            case \Innomatic\Core\InnomaticContainer::STATE_DEBUG:
                // break was intentionally omitted
            case \Innomatic\Core\InnomaticContainer::STATE_PRODUCTION:
                // break was intentionally omitted
            case \Innomatic\Core\InnomaticContainer::STATE_UPGRADE:
                // break was intentionally omitted
            case \Innomatic\Core\InnomaticContainer::STATE_MAINTENANCE:
                $this->state = $state;
                break;
        }
    }

    public function getEnvironment()
    {
        return $this->environment;
    }

    public function setEnvironment($environment)
    {
        switch ($environment) {
            case \Innomatic\Core\InnomaticContainer::ENVIRONMENT_DEVELOPMENT:
                // break was intentionally omitted
            case \Innomatic\Core\InnomaticContainer::ENVIRONMENT_INTEGRATION:
                // break was intentionally omitted
            case \Innomatic\Core\InnomaticContainer::ENVIRONMENT_PRODUCTION:
                // break was intentionally omitted
            case \Innomatic\Core\InnomaticContainer::ENVIRONMENT_STAGING:
                $this->environment = $environment;
                break;
        }
    }

    public function getMode()
    {
        return $this->mode;
    }

    public function setMode($mode)
    {
        // Mode Base cannot be set
        switch ($mode) {
            case \Innomatic\Core\InnomaticContainer::MODE_ROOT:
                // break was intentionally omitted
            case \Innomatic\Core\InnomaticContainer::MODE_DOMAIN:
                $this->mode = $mode;
                break;
        }
    }

    public function getInterface()
    {
        return $this->interface;
    }

    public function setInterface($interface)
    {
        switch ($interface) {
            case \Innomatic\Core\InnomaticContainer::INTERFACE_UNKNOWN:
                // break was intentionally omitted
            case \Innomatic\Core\InnomaticContainer::INTERFACE_CONSOLE:
                // break was intentionally omitted
            case \Innomatic\Core\InnomaticContainer::INTERFACE_WEB:
                // break was intentionally omitted
            case \Innomatic\Core\InnomaticContainer::INTERFACE_WEBSERVICES:
                // break was intentionally omitted
            case \Innomatic\Core\InnomaticContainer::INTERFACE_GUI:
                // break was intentionally omitted
            case \Innomatic\Core\InnomaticContainer::INTERFACE_EXTERNAL:
                $this->interface = $interface;
                break;
        }
    }

    public function getEdition()
    {
        return $this->edition;
    }

    public function getPlatformName()
    {
        return $this->platformName;
    }

    public function getPlatformGroup()
    {
        return $this->platformGroup;
    }

    public function &getMaintenanceResult()
    {
        return $this->maintenanceResult;
    }

    public function getLoadTimer()
    {
        return $this->loadTimer;
    }

    public function getDbLoadTimer()
    {
        return $this->dbLoadTimer;
    }

    public function getLockOverride()
    {
        return $this->lockOverride;
    }

    public function setLockOverride($status)
    {
        $this->lockOverride = $status;
    }

    public function unlock()
    {
        // Erases all semaphores.
        $handle = opendir(
            $this->getHome()
            . 'core/temp/semaphores'
        );
        if ($handle) {
            while (($file = readdir($handle)) !== false) {
                if ($file != '.' and $file != '..') {
                    @unlink(
                        \Innomatic\Core\InnomaticContainer::instance(
                            '\Innomatic\Core\InnomaticContainer'
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
                $this->getHome()
                . 'core/temp/upgrading_system_lock'
            )
        ) {
            if (
                @unlink(
                    \Innomatic\Core\InnomaticContainer::instance(
                        '\Innomatic\Core\InnomaticContainer'
                    )->getHome()
                    . 'core/temp/upgrading_system_lock'
                )
            ) {

                $tmpLog = \Innomatic\Core\InnomaticContainer::instance(
                    '\Innomatic\Core\InnomaticContainer'
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
        $this->baseUrl = $url;
    }

    public function getBaseUrl($addController = true, $addHostname = false)
    {
        if ($addHostname) {
            $port = \Innomatic\Webapp\WebAppContainer::instance(
                '\Innomatic\Webapp\WebAppContainer'
            )->getProcessor()->getRequest()->getServerPort();

            if (!strlen($port) or $port == 80) {
                $port = '';
            } else {
                $port = ':' . $port;
            }
        }

        return (
            $addHostname ? \Innomatic\Webapp\WebAppContainer::instance(
                '\Innomatic\Webapp\WebAppContainer'
            )->getProcessor()->getRequest()->getScheme()
            . '://' . \Innomatic\Webapp\WebAppContainer::instance(
                '\Innomatic\Webapp\WebAppContainer'
            )->getProcessor()->getRequest()->getServerName().$port : ''
        )
        . ( $addController ? $this->baseUrl : (
            \Innomatic\Webapp\WebAppContainer::instance(
                '\Innomatic\Webapp\WebAppContainer'
            )->getProcessor()->getRequest()->isUrlRewriteOn() ? $this->baseUrl
            : substr($this->baseUrl, 0, -10)
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
        if (!empty($this->externalBaseUrl)) {
            return $this->externalBaseUrl;
        }

        // Not already set, so reads it from the configuration file.
        $this->externalBaseUrl = $this->config->value('InnomaticBaseUrl');

        // Should be empty only during setup phase.
        if (empty($this->externalBaseUrl)) {
            $this->externalBaseUrl = \Innomatic\Webapp\WebAppContainer::instance(
                '\Innomatic\Webapp\WebAppContainer'
            )->getProcessor()->getRequest()->getRequestURL();
            // Checks if the URL contains the setup layout frames names
            // and strips them away.
            switch(substr($this->externalBaseUrl, -5)) {
                case '/main':
                    // break was intentionally omitted
                case '/logo':
                    // break was intentionally omitted
                case '/menu':
                    $this->externalBaseUrl = substr(
                        $this->externalBaseUrl, 0, -4
                    );
                    break;
            }
        }
        return $this->externalBaseUrl;
    }

    public static function setRootPassword($oldPassword, $newPassword)
    {
        $result = false;

        $fh = @fopen(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome()
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
                    \Innomatic\Core\InnomaticContainer::instance(
                        '\Innomatic\Core\InnomaticContainer'
                    )->getHome()
                    . 'core/conf/rootpasswd.ini',
                    'w'
                );
                if ($fh) {

                    $log = \Innomatic\Core\InnomaticContainer::instance(
                        '\Innomatic\Core\InnomaticContainer'
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
                    $result = \Innomatic\Core\InnomaticContainer::SETROOTPASSWORD_UNABLE_TO_WRITE_NEW_PASSWORD;
                }
            } else {
                $result = \Innomatic\Core\InnomaticContainer::SETROOTPASSWORD_NEW_PASSWORD_IS_EMPTY;
            }
        } else {
            $result = \Innomatic\Core\InnomaticContainer::SETROOTPASSWORD_OLD_PASSWORD_IS_WRONG;
        }

        return $result;
    }

    // Web services methods

    public function setWebServicesUser($user)
    {
        $this->currentWebServicesUser = $user;
    }

    public function getWebServicesUser()
    {
        return $this->currentWebServicesUser;
    }

    public function setWebServicesProfile($profile)
    {
        $this->currentWebServicesProfile = $profile;
    }

    public function getWebServicesProfile()
    {
        return $this->currentWebServicesProfile;
    }

    public function setWebServicesMethods($methods)
    {
        $this->currentWebServicesMethods = $methods;
    }

    public function getWebServicesMethods()
    {
        return $this->currentWebServicesMethods;
    }

    // Panel controller

    public function setPanelController($controller)
    {
    	$this->currentPanelController = $controller;
    }

    public function getPanelController()
    {
    	return $this->currentPanelController;
    }

    // Tenant / User methods

    /* public getCurrentTenant() {{{ */
    /**
     * Gets current tenant object.
     *
     * @since 6.4.0 introduced
     * @access public
     * @return \Innomatic\Domain\Domain
     */
    public function getCurrentTenant()
    {
        return $this->currentTenant;
    }
    /* }}} */

    /* public getCurrentDomain() {{{ */
    /**
     * Get current domain object.
     *
     * @deprecated Deprecated in favor of getCurrentTenant method.
     * @see \Innomatic\Core\InnomaticContainer::getCurrentTenant
     * @access public
     * @return \Innomatic\Domain\Domain
     */
    public function getCurrentDomain()
    {
        return $this->currentTenant;
    }
    /* }}} */

    /* public setCurrentTenant(\Innomatic\Domain\Domain $tenant) {{{ */
    /**
     * Sets current tenant object.
     *
     * @since 6.4.0 introduced
     * @param \Innomatic\Domain\Domain $tenant Tenant object.
     * @access public
     * @return void
     */
    public function setCurrentTenant(\Innomatic\Domain\Domain $tenant)
    {
        $this->currentTenant = $tenant;
    }
    /* }}} */

    /* public setCurrentDomain(\Innomatic\Domain\Domain $domain) {{{ */
    /**
     * Sets current tenant object.
     *
     * @deprecated Deprecated in favor of setCurrentTenant method.
     * @see \Innomatic\Core\InnomaticContainer::setCurrentTenant()
     * @param \Innomatic\Domain\Domain $domain Tenant object.
     * @access public
     * @return void
     */
    public function setCurrentDomain(\Innomatic\Domain\Domain $domain)
    {
        return $this->setCurrentTenant($domain);
    }
    /* }}} */

    public function getCurrentUser()
    {
        return $this->currentUser;
    }

    public function setCurrentUser(\Innomatic\Domain\User\User $user)
    {
        $this->currentUser = $user;
    }

    public function isBootstrapped()
    {
        return $this->bootstrapped;
    }

    /* public isTenantStarted() {{{ */
    /**
     * Checks if a tenant has been started.
     *
     * @since 6.4.0 introduced
     * @access public
     * @return boolean
     */
    public function isTenantStarted()
    {
        return $this->tenantStarted;
    }
    /* }}} */

    /* public isDomainStarted() {{{ */
    /**
     * Checks if a tenant has been started.
     *
     * @deprecated This method has been superseded by isTenantStarted method.
     * @see \Innomatic\Core\InnomaticContainer::isTenantStarted()
     * @access public
     * @return boolean
     */
    public function isDomainStarted()
    {
        return $this->tenantStarted;
    }
    /* }}} */

    /* public isRootStarted() {{{ */
    /**
     * Checks if the root has been started.
     *
     * @access public
     * @return boolean
     */
    public function isRootStarted()
    {
        return $this->rootStarted;
    }
    /* }}} */
}
