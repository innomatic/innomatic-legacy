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
 */
namespace Innomatic\Domain\User;

use \Innomatic\Core;

/**
 * User permission.
 *
 * Permissions can be added by an application or manually by a tenant user with
 * enough permissions. In the former case, the permission title that users can
 * see in the desktop is defined in the component attributes in the application
 * definition file and is localized using a catalog, while in the latter case
 * the title is set manually and is not localized.
 *
 * @since Class available since Release 6.4.0
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 */
class Permission
{
    /**
     * Tenant data access handler.
     *
     * @var \Innomatic\Dataaccess\DataAccess $dataAccess
     * @access protected
     */
    protected $dataAccess;

    /**
     * Internal permission identifier number.
     *
     * @var integer
     * @access public
     */
    public $id;

    /**
     * Permission internal identifier string.
     *
     * @var string
     * @access public
     */
    public $name;

    /**
     * Title of the permission that users can see in the desktop.
     *
     * When the permission has been installed through the application definition
     * file, this is the key to be used in the locale catalog.
     *
     * @var string
     * @access public
     */
    public $title;

    /**
     * Extended permission description.
     *
     * @var string
     * @access public
     */
    public $description;

    /**
     * Application identifier name to be used when the permission
     * is installed by an application.
     *
     * @var mixed
     * @access public
     */
    public $application;

    /**
     * Catalog to be used to localize the permission title when the
     * permission is installed by an application.
     *
     * @var mixed
     * @access protected
     */
    protected $catalog;

    /* public __construct($id = null) {{{ */
    /**
     * Constructs the object.
     *
     * @param int $id Optional permission id number.
     * @access public
     * @return void
     */
    public function __construct($id = null)
    {
        // Gets dataaccess from Innomatic DIC
        $this->dataAccess = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')
            ->getCurrentDomain()
            ->getDataAccess();

        if (is_int($id)) {
            // Extract the permission data from the database
            $permissionData = $this->dataAccess->execute(
                'SELECT * FROM domain_permissions WHERE id='.$id
            );

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
    /* }}} */

    /* public add($name, $title, $description, $catalog = '', $application = '') {{{ */
    /**
     * Add a new permission type.
     *
     * @param string $name Permission internal name.
     * @param string $title Permission title to be used in the UI.
     * @param string $description Permission description.
     * @param string $catalog Catalog name to be used to get the title string
     * when the permission has been installed by the permission component.
     * @param string $application Application identifier name required when
     * the permission has been installed by the permission component.
     * @access public
     * @return void
     */
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
                    ->getCurrentDomain()
                    ->getLanguage()
            );

            $this->title = $localeCatalog->getStr($this->title);
            $this->description = $localeCatalog->getStr($this->description);
        }

        return true;
    }
    /* }}} */

    /* public setName($name) {{{ */
    /**
     * Sets the permission name
     *
     * @param string $name Permission name.
     * @access public
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    /* }}} */

    /* public setDescription($description) {{{ */
    /**
     * Sets the permission description.
     *
     * @param string $description Permission description.
     * @access public
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }
    /* }}} */

    /* public setTitle($title) {{{ */
    /**
     * Sets the permission title.
     *
     * @param string $title Permission title.
     * @access public
     * @return self
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }
    /* }}} */

    /* public setCatalog($catalog) {{{ */
    /**
     * Sets the title catalog.
     *
     * @param string $catalog Locale catalog.
     * @access public
     * @return self
     */
    public function setCatalog($catalog)
    {
        $this->catalog = $catalog;
        return $this;
    }
    /* }}} */

    /* public setApplication($application) {{{ */
    /**
     * Sets the permission application.
     *
     * @param string $application Application identifier name.
     * @access public
     * @return self
     */
    public function setApplication($application)
    {
        $this->application = $application;
        return $this;
    }
    /* }}} */

    /* public store() {{{ */
    /**
     * Stores the permission data in the database.
     *
     * To be used after changing permission attributes with the set methods.
     *
     * @access public
     * @return void
     */
    public function store()
    {
        if (!is_int($this->id)) {
            return false;
        }

        // Update the permission in the database
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
    /* }}} */

    /* public remove() {{{ */
    /**
     * Permanently removes the permission from the database and all its
     * assigned roles.
     *
     * @access public
     * @return boolean
     */
    public function remove()
    {
        if (!is_int($this->id)) {
            return false;
        }

        // Remove the permissions from the permissions table
        $removePermissionQuery = $this->dataAccess->execute(
            "DELETE FROM domain_permissions"
            ." WHERE id={$this->id}"
        );

        // Remove any roles relations to the permission
        $removeRolesPermissionQuery = $this->dataAccess->execute(
            "DELETE FROM domain_roles_permissions"
            ." WHERE permissionid={$this->id}"
        );

        return $removePermissionQuery && $removeRolesPermissionQuery;
    }
    /* }}} */

    /* public getId() {{{ */
    /**
     * Gets permission id.
     *
     * @access public
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
    /* }}} */

    /* public getName() {{{ */
    /**
     * Gets permission name.
     *
     * @access public
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    /* }}} */

    /* public getTitle() {{{ */
    /**
     * Gets permission title.
     *
     * @access public
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
    /* }}} */

    /* public getDescription() {{{ */
    /**
     * Gets permission description.
     *
     * @access public
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
    /* }}} */

    /* public getRoles() {{{ */
    /**
     * Gets the assigned roles for the current permission.
     *
     * @access public
     * @return \Innomatic\Dataaccess\DataAccessResult
     */
    public function getRoles()
    {
        if (!is_int($this->id)) {
            return false;
        }

        $query = $this->dataAccess->execute(
            "SELECT roleid"
            ." FROM domain_roles_permissions"
            ." WHERE permissionid={$this->id}"
        );
    }
    /* }}} */

    /* public assignRole($role) {{{ */
    /**
     * Assigns a role to the permission.
     *
     * @param integer|string $role Role identifier number or string,
     * it gets automatically decoded to the identifier number.
     * @access public
     * @return boolean
     */
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
    /* }}} */

    /* public unassignRole($role) {{{ */
    /**
     * Removes a previously assigned role.
     *
     * @param integer|string $role Role identifier number or string,
     * it gets automatically decoded to the identifier number.
     * @access public
     * @return boolean
     */
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
    /* }}} */

    /* public getAllPermissions() {{{ */
    /**
     * Gets a list of all available permissions, with their attributes.
     *
     * @static
     * @access public
     * @return array
     */
    public static function getAllPermissions()
    {
        $dataAccess = InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')
            ->getCurrentDomain()
            ->getDataAccess();

        $permissionsQuery = $dataAccess->execute(
            "SELECT * FROM domain_permissions ORDER BY application,title"
        );

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
    /* }}} */

    /* public getIdFromName($name) {{{ */
    /**
     * Gets the permission identifier number from its name.
     *
     * @param string $name Permission name.
     * @static
     * @access public
     * @return integer
     */
    public static function getIdFromName($name)
    {
        $dataAccess = InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')
        ->getCurrentDomain()
        ->getDataAccess();

        $idQuery = $dataAccess->execute(
            "SELECT id FROM domain_permissions WHERE name=".$dataAccess->formatText($name)
        );

        if ($idQuery->getNumberRows() == 0) {
            return false;
        }

        return $idQuery->getFields('id');
    }
    /* }}} */
}
