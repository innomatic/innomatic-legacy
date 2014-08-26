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
namespace Innomatic\Application;

/**
 * This class is to be extended for every component type.
 *
 * Extended classes should define doInstallAction(), doUninstallAction(),
 * doUpdateAction(), doEnableDomainAction() and doDisableDomainAction(),
 * or some of them, for their intended use.
 *
 * @since 5.0.0 introduced
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 */
abstract class ApplicationComponent implements ApplicationComponentBase
{
    /**
     * Innomatic Container.
     * 
     * @var \Innomatic\Core\InnomaticContainer
     * @access public
     */
    public $container;

    /**
     * Innomatic root data access
     *
     * @var \Innomatic\Dataaccess\DataAccess
     * @access public
     */
    public $rootda;

    /**
     * Domain data access
     *
     * @var \Innomatic\Dataaccess\DataAccess
     * @access public
     */
    public $domainda;

    /**
     * Application register handler
     *
     * @var ApplicationComponentRegister
     * @access public
     */
    public $applicationsComponentsRegister;

    /**
     * Application identifier name
     *
     * @var string
     * @access public
     */
    public $appname;

    /**
     * Component type name
     *
     * @var string
     * @access public
     */
    public $name;

    /**
     * Application temporary directory.
     *
     * This is the path where the application archive has been extracted.
     *
     * @var string
     * @access public
     */
    public $basedir;

    /**
     * Innomatic setup flag.
     *
     * This is set to true when in Innomatic is in setup mode.
     *
     * @var bool
     * @access public
     */
    public $setup = false;

    /**
     * Innomatic logger.
     *
     * @var \Innomatic\Logging\Logger
     * @access public
     */
    public $mLog;

    /**
     * Component doesn't support overriding
     */
    const OVERRIDE_NONE = 'false';

    /**
     * Component supports domain level overriding
     */
    const OVERRIDE_DOMAIN = 'domain';

    /**
     * Component supports global overriding
     */
    const OVERRIDE_GLOBAL = 'global';

    /**
     * Constructor.
     *
     * @param \Innomatic\Dataaccess\DataAccess $rootda Innomatic root data access
     * @param \Innomatic\Dataaccess\DataAccess $domainda Domain data access
     * @param string $appname Application identifier
     * @param string $name Component name
     * @param string $basedir Application extracted archive temporary directory
     */
    public function __construct(
        \Innomatic\Dataaccess\DataAccess $rootda,
        $domainda,
        $appname,
        $name,
        $basedir
    ) {
        // Arguments check and properties initialization
        //
        $this->container = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');
        $this->rootda    = $rootda;
        $this->domainda  = $domainda;

        if (!empty($appname)) {
            $this->appname = $appname;
        }
        if (!empty($name)) {
            $this->name = $name;
        }
        if (!empty($basedir)) {
            $this->basedir = $basedir;
        }

        $this->applicationsComponentsRegister = new ApplicationComponentRegister($this->rootda);
        $this->mLog = $this->container->getLogger();
    }

    /* public install($params) {{{ */
    /**
     * Installs the component and registers it in the application component register.
     *
     * @param array $params Array of the parameters in the component definition
     * @access public
     * @return bool Returns true if succesfully or already installed
     */
    public function install($params)
    {
        $result = false;
        $override = self::OVERRIDE_NONE;
        if (isset($params['override'])) {
            switch($params['override']) {
            case self::OVERRIDE_DOMAIN:
            case self::OVERRIDE_GLOBAL:
                $override = $params['override'];
                break;
            }
        }

        if (
            $this->applicationsComponentsRegister->checkRegisterComponent(
                $this->getType(),
                $this->name,
                '',
                '',
                $override
            ) == false
        ) {
            //if ( isset($params['donotinstall'] ) or $this->setup ) $result = true;
            if (
                (isset($params['donotinstall']) or $this->setup)
                and (!isset($params['forceinstall']))
            ) {
                $result = true;
            } else {
                $result = $this->doInstallAction($params);
            }

            if ($result == true) {
                $this->applicationsComponentsRegister->registerComponent(
                    $this->appname,
                    $this->getType(),
                    $this->name,
                    '',
                    $override
                );
            }
        } else {
            $this->applicationsComponentsRegister->registerComponent(
                $this->appname,
                $this->getType(),
                $this->name,
                '',
                $override,
                true
            );
        }

        return $result;
    }
    /* }}} */

