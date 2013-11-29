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

/**
 * This class is to be extended for every component type. Extended classes
 * should define DoInstallAction(), DoUninstallAction(), DoUpdateAction(),
 * doEnableDomainAction() and doDisableDomainAction(), or some of them,
 * for their intended use.
 *
 */
abstract class ApplicationComponent
{
    /*! @public rootda DataAccess class - Innomatic database handler. */
    public $rootda;
    /*! @public domainda DataAccess class - Domain data access handler. */
    public $domainda;
    /*! @public applicationsComponentsRegister applicationsComponentsRegister
    class - Application register handler. */
    public $applicationsComponentsRegister;
    /*! @public appname string - Application name. */
    public $appname;
    /*! @public name string - Component name. */
    public $name;
    /*! @public basedir string - Application temporary directory path, where the
    application has been extracted. */
    public $basedir;
    /*! @public setup bool - Setup flag, TRUE when in Innomatic setup phase. */
    public $setup = false;
    public $mLog;
    const OVERRIDE_NONE = 'false';
    const OVERRIDE_DOMAIN = 'domain';
    const OVERRIDE_GLOBAL = 'global';

    /*!
     @abstract Class constructor.
     @param rootda DataAccess class - Innomatic database handler.
     @param domainda DataAccess class - Domain database handler. Used when
    enabling/disabling an component to a domain. May be null otherwise.
     @param appname string - Application name.
     @param name string - Component name.
     @param basedir string - Application temporary directory path.
     */
    public function __construct(
        DataAccess $rootda,
        $domainda,
        $appname,
        $name,
        $basedir
    )
    {
        // Arguments check and properties initialization
        //
        $this->rootda = $rootda;
        $this->domainda = $domainda;

        if (!empty($appname)) {
            $this->appname = $appname;
        }
        if (!empty($name)) {
            $this->name = $name;
        }
        if (!empty($basedir)) {
            $this->basedir = $basedir;
        }

        require_once('innomatic/application/ApplicationComponentRegister.php');
        $this->applicationsComponentsRegister = new ApplicationComponentRegister($this->rootda);
        $this->mLog = InnomaticContainer::instance('innomaticcontainer')->getLogger();
    }

