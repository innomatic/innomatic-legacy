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

// NOTE: This is an old-style panel code with a single file
// acting as model, view and controller.

require_once('innomatic/locale/LocaleCatalog.php');
require_once('innomatic/wui/Wui.php');
require_once('innomatic/wui/dispatch/WuiEventsCall.php');
require_once('innomatic/security/SecurityManager.php');

    global $gLocale, $gPageStatus, $alertText;
    global $gXmlDefinition, $gLocale, $gPageTitle;
    
$alertText = '';

$gLocale = new LocaleCatalog(
    'innomatic::root_security',
    InnomaticContainer::instance('innomaticcontainer')->getLanguage()
);
$gWui = Wui::instance('wui');
$gWui->loadWidget('xml');
$gWui->loadWidget('innomaticpage');
$gWui->loadWidget('innomatictoolbar');

$gXmlDefinition = $gPageStatus = '';
$gPageTitle = $gLocale->getStr('security.title');

$gToolbars['main'] = array(
    'check' => array(
        'label' => $gLocale->getStr('check.toolbar'),
        'themeimage' => 'zoom',
        'horiz' => 'true',
        'action' => WuiEventsCall::buildEventsCallString(
            '',
            array(
                array(
                    'view',
                    'default',
                    ''
                )
            )
        )
    ),
    'settings' => array(
        'label' => $gLocale->getStr('settings.toolbar'),
        'themeimage' => 'configure',
        'horiz' => 'true',
        'action' => WuiEventsCall::buildEventsCallString(
            '',
            array(
                array(
                    'view',
                    'settings',
                    ''
                )
            )
        )
    )
);

$gToolbars['password'] = array(
    'change_password' => array(
        'label' => $gLocale->getStr('chpasswd_button'),
        'themeimage' => 'password',
        'horiz' => 'true', 'action' => WuiEventsCall::buildEventsCallString(
            '',
            array(
                array(
                    'view',
                    'change_password',
                    ''
                )
            )
        )
    )
);

$gToolbars['help'] = array(
    'help' => array(
        'label' => $gLocale->getStr('help.toolbar'),
        'themeimage' => 'info',
        'horiz' => 'true',
        'action' => WuiEventsCall::buildEventsCallString(
            '',
            array(
                array(
                    'view',
                    'help',
                    ''
                )
            )
        )
    )
);

// ----- Action dispatcher -----
//
$gActionDispatcher = new WuiDispatcher('action');

$gActionDispatcher->addEvent('set_security_preset', 'action_set_security_preset');
function action_set_security_preset($eventData)
{
    global $gLocale, $gPageStatus;

    $innomaticSecurity = new SecurityManager();

    $innomaticSecurity->setPredefinedLevel($eventData['preset']);

    $gPageStatus = $gLocale->getStr('security_settings_set.status');
}

$gActionDispatcher->addEvent('set_access_prefs', 'action_set_access_prefs');
function action_set_access_prefs($eventData)
{
    global $gLocale, $gPageStatus;

    $innomaticSecurity = new SecurityManager();

    $innomaticSecurity->setSessionLifetime($eventData['sessionlifetime']);
    $innomaticSecurity->setMaxWrongLogins($eventData['maxwronglogins']);
    $innomaticSecurity->setWrongLoginDelay($eventData['wronglogindelay']);
    $eventData['lockunsecurewebservices'] == 'on'
        ? $innomaticSecurity->LockUnsecureWebServices() : $innomaticSecurity->UnLockUnsecureWebServices();
    $eventData['onlyhttpsroot'] == 'on'
        ? $innomaticSecurity->AcceptOnlyHttpsRootAccess(true) : $innomaticSecurity->AcceptOnlyHttpsRootAccess(false);
    $eventData['onlyhttpsdomain'] == 'on'
        ? $innomaticSecurity->AcceptOnlyHttpsDomainAccess(true)
        : $innomaticSecurity->AcceptOnlyHttpsDomainAccess(false);

    $gPageStatus = $gLocale->getStr('security_settings_set.status');
}

$gActionDispatcher->addEvent('set_alerts_prefs', 'action_set_alerts_prefs');
function action_set_alerts_prefs($eventData)
{
    global $gLocale, $gPageStatus;

    $innomaticSecurity = new SecurityManager();

    $alerts['wronglocalrootlogin'] = $eventData['wronglocalrootlogin'] == 'on' ? true : false;
    $alerts['wronglocaluserlogin'] = $eventData['wronglocaluserlogin'] == 'on' ? true : false;
    $alerts['wrongwebserviceslogin'] = $eventData['wrongwebserviceslogin'] == 'on' ? true : false;
    $alerts['applicationoperation'] = $eventData['applicationoperation'] == 'on' ? true : false;
    $alerts['applicationdomainoperation'] = $eventData['domainapplicationoperation'] == 'on' ? true : false;
    $alerts['domainoperation'] = $eventData['domainoperation'] == 'on' ? true : false;

    $innomaticSecurity->setAlertEvents($alerts);
    $innomaticSecurity->setAlertsEmail($eventData['alertdestinationemail']);

    $gPageStatus = $gLocale->getStr('security_settings_set.status');
}

