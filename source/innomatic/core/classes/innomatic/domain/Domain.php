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
namespace Innomatic\Domain;

/*!
 @class Domain
 @abstract Domain management
 */
class Domain
{
    public $rootda;
    public $domainid;
    public $domainserial;
    public $domainlog;
    public $domaindata;
    public $unmetdeps = array();
    public $unmetsuggs = array();
    public $reservedNames = array();
    protected $dataAccess;

    public function __construct(DataAccess $rootda, $domainid = '0', $domainda = null)
    {
        $this->rootda = $rootda;
        if (!get_cfg_var('safe_mode')) {
            set_time_limit(0);
        }
        if (empty($domainda) and $domainid != '0') {
            $tmpquery = $this->rootda->execute('SELECT * FROM domains WHERE domainid='.$this->rootda->formatText($domainid));
            if ($tmpquery->getNumberRows() == 1) {
                $this->domaindata = $tmpquery->getFields();

                if (InnomaticContainer::instance('innomaticcontainer')->getEdition() == InnomaticContainer::EDITION_SAAS) {
                    $args['dbtype'] = $this->domaindata['dataaccesstype'];
                    $args['dbname'] = $this->domaindata['domaindaname'];
                    $args['dbhost'] = $this->domaindata['dataaccesshost'];
                    $args['dbport'] = $this->domaindata['dataaccessport'];
                    $args['dbuser'] = $this->domaindata['dataaccessuser'];
                    $args['dbpass'] = $this->domaindata['dataaccesspassword'];
                    $args['dblog'] = InnomaticContainer::instance('innomaticcontainer')->getHome().'core/domains/'.$this->domaindata['domainid'].'/log/dataaccess.log';

                    require_once('innomatic/dataaccess/DataAccessFactory.php');
                    require_once('innomatic/dataaccess/DataAccessSourceName.php');
                    $dasn_string = $args['dbtype'].'://'.
                    $args['dbuser'].':'.
                    $args['dbpass'].'@'.
                    $args['dbhost'].':'.
                    $args['dbport'].'/'.
                    $args['dbname'].'?'.
                        'logfile='.$args['dblog'];


                    $this->dataAccess = DataAccessFactory::getDataAccess(new DataAccessSourceName($dasn_string));
                    $this->dataAccess->Connect();
                } else {
                    $this->dataAccess = $this->rootda;
                }

                $this->domainserial = $this->domaindata['id'];
                $this->domainid = $this->domaindata['domainid'];

                
                $this->domainlog = new \Innomatic\Logging\Logger(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/domains/'.$domainid.'/log/domain.log');
            } else {
                
                $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
                $log->LogDie('innomatic.domains.domain.domain', 'No domain exists with specified domain id ('.$domainid.')');
            }
        } else {
            if (InnomaticContainer::instance('innomaticcontainer')->getEdition() == InnomaticContainer::EDITION_SAAS) {
                $this->dataAccess = $domainda;
            } else {
                $this->dataAccess = $this->rootda;
            }
            $this->domainid = $domainid;
            $tmpquery = $this->rootda->execute('SELECT * FROM domains WHERE domainid='.$this->rootda->formatText($domainid));

            $this->domainserial = $tmpquery->getFields('id');
            
            $this->domainlog = new \Innomatic\Logging\Logger(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/domains/'.$domainid.'/log/domain.log');
        }

        $this->reservedNames[] = 'innomatic';
    }

    /**
     * Tells if the Domain object is a valid domain
     *
     * @return boolean
     */
    public function isValid()
    {
        return is_object($this->dataAccess);
    }

    public function create($domaindata, $createDb = true)
    {
        $result = false;

        require_once('innomatic/process/Hook.php');
        $hook = new Hook($this->rootda, 'innomatic', 'domain.create');
        if ($hook->CallHooks('calltime', $this, array('domaindata' => $domaindata)) == Hook::RESULT_OK) {
            $domaindata['domainid'] = strtolower(str_replace(' ', '', trim($domaindata['domainid'])));

            // Checks if the domainid contains reserved words.
            if (in_array($domaindata['domainid'], $this->reservedNames)) {
                
                $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
                $log->logEvent('innomatic.domain.create', 'Cannot create domain with id "'.$domaindata['domainid'].'" since it is a reserved word', \Innomatic\Logging\Logger::WARNING);
                return false;
            }

            // When in enterprise edition, checks if there are no domains.
            $goon = true;

            if (InnomaticContainer::instance('innomaticcontainer')->getEdition() == InnomaticContainer::EDITION_ENTERPRISE) {
                $check_query = InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->execute('SELECT count(*) AS domains FROM domains');

                if ($check_query->getFields('domains') > 0)
                $goon = false;
            }

            if ($goon) {
                // Default settings and settings tuning
                //
                $nextseq = $this->rootda->getNextSequenceValue('domains_id_seq');

                // TODO check that the domainid doesn't contain unsupported characters.
                $domaindata['domainid'] = $this->defopt($domaindata['domainid'], $nextseq);
                $domaindata['domainname'] = $this->defopt(trim($domaindata['domainname']), $domaindata['domainid'].' domain');
                $domaindata['domainpassword'] = $this->defopt(trim($domaindata['domainpassword']), $domaindata['domainid']);
                $domaindata['domaindaname'] = $this->defopt(strtolower(str_replace(' ', '', trim($domaindata['domaindaname']))), 'innomatic_'.$domaindata['domainid'].'_domain');
                $domaindata['dataaccesshost'] = $this->defopt(trim($domaindata['dataaccesshost']), InnomaticContainer::instance('innomaticcontainer')->getConfig()->value('RootDatabaseHost'));
                $domaindata['dataaccessport'] = $this->defopt(trim($domaindata['dataaccessport']), InnomaticContainer::instance('innomaticcontainer')->getConfig()->value('RootDatabasePort'));
                $domaindata['dataaccessuser'] = $this->defopt(str_replace(' ', '', trim($domaindata['dataaccessuser'])), InnomaticContainer::instance('innomaticcontainer')->getConfig()->value('RootDatabaseUser'));
                $domaindata['dataaccesspassword'] = $this->defopt(trim($domaindata['dataaccesspassword']), InnomaticContainer::instance('innomaticcontainer')->getConfig()->value('RootDatabasePassword'));
                $domaindata['dataaccesstype'] = $this->defopt(trim($domaindata['dataaccesstype']), InnomaticContainer::instance('innomaticcontainer')->getConfig()->value('RootDatabaseType'));
                $domaindata['domaincreationdate'] = isset($domaindata['domaincreationdate']) ? trim($domaindata['domaincreationdate']) : time();
                $domaindata['domainexpirydate'] = isset($domaindata['domainexpirytime']) ? trim($domaindata['domainexpirydate']) : time();
                $domaindata['domainactive'] = isset($domaindata['domainactive']) ? $domaindata['domainactive'] : $this->rootda->fmttrue;
                $domaindata['maxusers'] = isset($domaindata['maxusers']) ? $domaindata['maxusers'] : '0';
                if (!isset($domaindata['domainnotes'])) {
                    $domaindata['domainnotes'] = '';
                }
                $domaindata['webappskeleton'] = $this->defopt(trim($domaindata['webappskeleton']), 'default');

                if (InnomaticContainer::instance('innomaticcontainer')->getEdition() == InnomaticContainer::EDITION_ENTERPRISE) {
                    $domaindata['domaindaname'] = InnomaticContainer::instance('innomaticcontainer')->getConfig()->value('RootDatabaseName');
                    $domaindata['dataaccesshost'] = InnomaticContainer::instance('innomaticcontainer')->getConfig()->value('RootDatabaseHost');
                    $domaindata['dataaccessport'] = InnomaticContainer::instance('innomaticcontainer')->getConfig()->value('RootDatabasePort');
                    $domaindata['dataaccessuser'] = InnomaticContainer::instance('innomaticcontainer')->getConfig()->value('RootDatabaseUser');
                    $domaindata['dataaccesspassword'] = InnomaticContainer::instance('innomaticcontainer')->getConfig()->value('RootDatabasePassword');
                    $domaindata['dataaccesstype'] = InnomaticContainer::instance('innomaticcontainer')->getConfig()->value('RootDatabaseType');
                }

                if ($this->rootda->execute('INSERT INTO domains VALUES ( '.$nextseq.','.$this->rootda->formatText($domaindata['domainid']).','.
                $this->rootda->formatText($domaindata['domainname']).','.
                $this->rootda->formatText(md5($domaindata['domainpassword'])).','.
                $this->rootda->formatText($domaindata['domaindaname']).','.
                $this->rootda->formatText($domaindata['dataaccesshost']).','.
                $this->rootda->formatInteger($domaindata['dataaccessport']).','.
                $this->rootda->formatText($domaindata['dataaccessuser']).','.
                $this->rootda->formatText($domaindata['dataaccesspassword']).','.
                $this->rootda->formatText($domaindata['dataaccesstype']).','.
                $this->rootda->formatDate($domaindata['domaincreationdate']).','.
                $this->rootda->formatDate($domaindata['domainexpirydate']).','.
                $this->rootda->formatText($domaindata['domainactive']).','.
                $this->rootda->formatText($domaindata['domainnotes']).','.
                $this->rootda->formatInteger($domaindata['maxusers']).','.
                $this->rootda->formatText($domaindata['webappskeleton']).','.
                $this->rootda->formatText($domaindata['webappurl']).
                ')')) {
                    $this->domainid = $domaindata['domainid'];
                    $this->domainserial = $nextseq;
                    
                    $this->domainlog = new \Innomatic\Logging\Logger(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/domains/'.$domaindata['domainid'].'/log/domain.log');

                    // Domain private directory tree creation inside Innomatic webapp.
                    $this->makedir(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/domains/'.$domaindata['domainid']);
                    $this->makedir(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/domains/'.$domaindata['domainid'].'/log');
                    $this->makedir(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/domains/'.$domaindata['domainid'].'/conf');

                    // Domain webapp creation.
                    require_once('innomatic/webapp/WebAppContainer.php');
                    WebAppContainer::createWebApp($domaindata['domainid'], $domaindata['webappskeleton']);

                    // Creates the database, if asked.
                    if (InnomaticContainer::instance('innomaticcontainer')->getEdition() == InnomaticContainer::EDITION_SAAS) {
                        $args['dbtype'] = strlen($domaindata['dataaccesstype']) ? $domaindata['dataaccesstype'] : InnomaticContainer::instance('innomaticcontainer')->getConfig()->value('RootDatabaseType');
                        $args['dbname'] = $domaindata['domaindaname'];
                        $args['dbhost'] = $domaindata['dataaccesshost'];
                        $args['dbport'] = $domaindata['dataaccessport'];
                        $args['dbuser'] = $domaindata['dataaccessuser'];
                        $args['dbpass'] = $domaindata['dataaccesspassword'];
                        $args['dblog'] = InnomaticContainer::instance('innomaticcontainer')->getHome().'core/domains/'.$domaindata['domainid'].'/log/dataaccess.log';

                        $args['name'] = $domaindata['domaindaname'];

                        require_once('innomatic/dataaccess/DataAccessFactory.php');
                        require_once('innomatic/dataaccess/DataAccessSourceName.php');
                        $dasn_string = $args['dbtype'].'://'.
                        $args['dbuser'].':'.
                        $args['dbpass'].'@'.
                        $args['dbhost'].':'.
                        $args['dbport'].'/'.
                        $args['dbname'].'?'.
                        'logfile='.$args['dblog'];
                        $tmpdb = DataAccessFactory::getDataAccess(new DataAccessSourceName($dasn_string));

                        if ($createDb) {
                            if ($tmpdb->Connect()) {
                                $tmpdb->DropDB($args);
                                $tmpdb->Close();
                            }
                        }
                    } else {
                        $tmpdb = $this->rootda;
                    }

                    if (!$createDb or InnomaticContainer::instance('innomaticcontainer')->getEdition() == InnomaticContainer::EDITION_ENTERPRISE or $created = $tmpdb->createDB($args)) {
                        if (isset($created) and $created == true) {
                            $this->domainlog->logEvent($domaindata['domainid'], 'Database '.$args['dbname'].' created', \Innomatic\Logging\Logger::NOTICE);
                        }
                        if (InnomaticContainer::instance('innomaticcontainer')->getEdition() == InnomaticContainer::EDITION_ENTERPRISE or $tmpdb->connect()) {
                            if (InnomaticContainer::instance('innomaticcontainer')->getEdition() == InnomaticContainer::EDITION_SAAS) {
                                $this->dataAccess = $tmpdb;
                            } else {
                                $this->dataAccess = $this->rootda;
                            }

                            //$xmldb = new DataAccessXmlTable( $tmpdb, DataAccessXmlTable::SQL_CREATE );

                            $tmpquery = $this->rootda->execute('SELECT id FROM applications WHERE appid='.$this->rootda->formatText('innomatic'));

                            if ($this->EnableApplication($tmpquery->getFields('id'))) {
                                require_once('innomatic/domain/user/User.php');
                                $tmpuser = new User($nextseq);
                                $tmpuser->createAdminUser($domaindata['domainid'], $domaindata['domainpassword']);

                                
                                $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();

                                $log->logEvent($domaindata['domainid'], 'Created new domain '.$domaindata['domainid'], \Innomatic\Logging\Logger::NOTICE);

                                $this->domainlog->logEvent($domaindata['domainid'], 'Created domain '.$domaindata['domainid'], \Innomatic\Logging\Logger::NOTICE);

                                if ($hook->CallHooks('domaincreated', $this, array('domaindata' => $domaindata)) != Hook::RESULT_ABORT)
                                $result = true;

                                if (InnomaticContainer::instance('innomaticcontainer')->getConfig()->Value('SecurityAlertOnDomainOperation') == '1') {
                                    require_once('innomatic/security/SecurityManager.php');

                                    $innomatic_security = new SecurityManager();
                                    $innomatic_security->sendAlert('A domain has been created with id '.$domaindata['domainid']);
                                    unset($innomatic_security);
                                }
                            } else {
                                
                                $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
                                $log->logEvent('innomatic.domains.domain.create', 'Unable to enable Innomatic to the domain', \Innomatic\Logging\Logger::ERROR);
                            }
                        } else {
                            
                            $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
                            $log->logEvent('innomatic.domains.domain.create', 'Unable to connect to domain database', \Innomatic\Logging\Logger::ERROR);
                        }
                    } else {
                        
                        $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
                        $log->logEvent('innomatic.domains.domain.create', 'Unable to create domain database', \Innomatic\Logging\Logger::ERROR);
                    }
                } else {
                    
                    $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
                    $log->logEvent('innomatic.domains.domain.create', 'Unable to insert domain row in domains table', \Innomatic\Logging\Logger::ERROR);
                }
            } else {
                
                $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
                $log->logEvent('innomatic.domains.domain.create', 'Tried to create another domain in Enterprise edition', \Innomatic\Logging\Logger::WARNING);
            }
        }

        return $result;
    }

    private function makeDir($dirname)
    {
        if (!file_exists($dirname))
        return @mkdir($dirname, 0755);
        else
        return true;
    }

    private function defOpt($option, $defaultopt)
    {
        if (strlen($option) == 0) {
            return $defaultopt;
        } else {
            return $option;
        }
    }

    public function edit($domaindata)
    {
        $result = false;

        require_once('innomatic/process/Hook.php');
        $hook = new Hook($this->rootda, 'innomatic', 'domain.edit');
        if ($hook->CallHooks('calltime', $this, array('domaindata' => $domaindata)) == Hook::RESULT_OK) {
            if (!empty($domaindata['domainserial'])) {
                $updatestr = 'UPDATE domains SET domainname='.$this->rootda->formatText($domaindata['domainname']).
                    ',webappurl='.$this->rootda->formatText($domaindata['webappurl']).
                    ',domaindaname='.$this->rootda->formatText($domaindata['domaindaname']).
                    ',dataaccesshost='.$this->rootda->formatText($domaindata['dataaccesshost']).
                    ',dataaccessport='.$this->rootda->formatText($domaindata['dataaccessport']).
                    ',dataaccessuser='.$this->rootda->formatText($domaindata['dataaccessuser']).
                    ',dataaccesspassword='.$this->rootda->formatText($domaindata['dataaccesspassword']).
                    ' WHERE id='. (int) $domaindata['domainserial'];

                $result = $this->rootda->execute($updatestr);

                $this->domaindata = $domaindata;

                $tmpquery = $this->rootda->execute('SELECT domainid FROM domains WHERE id='. (int) $domaindata['domainserial']);
                $tmpdata = $tmpquery->getFields();

                if (strlen($domaindata['domainpassword']))
                $this->changePassword($domaindata['domainpassword']);

                
                $this->domainlog->logEvent($tmpdata['domainid'], 'Changed domain settings', \Innomatic\Logging\Logger::NOTICE);

                if ($hook->CallHooks('domainedited', $this, array('domaindata' => $domaindata)) == Hook::RESULT_ABORT)
                $result = false;
            }
        }

        return $result;
    }

    /*!
     @function getNotes

     @abstract Gets domain notes.

     @result Domain notes if any, empty string otherwise.
     */
    public function getNotes()
    {
        if ($domain_query = $this->rootda->execute('SELECT notes FROM domains WHERE id='. (int) $this->domainid)) {
            return $domain_query->getFields('notes');
        }
        return '';
    }

    /*!
     @function setNotes

     @abstract Edits domain notes.

     @param notes string - Notes text.

     @result True if notes were updated.
     */
    public function setNotes($notes)
    {
        if ($this->rootda->execute(
            'UPDATE domains SET notes='.$this->rootda->formatText($notes).
            ' WHERE domainid='.$this->rootda->formatText($this->domainid))) {
        return true;
            } else {
                return false;
            }
    }

    /*!
     @function getMaxUsers

     @abstract Gets domain max users limit.

     @result Max users limit.
     */
    public function getMaxUsers()
    {
        if ($domain_query = $this->rootda->execute('SELECT maxusers FROM domains WHERE id='. (int) $this->domainid)) {
            return $domain_query->getFields('maxusers');
        }
        return '';
    }

    /*!
     @function setMaxUsers

     @abstract Sets domain max users limit.

     @param maxUsers integer - Max users limit.

     @result True if max users limit has been updated.
     */
    public function setMaxUsers($maxUsers = 0)
    {
        if ($maxUsers == '')
        $maxUsers = 0;

        if ($this->rootda->execute('UPDATE domains SET maxusers='.$maxUsers.' WHERE domainid='.$this->rootda->formatText($this->domainid)))
        return true;

        return false;
    }

    /**
     * Gets the webapp skeleton used for creating the domain webapp.
     *
     * @return string
     */
    public function getWebappSkeleton()
    {
        if ($domain_query = $this->rootda->execute('SELECT webappskeleton FROM domains WHERE id='.(int)$this->domainid)) {
            return $domain_query->getFields('webappskeleton');
        }
        return '';
    }

    /**
     * Applies a new webapp skeleton to the domain webapp.
     *
     * @param string $skeleton
     * @return bool
     */
    public function setWebappSkeleton($skeleton)
    {
        if ($this->rootda->execute(
            'UPDATE domains SET webappskeleton='.$this->rootda->formatText($skeleton).
            ' WHERE domainid='.$this->rootda->formatText($this->domainid))) {
            require_once('innomatic/webapp/WebAppContainer.php');
            return WebAppContainer::applyNewSkeleton($this->domainid, $skeleton);
        } else {
            return false;
        }
    }


    /*!
     @function ChPasswd

     @abstract Changes domain password

     @discussion This function changes domain and domain superuser password.

     @param password string - New domain password
     */
    public function changePassword($password)
    {
        require_once('innomatic/process/Hook.php');
        $hook = new Hook($this->rootda, 'innomatic', 'domain.chpasswd');
        if ($hook->CallHooks('calltime', $this, array('password' => $password)) == Hook::RESULT_OK) {
            if (strlen($password) and $this->domainserial) {
                // We may require old password if superuser password cannot be changed
                //
                $domainquery = $this->rootda->execute('SELECT domainpassword FROM domains WHERE id='. (int) $this->domainserial);

                // Changes domain password
                //
                if ($this->rootda->execute('UPDATE domains SET domainpassword='.$this->rootda->formatText(md5($password)).' WHERE id='. (int) $this->domainserial)) {
                    // Changes domain superuser password
                    //
                    require_once('innomatic/domain/user/User.php');
                    $tmpuser = new User($this->domainserial);
                    $tmpuser->setUserIDByUserName($this->domainid);
                    $userdata = $tmpuser->getUserData();
                    $qres = $this->dataAccess->execute('UPDATE domain_users SET password = '.$this->rootda->formatText(md5($password)).' WHERE id='. (int) $userdata[id]);

                    if ($qres) {
                        if ($hook->CallHooks('passwordchanged', $this, array('password' => $password)) == Hook::RESULT_OK)
                        return true;
                    } else {
                        // Fallback to old domain password
                        //
                        $this->rootda->execute('UPDATE domains SET domainpassword='.$this->rootda->formatText($domainquery->getFields('domainpassword')).' WHERE id='. (int) $this->domainserial);
                        
                        $this->domainlog->logEvent($this->domainid, 'Unable to change password for user '.$this->domainid.'; restored old domain password', \Innomatic\Logging\Logger::ERROR);
                    }
                } else
                $this->domainlog->logEvent($this->domainid, 'Unable to change domain password', \Innomatic\Logging\Logger::ERROR);
            } else {
                

                if (!strlen($password))
                $this->domainlog->logEvent($this->domainid, 'Empty password', \Innomatic\Logging\Logger::ERROR);
                if (!$this->domainserial)
                $this->domainlog->logEvent($this->domainid, 'Empty domain serial', \Innomatic\Logging\Logger::ERROR);
            }

        }
        return false;
    }

    /*!
     @function Enable

     @abstract Enables the domain

     @result True if the domain has been enabled
     */
    public function enable()
    {
        $result = false;

        if ($this->rootda) {
            if ($this->domainserial) {
                $result = $this->rootda->execute('UPDATE domains SET domainactive='.$this->rootda->formatText($this->rootda->fmttrue).' WHERE id='. (int) $this->domainserial);
                if ($result) {
                    
                    $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
                    $log->logEvent($this->domainid, 'Enabled domain '.$this->domainid, \Innomatic\Logging\Logger::NOTICE);
                    $this->domainlog->logEvent($this->domainid, 'Enabled domain '.$this->domainid, \Innomatic\Logging\Logger::NOTICE);

                    if (InnomaticContainer::instance('innomaticcontainer')->getConfig()->Value('SecurityAlertOnDomainOperation') == '1') {
                        require_once('innomatic/security/SecurityManager.php');

                        $innomatic_security = new SecurityManager();
                        $innomatic_security->SendAlert('Domain '.$this->domainid.' has been enabled');
                        unset($innomatic_security);
                    }
                } else {
                    
                    $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
                    $log->logEvent('innomatic.domains.domain.disable', 'Unable to enable the domain', \Innomatic\Logging\Logger::ERROR);

                    $this->domainlog->logEvent('innomatic.domains.domain.disable', 'Unable to enable the domain', \Innomatic\Logging\Logger::ERROR);
                }
            } else {
                
                $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
                $log->logEvent('innomatic.domains.domain.enable', 'Invalid domain serial', \Innomatic\Logging\Logger::ERROR);
            }
        } else {
            
            $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
            $log->logEvent('innomatic.domains.domain.enable', 'Invalid Innomatic database handler', \Innomatic\Logging\Logger::ERROR);
        }
        return $result;
    }

    /*!
     @function Disable

     @abstract Disables the domain

     @result True if the domain has been disabled
     */
    public function disable()
    {
        $result = false;

        if ($this->rootda) {
            if ($this->domainserial) {
                $result = $this->rootda->execute('UPDATE domains SET domainactive='.$this->rootda->formatText($this->rootda->fmtfalse).' WHERE id='. (int) $this->domainserial);
                if ($result) {
                    
                    $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
                    $log->logEvent($this->domainid, 'Disabled domain '.$this->domainid, \Innomatic\Logging\Logger::NOTICE);

                    $this->domainlog->logEvent($this->domainid, 'Disabled domain '.$this->domainid, \Innomatic\Logging\Logger::NOTICE);

                    if (InnomaticContainer::instance('innomaticcontainer')->getConfig()->Value('SecurityAlertOnDomainOperation') == '1') {
                        require_once('innomatic/security/SecurityManager.php');

                        $innomatic_security = new SecurityManager();
                        $innomatic_security->SendAlert('Domain '.$this->domainid.' has been disabled');
                        unset($innomatic_security);
                    }
                } else {
                    
                    $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
                    $log->logEvent('innomatic.domains.domain.disable', 'Unable to disable the domain', \Innomatic\Logging\Logger::ERROR);
                }
            } else {
                
                $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
                $log->logEvent('innomatic.domains.domain.disable', 'Invalid domain serial', \Innomatic\Logging\Logger::ERROR);
            }
        } else {
            $log->logEvent('innomatic.domains.domain.disable', 'Invalid Innomatic database handler', \Innomatic\Logging\Logger::ERROR);
        }
        return $result;
    }

    /*!
     @function Remove

     @abstract Removes the domain

     @discussion Before removing the domain, this function disables all the applications
     */
    public function remove()
    {
        $result = false;

        require_once('innomatic/process/Hook.php');
        $hook = new Hook($this->rootda, 'innomatic', 'domain.remove');
        if ($hook->CallHooks('calltime', $this, '') == Hook::RESULT_OK) {
            $query = $this->rootda->execute('SELECT * FROM domains WHERE id='. (int) $this->domainserial);
            $data = $query->getFields();

            // Removes domain users.
            // They must be removed before disabling applications
            // and dropping the database.
            $this->removeAllUsers();

            // Disables all applications.
            $this->disableAllApplications($this->domainserial);

            if (InnomaticContainer::instance('innomaticcontainer')->getEdition() == InnomaticContainer::EDITION_SAAS) {
                $args['dbname'] = $data['domaindaname'];
                $args['dbhost'] = $data['dataaccesshost'];
                $args['dbport'] = $data['dataaccessport'];
                $args['dbuser'] = $data['dataaccessuser'];
                $args['dbpass'] = $data['domaindapass'];
                $args['dbtype'] = $data['dataaccesstype'];
                $args['dblog'] = InnomaticContainer::instance('innomaticcontainer')->getHome().'core/domains/'.$data['domainid'].'/log/dataaccess.log';

                $this->dataAccess->close();
                $this->dataAccess->dropDB($args);
            }

            // Removes cached items.
            require_once('innomatic/datatransfer/cache/CacheGarbageCollector.php');
            $cache_gc = new CacheGarbageCollector();
            $cache_gc->removeDomainItems((int)$data['id']);

            // Removes domain from root database.
            $this->rootda->execute('DELETE FROM domains WHERE id='. (int) $data['id']);
            $this->rootda->execute('DELETE FROM applications_options_disabled WHERE domainid='.$this->domainserial);

            
            $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
            $log->logEvent($data['domainid'], 'Removed domain '.$data['domainid'], \Innomatic\Logging\Logger::NOTICE);

            if (!empty($data['domainid']) and !in_array($data['domainid'], $this->reservedNames) ) {
                require_once('innomatic/security/SecurityManager.php');
                if (!SecurityManager::isAboveBasePath(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/domains/'.$data['domainid'], InnomaticContainer::instance('innomaticcontainer')->getHome().'core/domains/')) {
                    // Removes domain directory inside Innomatic webapp
                    \Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/domains/'.$data['domainid']);
                }
                // Removes domain webapp
                require_once('innomatic/webapp/WebAppContainer.php');
                WebAppContainer::eraseWebApp($data['domainid']);
            }

            if ($hook->CallHooks('domainremoved', $this, '') == Hook::RESULT_OK)
            $result = true;

            // Tells the security manager that the domain has been removed.
            if (InnomaticContainer::instance('innomaticcontainer')->getConfig()->Value('SecurityAlertOnDomainOperation') == '1') {
                require_once('innomatic/security/SecurityManager.php');

                $innomatic_security = new SecurityManager();
                $innomatic_security->SendAlert('Domain '.$data['domainid'].' has been removed');
                unset($innomatic_security);
            }
        }

        return $result;
    }

    // Removes all domains users
    //
    public function removeAllUsers()
    {
        $usersquery = $this->dataAccess->execute('SELECT id FROM domain_users');

        if ($usersquery->getNumberRows() > 0) {
            require_once('innomatic/domain/user/User.php');
            $tmpuser = new User($this->domainserial);

            while (!$usersquery->eof) {
                $userdata = $usersquery->getFields();
                $tmpuser->setUserId($userdata['id']);
                $tmpuser->remove();

                $usersquery->moveNext();
            }
        }
        //$this->rootda->execute( "DELETE FROM domain_users where domainid = '$data['id']'" );
    }

    /*!
     @function EnableApplication

     @abstract Enables a application to the domain

     @param appid integer - Application serial
     */
    public function enableApplication($appid)
    {
        $result = false;

        require_once('innomatic/process/Hook.php');
        $hook = new Hook($this->rootda, 'innomatic', 'domain.application.enable');
        if ($hook->CallHooks('calltime', $this, array('domainserial' => $this->domainserial, 'appid' => $appid)) == Hook::RESULT_OK) {
            if (!empty($this->dataAccess) and !empty($appid) and !$this->IsApplicationEnabled($appid)) {
                require_once('innomatic/application/Application.php');

                $modquery = $this->rootda->execute('SELECT appid FROM applications WHERE id='. (int) $appid);

                $tmpmod = new Application($this->rootda, $appid);

                if ($tmpmod->Enable($this->domainserial)) {
                    if ($hook->CallHooks('applicationenabled', $this, array('domainserial' => $this->domainserial, 'appid' => $appid)) == Hook::RESULT_OK)
                    $result = true;

                    
                    $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
                    $log->logEvent($this->domainid, 'Enabled application '.$modquery->getFields('appid'), \Innomatic\Logging\Logger::NOTICE);

                    $this->domainlog->logEvent($this->domainid, 'Enabled application '.$modquery->getFields('appid'), \Innomatic\Logging\Logger::NOTICE);
                }

                $this->unmetdeps = $tmpmod->getLastActionUnmetDeps();
                $this->unmetsuggs = $tmpmod->getLastActionUnmetSuggs();
            } else {
                
                $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();

                if (empty($this->dataAccess))
                $log->logEvent('innomatic.domains.domain.enableapplication', 'Invalid domain database handler', \Innomatic\Logging\Logger::ERROR);

                if (empty($appid))
                $log->logEvent('innomatic.domains.domain.enableapplication', 'Empty application id', \Innomatic\Logging\Logger::ERROR);

                if ($this->IsApplicationEnabled($appid))
                $log->logEvent('innomatic.domains.domain.enableapplication', 'Innomatic already enabled to the domain', \Innomatic\Logging\Logger::ERROR);
            }
        }

        return $result;
    }

    /*!
     @function DisableApplication

     @abstract Disables a application from the domain

     @param appid string - Application name
     */
    public function disableApplication($appid)
    {
        $result = false;

        require_once('innomatic/process/Hook.php');
        $hook = new Hook($this->rootda, 'innomatic', 'domain.application.disable');
        if ($hook->CallHooks('calltime', $this, array('domainserial' => $this->domainserial, 'appid' => $appid)) == Hook::RESULT_OK) {
            if (!empty($this->dataAccess) and !empty($appid) and $this->IsApplicationEnabled($appid)) {
                require_once('innomatic/application/Application.php');

                $modquery = $this->rootda->execute('SELECT appid FROM applications WHERE id='. (int) $appid);

                $tmpmod = new Application($this->rootda, $appid);

                if ($tmpmod->Disable($this->domainserial)) {
                    if ($hook->CallHooks('applicationdisabled', $this, array('domainserial' => $this->domainserial, 'appid' => $appid)) == Hook::RESULT_OK)
                    $result = true;

                    
                    $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
                    $log->logEvent($this->domainid, 'Disabled application '.$modquery->getFields('appid'), \Innomatic\Logging\Logger::NOTICE);

                    $this->domainlog->logEvent($this->domainid, 'Disabled application '.$modquery->getFields('appid'), \Innomatic\Logging\Logger::NOTICE);
                }

                $this->unmetdeps = $tmpmod->getLastActionUnmetDeps();
            }
        }

        return $result;
    }

    public function isApplicationEnabled($appid)
    {
        if (!empty($this->rootda) and !empty($appid)) {
            $actquery = $this->rootda->execute('SELECT * FROM applications_enabled WHERE domainid = '.$this->domainserial.' AND applicationid = '.$appid);
            if ($actquery->getNumberRows())
            return true;
        }
        return false;
    }

    public function getEnabledApplications()
    {
        $query = 'SELECT appid FROM applications
LEFT JOIN applications_enabled ON applications.id = applications_enabled.applicationid
LEFT JOIN domains ON domains.id=applications_enabled.domainid
WHERE domains.domainid = '.$this->rootda->formatText($this->domainid);

        $query_result = $this->rootda->execute($query);
        $list = array();
        while (!$query_result->eof) {
            $list[] = $query_result->getFields('appid');
            $query_result->moveNext();
        }
        return $list;
    }

    public function getLastActionUnmetDeps()
    {
        return (array)$this->unmetdeps;
    }

    public function getLastActionUnmetSuggs()
    {
        return (array)$this->unmetsuggs;
    }

    public function enableAllApplications()
    {
        $result = false;

        $applications_query = InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->execute('SELECT id FROM applications WHERE onlyextension!='.InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->formatText(InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->fmttrue));
        $applications = array();

        while (!$applications_query->eof) {
            if (!$this->IsApplicationEnabled($applications_query->getFields('id'))) {
                $applications[$applications_query->getFields('id')] = $applications_query->getFields('id');
            }

            $applications_query->moveNext();
        }

        $count = 0;
        $max = $this->domainsFactorial(count($applications));

        while (count($applications)) {
            if ($count > $max)
            break;

            $id = current($applications);

            if ($this->EnableApplication($id)) {
                unset($applications[$id]);
            }

            if (count($applications) and !next($applications))
            reset($applications);

            $count ++;
        }

        if (!count($applications))
        $result = true;

        return $result;
    }

    /*!
     @function DisableAllApplications

     @abstract Disables all the applications enabled to the domain
     */
    public function disableAllApplications($innomaticToo = true)
    {
        $result = false;

        if ($this->rootda) {
            // Checks the enabled applications
            //
            $modsquery = $this->rootda->execute('SELECT id FROM applications_enabled,applications WHERE applications_enabled.domainid='. (int) $this->domainserial.' AND applications_enabled.applicationid=applications.id');

            $applications = array();

            while (!$modsquery->eof) {
                $applications[$modsquery->getFields('id')] = $modsquery->getFields('id');
                $modsquery->moveNext();
            }

            $numapplications = $modsquery->getNumberRows();

            $innomaticquery = $this->rootda->execute('SELECT id FROM applications WHERE appid='.$this->rootda->formatText('innomatic'));

            if (!$innomaticToo) {
                unset($applications[$innomaticquery->getFields('id')]);
                $numapplications --;
            }

            // Tries to disable every application since all applications are disabled, following dependencies
            //
            while (count($applications) > 0) {
                $appid = current($applications);
                if ((count($applications) == 1 and $appid == $innomaticquery->getFields('id')) or (count($applications) > 1 and $appid != $innomaticquery->getFields('id')) or (!$innomaticToo)) {
                    require_once('innomatic/application/Application.php');

                    $tmpmod = new Application($this->rootda, $appid);
                    if ($tmpmod->Disable($this->domainserial)) {
                        
                        $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
                        $log->logEvent($this->domainid, 'Disabled application '.$tmpmod->appname, \Innomatic\Logging\Logger::NOTICE);

                        $this->domainlog->logEvent($this->domainid, 'Disabled application '.$tmpmod->appname, \Innomatic\Logging\Logger::NOTICE);

                        unset($applications[$appid]);
                    }
                }
                if (!next($applications))
                reset($applications);
            }
            $result = true;
        }
        return $result;
    }

    public function getMotd()
    {
        if (is_object($this->dataAccess)) {
            require_once('innomatic/domain/DomainSettings.php');
            $sets = new DomainSettings($this->dataAccess);
            return $sets->getKey('domain-motd');
        }
        return false;
    }

    public function setMotd($motd)
    {
        if (is_object($this->dataAccess)) {
            require_once('innomatic/domain/DomainSettings.php');
            $sets = new DomainSettings($this->dataAccess);
            return $sets->setKey('domain-motd', $motd);
        }
        return false;
    }

    public function cleanMotd()
    {
        if (is_object($this->dataAccess)) {
            require_once('innomatic/domain/DomainSettings.php');
            $sets = new DomainSettings($this->dataAccess);
            return $sets->DeleteKey('domain-motd');
        }
        return false;
    }

    public function refreshCachedDomainData()
    {
        $result = false;
        $stquery = $this->rootda->execute('SELECT * FROM domains WHERE domainid = '.$this->rootda->formatText($this->domainserial));
        if ($stquery->getNumberRows() > 0) {
            $this->domaindata = $stquery->getFields();
        }
        return $result;
    }

    public function domainsFactorial($s)
    {
        $r = (int) $s;
        for ($i = $r; $i --; $i > 1) {
            if ($i) {
                $r = $r * $i;
            }
        }
        return $r;
    }

    public function setDataAccess(DataAccess $da)
    {
        $this->dataAccess = $da;
    }

    public function getDataAccess()
    {
        return $this->dataAccess;
    }

    public function getLanguage()
    {
        require_once('innomatic/domain/DomainSettings.php');
        $domain_settings = new DomainSettings(
        $this->dataAccess);
        $key = $domain_settings->getKey('desktop-language');
        return strlen($key) ? $key : InnomaticContainer::instance('innomaticcontainer')->getLanguage();
    }

    public function getCountry()
    {
        require_once('innomatic/domain/DomainSettings.php');
        $domain_settings = new DomainSettings(
        $this->dataAccess);
        $key = $domain_settings->getKey('desktop-country');
        return strlen($key) ? $key : InnomaticContainer::instance('innomaticcontainer')->getCountry();
    }

    public function getDomainId()
    {
        return $this->domainid;
    }

    public static function getDomainByHostname($hostname = '')
    {
        if (InnomaticContainer::instance('innomaticcontainer')->getEdition() == InnomaticContainer::EDITION_ENTERPRISE) {
            return false;
        }

        if (!strlen($hostname) and InnomaticContainer::instance('innomaticcontainer')->getInterface() != InnomaticContainer::INTERFACE_WEB) {
            return false;
        }

        if (!strlen($hostname)) {
            require_once('innomatic/webapp/WebAppContainer.php');
            $hostname = WebAppContainer::instance('webappcontainer')->getProcessor()->getRequest()->getServerName();
        }

        // Is it still empty?
        if (!strlen($hostname)) {
            return false;
        }

        $pos = strpos($hostname, '.');

        if ($pos === false) {
            $domain_guess = $hostname;
        } else {
            $domain_guess = substr($hostname, 0, $pos);
        }

        if (!strlen($domain_guess)) {
            return false;
        }

        $domain_query = InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->execute(
                'SELECT domainid FROM domains WHERE domainid='.
                InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->formatText($domain_guess));
        if ($domain_query->getNumberRows() == 1) {
            return $domain_guess;
        }

        return false;
    }
}
