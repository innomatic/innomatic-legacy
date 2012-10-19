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
require_once ('innomatic/application/ApplicationComponent.php');
/**
 * Maintenance task component handler.
 */
class MaintenanceTaskComponent extends ApplicationComponent
{
    function MaintenanceTaskComponent ($rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
    }
    public static function getType ()
    {
        return 'maintenancetask';
    }
    public static function getPriority ()
    {
        return 0;
    }
    public static function getIsDomain ()
    {
        return false;
    }
    public static function getIsOverridable ()
    {
        return false;
    }
    function DoInstallAction ($args)
    {
        $result = false;
        $args['file'] = $this->basedir . '/core/classes/shared/maintenance/' . basename($args['file']);
        if (strlen($args['name']) and strlen($args['file']) and file_exists($args['file'])) {
            if (! isset($args['enabled']))
                $args['enabled'] = 'false';
            if (! isset($args['catalog']))
                $args['catalog'] = '';
            $result = InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->execute('INSERT INTO maintenance_tasks VALUES (' . InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->formatText($args['name']) . ',' . InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->formatText(basename($args['file'])) . ',' . InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->formatText($args['catalog']) . ',' . InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->formatText($args['enabled'] == 'true' ? InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->fmttrue : InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->fmtfalse) . ')');
            if ($result) {
                @copy($args['file'], InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/classes/shared/maintenance/' . basename($args['file']));
                @chmod(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/classes/shared/maintenance/' . basename($args['file']), 0644);
            }
        }
        return $result;
    }
    function DoUninstallAction ($args)
    {
        $result = false;
        $args['file'] = $this->basedir . '/core/classes/shared/maintenance/' . basename($args['file']);
        if (strlen($args['name'])) {
            $result = InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->execute('DELETE FROM maintenance_tasks WHERE name=' . InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->formatText($args['name']));
            @unlink(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/classes/shared/maintenance/' . $args['file']);
        }
        return $result;
    }
    function DoUpdateAction ($args)
    {
        $result = false;
        $args['file'] = $this->basedir . '/core/classes/shared/maintenance/' . basename($args['file']);
        if (strlen($args['name']) and strlen($args['file']) and file_exists($args['file'])) {
            if (! isset($args['catalog'])) {
                $args['catalog'] = '';
            }
            $result = InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->execute('UPDATE maintenance_tasks SET file=' . InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->formatText(basename($args['file'])) . ',catalog=' . InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->formatText($args['catalog']) . ' WHERE name=' . InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->formatText($args['name']));
            if ($result) {
                @copy($args['file'], InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/classes/shared/maintenance/' . basename($args['file']));
                @chmod(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/classes/shared/maintenance/' . basename($args['file']), 0644);
            }
        }
        return $result;
    }
}
