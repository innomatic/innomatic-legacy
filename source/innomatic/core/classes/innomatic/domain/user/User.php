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
 * @since      Class available since Release 5.0
*/
namespace Innomatic\Domain\User;

/*!
 @class User

 @abstract User management
 */
class User
{
    protected $container;
    protected $rootDA;
    protected $domainDA;
    protected $domainserial;
    protected $userid;
    protected $username;
    protected $userExists = false;

    /*!
     @param domainSerial integer - Domain serial number.
     @param userId integer - User id number.
     */
    public function __construct($domainSerial, $userId = 0)
    {
        $this->container = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');
        
        $this->rootDA = $this->container->getDataAccess();
        $this->domainserial = $domainSerial;
        $this->userid = $userId;

        $domain = $this->container->getCurrentDomain();
        if (!is_object($domain)) {
            $domain_query = $this->rootDA->execute('SELECT domainid FROM domains WHERE id='.$domainSerial);
            $domain = new \Innomatic\Domain\Domain($this->rootDA, $domain_query->getFields('domainid'), null);
        }
        $this->domainDA = $domain->getDataAccess();

        if ($this->userid != 0) {
            // @todo to be cached in a more elegant way eg. registry
            if (isset($GLOBALS['gEnv']['runtime']['innomatic']['users']['username_check'][(int)$this->userid])) {
                $this->username = $GLOBALS['gEnv']['runtime']['innomatic']['users']['username_check'][(int)$this->userid];
                $this->userExists = true;
            } else {
                $uquery = $this->domainDA->execute('SELECT username FROM domain_users WHERE id='.(int)$this->userid);

                if ($uquery->getNumberRows() > 0) {
                    $this->username = $uquery->getFields('username');
                    $GLOBALS['gEnv']['runtime']['innomatic']['users']['username_check'][(int)$this->userid] = $this->username;
                    $uquery->free();
                    $this->userExists = true;
                }
            }
        }
    }

    /* public exists() {{{ */
    /**
     * Tells if the user exists in users table
     *
     * @access public
     * @return boolean
     */
    public function exists()
    {
        return $this->userExists;
    }
    /* }}} */

    /*!
     @abstract Sets the user id.
     */
    public function setUserId($uid)
    {
        $this->userid = $uid;
        return true;
    }

    public function getUserId()
    {
        return $this->userid;
    }

    public function getUserName()
    {
        return $this->username;
    }

    /*!
     @function setUserIdByUsername

     @abstract Sets the user id by username.
     */
    public function setUserIdByUsername($username)
    {
        if (!empty($username)) {
            $edition = $this->container->getEdition();
            if ($edition == \Innomatic\Core\InnomaticContainer::EDITION_SAAS) {
                $formatUsername = $this->domainDA->formatText('%@'.$username);
                $sql = "SELECT id
                    FROM domain_users
                    WHERE username LIKE $formatUsername";
            } else {
                $username = $this->domainDA->formatText($username);
                $sql = "SELECT id
                    FROM domain_users
                    WHERE username = $username";
            }

            $uquery = $this->domainDA->execute($sql);
            $this->userid = $uquery->getFields('id');
            return $this->userid;
        }
        return false;
    }

