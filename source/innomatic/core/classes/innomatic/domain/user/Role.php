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
namespace Innomatic\Domain\User;

use \Innomatic\Core;

class Role
{
    protected $dataAccess;
    public $id;
    public $name;
    public $title;
    public $description;
    public $application;
    protected $catalog;
    
    public function __construct($id = null)
    {
        // Gets dataaccess from Innomatic DIC
        $this->dataAccess = InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')
            ->getCurrentDomain()
            ->getDataAccess();
    
        if (is_int($id)) {
            $roleData = $this->dataAccess->execute('SELECT * FROM domain_roles WHERE id='.$id);

            // Check if the given role id really exists
            if ($roleData->getNumberRows() > 0) {
                $this->id = $id;
                $this->catalog = $roleData->getFields('catalog');
                $this->name = $roleData->getFields('name');
                $this->title = $roleData->getFields('title');
                $this->description = $roleData->getFields('description');
                $this->application = $roleData->getFields('application');
                
                // If the role has been created by an Innomatic component it should have
                // a catalog definition. In that case, replace the title and the description
                // with the localized versions.
                if (strlen($this->catalog)) {
                    $localeCatalog = new \Innomatic\Locale\LocaleCatalog(
                        $this->catalog,
                        InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')
                            ->getCurrentUser()
                            ->getLanguage()
                    );
                    
                    $this->title = $localeCatalog->getStr($this->title);
                    $this->description = $localeCatalog->getStr($this->description);
                }
            }
        }
    }
    
