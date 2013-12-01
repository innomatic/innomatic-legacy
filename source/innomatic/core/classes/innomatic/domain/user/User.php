<?php
/**
 * Innomatic
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2012 Innoteam Srl
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
        $this->rootDA = InnomaticContainer::instance('innomaticcontainer')->getDataAccess();
        $this->domainserial = $domainSerial;
        $this->userid = $userId;

        $domain = InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain();
        if (!is_object($domain)) {
            require_once('innomatic/domain/Domain.php');
            $domain_query = $this->rootDA->execute('SELECT domainid FROM domains WHERE id='.$domainSerial);
            $domain = new Domain(InnomaticContainer::instance('innomaticcontainer')->getDataAccess(), $domain_query->getFields('domainid'), null);
        }
        $this->domainDA = $domain->getDataAccess();

        if (strlen($this->userid)) {
            // TODO to be cached in a more elegant way eg. registry
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
            $uquery = InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess()->execute('SELECT id FROM domain_users WHERE username='.InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess()->formatText($username));
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

        require_once('innomatic/process/Hook.php');
        $hook = new Hook($this->rootDA, 'innomatic', 'domain.user.add');
        if ($hook->CallHooks('calltime', $this, array('domainserial' => $this->domainserial, 'userdata' => $userdata)) == Hook::RESULT_OK) {
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

                        \Innomatic\Io\Filesystem\DirectoryUtils::mktree(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/domains/'.$max_users_query->getFields('domainid').'/users/'.$userdata['username'].'/', 0755);

                        if ($hook->CallHooks('useradded', $this, array('domainserial' => $this->domainserial, 'userdata' => $userdata)) != Hook::RESULT_OK)
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
        $userdata['username'] = 'admin'.(InnomaticContainer::instance('innomaticcontainer')->getEdition() == InnomaticContainer::EDITION_SAAS ? '@'.$domainid : '');
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

        require_once('innomatic/process/Hook.php');
        $hook = new Hook($this->rootDA, 'innomatic', 'domain.user.edit');
        if ($hook->callHooks('calltime', $this, array('domainserial' => $this->domainserial, 'userdata' => $userdata)) == Hook::RESULT_OK) {
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

                    if ($hook->callHooks('useredited', $this, array('domainserial' => $this->domainserial, 'userdata' => $userdata)) != Hook::RESULT_OK)
                        $result = false;
                } else {
                    
                    $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
                    $log->logEvent('innomatic.users.users.edituser', 'Empty username or group id', \Innomatic\Logging\Logger::WARNING);
                }
            } else {
                
                $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
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

                require_once('innomatic/domain/Domain.php');
                $tmpdomain = new Domain($this->rootDA, $uquery->getFields('username'), $empty);
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
        require_once('innomatic/process/Hook.php');

        $hook = new Hook($this->rootDA, 'innomatic', 'domain.user.remove');
        if ($hook->CallHooks('calltime', $this, array('domainserial' => $this->domainserial, 'userid' => $this->userid)) == Hook::RESULT_OK) {
            if ($this->userid != 0) {
                $result = $this->domainDA->execute('DELETE FROM domain_users WHERE id='. (int) $this->userid);

                // Remove user dir
                $domain_query = InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->execute('SELECT domainid FROM domains WHERE id='. (int) $this->domainserial);

                if (InnomaticContainer::instance('innomaticcontainer')->getHome().'core/domains/'.$domain_query->getFields('domainid').'/users/'.$this->username != InnomaticContainer::instance('innomaticcontainer')->getHome().'core/domains/'.$domain_query->getFields('domainid').'/users/') {
                    \Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/domains/'.$domain_query->getFields('domainid').'/users/'.$this->username, 0755);
                }

                // Remove cached items

                require_once('innomatic/datatransfer/cache/CacheGarbageCollector.php');
                $cache_gc = new CacheGarbageCollector();
                $cache_gc->RemoveUserItems((int) $this->userid);

                //$this->htp->remuser( $this->username );
                if ($hook->CallHooks('userremoved', $this, array('domainserial' => $this->domainserial, 'userid' => $this->userid)) != Hook::RESULT_OK)
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
        if (InnomaticContainer::instance('innomaticcontainer')->getEdition() == InnomaticContainer::EDITION_ENTERPRISE) {
            $domain_query = InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->execute('SELECT domainid FROM domains LIMIT 1');
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
        require_once('innomatic/domain/user/UserSettings.php');
        $user_settings = new UserSettings(
            InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess(),
            InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getUserId());
        $lang = $user_settings->getKey('desktop-language');

        return strlen($lang) ? $lang : InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getLanguage();
    }

    public function getCountry()
    {
        require_once('innomatic/domain/user/UserSettings.php');
        $user_settings = new UserSettings(
            InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess(),
            InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getUserId());
        $country = $user_settings->getKey('desktop-country');

        return strlen($country) ? $country : InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getCountry();
    }

    public static function isAdminUser($username, $domain)
    {
        $admin_username = 'admin'.(InnomaticContainer::instance('innomaticcontainer')->getEdition() == InnomaticContainer::EDITION_SAAS ? '@'.$domain : '');
        return $username == $admin_username ? true : false;
    }

    public static function getAdminUsername($domain)
    {
        return 'admin'.(InnomaticContainer::instance('innomaticcontainer')->getEdition() == InnomaticContainer::EDITION_SAAS ? '@'.$domain : '');
    }

}
