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

class Permission
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
        $this->dataAccess = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')
            ->getCurrentDomain()
            ->getDataAccess();
        
        if (is_int($id)) {
            $permissionData = $this->dataAccess->execute('SELECT * FROM domain_permissions WHERE id='.$id);

            // Check if the given permission id really exists
            if ($permissionData->getNumberRows() > 0) {
                $this->id = $id;
                $this->catalog = $permissionData->getFields('catalog');
                $this->name = $permissionData->getFields('name');
                $this->title = $permissionData->getFields('title');
                $this->description = $permissionData->getFields('description');
                $this->application = $permissionData->getFields('application');
                
                // If the permissions has been created by an Innomatic component it should have
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
        $id = $this->dataAccess->getNextSequenceValue('domain_permissions_id_seq');
        
        $result = $this->dataAccess->execute(
            "INSERT INTO domain_permissions
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
        
        return $this->dataAccess(
            "UPDATE domain_permissions
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
        
        // Remove the permissions from the permissions table
        $removePermissionQuery = $this->dataAccess->execute("DELETE FROM domain_permissions WHERE id={$this->id}");
        
        // Remove any roles relations to the permission
        $removeRolesPermissionQuery = $this->dataAccess->execute("DELETE FROM domain_roles_permissions WHERE permissionid={$this->id}");
        
        return $removePermissionQuery && $removeRolesPermissionQuery;
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
    
    public function getRoles()
    {
        if (!is_int($this->id)) {
            return false;
        }
        
        $query = $this->dataAccess->execute("SELECT roleid
            FROM domain_roles_permissions
            WHERE permissionid={$this->id}");
    }
    
    public function assignRole($role)
    {
        if (!is_int($this->id)) {
            return false;
        }
        
        // If the role has been given by name, get its id
        if (!is_int($role)) {
            $role = Role::getIdFromName($role);
            if ($role === false) {
                return false;
            }
        }
        
        // Check if the role has been already assigned
        $assignCheck = $this->dataAccess->execute(
            "SELECT count(*) AS count
            FROM domain_roles_permissions
            WHERE roleid={$role} and permissionid={$this->id}"
        );
        
        if ($assignCheck->getFields('count') > 0) {
            // The role has been already assigned
            return true;
        }
        
        // Assign the role
        return $this->dataAccess->execute("INSERT INTO domain_roles_permissions
            (roleid,permissionid)
            VALUES ({$role}, {$this->id})");
    }
    
    public function unassignRole($role)
    {
        if (!is_int($this->id)) {
            return false;
        }
        
        // If the role has been given by name, get its id
        if (!is_int($role)) {
            $role = Role::getIdFromName($role);
            if ($role === false) {
                return false;
            }
        }
        
        return $this->dataAccess->execute(
            "DELETE
            FROM domain_roles_permissions
            WHERE roleid={$role} and permissionid={$this->id}"
        );
    }
    
    public static function getAllPermissions()
    {
        $dataAccess = InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')
            ->getCurrentDomain()
            ->getDataAccess();
    
        $permissionsQuery = $dataAccess->execute("SELECT * FROM domain_permissions ORDER BY application,title");
    
        // Build the permissions list
        $permissions = array();
    
        if ($permissionsQuery !== false) {
            $catalog = '';
    
            while (!$permissionsQuery->eof) {
                $title = $permissionsQuery->getFields('title');
                $description = $permissionsQuery->getFields('description');
    
                if (strlen($permissionsQuery->getFields('catalog')) > 0) {
                    if ($permissionsQuery->getFields('catalog') != $catalog) {
                        $localeCatalog = new \Innomatic\Locale\LocaleCatalog(
                            $permissionsQuery->getFields('catalog'),
                            InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')
                                ->getCurrentUser()
                                ->getLanguage()
                        );
                    }
    
                    $title = $localeCatalog->getStr($title);
                    $description = $localeCatalog->getStr($description);
                }
    
                $catalog = $permissionsQuery->getFields('catalog');
    
                $permissions[$permissionsQuery->getFields('id')] = array(
                    'name' => $permissionsQuery->getFields('name'),
                    'title' => $title,
                    'description' => $description,
                    'application' => $permissionsQuery->getFields('application')
                );
                
                $permissionsQuery->moveNext();
            }
        }
    
        return $permissions;
    }
    
    public static function getIdFromName($name)
    {
        $dataAccess = InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')
        ->getCurrentDomain()
        ->getDataAccess();
    
        $idQuery = $dataAccess->execute("SELECT id FROM domain_permissions WHERE name=".$dataAccess->formatText($name));
    
        if ($idQuery->getNumberRows() == 0) {
            return false;
        }
    
        return $idQuery->getFields('id');
    }
}