    public function add($name, $title, $description, $catalog = '', $application = '')
    {
        $id = $this->dataAccess->getNextSequenceValue('domain_roles_id_seq');
        
        $result = $this->dataAccess->execute(
            "INSERT INTO domain_roles
            (id, name, title, description, catalog, application)
            VALUES ({$id},".
            $this->dataAccess->formatText($name).','.
            $this->dataAccess->formatText($title).','.
            $this->dataAccess->formatText($description).','.
            $this->dataAccess->formatText($catalog).','.
            $this->dataAccess->formatText($application).
            ")"
        );
        
        if (!$result) {
            return false;
        }
        
        $this->id = $id;
        $this->name = $name;
        $this->title = $title;
        $this->description = $description;
        $this->application = $application;
        $this->catalog = $catalog;
        
        // If the role has been created by an Innomatic component it should have
        // a catalog definition. In that case, replace the title and the description
        // with the localized versions.
        if (strlen($this->catalog)) {
            $localeCatalog = new \Innomatic\Locale\LocaleCatalog(
                $this->catalog,
                InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')
                    ->getCurrentUser()
                    ->getLanguage()
            );
            
            $this->title = $localeCatalog->getStr($this->title);
            $this->description = $localeCatalog->getStr($this->description);
        }
        
        return true;
    }
    
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }
    
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }
    
    public function setCatalog($catalog)
    {
        $this->catalog = $catalog;
        return $this;
    }
    
    public function setApplication($application)
    {
        $this->application = $application;
        return $this;
    }
    
    public function store()
    {
        if (!is_int($this->id)) {
            return false;
        }
    
        return $this->dataAccess->execute(
            "UPDATE domain_roles
            SET name=".$this->dataAccess->formatText($this->name).",
            title=".$this->dataAccess->formatText($this->title).",
            description=".$this->dataAccess->formatText($this->description).",
            catalog=".$this->dataAccess->formatText($this->catalog).",
            application=".$this->dataAccess->formatText($this->application)."
            WHERE id={$this->id}"
        );
    }
    
    public function remove()
    {
        if (!is_int($this->id)) {
            return false;
        }
        
        // Remove the role from the roles table
        $removeRoleQuery = $this->dataAccess->execute('DELETE FROM domain_roles WHERE id='.$this->id);
        
        // Remove any users relations to the role
        $removeUsersRoleQuery = $this->dataAccess->execute('DELETE FROM domain_users_roles WHERE roleid='.$this->id);
        
        // Remove any permissions relations to the role
        $removePermissionsRoleQuery = $this->dataAccess->execute("DELETE FROM domain_roles_permissions WHERE roleid={$this->id}");
        
        return $removeRoleQuery && $removeUsersRoleQuery && $removePermissionsRoleQuery;
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function getTitle()
    {
        return $this->title;
    }
    
    public function getDescription()
    {
        return $this->description;
    }
    
    public function getPermissions()
    {
        if (!is_int($this->id)) {
            return false;
        }
        
        $permissionQuery = $this->dataAccess->execute(
            "SELECT id,name FROM domain_permissions AS perms
            JOIN domain_roles_permissions AS rp ON perms.id=rp.permissionid
            WHERE rp.roleid={$this->id}"
        );
        
        // Build the permissions list
        $permissions = array();
        
        if ($permissionQuery !== false) {
            while (!$permissionQuery->eof) {
                $permissions[$permissionQuery->getFields('id')] = $permissionQuery->getFields('name');
                $permissionQuery->moveNext();
            }
        }
        
        return $permissions;
    }
    
    public function hasPermission($permission)
    {
        if (!is_int($this->id)) {
            return false;
        }
        
        // If the permission has been given by name, get its id
        if (!is_int($permission)) {
            $permission = Permission::getIdFromName($permission);
            if ($permission === false) {
                return false;
            }
        }
        
        $permissionQuery = $this->domainDA->execute(
            "SELECT count(*) AS count
            FROM domain_roles_permissions
            WHERE roleid={$this->id} AND permissionid={$permission}"
        );
        
        if ($permissionQuery->getFields('count') > 0) {
            return true;
        } else {
            return false;
        }
    }
    
    public function assignPermission($permission)
    {
        if (!is_int($this->id)) {
            return false;
        }
        
        // If the permission has been given by name, get its id
        if (!is_int($permission)) {
            $permission = Permission::getIdFromName($permission);
            if ($permission === false) {
                return false;
            }
        }
        
        $check_query = $this->dataAccess->execute(
            "SELECT count(*) AS count
            FROM domain_roles_permissions
            WHERE roleid={$this->id} AND permissionid={$permission}"
        );
        
        if ($check_query->getFields('count') > 0) {
            // This permission has already been assigned
            return true;
        }
        
        return $this->dataAccess->execute(
            "INSERT INTO domain_roles_permissions
            (roleid,permissionid)
            VALUES ({$this->id}, {$permission})"
        );
    }
    
    public function unassignPermission($permission)
    {
        if (!is_int($this->id)) {
            return false;
        }
        
        // If the permission has been given by name, get its id
        if (!is_int($permission)) {
            $permission = Permission::getIdFromName($permission);
            if ($permission === false) {
                return false;
            }
        }
        
        return $this->dataAccess->execute(
            "DELETE FROM domain_roles_permissions
            WHERE roleid={$this->id} AND permissionid={$permission}"
        );
    }

    public function assignUser($user)
    {
        if (!is_int($this->id)) {
            return false;
        }
        
        $user = new User(
            InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')
                ->getCurrentDomain()
                ->domainserial,
            $user
        );
        
        return $user->assignRole($this->id);
    }
    
    public function unassignUser($user)
    {
        if (!is_int($this->id)) {
            return false;
        }
        
        $user = new User(
            InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')
            ->getCurrentDomain()
            ->domainserial,
            $user
        );
        
        return $user->unassignRole($this->id);
    }
    
    public function getRolesByApplication($application)
    {
        if (!is_int($this->id)) {
            return false;
        }
    
        $rolesQuery = $this->dataAccess->execute(
            "SELECT id FROM domain_roles WHERE application=".$this->dataAccess->formatText($application)
        );
    
        // Build the roles list
        $roles = array();
    
        if ($rolesQuery !== false) {
            while (!$rolesQuery->eof) {
                $roles[] = $rolesQuery->getFields('id');
                $rolesQuery->moveNext();
            }
        }
    
        return $roles;
    }
    
    public static function getAllRoles()
    {
        $dataAccess = InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')
            ->getCurrentDomain()
            ->getDataAccess();
        
        $rolesQuery = $dataAccess->execute("SELECT * FROM domain_roles ORDER BY application,title");
    
        // Build the roles list
        $roles = array();
    
        if ($rolesQuery !== false) {
            $catalog = '';
            
            while (!$rolesQuery->eof) {
                $title = $rolesQuery->getFields('title');
                $description = $rolesQuery->getFields('description');
                
                if (strlen($rolesQuery->getFields('catalog')) > 0) {
                    if ($rolesQuery->getFields('catalog') != $catalog) {
                        $localeCatalog = new \Innomatic\Locale\LocaleCatalog(
                            $rolesQuery->getFields('catalog'),
                            InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')
                            ->getCurrentUser()
                            ->getLanguage()
                        );
                    }
                    
                    $title = $localeCatalog->getStr($title);
                    $description = $localeCatalog->getStr($description);
                }
                
                $catalog = $rolesQuery->getFields('catalog');
                
                $roles[$rolesQuery->getFields('id')] = array(
                	'name' => $rolesQuery->getFields('name'),
                    'title' => $title,
                    'description' => $description,
                    'application' => $rolesQuery->getFields('application')
                );
                
                $rolesQuery->moveNext();
            }
        }
    
        return $roles;
    }
    
    public static function getIdFromName($name)
    {
        $dataAccess = InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')
            ->getCurrentDomain()
            ->getDataAccess();

        $idQuery = $dataAccess->execute("SELECT id FROM domain_roles WHERE name=".$dataAccess->formatText($name));
        
        if ($idQuery->getNumberRows() == 0) {
            return false;
        }
        
        return $idQuery->getFields('id');
    }
}