$gActionDispatcher->addEvent('set_reports_prefs', 'action_set_reports_prefs');
function action_set_reports_prefs($eventData)
{
    global $gLocale, $gPageStatus;

    $innomaticSecurity = new SecurityManager();

    $innomaticSecurity->setReportsEmail($eventData['reportdestinationemail']);
    $innomaticSecurity->setReportsInterval($eventData['enablereports'] == 'on' ? $eventData['reportsinterval'] : '0');

    $gPageStatus = $gLocale->getStr('security_settings_set.status');
}

$gActionDispatcher->addEvent('clean_access_log', 'action_clean_access_log');
function action_clean_access_log($eventData)
{
    global $gLocale, $gPageStatus;

    $innomaticSecurity = new SecurityManager();

    $innomaticSecurity->EraseAccessLog();

    $gPageStatus = $gLocale->getStr('access_log_erased.status');
}

$gActionDispatcher->addEvent('logout_sessions', 'action_logout_sessions');
function action_logout_sessions($eventData)
{
    global $gLocale, $gPageStatus;

    $innomaticSecurity = new SecurityManager();

    foreach ($eventData['sessions'] as $id => $session) {
        $innomaticSecurity->LogoutSession($session);
    }

    $gPageStatus = $gLocale->getStr('sessions_logged_out.status');
}

$gActionDispatcher->addEvent('change_password', 'action_change_password');
function action_change_password($eventData)
{
    global $gPageStatus, $gLocale;

    if ($eventData['newpassworda'] == $eventData['newpasswordb']) {
        $result = InnomaticContainer::setRootPassword($eventData['oldpassword'], $eventData['newpassworda']);

        switch ($result) {
            case 1 :
                $gPageStatus = $gLocale->getStr('passwordchanged_status');
                break;

            case InnomaticContainer::SETROOTPASSWORD_NEW_PASSWORD_IS_EMPTY :
                $gPageStatus = $gLocale->getStr('newpasswordisempty_status');
                break;

            case InnomaticContainer::SETROOTPASSWORD_UNABLE_TO_WRITE_NEW_PASSWORD :
                $gPageStatus = $gLocale->getStr('unabletowritenewpassword_status');
                break;

            case InnomaticContainer::SETROOTPASSWORD_OLD_PASSWORD_IS_WRONG :
                $gPageStatus = $gLocale->getStr('wrongoldpassword_status');
                break;
        }
    } else {
        $gPageStatus = $gLocale->getStr('newpasswordnomatch_status');
    }
}

$gActionDispatcher->Dispatch();

// ----- Main dispatcher -----
//
$gViewDispatcher = new WuiDispatcher('view');

function default_tab_builder($tab)
{
    return WuiEventsCall::buildEventsCallString('', array(array('view', 'default', array('tab' => $tab))));
}

