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
 * @since      Class available since Release 6.4.0
*/

use \Innomatic\Core;
use \Innomatic\Wui\Widgets;
use \Innomatic\Wui\Dispatch;
use \Innomatic\Locale\LocaleCatalog;
use \Innomatic\Domain\User;
use \Innomatic\Domain;
use \Shared\Wui;

class ProfilesPanelActions extends \Innomatic\Desktop\Panel\PanelActions
{
    protected $localeCatalog;
    public $status;
    public $javascript;

    public function __construct(\Innomatic\Desktop\Panel\PanelController $controller)
    {
        parent::__construct($controller);
    }

    public function beginHelper()
    {
        $this->localeCatalog = new LocaleCatalog(
            'innomatic::domain_profiles',
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getLanguage()
        );
    }

    public function endHelper()
    {
    }

    public function executeNewgroup($eventData)
    {
        $tempGroup = new Group();
        $groupData['groupname'] = $eventData['groupname'];
        $tempGroup->createGroup($groupData);
    }

    public function executeRengroup($eventData)
    {
        $tempGroup = new Group($eventData['gid']);
        $groupData['groupname'] = $eventData['groupname'];
        $tempGroup->editGroup($groupData);
    }

    public function executeRemovegroup($eventData)
    {
        if ($eventData['userstoo'] == 1)
            $deleteUsersToo = true;
        else
            $deleteUsersToo = false;

        $tempGroup = new Group($eventData['gid']);
        $tempGroup->removeGroup($deleteUsersToo);
    }

    public function executeAdduser($eventData)
    {
        if ($eventData['passworda'] == $eventData['passwordb']) {
            $tempUser = new User(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->domaindata['id']);
            $userData['domainid'] = \Innomatic\Core\InnomaticContainer::instance(
                '\Innomatic\Core\InnomaticContainer'
            )->getCurrentDomain()->domaindata['id'];
            $userData['groupid'] = $eventData['groupid'];
            $userData['username'] = $eventData['username']
            . (\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getEdition() == \Innomatic\Core\InnomaticContainer::EDITION_MULTITENANT ? '@'
                .\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDomainId() : '');
            $userData['password'] = $eventData['passworda'];
            $userData['fname'] = $eventData['fname'];
            $userData['lname'] = $eventData['lname'];
            $userData['email'] = $eventData['email'];
            $userData['otherdata'] = $eventData['other'];

            $tempUser->create($userData);
        }
    }

    public function executeEdituser($eventData)
    {
        $tempUser = new User(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->domaindata['id'],
            $eventData['uid']
        );
        $userData['groupid'] = $eventData['profileid'];
        $userData['username'] = $eventData['username'];
        $userData['fname'] = $eventData['fname'];
        $userData['lname'] = $eventData['lname'];
        $userData['email'] = $eventData['email'];
        $userData['otherdata'] = $eventData['other'];

        if (!empty($eventData['oldpassword']) and !empty($eventData['passworda']) and !empty($eventData['passwordb'])) {
            if (
                ($eventData['uid'] != \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId())
                and
            (User::isAdminUser(
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserName(),
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDomainId()
            ) or \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')
                ->getCurrentUser()
                ->hasPermission('edit_password_all'))
            ) {

                if ($eventData['passworda'] == $eventData['passwordb']) {
                    $userData['password'] = $eventData['passworda'];
                }
            }
        }

        $tempUser->update($userData);

        // Roles
        $roles = \Innomatic\Domain\User\Role::getAllRoles();
        foreach ($roles as $roleId => $roleData) {
            if (isset($eventData['role_'.$roleId])) {
                $tempUser->assignRole($roleId);
            } else {
                $tempUser->unassignRole($roleId);
            }
        }
    }

    public function executeChpasswd($eventData)
    {
        if (
            ($eventData['uid'] != \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId())
            and
        !(User::isAdminUser(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserName(),
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDomainId()
        ) or \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')
            ->getCurrentUser()
            ->hasPermission('edit_password_all'))
        ) {
            return;
        }

        $tempUser = new User(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->domaindata['id'],
            $eventData['uid']
        );
        $tempUser->changePassword($eventData['password']);
    }

    public function executeRemoveuser($eventData)
    {
        $tempUser = new User(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->domaindata['id'],
            $eventData['uid']
        );
        $tempUser->remove();
    }

    public function executeEnableuser($eventData)
    {
        $tempUser = new User(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->domaindata['id'],
            $eventData['userid']
        );
        $tempUser->enable();
    }

    public function executeDisableuser($eventData)
    {
        $tempUser = new User(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->domaindata['id'],
            $eventData['userid']
        );
        $tempUser->disable();
    }

    public function executeEnablenode($eventData)
    {
        $tempPerm = new \Innomatic\Desktop\Auth\DesktopPanelAuthorizator(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(),
            $eventData['gid']
        );
        $tempPerm->enable($eventData['node'], $eventData['ntype']);
    }

    public function executeDisablenode($eventData)
    {

        $tempPerm = new \Innomatic\Desktop\Auth\DesktopPanelAuthorizator(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(),
            $eventData['gid']
        );
        $tempPerm->disable($eventData['node'], $eventData['ntype']);
    }

    public function executeSetmotd($eventData)
    {
        if (
            User::isAdminUser(
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserName(),
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDomainId()
            ) or
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')
            ->getCurrentUser()
            ->hasPermission('edit_motd')
        ) {
            $domain = new \Innomatic\Domain\Domain(
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDomainId(),
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()
            );

            $domain->setMotd($eventData['motd']);
            $this->status = $this->localeCatalog->getStr('motd_set.status');

            $this->setChanged();
            $this->notifyObservers('status');
        }
    }

    public function executeAddrole($eventData)
    {
        $role = new \Innomatic\Domain\User\Role();
        $role->add($eventData['name'], $eventData['name'], $eventData['description']);
    }

    public function executeRemoverole($eventData)
    {
        $role = new \Innomatic\Domain\User\Role((int)$eventData['id']);
        $role->remove();
    }

    public function executeEditrole($eventData)
    {
        $role = new \Innomatic\Domain\User\Role((int)$eventData['id']);
        $role
            ->setName($eventData['name'])
            ->setTitle($eventData['name'])
            ->setDescription($eventData['description'])
            ->store();
    }

    public static function ajaxSaveRolesPermissions($permissions) {
        // Build list of checked roles/permissions
        $permissions = explode(',', $permissions);
        $checkedPermissions = array();
        foreach ($permissions as $id => $permission) {
            $permission = str_replace('cbrole_', '', $permission);
            list($roleId, $permissionId) = explode('-', $permission);
            $checkedPermissions[$roleId][$permissionId] = true;
        }

        // Get list of all roles and permissions
        $rolesList = \Innomatic\Domain\User\Role::getAllRoles();
        $permissionsList = \Innomatic\Domain\User\Permission::getAllPermissions();

        // Check which permissions have been checked
        foreach ($rolesList as $roleId => $roleData) {
            $role = new \Innomatic\Domain\User\Role($roleId);

            foreach ($permissionsList as $permissionId => $permissionData) {
                if (isset($checkedPermissions[$roleId][$permissionId])) {
                    $role->assignPermission($permissionId);
                } else {
                    $role->unassignPermission($permissionId);
                }
            }
        }

        $html = WuiXml::getContentFromXml('', \ProfilesPanelController::getRolesPermissionsXml());

        $objResponse = new XajaxResponse();
        $objResponse->addAssign("roleslist", "innerHTML", $html);

        return $objResponse;
    }
}
