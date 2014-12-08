<?php
/**
 * Innomatic
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2014 Innomatic Company
 * @license    http://www.innomatic.io/license/ New BSD License
 * @link       http://www.innomatic.io
 * @since      Class available since Release 5.0
*/

// NOTE: This is an old-style panel code with a single file
// acting as model, view and controller.

global $wuiMainStatus, $wuiPage, $wuiMainVertGroup, $gStatus, $gXmlDefinition;
global $gPageTitle, $gToolbars, $gLocale, $gPageContent;

$log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();
$gLocale = new \Innomatic\Locale\LocaleCatalog(
    'innomatic::root_applications', \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLanguage()
);
$gPageContent = $gStatus = $gToolbars = $gXmlDefinition = '';

$wui = \Innomatic\Wui\Wui::instance('\Innomatic\Wui\Wui');
$wui->loadAllWidgets();

$gPageTitle = $gLocale->getStr('applications_title');

// Help tool bar
//
$wuiHelpToolBar = new WuiToolBar('helpbar');

$gViewDispatcher = new WuiDispatcher('view');
$eventName = $gViewDispatcher->getEventName();

if (strcmp($eventName, 'help')) {
    $helpAction = new \Innomatic\Wui\Dispatch\WuiEventsCall();
    $helpAction->addEvent(new \Innomatic\Wui\Dispatch\WuiEvent('view', 'help', array('node' => $eventName)));
    $wuiHelpButton = new WuiButton(
        'helpbutton',
        array(
            'label' => $gLocale->getStr('help_button'),
            'themeimage' => 'info',
            'horiz' => 'true',
            'action' => $helpAction->getEventsCallString()
        )
    );

    $wuiHelpToolBar->addChild($wuiHelpButton);
}

$gToolbars['view'] = array(
    'default' => array(
        'label' => $gLocale->getStr('applications_button'),
        'themeimage' => 'listdetailed2',
        'action' => \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('', array(array('view', 'default', ''))),
        'horiz' => 'true'
        ),
    'repository' => array(
        'label' => $gLocale->getStr('repository.toolbar'),
        'themeimage' => 'globe2',
        'action' => \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('', array(array('view', 'appcentral', ''))),
        'horiz' => 'true'
        ),
    'keyring' => array(
        'label' => $gLocale->getStr('keys.toolbar'),
        'themeimage' => 'keyhole',
        'horiz' => 'true',
        'action' => \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString(
            '',
            array(
                array(
                    'view',
                    'keyring',
                    ''
                )
            )
        )
    )
);

    $wuiMainFrame = new WuiHorizframe('mainframe');
//$wui_mainstatus = new WuiStatusBar('mainstatusbar');

// Pass dispatcher
//
$gActionDispatcher = new WuiDispatcher('action');

$gActionDispatcher->addEvent('install', 'action_install');
function action_install($eventData)
{
    global $gLocale, $gLocale, $gStatus;

    if (strcmp($eventData['applicationfile']['tmp_name'], 'none') != 0) {
        $tempApplication = new \Innomatic\Application\Application(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(), '');

        move_uploaded_file(
            $eventData['applicationfile']['tmp_name'],
            \Innomatic\Core\InnomaticContainer::instance(
                '\Innomatic\Core\InnomaticContainer'
            )->getHome().'core/temp/'.$eventData['applicationfile']['name']
        );
        if (
            !$tempApplication->Install(
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome().'core/temp/'
                .$eventData['applicationfile']['name']
            )
        ) {
            $unmetDeps = $tempApplication->getLastActionUnmetDeps();
            $unmetDepsStr = '';
            while (list ($key, $val) = each($unmetDeps)) {
                $unmetDepsStr.= ' '.$val;
            }
            $gStatus = $gLocale->getStr('unmetdeps_status').$unmetDepsStr;
        } else {
            $gStatus = $gLocale->getStr('appinstalled_status');
        }

        $unmetSuggs = $tempApplication->getLastActionUnmetSuggs();
        $unmetSuggsStr = '';
        while (list ($key, $val) = each($unmetSuggs)) {
            $unmetSuggsStr.= ' '.$val;
        }
        if (strlen($unmetSuggsStr)) {
            $gStatus.= $gLocale->getStr('unmetsuggs_status').$unmetSuggsStr;
        }
    }
}

$gActionDispatcher->addEvent('uninstall', 'action_uninstall');
function action_uninstall($eventData)
{
    global $gLocale, $gLocale, $wuiPage, $gStatus;

    $tempApplication = new \Innomatic\Application\Application(
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
        $eventData['appid']
    );
    if (!$tempApplication->uninstall()) {
        $unmetDeps = $tempApplication->getLastActionUnmetDeps();
        while (list ($key, $val) = each($unmetDeps))
            $unmetDepsStr.= ' '.$val;
        $gStatus = $gLocale->getStr('removeunmetdeps_status').$unmetDepsStr;
    } else
        $gStatus = $gLocale->getStr('moduninstalled_status');
}

$gActionDispatcher->addEvent('activateapplication', 'action_activateapplication');
function action_activateapplication($eventData)
{
    global $gLocale, $gLocale, $gStatus;

    $domainQuery = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->execute(
        'SELECT domainid FROM domains WHERE id = '.$eventData['domainid']
    );

    if ($domainQuery) {
        $domainData = $domainQuery->getFields();

        $domain = new \Innomatic\Domain\Domain(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
            $domainData['domainid'], $null
        );
        if (!$domain->enableApplication($eventData['appid'])) {
            $unmetDeps = $domain->getLastActionUnmetDeps();

            if (count($unmetDeps)) {
                while (list (, $dep) = each($unmetDeps))
                    $unmetDepsStr.= ' '.$dep;

                $gStatus.= $gLocale->getStr('modnotenabled_status').' ';
                $gStatus.= $gLocale->getStr('unmetdeps_status').$unmetDepsStr.'.';
            }

            $unmetSuggs = $domain->getLastActionUnmetSuggs();

            if (count($unmetSuggs)) {
                while (list (, $sugg) = each($unmetSuggs))
                    $unmetSuggsStr.= ' '.$sugg.$gStatus.= $gLocale->getStr('unmetsuggs_status').$unmetSuggsStr.'.';
            }
        } else
            $gStatus.= $gLocale->getStr('modenabled_status');
    }
}

$gActionDispatcher->addEvent('deactivateapplication', 'action_deactivateapplication');
function action_deactivateapplication($eventData)
{
    global $gLocale, $gLocale, $gStatus;

    $domainQuery = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->execute(
        'SELECT domainid FROM domains WHERE id = '.$eventData['domainid']
    );

    if ($domainQuery) {
        $domainData = $domainQuery->getFields();

        $domain = new \Innomatic\Domain\Domain(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
            $domainData['domainid'],
            $null
        );
        if (!$domain->disableApplication($eventData['appid'])) {
            $unmetDeps = $domain->getLastActionUnmetDeps();

            if (count($unmetDeps)) {
                while (list (, $dep) = each($unmetDeps))
                    $unmetDepsStr.= ' '.$dep;

                $gStatus.= $gLocale->getStr('modnotdisabled_status').' ';
                $gStatus.= $gLocale->getStr('disunmetdeps_status').$unmetDepsStr.'.';
            }
        } else
            $gStatus.= $gLocale->getStr('moddisabled_status');
    }
}

$gActionDispatcher->addEvent('cleanmodlog', 'action_cleanmodlog');
function action_cleanmodlog($eventData)
{
    global $gLocale, $gLocale, $gStatus;

    $tempLog = new \Innomatic\Logging\Logger(
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome().'core/applications/'.$eventData['appid']
        .'/application.log'
    );

    if ($tempLog->cleanLog()) {
        $gStatus = $gLocale->getStr('logcleaned_status');
    } else {
        $gStatus = $gLocale->getStr('lognotcleaned_status');
    }
}

$gActionDispatcher->addEvent('newrepository', 'action_newrepository');
function action_newrepository($eventData)
{
    global $gLocale, $gStatus;

    $remoteAc = new \Innomatic\Application\AppCentralRemoteServer();
    if ($remoteAc->Add($eventData['accountid'])) $gStatus = $gLocale->getStr('repository_added.status');
    else $gStatus = $gLocale->getStr('repository_not_added.status');
}

