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

require_once ('innomatic/application/ApplicationComponent.php');
/**
 * Rootgroup component handler.
 */
class RootgroupComponent extends ApplicationComponent
{
    public function __construct($rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
    }
    public static function getType()
    {
        return 'rootgroup';
    }
    public static function getPriority()
    {
        return 10;
    }
    public static function getIsDomain()
    {
        return false;
    }
    public static function getIsOverridable()
    {
        return false;
    }
    public function DoInstallAction($params)
    {
        $result = &$this->rootda->execute('INSERT INTO root_panels_groups VALUES (' . $this->rootda->getNextSequenceValue('root_panels_groups_id_seq') . ',' . $this->rootda->formatText($params['name']) . ',' . $this->rootda->formatText($params['catalog']) . ')');
        if (! $result)
            $this->mLog->logEvent('innomatic.rootgroupcomponent.rootgroupcomponent.doenabledomainaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to insert rootgroup into root_panels_groups table', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
    public function DoUninstallAction($params)
    {
        $tmpquery = &$this->rootda->execute('SELECT id ' . 'FROM root_panels_groups ' . 'WHERE name = ' . $this->rootda->formatText($params['name']));
        /*
        $tmpperm = new RootPermissions( $this->domainid, 0 );
        $tmpperm->RemoveNodes( $tmpquery->getFields( 'id' ), 'group' );
        */
        $result = &$this->rootda->execute('DELETE FROM root_panels_groups ' . 'WHERE name = ' . $this->rootda->formatText($params['name']));
        if (! $result)
            $this->mLog->logEvent('innomatic.rootgroupcomponent.rootgroupcomponent.dodisabledomainaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to remove rootgroup from root_panels_groups table', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
    public function DoUpdateAction($params)
    {
        $result = false;
        if ($this->rootda->execute('UPDATE root_panels_groups SET catalog=' . $this->rootda->formatText($params['catalog']) . ' WHERE name=' . $this->rootda->formatText($params['name']))) {
            $result = true;
        } else {
            $this->mLog->logEvent('innomatic.rootgroupcomponent.rootgroupcomponent.doupdatedomainaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to update root_panels_groups table for domainid ' . $domainid, \Innomatic\Logging\Logger::ERROR);
        }
        return $result;
    }
}
