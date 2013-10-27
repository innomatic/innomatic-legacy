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
require_once('innomatic/locale/LocaleCountry.php');
require_once('innomatic/wui/Wui.php');
require_once('innomatic/wui/dispatch/WuiEventsCall.php');

require_once('innomatic/maintenance/MaintenanceHandler.php');

    global $gPageStatus, $gLocale;
    global $gXmlDefinition, $gLocale, $gPageTitle, $gToolbars;

$gLocale = new LocaleCatalog(
    'innomatic::root_maintenance',
    InnomaticContainer::instance('innomaticcontainer')->getLanguage()
);

$gWui = Wui::instance('wui');
$gWui->loadWidget('xml');
$gWui->loadWidget('innomaticpage');
$gWui->loadWidget('innomatictoolbar');

$gXmlDefinition = $gPageStatus = '';
$gPageTitle = $gLocale->getStr('maintenance.title');

$gToolbars['view'] = array(
    'innomatic' => array(
        'label' => $gLocale->getStr('general.toolbar'),
        'themeimage' => 'gear',
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
    'general' => array(
        'label' => $gLocale->getStr('innomatic.toolbar'),
        'themeimage' => 'gear',
        'horiz' => 'true',
        'action' => WuiEventsCall::buildEventsCallString(
            '',
            array(
                array(
                    'view',
                    'innomatic',
                    ''
                )
            )
        )
    )
);

// Info tool bar
//
if (file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/log/innomatic.log')) {
    $innomaticLogAction = new WuiEventsCall();
    $innomaticLogAction->addEvent(new WuiEvent('view', 'showrootlog', ''));
    $gToolbars['info']['rootlog'] = array(
        'label' => $gLocale->getStr('rootlog_button'),
        'themeimage' => 'alignjustify',
        'horiz' => 'true',
        'action' => $innomaticLogAction->getEventsCallString()
    );
}

if (file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/log/webservices.log')) {
    $innomaticWebServicesLogAction = new WuiEventsCall();
    $innomaticWebServicesLogAction->addEvent(new WuiEvent('view', 'showrootwebserviceslog', ''));
    $gToolbars['info']['webserviceslog'] = array(
        'label' => $gLocale->getStr('rootwebserviceslog_button'),
        'themeimage' => 'alignjustify',
        'horiz' => 'true',
        'action' => $innomaticWebServicesLogAction->getEventsCallString()
    );
}


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

$gActionDispatcher->addEvent('clear_systemlogs', 'action_clear_systemlogs');
function action_clear_systemlogs($eventData)
{
    global $gPageStatus, $gLocale;

    require_once('shared/maintenance/InnomaticLogsMaintenance.php');
    $maint = new InnomaticLogsMaintenance();
    $maint->mCleanRootLog = true;
    $maint->mCleanRootDbLog = true;
    $maint->mCleanPhpLog = true;
    $maint->mCleanWebServicesLog = true;
    $maint->mCleanAccessLog = true;
    $maint->CleanSystemLogs();

    $gPageStatus = $gLocale->getStr('systemlogs_cleaned.status');
}

$gActionDispatcher->addEvent('clear_domainslogs', 'action_clear_domainslogs');
function action_clear_domainslogs($eventData)
{
    global $gPageStatus, $gLocale;

    require_once('shared/maintenance/InnomaticLogsMaintenance.php');
    $maint = new InnomaticLogsMaintenance();

    $maint->CleanDomainsLogs();

    $gPageStatus = $gLocale->getStr('domainslogs_cleaned.status');
}

$gActionDispatcher->addEvent('clear_cache', 'action_clear_cache');
function action_clear_cache($eventData)
{
    global $gPageStatus, $gLocale;

    require_once('shared/maintenance/InnomaticCacheMaintenance.php');
    $maint = new InnomaticCacheMaintenance();
    $maint->CleanCache();

    $gPageStatus = $gLocale->getStr('cache_cleaned.status');
}

$gActionDispatcher->addEvent('clear_pidfiles', 'action_clear_pidfiles');
function action_clear_pidfiles($eventData)
{
    global $gPageStatus, $gLocale;

    require_once('shared/maintenance/InnomaticCacheMaintenance.php');
    $maint = new InnomaticCacheMaintenance();
    $maint->CleanPidFiles();

    $gPageStatus = $gLocale->getStr('pidfiles_cleaned.status');
}

$gActionDispatcher->addEvent('clear_sessions', 'action_clear_sessions');
function action_clear_sessions($eventData)
{
    global $gPageStatus, $gLocale;

    require_once('shared/maintenance/InnomaticCacheMaintenance.php');
    $maint = new InnomaticCacheMaintenance();
    $maint->CleanSessions();

    $gPageStatus = $gLocale->getStr('sessions_cleaned.status');
}

$gActionDispatcher->addEvent('clear_tempdirs', 'action_clear_tempdirs');
function action_clear_tempdirs($eventData)
{
    global $gPageStatus, $gLocale;

    require_once('shared/maintenance/InnomaticCacheMaintenance.php');
    $maint = new InnomaticCacheMaintenance();
    $maint->CleanRootTempDirs();

    $gPageStatus = $gLocale->getStr('tempdirs_cleaned.status');
}

$gActionDispatcher->addEvent('clear_clipboard', 'action_clear_clipboard');
function action_clear_clipboard($eventData)
{
    global $gPageStatus, $gLocale;

    require_once('shared/maintenance/InnomaticCacheMaintenance.php');
    $maint = new InnomaticCacheMaintenance();
    $maint->CleanClipboard();

    $gPageStatus = $gLocale->getStr('clipboard_cleaned.status');
}

$gActionDispatcher->addEvent('clear_all', 'action_clear_all');
function action_clear_all($eventData)
{
    global $gPageStatus, $gLocale;

    require_once('shared/maintenance/InnomaticCacheMaintenance.php');
    $maint = new InnomaticCacheMaintenance();
    $maint->CleanCache();
    $maint->CleanSessions();
    $maint->CleanPidFiles();
    $maint->CleanRootTempDirs();
    $maint->CleanClipboard();

    require_once('shared/maintenance/InnomaticLogsMaintenance.php');
    $maint = new InnomaticLogsMaintenance();
    $maint->mCleanRootLog = true;
    $maint->mCleanRootDbLog = true;
    $maint->mCleanPhpLog = true;
    $maint->mCleanWebServicesLog = true;
    $maint->mCleanAccessLog = true;
    $maint->CleanSystemLogs();
    $maint->CleanDomainsLogs();

    $gPageStatus = $gLocale->getStr('all_cleaned.status');
}

$gActionDispatcher->addEvent('set_innomatic', 'action_set_innomatic');
function action_set_innomatic($eventData)
{
    global $gPageStatus, $gLocale;

    require_once('shared/maintenance/InnomaticCacheMaintenance.php');
    $maint = new InnomaticCacheMaintenance();
    $maint->setCleanCache(
        isset($eventData['cache']) and $eventData['cache'] == 'on' ? true : false
    );
    $maint->setCleanSessions(
        isset($eventData['sessions']) and $eventData['sessions'] == 'on' ? true : false
    );
    $maint->setCleanPidFiles(
        isset($eventData['pidfiles']) and $eventData['pidfiles'] == 'on' ? true : false
    );
    $maint->setCleanRootTempDirs(
        isset($eventData['roottempdirs']) and $eventData['roottempdirs'] == 'on' ? true : false
    );
    $maint->setCleanClipboard(
        isset($eventData['clipboard']) and $eventData['clipboard'] == 'on' ? true : false
    );

    require_once('shared/maintenance/InnomaticLogsMaintenance.php');
    $maint = new InnomaticLogsMaintenance();

    switch ($eventData['rootlog']) {
        case 'clean' :
            $maint->setCleanRootLog(true);
            $maint->setRotateRootLog(false);
            break;
        case 'rotate' :
            $maint->setCleanRootLog(false);
            $maint->setRotateRootLog(true);
            break;
        case 'leave' :
            $maint->setCleanRootLog(false);
            $maint->setRotateRootLog(false);
            break;
    }

    switch ($eventData['rootdalog']) {
        case 'clean' :
            $maint->setCleanRootdbLog(true);
            $maint->setRotateRootdbLog(false);
            break;
        case 'rotate' :
            $maint->setCleanRootdbLog(false);
            $maint->setRotateRootdbLog(true);
            break;
        case 'leave' :
            $maint->setCleanRootdbLog(false);
            $maint->setRotateRootdbLog(false);
            break;
    }

    switch ($eventData['phplog']) {
        case 'clean' :
            $maint->setCleanphpLog(true);
            $maint->setRotatephpLog(false);
            break;
        case 'rotate' :
            $maint->setCleanphpLog(false);
            $maint->setRotatephpLog(true);
            break;
        case 'leave' :
            $maint->setCleanphpLog(false);
            $maint->setRotatephpLog(false);
            break;
    }

    switch ($eventData['webserviceslog']) {
        case 'clean' :
            $maint->setCleanwebserviceslog(true);
            $maint->setRotatewebserviceslog(false);
            break;
        case 'rotate' :
            $maint->setCleanwebserviceslog(false);
            $maint->setRotatewebserviceslog(true);
            break;
        case 'leave' :
            $maint->setCleanwebserviceslog(false);
            $maint->setRotatewebserviceslog(false);
            break;
    }

    switch ($eventData['accesslog']) {
        case 'clean' :
            $maint->setCleanaccesslog(true);
            $maint->setRotateaccesslog(false);
            break;
        case 'rotate' :
            $maint->setCleanaccesslog(false);
            $maint->setRotateaccesslog(true);
            break;
        case 'leave' :
            $maint->setCleanaccesslog(false);
            $maint->setRotateaccesslog(false);
            break;
    }

    switch ($eventData['domainslogs']) {
        case 'clean' :
            $maint->setCleandomainslogs(true);
            $maint->setRotatedomainslogs(false);
            break;
        case 'rotate' :
            $maint->setCleandomainslogs(false);
            $maint->setRotatedomainslogs(true);
            break;
        case 'leave' :
            $maint->setCleandomainslogs(false);
            $maint->setRotatedomainslogs(false);
            break;
    }

    $gPageStatus = $gLocale->getStr('settings_set.status');
}

$gActionDispatcher->addEvent('set_report', 'action_set_report');
function action_set_report($eventData)
{
    global $gPageStatus, $gLocale;

    $main = new MaintenanceHandler();

    if (isset($eventData['reportenabled']) and $eventData['reportenabled'] == 'on') {
        $main->EnableReports();
    } else {
        $main->DisableReports();
    }

    $main->setReportsEmail($eventData['reportemail']);

    $gPageStatus = $gLocale->getStr('settings_set.status');
}

$gActionDispatcher->addEvent('set_general', 'action_set_general');
function action_set_general($eventData)
{
    global $gPageStatus, $gLocale;

    $main = new MaintenanceHandler();
    $tasks = $main->getTasksList();

    foreach ($tasks as $task) {
        if (isset($eventData[$task['name'].'_task']) and $eventData[$task['name'].'_task'] == 'on') {
            $main->EnableTask($task['name']);
        } else {
            $main->DisableTask($task['name']);
        }
    }

    $gPageStatus = $gLocale->getStr('settings_set.status');
}

$gActionDispatcher->addEvent('run_maintenance', 'action_run_maintenance');
function action_run_maintenance($eventData)
{
    global $gPageStatus, $gLocale;

    $innomatic = InnomaticContainer::instance('innomaticcontainer');
    $innomatic->startMaintenance();
    $innomatic->setInterface(InnomaticContainer::INTERFACE_WEB);

    $gPageStatus = $gLocale->getStr('maintenance_done.status');
}

$gActionDispatcher->addEvent('cleanrootlog', 'action_cleanrootlog');
function action_cleanrootlog($eventData)
{
    global $gLocale, $gPageStatus;

    $tempLog = InnomaticContainer::instance('innomaticcontainer')->getLogger();

    if ($tempLog->cleanLog()) {
        $gPageStatus = $gLocale->getStr('logcleaned_status');
    } else {
        $gPageStatus = $gLocale->getStr('lognotcleaned_status');
    }
}

$gActionDispatcher->addEvent('cleanrootwebserviceslog', 'action_cleanrootwebserviceslog');
function action_cleanrootwebserviceslog($eventData)
{
    global $gPageStatus, $gLocale;

    $tempLog = new Logger(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/log/webservices.log');

    if ($tempLog->cleanLog()) {
        $gPageStatus = $gLocale->getStr('logcleaned_status');
    } else {
        $gPageStatus = $gLocale->getStr('lognotcleaned_status');
    }
}

$gActionDispatcher->Dispatch();

// ----- Main dispatcher -----
//
$gViewDispatcher = new WuiDispatcher('view');

function general_tab_builder($tab)
{
    return WuiEventsCall::buildEventsCallString('', array(array('view', 'default', array('tab' => $tab))));
}

$gViewDispatcher->addEvent('default', 'main_default');
function main_default($eventData)
{
    global $gXmlDefinition, $gLocale, $gPageTitle;

    $main = new MaintenanceHandler();
    $mainTime = $main->getLastMaintenanceTime();
    $tasks = $main->getTasksList();

    $tabs[0]['label'] = $gLocale->getStr('general_status.tab');
    $tabs[1]['label'] = $gLocale->getStr('general_report.tab');
    $tabs[2]['label'] = $gLocale->getStr('general_tasks.tab');

    $country = new LocaleCountry(InnomaticContainer::instance('innomaticcontainer')->getCountry());

    $dateArray = $country->getDateArrayFromUnixTimestamp($mainTime);

    $row = 0;

    $gXmlDefinition = '<vertgroup>
      <children>
    
      <tab><name>general</name>
        <args>
          <tabs type="array">'.WuiXml::encode($tabs).'</tabs>
          <tabactionfunction>general_tab_builder</tabactionfunction>
          <activetab>'. (isset($eventData['tab']) ? $eventData['tab'] : '').'</activetab>
        </args>
        <children>
    
        <vertgroup>
          <children>
    
            <label><name>status</name>
              <args>
                <label type="encoded">'.urlencode($gLocale->getStr('status.label')).'</label>
                <bold>true</bold>
              </args>
            </label>
    
        <horizgroup>
          <children>
    
            <label>
              <args>
                <label type="encoded">'.urlencode($gLocale->getStr('last_maintenance.label')).'</label>
              </args>
            </label>
    
            <date>
              <args>
                <readonly>true</readonly>
                <type>date</type>
                <value type="array">'.WuiXml::encode($dateArray).'</value>
              </args>
            </date>
    
            <date>
              <args>
                <readonly>true</readonly>
                <type>time</type>
                <value type="array">'.WuiXml::encode($dateArray).'</value>
              </args>
            </date>
    
          </children>
        </horizgroup>
    
            <horizbar/>';

    $maintenanceResult = &InnomaticContainer::instance('innomaticcontainer')->getMaintenanceResult();
    if (is_array($maintenanceResult)) {
        $row = 0;

        $gXmlDefinition.= '
                <label>
                  <args>
                    <label type="encoded">'.urlencode($gLocale->getStr('report.label')).'</label>
                    <bold>true</bold>
                  </args>
                </label>
        <grid><children>';

        foreach ($maintenanceResult as $task => $result) {
            $gXmlDefinition.= '<label row="'.$row.'" col="0">
              <args>
                <nowrap>true</nowrap>
                <label type="encoded">'.urlencode($tasks[$task]['description']).'</label>
              </args>
            </label>
            <button row="'.$row.'" col="1">
              <args>
                <themeimage>'. ($result ? 'buttonok' : 'buttoncancel').'</themeimage>
                <disabled>true</disabled>
              </args>
            </button>';
            $row ++;
        }

        $gXmlDefinition.= '</children></grid><horizbar/>';
    }

    $gXmlDefinition.= '        <button>
              <args>
                <themeimage>buttonok</themeimage>
                <label type="encoded">'.urlencode($gLocale->getStr('run_maintenance.button')).'</label>
                <horiz>true</horiz>
                <frame>false</frame>
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
                                'run_maintenance'
                            )
                        )
                    )
                ).'</action>
              </args>
            </button>
    
          </children>
        </vertgroup>
    
        <vertgroup>
          <children>
    
            <label>
              <args>
                <label type="encoded">'.urlencode($gLocale->getStr('report.label')).'</label>
                <bold>true</bold>
              </args>
            </label>
    
            <form><name>report</name>
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
                                'set_report'
                            )
                        )
                    )
                ).'</action>
              </args>
              <children>
    
            <grid>
              <children>
    
                <label row="0" col="0">
                  <args>
                    <label type="encoded">'.urlencode($gLocale->getStr('report_enabled.label')).'</label>
                  </args>
                </label>
    
                <checkbox row="0" col="1"><name>reportenabled</name>
                  <args>
                    <disp>action</disp>
                    <checked>'. ($main->getReportsEnableStatus() ? 'true' : 'false').'</checked>
                  </args>
                </checkbox>
    
                <label row="1" col="0">
                  <args>
                    <label type="encoded">'.urlencode($gLocale->getStr('report_email.label')).'</label>
                  </args>
                </label>
    
                <string row="1" col="1"><name>reportemail</name>
                  <args>
                    <disp>action</disp>
                    <value type="encoded">'.urlencode($main->getReportsEmail()).'</value>
                    <size>25</size>
                  </args>
                </string>
    
              </children>
            </grid>
    
            </children>
            </form>
    
            <horizbar/>
    
            <button>
              <args>
                <themeimage>buttonok</themeimage>
                <label type="encoded">'.urlencode($gLocale->getStr('apply.button')).'</label>
                <horiz>true</horiz>
                <frame>false</frame>
                <formsubmit>report</formsubmit>
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
                                'set_report'
                            )
                        )
                    )
                ).'</action>
              </args>
            </button>
    
          </children>
        </vertgroup>
    
        <vertgroup>
          <children>
    
            <label>
              <args>
                <label type="encoded">'.urlencode($gLocale->getStr('scheduled_tasks.label')).'</label>
                <bold>true</bold>
              </args>
            </label>
    
            <form><name>settings</name>
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
                                'set_general'
                            )
                        )
                    )
                ).'</action>
              </args>
              <children>
    
                <grid>
                  <children>';

    reset($tasks);

    foreach ($tasks as $task) {
        $gXmlDefinition.= '<checkbox row="'.$row.'" col="0"><name type="encoded">'
        .urlencode($task['name'].'_task').'</name>
          <args>
            <disp>action</disp>
            <checked>'. ($task['enabled'] ? 'true' : 'false').'</checked>
          </args>
        </checkbox>
        <label row="'.$row.'" col="1">
          <args>
            <label type="encoded">'.urlencode($task['description']).'</label>
            <nowrap>false</nowrap>
          </args>
        </label>';

        $row ++;
    }

    $gXmlDefinition.= '              </children>
                </grid>
    
              </children>
            </form>
    
        <horizbar/>
    
            <button>
              <args>
                <themeimage>buttonok</themeimage>
                <label type="encoded">'.urlencode($gLocale->getStr('apply.button')).'</label>
                <horiz>true</horiz>
                <frame>false</frame>
                <formsubmit>settings</formsubmit>
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
                                'set_general'
                            )
                        )
                    )
                ).'</action>
              </args>
            </button>
    
          </children>
          </vertgroup>
    
          </children>
        </tab>
    
      </children>
    </vertgroup>';

    $gPageTitle.= ' - '.$gLocale->getStr('general.title');
}