$gActionDispatcher->addEvent('removerepository', 'action_removerepository');
function action_removerepository($eventData)
{
    global $gLocale, $gStatus;

    $remoteAc = new \Innomatic\Application\AppCentralRemoteServer(
        $eventData['id']
    );
    if ($remoteAc->Remove()) $gStatus = $gLocale->getStr('repository_removed.status');
    else $gStatus = $gLocale->getStr('repository_not_removed.status');
}

$gActionDispatcher->addEvent(
    'installapplication',
    'action_installapplication'
);
function action_installapplication($eventData)
{
    global $gLocale, $gStatus;

    $remoteAc = new \Innomatic\Application\AppCentralRemoteServer(
        $eventData['id']
    );
    if (
        $remoteAc->RetrieveApplication(
            $eventData['repid'],
            $eventData['applicationid'],
            isset($eventData['version']) ? $eventData['version'] : ''
        )
    ) {
            $gStatus = $gLocale->getStr('application_installed.status');
    } else {
            $gStatus = $gLocale->getStr('application_not_installed.status');
    }
}

$gActionDispatcher->addEvent(
    'newkey',
    'action_newkey'
);
function action_newkey($eventData)
{
    global $gStatus, $gLocale;
    $keyring = new \Innomatic\Application\ApplicationKeyRing();
    if (
        $keyring->AddKey(
            $eventData['key']['tmp_name']
        )
    ) {
        $gStatus = $gLocale->getStr('newkey_added.status');
    } else {
        $gStatus = $gLocale->getStr('newkey_not_added.status');
    }
}

$gActionDispatcher->addEvent(
    'removekey',
    'action_removekey'
);
function action_removekey($eventData)
{
    global $gStatus, $gLocale;

    $keyring = new \Innomatic\Application\ApplicationKeyRing();
    $keyring->RemoveKey($eventData['id']);

    $gStatus = $gLocale->getStr('removekey_removed.status');
}

$gActionDispatcher->Dispatch();

// Main dispatcher
//
$gViewDispatcher = new WuiDispatcher('view');

function applications_list_action_builder($pageNumber)
{
    return \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString(
        '',
        array(
            array(
                'view',
                'default',
                array('applicationspage' => $pageNumber)
            )
        )
    );
}

function applications_tab_action_builder($tab)
{
    return \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString(
        '',
        array(
            array(
                'view',
                'default',
                array('activetab' => $tab)
            )
        )
    );
}

$gViewDispatcher->addEvent('default', 'main_default');
function main_default($eventData)
{
    global $wuiMainFrame, $gLocale, $gPageContent;
    $gPageContent = new WuiVertgroup('apps');

    $applicationsQuery = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->execute(
        'SELECT * FROM applications ORDER BY category,appid'
    );

    if ($applicationsQuery->getNumberRows() > 0) {
        $headers[1]['label'] = $gLocale->getStr('appid_header');
        $headers[2]['label'] = $gLocale->getStr('modauthor_header');
        $headers[3]['label'] = $gLocale->getStr('appversion_header');
        $headers[4]['label'] = $gLocale->getStr('appdate_header');

        $row = 0;
        $currentCategory = '';

        while (!$applicationsQuery->eof) {
            $tmpData = $applicationsQuery->getFields();
            if ($tmpData['category'] == '')
                $tmpData['category'] = 'various';
            $applicationsArray[$tmpData['category']][] = $tmpData;
            $applicationsQuery->moveNext();
        }

        ksort($applicationsArray);

        $categories = array();

        while (list (, $tmpData) = each($applicationsArray)) {
            while (list (, $data) = each($tmpData)) {
                if ($data['category'] != $currentCategory) {
                    $wuiApplicationsTable[$data['category']] = new WuiTable(
                        'applicationstable',
                        array(
                            'headers' => $headers,
                            'rowsperpage' => '10',
                            'pagesactionfunction' => 'applications_list_action_builder',
                            'pagenumber' => (isset(
                                $eventData['applicationspage']
                            ) ? $eventData['applicationspage'] : ''),
                            'sessionobjectusername' => $data['category']
                        )
                    );
                    $currentCategory = $data['category'];

                    $categories[] = $data['category'];
                    $row = 0;

                  //$wui_applications_table->addChild(
                  //    new WuiLabel(
                  //        'modcategory'.$row,
                  //        array(
                  //            'label' => '<strong><font color="red">'.ucfirst($data['category']).'</font></strong>'
                  //        )
                  //    ),
                  //    $row, 0
                  //);
                  //$row++;
                }

                if (strlen($data['iconfile'])) {
                    $wuiApplicationsTable[$data['category']]->addChild(
                        new WuiImage(
                            'icon'.$row,
                            array(
                                'hint' => $data['appid'],
                                'imageurl' => \Innomatic\Core\InnomaticContainer::instance(
                                    '\Innomatic\Core\InnomaticContainer'
                                )->getBaseUrl(false).'/core/applications/'.$data['appid'].'/'.$data['iconfile']
                            )
                        ),
                        $row, 0, 'center'
                    );
                }
                $wuiApplicationsTable[$data['category']]->addChild(
                    new WuiLabel(
                        'appidlabel'.$row,
                        array(
                            'label' => '<strong>'.$data['appid'].'</strong><br />'.$data['appdesc'],
                            'nowrap' => 'false'
                        )
                    ),
                    $row, 1
                );
                $wuiApplicationsTable[$data['category']]->addChild(
                    new WuiLink(
                        'modauthorlabel'.$row,
                        array(
                            'label' => $data['author'],
                            'link' => $data['authorsite']
                        )
                    ),
                    $row, 2
                );
                $wuiApplicationsTable[$data['category']]->addChild(
                    new WuiLabel(
                        'appversionlabel'.$row,
                        array(
                            'label' => $data['appversion']
                        )
                    ),
                    $row, 3
                );
                $wuiApplicationsTable[$data['category']]->addChild(
                    new WuiLabel(
                        'appdatedatelabel'.$row,
                        array(
                            'label' => $data['appdate']
                        )
                    ),
                    $row, 4
                );

                $wuiApplicationToolbar[$data['category']][$row] = new WuiHorizgroup('applicationtoolbar'.$row);

                $detailsAction[$data['category']][$row] = new \Innomatic\Wui\Dispatch\WuiEventsCall();
                $detailsAction[$data['category']][$row]->addEvent(
                    new \Innomatic\Wui\Dispatch\WuiEvent(
                        'view',
                        'details',
                        array(
                            'appid' => $data['id']
                        )
                    )
                );
                $wuiDetailsButton[$data['category']][$row] = new WuiButton(
                    'detailsbutton'.$row,
                    array(
                        'label' => $gLocale->getStr('moddetails_label'),
                        'themeimage' => 'zoom',
                        'action' => $detailsAction[$data['category']][$row]->getEventsCallString(),
                        'horiz'=>'true'
                    )
                );
                $wuiApplicationToolbar[$data['category']][$row]->addChild($wuiDetailsButton[$data['category']][$row]);

                if (strcmp($data['appid'], 'innomatic')) {
                    $depsAction[$data['category']][$row] = new \Innomatic\Wui\Dispatch\WuiEventsCall();
                    $depsAction[$data['category']][$row]->addEvent(
                        new \Innomatic\Wui\Dispatch\WuiEvent(
                            'view',
                            'dependencies',
                            array(
                                'appid' => $data['id']
                            )
                        )
                    );
                    $wuiDepsButton[$data['category']][$row] = new WuiButton(
                        'depsbutton'.$row,
                        array(
                            'label' => $gLocale->getStr('applicationdeps_label'),
                            'themeimage' => 'listbulletleft',
                            'action' => $depsAction[$data['category']][$row]->getEventsCallString(),
                            'horiz'=>'true'
                        )
                    );
                    $wuiApplicationToolbar[$data['category']][$row]->addChild(
                        $wuiDepsButton[$data['category']][$row]
                    );

                    $removeAction[$data['category']][$row] = new \Innomatic\Wui\Dispatch\WuiEventsCall();
                    $removeAction[$data['category']][$row]->addEvent(
                        new \Innomatic\Wui\Dispatch\WuiEvent(
                            'view',
                            'default',
                            ''
                            )
                    );
                    $removeAction[$data['category']][$row]->addEvent(
                        new \Innomatic\Wui\Dispatch\WuiEvent(
                            'action',
                            'uninstall',
                            array(
                                'appid' => $data['id']
                            )
                        )
                    );
                    $wuiRemoveButton[$data['category']][$row] = new WuiButton(
                        'removebutton'.$row,
                        array(
                            'label' => $gLocale->getStr('removeapplication_label'),
                            'themeimage' => 'trash',
                            'action' => $removeAction[$data['category']][$row]->getEventsCallString(),
                            'needconfirm' => 'true',
                            'confirmmessage' => sprintf(
                                $gLocale->getStr(
                                    'removeapplicationquestion_label'
                                ),
                                $data['appid']
                            ),
                            'horiz'=>'true'
                        )
                    );
                    $wuiApplicationToolbar[$data['category']][$row]->addChild(
                        $wuiRemoveButton[$data['category']][$row]
                    );
                }

                if (
                    file_exists(
                        \Innomatic\Core\InnomaticContainer::instance(
                            '\Innomatic\Core\InnomaticContainer'
                        )->getHome().'core/applications/'.$data['appid'].'/application.log'
                    )
                ) {
                    $logAction[$data['category']][$row] = new \Innomatic\Wui\Dispatch\WuiEventsCall();
                    $logAction[$data['category']][$row]->addEvent(
                        new \Innomatic\Wui\Dispatch\WuiEvent(
                            'view',
                            'applicationlog',
                            array(
                                'appid' => $data['id']
                            )
                        )
                    );
                    $wuiLogButton[$data['category']][$row] = new WuiButton(
                        'logbutton'.$row,
                        array(
                            'label' => $gLocale->getStr('modlog_label'),
                            'themeimage' => 'alignjustify',
                            'action' => $logAction[$data['category']][$row]->getEventsCallString(),
                            'horiz'=>'true'
                        )
                    );
                    $wuiApplicationToolbar[$data['category']][$row]->addChild(
                        $wuiLogButton[$data['category']][$row]
                    );
                }

                $wuiApplicationsTable[$data['category']]->addChild(
                    $wuiApplicationToolbar[$data['category']][$row],
                    $row,
                    5
                );

                $row ++;
            }

            while (list (, $category) = each($categories)) {
                $tabs[]['label'] = ucfirst($category);
            }
            reset($categories);

            $wuiTabs = new WuiTab(
                'applicationstab',
                array(
                    'tabactionfunction' => 'applications_tab_action_builder',
                    'tabs' => $tabs,
                    'activetab' => (isset($eventData['activetab']) ? $eventData['activetab'] : '')
                )
            );

            while (list (, $category) = each($categories)) {
                $wuiTabs->addChild($wuiApplicationsTable[$category]);
            }
        }

        $gPageContent->addChild($wuiTabs);

        $gPageContent->addChild(new WuiHorizBar('hb'));
    } else {
        $gStatus = $gLocale->getStr('no_available_applications_status');
    }

    $gXmlDef =
'<vertgroup>
  <children>

    <form><name>newapplicationform</name>
      <args>
        <action type="encoded">'
        .urlencode(
            \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString(
                '',
                array(
                    array(
                        'view',
                        'default'
                    ),
                    array(
                        'action',
                        'install'
                    )
                )
            )
        )
        .'</action>
      </args>
      <children>

        <grid>
          <children>

            <label row="0" col="0">
              <args>
                <label type="encoded">'.urlencode($gLocale->getStr('applicationfile_label')).'</label>
              </args>
            </label>

            <file row="0" col="1"><name>applicationfile</name>
              <args>
                <disp>action</disp>
              </args>
            </file>

    <button row="0" col="2">
      <args>
        <horiz>true</horiz>
        <frame>false</frame>
        <themeimage>mathadd</themeimage>
        <formsubmit>newapplicationform</formsubmit>
        <label type="encoded">'.urlencode($gLocale->getStr('newapplication_submit')).'</label>
        <action type="encoded">'
        .urlencode(
            \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString(
                '',
                array(
                    array(
                        'view',
                        'default'
                    ),
                    array(
                        'action',
                        'install'
                    )
                )
            )
        )
        .'</action>
      </args>
    </button>

          </children>
        </grid>

      </children>
    </form>

  </children>
</vertgroup>';

    $gPageContent->addChild(new WuiXml('newapp', array('definition' => $gXmlDef)));
}

