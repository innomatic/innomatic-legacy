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
    protected $rootDA;
    protected $domainDA;
    protected $domainserial;
    protected $userid;
    protected $username;

    /*!
     @param domainSerial integer - Domain serial number.
     @param userId integer - User id number.
     */
    public function __construct($domainSerial, $userId = 0)
    {
        $this->rootDA = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess();
        $this->domainserial = $domainSerial;
        $this->userid = $userId;

        $domain = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain();
        if (!is_object($domain)) {
            $domain_query = $this->rootDA->execute('SELECT domainid FROM domains WHERE id='.$domainSerial);
            $domain = new \Innomatic\Domain\Domain(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(), $domain_query->getFields('domainid'), null);
        }
        $this->domainDA = $domain->getDataAccess();

        if (strlen($this->userid)) {
            // @todo to be cached in a more elegant way eg. registry
            if (isset($GLOBALS['gEnv']['runtime']['innomatic']['users']['username_check'][(int)$this->userid])) {
                $this->username = $GLOBALS['gEnv']['runtime']['innomatic']['users']['username_check'][(int)$this->userid];
            } else {
                $uquery = $this->domainDA->execute('SELECT username FROM domain_users WHERE id='.(int)$this->userid);

                if ($uquery) {
                    $this->username = $uquery->getFields('username');
                    $GLOBALS['gEnv']['runtime']['innomatic']['users']['username_check'][(int)$this->userid] = $this->username;
                    $uquery->free();
                }
            }
        }
    }

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
            $uquery = $this->domainDA->execute('SELECT id FROM domain_users WHERE username='.$this->domainDA->formatText($username));
            $this->userid = $uquery->getFields('id');
            return $this->userid;
        }
        return false;
    }

    public static function getUserIdByUsername($username)
    {
        if (!empty($username)) {
            $uquery = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->execute('SELECT id FROM domain_users WHERE username='.\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->formatText($username));
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
                        $user.= $this->domainDA->formatText($userdata['email']).')';

                        $this->domainDA->execute($user);
                        $this->userid = $seqval;

                        $result = $seqval;

                        \Innomatic\Io\Filesystem\DirectoryUtils::mktree(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome().'core/domains/'.$max_users_query->getFields('domainid').'/users/'.$userdata['username'].'/', 0755);

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
        $userdata['username'] = 'admin'.(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getEdition() == \Innomatic\Core\InnomaticContainer::EDITION_SAAS ? '@'.$domainid : '');
        $userdata['lname'] = 'Administrator';
        $userdata['password'] = $domainpassword;
        $userdata['groupid'] = 0;
        $domainsquery->free();
        $this->create($userdata);
        
        // Assign root role to the admin user
        $this->assignRole('root');
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
                    
                    $log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();
                    $log->logEvent('innomatic.users.users.edituser', 'Empty username or group id', \Innomatic\Logging\Logger::WARNING);
                }
            } else {
                
                $log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();
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
                $domain_query = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->execute('SELECT domainid FROM domains WHERE id='. (int) $this->domainserial);

                if (\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome().'core/domains/'.$domain_query->getFields('domainid').'/users/'.$this->username != \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome().'core/domains/'.$domain_query->getFields('domainid').'/users/') {
                    \Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome().'core/domains/'.$domain_query->getFields('domainid').'/users/'.$this->username, 0755);
                }

                // Remove cached items
                $cache_gc = new \Innomatic\Datatransfer\Cache\CacheGarbageCollector();
                $cache_gc->removeUserItems((int) $this->userid);

                //$this->htp->remuser( $this->username );
                if ($hook->callHooks('userremoved', $this, array('domainserial' => $this->domainserial, 'userid' => $this->userid)) != \Innomatic\Process\Hook::RESULT_OK)
                    $result = false;
                $this->userid = 0;
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
        if (\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getEdition() == \Innomatic\Core\InnomaticContainer::EDITION_ENTERPRISE) {
            $domain_query = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->execute('SELECT domainid FROM domains LIMIT 1');
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
        $user_settings = new \Innomatic\Domain\User\UserSettings(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(),
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId());
        $lang = $user_settings->getKey('desktop-language');

        return strlen($lang) ? $lang : \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getLanguage();
    }

    public function getCountry()
    {
        $user_settings = new \Innomatic\Domain\User\UserSettings(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(),
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId());
        $country = $user_settings->getKey('desktop-country');

        return strlen($country) ? $country : \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getCountry();
    }

    public static function isAdminUser($username, $domain)
    {
        $admin_username = 'admin'.(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getEdition() == \Innomatic\Core\InnomaticContainer::EDITION_SAAS ? '@'.$domain : '');
        return $username == $admin_username ? true : false;
    }

    public static function getAdminUsername($domain)
    {
        return 'admin'.(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getEdition() == \Innomatic\Core\InnomaticContainer::EDITION_SAAS ? '@'.$domain : '');
    }

    /**
     * Checks to see whether a user has a role or not
     *
     * @param integer|string $role
     *        	id, title or path
     * @param integer $User
     *        	UserID, not optional
     *
     * @throws UserNotProvidedException
     * @return boolean success
     */
    public function hasRole($role)
    {    
        if (is_int ( $role )) {
            $roleid = $role;
        } else {
            $role_manager = new \Innomatic\Domain\Role\RoleManager();
            
            if (substr ( $role, 0, 1 ) == "/")
                $roleid = $role_manager->PathID ( $role );
            else
                $roleid = $role_manager->TitleID ( $role );
        }
    
        $R = jf::SQL ( "SELECT * FROM domain_users_roles AS TUR
			JOIN domain_roles AS TRdirect ON (TRdirect.ID=TUR.roleid)
			JOIN domain_roles AS TR ON (TR.left BETWEEN TRdirect.left AND TRdirect.right)
    
			WHERE
			TUR.UserID={$this->userid} AND TR.ID={$roleid}" );
        return $R !== null;
    }
    
    /**
     * Assigns a role to a user
     *
     * @param integer|string $role
     *        	id or path or title
     * @param integer $UserID
     *        	UserID (use 0 for guest)
     *
     * @throws UserNotProvidedException
     * @return inserted or existing
     */
    public function assignRole($role)
    {    
        if (is_int ( $role ))
        {
            $roleid = $role;
        }
        else
        {
            if (substr ( $role, 0, 1 ) == "/")
                $roleid = jf::$RBAC->Roles->PathID ( $role );
            else
                $roleid = jf::$RBAC->Roles->TitleID ( $role );
        }
        return $this->domainDA->execute( "INSERT INTO domain_users_roles
				(UserID,roleid,assignmentdate)
				VALUES ({$this->userid},{$roleid},'.time().')
				");
    }
    
    /**
     * Unassigns a role from a user
     *
     * @param integer $role
     *        	ID
     * @param integer $UserID
     *        	UserID (use 0 for guest)
     *
     * @throws UserNotProvidedException
     * @return boolean success
     */
    public function unassignRole($role)
    {    
        return $this->domainDA->execute( "DELETE FROM domain_users_roles
		WHERE UserID={$this->userid} AND roleid={$role}") >= 1;
    }
    
    /**
     * Returns all roles of a user
     *
     * @param integer $UserID
     *        	Not optional
     *
     * @throws UserNotProvidedException
     * @return array null
     *
     */
    public function getAllRoles()
    {
        return $this->domainDA->execute( "SELECT TR.*
			FROM
			domain_users_roles AS `TRel`
			JOIN domain_roles AS `TR` ON
			(`TRel`.roleid=`TR`.ID)
			WHERE TRel.UserID={$this->userid}"  );
    }
    /**
     * Return count of roles for a user
     *
     * @param integer $UserID
     *
     * @throws UserNotProvidedException
     * @return integer
     */
    public function getRoleCount()
    {
        $Res = jf::SQL ( "SELECT COUNT(*) AS Result FROM domain_users_roles WHERE UserID={$this->userid}" );
        return (int)$Res [0] ['Result'];
    }
}