    /* public uninstall($params) {{{ */
    /**
     * Uninstalls the component and removes it from the application component
     * register.
     *
     * @param array $params Parameters in the component definition
     * @access public
     * @return bool Returns true when successfully uninstalled and removed from
     * the register, false when the component has not been found
     */
    public function uninstall($params)
    {
        $result = false;
        $override = self::OVERRIDE_NONE;
        if (isset($params['override'])) {
            switch($params['override']) {
                case self::OVERRIDE_DOMAIN:
                case self::OVERRIDE_GLOBAL:
                    $override = $params['override'];
                    break;
            }
        }

        if (
            $this->applicationsComponentsRegister->checkRegisterComponent(
                $this->getType(),
                $this->name,
                '',
                $this->appname,
                $override
            ) != false
        ) {
            if (
                $this->applicationsComponentsRegister->checkRegisterComponent(
                    $this->getType(),
                    $this->name,
                    '',
                    $this->appname,
                    $override,
                    true
                ) == false
            ) {
                $result = $this->doUninstallAction($params);
                $this->applicationsComponentsRegister->unregisterComponent(
                    $this->appname,
                    $this->getType(),
                    $this->name,
                    '',
                    $override
                );
            } else {
                $result = $this->applicationsComponentsRegister->unregisterComponent(
                    $this->appname,
                    $this->getType(),
                    $this->name,
                    '',
                    $override
                );
            }
        }
        return $result;
    }
    /* }}} */