$gViewDispatcher->addEvent('details', 'main_details');
function main_details($eventData)
{
    global $wuiMainFrame, $gLocale, $gPageTitle, $gPageContent;

    $query = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->execute(
        'SELECT * FROM applications WHERE id='.$eventData['appid'].' '
    );

    $applicationData = $query->getFields();

    $gPageContent = new WuiVertgroup('vgroup');

    $detailsGrid = new WuiGrid(
        'applicationdetailsgrid',
        array(
            'rows' => '9',
            'cols' => '2'
        )
    );

    $detailsGrid->addChild(
        new WuiLabel(
            'authorlabel',
            array(
                'label' => $gLocale->getStr('author_label')
            )
        ),
        0, 0
    );
    $detailsGrid->addChild(
        new WuiString(
            'author',
            array(
                'value' => $applicationData['author'],
                'readonly' => 'true',
                'size' => 40
            )
        ),
        0, 1
    );

    $detailsGrid->addChild(
        new WuiLabel(
            'authorsitelabel',
            array(
                'label' => $gLocale->getStr('authorsite_label')
            )
        ),
        1, 0
    );
    $detailsGrid->addChild(
        new WuiLink(
            'authorsite',
            array(
                'label' => $applicationData['authorsite'],
                'link' => $applicationData['authorsite']
            )
        ),
        1, 1
    );

    $detailsGrid->addChild(
        new WuiLabel(
            'authoremaillabel',
            array(
                'label' => $gLocale->getStr('authoremail_label')
            )
        ),
        2, 0
    );
    $detailsGrid->addChild(
        new WuiLink(
            'authoremail',
            array(
                'label' => $applicationData['authoremail'],
                'link' => (
                    strlen($applicationData['authoremail']) ? 'mailto:'.$applicationData['authoremail'] : ''
                )
            )
        ),
        2, 1
    );

    $detailsGrid->addChild(
        new WuiLabel(
            'supportemaillabel',
            array(
                'label' => $gLocale->getStr('supportemail_label')
            )
        ),
        3, 0
    );
    $detailsGrid->addChild(
        new WuiLink(
            'supportemail',
            array(
                'label' => $applicationData['supportemail'],
                'link' => (
                    strlen($applicationData['supportemail']) ? 'mailto:'.$applicationData['supportemail'] : ''
                )
            )
        ),
        3, 1
    );

    $detailsGrid->addChild(
        new WuiLabel(
            'bugsemaillabel',
            array(
                'label' => $gLocale->getStr('bugsemail_label')
            )
        ),
        4, 0
    );
    $detailsGrid->addChild(
        new WuiLink(
            'bugsemail',
            array(
                'label' => $applicationData['bugsemail'],
                'link' => (
                    strlen($applicationData['bugsemail']) ? 'mailto:'.$applicationData['bugsemail'] : ''
                )
            )
        ),
        4, 1
    );

    $detailsGrid->addChild(
        new WuiLabel(
            'maintainerlabel',
            array(
                'label' => $gLocale->getStr('maintainer_label')
            )
        ),
        5, 0
    );
    $detailsGrid->addChild(
        new WuiString(
            'maintainer',
            array(
                'value' => $applicationData['maintainer'],
                'readonly' => 'true',
                'size' => 40
            )
        ),
        5, 1
    );

    $detailsGrid->addChild(
        new WuiLabel(
            'maintaineremaillabel',
            array(
                'label' => $gLocale->getStr('maintaineremail_label')
            )
        ),
        6, 0
    );
    $detailsGrid->addChild(
        new WuiLink(
            'maintaineremail',
            array(
                'label' => $applicationData['maintaineremail'],
                'link' => (
                    strlen($applicationData['maintaineremail']) ? 'mailto:'.$applicationData['maintaineremail'] : ''
                )
            )
        ),
        6, 1
    );

    $detailsGrid->addChild(
        new WuiLabel(
            'copyrightlabel',
            array(
                'label' => $gLocale->getStr('copyright_label')
            )
        ),
        7, 0
    );
    $detailsGrid->addChild(
        new WuiString(
            'copyright',
            array(
                'value' => $applicationData['copyright'],
                'readonly' => 'true',
                'size' => 40
            )
        ),
        7, 1
    );

    $detailsGrid->addChild(
        new WuiLabel(
            'licenselabel',
            array(
                'label' => $gLocale->getStr('license_label')
            )
        ),
        8, 0
    );
    $detailsGrid->addChild(
        new WuiString(
            'license',
            array(
                'value' => $applicationData['license'],
                'readonly' => 'true',
                'size' => 20
            )
        ),
        8, 1
    );

    $rows = 9;

    if (
        strlen($applicationData['licensefile']) and file_exists(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome()
            .'core/applications/'.$applicationData['appid'].'/'.$applicationData['licensefile']
        )
    ) {
            $licenseText = file_get_contents(
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome()
                .'core/applications/'.$applicationData['appid'].'/'.$applicationData['licensefile']
            );
            $detailsGrid->addChild(
                new WuiText(
                    'licensetext',
                    array(
                        'label' => $applicationData['license'],
                        'value' => $licenseText,
                        'readonly' => 'true',
                        'cols' => 90,
                        'rows' => '20'
                    )
                ),
                $rows, 1
            );
            $detailsGrid->mRows = ++$rows;
    }

    if (
        strlen($applicationData['changesfile']) and file_exists(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome()
            .'core/applications/'.$applicationData['appid'].'/'.$applicationData['changesfile']
        )
    ) {
            $changesText = file_get_contents(
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome()
                .'core/applications/'.$applicationData['appid'].'/'.$applicationData['changesfile']
            );
            $detailsGrid->addChild(
                new WuiLabel(
                    'changeslabel',
                    array(
                        'label' => $gLocale->getStr('changes_label')
                    )
                ),
                $rows, 0
            );
            $detailsGrid->addChild(
                new WuiText(
                    'changestext',
                    array(
                        'value' => $changesText,
                        'readonly' => 'true',
                        'cols' => 90,
                        'rows' => '20'
                    )
                ),
                $rows, 1
            );
            $detailsGrid->mRows = ++$rows;
    }

    $gPageContent->addChild($detailsGrid);

    $gPageTitle.= ' - '.$applicationData['appid'].' - '.$gLocale->getStr('applicationdetails_title');
}


