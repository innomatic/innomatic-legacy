<?php
namespace Innomatic\Domain\Role;

/**
 * RBAC Permission Manager
 * holds specific operations for permissions
 *
 * @author abiusx
 * @version 1.0
 */
class PermissionManager extends Base
{

    /**
     * Permissions Nested Set
     *
     * @var FullNestedSet
     */
    protected $domain_permissions;

    protected function type()
    {
        return "domain_permissions";
    }

    function __construct()
    {
        parent::_construct();
        $this->domain_permissions = new \Innomatic\Dataaccess\Nestedset\FullNestedSet($this->domainda, "domain_permissions", "id", "left", "right");
    }

    /**
     * Remove a permission from system
     *
     * @param integer $ID
     *            permission id
     * @param boolean $Recursive
     *            delete all descendants
     *            
     */
    function Remove($ID, $Recursive = false)
    {
        $this->UnassignRoles($ID);
        if (! $Recursive)
            return $this->domain_permissions->DeleteConditional("ID=" . $ID);
        else
            return $this->domain_permissions->DeleteSubtreeConditional("ID=" . $ID);
    }

    /**
     * Unassignes all roles of this permission, and returns their number
     *
     * @param integer $ID            
     * @return integer
     */
    function UnassignRoles($ID)
    {
        $res = jf::SQL("DELETE FROM domain_roles_permissions WHERE
			permissionid=" . $ID);
        return (int) $res;
    }

    /**
     * Returns all roles assigned to a permission
     *
     * @param integer $Permission
     *            ID
     * @param boolean $OnlyIDs
     *            if true, result would be a 1D array of IDs
     * @return Array 2D or 1D or null
     */
    function Roles($Permission, $OnlyIDs = true)
    {
        if (! is_numeric($Permission))
            $Permission = $this->Permission_ID($Permission);
        if ($OnlyIDs) {
            $Res = jf::SQL("SELECT roleid AS id FROM
				domain_roles_permissions WHERE permissionid={$Permission} ORDER BY roleid");
            if (is_array($Res)) {
                $out = array();
                foreach ($Res as $R)
                    $out[] = $R['ID'];
                return $out;
            } else
                return null;
        } else
            return jf::SQL("SELECT `TP`.* FROM domain_roles_permissions AS `TR`
				RIGHT JOIN domain_roles AS `TP` ON (`TR`.roleid=`TP`.ID)
				WHERE permissionid={$Permission} ORDER BY TP.roleid");
    }
}