    public static function getUserIdByUsername($username)
    {
        $domainDA = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess();
        if (!empty($username)) {
            $uquery = $domainDA->execute('SELECT id FROM domain_users WHERE username='.$domainDA->formatText($username));
            return $uquery->getFields('id');
        }
        return false;
    }
    /*!
     @function CreateUser

     @abstract Creates a new user.
     */
    public function create($userdata)
    {
        $result = false;
        $userdata['username'] = str_replace(':', '', $userdata['username']);
        $userdata['username'] = str_replace('|', '', $userdata['username']);
        $userdata['username'] = str_replace('/', '', $userdata['username']);
        $userdata['username'] = str_replace('\\', '', $userdata['username']);

        $hook = new \Innomatic\Process\Hook($this->rootDA, 'innomatic', 'domain.user.add');
        if ($hook->callHooks('calltime', $this, array('domainserial' => $this->domainserial, 'userdata' => $userdata)) == \Innomatic\Process\Hook::RESULT_OK) {
            if ($this->userid == 0) {
                $max_users_query = $this->rootDA->execute('SELECT maxusers,domainid FROM domains WHERE id='.$userdata['domainid']);
                $goon = true;

                if ($max_users_query->getFields('maxusers')) {
                    $users_num_query = $this->domainDA->execute('SELECT id FROM domain_users');

                    if ($users_num_query->getNumberRows() >= $max_users_query->getFields('maxusers'))
                        $goon = false;
                }

                if ($goon) {
                    // Check if the given username is unique
                    $uquery = $this->domainDA->execute('SELECT * FROM domain_users WHERE username='.$this->domainDA->formatText($userdata['username']));

                    if (($uquery->getNumberRows() == 0) & (strlen($userdata['username']) > 0) & (strlen($userdata['password']) > 0) & (strlen($userdata['groupid']) > 0)) {
                        $seqval = $this->domainDA->getNextSequenceValue('domain_users_id_seq');
                        $user = 'INSERT INTO domain_users values ( '.$seqval.',';
                        $user.= $userdata['groupid'].',';
                        $user.= $this->domainDA->formatText($userdata['username']).',';
                        $user.= $this->domainDA->formatText(md5($userdata['password'])).',';
                        $user.= $this->domainDA->formatText($userdata['fname']).',';
                        $user.= $this->domainDA->formatText($userdata['lname']).',';
                        $user.= $this->domainDA->formatText($userdata['otherdata']).',';
                        $user.= $this->domainDA->formatText($userdata['email']).',';
                        $user.= $this->domainDA->formatText($this->domainDA->fmtfalse).')';

                        $this->domainDA->execute($user);
                        $this->userid = $seqval;

                        $this->userExists = true;

                        $result = $seqval;

                        \Innomatic\Io\Filesystem\DirectoryUtils::mktree($this->container->getHome().'core/domains/'.$max_users_query->getFields('domainid').'/users/'.$userdata['username'].'/', 0755);

                        if ($hook->callHooks('useradded', $this, array('domainserial' => $this->domainserial, 'userdata' => $userdata)) != \Innomatic\Process\Hook::RESULT_OK)
                            $result = false;
                    }
                }
            }
        }

        return $result;
    }

    /*!
     @function CreateAdminUser

     @abstract Creates a new user as domain superuser.
     */
    public function createAdminUser($domainid, $domainpassword)
    {
        $domainsquery = $this->rootDA->execute('SELECT id FROM domains WHERE domainid='.$this->rootDA->formatText($domainid));

        $userdata['domainid'] = $domainsquery->getFields('id');
        $userdata['username'] = 'admin'.($this->container->getEdition() == \Innomatic\Core\InnomaticContainer::EDITION_SAAS ? '@'.$domainid : '');
        $userdata['lname'] = 'Administrator';
        $userdata['password'] = $domainpassword;
        $userdata['groupid'] = 0;
        $domainsquery->free();
        $this->create($userdata);
    }

    /*!
     @function EditUser

     @abstract Edits user data.
     */
    public function update($userdata)
    {
        $result = false;

        $hook = new \Innomatic\Process\Hook($this->rootDA, 'innomatic', 'domain.user.edit');
        if ($hook->callHooks('calltime', $this, array('domainserial' => $this->domainserial, 'userdata' => $userdata)) == \Innomatic\Process\Hook::RESULT_OK) {
            if ($this->userid != 0) {
                if ((!empty($userdata['username'])) & (strlen($userdata['groupid']) > 0)) {
                    $upd = 'UPDATE domain_users SET groupid = '.$userdata['groupid'];
                    $upd.= ', username = '.$this->domainDA->formatText($userdata['username']);
                    $upd.= ', fname = '.$this->domainDA->formatText($userdata['fname']);
                    $upd.= ', lname = '.$this->domainDA->formatText($userdata['lname']);
                    $upd.= ', otherdata = '.$this->domainDA->formatText($userdata['otherdata']);
                    $upd.= ', email = '.$this->domainDA->formatText($userdata['email']);
                    $upd.= ' WHERE id='. (int) $this->userid;

                    //$this->htp->changePassword( $userdata['username'], $userdata['password'] );

                    unset($GLOBALS['gEnv']['runtime']['innomatic']['users']['username_check'][(int)$this->userid]);
                    unset($GLOBALS['gEnv']['runtime']['innomatic']['users']['getgroup'][(int)$this->userid]);

                    $result = $this->domainDA->execute($upd);
                    if (strlen($userdata['password'])) {
                        $this->changePassword($userdata['password']);
                    }

                    if ($hook->callHooks('useredited', $this, array('domainserial' => $this->domainserial, 'userdata' => $userdata)) != \Innomatic\Process\Hook::RESULT_OK)
                        $result = false;
                } else {

                    $log = $this->container->getLogger();
                    $log->logEvent('innomatic.users.users.edituser', 'Empty username or group id', \Innomatic\Logging\Logger::WARNING);
                }
            } else {

                $log = $this->container->getLogger();
                $log->logEvent('innomatic.users.users.edituser', 'Invalid user id '.$this->userid, \Innomatic\Logging\Logger::WARNING);
            }
        }
        return $result;
    }