$gViewDispatcher->addEvent('dependencies', 'main_dependencies');
function main_dependencies($eventData)
{
    global $gLocale, $gPageTitle, $gXmlDefinition;

    $query = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->execute(
        'SELECT appid FROM applications WHERE id='.$eventData['appid'].' '
    );

    $applicationData = $query->getFields();

    $tempDeps = new \Innomatic\Application\ApplicationDependencies();

    $applicationDeps = array();
    $applicationSuggs = array();
    $dependingMods = array();
    $suggestingMods = array();
    $enabledDomains = array();

    $applicationDepsArray = $tempDeps->dependsOn($applicationData['appid']);
    if (is_array($applicationDepsArray)) {
        while (list ($key, $val) = each($applicationDepsArray)) {
            if ($val['deptype'] == \Innomatic\Application\ApplicationDependencies::TYPE_DEPENDENCY)
                $applicationDeps[$val['moddep']] = $val['moddep'].' '.$val['version'];
            else
                $applicationSuggs[$val['moddep']] = $val['moddep'].' '.$val['version'];
        }
    }

    $dependingModsArray = $tempDeps->checkDependingApplications(
        $applicationData['appid'],
        \Innomatic\Application\ApplicationDependencies::TYPE_DEPENDENCY
    );
    if (is_array($dependingModsArray)) {
        while (list ($key, $val) = each($dependingModsArray)) {
            $dependingMods[$val] = $val;
        }
    }

    $suggestingModsArray = $tempDeps->checkDependingApplications(
        $applicationData['appid'],
        \Innomatic\Application\ApplicationDependencies::TYPE_SUGGESTION
    );
    if (is_array($suggestingModsArray)) {
        while (list ($key, $val) = each($suggestingModsArray)) {
            $suggestingMods[$val] = $val;
        }
    }

    $enabledDomainsArray = $tempDeps->checkEnabledDomains($eventData['appid']);
    if (is_array($enabledDomainsArray)) {
        asort($enabledDomainsArray);

        while (list ($key, $val) = each($enabledDomainsArray)) {
            $enabledDomains[$val] = $val;
        }
    }

    $$xmlDef = '<grid><name>deps</name><children>

      <vertframe row="0" col="0">
        <name>deps</name>
        <children>
          <label>
            <name>deps</name>
              <args>
                <label>'.$gLocale->getStr('appdeps_label').'</label>
              </args>
            </label>
            <listbox>
              <name>deps</name>
              <args>
                <disp>action</disp>
                <readonly>true</readonly>
                <elements type="array">'.WuiXml::encode($applicationDeps).'</elements>
                <size>5</size>
              </args>
            </listbox>
          </children>
        </vertframe>

      <vertframe row="0" col="1"><name>suggs</name><children>
        <label><name>suggs</name><args><label>'.$gLocale->getStr('appsuggs_label').'</label></args></label>
        <listbox><name>suggs</name><args><disp>action</disp><readonly>true</readonly><elements type="array">'
        .WuiXml::encode($applicationSuggs)
        .'</elements><size>5</size></args></listbox>
      </children></vertframe>';

    if (strcmp($applicationData['appid'], 'innomatic')) {
        $$xmlDef.= '  <vertframe row="1" col="0"><name>depending</name><children>
            <label><name>depending</name><args><label>'
            .sprintf($gLocale->getStr('dependingapps_label'), $applicationData['appid'])
            .'</label></args></label>
            <listbox><name>depending</name><args><disp>action</disp><readonly>true</readonly><elements type="array">'
            .WuiXml::encode($dependingMods)
            .'</elements><size>5</size></args></listbox>
          </children></vertframe>

          <vertframe row="1" col="1"><name>suggesting</name><children>
            <label><name>suggesting</name><args><label>'
            .sprintf($gLocale->getStr('suggestingapps_label'), $applicationData['appid'])
            .'</label></args></label>
            <listbox><name>suggesting</name><args><disp>action</disp><readonly>true</readonly><elements type="array">'
            .WuiXml::encode($suggestingMods)
            .'</elements><size>5</size></args></listbox>
          </children></vertframe>

          <vertframe row="2" col="0"><name>enabled</name><children>
            <label><name>enabled</name><args><label>'
            .$gLocale->getStr('enableddomains_label')
            .'</label></args></label>
            <listbox><name>enabled</name><args><disp>action</disp><readonly>true</readonly><elements type="array">'
            .WuiXml::encode($enabledDomains)
            .'</elements><size>5</size></args></listbox>
          </children></vertframe>';
    }

    $$xmlDef.= '</children></grid>';

    $gXmlDefinition = $$xmlDef;

    $gPageTitle.= ' - '.$applicationData['appid'].' - '.$gLocale->getStr('applicationdeps_title');
}

