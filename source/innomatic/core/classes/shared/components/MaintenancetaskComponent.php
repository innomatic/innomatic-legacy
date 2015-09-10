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
 * @since      Class available since Release 5.0
 */
namespace Shared\Components;

/**
 * Maintenance task component handler.
 *
 * A maintenance task component is used to define the actions to be done in
 * planned maintenance with a cronjob.
 *
 * Component parameters:
 *
 * - name: name of the class that extends the Maintenance task component
 * - file: name of the class file, without path
 * - catalog: name of catalog used to translate the text
 * - enabled: defines if the scheduled task is enabled by default or not
 */
class MaintenancetaskComponent extends \Innomatic\Application\ApplicationComponent
{
    /* public __construct($rootda, $domainda, $appname, $name, $basedir) {{{ */
    /**
     * Class constructor.
     *
     * @param \Innomatic\Dataaccess\DataAccess $rootda Innomatic root data access.
     * @param \Innomatic\Dataaccess\DataAccess $domainda Tenant data access.
     * @param string $appname Application name identifier.
     * @param string $name Component name.
     * @param string $basedir Temporary directory containing the application extracted archive.
     */
    public function __construct($rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
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
        return 'maintenancetask';
    }
    /* }}} */

    /* public getPriority() {{{ */
    /**
     * Gets the priority applied when executing operations with
     * the component.
     *
     * @static
     * @access public
     * @return integer
     */
    public static function getPriority()
    {
        return 0;
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
        return false;
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

    /* public doInstallAction($args) {{{ */
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
    public function doInstallAction($args)
    {
        $result = false;

        $args['file'] = $this->basedir . '/core/classes/shared/maintenance/' . basename($args['file']);

        if (strlen($args['name']) and strlen($args['file']) and file_exists($args['file'])) {
            if (!isset($args['enabled'])) {
                $args['enabled'] = 'false';
            }

            if (!isset($args['catalog'])) {
                $args['catalog'] = '';
            }

            $result = $this->container->getDataAccess()->execute(
                'INSERT INTO maintenance_tasks '.
                'VALUES (' . $this->container->getDataAccess()->formatText($args['name']) .
                ',' . $this->container->getDataAccess()->formatText(basename($args['file'])) .
                ',' . $this->container->getDataAccess()->formatText($args['catalog']) .
                ',' . $this->container->getDataAccess()->formatText(
                    $args['enabled'] == 'true' ? $this->container->getDataAccess()->fmttrue : $this->container->getDataAccess()->fmtfalse
                ) .
                ')'
            );

            if ($result) {
                @copy($args['file'], $this->container->getHome() . 'core/classes/shared/maintenance/' . basename($args['file']));
                @chmod($this->container->getHome() . 'core/classes/shared/maintenance/' . basename($args['file']), 0644);
            }
        }
        return $result;
    }
    /* }}} */

    /* public doUninstallAction($args) {{{ */
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
    public function doUninstallAction($args)
    {
        $result = false;

        $args['file'] = $this->basedir . '/core/classes/shared/maintenance/' . basename($args['file']);

        if (strlen($args['name'])) {
            $result = $this->container->getDataAccess()->execute(
                'DELETE FROM maintenance_tasks ' .
                'WHERE name=' . $this->container->getDataAccess()->formatText($args['name'])
            );

            @unlink($this->container->getHome() . 'core/classes/shared/maintenance/' . $args['file']);
        }

        return $result;
    }
    /* }}} */

    /* public doUpdateAction($args) {{{ */
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
    public function doUpdateAction($args)
    {
        $result = false;

        $args['file'] = $this->basedir . '/core/classes/shared/maintenance/' . basename($args['file']);

        if (strlen($args['name']) and strlen($args['file']) and file_exists($args['file'])) {
            if (! isset($args['catalog'])) {
                $args['catalog'] = '';
            }

            $result = $this->container->getDataAccess()->execute('UPDATE maintenance_tasks SET file=' . $this->container->getDataAccess()->formatText(basename($args['file'])) . ',catalog=' . $this->container->getDataAccess()->formatText($args['catalog']) . ' WHERE name=' . $this->container->getDataAccess()->formatText($args['name']));

            if ($result) {
                @copy($args['file'], $this->container->getHome() . 'core/classes/shared/maintenance/' . basename($args['file']));
                @chmod($this->container->getHome() . 'core/classes/shared/maintenance/' . basename($args['file']), 0644);
            }
        }

        return $result;
    }
    /* }}} */
}