$gViewDispatcher->addEvent('default', 'main_default');
function main_default($eventData)
{
    global $gXmlDefinition, $gLocale, $gPageTitle, $alertText;

    //$tabs[0]['label'] = $gLocale->getStr( 'currentactivities.tab' );
    $tabs[0]['label'] = $gLocale->getStr('accesslog.tab');
    $tabs[1]['label'] = $gLocale->getStr('loggedusers.tab');
    $tabs[2]['label'] = $gLocale->getStr('securitycheck.tab');

    $innomaticSecurity = new SecurityManager();
    $securityCheck = $innomaticSecurity->SecurityCheck();
    
    if ($securityCheck['rootpasswordcheck'] == false
    or $securityCheck['rootdapasswordcheck'] == false
    or count($securityCheck['domainswithunsecuredbpassword'])
    or count($securityCheck['unsecurelocalaccounts'])
    or count($securityCheck['unsecurewebservicesprofiles'])
    or count($securityCheck['unsecurewebservicesaccounts'])) {
        $alertText = $gLocale->getStr('security_check_failed_status');
    }

    $loggedUsers = $innomaticSecurity->getLoggedSessions();
    $rootSessions = $usersSessions = array();

    foreach ($loggedUsers['root'] as $rootSession) {
        $rootSessions[$rootSession] = $rootSession;
    }

    foreach ($loggedUsers['domains'] as $user => $sessions) {
        $usersSessions[$user] = $user;

        foreach ($sessions as $session) {
            //$usersSessions[$user.'-'.$session] = '- '.$session;
            $usersSessions[$session] = '- '.$session;
        }
    }

    $tmpKey = array_search('', $securityCheck['unsecurewebservicesaccounts']);
    if (strlen($tmpKey))
        $securityCheck['unsecurewebservicesaccounts'][$tmpKey] = 'Anonymous';

    $gXmlDefinition = '<tab><name>security</name>
      <args>
        <tabs type="array">'.WuiXml::encode($tabs).'</tabs>
        <tabactionfunction>default_tab_builder</tabactionfunction>
        <activetab>'. (isset($eventData['tab']) ? $eventData['tab'] : '').'</activetab>
      </args>
      <children>
    
        <vertgroup><name></name>
          <children>
    
            <table><name>accesslog</name>
              <args>
                <headers type="array">'
                .WuiXml::encode(array('0' => array('label' => $gLocale->getStr('accesslog.header'))))
                .'</headers>
              </args>
              <children>
    
                <text row="0" col="0"><name>accesslog</name>
                  <args>
                    <readonly>true</readonly>
                    <value type="encoded">'.urlencode($innomaticSecurity->getAccessLog()).'</value>
                    <cols>120</cols>
                    <rows>15</rows>
                  </args>
                </text>
                
                <button row="1" col="0"><name>erase</name>
                  <args>
                    <themeimage>trash</themeimage>
                    <label type="encoded">'.urlencode($gLocale->getStr('eraselog.button')).'</label>
                    <horiz>true</horiz>
                    <frame>false</frame>
                    <action type="encoded">'
                    .urlencode(
                        WuiEventsCall::buildEventsCallString(
                            '',
                            array(
                                array(
                                    'view',
                                    'default',
                                    ''
                                ),
                                array(
                                    'action',
                                    'clean_access_log',
                                    ''
                                )
                            )
                        )
                    ).'</action>
                  </args>
                </button>
    
              </children>
            </table>
    
          </children>
        </vertgroup>
    
        <vertgroup>
          <children>
    
            <label>
              <args>
                <label type="encoded">'.urlencode($gLocale->getStr('root_sessions.label')).'</label>
                <bold>true</bold>
              </args>
            </label>
    
            <form><name>rootsessions</name>
              <args>
                <action type="encoded">'
                .urlencode(
                    WuiEventsCall::buildEventsCallString(
                        '',
                        array(
                            array(
                                'view',
                                'default'
                            ),
                            array(
                                'action',
                                'logout_sessions'
                            )
                        )
                    )
                ).'</action>
              </args>
              <children>
            <listbox><name>sessions</name>
              <args>
                <size>5</size>
                <elements type="array">'.WuiXml::encode($rootSessions).'</elements>
                <multiselect>true</multiselect>
                <disp>action</disp>
              </args>
            </listbox>
              </children>
            </form>
    
            <button>
              <args>
                <horiz>true</horiz>
                <frame>false</frame>
                <label type="encoded">'.urlencode($gLocale->getStr('logout_sessions.button')).'</label>
                <themeimage>exit</themeimage>
                <formsubmit>rootsessions</formsubmit>
                <action type="encoded">'
                .urlencode(
                    WuiEventsCall::buildEventsCallString(
                        '',
                        array(
                            array(
                                'view',
                                'default'
                            ),
                            array(
                                'action',
                                'logout_sessions'
                            )
                        )
                    )
                ).'</action>
              </args>
            </button>';

    $gXmlDefinition.= '        <label>
              <args>
                <label type="encoded">'.urlencode($gLocale->getStr('users_sessions.label')).'</label>
                <bold>true</bold>
              </args>
            </label>
    
            <form><name>userssessions</name>
              <args>
                <action type="encoded">'
                .urlencode(
                    WuiEventsCall::buildEventsCallString(
                        '',
                        array(
                            array(
                                'view',
                                'default'
                            ),
                            array(
                                'action',
                                'logout_sessions'
                            )
                        )
                    )
                ).'</action>
              </args>
              <children>
            <listbox><name>sessions</name>
              <args>
                <size>15</size>
                <elements type="array">'.WuiXml::encode($usersSessions).'</elements>
                <multiselect>true</multiselect>
                <disp>action</disp>
              </args>
            </listbox>
              </children>
            </form>
    
            <button>
              <args>
                <horiz>true</horiz>
                <frame>false</frame>
                <label type="encoded">'.urlencode($gLocale->getStr('logout_sessions.button')).'</label>
                <themeimage>exit</themeimage>
                <formsubmit>userssessions</formsubmit>
                <action type="encoded">'
                .urlencode(
                    WuiEventsCall::buildEventsCallString(
                        '',
                        array(
                            array(
                                'view',
                                'default'
                            ),
                            array(
                                'action',
                                'logout_sessions'
                            )
                        )
                    )
                ).'</action>
              </args>
            </button>';

    $gXmlDefinition.= '      </children>
        </vertgroup>
    
        <vertgroup><name></name>
          <children>
    
            <label><name>tabtitle</name>
              <args>
                <label type="encoded">'.urlencode($gLocale->getStr('securitycheck.label')).'</label>
                <bold>true</bold>
              </args>
            </label>
    
                    <grid>
                      <children>
    
                        <button row="0" col="0"><name>check</name>
                          <args>
                            <themeimage>'. (
                                $securityCheck['rootpasswordcheck'] == false ? 'button_cancel' : 'button_ok'
                            ).'</themeimage>
                            <disabled>true</disabled>
                          </args>
                        </button>
    
                        <label row="0" col="1"><name>check</name>
                          <args>
                            <label type="encoded">'.urlencode($gLocale->getStr('root_password_check.label')).'</label>
                          </args>
                        </label>
    
                        <label row="0" col="2"><name>check</name>
                          <args>
                            <label type="encoded">'
                            .urlencode(
                                $gLocale->getStr(
                                    $securityCheck['rootpasswordcheck']
                                    ? 'check_password_ok.label' : 'check_password_unsafe.label'
                                )
                            ).'</label>
                          </args>
                        </label>
    
                        <button row="1" col="0"><name>check</name>
                          <args>
                            <themeimage>'. (
                                $securityCheck['rootdapasswordcheck'] == false ? 'button_cancel' : 'button_ok'
                            ).'</themeimage>
                            <disabled>true</disabled>
                          </args>
                        </button>
    
                        <label row="1" col="1"><name>check</name>
                          <args>
                            <label type="encoded">'.urlencode($gLocale->getStr('root_dbpassword_check.label')).'</label>
                          </args>
                        </label>
    
                        <label row="1" col="2"><name>check</name>
                          <args>
                            <label type="encoded">'
                            .urlencode(
                                $gLocale->getStr(
                                    $securityCheck['rootdapasswordcheck']
                                    ? 'check_password_ok.label' : 'check_password_unsafe.label'
                                )
                            ).'</label>
                          </args>
                        </label>
    
                        <button row="2" col="0" halign="" valign="top"><name>check</name>
                          <args>
                            <themeimage>'
                            . (
                               count($securityCheck['domainswithunsecuredbpassword']) ? 'button_cancel' : 'button_ok'
                            ).'</themeimage>
                            <disabled>true</disabled>
                          </args>
                        </button>
    
                        <label row="2" col="1" halign="" valign="top"><name>check</name>
                          <args>
                            <label type="encoded">'
                            .urlencode($gLocale->getStr('unsecure_domains_db_check.label'))
                            .'</label>
                          </args>
                        </label>
    
                        <listbox row="2" col="2"><name>check</name>
                          <args>
                            <readonly>true</readonly>
                            <elements type="array">'
                            .WuiXml::encode($securityCheck['domainswithunsecuredbpassword'])
                            .'</elements>
                            <size>5</size>
                          </args>
                        </listbox>
    
                        <button row="3" col="0" halign="" valign="top"><name>check</name>
                          <args>
                            <themeimage>'
                            . (
                               count($securityCheck['unsecurelocalaccounts']) ? 'button_cancel' : 'button_ok'
                            )
                            .'</themeimage>
                            <disabled>true</disabled>
                          </args>
                        </button>
    
                        <label row="3" col="1" halign="" valign="top"><name>check</name>
                          <args>
                            <label type="encoded">'.urlencode($gLocale->getStr('local_accounts_check.label')).'</label>
                          </args>
                        </label>
    
                        <listbox row="3" col="2"><name>check</name>
                          <args>
                            <readonly>true</readonly>
                            <elements type="array">'
                            .WuiXml::encode($securityCheck['unsecurelocalaccounts'])
                            .'</elements>
                            <size>5</size>
                          </args>
                        </listbox>
    
                        <button row="4" col="0" halign="" valign="top"><name>check</name>
                          <args>
                            <themeimage>'
                            . (
                               count($securityCheck['unsecurewebservicesprofiles']) ? 'button_cancel' : 'button_ok'
                            )
                            .'</themeimage>
                            <disabled>true</disabled>
                          </args>
                        </button>
    
                        <label row="4" col="1" halign="" valign="top"><name>check</name>
                          <args>
                            <label type="encoded">'
                            .urlencode($gLocale->getStr('webservices_profiles_check.label'))
                            .'</label>
                          </args>
                        </label>
    
                        <listbox row="4" col="2"><name>check</name>
                          <args>
                            <readonly>true</readonly>
                            <elements type="array">'
                            .WuiXml::encode($securityCheck['unsecurewebservicesprofiles'])
                            .'</elements>
                            <size>5</size>
                          </args>
                        </listbox>
    
                        <button row="5" col="0" halign="" valign="top"><name>check</name>
                          <args>
                            <themeimage>'
                            . (
                               count($securityCheck['unsecurewebservicesaccounts']) ? 'button_cancel' : 'button_ok'
                            )
                            .'</themeimage>
                            <disabled>true</disabled>
                          </args>
                        </button>
    
                        <label row="5" col="1" halign="" valign="top"><name>check</name>
                          <args>
                            <label type="encoded">'
                            .urlencode($gLocale->getStr('webservices_accounts_check.label'))
                            .'</label>
                          </args>
                        </label>
    
                        <listbox row="5" col="2"><name>check</name>
                          <args>
                            <readonly>true</readonly>
                            <elements type="array">'
                            .WuiXml::encode($securityCheck['unsecurewebservicesaccounts'])
                            .'</elements>
                            <size>5</size>
                          </args>
                        </listbox>
    
                      </children>
                    </grid>
    
          </children>
        </vertgroup>
    
      </children>
    </tab>';

    $gPageTitle.= ' - '.$gLocale->getStr('security_check.title');
}

