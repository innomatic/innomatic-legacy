<?php
/**
 * Innomatic
 *
 * LICENSE 
 * 
 * This source file is subject to the new BSD license that is bundled 
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2012 Innoteam S.r.l.
 * @license    http://www.innomatic.org/license/   BSD License
 * @link       http://www.innomatic.org
 * @since      Class available since Release 5.0
*/

class SecurityManager
{
    public $mAlertsEmail;
    public $mReportsEmail;
    public $mSecurityLog;
    public $mAccessLog;

    const PRESET_LOW = 1;
    const PRESET_NORMAL = 2;
    const PRESET_HIGH = 3;
    const PRESET_PARANOID = 4;

    /*!
     @function SecurityManager
     @abstract Class constructor.
     */
    public function __construct()
    {
        $this->mSecurityLog = InnomaticContainer::instance('innomaticcontainer')->getHome().'core/log/security.log';
        $this->mAccessLog = InnomaticContainer::instance('innomaticcontainer')->getHome().'core/log/access.log';
    }

    /*!
     @function setPredefinedLevel
     @abstract Sets the security profile as a defined preset.
     @param level integer - One of the defined presets.
     @result True if the preset exists.
     */
    public function setPredefinedLevel($level)
    {
        $result = true;

        switch ($level) {
            case SecurityManager::PRESET_LOW :
                $maxWrongLogins = 1000;
                $wrongLoginDelay = 0;
                $sessionLifetime = 525600;
                $alerts = array('wronglocalrootlogin' => false, 'wronglocaluserlogin' => false, 'wrongwebserviceslogin' => false, 'applicationoperation' => false, 'applicationdomainoperation' => false, 'domainoperation' => false);
                $reportsInterval = 0;
                break;

            case SecurityManager::PRESET_NORMAL :
                $maxWrongLogins = 3;
                $wrongLoginDelay = 1;
                $sessionLifetime = 525600;
                $alerts = array('wronglocalrootlogin' => false, 'wronglocaluserlogin' => false, 'wrongwebserviceslogin' => false, 'applicationoperation' => false, 'applicationdomainoperation' => false, 'domainoperation' => false);
                $reportsInterval = 0;
                break;

            case SecurityManager::PRESET_HIGH :
                $maxWrongLogins = 3;
                $wrongLoginDelay = 2;
                $sessionLifetime = 525600;
                $alerts = array('wronglocalrootlogin' => true, 'wronglocaluserlogin' => false, 'wrongwebserviceslogin' => true, 'applicationoperation' => true, 'applicationdomainoperation' => false, 'domainoperation' => true);
                $reportsInterval = 7;
                break;

            case SecurityManager::PRESET_PARANOID :
                $maxWrongLogins = 1;
                $wrongLoginDelay = 3;
                $sessionLifetime = 0;
                $alerts = array('wronglocalrootlogin' => true, 'wronglocaluserlogin' => true, 'wrongwebserviceslogin' => true, 'applicationoperation' => true, 'applicationdomainoperation' => true, 'domainoperation' => true);
                $reportsInterval = 1;
                break;

            default :
                $result = false;
        }

        if ($result) {
            $this->setMaxWrongLogins($maxWrongLogins);
            $this->setWrongLoginDelay($wrongLoginDelay);
            $this->setSessionLifetime($sessionLifetime);
            $this->setAlertEvents($alerts);
            $this->setReportsInterval($reportsInterval);
        }

        return $result;
    }

    /*!
     @function setAlertsEmail
     @abstract Sets alerts destination email.
     @param email string - Destination email.
     */
    public function setAlertsEmail($email)
    {
        $result = '';
        require_once('innomatic/config/ConfigFile.php');
        $cfg = new ConfigFile(
            InnomaticContainer::instance(
                'innomaticcontainer'
            )->getConfigurationFile()
        );
        $result = $cfg->setValue('SecurityAlertsEmail', $email);
        return $result;
    }

    /*!
     @function getAlertsEmail
     @abstract Gets alerts destination email.
     @result Destination email.
     */
    public function getAlertsEmail()
    {
        $cfg = @parse_ini_file(
            InnomaticContainer::instance(
                'innomaticcontainer'
            )->getConfigurationFile()
        );
        return $cfg['SecurityAlertsEmail'];
    }

