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
 * @license    http://www.innomatic.org/license/   BSD License
 * @link       http://www.innomatic.org
 * @since      Class available since Release 6.4.0
 */
namespace Shared\Components;

/**
 * Role component handler.
 */
class RoleComponent extends \Innomatic\Application\ApplicationComponent
{
    public function __construct($rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
    }

    public static function getType()
    {
        return 'role';
    }

    public static function getPriority()
    {
        return 20;
    }

    public static function getIsDomain()
    {
        return true;
    }

    public static function getIsOverridable()
    {
        return false;
    }

    public function doInstallAction($args)
    {
        return true;
    }

    public function doUninstallAction($args)
    {
        return true;
    }

    public function doUpdateAction($args)
    {
        return true;
    }

    public function doEnableDomainAction($domainid, $params)
    {
        $role = new \Innomatic\Domain\User\Role();
        
        $result = $role->add(
            $params['name'],
            $params['title'],
            $params['description'],
            $params['catalog'],
            $this->appname
        );
        
        
        // Add default permissions for this role, if defined
        if ($result && isset($params['defaultpermissions'])) {
            $perms = explode(',', $params['defaultpermissions']);
            
            foreach($perms as $permission) {
                $role->assignPermission(trim($permission));
            }
        }
        
        return $result;
    }

    public function doDisableDomainAction($domainid, $params)
    {
        /*
        $role = new \Innomatic\Domain\User\Role(
            \Innomatic\Domain\User\Role::getIdFromName($params['name'])
        );
        
        return $role->remove();
        */
        
        return true;
    }

    public function doUpdateDomainAction($domainid, $params)
    {
        $role = new \Innomatic\Domain\User\Role(
            \Innomatic\Domain\User\Role::getIdFromName($params['name'])
        );
        
        return $role
            ->setTitle($params['title'])
            ->setDescription($params['description'])
            ->setCatalog($params['catalog'])
            ->setApplication($this->appname)
            ->store();
    }
}