    /* public update($updatemode, $params, $domainprescript = '', $domainpostscript = '') {{{ */
    /**
     * Updates the component.
     *
     * @param integer $updatemode Update mode (Application::UPDATE_MODE_* constants)
     * @param array $params Parameters in the component definition
     * @param string $domainprescript Full path of an optional PHP script to be executed
     * before proceeding with the component update
     * @param string $domainpostscript Full path of an optional PHP script to be executed
     * after proceeding with the component update
     * @access public
     * @return void
     */
    public function update($updatemode, $params, $domainprescript = '', $domainpostscript = '')
    {
        $result = false;

        if ($this->getIsDomain() or (isset($params['override']) and $params['override'] == self::OVERRIDE_DOMAIN)) {
            $domainsquery = $this->rootda->execute('SELECT * FROM domains');
            $modquery = $this->rootda->execute(
                'SELECT id FROM applications WHERE appid='.$this->rootda->formatText($this->appname)
            );
            $appid = $modquery->getFields('id');
        }

        switch ($updatemode) {
            case Application::UPDATE_MODE_ADD :
                if ($this->install($params)) {
                    $result = true;

                    if (
                        $this->getIsDomain()
                        or (isset($params['override']) and $params['override'] == self::OVERRIDE_DOMAIN)
                    ) {
                        if ($domainsquery->getNumberRows() > 0) {
                            while (!$domainsquery->eof) {
                                $domaindata = $domainsquery->getFields();

                                // Check if the application is enabled for the current iteration domain
                                $actquery = $this->rootda->execute(
                                    'SELECT * FROM applications_enabled WHERE domainid='.(int)$domaindata['id']
                                    .' AND applicationid='.(int)$appid
                                );

                                if ($actquery->getNumberRows()) {
                                    // @todo check if it is better to move to a switchDomain() for performance
                                	// Start domain
                                    $this->container->startDomain($domaindata['domainid']);

                                    // Set the domain dataaccess for the component
                                    $this->domainda = $this->container->getCurrentDomain()->getDataAccess();

                                    // Enable the component for the current iteration domain
                                    if (!$this->enable($domainsquery->getFields('id'), $params)) {
                                        $result = false;
                                    }

                                    // Stop domain
                                    $this->container->stopDomain();
                                }

                                $actquery->free();
                                $domainsquery->moveNext();
                            }
                        }
                    }
                }
                break;

            case Application::UPDATE_MODE_REMOVE:
            	// Disables the component for each domain, before uninstalling it
                    if (
                        $this->getIsDomain()
                        or (isset($params['override']) and $params['override'] == self::OVERRIDE_DOMAIN)
                    ) {
                        if ($domainsquery->getNumberRows() > 0) {
                            while (!$domainsquery->eof) {
                                $domaindata = $domainsquery->getFields();

                                $actquery = $this->rootda->execute(
                                    'SELECT * FROM applications_enabled WHERE domainid='
                                    . (int) $domaindata['id'].' AND applicationid='. (int) $appid
                                );
                                if ($actquery->getNumberRows()) {
                                	// Start domain
                                    $this->container->startDomain($domaindata['domainid']);

                                    // Set the domain dataaccess for the component
                                    $this->domainda = $this->container->getCurrentDomain()->getDataAccess();

                                    // Disable the component for the current iteration domain
                                    if (!$this->disable($domainsquery->getFields('id'), $params)) {
                                        $result = false;
                                    }

                                    // Stop domain
                                    $this->container->stopDomain();
                                }

                                $actquery->free();
                                $domainsquery->moveNext();
                            }
                        }
                    }

                if ($this->uninstall($params)) {
                   	$result = true;
                }
                break;

            case Application::UPDATE_MODE_CHANGE :
                if ($this->doUpdateAction($params)) {
                    $result = true;

                    if (
                        $this->getIsDomain()
                        or (
                            isset($params['override'])
                            and $params['override'] == self::OVERRIDE_DOMAIN
                        )
                    ) {
                        if ($domainsquery->getNumberRows() > 0) {
                            while (!$domainsquery->eof) {
                                $domaindata = $domainsquery->getFields();

                                $actquery = $this->rootda->execute(
                                    'SELECT * FROM applications_enabled WHERE domainid='. (int) $domaindata['id']
                                    .' AND applicationid='. (int) $appid
                                );
                                if ($actquery->getNumberRows()) {
                                    $this->container->startDomain($domaindata['domainid']);
                                    $this->domainda = $this->container->getCurrentDomain()->getDataAccess();

                                    if (
                                        strlen($domainprescript)
                                        and file_exists($domainprescript)
                                    ) {
                                        include($domainprescript);
                                    }

                                    if (
                                        !$this->doUpdateDomainAction(
                                            $domainsquery->getFields('id'),
                                            $params
                                        )
                                    ) {
                                        $result = false;
                                    }

                                    if (
                                        strlen($domainpostscript)
                                        and file_exists($domainpostscript)
                                    ) {
                                        include($domainpostscript);
                                    }

                                    $this->container->stopDomain();
                                }

                                $actquery->free();
                                $domainsquery->moveNext();
                            }
                        }
                    }
                }
                break;

            default:
                $log = $this->container->getLogger();
                $log->logEvent(
                    'innomatic.applications.applicationcomponent.update',
                    'Invalid update mode',
                    \Innomatic\Logging\Logger::ERROR
                );
                break;
        }

        if (
            $this->getIsDomain()
            or (
                isset($params['override'])
                and $params['override'] == self::OVERRIDE_DOMAIN
            )
        ) {
            $domainsquery->free();
            $modquery->free();
        }

        return $result;
    }
    /* }}} */

    /* public enable($domainid, $params) {{{ */
    /**
     * Enables the component to the given domain.
     *
     * @param string $domainid Identifier name of the domain
     * @param array $params Parameters in the component definition
     * @access public
     * @return bool True when the component has been successfully enabled to the domain
     */
    public function enable($domainid, $params)
    {
        $result = false;
        $override = self::OVERRIDE_NONE;
        if (isset($params['override'])) {
            switch($params['override']) {
                case self::OVERRIDE_DOMAIN:
                case self::OVERRIDE_GLOBAL:
                    $override = $params['override'];
                    break;
            }
        }

        if (
            $this->getIsDomain()
            or (
                isset($params['override'])
                and $params['override'] == self::OVERRIDE_DOMAIN
            )
        ) {
            if (
                $this->applicationsComponentsRegister->checkRegisterComponent(
                    $this->getType(),
                    $this->name,
                    $domainid,
                    '',
                    $override
                ) == false
            ) {
                if ($this->doEnableDomainAction($domainid, $params)) {
                    $this->applicationsComponentsRegister->registerComponent(
                        $this->appname,
                        $this->getType(),
                        $this->name,
                        $domainid,
                        $override
                    );
                    $result = true;
                }
            } else {
                $result = $this->applicationsComponentsRegister->registerComponent(
                    $this->appname,
                    $this->getType(),
                    $this->name,
                    $domainid,
                    $override,
                    true
                );
            }
        } else {
            $result = true;
        }
        return $result;
    }
    /* }}} */