    /*!
     @abstract Installs the component and registers the component in the
application register.
     @param params array - Array of the parameters in the component definition
structure.
     @result True if succesfully or already installed.
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
            ) == FALSE
        ) {
            //if ( isset($params['donotinstall'] ) or $this->setup ) $result = TRUE;
            if (
                (
                    isset($params['donotinstall'])
                    or $this->setup
                )
                and (!isset($params['forceinstall']))
            ) {
                $result = true;
            } else {
                $result = $this->DoInstallAction($params);
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

    /*!
     @abstract Uninstalls the component and unregisters the component from
the application register.
     @param params array - Array of the parameters in the component definition
structure.
     @result True if successfully uninstalled and unregistered, FALSE if
component not found.
     */
    public function uninstall($params)
    {
        $result = FALSE;
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
            ) != FALSE
        ) {
            if (
                $this->applicationsComponentsRegister->checkRegisterComponent(
                    $this->getType(),
                    $this->name,
                    '',
                    $this->appname,
                    $override,
                    TRUE
                ) == FALSE
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

    /*!
     @abstract Updates the component.
     @discussion $this->domain controls if the component may be used by a domain,
                 and there isn't an error of the function if it isn't usable.
     @param updatemode int - update mode (defined).
     @param params array - Array of the parameters in the component definition structure.
     @result True if successfully updated.
     */
    public function update($updatemode, $params, $domainprescript = '', $domainpostscript = '')
    {
        require_once('innomatic/application/Application.php');
        $result = FALSE;

        if ($this->getIsDomain() or (isset($params['override']) and $params['override'] == self::OVERRIDE_DOMAIN)) {
            $domainsquery = $this->rootda->execute('SELECT * FROM domains');
            $modquery = $this->rootda->execute(
                'SELECT id FROM applications WHERE appid='.$this->rootda->formatText($this->appname)
            );
            $appid = $modquery->getFields('id');
        }

        switch ($updatemode) {
            case Application::UPDATE_MODE_ADD :
                if ($this->DoInstallAction($params)) {
                    $result = TRUE;

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
                                    InnomaticContainer::instance(
                                        'innomaticcontainer'
                                    )->startDomain($domaindata['domainid']);
                                    $this->domainda = InnomaticContainer::instance(
                                        'innomaticcontainer'
                                    )->getCurrentDomain()->getDataAccess();

                                    if (!$this->Enable($domainsquery->getFields('id'), $params)) {
                                        $result = FALSE;
                                    }

                                    InnomaticContainer::instance('innomaticcontainer')->stopDomain();
                                }

                                $actquery->free();
                                $domainsquery->moveNext();
                            }
                        }
                    }
                }
                break;

            case Application::UPDATE_MODE_REMOVE :
                if ($this->DoUninstallAction($params)) {
                    $result = TRUE;

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
                                    'SELECT * FROM applications_enabled WHERE domainid='
                                    . (int) $domaindata['id'].' AND applicationid='. (int) $appid
                                );
                                if ($actquery->getNumberRows()) {
                                    InnomaticContainer::instance(
                                        'innomaticcontainer'
                                    )->startDomain($domaindata['domainid']);
                                    $this->domainda = InnomaticContainer::instance(
                                        'innomaticcontainer'
                                    )->getCurrentDomain()->getDataAccess();


                                    if (!$this->Disable($domainsquery->getFields('id'), $params)) {
                                        $result = FALSE;
                                    }

                                    InnomaticContainer::instance('innomaticcontainer')->stopDomain();
                                }

                                $actquery->free();

                                $domainsquery->moveNext();
                            }
                        }
                    }
                }
                break;

            case Application::UPDATE_MODE_CHANGE :
                if ($this->DoUpdateAction($params)) {
                    $result = TRUE;

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
                                    InnomaticContainer::instance(
                                        'innomaticcontainer'
                                    )->startDomain($domaindata['domainid']);
                                    $this->domainda = InnomaticContainer::instance(
                                        'innomaticcontainer'
                                    )->getCurrentDomain()->getDataAccess();

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
                                        $result = FALSE;
                                    }

                                    if (
                                        strlen($domainpostscript)
                                        and file_exists($domainpostscript)
                                    ) {
                                        include($domainpostscript);
                                    }

                                    InnomaticContainer::instance(
                                        'innomaticcontainer'
                                    )->stopDomain();
                                }

                                $actquery->free();
                                $domainsquery->moveNext();
                            }
                        }
                    }
                }
                break;

            default:
                $log = InnomaticContainer::instance(
                    'innomaticcontainer'
                )->getLogger();
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

    /*!
     @abstract Enables the component to a domain.
     @param domainid string - id name of the domain to enable.
     @param params array - Array of the parameters in the component definition structure.
     @result True if successfully enabled, registered or not usable.
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
                ) == FALSE
            ) {
                if ($this->doEnableDomainAction($domainid, $params)) {
                    $this->applicationsComponentsRegister->registerComponent(
                        $this->appname,
                        $this->getType(),
                        $this->name,
                        $domainid,
                        $override
                    );
                    $result = TRUE;
                }
            } else {
                $result = $this->applicationsComponentsRegister->registerComponent(
                    $this->appname,
                    $this->getType(),
                    $this->name,
                    $domainid,
                    $override,
                    TRUE
                );
            }
        } else {
            $result = TRUE;
        }
        return $result;
    }

    /*!
     @abstract Disables the component to a domain.
     @param domainid string - id name of the domain to disable.
     @param params array - Array of the parameters in the component definition
structure.
     @result True if successfully disabled, unregistered or not usable.
     */
    public function disable($domainid, $params)
    {
        $result = FALSE;
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
                ) != FALSE
            ) {
                if (
                    $this->applicationsComponentsRegister->checkRegisterComponent(
                        $this->getType(),
                        $this->name,
                        $domainid,
                        $this->appname,
                        $override,
                        TRUE
                    ) == FALSE
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
            $result = TRUE;
        }
        return $result;
    }

    /*!
     @abstract Executes component install action.
     @discussion It should be called by Install() member only. It should be
redefined by the extended class.
     @param params array - Array of the parameters in the component definition
structure
     @result True if not redefined.
     */
    public function doInstallAction($params)
    {
        return TRUE;
    }

    /*!
     @abstract Executes component uninstall action.
     @discussion It should be called by Uninstall() member only. It should be
redefined by the extended class.
     @param params array - Array of the parameters in the component definition
structure.
     @result True if not redefined.
     */
    public function doUninstallAction($params)
    {
        return TRUE;
    }

    /*!
     @abstract Executes component update action.
     @discussion It should be called by Update() member only. It should be
redefined by the extended class.
     @param params array - Array of the parameters in the component definition
structure.
     @result True if not redefined.
     */
    public function doUpdateAction($params)
    {
        return TRUE;
    }

    /*!
     @abstract Executes enable domain action.
     @discussion It should be called by Enable() member only. It should be
redefined by the extended class.
     @param domainid string - Domain name.
     @param params array - Array of the parameters in the component definition
structure.
     @result True if not redefined.
     */
    public function doEnableDomainAction($domainid, $params)
    {
        return TRUE;
    }

    /*!
     @abstract Executes disable domain action.
     @discussion It should be called by Disable() member only. It should be
redefined by the extended class.
     @param domainid string - Domain name.
     @param params array - Array of the parameters in the component definition
structure.
     @result True if not redefined.
     */
    public function doDisableDomainAction($domainid, $params)
    {
        return TRUE;
    }

    /*!
     @abstract Executes domain component update action.
     @discussion It should be called by Update() member only. It should be
redefined by the extended class.
     @param domainid string - Domain name.
     @param params array - Array of the parameters in the component definition
structure.
     @result True if not redefined.
     */
    public function doUpdateDomainAction($domainid, $params)
    {
        return TRUE;
    }

    /**
     * Tells the component type identifier string.
     *
     */
    abstract public static function getType();

    /**
     * Tells the component priority over the other ones.
     *
     */
    abstract public static function getPriority();

    /**
     * Tells if the component supports the domain abilitation.
     *
     */
    abstract public static function getIsDomain();

    /**
     * Tells if the component supports the override feature.
     *
     */
    abstract public static function getIsOverridable();
}