function settings_tab_builder($tab)
{
    return WuiEventsCall::buildEventsCallString('', array(array('view', 'settings', array('tab' => $tab))));
}

$gViewDispatcher->addEvent('settings', 'main_settings');
function main_settings($eventData)
{
    global $gXmlDefinition, $gLocale, $gPageTitle;

    $innomaticSecurity = new SecurityManager();
    $sessionLifeTime = $innomaticSecurity->getSessionLifetime();
    $maxWrongLogins = $innomaticSecurity->getMaxWrongLogins();
    $wrongLoginDelay = $innomaticSecurity->getWrongLoginDelay();
    $lockUnsecureWebservices = $innomaticSecurity->getUnsecureWebServicesLock();
    $onlyHttpsRoot = $innomaticSecurity->getOnlyHttpsRootAccess();
    $onlyHttpsDomain = $innomaticSecurity->getOnlyHttpsDomainAccess();

    $alertsOn = $innomaticSecurity->getAlertEvents();

    $wrongLocalRootLogin = $alertsOn['wronglocalrootlogin'] ? 'true' : 'false';
    $wrongLocalUserLogin = $alertsOn['wronglocaluserlogin'] ? 'true' : 'false';
    $wrongWebservicesLogin = $alertsOn['wrongwebserviceslogin'] ? 'true' : 'false';
    $applicationOperation = $alertsOn['applicationoperation'] ? 'true' : 'false';
    $domainApplicationOperation = $alertsOn['applicationdomainoperation'] ? 'true' : 'false';
    $domainOperation = $alertsOn['domainoperation'] ? 'true' : 'false';

    $reportsInterval = $innomaticSecurity->getReportsInterval();
    $reportsEnabled = $reportsInterval ? 'true' : 'false';
    $reportDestinationEmail = $innomaticSecurity->getReportsEmail();

    $alertDestinationEmail = $innomaticSecurity->getAlertsEmail();

    $tabs[0]['label'] = $gLocale->getStr('security_presets.tab');
    $tabs[1]['label'] = $gLocale->getStr('access_settings.tab');
    $tabs[2]['label'] = $gLocale->getStr('alerts_settings.tab');
    $tabs[3]['label'] = $gLocale->getStr('reports_settings.tab');

    $gXmlDefinition = '<tab><name>security</name>
      <args>
        <tabs type="array">'.WuiXml::encode($tabs).'</tabs>
        <tabactionfunction>settings_tab_builder</tabactionfunction>
        <activetab>'. (isset($eventData['tab']) ? $eventData['tab'] : '').'</activetab>
      </args>
      <children>
    
        <vertgroup><name></name>
          <children>
    
            <table><name>presets</name>
              <args>
                <headers type="array">'
                .WuiXml::encode(
                    array(
                        0 => array(
                            'label' => $gLocale->getStr('security_presets.label')
                        )
                    )
                )
                .'</headers>
              </args>
              <children>
    
              <button row="0" col="0"><name>preset</name>
                <args>
                    <themeimage>decrypted</themeimage>
                    <label type="encoded">'.urlencode($gLocale->getStr('level_low.label')).'</label>
                    <horiz>true</horiz>
                    <frame>false</frame>
                    <action type="encoded">'
                    .urlencode(
                        WuiEventsCall::buildEventsCallString(
                            '',
                            array(
                                array(
                                    'view',
                                    'settings',
                                    ''
                                ),
                                array(
                                    'action',
                                    'set_security_preset',
                                    array(
                                        'preset' => SecurityManager::PRESET_LOW
                                    )
                                )
                            )
                        )
                    )
                    .'</action>
                </args>
              </button>
    
              <label row="0" col="1"><name>details</name>
                <args>
                  <label type="encoded">'.urlencode($gLocale->getStr('level_low.text')).'</label>
                  <nowrap>false</nowrap>
                </args>
              </label>
    
              <button row="1" col="0"><name>preset</name>
                <args>
                    <themeimage>encrypted</themeimage>
                    <label type="encoded">'.urlencode($gLocale->getStr('level_normal.label')).'</label>
                    <horiz>true</horiz>
                    <frame>false</frame>
                    <action type="encoded">'
                    .urlencode(
                        WuiEventsCall::buildEventsCallString(
                            '',
                            array(
                                array(
                                    'view',
                                    'settings',
                                    ''
                                ),
                                array(
                                    'action',
                                    'set_security_preset',
                                    array(
                                        'preset' => SecurityManager::PRESET_NORMAL
                                    )
                                )
                            )
                        )
                    )
                    .'</action>
                </args>
              </button>
    
              <label row="1" col="1"><name>details</name>
                <args>
                  <label type="encoded">'.urlencode($gLocale->getStr('level_normal.text')).'</label>
                  <nowrap>false</nowrap>
                </args>
              </label>
    
              <button row="2" col="0"><name>preset</name>
                <args>
                    <themeimage>encrypted</themeimage>
                    <label type="encoded">'.urlencode($gLocale->getStr('level_high.label')).'</label>
                    <horiz>true</horiz>
                    <frame>false</frame>
                    <action type="encoded">'
                    .urlencode(
                        WuiEventsCall::buildEventsCallString(
                            '',
                            array(
                                array(
                                    'view',
                                    'settings',
                                    ''
                                ),
                                array(
                                    'action',
                                    'set_security_preset',
                                    array(
                                        'preset' => SecurityManager::PRESET_HIGH
                                    )
                                )
                            )
                        )
                    )
                    .'</action>
                </args>
              </button>
    
              <label row="2" col="1"><name>details</name>
                <args>
                  <label type="encoded">'.urlencode($gLocale->getStr('level_high.text')).'</label>
                  <nowrap>false</nowrap>
                </args>
              </label>
    
              <button row="3" col="0"><name>preset</name>
                <args>
                    <themeimage>encrypted</themeimage>
                    <label type="encoded">'.urlencode($gLocale->getStr('level_paranoid.label')).'</label>
                    <horiz>true</horiz>
                    <frame>false</frame>
                    <action type="encoded">'
                    .urlencode(
                        WuiEventsCall::buildEventsCallString(
                            '',
                            array(
                                array(
                                    'view',
                                    'settings',
                                    ''
                                ),
                                array(
                                    'action',
                                    'set_security_preset',
                                    array(
                                        'preset' => SecurityManager::PRESET_PARANOID
                                    )
                                )
                            )
                        )
                    )
                    .'</action>
                </args>
              </button>
    
              <label row="3" col="1"><name>details</name>
                <args>
                  <label type="encoded">'.urlencode($gLocale->getStr('level_paranoid.text')).'</label>
                  <nowrap>false</nowrap>
                </args>
              </label>
    
              </children>
            </table>
    
          </children>
        </vertgroup>
    
        <vertgroup><name></name>
          <children>
    
            <table><name>access</name>
              <args>
                <headers type="array">'
                .WuiXml::encode(array(0 => array('label' => $gLocale->getStr('access_settings.label'))))
                .'</headers>
              </args>
              <children>
    
                <form row="0" col="0"><name>access</name>
                  <args>
                    <method>post</method>
                    <action type="encoded">'
                    .urlencode(
                        WuiEventsCall::buildEventsCallString(
                            '',
                            array(
                                array(
                                    'view',
                                    'settings',
                                    ''
                                ),
                                array(
                                    'action',
                                    'set_access_prefs',
                                    ''
                                )
                            )
                        )
                    )
                    .'</action>
                  </args>
                  <children>
                    <grid>
                      <children>
    
                        <label row="0" col="0"><name>sessionlifetime</name>
                          <args>
                            <label type="encoded">'.urlencode($gLocale->getStr('session_lifetime.label')).'</label>
                          </args>
                        </label>
    
                        <string row="0" col="1"><name>sessionlifetime</name>
                          <args>
                            <value>'.$sessionLifeTime.'</value>
                            <disp>action</disp>
                            <size>10</size>
                          </args>
                        </string>
    
                        <label row="1" col="0"><name>maxwronglogins</name>
                          <args>
                            <label type="encoded">'.urlencode($gLocale->getStr('max_wrong_logins.label')).'</label>
                          </args>
                        </label>
    
                        <string row="1" col="1"><name>maxwronglogins</name>
                          <args>
                            <value>'.$maxWrongLogins.'</value>
                            <disp>action</disp>
                            <size>4</size>
                          </args>
                        </string>
    
                        <label row="2" col="0"><name>wronglogindelay</name>
                          <args>
                            <label type="encoded">'.urlencode($gLocale->getStr('wrong_login_delay.label')).'</label>
                          </args>
                        </label>
    
                        <string row="2" col="1"><name>wronglogindelay</name>
                          <args>
                            <value>'.$wrongLoginDelay.'</value>
                            <disp>action</disp>
                            <size>3</size>
                          </args>
                        </string>
    
                        <label row="3" col="0"><name>lockunsecurewebservices</name>
                          <args>
                            <label type="encoded">'
                            .urlencode($gLocale->getStr('block_unsecure_webservices.label'))
                            .'</label>
                          </args>
                        </label>
    
                        <checkbox row="3" col="1"><name>lockunsecurewebservices</name>
                          <args>
                            <checked>'. ($lockUnsecureWebservices ? 'true' : 'false').'</checked>
                            <disp>action</disp>
                          </args>
                        </checkbox>
    
                        <label row="4" col="0"><name>onlyhttpsroot</name>
                          <args>
                            <label type="encoded">'.urlencode($gLocale->getStr('only_https_root.label')).'</label>
                          </args>
                        </label>
    
                        <checkbox row="4" col="1"><name>onlyhttpsroot</name>
                          <args>
                            <checked>'. ($onlyHttpsRoot ? 'true' : 'false').'</checked>
                            <disp>action</disp>
                          </args>
                        </checkbox>
    
                        <label row="5" col="0"><name>onlyhttpsdomain</name>
                          <args>
                            <label type="encoded">'.urlencode($gLocale->getStr('only_https_domain.label')).'</label>
                          </args>
                        </label>
    
                        <checkbox row="5" col="1"><name>onlyhttpsdomain</name>
                          <args>
                            <checked>'. ($onlyHttpsDomain ? 'true' : 'false').'</checked>
                            <disp>action</disp>
                          </args>
                        </checkbox>
    
                      </children>
                    </grid>
                  </children>
                </form>
    
                <button row="1" col="0"><name>apply</name>
                  <args>
                    <horiz>true</horiz>
                    <frame>false</frame>
                    <label type="encoded">'.urlencode($gLocale->getStr('apply.submit')).'</label>
                    <themeimage>button_ok</themeimage>
                    <formsubmit>access</formsubmit>
                    <action type="encoded">'
                    .urlencode(
                        WuiEventsCall::buildEventsCallString(
                            '',
                            array(
                                array(
                                    'view',
                                    'settings',
                                    ''
                                ),
                                array(
                                    'action',
                                    'set_access_prefs',
                                    ''
                                )
                            )
                        )
                    )
                    .'</action>
                  </args>
                </button>
              </children>
            </table>
    
          </children>
        </vertgroup>
    
        <vertgroup><name></name>
          <children>
    
            <table><name>alerts</name>
              <args>
                <headers type="array">'
                .WuiXml::encode(array(0 => array('label' => $gLocale->getStr('alerts_settings.label'))))
                .'</headers>
              </args>
              <children>
    
                <form row="0" col="0"><name>alerts</name>
                  <args>
                    <method>post</method>
                    <action type="encoded">'
                    .urlencode(
                        WuiEventsCall::buildEventsCallString(
                            '',
                            array(
                                array(
                                    'view',
                                    'settings',
                                    ''
                                ),
                                array(
                                    'action',
                                    'set_alerts_prefs',
                                    ''
                                )
                            )
                        )
                    )
                    .'</action>
                  </args>
                  <children>
                    <grid>
                      <children>
    
                        <label row="0" col="0"><name>alertonevents</name>
                          <args>
                            <bold>true</bold>
                            <label type="encoded">'.urlencode($gLocale->getStr('alert_on_events.label')).'</label>
                          </args>
                        </label>
    
                        <label row="1" col="0"><name>wronglocalrootlogin</name>
                          <args>
                            <label type="encoded">'
                            .urlencode($gLocale->getStr('wrong_local_root_login.label'))
                            .'</label>
                          </args>
                        </label>
    
                        <checkbox row="1" col="1"><name>wronglocalrootlogin</name>
                          <args>
                            <checked>'.$wrongLocalRootLogin.'</checked>
                            <disp>action</disp>
                          </args>
                        </checkbox>
    
                        <label row="2" col="0"><name>wronglocaluserlogin</name>
                          <args>
                            <label type="encoded">'
                            .urlencode($gLocale->getStr('wrong_local_user_login.label'))
                            .'</label>
                          </args>
                        </label>
    
                        <checkbox row="2" col="1"><name>wronglocaluserlogin</name>
                          <args>
                            <checked>'.$wrongLocalUserLogin.'</checked>
                            <disp>action</disp>
                          </args>
                        </checkbox>
    
                        <label row="3" col="0"><name>wrongwebserviceslogin</name>
                          <args>
                            <label type="encoded">'
                            .urlencode($gLocale->getStr('wrong_webservices_login.label'))
                            .'</label>
                          </args>
                        </label>
    
                        <checkbox row="3" col="1"><name>wrongwebserviceslogin</name>
                          <args>
                            <checked>'.$wrongWebservicesLogin.'</checked>
                            <disp>action</disp>
                          </args>
                        </checkbox>
    
                        <label row="4" col="0"><name>applicationoperation</name>
                          <args>
                            <label type="encoded">'.urlencode($gLocale->getStr('application_operation.label')).'</label>
                          </args>
                        </label>
    
                        <checkbox row="4" col="1"><name>applicationoperation</name>
                          <args>
                            <checked>'.$applicationOperation.'</checked>
                            <disp>action</disp>
                          </args>
                        </checkbox>
    
                        <label row="5" col="0"><name>domainapplicationoperation</name>
                          <args>
                            <label type="encoded">'
                            .urlencode($gLocale->getStr('domainapplication_operation.label'))
                            .'</label>
                          </args>
                        </label>
    
                        <checkbox row="5" col="1"><name>domainapplicationoperation</name>
                          <args>
                            <checked>'.$domainApplicationOperation.'</checked>
                            <disp>action</disp>
                          </args>
                        </checkbox>
    
                        <label row="6" col="0"><name>domainoperation</name>
                          <args>
                            <label type="encoded">'.urlencode($gLocale->getStr('domain_operation.label')).'</label>
                          </args>
                        </label>
    
                        <checkbox row="6" col="1"><name>domainoperation</name>
                          <args>
                            <checked>'.$domainOperation.'</checked>
                            <disp>action</disp>
                          </args>
                        </checkbox>
    
                        <label row="7" col="0"><name>alertdestinationemail</name>
                          <args>
                            <label type="encoded">'
                            .urlencode($gLocale->getStr('alert_destination_email.label'))
                            .'</label>
                          </args>
                        </label>
    
                        <string row="7" col="1"><name>alertdestinationemail</name>
                          <args>
                            <value type="encoded">'.urlencode($alertDestinationEmail).'</value>
                            <disp>action</disp>
                            <size>25</size>
                          </args>
                        </string>
    
                      </children>
                    </grid>
                  </children>
                </form>
    
                <button row="1" col="0"><name>apply</name>
                  <args>
                    <horiz>true</horiz>
                    <frame>false</frame>
                    <label type="encoded">'.urlencode($gLocale->getStr('apply.submit')).'</label>
                    <themeimage>button_ok</themeimage>
                    <formsubmit>alerts</formsubmit>
                    <action type="encoded">'
                    .urlencode(
                        WuiEventsCall::buildEventsCallString(
                            '',
                            array(
                                array(
                                    'view',
                                    'settings',
                                    ''
                                ),
                                array(
                                    'action',
                                    'set_alerts_prefs',
                                    ''
                                )
                            )
                        )
                    ).'</action>
                  </args>
                </button>
              </children>
            </table>
    
          </children>
        </vertgroup>
    
        <vertgroup><name></name>
          <children>
    
            <table><name>alerts</name>
              <args>
                <headers type="array">'
                .WuiXml::encode(array(0 => array('label' => $gLocale->getStr('reports_settings.label'))))
                .'</headers>
              </args>
              <children>
    
                <form row="0" col="0"><name>alerts</name>
                  <args>
                    <method>post</method>
                    <action type="encoded">'
                    .urlencode(
                        WuiEventsCall::buildEventsCallString(
                            '',
                            array(
                                array(
                                    'view',
                                    'settings',
                                    ''
                                ),
                                array(
                                    'action',
                                    'set_reports_prefs',
                                    ''
                                )
                            )
                        )
                    )
                    .'</action>
                  </args>
                  <children>
                    <grid>
                      <children>
    
                        <label row="0" col="0"><name>enablereports</name>
                          <args>
                            <label type="encoded">'.urlencode($gLocale->getStr('enable_reports.label')).'</label>
                          </args>
                        </label>
    
                        <checkbox row="0" col="1"><name>enablereports</name>
                          <args>
                            <checked>'.$reportsEnabled.'</checked>
                            <disp>action</disp>
                          </args>
                        </checkbox>
    
                        <label row="1" col="0"><name>reportsinterval</name>
                          <args>
                            <label type="encoded">'.urlencode($gLocale->getStr('reports_interval.label')).'</label>
                          </args>
                        </label>
    
                        <string row="1" col="1"><name>reportsinterval</name>
                          <args>
                            <value>'.$reportsInterval.'</value>
                            <disp>action</disp>
                            <size>3</size>
                          </args>
                        </string>
    
                        <label row="2" col="0"><name>reportdestinationemail</name>
                          <args>
                            <label type="encoded">'
                            .urlencode($gLocale->getStr('report_destination_email.label'))
                            .'</label>
                          </args>
                        </label>
    
                        <string row="2" col="1"><name>reportdestinationemail</name>
                          <args>
                            <value type="encoded">'.urlencode($reportDestinationEmail).'</value>
                            <disp>action</disp>
                            <size>25</size>
                          </args>
                        </string>
    
                      </children>
                    </grid>
                  </children>
                </form>
    
                <button row="1" col="0"><name>apply</name>
                  <args>
                    <horiz>true</horiz>
                    <frame>false</frame>
                    <label type="encoded">'.urlencode($gLocale->getStr('apply.submit')).'</label>
                    <themeimage>button_ok</themeimage>
                    <formsubmit>alerts</formsubmit>
                    <action type="encoded">'
                    .urlencode(
                        WuiEventsCall::buildEventsCallString(
                            '',
                            array(
                                array(
                                    'view',
                                    'settings',
                                    ''
                                ),
                                array(
                                    'action',
                                    'set_reports_prefs',
                                    ''
                                )
                            )
                        )
                    ).'</action>
                  </args>
                </button>
              </children>
            </table>
    
          </children>
        </vertgroup>
    
      </children>
    </tab>';

    $gPageTitle .= ' - '.$gLocale->getStr('settings.title');
}