    /*!
     @function setReportsEmail
     @abstract Sets reports destination email.
     @param email string - Destination email.
     */
    public function setReportsEmail($email)
    {
        $result = '';
        require_once('innomatic/config/ConfigFile.php');
        $cfg = new ConfigFile(InnomaticContainer::instance('innomaticcontainer')->getConfigurationFile());
        $result = $cfg->setValue('SecurityReportsEmail', $email);
        return $result;
    }

    /*!
     @function getReportsEmail
     @abstract Gets reports destination email.
     @result Destination email.
     */
    public function getReportsEmail()
    {
        $cfg = @parse_ini_file(InnomaticContainer::instance('innomaticcontainer')->getConfigurationFile());
        return isset($cfg['SecurityReportsEmail']) ? $cfg['SecurityReportsEmail'] : '';
    }

    /*!
     @function setSessionLifetime
     @abstract Sets session lifetime.
     @param lifeTime integer - Session lifetime in seconds.
     */
    public function setSessionLifetime($lifeTime)
    {
        $result = '';
        require_once('innomatic/config/ConfigFile.php');
        $cfg = new ConfigFile(InnomaticContainer::instance('innomaticcontainer')->getConfigurationFile());
        $result = $cfg->setValue('DesktopSessionLifetime', $lifeTime);
        return $result;
    }

    /*!
     @function getSessionLifetime
     @abstract Gets session lifetime.
     @result Session lifetime in seconds.
     */
    public function getSessionLifetime()
    {
        $cfg = @parse_ini_file(InnomaticContainer::instance('innomaticcontainer')->getConfigurationFile());
        return $cfg['DesktopSessionLifetime'];
    }

    /*!
     @function setMaxWrongLogins
     @abstract Sets the max number of wrong logins.
     @param maxWrongLogins integer - Max number of wrong logins.
     @result True if the setting has been applied.
     */
    public function setMaxWrongLogins($maxWrongLogins)
    {
        $result = '';
        require_once('innomatic/config/ConfigFile.php');
        $cfg = new ConfigFile(InnomaticContainer::instance('innomaticcontainer')->getConfigurationFile());
        $result = $cfg->setValue('MaxWrongLogins', $maxWrongLogins);
        return $result;
    }

    /*!
     @function getMaxWrongLogins
     @abstract Gets the max number of allowed wrong logins.
     @result The max number of allowed wrong logins.
     */
    public function getMaxWrongLogins()
    {
        $cfg = @parse_ini_file(InnomaticContainer::instance('innomaticcontainer')->getConfigurationFile());
        $result = $cfg['MaxWrongLogins'];
        if (!strlen($result))
        $result = 3;

        return $result;
    }

    /*!
     @function setWrongLoginDelay
     @abstract Sets the delay before a new login when the previous one was wrong.
     @param delay integer - Delay in seconds.
     @result True if the setting has been applied.
     */
    public function setWrongLoginDelay($delay)
    {
        require_once('innomatic/config/ConfigFile.php');
        $cfg = new ConfigFile(InnomaticContainer::instance('innomaticcontainer')->getConfigurationFile());
        $result = $cfg->setValue('WrongLoginDelay', $delay);
        return $result;
    }

    /*!
     @function getWrongLoginDelay
     @abstract Gets the delay before a new login when the previous one was wrong.
     @result The delay in seconds.
     */
    public function getWrongLoginDelay()
    {
        $cfg = @parse_ini_file(InnomaticContainer::instance('innomaticcontainer')->getConfigurationFile());
        $result = $cfg['WrongLoginDelay'];
        if (!strlen($result))
        $result = 1;
        return $result;
    }

    /*!
     @function LockUnsecureWebServices
     @abstract Locks the unsecure web services, even if enabled.
     @result Always true.
     */
    public function lockUnsecureWebServices()
    {
        require_once('innomatic/config/ConfigFile.php');
        $cfg = new ConfigFile(InnomaticContainer::instance('innomaticcontainer')->getConfigurationFile());
        $result = $cfg->setValue('SecurityLockUnsecureWebservices', '1');
        return $result;
    }

