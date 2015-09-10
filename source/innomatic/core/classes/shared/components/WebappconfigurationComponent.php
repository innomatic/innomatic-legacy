<?php
/**
 * Innomatic
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @copyright 2014 Innoteam Srl
 * @license   http://www.innomatic.io/license/ New BSD License
 * @link      http://www.innomatic.io
 * @since     2.0.0
 */
namespace Shared\Components;

use \Innomatic\Io\Filesystem;
use \Innomatic\Core;

/**
 * Webapp Configuration file component handler.
 *
 * A webapp configuration component is used to define a configuration file
 * to be copied inside a tenant webapp.
 *
 * Component parameters:
 *
 * - name: name of the webapp configuration
 * - file: name of the configuration file, without path
 * - keep: will preserve changes if an already existing configuration file has been enabled to a tenant
 */
class WebappconfigurationComponent extends \Innomatic\Application\ApplicationComponent
{
    /* public __construct($rootda, $domainida, $appname, $name, $basedir) {{{ */
    /**
     * Class constructor.
     *
     * @param \Innomatic\Dataaccess\DataAccess $rootda Innomatic root data access.
     * @param \Innomatic\Dataaccess\DataAccess $domainda Tenant data access.
     * @param string $appname Application name identifier.
     * @param string $name Component name.
     * @param string $basedir Temporary directory containing the application extracted archive.
     */
    public function __construct($rootda, $domainida, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainida, $appname, $name, $basedir);
    }
    /* }}} */

    /* public getType() {{{ */
    /**
     * Gets the component type identifier string.
     *
     * @static
     * @access public
     * @return string
     */
    public static function getType()
    {
        return 'webappconfiguration';
    }
    /* }}} */

    /* public getPriority() {{{ */
    /**
     * getType
     *
     * @static
     * @access public
     * @return void
     */
    public static function getPriority()
    {
        return 10;
    }
    /* }}} */

    /* public getIsDomain() {{{ */
    /**
     * Checks if the component can be enabled to/disabled from tenants.
     *
     * @static
     * @access public
     * @return boolean
     */
    public static function getIsDomain()
    {
        return true;
    }
    /* }}} */

    /* public getIsOverridable() {{{ */
    /**
     * Checks if the component supports custom overrides.
     *
     * @static
     * @access public
     * @return boolean
     */
    public static function getIsOverridable()
    {
        return false;
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
        $result = false;
        if (strlen($params['file'])) {
            $file = $this->basedir . '/core/conf/' . basename($params['file']);

            if (!file_exists(InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/applications/' . $this->appname . '/conf/')) {
                DirectoryUtils::mkTree(
                    InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() .
                    'core/applications/' . $this->appname . '/conf/', 0755
                );
            }

            if (copy(
                $file,
                InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() .
                'core/applications/' . $this->appname . '/conf/' .basename($file)
            )) {
                $result = true;
            }
        } else {
            $this->mLog->logEvent(
                'innomatic.webappconfiguration.doinstallaction',
                'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty file name',
                \Innomatic\Logging\Logger::ERROR
            );
        }
        return $result;
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
        $result = false;
        if (strlen($params['file'])) {
            if (is_dir(InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/applications/' . $this->appname . '/conf/' . basename($params['file']))) {
                DirectoryUtils::unlinkTree(
                    InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome().
                    'core/applications/'.$this->appname.'/conf/'.basename($params['file'])
                );
                $result = true;
            } else {
                $result = true;
            }
        } else {
            $this->mLog->logEvent(
                'innomatic.webappconfiguration.douninstallaction',
                'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty file name',
                \Innomatic\Logging\Logger::ERROR
            );
        }
        return $result;
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
        $result = false;

        if (strlen($params['file'])) {
            if (!file_exists(InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/applications/' . $this->appname . '/conf/')) {
                DirectoryUtils::mkTree(InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/applications/' . $this->appname . '/conf/', 0755);
            }

           if (file_exists(InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/applications/' . $this->appname . '/conf/' . basename($params['file']))) {
                unlink(
                    InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome().
                    'core/applications/' . $this->appname . '/conf/' . basename($params['file'])
                );
            }

            $file = $this->basedir . '/core/conf/' . basename($params['file']);
            if (file_exists($file)) {
                if (copy(
                    $file,
                    InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/applications/' . $this->appname . '/conf/' .basename($file)
                )) {
                    $result = true;
                }
            }
        } else {
            $this->mLog->logEvent(
                'innomatic.webappconfiguration.douninstallaction',
                'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty file name',
                \Innomatic\Logging\Logger::ERROR
            );
        }
        return $result;
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
        $domainQuery = $this->rootda->execute("SELECT domainid FROM domains WHERE id={$domainid}");
        if (!$domainQuery->getNumberRows()) {
            return false;
        }

        $domain = $domainQuery->getFields('domainid');

        $fileDestName = RootContainer::instance('\Innomatic\Core\RootContainer')->getHome() .
            $domain . '/core/conf/' . basename($params['file']);

        if (!file_exists(RootContainer::instance('\Innomatic\Core\RootContainer')->getHome().$domain.'/core/conf/')) {
            DirectoryUtils::mkTree(RootContainer::instance('\Innomatic\Core\RootContainer')->getHome().$domain.'/core/conf/', 0755);
        }

        if (!copy(
            InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/applications/' . $this->appname . '/conf/' .basename($params['file']),
            $fileDestName
        )) {
            return false;
        }
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
        $domainQuery = $this->rootda->execute(
            "SELECT domainid FROM domains WHERE id={$domainid}"
        );

        if (!$domainQuery->getNumberRows()) {
            return false;
        }

        $domain = $domainQuery->getFields('domainid');

        $fileDestName = RootContainer::instance('\Innomatic\Core\RootContainer')->getHome() .
            $domain . '/core/conf/' . basename($params['file']);

        // Checks if the "keep" parameter is set to true.
        // If so, the configuration file will not be overwritten.
        if (isset($params['keep']) and $params['keep'] == true and file_exists($fileDestName)) {
            return true;
        }

        if (!file_exists(RootContainer::instance('\Innomatic\Core\RootContainer')->getHome().$domain.'/core/conf/')) {
            DirectoryUtils::mkTree(RootContainer::instance('\Innomatic\Core\RootContainer')->getHome().$domain.'/core/conf/', 0755);
        }

        if (!copy(
            InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/applications/' . $this->appname . '/conf/' .basename($params['file']),
            $fileDestName
        )) {
            return false;
        }
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
        $domainQuery = $this->rootda->execute(
            "SELECT domainid FROM domains WHERE id={$domainid}"
        );

        if (!$domainQuery->getNumberRows()) {
            return false;
        }

        $domain = $domainQuery->getFields('domainid');

        $fileDestName = RootContainer::instance('\Innomatic\Core\RootContainer')->getHome() .
            $domain . '/core/conf/' . basename($params['file']);

        if (file_exists($fileDestName)) {
            return unlink($fileDestName);
        } else {
            return false;
        }
    }
    /* }}} */
}