$gViewDispatcher->addEvent('applicationlog', 'main_applicationlog');
function main_applicationlog($eventData)
{
    global $gLocale, $gLocale, $gPageTitle, $wuiMainVertGroup, $gPageContent;

    $query = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->execute(
        'SELECT appid FROM applications WHERE id='.$eventData['appid']
    );

    $applicationData = $query->getFields();

    $gPageContent = new WuiVertgroup('vgroup');

    $appLogContent = '';

    if (
        file_exists(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome()
            .'core/applications/'.$applicationData['appid'].'/application.log'
        )
    ) {
            $logToolbar = new WuiToolBar('logbar');

            $cleanLogAction = new \Innomatic\Wui\Dispatch\WuiEventsCall();
            $cleanLogAction->addEvent(
                new \Innomatic\Wui\Dispatch\WuiEvent(
                    'view',
                    'default',
                    ''
                )
            );
            $cleanLogAction->addEvent(
                new \Innomatic\Wui\Dispatch\WuiEvent(
                    'action',
                    'cleanmodlog',
                    array(
                        'appid' => $applicationData['appid']
                    )
                )
            );
            $cleanLogButton = new WuiButton(
                'cleanlogbutton',
                array(
                    'label' => $gLocale->getStr('cleanlog_button'),
                    'themeimage' => 'documentdelete',
                    'action' => $cleanLogAction->getEventsCallString()
                )
            );

            $logToolbar->addChild($cleanLogButton);
            $logFrame = new WuiHorizframe('logframe');
            $logFrame->addChild($logToolbar);
            $wuiMainVertGroup->addChild($logFrame);

            $appLogContent = file_get_contentes(
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome()
                .'core/applications/'.$applicationData['appid'].'/application.log'
            );
    }

    $wuiVGroup->addChild(
        new WuiText(
            'modlog',
            array(
                'disp' => 'action',
                'readonly' => 'true',
                'value' => \Innomatic\Wui\Wui::utf8_entities($appLogContent),
                'rows' => '20',
                'cols' => '120'
            )
        ),
        0, 1
    );
    $gPageTitle.= ' - '.$applicationData['appid'].' - '.$gLocale->getStr('modlog.title');
}

$gViewDispatcher->addEvent('help', 'main_help');
function main_help($eventData)
{
    global $gPageTitle, $wuiMainFrame, $gLocale, $gPageContent;
    $gPageTitle.= ' - '.$gLocale->getStr('help_title');
    $gPageContent = new WuiHelpNode(
        'applications_help',
        array(
            'base' => 'innomatic',
            'node' => 'innomatic.root.applications.'.$eventData['node'].'.html',
            'language' => \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLanguage()
        )
    );
}


function reps_tab_action_builder($tab)
{
    return \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString(
        '',
        array(
            array(
                'view',
                'appcentral',
                array(
                    'activetab' => $tab
                )
            )
        )
    );
}

$gViewDispatcher->addEvent('appcentral', 'main_appcentral');
function main_appcentral($eventData)
{
    global $gLocale, $gXmlDefinition, $gPageTitle, $gStatus, $gToolbars;

    $repsQuery = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->execute(
        'SELECT
            applications_repositories.id AS id,
            applications_repositories.accountid AS accountid,
            webservices_accounts.name AS name
        FROM
            applications_repositories,webservices_accounts
        WHERE
            applications_repositories.accountid=webservices_accounts.id
        ORDER BY
            name'
    );

        $newRepXml = '';

    $accsQuery = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->execute(
        'SELECT
            id,
             name
        FROM
            webservices_accounts
        WHERE
            webservices_accounts.id NOT IN (SELECT accountid FROM applications_repositories)
        ORDER BY
            name'
    );

    $accounts = array();

    while (!$accsQuery->eof) {
        $accounts[$accsQuery->getFields('id')] = $accsQuery->getFields('name');
        $accsQuery->moveNext();
    }

    if ($accsQuery->getNumberRows()) {
    $newRepXml =
'    <form><name>newrepository</name>
      <args>
        <method>post</method>
        <action type="encoded">'
        .urlencode(
            \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString(
                '',
                array(
                    array(
                        'view',
                        'appcentral',
                        ''
                    ),
                    array(
                        'action',
                        'newrepository',
                        ''
                    )
                )
            )
        )
        .'</action>
      </args>
      <children>
        <grid><name>new</name>
          <children>
            <label row="0" col="0"><name>name</name>
              <args>
                <label type="encoded">'.urlencode($gLocale->getStr('account.label')).'</label>
              </args>
            </label>
            <combobox row="0" col="1"><name>accountid</name>
              <args>
                <disp>action</disp>
                <elements type="array">'.WuiXml::encode($accounts).'</elements>
              </args>
            </combobox>
    <button row="0" col="2"><name>apply</name>
      <args>
        <themeimage>mathadd</themeimage>
        <formsubmit>newrepository</formsubmit>
        <horiz>true</horiz>
        <frame>false</frame>
        <label type="encoded">'.urlencode($gLocale->getStr('new_repository.submit')).'</label>
        <action type="encoded">'
        .urlencode(
            \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString(
                '',
                array(
                    array(
                        'view',
                        'appcentral',
                        ''
                    ),
                    array(
                        'action',
                        'newrepository',
                        ''
                    )
                )
            )
        )
        .'</action>
      </args>
    </button>
          </children>
        </grid>
      </children>
    </form>';
    }

    // Refresh repositories and applications list if requested
    if (isset($eventData['refresh'])) {
        $helper = new \Innomatic\Application\AppCentralHelper();
        $helper->updateApplicationsList();
    }

    if ( $repsQuery->getNumberRows() ) {
        $tabs = array();

        while (!$repsQuery->eof) {
            $tabs[]['label'] = $repsQuery->getFields('name');
            $repsQuery->moveNext();
        }

        $headers[0]['label'] = $gLocale->getStr('repository_name.header');

        $gXmlDefinition =
'<vertgroup><name>reps</name>
  <children>
    <tab><name>repositories</name>
      <args>
        <tabs type="array">'.WuiXml::encode($tabs).'</tabs>
        <tabactionfunction>reps_tab_action_builder</tabactionfunction>
        <activetab>'.(isset($eventData['activetab']) ? $eventData['activetab'] : '').'</activetab>
      </args>
      <children>';

        $repsQuery->MoveFirst();
        while (!$repsQuery->eof) {
            $acRemote = new \Innomatic\Application\AppCentralRemoteServer(
                $repsQuery->getFields('id')
            );
            $availReps = $acRemote->listAvailableRepositories();

            $gXmlDefinition .=
'<vertgroup><name>tab</name><children>
<table><name>reps</name>
  <args>
    <headers type="array">'.WuiXml::encode($headers).'</headers>
  </args>
  <children>';

            $row = 0;

            if (is_array($availReps)) {
                while (list($id, $data) = each($availReps)) {
                    $gXmlDefinition .=
'<label row="'.$row.'" col="0"><name>rep</name>
  <args>
    <label type="encoded">'.urlencode('<strong>'.$data['name'].'</strong><br>'.$data['description']).'</label>
  </args>
</label>
<horizgroup row="'.$row.'" col="1"><name>tb</name>
        <children>
          <button>
            <args>
              <label>'.WuiXml::cdata($gLocale->getStr('repository_applications.button')).'</label>
        <themeimage>listdetailed</themeimage>
        <horiz>true</horiz>
        <action>'.WuiXml::cdata(\Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString(
                        '',
                        array(
                            array(
                                'view',
                                'repositoryapplications',
                                array(
                                    'id' => $repsQuery->getFields('id'),
                                    'repid' => $id
                                )
                            )
                        )
                    )).'</action>
            </args>
          </button>
        </children>
</horizgroup>';
                    $row++;
                }
            }

            $gXmlDefinition .=
'  </children>
</table>
        <horizbar><name>hb</name></horizbar>
         <button><name>remove</name>
           <args>
            <themeimage>trash</themeimage>
            <horiz>true</horiz>
            <frame>false</frame>
            <action type="encoded">'
            .urlencode(
                \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString(
                    '',
                    array(
                        array(
                            'view',
                            'appcentral',
                            ''
                        ),
                        array(
                            'action',
                            'removerepository',
                            array(
                                'id' => $repsQuery->getFields('id')
                            )
                        )
                    )
                )
            )
            .'</action>
            <label type="encoded">'.urlencode($gLocale->getStr('remove_account.button')).'</label>
            <needconfirm>true</needconfirm>
            <confirmmessage type="encoded">'.urlencode($gLocale->getStr('remove_account.confirm')).'</confirmmessage>
           </args>
         </button>
</children></vertgroup>';

            $repsQuery->moveNext();
        }

      $gXmlDefinition .=