    /*!
     @function getUnsecureWebServicesLock
     @abstract Tells if the unsecure web services are locked.
     @result True if the unsecure web services are locked, false otherwise.
     */
    public function getUnsecureWebServicesLock()
    {
        $result = false;
        $cfg = @parse_ini_file(InnomaticContainer::instance('innomaticcontainer')->getConfigurationFile());
        if ($cfg['SecurityLockUnsecureWebservices'] == '1')
        $result = true;
        return $result;
    }

    /*!
     @function AcceptOnlyHttpsRootAccess
     @result Always true.
     */
    public function acceptOnlyHttpsRootAccess($accept = true)
    {
        require_once('innomatic/config/ConfigFile.php');
        $cfg = new ConfigFile(InnomaticContainer::instance('innomaticcontainer')->getConfigurationFile());
        $result = $cfg->setValue('SecurityOnlyHttpsRootAccessAllowed', $accept ? '1' : '0');
        return $result;
    }

    /*!
     @function getOnlyHttpsRootAccess
     @abstract Tells if the only https root access is allowed.
     @result True if https is needed.
     */
    public function getOnlyHttpsRootAccess()
    {
        $result = false;
        $cfg = @parse_ini_file(InnomaticContainer::instance('innomaticcontainer')->getConfigurationFile());
        if ($cfg['SecurityOnlyHttpsRootAccessAllowed'] == '1')
        $result = true;
        return $result;
    }

    /*!
     @function AcceptOnlyHttpsDomainAccess
     @result Always true.
     */
    public function AcceptOnlyHttpsDomainAccess($accept = true)
    {
        require_once('innomatic/config/ConfigFile.php');
        $cfg = new ConfigFile(InnomaticContainer::instance('innomaticcontainer')->getConfigurationFile());
        $result = $cfg->setValue('SecurityOnlyHttpsDomainAccessAllowed', $accept ? '1' : '0');
        return $result;
    }

    /*!
     @function getOnlyHttpsDomainAccess
     @abstract Tells if the only https domain access is allowed.
     @result True if https is needed.
     */
    public function getOnlyHttpsDomainAccess()
    {
        $result = false;
        $cfg = @parse_ini_file(InnomaticContainer::instance('innomaticcontainer')->getConfigurationFile());
        if ($cfg['SecurityOnlyHttpsDomainAccessAllowed'] == '1')
        $result = true;
        return $result;
    }

    /*!
     @function UnlockUnsecureWebServices
     @abstract Unlocks the unsecure web services.
     @result Always true.
     */
    public function unlockUnsecureWebServices()
    {
        require_once('innomatic/config/ConfigFile.php');
        $cfg = new ConfigFile(InnomaticContainer::instance('innomaticcontainer')->getConfigurationFile());
        $result = $cfg->setValue('SecurityLockUnsecureWebservices', '0');
        return $result;
    }

    /*!
     @function LogAccess
     @abstract Logs an access to the administration area.
     @result Always true.
     */
    public function logAccess($user = '', $logout = false, $root = false, $ip = '')
    {
        require_once('innomatic/logging/Logger.php');
        $log = new Logger($this->mAccessLog);
        $log->logEvent('innomatic', ($root ? 'Root ' : 'User '.$user.' '). ($logout ? 'logged out' : 'logged in'). (strlen($ip) ? ' from address '.$ip : ''), Logger::NOTICE);
        return true;
    }

    /*!
     @function LogFailedAccess
     @abstract Logs a failed access to the administration area.
     @result Always true.
     */
    public function logFailedAccess($user = '', $root = false, $ip = '')
    {
        require_once('innomatic/logging/Logger.php');
        $log = new Logger($this->mAccessLog);
        $log->logEvent('innomatic', 'Wrong access from '. ($root ? 'root ' : 'user '.$user.' '). (strlen($ip) ? 'from address '.$ip : ''), Logger::NOTICE);
        return true;
    }