    /*!
     @function ChPasswd

     @abstract Changes user password.
     */
    public function changePassword($newpassword)
    {
        $result = false;

        if ($this->userid != 0) {
            $uquery = $this->domainDA->execute('SELECT username FROM domain_users WHERE id='.$this->userid);
            $squery = $this->rootDA->execute('SELECT id FROM domains WHERE domainid='.$this->rootDA->formatText($uquery->getFields('username')));

            if ($squery->getNumberRows()) {
                $empty = '';

                $tmpdomain = new \Innomatic\Domain\Domain($this->rootDA, $uquery->getFields('username'), $empty);
                $result = $tmpdomain->changePassword($newpassword);
            } else
                if (!empty($newpassword)) {
                    $upd.= 'UPDATE domain_users SET password = '.$this->domainDA->formatText(md5($newpassword)).' WHERE id='.$this->userid;
                    //$this->htp->changePassword( $uquery->getFields( 'username' ), $newpassword );
                    $result = $this->domainDA->execute($upd);
                }
        }

        return $result;
    }

    /*!
     @function getUserData

     @abstract Returns user data array.
     */
    public function getUserData()
    {
        $result = false;

        if ($this->userid != 0) {
            $uquery = $this->domainDA->execute('SELECT * FROM domain_users WHERE id='. (int) $this->userid);
            $result = $uquery->getFields();
        }

        return $result;
    }

    /*!
     @function getGroup

     @abstract Returns the user group.
     */
    public function getGroup()
    {
        $result = false;

        if ($this->userid != 0) {
            if (isset($GLOBALS['gEnv']['runtime']['innomatic']['users']['getgroup'][(int) $this->userid])) {
                $result = $GLOBALS['gEnv']['runtime']['innomatic']['users']['getgroup'][(int) $this->userid];
            } else {
                $uquery = $this->domainDA->execute('SELECT groupid FROM domain_users WHERE id='. (int) $this->userid);
                $result = $uquery->getFields('groupid');
                $GLOBALS['gEnv']['runtime']['innomatic']['users']['getgroup'][(int) $this->userid] = $result;
            }
        }

        return $result;
    }

    /*!
     @function RemoveUser

     @abstract Removes the user.
     */
    public function remove()
    {
        $hook = new \Innomatic\Process\Hook($this->rootDA, 'innomatic', 'domain.user.remove');
        if ($hook->callHooks('calltime', $this, array('domainserial' => $this->domainserial, 'userid' => $this->userid)) == \Innomatic\Process\Hook::RESULT_OK) {
            if ($this->userid != 0) {
                $result = $this->domainDA->execute('DELETE FROM domain_users WHERE id='. (int) $this->userid);

                // Remove user dir
                $domain_query = $this->rootDA->execute('SELECT domainid FROM domains WHERE id='. (int) $this->domainserial);

                if ($this->container->getHome().'core/domains/'.$domain_query->getFields('domainid').'/users/'.$this->username != $this->container->getHome().'core/domains/'.$domain_query->getFields('domainid').'/users/') {
                    \Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree($this->container->getHome().'core/domains/'.$domain_query->getFields('domainid').'/users/'.$this->username, 0755);
                }

                // Remove cached items
                $cache_gc = new \Innomatic\Datatransfer\Cache\CacheGarbageCollector();
                $cache_gc->removeUserItems((int) $this->userid);

                //$this->htp->remuser( $this->username );
                if ($hook->callHooks('userremoved', $this, array('domainserial' => $this->domainserial, 'userid' => $this->userid)) != \Innomatic\Process\Hook::RESULT_OK)
                    $result = false;
                $this->userid = 0;
                $this->userExists = false;
            }
        }

        return $result;
    }

