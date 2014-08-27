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
    public function doInstallAction($args)
    {
        $result = false;
        $args['file'] = $this->basedir . '/core/classes/shared/maintenance/' . basename($args['file']);
        if (strlen($args['name']) and strlen($args['file']) and file_exists($args['file'])) {
            if (! isset($args['enabled']))
                $args['enabled'] = 'false';
            if (! isset($args['catalog']))
                $args['catalog'] = '';
            $result = $this->container->getDataAccess()->execute('INSERT INTO maintenance_tasks VALUES (' . $this->container->getDataAccess()->formatText($args['name']) . ',' . $this->container->getDataAccess()->formatText(basename($args['file'])) . ',' . $this->container->getDataAccess()->formatText($args['catalog']) . ',' . $this->container->getDataAccess()->formatText($args['enabled'] == 'true' ? $this->container->getDataAccess()->fmttrue : $this->container->getDataAccess()->fmtfalse) . ')');
            if ($result) {
                @copy($args['file'], $this->container->getHome() . 'core/classes/shared/maintenance/' . basename($args['file']));
                @chmod($this->container->getHome() . 'core/classes/shared/maintenance/' . basename($args['file']), 0644);
            }
        }
        return $result;
    }
    public function doUninstallAction($args)
    {
        $result = false;
        $args['file'] = $this->basedir . '/core/classes/shared/maintenance/' . basename($args['file']);
        if (strlen($args['name'])) {
            $result = $this->container->getDataAccess()->execute('DELETE FROM maintenance_tasks WHERE name=' . $this->container->getDataAccess()->formatText($args['name']));
            @unlink($this->container->getHome() . 'core/classes/shared/maintenance/' . $args['file']);
        }
        return $result;
    }
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
}