    /*!
     @function getAccessLog
     @abstract Returns the access log content.
     @result The access log full content.
     */
    public function getAccessLog()
    {
        if (file_exists($this->mAccessLog)) {
            return file_get_contents($this->mAccessLog);
        }
        return '';
    }

    /*!
     @function EraseAccessLog
     @abstract Erases the entire access log.
     @result Always true.
     */
    public function eraseAccessLog()
    {
        require_once('innomatic/logging/Logger.php');
        $log = new Logger($this->mAccessLog);
        $log->cleanLog();
        return true;
    }

    public function logEvent($event)
    {
    }

    public function getEventsLog()
    {
    }

    public function eraseEventsLog()
    {
    }

    public function logoutSession($session)
    {
        $result = true;
        if (strlen($session)) {
            if (file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/phpsessions/'.$session))
            $result = unlink(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/phpsessions/'.$session);
        } else
        $result = false;
        return $result;
    }

    // ----- Security check -----

    public function securityCheck()
    {
        $result = array();
        $result['unsecurewebservicesprofiles'] = $this->getUnsecureWebServicesProfiles();
        $result['unsecurelocalaccounts'] = $this->getUnsecureLocalAccounts();
        $result['unsecurewebservicesaccounts'] = $this->getUnsecureWebServicesAccounts();
        $result['rootpasswordcheck'] = $this->CheckRootPassword();
        $result['rootdapasswordcheck'] = $this->CheckRootDatabasePassword();
        $result['domainswithunsecuredbpassword'] = $this->getDomainsWithUnsecureDatabasePassword();
        $result['registerglobals'] = $this->getRegisterGlobalsSetting();
        return $result;
    }

    /*!
     @function getUnsecureWebServicesProfiles
     @abstract Gets the list of the web services profiles with unsecure methods enabled.
     @result Array of the profiles id and name.
     */
    public function getUnsecureWebServicesProfiles()
    {
        $result = array();
        $innomatic_db = InnomaticContainer::instance('innomaticcontainer')->getDataAccess();

        $query = $innomatic_db->execute('SELECT webservices_permissions.profileid AS profileid,webservices_profiles.profilename AS profilename FROM webservices_permissions,webservices_methods,webservices_profiles WHERE ((webservices_permissions.method=webservices_methods.name AND webservices_methods.unsecure='.$innomatic_db->formatText($innomatic_db->fmttrue).') OR (webservices_permissions.method=\'\' AND webservices_permissions.application=webservices_methods.application AND webservices_methods.unsecure='.$innomatic_db->formatText($innomatic_db->fmttrue).')) AND webservices_profiles.id=webservices_permissions.profileid GROUP BY webservices_permissions.profileid,webservices_profiles.profilename');
        while (!$query->eof) {
            $result[$query->getFields('profileid')] = $query->getFields('profilename');
            $query->moveNext();
        }
        return $result;
    }

    /*!
     @function getUnsecureLocalAccounts
     @abstract Tells which locale accounts have a too simple password. At this time it only checks
     if the username is the same as the password.
     @result An array of the accounts with a too simple password.
     */
    public function getUnsecureLocalAccounts()
    {
        $result = array();
        // TODO Must be adapted to new domain_users table stored in domains database
        return $result;

        $innomaticDA = InnomaticContainer::instance('innomaticcontainer')->getDataAccess();
        $usersQuery = $innomaticDA->execute('SELECT username,password FROM domain_users ORDER BY username');
        while (!$usersQuery->eof) {
            $completeUsername = $usersQuery->getFields('username');
            $cryptedPassword = $usersQuery->getFields('password');

            if (strpos($completeUsername, '@')) {
                $username = substr($completeUsername, 0, strpos($completeUsername, '@'));
                $domain = substr($completeUsername, strpos($completeUsername, '@') + 1);
            } else
            $username = $domain = $completeUsername;

            if (md5($username) == $cryptedPassword or md5($domain) == $cryptedPassword or md5($completeUsername) == $cryptedPassword)
            $result[] = $completeUsername;

            $usersQuery->moveNext();
        }
        return $result;
    }

    /*!
     @function getUnsecureWebServicesAccounts

     @abstract Tells which web services accounts have a too simple password. At this time it only checks
     if the username is the same as the password.

     @result An array of the accounts with a too simple password.
     */
    public function getUnsecureWebServicesAccounts()
    {
        $result = array();
        $innomaticDA = InnomaticContainer::instance('innomaticcontainer')->getDataAccess();
        $usersQuery = $innomaticDA->execute('SELECT username,password FROM webservices_users ORDER BY username');
        while (!$usersQuery->eof) {
            $username = $usersQuery->getFields('username');
            $cryptedPassword = $usersQuery->getFields('password');

            if (md5($username) == $cryptedPassword) {
                $result[] = $username;
            }

            $usersQuery->moveNext();
        }
        return $result;
    }

    public function checkRootPassword()
    {
        $result = true;
        $fh = @fopen(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/conf/rootpasswd.ini', 'r');
        if ($fh) {
            $password = fgets($fh, 4096);
            if (md5('') == $password or md5('root') == $password)
            $result = false;
            fclose($fh);
        }
        return $result;
    }

    public function checkRootDatabasePassword()
    {
        $result = true;
        $cfg = InnomaticContainer::instance('innomaticcontainer')->getConfig();
        $username = $cfg->Value('RootDatabaseUser');
        $password = $cfg->Value('RootDatabasePassword');

        if ($password == '' or $username == $password)
        $result = false;
        return $result;
    }

    public function getDomainsWithUnsecureDatabasePassword()
    {
        $result = array();

        $innomaticDA = InnomaticContainer::instance('innomaticcontainer')->getDataAccess();
        $domainsQuery = $innomaticDA->execute('SELECT id,domainid FROM domains WHERE dataaccessuser=dataaccesspassword OR dataaccesspassword=\'\' ORDER BY domainid');
        while (!$domainsQuery->eof) {
            $result[$domainsQuery->getFields('id')] = $domainsQuery->getFields('domainid');
            $domainsQuery->moveNext();
        }
        return $result;
    }

    /*!
     @function getRegisterGlobalsSetting

     @abstract Gets the PHP register_globals ini setting.

     @result True if set to on, false if set to off.
     */
    public function getRegisterGlobalsSetting()
    {
        if (ini_get('register_globals')) {
            return true;
        } else {
            return false;
        }
    }

    /*!
     @function getLoggedSessions

     @abstract Gets the list of the sessions with logged root and domain users.

     @result Array of sessions.
     */
    public function getLoggedSessions()
    {
        $result['root'] = $result['domains'] = array();
        $dir = InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/phpsessions/';

        if (is_dir($dir)) {
            $dh = opendir($dir);
            if ($dh) {
                while (($file = readdir($dh)) !== false) {
                    if ($file != '.' and $file != '..') {
                        if (filesize($dir.$file)) {
                            $content = file($dir.$file);

                            $extracted = $this->_sessStringToArray($content[0]);

                            if (isset($extracted['INNOMATIC_ROOT_AUTH_USER'])) {
                                $result['root'][] = $file;
                            }

                            if (isset($extracted['INNOMATIC_AUTH_USER'])) {
                                $result['domains'][$extracted['INNOMATIC_AUTH_USER']][] = $file;
                            }
                        }
                    }
                }

                closedir($dh);
            }
        }
        return $result;
    }

    private function _sessStringToArray($sd)
    {
        $sessArray = Array();
        $vars = explode(';', $sd);
        for ($i = 0; $i < sizeof($vars); $i ++) {
            $parts = explode('|', $vars[$i]);
            $key = $parts[0];
            $val = unserialize($parts[1].';');

            $sessArray[$key] = $val;
        }
        return $sessArray;
    }

    // ----- Reports -----

    public function setReportsInterval($interval)
    {
        require_once('innomatic/config/ConfigFile.php');
        $cfg = new ConfigFile(InnomaticContainer::instance('innomaticcontainer')->getConfigurationFile());
        $result = $cfg->setValue('SecurityReportsInterval', $interval);
        return $result;
    }

    public function getReportsInterval()
    {
        $cfg = @parse_ini_file(InnomaticContainer::instance('innomaticcontainer')->getConfigurationFile());
        $result = $cfg['SecurityReportsInterval'];
        if (!strlen($result)) {
            $result = 0;
        }
        return $result;
    }

    public function sendReport()
    {
        $result = false;
        $email = $this->getReportsEmail();
        if (strlen($email)) {
            $secCheck = $this->SecurityCheck();

            if ($secCheck['rootpasswordcheck'] == true) {
                $rootPasswordCheck = 'Root password should be safe'."\n";
            } else {
                $rootPasswordCheck = 'Root password is NOT safe'."\n";
            }

            if ($secCheck['rootdapasswordcheck'] == true) {
                $rootDbPasswordCheck = 'Root database password should be safe'."\n";
            } else { 
                $rootDbPasswordCheck = 'Root database password is NOT safe'."\n";
            }

            if (count($secCheck['unsecurewebservicesprofiles'])) {
                $unsecureWebServicesProfiles = '';

                while (list (, $profile) = each($secCheck['unsecurewebservicesprofiles'])) {
                    $unsecureWebServicesProfiles.= $profile."\n";
                }
            } else
            $unsecureWebServicesProfiles = 'No unsecure web services profiles.'."\n";

            if (count($secCheck['unsecurewebservicesaccounts'])) {
                $unsecureWebServicesAccounts = '';

                while (list (, $account) = each($secCheck['unsecurewebservicesaccounts'])) {
                    $unsecureWebServicesAccounts.= (strlen($account) ? $account : 'Anonymous user')."\n";
                }
            } else {
                $unsecureWebServicesAccounts = 'No unsecure web services accounts.'."\n";
            }

            if (count($secCheck['unsecurelocalaccounts'])) {
                $unsecureLocalAccounts = '';

                while (list (, $account) = each($secCheck['unsecurelocalaccounts'])) {
                    $unsecureLocalAccounts.= $account."\n";
                }
            } else {
                $unsecureLocalAccounts = 'No unsecure local accounts.'."\n";
            }

            if (count($secCheck['domainswithunsecuredbpassword'])) {
                $unsecureDbDomains = '';

                while (list (, $domain) = each($secCheck['domainswithunsecuredbpassword'])) {
                    $unsecureDbDomains.= $domain."\n";
                }
            } else {
                $unsecureDbDomains = 'No unsecure domain database passwords.'."\n";
            }

            $config = '';
            if (file_exists(InnomaticContainer::instance('innomaticcontainer')->getConfigurationFile())) {
                $config = file_get_contents(InnomaticContainer::instance('innomaticcontainer')->getConfigurationFile());
            }

            $result = mail($email, '[INNOMATIC SECURITY REPORT] - Scheduled security report about '.InnomaticContainer::instance('innomaticcontainer')->getPlatformName().'.'.InnomaticContainer::instance('innomaticcontainer')->getPlatformGroup(), 'This is the scheduled security report about '.InnomaticContainer::instance('innomaticcontainer')->getPlatformName().'.'.InnomaticContainer::instance('innomaticcontainer')->getPlatformGroup()."\n\n".'== SECURITY CHECK RESULTS =='."\n"."\n".'--> Root password check'."\n".$rootPasswordCheck."\n".'--> Root database password check'."\n".$rootDbPasswordCheck."\n".'--> Domains with unsecure database password'."\n".$unsecureDbDomains."\n".'--> Unsecure local accounts'."\n".$unsecureLocalAccounts."\n".'--> Unsecure web services profiles'."\n".$unsecureWebServicesProfiles."\n".'--> Unsecure web services accounts'."\n".$unsecureWebServicesAccounts."\n".'== CURRENT INNOMATIC CONFIGURATION FILE CONTENT =='."\n\n".$config);

            if ($result) {
                require_once('innomatic/config/ConfigFile.php');
                $cfg = new ConfigFile(InnomaticContainer::instance('innomaticcontainer')->getConfigurationFile());
                $cfg->setValue('SecurityLastReportTime', time());
            }
        }

        return $result;
    }

    // ----- Alerts -----

    /*!
     @function setAlertEvents
     @abstract Sets which events have to be notified by email.
     @param events array - Array of the events to be notified. Allowed keys:
     wronglocalrootlogin, wronglocaluserlogin, wrongwebserviceslogin,
     applicationoperation, applicationdomainoperation, domainoperation.
     @result Always true.
     */
    public function setAlertEvents($events)
    {
        require_once('innomatic/config/ConfigFile.php');
        $cfg = new ConfigFile(InnomaticContainer::instance('innomaticcontainer')->getConfigurationFile());
        $cfg->setValue('SecurityAlertOnWrongLocalRootLogin', $events['wronglocalrootlogin'] ? '1' : '0');
        $cfg->setValue('SecurityAlertOnWrongLocalUserLogin', $events['wronglocaluserlogin'] ? '1' : '0');
        $cfg->setValue('SecurityAlertOnWrongWebServicesLogin', $events['wrongwebserviceslogin'] ? '1' : '0');
        $cfg->setValue('SecurityAlertOnApplicationOperation', $events['applicationoperation'] ? '1' : '0');
        $cfg->setValue('SecurityAlertOnApplicationDomainOperation', $events['applicationdomainoperation'] ? '1' : '0');
        $cfg->setValue('SecurityAlertOnDomainOperation', $events['domainoperation'] ? '1' : '0');
        return true;
    }

    /*!
     @function getAlertEvents
     @abstract Tells which events have to be notified by email.
     @result Array of the events.
     */
    public function getAlertEvents()
    {
        $result = array();
        $cfg = @parse_ini_file(InnomaticContainer::instance('innomaticcontainer')->getConfigurationFile());
        $result['wronglocalrootlogin'] = $cfg['SecurityAlertOnWrongLocalRootLogin'] == '1' ? true : false;
        $result['wronglocaluserlogin'] = $cfg['SecurityAlertOnWrongLocalUserLogin'] == '1' ? true : false;
        $result['wrongwebserviceslogin'] = $cfg['SecurityAlertOnWrongWebServicesLogin'] == '1' ? true : false;
        $result['applicationoperation'] = $cfg['SecurityAlertOnApplicationOperation'] == '1' ? true : false;
        $result['applicationdomainoperation'] = $cfg['SecurityAlertOnApplicationDomainOperation'] == '1' ? true : false;
        $result['domainoperation'] = $cfg['SecurityAlertOnDomainOperation'] == '1' ? true : false;
        return $result;
    }

    /*!
     @function SendAlert
     @abstract Send an event alert by email.
     @param event string - Event description.
     @result True if the email has been sent.
     */
    public function sendAlert($event)
    {
        $result = false;
        $email = $this->getAlertsEmail();
        if (strlen($email)) {
            $result = mail($email, '[INNOMATIC SECURITY ALERT] - Security alert on '.InnomaticContainer::instance('innomaticcontainer')->getPlatformName().'.'.InnomaticContainer::instance('innomaticcontainer')->getPlatformGroup(), 'Warning: an event marked to be notified has been issued on '.InnomaticContainer::instance('innomaticcontainer')->getPlatformName().'.'.InnomaticContainer::instance('innomaticcontainer')->getPlatformGroup()."\n\n".'Event was: '.$event);
        }
        return $result;
    }

    // ----- Misc functions -----

    // Encrypts a password
    //
    public static function cryptpasswd($password)
    {
        srand((double) microtime() * 1000000);

        // Generates salt string for the crypt function
        //
        $random = rand();
        $rand = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $salt = substr($rand, $random % 64, 1)
            . substr($rand, ($random / 64) % 64, 1);
        $salt = substr($salt, 0, 2);

        return crypt($password, $salt);
    }

    /**
     * Tells if the realpath of $path goes above the $base path.
     *
     * @param string $path
     * @param string $base
     * @return bool
     */
    public static function isAboveBasePath($path, $base)
    {
        $path = self::getAbsolutePath($path);
        $base = self::getAbsolutePath($base);
        return 0 !== strncmp($path, $base, strlen($base));
    }
    
    public static function getAbsolutePath($path)
    {
        $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
        $absolutes = array();
        foreach ($parts as $part) {
            if ('.' == $part) continue;
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }
        return implode(DIRECTORY_SEPARATOR, $absolutes);
    }
}
