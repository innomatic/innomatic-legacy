<?php
namespace Innomatic\Domain\Role;

/**
 * RBACManager class, provides NIST Level 2 Standard Hierarchical Role Based
 * Access Control
 * Has three members, Roles, Users and Permissions for specific operations
 *
 * @author abiusx
 * @version 1.0
 */
class Manager
{
    function __construct()
    {
        $this->Roles = new RoleManager ();
        $this->Permissions = new PermissionManager ();
    }
    /**
     *
     * @var \jf\PermissionManager
     */
    public $Permissions;
    /**
     *
     * @var \jf\RoleManager
     */
    public $Roles;

    /**
     * Assign a role to a permission.
     * Alias for what's in the base class
     *
     * @param string|integer $Role
     *        	path or string title or integer id
     * @param string|integer $Permission
     *        	path or string title or integer id
     * @return boolean
     */
    function Assign($Role, $Permission)
    {
        if (is_int ( $Permission ))
        {
            $permissionid = $Permission;
        }
        else
        {
            if (substr ( $Permission, 0, 1 ) == "/")
                $permissionid = $this->Permissions->PathID ( $Permission );
            else
                $permissionid = $this->Permissions->TitleID ( $Permission );
        }
        if (is_int ( $Role ))
        {
            $roleid = $Role;
        }
        else
        {
            if (substr ( $Role, 0, 1 ) == "/")
                $roleid = $this->Roles->PathID ( $Role );
            else
                $roleid = $this->Roles->TitleID ( $Role );
        }

        return $this->Roles->Assign ( $roleid, $permissionid );
    }

    /**
     * Prepared statement for check query
     *
     * @var BaseDatabaseStatement
     */
    private $ps_Check = null;

    /**
     * Checks whether a user has a permission or not.
     *
     * @param string|integer $Permission
     *        	you can provide a path like /some/permission, a title, or the
     *        	permission ID.
     *        	in case of ID, don't forget to provide integer (not a string
     *        	containing a number)
     * @param string|integer $UserID
     *        	User ID of a user
     *
     * @throws PermissionNotFoundException
     * @return boolean
     */
    function Check($Permission, $UserID = null)
    {
        // convert permission to ID
        if (is_int ( $Permission ))
        {
            $permissionid = $Permission;
        }
        else
        {
            if (substr ( $Permission, 0, 1 ) == "/")
                $permissionid = $this->Permissions->PathID ( $Permission );
            else
                $permissionid = $this->Permissions->TitleID ( $Permission );
        }

        // if invalid, throw exception
        if ($permissionid === null)
            throw new PermissionNotFoundException ( "The permission '{$Permission}' not found." );


        $LastPart="ON ( TR.ID = TRel.roleid)
 							WHERE
 							TUrel.UserID=?
 							AND
 							TPdirect.ID=?";

        $Res=jf::SQL ( "SELECT COUNT(*) AS Result
            FROM
            domain_users_roles AS TUrel

            JOIN domain_roles AS TRdirect ON (TRdirect.ID=TUrel.roleid)
            JOIN domain_roles AS TR ON ( TR.left BETWEEN TRdirect.left AND TRdirect.right)
            JOIN
            (	domain_permissions AS TPdirect
            JOIN domain_permissions AS TP ON ( TPdirect.left BETWEEN TP.left AND TP.right)
            JOIN domain_roles_permissions AS TRel ON (TP.ID=TRel.permissionid)
        ) $LastPart",
            $UserID, $permissionid );

        return $Res [0] ['Result'] >= 1;
    }

    /**
     * Enforce a permission on a user
     *
     * @param string|integer $Permission
     *        	path or title or ID of permission
     *
     * @param integer $UserID
     */
    function Enforce($Permission, $UserID)
    {
        if (! $this->Check($Permission, $UserID)) {
            header('HTTP/1.1 403 Forbidden');
            die("<strong>Forbidden</strong>: You do not have permission to access this resource.");
        }

        return true;
    }

    /**
     * Remove all roles, permissions and assignments
     * mostly used for testing
     *
     * @param boolean $Ensure
     *        	must set or throws error
     * @return boolean
     */
    function Reset($Ensure = false)
    {
        if ($Ensure !== true)
        {
            throw new \Exception ("You must pass true to this function, otherwise it won't work.");
            return;
        }
        $res = true;
        $res = $res and $this->Roles->ResetAssignments ( true );
        $res = $res and $this->Roles->Reset ( true );
        $res = $res and $this->Permissions->Reset ( true );
        $res = $res and $this->Users->ResetAssignments ( true );
        return $res;
    }
}