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
 * @license    http://www.innomaticplatform.com/license/   BSD License
 * @link       http://www.innomaticplatform.com
 */
namespace Innomatic\Domain\User;

use \Innomatic\Core;

/**
 * User role.
 * 
 * @since Class available since Release 6.4.0
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 */
class Role
{
    /** @type \Innomatic\Dataaccess\DataAccess $dataAccess */
    protected $dataAccess;

    /** @type int $id */
    public $id;

    /** @type string $name is the role internal identifier. */
    public $name;

    /** @type string $title is the role human readable name. */
    public $title;

    /** @type string $description is the role extended description. */
    public $description;

    /** @type string $application is the application identifier name. */
    public $application;

    /** @type string $catalog is the catalog name used for localization of role
     * parameters, when set by an application. */
    protected $catalog;

    /**
     * Constructs the object.
     * 
     * @param int $id Optional role id number.
     */
    public function __construct($id = null)
    {
        // Gets dataaccess from Innomatic DIC
        $this->dataAccess = InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess();
        
        if (is_int($id)) {
            $roleData = $this->dataAccess->execute('SELECT * FROM domain_roles WHERE id=' . $id);
            
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
                    $localeCatalog = new \Innomatic\Locale\LocaleCatalog($this->catalog, InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getLanguage());
                    
                    $this->title = $localeCatalog->getStr($this->title);
                    $this->description = $localeCatalog->getStr($this->description);
                }
            }
        }
    }

    /**
     * Creates a new role.
     * 
     * This method creates a new role. When called by the role component
     * handler (because the role is defined in application structure) it
     * accepts the optional $catalog and $description parameters.
     * 
     * When the role is added at runtime (eg. by a domain user) the $catalog
     * and $application parameters must be empty and $name and $title
     * parameters may contain the same value.
     * 
     * @param string $name Role identifier name.
     * @param string $title Role human readable name.
     * @param string $description Role description.
     * @param string $catalog Role catalog, when set by a role component.
     * @param string $application Application defining the role, when set by a role component.
     * 
     * @return boolean True if the role has been successfully added.
     */
    public function add($name, $title, $description, $catalog = '', $application = '')
    {
        $id = $this->dataAccess->getNextSequenceValue('domain_roles_id_seq');
        
        $result = $this->dataAccess->execute("INSERT INTO domain_roles
            (id, name, title, description, catalog, application)
            VALUES ({$id}," . $this->dataAccess->formatText($name) . ',' . $this->dataAccess->formatText($title) . ',' . $this->dataAccess->formatText($description) . ',' . $this->dataAccess->formatText($catalog) . ',' . $this->dataAccess->formatText($application) . ")");
        
        if (! $result) {
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
            $localeCatalog = new \Innomatic\Locale\LocaleCatalog($this->catalog, InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getLanguage());
            
            $this->title = $localeCatalog->getStr($this->title);
            $this->description = $localeCatalog->getStr($this->description);
        }
        
        return true;
    }

    /**
     * Role name setter.
     * 
     * @param string $name
     * @return \Innomatic\Domain\User\Role
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Role description setter.
     * 
     * @param string $description
     * @return \Innomatic\Domain\User\Role
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Role title setter.
     * 
     * @param string $title
     * @return \Innomatic\Domain\User\Role
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Role catalog setter in Innomatic catalog format.
     * 
     * @param string $catalog
     * @return \Innomatic\Domain\User\Role
     */
    public function setCatalog($catalog)
    {
        $this->catalog = $catalog;
        return $this;
    }

    /**
     * Role application setter (it must be an existing application
     * identifier string).
     * 
     * @param string $application
     * @return \Innomatic\Domain\User\Role
     */
    public function setApplication($application)
    {
        $this->application = $application;
        return $this;
    }

    /**
     * Stores the changed properties of the role.
     * 
     * This method must be called after a series of role setters, in order
     * to save the new values. 
     * 
     * @return boolean True when the role has been successfully updated.
     */
    public function store()
    {
        if (! is_int($this->id)) {
            return false;
        }
        
        return $this->dataAccess->execute("UPDATE domain_roles
            SET name=" . $this->dataAccess->formatText($this->name) . ",
            title=" . $this->dataAccess->formatText($this->title) . ",
            description=" . $this->dataAccess->formatText($this->description) . ",
            catalog=" . $this->dataAccess->formatText($this->catalog) . ",
            application=" . $this->dataAccess->formatText($this->application) . "
            WHERE id={$this->id}");
    }

    /**
     * Removes the current role.
     * 
     * Removes the current role, any users relations to the role and any
     * permissions relations to the role.
     * 
     * @return boolean True if the role has been successfully removed.
     */
    public function remove()
    {
        if (! is_int($this->id)) {
            return false;
        }
        
        // Remove the role from the roles table
        $removeRoleQuery = $this->dataAccess->execute('DELETE FROM domain_roles WHERE id=' . $this->id);
        
        // Remove any users relations to the role
        $removeUsersRoleQuery = $this->dataAccess->execute('DELETE FROM domain_users_roles WHERE roleid=' . $this->id);
        
        // Remove any permissions relations to the role
        $removePermissionsRoleQuery = $this->dataAccess->execute("DELETE FROM domain_roles_permissions WHERE roleid={$this->id}");
        
        return $removeRoleQuery && $removeUsersRoleQuery && $removePermissionsRoleQuery;
    }

    /**
     * Returns the role id.
     * 
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the role name.
     * 
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the role title.
     * 
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Returns the role description.
     * 
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns the enabled permissions for the current role.
     * 
     * @return boolean|array An array of enabled permissions or false if the role is not defined.
     */
    public function getPermissions()
    {
        if (! is_int($this->id)) {
            return false;
        }
        
        $permissionQuery = $this->dataAccess->execute("SELECT id,name FROM domain_permissions AS perms
            JOIN domain_roles_permissions AS rp ON perms.id=rp.permissionid
            WHERE rp.roleid={$this->id}");
        
        // Build the permissions list
        $permissions = array();
        
        if ($permissionQuery !== false) {
            while (! $permissionQuery->eof) {
                $permissions[$permissionQuery->getFields('id')] = $permissionQuery->getFields('name');
                $permissionQuery->moveNext();
            }
        }
        
        return $permissions;
    }

    /**
     * Tells if the role has the given permission.
     * 
     * @param int|string $permission Permission identifier number or string.
     * @return boolean True if the permission is enabled.
     */
    public function hasPermission($permission)
    {
        if (! is_int($this->id)) {
            return false;
        }
        
        // If the permission has been given by name, get its id
        if (! is_int($permission)) {
            $permission = Permission::getIdFromName($permission);
            if ($permission === false) {
                return false;
            }
        }
        
        $permissionQuery = $this->domainDA->execute("SELECT count(*) AS count
            FROM domain_roles_permissions
            WHERE roleid={$this->id} AND permissionid={$permission}");
        
        if ($permissionQuery->getFields('count') > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Assigns a permission to the role.
     * 
     * @param int|string $permission Permission identifier number or string.
     * @return boolean True if the permission has been successfully added or if it has been already assigned.
     */
    public function assignPermission($permission)
    {
        if (! is_int($this->id)) {
            return false;
        }
        
        // If the permission has been given by name, get its id
        if (! is_int($permission)) {
            $permission = Permission::getIdFromName($permission);
            if ($permission === false) {
                return false;
            }
        }
        
        $check_query = $this->dataAccess->execute("SELECT count(*) AS count
            FROM domain_roles_permissions
            WHERE roleid={$this->id} AND permissionid={$permission}");
        
        if ($check_query->getFields('count') > 0) {
            // This permission has already been assigned
            return true;
        }
        
        return $this->dataAccess->execute("INSERT INTO domain_roles_permissions
            (roleid,permissionid)
            VALUES ({$this->id}, {$permission})");
    }

    /**
     * Unassigns a permission to the role.
     *
     * @param int|string $permission Permission identifier number or string.
     * @return boolean True if the permission has been successfully unassigned or if it has been already unassigned.
     */
    public function unassignPermission($permission)
    {
        if (! is_int($this->id)) {
            return false;
        }
        
        // If the permission has been given by name, get its id
        if (! is_int($permission)) {
            $permission = Permission::getIdFromName($permission);
            if ($permission === false) {
                return false;
            }
        }
        
        return $this->dataAccess->execute("DELETE FROM domain_roles_permissions
            WHERE roleid={$this->id} AND permissionid={$permission}");
    }

    /**
     * Assigns a user to the role.
     * 
     * @param int $user User identifier number.
     * @return boolean True if the user has been successfully assigned to the role.
     */
    public function assignUser($user)
    {
        if (! is_int($this->id)) {
            return false;
        }
        
        $user = new User(InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->domainserial, $user);
        
        return $user->assignRole($this->id);
    }

    /**
     * Unassigns a user to the role.
     *
     * @param int $user User identifier number.
     * @return boolean True if the user has been successfully unassigned to the role.
     */
    public function unassignUser($user)
    {
        if (! is_int($this->id)) {
            return false;
        }
        
        $user = new User(InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->domainserial, $user);
        
        return $user->unassignRole($this->id);
    }

    /**
     * Returns all the roles defined by the given application.
     * 
     * @param string $application Application identifier name.
     * @return array An array of roles.
     */
    public function getRolesByApplication($application)
    {        
        $rolesQuery = $this->dataAccess->execute("SELECT id FROM domain_roles WHERE application=" . $this->dataAccess->formatText($application));
        
        // Build the roles list
        $roles = array();
        
        if ($rolesQuery !== false) {
            while (! $rolesQuery->eof) {
                $roles[] = $rolesQuery->getFields('id');
                $rolesQuery->moveNext();
            }
        }
        
        return $roles;
    }

    /**
     * Returns an array of all the defined roles.
     * 
     * @return array
     */
    public static function getAllRoles()
    {
        $dataAccess = InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess();
        
        $rolesQuery = $dataAccess->execute("SELECT * FROM domain_roles ORDER BY application,title");
        
        // Build the roles list
        $roles = array();
        
        if ($rolesQuery !== false) {
            $catalog = '';
            
            while (! $rolesQuery->eof) {
                $title = $rolesQuery->getFields('title');
                $description = $rolesQuery->getFields('description');
                
                if (strlen($rolesQuery->getFields('catalog')) > 0) {
                    if ($rolesQuery->getFields('catalog') != $catalog) {
                        $localeCatalog = new \Innomatic\Locale\LocaleCatalog($rolesQuery->getFields('catalog'), InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getLanguage());
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

    /**
     * Returns the id number of a role by its name.
     * 
     * @param string $name Role internal name.
     * @return int|bool Role id number or false if the role with the given name doesn't exists.
     */
    public static function getIdFromName($name)
    {
        $dataAccess = InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess();
        
        $idQuery = $dataAccess->execute("SELECT id FROM domain_roles WHERE name=" . $dataAccess->formatText($name));
        
        if ($idQuery->getNumberRows() == 0) {
            return false;
        }
        
        return $idQuery->getFields('id');
    }
}
