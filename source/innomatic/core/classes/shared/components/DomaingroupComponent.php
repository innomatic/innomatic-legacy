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
require_once ('innomatic/application/ApplicationComponent.php');
require_once ('innomatic/domain/user/Group.php');
require_once ('innomatic/domain/user/Permissions.php');
require_once ('innomatic/domain/user/User.php');
/**
 * Domaingroup component handler.
 */
class DomaingroupComponent extends ApplicationComponent
{
    function DomaingroupComponent ($rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
    }
    public static function getType ()
    {
        return 'domaingroup';
    }
    public static function getPriority ()
    {
        return 10;
    }
    public static function getIsDomain ()
    {
        return true;
    }
    public static function getIsOverridable ()
    {
        return false;
    }
    function doEnableDomainAction ($domainid, $params)
    {
        $result = &$this->domainda->execute('INSERT INTO domain_panels_groups VALUES (' . $this->domainda->getNextSequenceValue('domain_panels_groups_id_seq') . ',' . $this->domainda->formatText($params['name']) . ',' . $this->domainda->formatText($params['catalog']) . ')');
        if (! $result)
            $this->mLog->logEvent('innomatic.domaingroupcomponent.domaingroupcomponent.doenabledomainaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to insert desktop group into domain_panels_groups table', Logger::ERROR);
        return $result;
    }
    function doDisableDomainAction ($domainid, $params)
    {
        $tmpquery = &$this->domainda->execute('SELECT id FROM domain_panels_groups WHERE name = ' . $this->domainda->formatText($params['name']));
        $tmpperm = new Permissions($this->domainda, 0);
        $tmpperm->RemoveNodes($tmpquery->getFields('id'), 'group');
        $result = &$this->domainda->execute('DELETE FROM domain_panels_groups WHERE name = ' . $this->domainda->formatText($params['name']));
        if (! $result)
            $this->mLog->logEvent('innomatic.domaingroupcomponent.domaingroupcomponent.dodisabledomainaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to remove desktop group from domain_panels_groups table', Logger::ERROR);
        return $result;
    }
    function doUpdateDomainAction ($domainid, $params)
    {
        $result = false;
        if ($this->domainda->execute('UPDATE domain_panels_groups SET catalog=' . $this->domainda->formatText($params['catalog']) . ' WHERE name=' . $this->domainda->formatText($params['name']))) {
            $result = TRUE;
        } else {
            $this->mLog->logEvent('innomatic.domaingroupcomponent.domaingroupcomponent.doupdatedomainaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to update domain_panels_groups table for domainid ' . $domainid, Logger::ERROR);
        }
        return $result;
    }
}
