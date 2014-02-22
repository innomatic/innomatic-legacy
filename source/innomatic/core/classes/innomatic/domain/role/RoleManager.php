<?php
namespace Innomatic\Domain\Role;

/**
 * RBAC Role Manager
 * it has specific functions to the roles
 *
 * @author abiusx
 * @version 1.0
 */
class RoleManager extends Base
{
    /**
     * Roles Nested Set
     *
     * @var FullNestedSet
     */
    protected $domain_roles = null;
    protected function type()
    {
        return "domain_roles";
    }
    function __construct()
    {
        $this->type = "domain_roles";
        $this->domain_roles = new \Innomatic\Dataaccess\Nestedset\FullNestedSet ( "domain_roles", "ID", "left", "right" );
    }

    /**
     * Remove a role from system
     *
     * @param integer $ID
     *        	role id
     * @param boolean $Recursive
     *        	delete all descendants
     *
     */
    function Remove($ID, $Recursive = false)
    {
        $this->UnassignPermissions ( $ID );
        $this->UnassignUsers ( $ID );
        if (! $Recursive)
            return $this->domain_roles->DeleteConditional ( "ID=?", $ID );
        else
            return $this->domain_roles->DeleteSubtreeConditional ( "ID=?", $ID );
    }
    /**
     * Unassigns all permissions belonging to a role
     *
     * @param integer $ID
     *        	role ID
     * @return integer number of assignments deleted
     */
    function UnassignPermissions($ID)
    {
        $r = jf::SQL ( "DELETE FROM domain_roles_permissions WHERE
			roleid=? ", $ID );
        return $r;
    }
    /**
     * Unassign all users that have a certain role
     *
     * @param integer $ID
     *        	role ID
     * @return integer number of deleted assignments
     */
    function UnassignUsers($ID)
    {
        return jf::SQL ( "DELETE FROM domain_users_roles WHERE
			roleid=?", $ID );
    }

    /**
     * Checks to see if a role has a permission or not
     *
     * @param integer $Role
     *        	ID
     * @param integer $Permission
     *        	ID
     * @return boolean
     *
     * @todo: If we pass a Role that doesn't exist the method just returns false. We may want to check for a valid Role.
     */
    function HasPermission($Role, $Permission)
    {
        $Res = jf::SQL ( "
					SELECT COUNT(*) AS Result
					FROM domain_roles_permissions AS TRel
					JOIN domain_permissions AS TP ON ( TP.ID= TRel.permissionid)
					JOIN domain_roles AS TR ON ( TR.ID = TRel.roleid)
					WHERE TR.left BETWEEN
					(SELECT left FROM domain_roles WHERE ID=?)
					AND
					(SELECT right FROM domain_roles WHERE ID=?)
					/* the above section means any row that is a descendants of our role (if descendant roles have some permission, then our role has it two) */
					AND TP.ID IN (
					SELECT parent.ID
					FROM domain_permissions AS node,
					domain_permissions AS parent
					WHERE node.left BETWEEN parent.left AND parent.right
					AND ( node.ID=? )
					ORDER BY parent.left
					);
					/*
					the above section returns all the parents of (the path to) our permission, so if one of our role or its descendants
					has an assignment to any of them, we're good.
					*/
					", $Role, $Role, $Permission );
        return $Res [0] ['Result'] >= 1;
    }
    /**
     * Returns all permissions assigned to a role
     *
     * @param integer $Role
     *        	ID
     * @param boolean $OnlyIDs
     *        	if true, result would be a 1D array of IDs
     * @return Array 2D or 1D or null
     *         the two dimensional array would have ID,Title and Description of permissions
     */
    function Permissions($Role, $OnlyIDs = true)
    {
        if ($OnlyIDs)
        {
            $Res = jf::SQL ( "SELECT permissionid AS `ID` FROM domain_roles_permissions WHERE roleid=? ORDER BY permissionid", $Role );
            if (is_array ( $Res ))
            {
                $out = array ();
                foreach ( $Res as $R )
                    $out [] = $R ['ID'];
                return $out;
            }
            else
                return null;
        }
        else
            return jf::SQL ( "SELECT `TP`.* FROM domain_roles_permissions AS `TR`
			RIGHT JOIN domain_permissions AS `TP` ON (`TR`.permissionid=`TP`.ID)
			WHERE roleid=? ORDER BY TP.permissionid", $Role );
    }
}
