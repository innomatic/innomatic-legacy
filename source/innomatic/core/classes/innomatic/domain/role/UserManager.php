<?php
namespace Innomatic\Domain\Role;

/**
 * RBAC User Manager
 * holds specific operations for users
 *
 * @author abiusx
 * @version 1.0
 */
class UserManager
{

    /**
     * Remove all role-user relations
     * mostly used for testing
     *
     * @param boolean $Ensure
     *            must set or throws error
     * @return number of deleted relations
     */
    function ResetAssignments($Ensure = false)
    {
        if ($Ensure !== true) {
            throw new \Exception("You must pass true to this function, otherwise it won't work.");
            return;
        }
        $res = jf::SQL("DELETE FROM domain_users_roles");
        
        jf::SQL("ALTER TABLE domain_users_roles AUTO_INCREMENT =1 ");
        $this->Assign("root", 1 /* root user */ );
        return $res;
    }
}