'
      </children>
    </tab>';

if (strlen($newRepXml)) {
    $gXmlDefinition .= '<horizbar><name>hb</name></horizbar>'.$newRepXml;
}
$gXmlDefinition .=
'  </children>
</vertgroup>';

                $gToolbars['reptools'] = array(
                    'refresh' => array(
                        'label' => $gLocale->getStr('refresh.button'),
                        'themeimage' => 'cycle',
                        'horiz' => 'true',
                        'action' => \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString(
                            '',
                            array(
                                array(
                                    'view',
                                    'appcentral',
                                    array(
                                        'refresh' => '1'
                                    )
                                )
                            )
                        )
                    )
                );

    } else {
        if (strlen($newRepXml)) {
            $gXmlDefinition .= $newRepXml;
        } else {
            $gXmlDefinition .= '<vertgroup><name>new</name>
        <children>
        <button><name>addaccount</name>
           <args>
             <themeimage>globe2</themeimage>
             <horiz>true</horiz>
             <frame>false</frame>
             <action type="encoded">'.urlencode('http://www.appcentral.it/').'</action>
             <label type="encoded">'.urlencode($gLocale->getStr('new_appcentral_account.button')).'</label>
           </args>
         </button>
        <button><name>addaccount2</name>
           <args>
            <themeimage>mathadd</themeimage>
            <horiz>true</horiz>
            <frame>false</frame>
            <action type="encoded">'
            .urlencode(
                \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString(
                    'webservices',
                    array(
                        array(
                            'view',
                            'newaccount',
                            ''
                        )
                    )
                )
            )
            .'</action>
            <label type="encoded">'.urlencode($gLocale->getStr('new_webservices_account.button')).'</label>
           </args>
         </button>
    </children>
    </vertgroup>';
        }
        if (!strlen($gStatus)) $gStatus = $gLocale->getStr('no_repositories.status');
    }

    $gPageTitle .= ' - '.$gLocale->getStr('repositories.title');
}

function repapplications_list_action_builder($pageNumber)
{
    $tempDisp = new WuiDispatcher('view');
    $eventData = $tempDisp->GetEventData();

    return \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString(
        '',
        array(
            array(
                'view',
                'repositoryapplications',
                array(
                    'id' => $eventData['id'],
                    'repid' => $eventData['repid'],
                    'pagenumber' => $pageNumber,
                    'tab' => isset($eventData['tab'] ) ? $eventData['tab'] : ''
                )
            )
        )
    );
}

function appcentral_applications_tab_action_builder($tab)
{
    $gMainDisp = new WuiDispatcher('view');
    $eventData = $gMainDisp->GetEventData();

    return \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString(
        '',
        array(
            array(
                'view',
                'repositoryapplications',
                array(
                    'id' => $eventData['id'],
                    'repid' => $eventData['repid'],
                    'tab' => $tab
                )
            )
        )
    );
}

$gViewDispatcher->addEvent('repositoryapplications', 'main_repositoryapplications');
function main_repositoryapplications($eventData)
{
    global $gLocale, $gXmlDefinition, $gPageTitle, $gToolbars;

    $acRemote = new \Innomatic\Application\AppCentralRemoteServer(
        $eventData['id']
    );

    $availReps = $acRemote->ListAvailableRepositories(
        isset($eventData['refresh'] ) ? true : false
    );

    $availModsList = $acRemote->ListAvailableApplications(
        $eventData['repid'],
        isset($eventData['refresh'] ) ? true : false
    );

    $availModsSortedList = array();
    $tabs = array();

    if (is_array($availModsList)) {
        foreach ($availModsList as $id => $data) {
            $availModsSortedList[$data['category'] ? $data['category'] : 'various'][$id] = $data;
        }
    }

    ksort($availModsSortedList);

    foreach ($availModsSortedList as $category => $data) {
        $tabs[]['label'] = ucfirst($category ? $category : 'various');
    }
    reset($availModsSortedList);

    $xAccount = new WebServicesAccount(
        $acRemote->mAccountId
    );

    $headers[0]['label'] = $gLocale->getStr('application.header');
    $headers[1]['label'] = $gLocale->getStr('lastversion.header');
    $headers[2]['label'] = $gLocale->getStr('dependencies.header');
    $headers[3]['label'] = $gLocale->getStr('installed_version.header');

    $gXmlDefinition =
'<vertgroup><name>applications</name>
  <children>
    <label><name>title</name>
      <args>
        <bold>true</bold>
        <label type="encoded">'.urlencode($xAccount->mName.' - '.$availReps[$eventData['repid']]['name']).'</label>
      </args>
    </label>
    <tab><name>'.$eventData['repid'].'repapplications</name>
      <args>
        <tabactionfunction>appcentral_applications_tab_action_builder</tabactionfunction>
        <tabs type="array">'.WuiXml::encode($tabs).'</tabs>
        <activetab>'.(isset($eventData['tab']) ? $eventData['tab'] : '').'</activetab>
      </args>
      <children>';

    foreach ($availModsSortedList as $availMods) {
    $gXmlDefinition .=
'    <table><name>applications</name>
      <args>
        <headers type="array">'.WuiXml::encode($headers).'</headers>
        <rowsperpage>10</rowsperpage>
        <pagesactionfunction>repapplications_list_action_builder</pagesactionfunction>
        <pagenumber>'.(isset($eventData['pagenumber']) ? $eventData['pagenumber'] : '').'</pagenumber>
        <sessionobjectusername>'.
            $eventData['id'].'-'.
            $eventData['repid'].'-'.
            (isset($eventData['tab']) ? $eventData['tab'] : '').
        '</sessionobjectusername>
      </args>
      <children>';

    $row = 0;

    while (list($id, $data) = each($availMods)) {
        $appQuery = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->Execute(
            'SELECT appversion '.
            'FROM applications '.
            'WHERE appid='
            .\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->formatText($data['appid'])
        );

        if (
            strlen($data['dependencies'])
        ) {
            $appDeps = new \Innomatic\Application\ApplicationDependencies();
            $depCheck = $appDeps->checkApplicationDependencies(
                0,
                '',
                $appDeps->explodeDependencies($data['dependencies'])
            );
        } else {
            $depCheck = false;
        }

        if ($appQuery->getNumberRows()) $currentVersion = $appQuery->getFields('appversion');
        else $currentVersion = $gLocale->getStr('none_version.label');

        if (
            $depCheck == false
        ) {
            $appInstallable = true;
            $missingDeps = '';

            if (
                $appQuery->getNumberRows()
            ) {
                switch (
                    \Innomatic\Application\ApplicationDependencies::compareVersionNumbers(
                        $data['lastversion'],
                        $currentVersion
                    )
                )
                {
                case \Innomatic\Application\ApplicationDependencies::VERSIONCOMPARE_EQUAL:
                    $label = $gLocale->getStr('reinstall_application.button');
                    $icon = 'cycle';
                    break;

                case \Innomatic\Application\ApplicationDependencies::VERSIONCOMPARE_MORE:
                    $label = $gLocale->getStr('update_application.button');
                    $icon = 'up';
                    break;

                case \Innomatic\Application\ApplicationDependencies::VERSIONCOMPARE_LESS:
                    $label = $gLocale->getStr('downgrade_application.button');
                    $icon = 'down';
                    break;
                }
            } else {
                $label = $gLocale->getStr('install_application.button');
                $icon = 'mathadd';
            }
        } else {
            $appInstallable = false;

            $missingDeps = '<br><strong>'.$gLocale->getStr('missing_deps.label').'</strong>';

            while ( list(, $dep) = each($depCheck) ) {
                $missingDeps .= '<br>'.$dep;
            }
        }

        $toolbars = '';

        $toolbars .= '<button>
                    <args>
              <label>'.WuiXml::cdata($gLocale->getStr('application_versions.button')).'</label>
        <themeimage>listdetailed</themeimage>
        <horiz>true</horiz>
        <action>'.WuiXml::cdata(\Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString(
                '',
                array(
                    array(
                        'view',
                        'applicationversions',
                        array(
                            'id' => $eventData['id'],
                            'repid' => $eventData['repid'],
                            'applicationid' => $id
                        )
                    )
                )
            )).'</action>
                  </args>
                </button>';


        if (
            $appInstallable
        ) {
            $toolbars .= '<button>
                    <args>
              <label>'.WuiXml::cdata($label).'</label>
        <themeimage>'.$icon.'</themeimage>
        <horiz>true</horiz>
        <action>'.WuiXml::cdata(\Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString(
                    '',
                    array(
                        array(
                            'view',
                            'repositoryapplications',
                            array(
                                'id' => $eventData['id'],
                                'repid' => $eventData['repid']
                            )
                        ),
                        array(
                            'action',
                            'installapplication',
                            array(
                                'id' => $eventData['id'],
                                'repid' => $eventData['repid'],
                                'applicationid' => $id
                            )
                        )
                    )
                )).'</action>
                  </args>
                </button>';
        }

        $gXmlDefinition .=