function innomatic_tab_builder($tab)
{
    return WuiEventsCall::buildEventsCallString('', array(array('view', 'innomatic', array('tab' => $tab))));
}

$gViewDispatcher->addEvent('innomatic', 'main_innomatic');
function main_innomatic($eventData)
{
    global $gXmlDefinition, $gLocale, $gPageTitle;

    require_once('shared/maintenance/InnomaticCacheMaintenance.php');
    require_once('shared/maintenance/InnomaticLogsMaintenance.php');

    $country = new LocaleCountry(InnomaticContainer::instance('innomaticcontainer')->getCountry());

    $tabs[0]['label'] = $gLocale->getStr('innomatic_status.tab');
    $tabs[1]['label'] = $gLocale->getStr('innomatic_requirements.tab');
    $tabs[2]['label'] = $gLocale->getStr('innomatic_settings.tab');
    
    $logsMain = new InnomaticLogsMaintenance();
    $cacheMain = new InnomaticCacheMaintenance();

    $gXmlDefinition = '<tab><name>innomatic</name>
      <args>
        <tabs type="array">'.WuiXml::encode($tabs).'</tabs>
        <tabactionfunction>innomatic_tab_builder</tabactionfunction>
        <activetab>'. (isset($eventData['tab']) ? $eventData['tab'] : '').'</activetab>
      </args>
      <children>
    
        <vertgroup><name></name>
          <children>
    
            <label><name>tabtitle</name>
              <args>
                <label type="encoded">'.urlencode($gLocale->getStr('innomatic_status.label')).'</label>
                <bold>true</bold>
              </args>
            </label>
    
        <grid>
          <children>
    
            <label row="0" col="0">
              <args>
                <label type="encoded">'.urlencode($gLocale->getStr('systemlogs_size.label')).'</label>
              </args>
            </label>
    
            <string row="0" col="1">
              <args>
                <size>15</size>
                <readonly>true</readonly>
                <value type="encoded">'.urlencode($country->FormatNumber($logsMain->getSystemLogsSize())).'</value>
              </args>
            </string>
    
            <button row="0" col="2">
              <args>
                <themeimage>documentdelete</themeimage>
                <label type="encoded">'.urlencode($gLocale->getStr('clear.label')).'</label>
                <horiz>true</horiz>
                <frame>false</frame>
                <action type="encoded">'
                .urlencode(
                    WuiEventsCall::buildEventsCallString(
                        '',
                        array(
                            array(
                                'view',
                                'innomatic'
                            ),
                            array(
                                'action',
                                'clear_systemlogs'
                            )
                        )
                    )
                ).'</action>
              </args>
            </button>
    
            <label row="1" col="0">
              <args>
                <label type="encoded">'.urlencode($gLocale->getStr('domainslogs_size.label')).'</label>
              </args>
            </label>
    
            <string row="1" col="1">
              <args>
                <size>15</size>
                <readonly>true</readonly>
                <value type="encoded">'.urlencode($country->FormatNumber($logsMain->getDomainsLogsSize())).'</value>
              </args>
            </string>
    
            <button row="1" col="2">
              <args>
                <themeimage>documentdelete</themeimage>
                <label type="encoded">'.urlencode($gLocale->getStr('clear.label')).'</label>
                <horiz>true</horiz>
                <frame>false</frame>
                <action type="encoded">'
                .urlencode(
                    WuiEventsCall::buildEventsCallString(
                        '',
                        array(
                            array(
                                'view',
                                'innomatic'
                            ),
                            array(
                                'action',
                                'clear_domainslogs'
                            )
                        )
                    )
                ).'</action>
              </args>
            </button>
    
            <label row="2" col="0">
              <args>
                <label type="encoded">'.urlencode($gLocale->getStr('cache_size.label')).'</label>
              </args>
            </label>
    
            <string row="2" col="1">
              <args>
                <size>15</size>
                <readonly>true</readonly>
                <value type="encoded">'.urlencode($country->FormatNumber($cacheMain->getCacheSize())).'</value>
              </args>
            </string>
    
            <button row="2" col="2">
              <args>
                <themeimage>documentdelete</themeimage>
                <label type="encoded">'.urlencode($gLocale->getStr('clear.label')).'</label>
                <horiz>true</horiz>
                <frame>false</frame>
                <action type="encoded">'
                .urlencode(
                    WuiEventsCall::buildEventsCallString(
                        '',
                        array(
                            array(
                                'view',
                                'innomatic'
                            ),
                            array(
                                'action',
                                'clear_cache'
                            )
                        )
                    )
                ).'</action>
              </args>
            </button>
    
            <label row="3" col="0">
              <args>
                <label type="encoded">'.urlencode($gLocale->getStr('sessions_size.label')).'</label>
              </args>
            </label>
    
            <string row="3" col="1">
              <args>
                <size>15</size>
                <readonly>true</readonly>
                <value type="encoded">'.urlencode($country->FormatNumber($cacheMain->getSessionsSize())).'</value>
              </args>
            </string>
    
            <button row="3" col="2">
              <args>
                <themeimage>documentdelete</themeimage>
                <label type="encoded">'.urlencode($gLocale->getStr('clear.label')).'</label>
                <horiz>true</horiz>
                <frame>false</frame>
                <action type="encoded">'
                .urlencode(
                    WuiEventsCall::buildEventsCallString(
                        '',
                        array(
                            array(
                                'view',
                                'innomatic'
                            ),
                            array(
                                'action',
                                'clear_sessions'
                            )
                        )
                    )
                ).'</action>
              </args>
            </button>
    
            <label row="4" col="0">
              <args>
                <label type="encoded">'.urlencode($gLocale->getStr('pidfiles_size.label')).'</label>
              </args>
            </label>
    
            <string row="4" col="1">
              <args>
                <size>15</size>
                <readonly>true</readonly>
                <value type="encoded">'.urlencode($country->FormatNumber($cacheMain->getPidFilesSize())).'</value>
              </args>
            </string>
    
            <button row="4" col="2">
              <args>
                <themeimage>documentdelete</themeimage>
                <label type="encoded">'.urlencode($gLocale->getStr('clear.label')).'</label>
                <horiz>true</horiz>
                <frame>false</frame>
                <action type="encoded">'
                .urlencode(
                    WuiEventsCall::buildEventsCallString(
                        '',
                        array(
                            array(
                                'view',
                                'innomatic'
                            ),
                            array(
                                'action',
                                'clear_pidfiles'
                            )
                        )
                    )
                ).'</action>
              </args>
            </button>
    
            <label row="5" col="0">
              <args>
                <label type="encoded">'.urlencode($gLocale->getStr('tempdirs_size.label')).'</label>
              </args>
            </label>
    
            <string row="5" col="1">
              <args>
                <size>15</size>
                <readonly>true</readonly>
                <value type="encoded">'.urlencode($country->FormatNumber($cacheMain->getRootTempDirsSize())).'</value>
              </args>
            </string>
    
            <button row="5" col="2">
              <args>
                <themeimage>documentdelete</themeimage>
                <label type="encoded">'.urlencode($gLocale->getStr('clear.label')).'</label>
                <horiz>true</horiz>
                <frame>false</frame>
                <action type="encoded">'
                .urlencode(
                    WuiEventsCall::buildEventsCallString(
                        '',
                        array(
                            array(
                                'view',
                                'innomatic'
                            ),
                            array(
                                'action',
                                'clear_tempdirs'
                            )
                        )
                    )
                ).'</action>
              </args>
            </button>
    
            <label row="6" col="0">
              <args>
                <label type="encoded">'.urlencode($gLocale->getStr('clipboard_size.label')).'</label>
              </args>
            </label>
    
            <string row="6" col="1">
              <args>
                <size>15</size>
                <readonly>true</readonly>
                <value type="encoded">'.urlencode($country->FormatNumber($cacheMain->getClipboardSize())).'</value>
              </args>
            </string>
    
            <button row="6" col="2">
              <args>
                <themeimage>documentdelete</themeimage>
                <label type="encoded">'.urlencode($gLocale->getStr('clear.label')).'</label>
                <horiz>true</horiz>
                <frame>false</frame>
                <action type="encoded">'
                .urlencode(
                    WuiEventsCall::buildEventsCallString(
                        '',
                        array(
                            array(
                                'view',
                                'innomatic'
                            ),
                            array(
                                'action',
                                'clear_clipboard'
                            )
                        )
                    )
                ).'</action>
              </args>
            </button>
    
            <label row="7" col="0">
              <args>
                <label type="encoded">'.urlencode($gLocale->getStr('cleanable_size.label')).'</label>
              </args>
            </label>
    
            <string row="7" col="1">
              <args>
                <size>15</size>
                <readonly>true</readonly>
                <value type="encoded">'
                .urlencode(
                    $country->FormatNumber(
                        $logsMain->getCleanableDiskSize() + $cacheMain->getCleanableDiskSize()
                    )
                ).'</value>
              </args>
            </string>
    
            <button row="7" col="2">
              <args>
                <themeimage>documentdelete</themeimage>
                <label type="encoded">'.urlencode($gLocale->getStr('clearall.label')).'</label>
                <horiz>true</horiz>
                <frame>false</frame>
                <action type="encoded">'
                .urlencode(
                    WuiEventsCall::buildEventsCallString(
                        '',
                        array(
                            array(
                                'view',
                                'innomatic'
                            ),
                            array(
                                'action',
                                'clear_all'
                            )
                        )
                    )
                ).'</action>
              </args>
            </button>
    
          </children>
        </grid>
    
          </children>
        </vertgroup>
        
        <vertgroup><name></name>
          <children>
    
            <label><name>tabtitle</name>
              <args>
                <label type="encoded">'.urlencode($gLocale->getStr('innomatic_requirements.label')).'</label>
                <bold>true</bold>
              </args>
            </label>
    
            <table><name>sysinfotable</name>
              <children>
        ';

    $wuiWidget = new WuiXml('dummy');
    // Required features

    // PHP version check
    //
    $row = 0;

    //if ( ereg( '[4-9]\.[0-9]\.[5-9].*', phpversion() ) or ereg( '[4-9]\.[1-9]\.[0-9].*', phpversion() ) )
    if (ereg("[5-9]\.[0-9]\.[0-9].*", phpversion())) {
        $ball = $wuiWidget->mThemeHandler->mStyle['greenball'];
        $checkResult = sprintf($gLocale->getStr('php_available_label'), phpversion());
    } else {
        $ball = $wuiWidget->mThemeHandler->mStyle['redball'];
        $checkResult = sprintf($gLocale->getStr('php_not_available_label'), phpversion());
    }

    $gXmlDefinition .= '<label row="'.$row.'" col="0"><name>required'.$row.'</name><args><label type="encoded">'
    .urlencode($gLocale->getStr('required_label')).'</label></args></label>
    <image row="'.$row.'" col="1"><name>status'.$row.'</name><args><imageurl type="encoded">'
    .urlencode($ball).'</imageurl></args></image>
    <label row="'.$row.'" col="2"><name>shared'.$row.'</name><args><label type="encoded">'
    .urlencode($gLocale->getStr('php_test_label')).'</label></args></label>
    <label row="'.$row.'" col="3"><name>checkresult'.$row.'</name><args><label type="encoded">'
    .urlencode($checkResult).'</label></args></label>';

    // File upload support
    //
    $row ++;

    if (ini_get('file_uploads') == '1') {
        $ball = $wuiWidget->mThemeHandler->mStyle['greenball'];
        $checkResult = $gLocale->getStr('fileupload_available_label');
    } else {
        $ball = $wuiWidget->mThemeHandler->mStyle['redball'];
        $checkResult = $gLocale->getStr('fileupload_not_available_label');
        $systemok = false;
    }

    $gXmlDefinition .= '<label row="'.$row.'" col="0"><name>required'.$row.'</name><args><label type="encoded">'
    .urlencode($gLocale->getStr('required_label')).'</label></args></label>
    <image row="'.$row.'" col="1"><name>status'.$row.'</name><args><imageurl type="encoded">'
    .urlencode($ball).'</imageurl></args></image>
    <label row="'.$row.'" col="2"><name>shared'.$row.'</name><args><label type="encoded">'
    .urlencode($gLocale->getStr('fileupload_test_label')).'</label></args></label>
    <label row="'.$row.'" col="3"><name>checkresult'.$row.'</name><args><label type="encoded">'
    .urlencode($checkResult).'</label></args></label>';

    // XML support
    //
    $row ++;

    if (function_exists('xml_set_object')) {
        $ball = $wuiWidget->mThemeHandler->mStyle['greenball'];
        $checkResult = $gLocale->getStr('xml_available_label');
    } else {
        $ball = $wuiWidget->mThemeHandler->mStyle['redball'];
        $checkResult = $gLocale->getStr('xml_not_available_label');
    }

    $gXmlDefinition .= '<label row="'.$row.'" col="0"><name>required'.$row.'</name><args><label type="encoded">'
    .urlencode($gLocale->getStr('required_label')).'</label></args></label>
    <image row="'.$row.'" col="1"><name>status'.$row.'</name><args><imageurl type="encoded">'
    .urlencode($ball).'</imageurl></args></image>
    <label row="'.$row.'" col="2"><name>shared'.$row.'</name><args><label type="encoded">'
    .urlencode($gLocale->getStr('xml_test_label')).'</label></args></label>
    <label row="'.$row.'" col="3"><name>checkresult'.$row.'</name><args><label type="encoded">'
    .urlencode($checkResult).'</label></args></label>';

    // Zlib support
    //
    $row ++;

    if (function_exists('gzinflate')) {
        $ball = $wuiWidget->mThemeHandler->mStyle['greenball'];
        $checkResult = $gLocale->getStr('zlib_available_label');
    } else {
        $ball = $wuiWidget->mThemeHandler->mStyle['redball'];
        $checkResult = $gLocale->getStr('zlib_not_available_label');
    }

    $gXmlDefinition .= '<label row="'.$row.'" col="0"><name>required'.$row.'</name><args><label type="encoded">'
    .urlencode($gLocale->getStr('required_label')).'</label></args></label>
    <image row="'.$row.'" col="1"><name>status'.$row.'</name><args><imageurl type="encoded">'
    .urlencode($ball).'</imageurl></args></image>
    <label row="'.$row.'" col="2"><name>shared'.$row.'</name><args><label type="encoded">'
    .urlencode($gLocale->getStr('zlib_test_label')).'</label></args></label>
    <label row="'.$row.'" col="3"><name>checkresult'.$row.'</name><args><label type="encoded">'
    .urlencode($checkResult).'</label></args></label>';

    // Database support
    //
    $row ++;

    if (function_exists('mysql_connect') or function_exists('pg_connect')) {
        $ball = $wuiWidget->mThemeHandler->mStyle['greenball'];
        $checkResult = $gLocale->getStr('db_available_label');
    } else {
        $ball = $wuiWidget->mThemeHandler->mStyle['redball'];
        $checkResult = $gLocale->getStr('db_not_available_label');
    }

    $gXmlDefinition .= '<label row="'.$row.'" col="0"><name>required'.$row.'</name><args><label type="encoded">'
    .urlencode($gLocale->getStr('required_label')).'</label></args></label>
    <image row="'.$row.'" col="1"><name>status'.$row.'</name><args><imageurl type="encoded">'
    .urlencode($ball).'</imageurl></args></image>
        <label row="'.$row.'" col="2"><name>shared'.$row.'</name><args><label type="encoded">'
        .urlencode($gLocale->getStr('db_test_label')).'</label></args></label>
    <label row="'.$row.'" col="3"><name>checkresult'.$row.'</name><args><label type="encoded">'
    .urlencode($checkResult).'</label></args></label>';
    
    // Optional features

    /*
    // Crontab
    //
    $row ++;

    if (strlen(InnomaticContainer::instance('innomaticcontainer')->getConfig()->value('RootCrontab'))) {
        $ball = $wuiWidget->mThemeHandler->mStyle['greenball'];
        $checkResult = $gLocale->getStr('crontab_available_label');
    } else {
        $ball = $wuiWidget->mThemeHandler->mStyle['goldball'];
        $checkResult = $gLocale->getStr('crontab_not_available_label');
    }

    $gXml_def .= '<label row="'.$row.'" col="0"><name>optional'.$row.'</name><args><label type="encoded">'
    .urlencode($gLocale->getStr('optional_label')).'</label></args></label>
    <image row="'.$row.'" col="1"><name>status'.$row.'</name><args><imageurl type="encoded">'
    .urlencode($ball).'</imageurl></args></image>
    <label row="'.$row.'" col="2"><name>shared'.$row.'</name><args><label type="encoded">'
    .urlencode($gLocale->getStr('crontab_test_label')).'</label></args></label>
    <label row="'.$row.'" col="3"><name>checkresult'.$row.'</name><args><label type="encoded">'
    .urlencode($checkResult).'</label></args></label>';
    */

    // XMLRPC auth
    //
    $row ++;

    if (php_sapi_name() != 'cgi') {
        $ball = $wuiWidget->mThemeHandler->mStyle['greenball'];
        $checkResult = $gLocale->getStr('xmlrpc_available_label');
    } else {
        $ball = $wuiWidget->mThemeHandler->mStyle['goldball'];
        $checkResult = $gLocale->getStr('xmlrpc_not_available_label');
    }

    $gXmlDefinition .= '<label row="'.$row.'" col="0"><name>optional'.$row.'</name><args><label type="encoded">'
    .urlencode($gLocale->getStr('optional_label')).'</label></args></label>
    <image row="'.$row.'" col="1"><name>status'.$row.'</name><args><imageurl type="encoded">'
    .urlencode($ball).'</imageurl></args></image>
    <label row="'.$row.'" col="2"><name>shared'.$row.'</name><args><label type="encoded">'
    .urlencode($gLocale->getStr('xmlrpc_test_label')).'</label></args></label>
    <label row="'.$row.'" col="3"><name>checkresult'.$row.'</name><args><label type="encoded">'
    .urlencode($checkResult).'</label></args></label>';
    

    // XMLRPC curl
    //
    $row ++;

    if (function_exists('curl_init')) {
        $ball = $wuiWidget->mThemeHandler->mStyle['greenball'];
        $checkResult = $gLocale->getStr('xmlrpc_ssl_available_label');
    } else {
        $ball = $wuiWidget->mThemeHandler->mStyle['goldball'];
        $checkResult = $gLocale->getStr('xmlrpc_ssl_not_available_label');
    }

    $gXmlDefinition .= '<label row="'.$row.'" col="0"><name>optional'.$row.'</name><args><label type="encoded">'
    .urlencode($gLocale->getStr('optional_label')).'</label></args></label>
    <image row="'.$row.'" col="1"><name>status'.$row.'</name><args><imageurl type="encoded">'
    .urlencode($ball).'</imageurl></args></image>
    <label row="'.$row.'" col="2"><name>shared'.$row.'</name><args><label type="encoded">'
    .urlencode($gLocale->getStr('xmlrpc_ssl_test_label')).'</label></args></label>
    <label row="'.$row.'" col="3"><name>checkresult'.$row.'</name><args><label type="encoded">'
    .urlencode($checkResult).'</label></args></label>';
    
    $gXmlDefinition .= '
    
              </children>
            </table>
          </children>
        </vertgroup>
        
    <vertgroup><name></name>
          <children>
    
            <label><name>tabtitle</name>
              <args>
                <label type="encoded">'
                .urlencode(
                    $gLocale->getStr('innomatic_settings.label')
                ).'</label>
                <bold>true</bold>
              </args>
            </label>
    
            <form><name>settings</name>
              <args>
                <action type="encoded">'
                .urlencode(
                    WuiEventsCall::buildEventsCallString(
                        '',
                        array(
                            array(
                                'view',
                                'innomatic'
                            ),
                            array(
                                'action',
                                'set_innomatic'
                            )
                        )
                    )
                ).'</action>          </args>
              <children>
        <vertgroup>
          <children>
        <grid>
          <children>
    
            <label row="0" col="0">
              <args>
                <label type="encoded">'.urlencode($gLocale->getStr('action_clean.label')).'</label>
                <bold>true</bold>
              </args>
            </label>
    
            <label row="0" col="1">
              <args>
                <label type="encoded">'.urlencode($gLocale->getStr('action_rotate.label')).'</label>
                <bold>true</bold>
              </args>
            </label>
    
            <label row="0" col="2">
              <args>
                <label type="encoded">'.urlencode($gLocale->getStr('action_none.label')).'</label>
                <bold>true</bold>
              </args>
            </label>
    
            <radio row="1" col="0" halign="center"><name>rootlog</name>
              <args>
                <disp>action</disp>
                <checked>'. ($logsMain->getCleanRootLog() ? 'true' : 'false').'</checked>
                <value>clean</value>
              </args>
            </radio>
    
            <radio row="1" col="1" halign="center"><name>rootlog</name>
              <args>
                <disp>action</disp>
                <checked>'. ($logsMain->getRotateRootLog() ? 'true' : 'false').'</checked>
                <value>rotate</value>
              </args>
            </radio>
    
            <radio row="1" col="2" halign="center"><name>rootlog</name>
              <args>
                <disp>action</disp>
                <checked>'
                . (
                   ($logsMain->getCleanRootLog() or $logsMain->getRotateRootLog()) ? 'false' : 'true'
                ).'</checked>
                <value>leave</value>
              </args>
            </radio>
    
            <label row="1" col="3">
              <args>
                <label type="encoded">'.urlencode($gLocale->getStr('rootlog_size.label')).'</label>
              </args>
            </label>
    
            <radio row="2" col="0" halign="center"><name>rootdalog</name>
              <args>
                <disp>action</disp>
                <checked>'. ($logsMain->getCleanRootDbLog() ? 'true' : 'false').'</checked>
                <value>clean</value>
              </args>
            </radio>
    
            <radio row="2" col="1" halign="center"><name>rootdalog</name>
              <args>
                <disp>action</disp>
                <checked>'. ($logsMain->getRotateRootDbLog() ? 'true' : 'false').'</checked>
                <value>rotate</value>
              </args>
            </radio>
    
            <radio row="2" col="2" halign="center"><name>rootdalog</name>
              <args>
                <disp>action</disp>
                <checked>'
                . (
                   ($logsMain->getCleanRootDbLog() or $logsMain->getRotateRootDbLog()) ? 'false' : 'true'
                ).'</checked>
                <value>leave</value>
              </args>
            </radio>
    
            <label row="2" col="3">
              <args>
                <label type="encoded">'.urlencode($gLocale->getStr('rootdalog_size.label')).'</label>
              </args>
            </label>
    
            <radio row="3" col="0" halign="center"><name>accesslog</name>
              <args>
                <disp>action</disp>
                <checked>'. ($logsMain->getCleanAccessLog() ? 'true' : 'false').'</checked>
                <value>clean</value>
              </args>
            </radio>
    
            <radio row="3" col="1" halign="center"><name>accesslog</name>
              <args>
                <disp>action</disp>
                <checked>'. ($logsMain->getRotateAccessLog() ? 'true' : 'false').'</checked>
                <value>rotate</value>
              </args>
            </radio>
    
            <radio row="3" col="2" halign="center"><name>accesslog</name>
              <args>
                <disp>action</disp>
                <checked>'
                . (
                   ($logsMain->getCleanAccessLog() or $logsMain->getRotateAccessLog()) ? 'false' : 'true'
                ).'</checked>
                <value>leave</value>
              </args>
            </radio>
    
            <label row="3" col="3">
              <args>
                <label type="encoded">'.urlencode($gLocale->getStr('accesslog_size.label')).'</label>
              </args>
            </label>
    
            <radio row="4" col="0" halign="center"><name>webserviceslog</name>
              <args>
                <disp>action</disp>
                <checked>'. ($logsMain->getCleanWebServicesLog() ? 'true' : 'false').'</checked>
                <value>clean</value>
              </args>
            </radio>
    
            <radio row="4" col="1" halign="center"><name>webserviceslog</name>
              <args>
                <disp>action</disp>
                <checked>'. ($logsMain->getRotateWebServicesLog() ? 'true' : 'false').'</checked>
                <value>rotate</value>
              </args>
            </radio>
    
            <radio row="4" col="2" halign="center"><name>webserviceslog</name>
              <args>
                <disp>action</disp>
                <checked>'
                . (
                   ($logsMain->getCleanWebServicesLog() or $logsMain->getRotateWebServicesLog()) ? 'false' : 'true'
                ).'</checked>
                <value>leave</value>
              </args>
            </radio>
    
            <label row="4" col="3">
              <args>
                <label type="encoded">'.urlencode($gLocale->getStr('webserviceslog_size.label')).'</label>
              </args>
            </label>
    
            <radio row="5" col="0" halign="center"><name>phplog</name>
              <args>
                <disp>action</disp>
                <checked>'. ($logsMain->getCleanPhpLog() ? 'true' : 'false').'</checked>
                <value>clean</value>
              </args>
            </radio>
    
            <radio row="5" col="1" halign="center"><name>phplog</name>
              <args>
                <disp>action</disp>
                <checked>'. ($logsMain->getRotatePhpLog() ? 'true' : 'false').'</checked>
                <value>rotate</value>
              </args>
            </radio>
    
            <radio row="5" col="2" halign="center"><name>phplog</name>
              <args>
                <disp>action</disp>
                <checked>'
                . (
                   ($logsMain->getCleanPhpLog() or $logsMain->getRotatePhpLog()) ? 'false' : 'true'
                ).'</checked>
                <value>leave</value>
              </args>
            </radio>
    
            <label row="5" col="3">
              <args>
                <label type="encoded">'.urlencode($gLocale->getStr('phplog_size.label')).'</label>
              </args>
            </label>
    
            <radio row="6" col="0" halign="center"><name>domainslogs</name>
              <args>
                <disp>action</disp>
                <checked>'. ($logsMain->getCleanDomainsLogs() ? 'true' : 'false').'</checked>
                <value>clean</value>
              </args>
            </radio>
    
            <radio row="6" col="1" halign="center"><name>domainslogs</name>
              <args>
                <disp>action</disp>
                <checked>'. ($logsMain->getRotateDomainsLogs() ? 'true' : 'false').'</checked>
                <value>rotate</value>
              </args>
            </radio>
    
            <radio row="6" col="2" halign="center"><name>domainslogs</name>
              <args>
                <disp>action</disp>
                <checked>'
                . (
                   ($logsMain->getCleanDomainsLogs() or $logsMain->getRotateDomainsLogs()) ? 'false' : 'true'
                ).'</checked>
                <value>leave</value>
              </args>
            </radio>
    
            <label row="6" col="3">
              <args>
                <label type="encoded">'.urlencode($gLocale->getStr('domainslogs_size.label')).'</label>
              </args>
            </label>
    
          </children>
        </grid>
    
        <horizbar/>
    
        <grid>
          <children>
            <checkbox row="0" col="0"><name>cache</name>
              <args>
                <disp>action</disp>
                <checked>'. ($cacheMain->getCleanCache() ? 'true' : 'false').'</checked>
              </args>
            </checkbox>
    
            <label row="0" col="1">
              <args>
                <label type="encoded">'.urlencode($gLocale->getStr('cache_size.label')).'</label>
              </args>
            </label>
    
            <checkbox row="1" col="0"><name>sessions</name>
              <args>
                <disp>action</disp>
                <checked>'. ($cacheMain->getCleanSessions() ? 'true' : 'false').'</checked>
              </args>
            </checkbox>
    
            <label row="1" col="1">
              <args>
                <label type="encoded">'.urlencode($gLocale->getStr('sessions_size.label')).'</label>
              </args>
            </label>
    
            <checkbox row="2" col="0"><name>pidfiles</name>
              <args>
                <disp>action</disp>
                <checked>'. ($cacheMain->getCleanPidFiles() ? 'true' : 'false').'</checked>
              </args>
            </checkbox>
    
            <label row="2" col="1">
              <args>
                <label type="encoded">'.urlencode($gLocale->getStr('pidfiles_size.label')).'</label>
              </args>
            </label>
    
            <checkbox row="3" col="0"><name>roottempdirs</name>
              <args>
                <disp>action</disp>
                <checked>'. ($cacheMain->getCleanRootTempDirs() ? 'true' : 'false').'</checked>
              </args>
            </checkbox>
    
            <label row="3" col="1">
              <args>
                <label type="encoded">'.urlencode($gLocale->getStr('tempdirs_size.label')).'</label>
              </args>
            </label>
    
            <checkbox row="4" col="0"><name>clipboard</name>
              <args>
                <disp>action</disp>
                <checked>'. ($cacheMain->getCleanClipboard() ? 'true' : 'false').'</checked>
              </args>
            </checkbox>
    
            <label row="4" col="1">
              <args>
                <label type="encoded">'.urlencode($gLocale->getStr('clipboard_size.label')).'</label>
              </args>
            </label>
    
          </children>
        </grid>
    
          </children>
        </vertgroup>
    
              </children>
            </form>
    
            <horizbar/>
    
            <button>
              <args>
                <themeimage>buttonok</themeimage>
                <label type="encoded">'.urlencode($gLocale->getStr('apply.button')).'</label>
                <horiz>true</horiz>
                <frame>false</frame>
                <formsubmit>settings</formsubmit>
                <action type="encoded">'
                .urlencode(
                    WuiEventsCall::buildEventsCallString(
                        '',
                        array(
                            array(
                                'view',
                                'innomatic'
                            ),
                            array(
                                'action',
                                'set_innomatic'
                            )
                        )
                    )
                ).'</action>
              </args>
            </button>
    
          </children>
        </vertgroup>
    
      </children>
    </tab>';

    $gPageTitle.= ' - '.$gLocale->getStr('innomatic.title');
}

