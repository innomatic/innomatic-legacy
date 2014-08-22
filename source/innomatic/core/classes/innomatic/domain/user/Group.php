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

use \Innomatic\Process\Hook;

class Group
{
    protected $container;
    public $mrRootDb;
    public $mrDomainDA;
    public $domainserial;
    public $groupid;

    /*!
     @function Group

     @abstract Class constructor
     */
    public function __construct($groupid = 0)
    {
        $this->container = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');
        $this->mrRootDb = $this->container->getDataAccess();
        $this->mrDomainDA = $this->container->getCurrentDomain()->getDataAccess();
        $this->domainserial = $this->container->getCurrentDomain()->domaindata['id'];
        $this->groupid = $groupid;
    }

    // Create a new group
    public function createGroup($groupdata)
    {
        $result = false;

        $hook = new \Innomatic\Process\Hook($this->mrRootDb, 'innomatic', 'domain.group.add');
        if ($hook->callHooks('calltime', $this, array('domainserial' => $this->domainserial, 'groupdata' => $groupdata)) == \Innomatic\Process\Hook::RESULT_OK) {
            if (($this->groupid == 0) & (strlen($groupdata['groupname']) > 0)) {
                // Check if a group with this name already exists
                $groupquery = $this->mrDomainDA->execute('SELECT groupname FROM domain_users_groups WHERE groupname = '.$this->mrDomainDA->formatText($groupdata['groupname']));
                if ($groupquery->getNumberRows() == 0) {
                    $groupsseq = $this->mrDomainDA->getNextSequenceValue('domain_users_groups_id_seq');

                    $ins = 'INSERT INTO domain_users_groups VALUES ( '.$groupsseq.','.$this->mrDomainDA->formatText($groupdata['groupname']).')';
                    $this->mrDomainDA->execute($ins);
                    $this->groupid = $groupsseq;

                    if ($hook->CallHooks('groupadded', $this, array('domainserial' => $this->domainserial, 'groupdata' => $groupdata, 'groupid' => $this->groupid)) != Hook::RESULT_OK)
                        $result = false;
                } else {
                    
                    $log = $this->container->getLogger();
                    $log->logEvent('innomatic.users.group.creategroup', 'Attempted to create an already existing group', \Innomatic\Logging\Logger::ERROR);
                }
            } else {
                
                $log = $this->container->getLogger();
                $log->logEvent('innomatic.users.group.creategroup', 'Invalid groupname or access to a member for a not initialized group object', \Innomatic\Logging\Logger::ERROR);
            }
        }

        return $result;
    }

    // Change group data
    public function editGroup($groupdata)
    {
        $result = false;

        if (($this->groupid != 0) & (strlen($groupdata['groupname']) > 0)) {
            $groupquery = $this->mrDomainDA->execute('SELECT groupname FROM domain_users_groups WHERE groupname = '.$this->mrDomainDA->formatText($groupdata['groupname']));
            if ($groupquery->getNumberRows() == 0) {
                $upd = 'UPDATE domain_users_groups SET groupname = '.$this->mrDomainDA->formatText($groupdata['groupname']).' WHERE id='. (int) $this->groupid;
                $this->mrDomainDA->execute($upd);
            } else {
                
                $log = $this->container->getLogger();
                $log->logEvent('innomatic.users.group.editgroup', 'No groups with specified name ('.$groupdata['groupname'].') exists', \Innomatic\Logging\Logger::ERROR);
            }
        } else {
            
            $log = $this->container->getLogger();
            $log->logEvent('innomatic.users.group.editgroup', 'Invalid group id ('.$this->groupid.') or groupname ('.$groupdata['groupname'].')', \Innomatic\Logging\Logger::ERROR);
        }
        return $result;
    }

    // Remove group
    public function removeGroup($deleteuserstoo)
    {
        $result = false;

        $hook = new \Innomatic\Process\Hook($this->mrRootDb, 'innomatic', 'domain.group.remove');
        if ($hook->callHooks('calltime', $this, array('domainserial' => $this->domainserial, 'groupid' => $this->groupid)) == \Innomatic\Process\Hook::RESULT_OK) {
            if ($this->groupid != 0) {
                if ($this->mrDomainDA->execute('DELETE FROM domain_users_groups WHERE id='. (int) $this->groupid)) {
                    // Check if we must delete users in this group
                    if ($deleteuserstoo == true) {
                        $usersquery = $this->mrDomainDA->execute('SELECT id FROM domain_users WHERE AND groupid='. (int) $this->groupid);
                        $numusers = $usersquery->getNumberRows();

                        if ($numusers > 0) {
                            // Remove users in this group
                            while (!$usersquery->eof) {
                                $usdata = $usersquery->getFields();

                                $tmpuser = new User($this->domainserial, $usdata['id']);
                                $tmpuser->remove();

                                $usersquery->moveNext();
                                //delete $tmpuser;
                            }
                        }
                    } else {
                        $this->mrDomainDA->execute("UPDATE domain_users SET groupid = '0' WHERE groupid=".$this->groupid);
                    }

                    if ($hook->callHooks('groupremoved', $this, array('domainserial' => $this->domainserial, 'groupid' => $this->groupid)) != \Innomatic\Process\Hook::RESULT_OK)
                        $result = false;
                    $this->groupid = 0;
                }
            } else {
                
                $log = $this->container->getLogger();
                $log->logEvent('innomatic.users.group.removegroup', "Attempted to call a member of an object that doesn't refer to any group", \Innomatic\Logging\Logger::ERROR);
            }
        }

        return $result;
    }

    // Get users list
    public function getUsersList()
    {
        if ($this->groupid != 0) {
            return $this->mrDomainDA->execute('SELECT * FROM domain_users WHERE groupid='. (int) $this->groupid.' AND domainid='. (int) $this->domainserial);
        }
        return false;
    }
}