'<label row="'.$row.'" col="0"><name>application</name>
  <args>
    <label type="encoded">'.urlencode('<strong>'.$data['appid'].'</strong><br>'.$data['description']).'</label>
  </args>
</label>

<label row="'.$row.'" col="1"><name>lastversion</name>
  <args>
    <label type="encoded">'.urlencode($data['lastversion'].'<br>('.$data['date'].')').'</label>
  </args>
</label>

<label row="'.$row.'" col="2"><name>dependencies</name>
  <args>
    <label type="encoded">'
    .urlencode(
        str_replace(
            ',',
            '<br>',
            $data['dependencies']
        ).(strlen($data['suggestions']) ? '<br><br><strong>'.$gLocale->getStr('suggestions.label')
        .'</strong><br>'.str_replace(',', '<br>', $data['suggestions']).'<br>' : '').$missingDeps
    ).'</label>
  </args>
</label>

<label row="'.$row.'" col="3"><name>current</name>
  <args>
    <label type="encoded">'.urlencode($currentVersion).'</label>
  </args>
</label>

<horizgroup row="'.$row.'" col="4"><name>tb</name>
  <children>'.$toolbars.'</children>
</horizgroup>';
        $row++;
    }

    $gXmlDefinition .=
'      </children>
    </table>';

    }

    $gXmlDefinition .=
'      </children>
    </tab>
  </children>
</vertgroup>';

                $gToolbars['reptools'] = array(
                    'refresh' => array(
                        'label' => $gLocale->getStr('refresh.button'),
                        'themeimage' => 'cycle',
                        'horiz' => 'true',
                        'action' => \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString(
                            '',
                            array(
                                array(
                                    'view',
                                    'repositoryapplications',
                                    array(
                                        'id' => $eventData['id'],
                                        'repid' => $eventData['repid'],
                                        'refresh' => '1'
                                    )
                                )
                            )
                        )
                    )
                );

    $gPageTitle .= ' - '.$gLocale->getStr('repositoryapplications.title');
}

$gViewDispatcher->addEvent('applicationversions', 'main_applicationversions');
function main_applicationversions($eventData)
{
    global $gLocale, $gXmlDefinition, $gPageTitle, $gToolbars;

    $acRemote = new \Innomatic\Application\AppCentralRemoteServer(
        $eventData['id']
    );

    $availReps = $acRemote->ListAvailableRepositories(
        isset($eventData['refresh']) ? true : false
    );

    $availMods = $acRemote->ListAvailableApplications(
        $eventData['repid'],
        isset($eventData['refresh'] ) ? true : false
    );

    $appVersions = $acRemote->ListAvailableApplicationVersions(
        $eventData['repid'],
        $eventData['applicationid'],
        isset($eventData['refresh'] ) ? true : false
    );


    $xAccount = new WebServicesAccount(
        $acRemote->mAccountId
    );

    $headers[0]['label'] = $gLocale->getStr('version.header');
    $headers[1]['label'] = $gLocale->getStr('dependencies.header');
    $headers[2]['label'] = $gLocale->getStr('installed_version.header');

    $gXmlDefinition =
'<vertgroup><name>applications</name>
  <children>
    <label><name>title</name>
      <args>
        <bold>true</bold>
        <label type="encoded">'
        .urlencode(
            $xAccount->mName.' - '.$availReps[$eventData['repid']]['name'].' - '
            .$availMods[$eventData['applicationid']]['appid']
        ).'</label>
      </args>
    </label>
    <table><name>applications</name>
      <args>
        <headers type="array">'.WuiXml::encode($headers).'</headers>
        <rowsperpage>10</rowsperpage>
        <pagesactionfunction>repapplications_list_action_builder</pagesactionfunction>
        <pagenumber>'.(isset($eventData['pagenumber']) ? $eventData['pagenumber'] : '').'</pagenumber>
        <sessionobjectusername>'.$eventData['id'].'-'
        .$eventData['repid'].'-'.$eventData['applicationid'].'</sessionobjectusername>
      </args>
      <children>';

    $row = 0;

    $appQuery = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->execute(
        'SELECT appversion '.
        'FROM applications '.
        'WHERE appid='
        .\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->formatText(
            $availMods[$eventData['applicationid']]['appid']
        )
    );

    while ( list($version, $data) = each($appVersions) ) {
        if (
            strlen($data['dependencies'])
        ) {
            $appDeps = new \Innomatic\Application\ApplicationDependencies();
            $depCheck = $appDeps->checkApplicationDependencies(
                0,
                '',
                $appDeps->explodeDependencies($data['dependencies'])
            );
        } else {
            $depCheck = false;
        }

        if ($appQuery->getNumberRows()) $currentVersion = $appQuery->getFields('appversion');
        else $currentVersion = $gLocale->getStr('none_version.label');

        if (
            $depCheck == false
        ) {
            $appInstallable = true;
            $missingDeps = '';

            if (
                $appQuery->getNumberRows()
            ) {
                switch (
                    \Innomatic\Application\ApplicationDependencies::compareVersionNumbers(
                        $version,
                        $currentVersion
                    )
                ) {
                case \Innomatic\Application\ApplicationDependencies::VERSIONCOMPARE_EQUAL:
                    $label = $gLocale->getStr('reinstall_application.button');
                    $icon = 'cycle';
                    break;

                case \Innomatic\Application\ApplicationDependencies::VERSIONCOMPARE_MORE:
                    $label = $gLocale->getStr('update_application.button');
                    $icon = 'up';
                    break;

                case \Innomatic\Application\ApplicationDependencies::VERSIONCOMPARE_LESS:
                    $label = $gLocale->getStr('downgrade_application.button');
                    $icon = 'down';
                    break;
                }
            } else {
                $label = $gLocale->getStr('install_application.button');
                $icon = 'folder';
            }
        } else {
            $appInstallable = false;

            $missingDeps = '<br><strong>'.$gLocale->getStr('missing_deps.label').'</strong>';

            while ( list(, $dep) = each($depCheck) ) {
                $missingDeps .= '<br>'.$dep;
            }
        }

        $toolbars = '';

        if ( $appInstallable ) {
            $toolbars .= '<button>
                    <args>
              <label>'.WuiXml::cdata($label).'</label>
        <themeimage>'.$icon.'</themeimage>
        <horiz>true</horiz>
        <action>'.WuiXml::cdata(\Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString(
                    '',
                    array(
                        array(
                            'view',
                            'repositoryapplications',
                            array(
                                'id' => $eventData['id'],
                                'repid' => $eventData['repid']
                            )
                        ),
                        array(
                            'action',
                            'installapplication',
                            array(
                                'id' => $eventData['id'],
                                'repid' => $eventData['repid'],
                                'applicationid' => $eventData['applicationid'],
                                'version' => $version
                            )
                        )
                    )
                )).'</action>
                  </args>
                </button>';
        }

        $gXmlDefinition .=
'<label row="'.$row.'" col="0"><name>version</name>
  <args>
    <label type="encoded">'.urlencode($version).'</label>
  </args>
</label>

