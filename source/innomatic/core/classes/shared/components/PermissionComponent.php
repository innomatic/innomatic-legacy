<?php
/**
 * Innomatic
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2014 Innomatic Company
 * @license    http://www.innomatic.io/license/ New BSD License
 * @link       http://www.innomatic.io
 * @since      Class available since Release 6.4.0
 */
namespace Shared\Components;

/**
 * Permission component handler.
 */
class PermissionComponent extends \Innomatic\Application\ApplicationComponent
{
    public function __construct($rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
    }

    public static function getType()
    {
        return 'permission';
    }

    public static function getPriority()
    {
        return 0;
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
        $permission = new \Innomatic\Domain\User\Permission();
        
        $result = $permission->add(
            $params['name'],
            $params['title'],
            $params['description'],
            $params['catalog'],
            $this->appname
        );
        
        // Add default roles for this permission, if defined
        if ($result && isset($params['defaultroles'])) {
            $roles = explode(',', $params['defaultroles']);
        
            foreach ($roles as $role) {
                $permission->assignRole(trim($role));
            }
        }
        
        return $result;
    }

    public function doDisableDomainAction($domainid, $params)
    {
        $permission = new \Innomatic\Domain\User\Permission(
            \Innomatic\Domain\User\Permission::getIdFromName($params['name'])
        );
        
        return $permission->remove();
    }

    public function doUpdateDomainAction($domainid, $params)
    {
        $permission = new \Innomatic\Domain\User\Permission(
            \Innomatic\Domain\User\Permission::getIdFromName($params['name'])
        );
        
        return $permission
            ->setTitle($params['title'])
            ->setDescription($params['description'])
            ->setCatalog($params['catalog'])
            ->setApplication($this->appname)
            ->store();
    }
}