    /* public disable($domainid, $params) {{{ */
    /**
     * Disables the component to the given domain.
     *
     * @param string $domainid Identifier name of the domain
     * @param array $params Parameters in the component definition
     * @access public
     * @return bool True when the component has been successfully disabled from the domain
     */
    public function disable($domainid, $params)
    {
        $result = false;
        $override = self::OVERRIDE_NONE;
        if (isset($params['override'])) {
            switch($params['override']) {
                case self::OVERRIDE_DOMAIN:
                case self::OVERRIDE_GLOBAL:
                    $override = $params['override'];
                    break;
            }
        }

        if (
            $this->getIsDomain()
            or (
                isset($params['override'])
                and $params['override'] == self::OVERRIDE_DOMAIN
            )
        ) {
            if (
                $this->applicationsComponentsRegister->checkRegisterComponent(
                    $this->getType(),
                    $this->name,
                    $domainid,
                    $this->appname,
                    $override
                ) != false
            ) {
                if (
                    $this->applicationsComponentsRegister->checkRegisterComponent(
                        $this->getType(),
                        $this->name,
                        $domainid,
                        $this->appname,
                        $override,
                        true
                    ) == false
                ) {
                    $result = $this->doDisableDomainAction($domainid, $params);
                    $this->applicationsComponentsRegister->unregisterComponent(
                        $this->appname,
                        $this->getType(),
                        $this->name,
                        $domainid,
                        $override
                    );
                } else {
                    $result = $this->applicationsComponentsRegister->unregisterComponent(
                        $this->appname,
                        $this->getType(),
                        $this->name,
                        $domainid,
                        $override
                    );
                }
            }
        } else {
            $result = true;
        }
        return $result;
    }
    /* }}} */

    /* public doInstallAction($params) {{{ */
    /**
     * Executes component install action.
     *
     * It should be called by install() method only. It should be redefined
     * by the extending class.
     *
     * @param array $params Parameters in the component definition
     * @access public
     * @return bool True if not extended.
     */
    public function doInstallAction($params)
    {
        return true;
    }
    /* }}} */

    /* public doUninstallAction($params) {{{ */
    /**
     * Executes component uninstall action.
     *
     * It should be called by uninstall() method only. It should be redefined
     * by the extending class.
     *
     * @param array $params Parameters in the component definition
     * @access public
     * @return bool True if not extended.
     */
    public function doUninstallAction($params)
    {
        return true;
    }
    /* }}} */

    /* public doUpdateAction($params) {{{ */
     /**
     * Executes component update action.
     *
     * It should be called by update() method only. It should be redefined
     * by the extending class.
     *
     * @param array $params Parameters in the component definition
     * @access public
     * @return bool True if not extended.
     */
    public function doUpdateAction($params)
    {
        return true;
    }
    /* }}} */

    /* public doEnableDomainAction($domainid, $params) {{{ */
    /**
     * Executes enable domain action.
     *
     * It should be called by enable() method only. It should be redefined
     * by the extending class.
     *
     * @param string $domainid Domain identifier name
     * @param array $params Parameters in the component definition
     * @access public
     * @return void
     */
    public function doEnableDomainAction($domainid, $params)
    {
        return true;
    }
    /* }}} */

    /* public doDisableDomainAction($domainid, $params) {{{ */
    /**
     * Executes disable domain action.
     *
     * It should be called by disable() method only. It should be redefined
     * by the extending class.
     *
     * @param string $domainid Domain identifier name
     * @param array $params Parameters in the component definition
     * @access public
     * @return void
     */
    public function doDisableDomainAction($domainid, $params)
    {
        return true;
    }
    /* }}} */

    /* public doUpdateDomainAction($domainid, $params) {{{ */
    /**
     * Executes update domain action.
     *
     * It should be called by update() method only. It should be redefined
     * by the extending class.
     *
     * @param string $domainid Domain identifier name
     * @param array $params Parameters in the component definition
     * @access public
     * @return void
     */
    public function doUpdateDomainAction($domainid, $params)
    {
        return true;
    }
    /* }}} */
}