<label row="'.$row.'" col="1"><name>dependencies</name>
  <args>
    <label type="encoded">'
    .urlencode(
        str_replace(
            ',',
            '<br>',
            $data['dependencies']
        )
        .(strlen($data['suggestions']) ? '<br><br><strong>'.$gLocale->getStr('suggestions.label')
     .'</strong><br>'.str_replace(',', '<br>', $data['suggestions']).'<br>' : '').$missingDeps
    ).'</label>
  </args>
</label>

<label row="'.$row.'" col="2"><name>current</name>
  <args>
    <label type="encoded">'.urlencode($currentVersion).'</label>
  </args>
</label>

<horizgroup row="'.$row.'" col="3"><name>tb</name>
    <children>'.$toolbars.'</children>
</horizgroup>';
        $row++;
    }

    $gXmlDefinition .=
'      </children>
    </table>
  </children>
</vertgroup>';

    $gToolbars['reptools'] = array(
        'refresh' => array(
            'label' => $gLocale->getStr('refresh.button'),
            'themeimage' => 'cycle',
            'horiz' => 'true',
            'action' => \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString(
                '',
                array(
                    array(
                        'view',
                        'applicationversions',
                        array(
                            'id' => $eventData['id'],
                            'repid' => $eventData['repid'],
                            'applicationid' => $eventData['applicationid'],
                            'refresh' => '1'
                        )
                    )
                )
            )
        )
    );

    $gPageTitle .= ' - '.$gLocale->getStr('applicationversions.title');
}

function keys_page_action_builder($page)
{
    return \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString(
        '',
        array(
            array(
                'view',
                'keyring',
                array(
                    'page' => $page
                )
            )
        )
    );
}

$gViewDispatcher->addEvent(
    'keyring',
    'main_keyring'
);
function main_keyring($eventData)
{
    global $gXmlDefinition, $gLocale, $gPageTitle, $gStatus;

    $query = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->Execute(
        'SELECT * '.
        'FROM applications_keyring_keys '.
        'ORDER BY application,version,domain'
    );

$gXmlDefinition .= '<vertgroup><name>vg</name><children>';

    if (
        $query->getNumberRows()
    ) {
        $headers[0]['label'] = $gLocale->getStr('application.header');
        $headers[1]['label'] = $gLocale->getStr('version.header');
        $headers[2]['label'] = $gLocale->getStr('domain.header');
        $headers[3]['label'] = $gLocale->getStr('maxdomainusers.header');
        $headers[4]['label'] = $gLocale->getStr('validip.header');
        $headers[5]['label'] = $gLocale->getStr('validrange.header');
        $headers[6]['label'] = $gLocale->getStr('expirydate.header');

        $gXmlDefinition .=
'<table>
  <args>
    <headers type="array">'.WuiXml::encode($headers).'</headers>
    <rowsperpage>20</rowsperpage>
    <pagesactionfunction>keys_page_action_builder</pagesactionfunction>
    <pagenumber>'.(isset($eventData['page']) ? $eventData['page'] : '').'</pagenumber>
  </args>
  <children>';

        $row = 0;

        while (!$query->eof) {
            $toolbars = '<button>
                    <args>
              <label>'.WuiXml::cdata($gLocale->getStr('removekey.button')).'</label>
        <themeimage>trash</themeimage>
        <horiz>true</horiz>
                <needconfirm>true</needconfirm>
                    <confirmmessage>'.WuiXml::cdata($gLocale->getStr('remove_key.confirm')).'</confirmmessage>
        <action>'.WuiXml::cdata(\Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString(
                        '',
                        array(
                            array(
                                'view',
                                'keyring'
                            ),
                            array(
                                'action',
                                'removekey',
                                array(
                                    'id' => $query->getFields('id')
                                )
                            )
                        )
                    )).'</action>
                  </args>
                </button>';

            $gXmlDefinition .=
'<label row="'.$row.'" col="0">
  <args>
    <label type="encoded">'.urlencode($query->getFields('application')).'</label>
  </args>
</label>
<label row="'.$row.'" col="1">
  <args>
    <label type="encoded">'.urlencode($query->getFields('version')).'</label>
  </args>
</label>
<label row="'.$row.'" col="2">
  <args>
    <label type="encoded">'.urlencode($query->getFields('domain')).'</label>
  </args>
</label>
<label row="'.$row.'" col="3">
  <args>
    <label type="encoded">'.urlencode($query->getFields('maxdomainusers')).'</label>
  </args>
</label>
<label row="'.$row.'" col="4">
  <args>
    <label type="encoded">'.urlencode($query->getFields('validip')).'</label>
  </args>
</label>
<label row="'.$row.'" col="5">
  <args>
    <label type="encoded">'.urlencode($query->getFields('validrange')).'</label>
  </args>
</label>
<label row="'.$row.'" col="6">
  <args>
    <label type="encoded">'.urlencode($query->getFields('expirydate')).'</label>
  </args>
</label>
<horizgroup row="'.$row.'" col="7">
    <children>'.$toolbars.'</children>
</horizgroup>';

            $query->moveNext();
            $row++;
        }

        $gXmlDefinition .=
'  </children>
</table>

<horizbar><name>hb</name></horizbar>';
    } else {
        if (!strlen($gStatus)) $gStatus = $gLocale->getStr('nokeys.status');
    }

    $gXmlDefinition .=
'<vertgroup>
  <children>

    <form><name>newkey</name>
      <args>
        <action type="encoded">'
        .urlencode(
            \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString(
                '',
                array(
                    array(
                        'view',
                        'keyring'
                    ),
                    array(
                        'action',
                        'newkey'
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
                <label type="encoded">'.urlencode($gLocale->getStr('key.label')).'</label>
              </args>
            </label>

            <file row="0" col="1"><name>key</name>
              <args>
                <disp>action</disp>
              </args>
            </file>

    <button row="0" col="2">
      <args>
        <horiz>true</horiz>
        <frame>false</frame>
        <themeimage>mathadd</themeimage>
        <formsubmit>newkey</formsubmit>
        <label type="encoded">'.urlencode($gLocale->getStr('newkey.submit')).'</label>
        <action type="encoded">'
        .urlencode(
            \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString(
                '',
                array(
                    array(
                        'view',
                        'keyring'
                    ),
                    array(
                        'action',
                        'newkey'
                    )
                )
            )
        ).'</action>
      </args>
    </button>

          </children>
        </grid>

      </children>
    </form>

  </children>
</vertgroup>';

$gXmlDefinition .= '</children></vertgroup>';

    $gPageTitle .= ' - '.$gLocale->getStr('keys.title');
}


$gViewDispatcher->addEvent('about', 'main_about');
function main_about($eventData)
{
    global $gLocale, $gPageTitle, $gPageContent;

    $$xmlDef = '<vertgroup>
          <args>
            <groupalign>center</groupalign>
            <align>center</align>
          </args>
          <children>

            <image>
              <args>
                <imageurl type="encoded">'
                .urlencode(
                    \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getBaseUrl(false)
                    .'/shared/innomatic_logo.png'
                ).'</imageurl>
                <width>310</width>
                <height>34</height>
              </args>
            </image>

            <label>
              <args>
                <label type="encoded">'.urlencode($gLocale->getStr('innomatic_copyright.label')).'</label>
              </args>
            </label>

            <link>
              <args>
                <label type="encoded">'.urlencode('www.innomatic.io').'</label>
                <link type="encoded">'.urlencode('http://www.innomatic.io').'</link>
                <target>_blank</target>
              </args>
            </link>

          </children>
        </vertgroup>';

    $gPageContent = new WuiXml('page', array('definition' => $$xmlDef));
}

$gViewDispatcher->Dispatch();

// Page render
//
if (
    strlen($gXmlDefinition)
) {
    $gPageContent = new WuiXml('page', array('definition' => $gXmlDefinition));
}

$wui->addChild(
    new WuiInnomaticPage(
        'page',
        array(
            'pagetitle' => $gPageTitle,
            'icon' => 'listicons',
            'toolbars' => array(
                new WuiInnomaticToolbar(
                    'view',
                    array(
                        'toolbars' => $gToolbars, 'toolbar' => 'true'
                    )
                )
            ),
            'maincontent' => $gPageContent,
            'status' => $gStatus
        )
    )
);

$wui->render();
