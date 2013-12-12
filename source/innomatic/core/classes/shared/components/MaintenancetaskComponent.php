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
namespace Shared\Components;

/**
 * Maintenance task component handler.
 */
class MaintenancetaskComponent extends \Innomatic\Application\ApplicationComponent
{
    public function __construct($rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
    }
    public static function getType()
    {
        return 'maintenancetask';
    }
    public static function getPriority()
    {
        return 0;
    }
    public static function getIsDomain()
    {
        return false;
    }
    public static function getIsOverridable()
    {
        return false;
    }
    public function DoInstallAction($args)
    {
        $result = false;
        $args['file'] = $this->basedir . '/core/classes/shared/maintenance/' . basename($args['file']);
        if (strlen($args['name']) and strlen($args['file']) and file_exists($args['file'])) {
            if (! isset($args['enabled']))
                $args['enabled'] = 'false';
            if (! isset($args['catalog']))
                $args['catalog'] = '';
            $result = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->execute('INSERT INTO maintenance_tasks VALUES (' . \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->formatText($args['name']) . ',' . \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->formatText(basename($args['file'])) . ',' . \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->formatText($args['catalog']) . ',' . \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->formatText($args['enabled'] == 'true' ? \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->fmttrue : \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->fmtfalse) . ')');
            if ($result) {
                @copy($args['file'], \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/classes/shared/maintenance/' . basename($args['file']));
                @chmod(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/classes/shared/maintenance/' . basename($args['file']), 0644);
            }
        }
        return $result;
    }
    public function DoUninstallAction($args)
    {
        $result = false;
        $args['file'] = $this->basedir . '/core/classes/shared/maintenance/' . basename($args['file']);
        if (strlen($args['name'])) {
            $result = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->execute('DELETE FROM maintenance_tasks WHERE name=' . \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->formatText($args['name']));
            @unlink(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/classes/shared/maintenance/' . $args['file']);
        }
        return $result;
    }
    public function DoUpdateAction($args)
    {
        $result = false;
        $args['file'] = $this->basedir . '/core/classes/shared/maintenance/' . basename($args['file']);
        if (strlen($args['name']) and strlen($args['file']) and file_exists($args['file'])) {
            if (! isset($args['catalog'])) {
                $args['catalog'] = '';
            }
            $result = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->execute('UPDATE maintenance_tasks SET file=' . \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->formatText(basename($args['file'])) . ',catalog=' . \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->formatText($args['catalog']) . ' WHERE name=' . \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->formatText($args['name']));
            if ($result) {
                @copy($args['file'], \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/classes/shared/maintenance/' . basename($args['file']));
                @chmod(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/classes/shared/maintenance/' . basename($args['file']), 0644);
            }
        }
        return $result;
    }
}