$gViewDispatcher->addEvent('change_password', 'main_change_password');
function main_change_password($eventData)
{
    global $gXmlDefinition, $gLocale, $gPageTitle;
    global $wuiMainFrame, $wuiTitleBar;

    $gXmlDefinition = '            <table><name>alerts</name>
              <args>
                <headers type="array">'
                .WuiXml::encode(array(0 => array('label' => $gLocale->getStr('password_title'))))
                .'</headers>
              </args>
              <children>
    <form row="0" col="0">
    <name>password</name>
                  <args>
                    <method>post</method>
                    <action type="encoded">'
                    .urlencode(
                        WuiEventsCall::buildEventsCallString(
                            '',
                            array(
                                array(
                                    'view',
                                    'change_password',
                                    ''
                                ),
                                array(
                                    'action',
                                    'change_password',
                                    ''
                                )
                            )
                        )
                    )
                    .'</action>
                  </args>
    <children>
    <grid>
        <name></name>
        <args>
            <rows>3</rows>
            <cols>2</cols>
        </args>
        <children>
            <label row="0" col="0">
                  <name />
                  <args>
                      <label type="encoded">'.urlencode($gLocale->getStr('rootpasswordold_label')).'</label>
                  </args>
            </label>
            <string row="0" col="1">
                <name>oldpassword</name>
                <args>
                    <disp>action</disp>
                    <password>true</password>
                </args>
            </string>
            <label row="1" col="0">
                  <name />
                  <args>
                      <label type="encoded">'.urlencode($gLocale->getStr('rootpasswordold_label')).'</label>
                  </args>
            </label>
            <string row="1" col="1">
                <name>newpassworda</name>
                <args>
                    <disp>action</disp>
                    <password>true</password>
                </args>
            </string>
            <label row="2" col="0">
                  <name />
                  <args>
                      <label type="encoded">'.urlencode($gLocale->getStr('rootpasswordold_label')).'</label>
                  </args>
            </label>
            <string row="2" col="1">
                <name>newpasswordb</name>
                <args>
                    <disp>action</disp>
                    <password>true</password>
                </args>
            </string>
            </children>
    </grid>
    </children>
    </form>
                    <button row="1" col="0"><name>apply</name>
                  <args>
                    <horiz>true</horiz>
                    <frame>false</frame>
                    <label type="encoded">'.urlencode($gLocale->getStr('apply.submit')).'</label>
                    <themeimage>button_ok</themeimage>
                    <formsubmit>password</formsubmit>
                    <action type="encoded">'
                    .urlencode(
                        WuiEventsCall::buildEventsCallString(
                            '',
                            array(
                                array(
                                    'view',
                                    'change_password',
                                    ''
                                ),
                                array(
                                    'action',
                                    'change_password',
                                    ''
                                )
                            )
                        )
                    )
                    .'</action>
                  </args>
                </button>
    </children>
    </table>';
    
    $gPageTitle .= ' - '.$gLocale->getStr('password_title');
}

$gViewDispatcher->Dispatch();

// ----- Rendering -----
//
$gWui->addChild(
    new WuiInnomaticPage(
        'page',
        array(
            'pagetitle' => $gPageTitle,
            'menu' => InnomaticContainer::getRootWuiMenuDefinition(
                InnomaticContainer::instance('innomaticcontainer')->getLanguage()
            ),
            'alerttext' => $alertText,
            'toolbars' => array(
                new WuiInnomaticToolbar(
                    'main',
                    array('toolbars' => $gToolbars, 'toolbar' => 'true')
                )
            ),
            'maincontent' => new WuiXml(
                'page',
                array('definition' => $gXmlDefinition)
            ),
            'status' => $gPageStatus,
            'icon' => 'important'
        )
    )
);

$gWui->render();