    /*!
     @function ChangeGroup

     @abstract Changes user group.
     */
    public function changeGroup($userdata)
    {
        if (($this->userid != 0) & (!empty($userdata))) {
            $this->domainDA->execute('UPDATE domain_users SET groupid='. (int) $userdata['groupid'].' WHERE id='. (int) $this->userid);
            $GLOBALS['gEnv']['runtime']['innomatic']['users']['getgroup'][(int) $this->userid] = $userdata['groupid'];
            return true;
        }
        return false;
    }

    public static function extractDomainID($username)
    {
        $container = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');
        if ($container->getEdition() == \Innomatic\Core\InnomaticContainer::EDITION_ENTERPRISE) {
            $domain_query = $container->getDataAccess()->execute('SELECT domainid FROM domains LIMIT 1');
            if ($domain_query->getNumberRows() == 1) {
                return $domain_query->getFields('domainid');
            }
            return false;
        }
        if (strpos($username, '@') !== false) {
            return substr($username, strpos($username, '@') + 1);
        }
        return false;
    }

    public function getLanguage()
    {
        $container = $this->container;
        
        $user_settings = new \Innomatic\Domain\User\UserSettings(
            $container->getCurrentDomain()->getDataAccess(),
            $container->getCurrentUser()->getUserId());
        $lang = $user_settings->getKey('desktop-language');

        return strlen($lang) ? $lang : $container->getCurrentDomain()->getLanguage();
    }

    public function getCountry()
    {
        $user_settings = new \Innomatic\Domain\User\UserSettings(
            $this->domainDA,
            $this->container->getCurrentUser()->getUserId());
        $country = $user_settings->getKey('desktop-country');

        return strlen($country) ? $country : $this->container->getCurrentDomain()->getCountry();
    }

    public static function isAdminUser($username, $domain)
    {
        $container = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');
        $admin_username = 'admin'.($container->getEdition() == \Innomatic\Core\InnomaticContainer::EDITION_SAAS ? '@'.$domain : '');
        return $username == $admin_username ? true : false;
    }

    public static function getAdminUsername($domain)
    {
        $container = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');
        return 'admin'.($container->getEdition() == \Innomatic\Core\InnomaticContainer::EDITION_SAAS ? '@'.$domain : '');
    }

    /* public enable() {{{ */
    /**
     * Enables the user so that he can login again.
     *
     * @access public
     * @return void
     */
    public function enable()
    {
        if ($this->userid == 0) {
            return false;
        }

        $this->domainDA->execute('UPDATE domain_users SET disabled='.$this->domainDA->formatText($this->domainDA->fmtfalse).' WHERE id='.$this->userid);

        $domain = $this->container->getCurrentDomain();
        $domain->domainlog->logEvent($domain->domainid, 'Enabled user '.$this->username, \Innomatic\Logging\Logger::NOTICE);
    }
    /* }}} */

    /* public disable() {{{ */
    /**
     * Disables the user so that he can't login.
     *
     * @access public
     * @return void
     */
    public function disable()
    {
        if ($this->userid == 0) {
            return false;
        }

        $this->domainDA->execute('UPDATE domain_users SET disabled='.$this->domainDA->formatText($this->domainDA->fmttrue).' WHERE id='.$this->userid);

        $domain = $this->container->getCurrentDomain();
        $domain->domainlog->logEvent($domain->domainid, 'Disabled user '.$this->username, \Innomatic\Logging\Logger::NOTICE);
    }
    /* }}} */