$gViewDispatcher->addEvent('showrootlog', 'main_showrootlog');
function main_showrootlog($eventData)
{
    global $gLocale, $gWuiMainStatus, $gPageTitle, $gWuiMainVertGroup, $gXmlDefinition, $gToolbars;

    $logContent = '';

    if (file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/log/innomatic.log')) {

        $cleanLogAction = new WuiEventsCall();
        $cleanLogAction->addEvent(new WuiEvent('view', 'showrootlog', ''));
        $cleanLogAction->addEvent(new WuiEvent('action', 'cleanrootlog', ''));

        $gToolbars['logs']['cleanlog'] = array(
            'label' => $gLocale->getStr('cleanlog_button'), 
            'themeimage' => 'documentdelete', 
            'horiz' => 'true', 
            'action' => $cleanLogAction->getEventsCallString()
        );
        
        if (file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/log/innomatic.log')) {
            $logContent = file_get_contents(
                InnomaticContainer::instance('innomaticcontainer')->getHome().'core/log/innomatic.log'
            );
        }
    }

    $gXmlDefinition = '<vertgroup><name />
    <children>
      <text><name>rootlog</name>
        <args>
          <disp>action</disp>
          <readonly>true</readonly>
          <rows>20</rows>
          <cols>120</cols>
          <value type="encoded">'.urlencode(Wui::utf8_entities($logContent)).'</value>
        </args>
      </text>
    </children>
    </vertgroup>';
        $gPageTitle.= ' - '.$gLocale->getStr('rootlog_title');
}