    /* public isEnabled() {{{ */
    /**
     * Tells if the user is enabled.
     *
     * @access public
     * @return boolean
     */
    public function isEnabled()
    {
        if ($this->userid == 0) {
            return false;
        }

        $query = $this->domainDA->execute('SELECT disabled FROM domain_users WHERE id='.$this->userid);
        return $query->getFields('disabled') == $this->domainDA->fmttrue ? false : true;
    }
    /* }}} */
    /**
     * Checks to see whether a user has a role or not.
     *
     * @param string $role
     *        	role name
     *
     * @return boolean success
     */
    public function hasRole($role)
    {
        if (self::isAdminUser($this->username, $this->container->getCurrentDomain()->getDomainId())) {
            // Administrator user has all roles and permissions by default
            return true;
        }

        // If the role has been given by name, get its id
        if (!is_int($role)) {
            $role = Role::getIdFromName($role);
            if ($role === false) {
                return false;
            }
        }

        $query = $this->domainDA->execute('SELECT * FROM domain_users_roles AS ur
            JOIN domain_roles AS dr ON ur.roleid=dr.id
            WHERE dr.id='.$role);

        return $query->getNumberRows() > 0;
    }

    /**
     * Assigns a role to a user
     *
     * @param string $role
     *        Role name
     * @return inserted or existing
     */
    public function assignRole($role)
    {
        if (self::isAdminUser($this->username, $this->container->getCurrentDomain()->getDomainId())) {
            // Administrator user has all roles and permissions by default
            return true;
        }

        // If the role has been given by name, get its id
        if (!is_int($role)) {
            $role = Role::getIdFromName($role);
            if ($role === false) {
                return false;
            }
        }

        $check_query = $this->domainDA->execute(
            "SELECT count(*) AS count
            FROM domain_users_roles
            WHERE userid={$this->userid} AND roleid={$role}"
        );

        if ($check_query->getFields('count') > 0) {
            // This role has already been assigned
            return true;
        }

        return $this->domainDA->execute(
            "INSERT INTO domain_users_roles
            (userid,roleid)
            VALUES ({$this->userid}, {$role})"
        );
    }

    /**
     * Unassigns a role from a user
     *
     * @param integer $role
     *        	ID
     * @return boolean success
     */
    public function unassignRole($role)
    {
        // If the role has been given by name, get its id
        if (!is_int($role)) {
            $role = Role::getIdFromName($role);
            if ($role === false) {
                return false;
            }
        }

        return $this->domainDA->execute(
            "DELETE FROM domain_users_roles
            WHERE userid={$this->userid} AND roleid={$role}"
        );
    }

    /**
     * Returns all roles of the current user.
     *
     * @return array null
     */
    public function getAllRoles()
    {
        return $this->domainDA->execute("SELECT dr.*
			FROM
			domain_users_roles AS dur
			JOIN domain_roles AS dr ON
			dur.roleid=dr.id
			WHERE dur.userid={$this->userid}");
    }

    /**
     * Return count of roles for the current user.
     *
     * @return integer
     */
    public function getRoleCount()
    {
        $query = $this->domainDA->execute("SELECT COUNT(*) AS result FROM domain_users_roles WHERE userid={$this->userid}" );
        return (int)$query->getFields('result');
    }

    public function hasPermission($permission)
    {
        if (self::isAdminUser($this->username, $this->container->getCurrentDomain()->getDomainId())) {
            // Administrator user has all roles and permissions by default
            return true;
        }

        // If the permissions has been given by name, get its id
        if (!is_int($permission)) {
            $permission = Permission::getIdFromName($permission);
            if ($permission === false) {
                return false;
            }
        }

        $permissionQuery = $this->domainDA->execute(
            "SELECT count(*) AS count
            FROM domain_roles_permissions AS rp
            JOIN domain_users_roles AS usersroles ON usersroles.roleid=rp.roleid
            WHERE usersroles.userid={$this->userid} AND rp.permissionid={$permission}"
        );

        if ($permissionQuery->getFields('count') > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getAllPermissions()
    {
        if (self::isAdminUser($this->username, $this->container->getCurrentDomain()->getDomainId())) {
            // Administrator user has all roles and permissions by default
            $permissionQuery = $this->domainDA->execute(
                "SELECT id,name FROM domain_permissions"
            );
        } else {
            $permissionQuery = $this->domainDA->execute(
                "SELECT id,name FROM domain_permissions AS perms
                JOIN domain_roles_permissions AS rp ON perms.id=rp.permissionid
                JOIN domain_users_roles AS usersroles ON usersroles.roleid=rp.roleid
                WHERE usersroles.userid={$this->userid}
                GROUP BY id"
            );
        }

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
}