$gViewDispatcher->addEvent('showrootwebserviceslog', 'main_showrootwebserviceslog');
function main_showrootwebserviceslog($eventData)
{
    global $gLocale, $gPageTitle, $gXmlDefinition, $gToolbars;
$gWui = Wui::instance('wui');
$gWui->loadWidget('vertgroup');
$gWui->loadWidget('toolbars');

    $logContent = '';

    if (file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/log/webservices.log')) {
        $cleanLogAction = new WuiEventsCall();
        $cleanLogAction->addEvent(new WuiEvent('view', 'showrootwebserviceslog', ''));
        $cleanLogAction->addEvent(new WuiEvent('action', 'cleanrootwebserviceslog', ''));

        $gToolbars['logs']['cleanlog'] = array('view' => array(
            'label' => $gLocale->getStr('cleanlog_button'), 
            'themeimage' => 'documentdelete', 
            'horiz' => 'true', 
            'action' => $cleanLogAction->getEventsCallString()
        ));
        
        if (file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/log/webservices.log')) {
            $logContent = file_get_contents(
                InnomaticContainer::instance('innomaticcontainer')->getHome().'core/log/webservices.log'
            );
        }
    }

    $gXmlDefinition = '<vertgroup><name />
    <children>
      <text><name>rootlog</name>
        <args>
          <disp>action</disp>
          <readonly>true</readonly>
          <rows>20</rows>
          <cols>120</cols>
          <value type="encoded">'.urlencode(Wui::utf8_entities($logContent)).'</value>
        </args>
      </text>
    </children>
    </vertgroup>';
        $gPageTitle.= ' - '.$gLocale->getStr('rootwebserviceslog_title');
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
            'toolbars' => array(
                new WuiInnomaticToolbar(
                    'view',
                    array(
                        'toolbars' => $gToolbars, 'toolbar' => 'true')
                    )
                ),
            'maincontent' => new WuiXml(
                'page',
                array(
                    'definition' => $gXmlDefinition)
                ),
            'status' => $gPageStatus,
            'icon' => 'gear'
        )
    )
);

$gWui->render();