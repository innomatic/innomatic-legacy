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

// NOTE: This is an old-style panel code with a single file
// acting as model, view and controller.

require_once('innomatic/logging/Logger.php');
require_once('innomatic/locale/LocaleCatalog.php');
require_once('innomatic/wui/Wui.php');
require_once('innomatic/webservices/WebServicesAccount.php');
require_once('innomatic/webservices/WebServicesProfile.php');
require_once('innomatic/webservices/WebServicesUser.php');
require_once('innomatic/webservices/xmlrpc/XmlRpc_Client.php');

global $innomaticLocale, $wuiMainFrame, $wuiTitleBar, $wuiMainStatus;

$log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
$innomaticLocale = new LocaleCatalog(
    'innomatic::root_webservices',
    InnomaticContainer::instance('innomaticcontainer')->getLanguage()
);
$wui = Wui::instance('wui');
$wui->loadWidget('button');
$wui->loadWidget('checkbox');
$wui->loadWidget('combobox');
$wui->loadWidget('date');
$wui->loadWidget('empty');
$wui->loadWidget('file');
$wui->loadWidget('formarg');
$wui->loadWidget('form');
$wui->loadWidget('grid');
$wui->loadWidget('helpnode');
$wui->loadWidget('horizbar');
$wui->loadWidget('horizframe');
$wui->loadWidget('horizgroup');
$wui->loadWidget('image');
$wui->loadWidget('label');
$wui->loadWidget('link');
$wui->loadWidget('listbox');
$wui->loadWidget('menu');
$wui->loadWidget('page');
$wui->loadWidget('progressbar');
$wui->loadWidget('radio');
$wui->loadWidget('sessionkey');
$wui->loadWidget('statusbar');
$wui->loadWidget('string');
$wui->loadWidget('submit');
$wui->loadWidget('tab');
$wui->loadWidget('table');
$wui->loadWidget('text');
$wui->loadWidget('titlebar');
$wui->loadWidget('toolbar');
$wui->loadWidget('treemenu');
$wui->loadWidget('vertframe');
$wui->loadWidget('vertgroup');
$wui->loadWidget('xml');

$wuiPage = new WuiPage('page', array('title' => $innomaticLocale->getStr('xmlrpc_title')));
$wuiMainVertGroup = new WuiVertGroup('mainvertgroup');
$wuiTitleBar = new WuiTitleBar(
    'titlebar',
    array(
        'title' => $innomaticLocale->getStr('xmlrpc_title'),
        'icon' => 'globe2'
    )
);
$wuiMainVertGroup->addChild($wuiTitleBar);

$menuFrame = new WuiHorizGroup('menuframe');
$menuFrame->addChild(
    new WuiMenu(
        'mainmenu',
        array(
            'menu' => InnomaticContainer::getRootWuiMenuDefinition(
                InnomaticContainer::instance('innomaticcontainer')->getLanguage()
            )
        )
    )
);
$wuiMainVertGroup->addChild($menuFrame);

// Profiles bar
//
$wuiProfilesToolBar = new WuiToolBar('profilestoolbar');

$homeAction = new WuiEventsCall();
$homeAction->addEvent(new WuiEvent('view', 'default', ''));
$wuiHomeButton = new WuiButton(
    'homebutton',
    array(
        'label' => $innomaticLocale->getStr('profiles_button'),
        'themeimage' => 'user',
        'horiz' => 'true',
        'action' => $homeAction->getEventsCallString()
    )
);
$wuiProfilesToolBar->addChild($wuiHomeButton);

$newProfileAction = new WuiEventsCall();
$newProfileAction->addEvent(new WuiEvent('view', 'newprofile', ''));
$wuiNewProfileButton = new WuiButton(
    'newprofilebutton',
    array(
        'label' => $innomaticLocale->getStr('newprofile_button'),
        'themeimage' => 'useradd',
        'horiz' => 'true',
        'action' => $newProfileAction->getEventsCallString()
    )
);
$wuiProfilesToolBar->addChild($wuiNewProfileButton);

// Users bar
//
$wuiUsersToolBar = new WuiToolBar('userstoolbar');

$usersAction = new WuiEventsCall();
$usersAction->addEvent(new WuiEvent('view', 'users', ''));
$wuiUsersButton = new WuiButton(
    'usersbutton',
    array(
        'label' => $innomaticLocale->getStr('users_button'),
        'themeimage' => 'user',
        'horiz' => 'true',
        'action' => $usersAction->getEventsCallString()
    )
);
$wuiUsersToolBar->addChild($wuiUsersButton);

$newUserAction = new WuiEventsCall();
$newUserAction->addEvent(new WuiEvent('view', 'newuser', ''));
$wuiNewUserButton = new WuiButton(
    'newuserbutton',
    array(
        'label' => $innomaticLocale->getStr('newuser_button'),
        'themeimage' => 'useradd',
        'horiz' => 'true',
        'action' => $newUserAction->getEventsCallString()
    )
);
$wuiUsersToolBar->addChild($wuiNewUserButton);

// Accounts tool bar
//
$wuiAccountsToolBar = new WuiToolBar('accountstoolbar');

$accountsAction = new WuiEventsCall();
$accountsAction->addEvent(new WuiEvent('view', 'accounts', ''));
$wuiAccountsButton = new WuiButton(
    'accountsbutton',
    array(
        'label' => $innomaticLocale->getStr('accounts_button'),
        'themeimage' => 'globe2',
        'horiz' => 'true',
        'action' => $accountsAction->getEventsCallString()
    )
);
$wuiAccountsToolBar->addChild($wuiAccountsButton);

$newAccountAction = new WuiEventsCall();
$newAccountAction->addEvent(
    new WuiEvent(
        'view',
        'newaccount',
        ''
    )
);
$wuiNewAccountButton = new WuiButton(
    'newaccountbutton',
    array(
        'label' => $innomaticLocale->getStr('newaccount_button'),
        'themeimage' => 'mathadd',
        'horiz' => 'true',
        'action' => $newAccountAction->getEventsCallString()
    )
);
$wuiAccountsToolBar->addChild($wuiNewAccountButton);

// Help tool bar
//
$wuiHelpToolBar = new WuiToolBar('helpbar');

$viewDispatcher = new WuiDispatcher('view');
$eventName = $viewDispatcher->getEventName();

if (strcmp($eventName, 'help')) {
    $helpAction = new WuiEventsCall();
    $helpAction->addEvent(new WuiEvent('view', 'help', array('node' => $eventName)));
    $wuiHelpButton = new WuiButton(
        'helpbutton',
        array(
            'label' => $innomaticLocale->getStr('help_button'),
            'themeimage' => 'info',
            'horiz' => 'true',
            'action' => $helpAction->getEventsCallString()
        )
    );

    $wuiHelpToolBar->addChild($wuiHelpButton);
}

// Toolbar frame
//
$wuiToolBarFrame = new WuiHorizGroup('toolbarframe');

$wuiToolBarFrame->addChild($wuiProfilesToolBar);
$wuiToolBarFrame->addChild($wuiUsersToolBar);
$wuiToolBarFrame->addChild($wuiAccountsToolBar);
$wuiToolBarFrame->addChild($wuiHelpToolBar);
$wuiMainVertGroup->addChild($wuiToolBarFrame);

$wuiMainFrame = new WuiHorizFrame('mainframe');
$wuiMainStatus = new WuiStatusBar('mainstatusbar');

// Pass dispatcher
//
$actionDispatcher = new WuiDispatcher('action');

$actionDispatcher->addEvent('adduser', 'pass_adduser');
function pass_adduser($eventData)
{
    global $wuiMainStatus, $innomaticLocale;

    $result = false;

    $xuser = new WebServicesUser(
        InnomaticContainer::instance('innomaticcontainer')->getDataAccess()
    );
    $result = $xuser->Add(
        $eventData['username'], $eventData['password'], $eventData['profileid'], $eventData['domainid']
    );

    if ($result) {
        $wuiMainStatus->mArgs['status'] = $innomaticLocale->getStr('adduserok_status');
    }
}

$actionDispatcher->addEvent('removeuser', 'pass_removeuser');
function pass_removeuser($eventData)
{
    global $wuiMainStatus, $innomaticLocale;

    $result = false;

    if (!empty($eventData['userid'])) {
        $xuser = new WebServicesUser(
            InnomaticContainer::instance('innomaticcontainer')->getDataAccess(), $eventData['userid']
        );
        $result = $xuser->Remove();
    }

    if ($result) {
        $wuiMainStatus->mArgs['status'] = $innomaticLocale->getStr('remuserok_status');
    }
}

$actionDispatcher->addEvent('chpasswd', 'pass_chpasswd');
function pass_chpasswd($eventData)
{
    global $wuiMainStatus, $innomaticLocale;

    $result = false;

    if (!empty($eventData['userid'])) {
        if (strlen($eventData['password'])) {
            $xuser = new WebServicesUser(
                InnomaticContainer::instance('innomaticcontainer')->getDataAccess(), $eventData['userid']
            );
            $result = $xuser->ChangePassword($eventData['password']);
        }
    }

    if ($result) {
        $wuiMainStatus->mArgs['status'] = $innomaticLocale->getStr('chpasswdok_status');
    }
}

$actionDispatcher->addEvent('assignprofile', 'pass_assignprofile');
function pass_assignprofile($eventData)
{
    global $wuiMainStatus, $innomaticLocale;

    $result = false;

    if (!empty($eventData['userid'])) {
        $xuser = new WebServicesUser(
            InnomaticContainer::instance('innomaticcontainer')->getDataAccess(), $eventData['userid']
        );
        $result = $xuser->AssignProfile($eventData['profileid']);
    }

    if ($result) {
        $wuiMainStatus->mArgs['status'] = $innomaticLocale->getStr('chprofileok_status');
    }
}

$actionDispatcher->addEvent('assigndomain', 'pass_assigndomain');
function pass_assigndomain($eventData)
{
    global $wuiMainStatus, $innomaticLocale;

    $result = false;

    if (!empty($eventData['userid'])) {
        $xuser = new WebServicesUser(
            InnomaticContainer::instance('innomaticcontainer')->getDataAccess(), $eventData['userid']
        );
        $result = $xuser->assignDomain($eventData['domainid']);
    }

    if ($result) {
        $wuiMainStatus->mArgs['status'] = $innomaticLocale->getStr('chdomainok_status');
    }
}

$actionDispatcher->addEvent('newprofile', 'pass_newprofile');
function pass_newprofile($eventData)
{
    global $wuiMainStatus, $innomaticLocale;

    $result = false;

    if (!empty($eventData['profilename'])) {
        $xprofile = new WebServicesProfile(InnomaticContainer::instance('innomaticcontainer')->getDataAccess());
        $result = $xprofile->Add($eventData['profilename']);
    }

    if ($result) {
        $wuiMainStatus->mArgs['status'] = $innomaticLocale->getStr('newprofileok_status');
    }
}

$actionDispatcher->addEvent('remprofile', 'pass_remprofile');
function pass_remprofile($eventData)
{
    global $wuiMainStatus, $innomaticLocale;

    $result = false;

    if (!empty($eventData['profileid'])) {
        $xprofile = new WebServicesProfile(
            InnomaticContainer::instance('innomaticcontainer')->getDataAccess(), $eventData['profileid']
        );
        $result = $xprofile->Remove();
    }

    if ($result) {
        $wuiMainStatus->mArgs['status'] = $innomaticLocale->getStr('remprofileok_status');
    }
}

$actionDispatcher->addEvent('renprofile', 'pass_renprofile');
function pass_renprofile($eventData)
{
    global $wuiMainStatus, $innomaticLocale;

    $result = false;

    if (!empty($eventData['profileid']) and !empty($eventData['profilename'])) {
        $xprofile = new WebServicesProfile(
            InnomaticContainer::instance('innomaticcontainer')->getDataAccess(), $eventData['profileid']
        );
        $result = $xprofile->Rename($eventData['profilename']);
    }

    if ($result) {
        $wuiMainStatus->mArgs['status'] = $innomaticLocale->getStr('renprofileok_status');
    }
}

$actionDispatcher->addEvent('enablenode', 'pass_enablenode');
function pass_enablenode($eventData)
{
    global $wuiMainStatus, $innomaticLocale;

    $result = false;

    if (!empty($eventData['profileid']) and strlen($eventData['nodetype']) and !empty($eventData['application'])) {
        $xprofile = new WebServicesProfile(
            InnomaticContainer::instance('innomaticcontainer')->getDataAccess(), $eventData['profileid']
        );
        $result = $xprofile->EnableNode(
            $eventData['nodetype'], $eventData['application'], isset($eventData['method']) ? $eventData['method'] : ''
        );
    }

    if ($result) {
        $wuiMainStatus->mArgs['status'] = $innomaticLocale->getStr('nodeenabledok_status');
    }
}

$actionDispatcher->addEvent('disablenode', 'pass_disablenode');
function pass_disablenode($eventData)
{
    global $wuiMainStatus, $innomaticLocale;

    $result = false;

    if (!empty($eventData['profileid']) and strlen($eventData['nodetype']) and !empty($eventData['application'])) {
        $xprofile = new WebServicesProfile(
            InnomaticContainer::instance('innomaticcontainer')->getDataAccess(), $eventData['profileid']
        );
        $result = $xprofile->DisableNode($eventData['nodetype'], $eventData['application'], $eventData['method']);
    }

    if ($result) {
        $wuiMainStatus->mArgs['status'] = $innomaticLocale->getStr('nodedisabledok_status');
    }
}

$actionDispatcher->addEvent('createaccount', 'pass_createaccount');
function pass_createaccount($eventData)
{
    global $wuiMainStatus, $innomaticLocale;

    $acc = new WebServicesAccount(InnomaticContainer::instance('innomaticcontainer')->getDataAccess());
    if (
        $acc->Create(
            $eventData['name'],
            $eventData['host'],
            $eventData['port'],
            $eventData['path'],
            $eventData['username'],
            $eventData['password'],
            $eventData['proxy'],
            $eventData['proxyport']
        )
    ) {
        $wuiMainStatus->mArgs['status'] = $innomaticLocale->getStr('accountcreated_status');
    } else {
        $wuiMainStatus->mArgs['status'] = $innomaticLocale->getStr('accountnotcreated_status');
    }
}

$actionDispatcher->addEvent('removeaccount', 'pass_removeaccount');
function pass_removeaccount($eventData)
{
    global $wuiMainStatus, $innomaticLocale;

    $result = false;

    if (!empty($eventData['accountid'])) {
        $acc = new WebServicesAccount(
            InnomaticContainer::instance('innomaticcontainer')->getDataAccess(), $eventData['accountid']
        );
        $result = $acc->Remove();
    }

    if ($result) {
        $wuiMainStatus->mArgs['status'] = $innomaticLocale->getStr('remaccountok_status');
    }
}

$actionDispatcher->addEvent('updateaccount', 'pass_updateaccount');
function pass_updateaccount($eventData)
{
    global $wuiMainStatus, $innomaticLocale;

    $result = false;

    if (!empty($eventData['accountid'])) {
        $acc = new WebServicesAccount(
            InnomaticContainer::instance('innomaticcontainer')->getDataAccess(), $eventData['accountid']
        );
        $result = $acc->Update(
            $eventData['name'],
            $eventData['host'],
            $eventData['port'],
            $eventData['path'],
            $eventData['username'],
            $eventData['password'],
            $eventData['proxy'],
            $eventData['proxyport']
        );
    }

    if ($result) {
        $wuiMainStatus->mArgs['status'] = $innomaticLocale->getStr('accountupdated_status');
    }
}

$actionDispatcher->Dispatch();

// Main dispatcher
//
$viewDispatcher = new WuiDispatcher('view');

function webservicesprofiles_list_action_builder($pageNumber)
{
    return WuiEventsCall::buildEventsCallString(
        '',
        array(
            array(
                'view',
                'default',
                array(
                    'webservicesprofilespage' => $pageNumber
                )
            )
        )
    );
}

$viewDispatcher->addEvent('default', 'main_default');
function main_default($eventData)
{
    global $innomaticLocale, $wuiMainFrame, $wuiTitleBar, $wuiMainStatus;

    $profilesQuery = InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->execute(
        'SELECT id,profilename FROM webservices_profiles ORDER BY profilename'
    );

    $profiles = array();
    while (!$profilesQuery->eof) {
        $profData = $profilesQuery->getFields();
        $profiles[$profData['id']] = $profData['profilename'];
        $profilesQuery->moveNext();
    }

    if (count($profiles)) {
        $headers[0]['label'] = $innomaticLocale->getStr('profilename_header');

        $row = 0;

        $wuiProfilesTable = new WuiTable(
            'profilestable',
            array(
                'headers' => $headers,
                'rowsperpage' => '10',
                'pagesactionfunction' => 'webservicesprofiles_list_action_builder',
                'pagenumber' => isset(
                    $eventData['webservicesprofilespage']
                ) ? $eventData['webservicesprofilespage'] : ''
            )
        );

        while (list ($id, $profileName) = each($profiles)) {
            $wuiProfilesTable->addChild(new WuiLabel('profnamelabel'.$row, array('label' => $profileName)), $row, 0);

            $wuiProfileToolBar[$row] = new WuiHorizGroup('applicationtoolbar'.$row);

            $profileAction[$row] = new WuiEventsCall();
            $profileAction[$row]->addEvent(new WuiEvent('view', 'editprofile', array('profileid' => $id)));
            $wuiProfileButton[$row] = new WuiButton(
                'profilebutton'.$row,
                array('label' => $innomaticLocale->getStr('editprofile_label'),
                      'horiz' => 'true',
                      'themeimage' => 'listbulletleft',
                      'action' => $profileAction[$row]->getEventsCallString()
                )
            );
            $wuiProfileToolBar[$row]->addChild($wuiProfileButton[$row]);

            $renameAction[$row] = new WuiEventsCall();
            $renameAction[$row]->addEvent(new WuiEvent('view', 'renameprofile', array('profileid' => $id)));
            $wuiRenameButton[$row] = new WuiButton(
                'renamebutton'.$row,
                array(
                    'label' => $innomaticLocale->getStr('renameprofile_label'),  
                    'horiz' => 'true',
                    'themeimage' => 'documenttext',
                    'action' => $renameAction[$row]->getEventsCallString()
                )
            );
            $wuiProfileToolBar[$row]->addChild($wuiRenameButton[$row]);

            $removeAction[$row] = new WuiEventsCall();
            $removeAction[$row]->addEvent(new WuiEvent('view', 'default', ''));
            $removeAction[$row]->addEvent(new WuiEvent('action', 'remprofile', array('profileid' => $id)));
            $wuiRemoveButton[$row] = new WuiButton(
                'removebutton'.$row,
                array(
                    'label' => $innomaticLocale->getStr('removeprofile_label'),
                    'horiz' => 'true',
                    'themeimage' => 'trash',
                    'action' => $removeAction[$row]->getEventsCallString(),
                    'needconfirm' => 'true',
                    'confirmmessage' => sprintf($innomaticLocale->getStr('removeprofilequestion_label'), $profileName)
                )
            );
            $wuiProfileToolBar[$row]->addChild($wuiRemoveButton[$row]);

            $wuiProfilesTable->addChild($wuiProfileToolBar[$row], $row, 1);

            $row ++;
        }

        $wuiMainFrame->addChild($wuiProfilesTable);
    } else
        $wuiMainStatus->mArgs['status'] = $innomaticLocale->getStr('noprofiles_status');

    $wuiTitleBar->mTitle.= ' - '.$innomaticLocale->getStr('default_title');
}

$viewDispatcher->addEvent('newprofile', 'main_newprofile');
function main_newprofile($eventData)
{
    global $wuiMainFrame, $innomaticLocale, $wuiTitleBar;

    $wuiVertGroup = new WuiVertGroup('vgroup');

    $wuiProfileGrid = new WuiGrid('newprofilegrid', array('rows' => '2', 'cols' => '2'));

    // Profile fields
    //
    $wuiProfileGrid->addChild(
        new WuiLabel(
            'namelabel',
            array(
                'label' => $innomaticLocale->getStr('profilename_label').' (*)'
            )
        ),
        0, 0
    );
    $wuiProfileGrid->addChild(
        new WuiString(
            'profilename',
            array('disp' => 'action')
        ),
        0, 1
    );

    $wuiVertGroup->addChild($wuiProfileGrid);
    $wuiVertGroup->addChild(
        new WuiSubmit(
            'submit1',
            array(
                'caption' => $innomaticLocale->getStr('newprofile_submit')
            )
        )
    );

    $wuiVertGroup->addChild(new WuiHorizBar('horizbar1'));
    $wuiVertGroup->addChild(
        new WuiLabel(
            'reqfieldslabel',
            array(
                'label' => $innomaticLocale->getStr('requiredfields_label')
            )
        )
    );

    $formEventsCall = new WuiEventsCall();
    $formEventsCall->addEvent(new WuiEvent('action', 'newprofile', ''));
    $formEventsCall->addEvent(new WuiEvent('view', 'default', ''));

    $wuiForm = new WuiForm('newprofileform', array('action' => $formEventsCall->getEventsCallString()));
    $wuiForm->addChild($wuiVertGroup);

    $wuiMainFrame->addChild($wuiForm);

    $wuiTitleBar->mTitle.= ' - '.$innomaticLocale->getStr('newprofile_title');
}

$viewDispatcher->addEvent('renameprofile', 'main_renameprofile');
function main_renameprofile($eventData)
{
    global $wuiMainFrame, $innomaticLocale, $wuiMainStatus, $wuiTitleBar;

    $profilesQuery = InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->execute(
        'SELECT * FROM webservices_profiles WHERE id='.$eventData['profileid']
    );

    $profileData = $profilesQuery->getFields();

    $wuiVGroup = new WuiVertGroup('vgroup');

    $wuiProfileGrid = new WuiGrid('renprofilegrid', array('rows' => '2', 'cols' => '2'));

    // Profile fields
    //
    $wuiProfileGrid->addChild(
        new WuiLabel(
            'namelabel',
            array(
                'label' => $innomaticLocale->getStr('profilename_label').' (*)'
            )
        ),
        0, 0
    );
    $wuiProfileGrid->addChild(
        new WuiString(
            'profilename',
            array(
                'disp' => 'action',
                'value' => $profileData['profilename']
            )
        ),
        0, 1
    );

    $wuiVGroup->addChild($wuiProfileGrid);
    $wuiVGroup->addChild(
        new WuiSubmit('submit1', array('caption' => $innomaticLocale->getStr('renameprofile_submit')))
    );

    $wuiVGroup->addChild(new WuiHorizBar('horizbar1'));
    $wuiVGroup->addChild(
        new WuiLabel('reqfieldslabel', array('label' => $innomaticLocale->getStr('requiredfields_label')))
    );
    $formEventsCall = new WuiEventsCall();
    $formEventsCall->addEvent(
        new WuiEvent('action', 'renprofile', array('profileid' => $eventData['profileid']))
    );
    $formEventsCall->addEvent(new WuiEvent('view', 'default', ''));

    $wuiForm = new WuiForm('renameprofileform', array('action' => $formEventsCall->getEventsCallString()));
    $wuiForm->addChild($wuiVGroup);

    $wuiMainFrame->addChild($wuiForm);

    $wuiTitleBar->mTitle.= ' - '.$profileData['profilename'].' - '.$innomaticLocale->getStr('renameprofile_title');
}

function editprofile_list_action_builder($pageNumber)
{
    $tmpMainDisp = new WuiDispatcher('view');

    $eventData = $tmpMainDisp->getEventData();
    return WuiEventsCall::buildEventsCallString(
        '',
        array(
            array(
                'view',
                'editprofile',
                array(
                    'editprofilepage' => $pageNumber,
                    'profileid' => $eventData['profileid']
                )
            )
        )
    );
}

$viewDispatcher->addEvent('editprofile', 'main_editprofile');
function main_editprofile($eventData)
{
    global $innomaticLocale, $wuiMainFrame, $wuiTitleBar;

    $profilesQuery = InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->execute(
        'SELECT * FROM webservices_profiles WHERE id='.$eventData['profileid']
    );

    $profileData = $profilesQuery->getFields();

    $methodsQuery = InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->execute(
        'SELECT application,name,unsecure,catalog FROM webservices_methods ORDER BY application, name'
    );

    if ($methodsQuery->getNumberRows()) {
        $nodes = $sec = $desc = array();
        $prevCatalog = $tmpLocale = '';

        while (!$methodsQuery->eof) {
            $nodes[$methodsQuery->getFields('application')][] = $methodsQuery->getFields('name');

            $sec[$methodsQuery->getFields('application')][$methodsQuery->getFields('name')]
            = $methodsQuery->getFields('unsecure')
            == InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->fmttrue ? false : true;

            $tmpDescription = '';
            if (strlen($methodsQuery->getFields('catalog'))) {
                if ($prevCatalog != $methodsQuery->getFields('catalog'))
                    $tmpLocale = new LocaleCatalog(
                        $methodsQuery->getFields('catalog'),
                        InnomaticContainer::instance('innomaticcontainer')->getLanguage()
                    );

                $desc[$methodsQuery->getFields('application')][$methodsQuery->getFields('name')]
                = $tmpLocale->getStr($methodsQuery->getFields('name'));

                $prevCatalog = $methodsQuery->getFields('catalog');
            }

            $methodsQuery->moveNext();
        }

        $row = 0;

        $headers[0]['label'] = '';
        $headers[1]['label'] = $innomaticLocale->getStr('xmlrpcapplication_header');
        $headers[2]['label'] = '';
        $headers[3]['label'] = $innomaticLocale->getStr('webservicesmethod_header');
        $headers[4]['label'] = $innomaticLocale->getStr('docstring_header');
        $headers[5]['label'] = $innomaticLocale->getStr('security_header');

        $wuiMethodsTable = new WuiTable(
            'methodstable',
            array(
                'headers' => $headers,
                'rowsperpage' => '15',
                'pagesactionfunction' => 'editprofile_list_action_builder',
                'pagenumber' => isset($eventData['editprofilepage']) ? $eventData['editprofilepage'] : '',
                'sessionobjectusername' => $eventData['profileid']
            )
        );

        while (list ($application, $methods) = each($nodes)) {
            $xprofile = new WebServicesProfile(
                InnomaticContainer::instance('innomaticcontainer')->getDataAccess(),
                $eventData['profileid']
            );
            $nodeState = $xprofile->NodeCheck(WebServicesProfile::NODETYPE_APPLICATION, $application);

            switch ($nodeState) {
                case WebServicesProfile::APPLICATIONNODE_FULLYENABLED :
                    $icon = $wuiMainFrame->mThemeHandler->mStyle['greenball'];
                    $enabled = true;
                    break;

                case WebServicesProfile::APPLICATIONNODE_PARTIALLYENABLED :
                    $icon = $wuiMainFrame->mThemeHandler->mStyle['goldball'];
                    $enabled = true;
                    break;

                case WebServicesProfile::APPLICATIONNODE_NOTENABLED :
                    $icon = $wuiMainFrame->mThemeHandler->mStyle['redball'];
                    $enabled = false;
                    break;
            }

            $wuiMethodsTable->addChild(new WuiImage('statusimage'.$row, array('imageurl' => $icon)), $row, 0);
            $wuiMethodsTable->addChild(new WuiLabel('applicationlabel'.$row, array('label' => $application)), $row, 1);

            $wuiApplicationToolBar[$row] = new WuiHorizGroup('applicationtoolbar'.$row);

            if ($enabled) {
                $disableAction[$row] = new WuiEventsCall();
                $disableAction[$row]->addEvent(
                    new WuiEvent(
                        'view',
                        'editprofile',
                        array('profileid' => $eventData['profileid'])
                    )
                );
                $disableAction[$row]->addEvent(
                    new WuiEvent(
                        'action',
                        'disablenode',
                        array(
                            'nodetype' => WebServicesProfile::NODETYPE_APPLICATION,
                            'application' => $application,
                            'profileid' => $eventData['profileid']
                        )
                    )
                );
                $wuiDisableButton[$row] = new WuiButton(
                    'disablebutton'.$row,
                    array(
                        'label' => $innomaticLocale->getStr('disablenode_label'),
                        'horiz' => 'true',
                        'themeimage' => 'lock',
                        'themeimagetype' => 'mini',
                        'action' => $disableAction[$row]->getEventsCallString()
                    )
                );
                $wuiApplicationToolBar[$row]->addChild($wuiDisableButton[$row]);
            }

            if (!$enabled or $nodeState == WebServicesProfile::APPLICATIONNODE_PARTIALLYENABLED) {
                $enableAction[$row] = new WuiEventsCall();
                $enableAction[$row]->addEvent(
                    new WuiEvent(
                        'view',
                        'editprofile',
                        array('profileid' => $eventData['profileid'])
                    )
                );
                $enableAction[$row]->addEvent(
                    new WuiEvent(
                        'action',
                        'enablenode',
                        array(
                            'nodetype' => WebServicesProfile::NODETYPE_APPLICATION,
                            'application' => $application,
                            'profileid' => $eventData['profileid']
                        )
                    )
                );
                $wuiEnableButton[$row] = new WuiButton(
                    'enablebutton'.$row,
                    array(
                        'label' => $innomaticLocale->getStr('enablenode_label'),
                        'horiz' => 'true',
                        'themeimage' => 'unlock',
                        'themeimagetype' => 'mini',
                        'action' => $enableAction[$row]->getEventsCallString()
                    )
                );
                $wuiApplicationToolBar[$row]->addChild($wuiEnableButton[$row]);
            }

            $wuiMethodsTable->addChild($wuiApplicationToolBar[$row], $row, 6);

            $row ++;

            while (list (, $method) = each($methods)) {
                $nodeState = $xprofile->NodeCheck(WebServicesProfile::NODETYPE_METHOD, $application, $method);

                switch ($nodeState) {
                    case WebServicesProfile::METHODNODE_ENABLED :
                        $icon = $wuiMainFrame->mThemeHandler->mStyle['greenball'];
                        $enabled = true;
                        break;

                    case WebServicesProfile::METHODNODE_NOTENABLED :
                        $icon = $wuiMainFrame->mThemeHandler->mStyle['redball'];
                        $enabled = false;
                        break;
                }

                $wuiMethodsTable->addChild(new WuiImage('statusimage'.$row, array('imageurl' => $icon)), $row, 2);
                $wuiMethodsTable->addChild(new WuiLabel('methodlabel'.$row, array('label' => $method)), $row, 3);
                $img = ($sec[$application][$method] == true ? 'buttonok' : 'buttoncancel');

                $secureImage = $wuiMethodsTable->mThemeHandler->mIconsBase
                .$wuiMethodsTable->mThemeHandler->mIconsSet['icons'][$img]['base']
                .'/icons/'.$wuiMethodsTable->mThemeHandler->mIconsSet['icons'][$img]['file'];

                $wuiMethodsTable->addChild(
                    new WuiLabel(
                        'desclabel'.$row,
                        array('label' => $desc[$application][$method])
                    ),
                    $row, 4
                );
                $wuiMethodsTable->addChild(
                    new WuiImage(
                        'secure'.$row,
                        array('imageurl' => $secureImage, 'width' => 20, 'heigth' => 20)
                        ),
                    $row, 5
                );

                $wuiMethodToolbar[$row] = new WuiHorizGroup('methodtoolbar'.$row);

                if ($enabled) {
                    $disableAction[$row] = new WuiEventsCall();
                    $disableAction[$row]->addEvent(
                        new WuiEvent(
                            'view',
                            'editprofile',
                            array('profileid' => $eventData['profileid'])
                        )
                    );
                    $disableAction[$row]->addEvent(
                        new WuiEvent(
                            'action',
                            'disablenode',
                            array(
                                'nodetype' => WebServicesProfile::NODETYPE_METHOD,
                                'method' => $method,
                                'application' => $application,
                                'profileid' => $eventData['profileid']
                            )
                        )
                    );
                    $wuiDisableButton[$row] = new WuiButton(
                        'disablebutton'.$row,
                        array(
                            'label' => $innomaticLocale->getStr('disablenode_label'),
                            'horiz' => 'true',
                            'themeimage' => 'lock',
                            'themeimagetype' => 'mini',
                            'action' => $disableAction[$row]->getEventsCallString()
                        )
                    );
                    $wuiMethodToolbar[$row]->addChild($wuiDisableButton[$row]);
                } else {
                    $enableAction[$row] = new WuiEventsCall();
                    $enableAction[$row]->addEvent(
                        new WuiEvent(
                            'view',
                            'editprofile',
                            array('profileid' => $eventData['profileid'])
                        )
                    );
                    $enableAction[$row]->addEvent(
                        new WuiEvent(
                            'action',
                            'enablenode',
                            array(
                                'nodetype' => WebServicesProfile::NODETYPE_METHOD,
                                'method' => $method,
                                'application' => $application,
                                'profileid' => $eventData['profileid']
                            )
                        )
                    );
                    $wuiEnableButton[$row] = new WuiButton(
                        'enablebutton'.$row,
                        array(
                            'label' => $innomaticLocale->getStr('enablenode_label'),
                            'horiz' => 'true',
                            'themeimage' => 'unlock',
                            'themeimagetype' => 'mini',
                            'action' => $enableAction[$row]->getEventsCallString()
                        )
                    );
                    $wuiMethodToolbar[$row]->addChild($wuiEnableButton[$row]);
                }

                $wuiMethodsTable->addChild($wuiMethodToolbar[$row], $row, 6);

                $row ++;
            }
        }

        $wuiMainFrame->addChild($wuiMethodsTable);
    }

    $wuiTitleBar->mTitle.= ' - '.$profileData['profilename'].' - '.$innomaticLocale->getStr('editprofile_title');
}

function users_list_action_builder($pageNumber)
{
    return WuiEventsCall::buildEventsCallString('', array(array('view', 'users', array('userspage' => $pageNumber))));
}

$viewDispatcher->addEvent('users', 'main_users');
function main_users($eventData)
{
    global $innomaticLocale, $wuiMainFrame, $wuiTitleBar, $wuiMainStatus;

    $usersQuery = InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->execute(
        'SELECT id,username,profileid,domainid FROM webservices_users ORDER BY username'
    );

    $profQuery = InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->execute(
        'SELECT id,profilename FROM webservices_profiles ORDER BY profilename'
    );

    $profiles = array();
    while (!$profQuery->eof) {
        $profData = $profQuery->getFields();
        $profiles[$profData['id']] = $profData['profilename'];
        $profQuery->moveNext();
    }

    if ($usersQuery->getNumberRows()) {
        $headers[0]['label'] = $innomaticLocale->getStr('username_header');
        $headers[1]['label'] = $innomaticLocale->getStr('userprofilename_header');
        $headers[2]['label'] = $innomaticLocale->getStr('userdomainname_header');

        $row = 0;

        $wuiUsersTable = new WuiTable(
            'userstable',
            array(
                'headers' => $headers,
                'rowsperpage' => '10',
                'pagesactionfunction' => 'users_list_action_builder',
                'pagenumber' => isset($eventData['userspage']) ? $eventData['userspage'] : ''
            )
        );

        while (!$usersQuery->eof) {
            $userData = $usersQuery->getFields();

            $domainId = '';
            if ($userData['domainid']) {
                $domainQuery = InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->execute(
                    'SELECT domainid FROM domains WHERE id='.$userData['domainid']
                );
                $domainId = $domainQuery->getFields('domainid');
            }

            $wuiUsersTable->addChild(
                new WuiLabel(
                    'usernamelabel'.$row,
                    array(
                        'label' => strlen(
                            $userData['username']
                        ) ? $userData['username'] : $innomaticLocale->getStr('anonuser_label')
                    )
                ),
                $row, 0
            );
            $wuiUsersTable->addChild(
                new WuiLabel(
                    'userprofilelabel'.$row,
                    array(
                        'label' => $userData['profileid']
                        ? $profiles[$userData['profileid']]
                        : $innomaticLocale->getStr('noprofileid_label')
                    )
                ),
                $row, 1
            );
            $wuiUsersTable->addChild(
                new WuiLabel(
                    'userdomainlabel'.$row,
                    array(
                        'label' => strlen($domainId)
                        ? $domainId
                        : $innomaticLocale->getStr('nodomainid_label')
                    )
                ),
                $row, 2
            );

            $wuiUserToolbar[$row] = new WuiHorizGroup('usertoolbar'.$row);

            $profileAction[$row] = new WuiEventsCall();
            $profileAction[$row]->addEvent(
                new WuiEvent(
                    'view',
                    'chprofile',
                    array(
                        'userid' => $userData['id']
                    )
                )
            );
            $wuiProfileButton[$row] = new WuiButton(
                'profilebutton'.$row,
                array(
                    'label' => $innomaticLocale->getStr('chprofile_label'),
                    'horiz' => 'true',
                    'themeimage' => 'listbulletleft',
                    'action' => $profileAction[$row]->getEventsCallString()
                )
            );
            $wuiUserToolbar[$row]->addChild($wuiProfileButton[$row]);

            $domainAction[$row] = new WuiEventsCall();
            $domainAction[$row]->addEvent(
                new WuiEvent(
                    'view',
                    'chdomain',
                    array(
                        'userid' => $userData['id']
                    )
                )
            );
            $wuiDomainButton[$row] = new WuiButton(
                'domainbutton'.$row,
                array(
                    'label' => $innomaticLocale->getStr('chdomain_label'),
                    'horiz' => 'true',
                    'themeimage' => 'listbulletleft',
                    'action' => $domainAction[$row]->getEventsCallString()
                )
            );
            $wuiUserToolbar[$row]->addChild($wuiDomainButton[$row]);

            $chpasswdAction[$row] = new WuiEventsCall();
            $chpasswdAction[$row]->addEvent(
                new WuiEvent(
                    'view',
                    'chpassword',
                    array(
                        'userid' => $userData['id']
                    )
                )
            );
            $wuiChPasswdButton[$row] = new WuiButton(
                'chpasswdbutton'.$row,
                array(
                    'label' => $innomaticLocale->getStr('chpasswd_label'),
                    'horiz' => 'true',
                    'themeimage' => 'documenttext',
                    'action' => $chpasswdAction[$row]->getEventsCallString()
                )
            );
            $wuiUserToolbar[$row]->addChild($wuiChPasswdButton[$row]);

            $removeAction[$row] = new WuiEventsCall();
            $removeAction[$row]->addEvent(new WuiEvent('view', 'users', ''));
            $removeAction[$row]->addEvent(new WuiEvent('action', 'removeuser', array('userid' => $userData['id'])));
            $wuiRemoveButton[$row] = new WuiButton(
                'removebutton'.$row,
                array(
                    'label' => $innomaticLocale->getStr('removeuser_label'),
                    'horiz' => 'true',
                    'themeimage' => 'trash',
                    'action' => $removeAction[$row]->getEventsCallString(),
                    'needconfirm' => 'true',
                    'confirmmessage' => sprintf(
                        $innomaticLocale->getStr('removeuserquestion_label'),
                        $userData['username']
                    )
                )
            );
            $wuiUserToolbar[$row]->addChild($wuiRemoveButton[$row]);

            $wuiUsersTable->addChild($wuiUserToolbar[$row], $row, 3);

            $usersQuery->moveNext();
            $row ++;
        }

        $wuiMainFrame->addChild($wuiUsersTable);
    } else
        $wuiMainStatus->mArgs['status'] = $innomaticLocale->getStr('nousers_status');

    $wuiTitleBar->mTitle.= ' - '.$innomaticLocale->getStr('users_title');
}

$viewDispatcher->addEvent('newuser', 'main_newuser');
function main_newuser($eventData)
{
    global $wuiMainFrame, $innomaticLocale, $wuiTitleBar;

    $profQuery = InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->execute(
        'SELECT id,profilename FROM webservices_profiles ORDER BY profilename'
    );

    $profiles = array();
    $profiles[0] = $innomaticLocale->getStr('noprofileid_label');
    while (!$profQuery->eof) {
        $profData = $profQuery->getFields();
        $profiles[$profData['id']] = $profData['profilename'];
        $profQuery->moveNext();
    }

    $domainsQuery = InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->execute(
        'SELECT id,domainid FROM domains ORDER BY domainid'
    );

    $domains = array();
    $domains[0] = $innomaticLocale->getStr('nodomainid_label');
    while (!$domainsQuery->eof) {
        $domains[$domainsQuery->getFields('id')] = $domainsQuery->getFields('domainid');
        $domainsQuery->moveNext();
    }

    $wuiVGroup = new WuiVertGroup('vgroup');

    $wuiUserGrid = new WuiGrid('newusergrid', array('rows' => '4', 'cols' => '2'));

    // User fields
    //
    $wuiUserGrid->addChild(
        new WuiLabel(
            'namelabel',
            array(
                'label' => $innomaticLocale->getStr('username_label').' (*)'
            )
        ),
        0, 0
    );
    $wuiUserGrid->addChild(
        new WuiString(
            'username',
            array(
                'disp' => 'action'
            )
        ),
        0, 1
    );

    $wuiUserGrid->addChild(
        new WuiLabel(
            'passwordlabel',
            array(
                'label' => $innomaticLocale->getStr('userpassword_label').' (*)'
            )
        ),
        1, 0
    );
    $wuiUserGrid->addChild(
        new WuiString(
            'password',
            array(
                'disp' => 'action',
                'password' => 'true'
            )
        ),
        1, 1
    );

    $wuiUserGrid->addChild(
        new WuiLabel(
            'profilelabel',
            array(
                'label' => $innomaticLocale->getStr('userprofile_label').' (*)'
            )
        ),
        2, 0
    );
    $wuiUserGrid->addChild(
        new WuiComboBox(
            'profileid',
            array('disp' => 'action', 'elements' => $profiles)
        ),
        2, 1
    );

    $wuiUserGrid->addChild(
        new WuiLabel(
            'domainlabel',
            array('label' => $innomaticLocale->getStr('userdomain_label')
            )
        ),
        3, 0
    );
    $wuiUserGrid->addChild(
        new WuiComboBox(
            'domainid',
            array('disp' => 'action', 'elements' => $domains)
        ),
        3, 1
    );

    $wuiVGroup->addChild($wuiUserGrid);
    $wuiVGroup->addChild(
        new WuiSubmit(
            'submit1',
            array('caption' => $innomaticLocale->getStr('newuser_submit')
            )
        )
    );

    $wuiVGroup->addChild(new WuiHorizBar('horizbar1'));
    $wuiVGroup->addChild(
        new WuiLabel(
            'reqfieldslabel',
            array('label' => $innomaticLocale->getStr('requiredfields_label')
            )
        )
    );

    $formEventsCall = new WuiEventsCall();
    $formEventsCall->addEvent(new WuiEvent('action', 'adduser', ''));
    $formEventsCall->addEvent(new WuiEvent('view', 'users', ''));

    $wuiForm = new WuiForm('newuserform', array('action' => $formEventsCall->getEventsCallString()));
    $wuiForm->addChild($wuiVGroup);

    $wuiMainFrame->addChild($wuiForm);

    $wuiTitleBar->mTitle.= ' - '.$innomaticLocale->getStr('newuser_title');
}

$viewDispatcher->addEvent('chpassword', 'main_chpassword');
function main_chpassword($eventData)
{
    global $wuiMainFrame, $innomaticLocale, $wuiTitleBar;

    $userQuery = InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->execute(
        'SELECT * FROM webservices_users WHERE id='.$eventData['userid']
    );

    $userData = $userQuery->getFields();

    $wuiVGroup = new WuiVertGroup('vgroup');

    $wuiUserGrid = new WuiGrid('chpasswdgrid', array('rows' => '2', 'cols' => '2'));

    // User fields
    //
    $wuiUserGrid->addChild(
        new WuiLabel(
            'pwdlabel',
            array('label' => $innomaticLocale->getStr('chpassword_label').' (*)'
            )
        ),
        0, 0
    );
    $wuiUserGrid->addChild(
        new WuiString(
            'password',
            array('disp' => 'action', 'password' => 'true')
        ),
        0, 1
    );

    $wuiVGroup->addChild($wuiUserGrid);
    $wuiVGroup->addChild(
        new WuiSubmit(
            'submit1',
            array('caption' => $innomaticLocale->getStr('chpasswd_submit')
            )
        )
    );

    $wuiVGroup->addChild(new WuiHorizBar('horizbar1'));
    $wuiVGroup->addChild(
        new WuiLabel(
            'reqfieldslabel',
            array('label' => $innomaticLocale->getStr('requiredfields_label')
            )
        )
    );

    $formEventsCall = new WuiEventsCall();
    $formEventsCall->addEvent(new WuiEvent('action', 'chpasswd', array('userid' => $eventData['userid'])));
    $formEventsCall->addEvent(new WuiEvent('view', 'users', ''));

    $wuiForm = new WuiForm('chpasswdform', array('action' => $formEventsCall->getEventsCallString()));
    $wuiForm->addChild($wuiVGroup);

    $wuiMainFrame->addChild($wuiForm);

    $wuiTitleBar->mTitle.= ' - '.$userData['username'].' - '.$innomaticLocale->getStr('chpasswd_title');
}

$viewDispatcher->addEvent('chprofile', 'main_chprofile');
function main_chprofile($eventData)
{
    global $wuiMainFrame, $innomaticLocale, $wuiMainStatus, $wuiTitleBar;

    $userQuery = InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->execute(
        'SELECT * FROM webservices_users WHERE id='.$eventData['userid'].' '
    );

    $userData = $userQuery->getFields();

    $profQuery = InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->execute(
        'SELECT id,profilename FROM webservices_profiles ORDER BY profilename'
    );

    $profiles = array();
    $profiles[0] = $innomaticLocale->getStr('noprofileid_label');
    while (!$profQuery->eof) {
        $profData = $profQuery->getFields();
        $profiles[$profData['id']] = $profData['profilename'];
        $profQuery->moveNext();
    }

    $wuiVGroup = new WuiVertGroup('vgroup');

    $wuiUserGrid = new WuiGrid('chprofilegrid', array('rows' => '2', 'cols' => '2'));

    // User fields
    //
    $wuiUserGrid->addChild(
        new WuiLabel(
            'profilelabel',
            array('label' => $innomaticLocale->getStr('changeprofile_label').' (*)')
        ),
        0, 0
    );
    $wuiUserGrid->addChild(
        new WuiComboBox(
            'profileid',
            array('disp' => 'action', 'elements' => $profiles, 'default' => $userData['profileid'])
        ),
        0, 1
    );

    $wuiVGroup->addChild($wuiUserGrid);
    $wuiVGroup->addChild(new WuiSubmit('submit1', array('caption' => $innomaticLocale->getStr('chprofile_submit'))));

    $wuiVGroup->addChild(new WuiHorizBar('horizbar1'));
    $wuiVGroup->addChild(
        new WuiLabel(
            'reqfieldslabel',
            array('label' => $innomaticLocale->getStr('requiredfields_label')
            )
        )
    );

    $formEventsCall = new WuiEventsCall();
    $formEventsCall->addEvent(
        new WuiEvent(
            'action',
            'assignprofile',
            array('userid' => $eventData['userid'])
        )
    );
    $formEventsCall->addEvent(new WuiEvent('view', 'users', ''));

    $wuiForm = new WuiForm('chprofileform', array('action' => $formEventsCall->getEventsCallString()));
    $wuiForm->addChild($wuiVGroup);

    $wuiMainFrame->addChild($wuiForm);

    $wuiTitleBar->mTitle.= ' - '.$userData['username'].' - '.$innomaticLocale->getStr('chprofile_title');
}

$viewDispatcher->addEvent('chdomain', 'main_chdomain');
function main_chdomain($eventData)
{
    global $wuiMainFrame, $innomaticLocale, $wuiMainStatus, $wuiTitleBar;

    $userQuery = InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->execute(
        'SELECT * FROM webservices_users WHERE id='.$eventData['userid'].' '
    );

    $userData = $userQuery->getFields();

    $domainsQuery = InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->execute(
        'SELECT id,domainid FROM domains ORDER BY domainid'
    );

    $domains = array();
    $domains[0] = $innomaticLocale->getStr('nodomainid_label');
    while (!$domainsQuery->eof) {
        $domains[$domainsQuery->getFields('id')] = $domainsQuery->getFields('domainid');
        $domainsQuery->moveNext();
    }

    $wuiVGroup = new WuiVertGroup('vgroup');

    $wuiUserGrid = new WuiGrid(
        'chprofilegrid',
        array('rows' => '2', 'cols' => '2')
    );

    // User fields
    //
    $wuiUserGrid->addChild(
        new WuiLabel(
            'profilelabel',
            array('label' => $innomaticLocale->getStr('changedomain_label').' (*)'
            )
        ),
        0, 0
    );
    $wuiUserGrid->addChild(
        new WuiComboBox(
            'domainid',
            array(
                'disp' => 'action',  
                'elements' => $domains,
                'default' => $userData['domainid']
            )
        ),
        0, 1
    );

    $wuiVGroup->addChild($wuiUserGrid);
    $wuiVGroup->addChild(new WuiSubmit('submit1', array('caption' => $innomaticLocale->getStr('chdomain_submit'))));

    $wuiVGroup->addChild(new WuiHorizBar('horizbar1'));
    $wuiVGroup->addChild(
        new WuiLabel(
            'reqfieldslabel',
            array('label' => $innomaticLocale->getStr('requiredfields_label')
            )
        )
    );

    $formEventsCall = new WuiEventsCall();
    $formEventsCall->addEvent(
        new WuiEvent(
            'action',
            'assigndomain',
            array('userid' => $eventData['userid']
            )
        )
    );
    $formEventsCall->addEvent(new WuiEvent('view', 'users', ''));

    $wuiForm = new WuiForm('chprofileform', array('action' => $formEventsCall->getEventsCallString()));
    $wuiForm->addChild($wuiVGroup);

    $wuiMainFrame->addChild($wuiForm);

    $wuiTitleBar->mTitle.= ' - '.$userData['username'].' - '.$innomaticLocale->getStr('chdomain_title');
}

function accounts_list_action_builder($pageNumber)
{
    return WuiEventsCall::buildEventsCallString(
        '',
        array(
            array(
                'view',
                'accounts',
                array('accountspage' => $pageNumber)
            )
        )
    );
}

$viewDispatcher->addEvent('accounts', 'main_accounts');
function main_accounts($eventData)
{
    global $wuiMainFrame, $innomaticLocale, $wuiMainStatus, $wuiTitleBar;

    $accQuery = InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->execute(
        'SELECT id,name,host FROM webservices_accounts ORDER BY name'
    );

    if ($accQuery->getNumberRows() > 0) {
        $headers[0]['label'] = $innomaticLocale->getStr('accountname_header');
        $headers[1]['label'] = $innomaticLocale->getStr('host_header');

        $row = 0;

        $wuiAccountsTable = new WuiTable(
            'accountstable',
            array(
                'headers' => $headers,
                'rowsperpage' => '10',
                'pagesactionfunction' => 'accounts_list_action_builder',
                'pagenumber' => $eventData['accountspage']
            )
        );

        while (!$accQuery->eof) {
            $accData = $accQuery->getFields();

            $wuiAccountsTable->addChild(
                new WuiLabel(
                    'accnamelabel'.$row,
                    array('label' => $accData['name'])
                ),
                $row, 0
            );
            $wuiAccountsTable->addChild(
                new WuiLabel(
                    'hostlabel'.$row,
                    array('label' => $accData['host']
                    )
                ),
                $row, 1
            );

            $wuiAccountToolbar[$row] = new WuiHorizGroup('accounttoolbar'.$row);

            $showAction[$row] = new WuiEventsCall();
            $showAction[$row]->addEvent(
                new WuiEvent(
                    'view',
                    'showaccount',
                    array('accountid' => $accData['id']
                    )
                )
            );
            $wuiShowButton[$row] = new WuiButton(
                'showbutton'.$row,
                array(
                    'label' => $innomaticLocale->getStr('showaccount_label'),
                    'horiz' => 'true',
                    'themeimage' => 'zoom',
                    'action' => $showAction[$row]->getEventsCallString()
                )
            );
            $wuiAccountToolbar[$row]->addChild($wuiShowButton[$row]);

            $methodsAction[$row] = new WuiEventsCall();
            $methodsAction[$row]->addEvent(
                new WuiEvent(
                    'view',
                    'showmethods',
                    array('accountid' => $accData['id']
                    )
                )
            );
            $wuiMethodsButton[$row] = new WuiButton(
                'methodsbutton'.$row,
                array(
                    'label' => $innomaticLocale->getStr('showmethods_label'),
                    'horiz' => 'true',
                    'themeimage' => 'listbulletleft',
                    'action' => $methodsAction[$row]->getEventsCallString()
                )
            );
            $wuiAccountToolbar[$row]->addChild($wuiMethodsButton[$row]);

            $editAction[$row] = new WuiEventsCall();
            $editAction[$row]->addEvent(new WuiEvent('view', 'updateaccount', array('accountid' => $accData['id'])));
            $wuiEditButton[$row] = new WuiButton(
                'editbutton'.$row,
                array(
                    'label' => $innomaticLocale->getStr('editaccount_label'),
                    'horiz' => 'true',
                    'themeimage' => 'documenttext',
                    'action' => $editAction[$row]->getEventsCallString()
                )
            );
            $wuiAccountToolbar[$row]->addChild($wuiEditButton[$row]);

            $removeAction[$row] = new WuiEventsCall();
            $removeAction[$row]->addEvent(new WuiEvent('view', 'accounts', ''));
            $removeAction[$row]->addEvent(
                new WuiEvent(
                    'action',
                    'removeaccount',
                    array('accountid' => $accData['id'])
                )
            );
            $wuiRemoveButton[$row] = new WuiButton(
                'removebutton'.$row,
                array(
                    'label' => $innomaticLocale->getStr('removeaccount_label'),
                    'horiz' => 'true',
                    'themeimage' => 'trash',
                    'action' => $removeAction[$row]->getEventsCallString(),
                    'needconfirm' => 'true',
                    'confirmmessage' => sprintf(
                        $innomaticLocale->getStr('removeaccountquestion_label'),
                        $accData['name']
                    )
                )
            );
            $wuiAccountToolbar[$row]->addChild($wuiRemoveButton[$row]);

            $wuiAccountsTable->addChild($wuiAccountToolbar[$row], $row, 2);

            $row ++;
            $accQuery->moveNext();
        }

        $wuiMainFrame->addChild($wuiAccountsTable);
    } else
        $wuiMainStatus->mArgs['status'] = $innomaticLocale->getStr('noaccounts_status');

    $wuiTitleBar->mTitle.= ' - '.$innomaticLocale->getStr('accounts_title');
}

$viewDispatcher->addEvent('newaccount', 'main_newaccount');
function main_newaccount($eventData)
{
    global $wuiMainFrame, $innomaticLocale, $wuiMainStatus, $wuiTitleBar;

    $wuiVGroup = new WuiVertGroup('vgroup');

    $wuiFormGrid = new WuiGrid('newaccountgrid', array('rows' => '6', 'cols' => '2'));

    $wuiFormGrid->addChild(
        new WuiLabel(
            'namelabel',
            array('label' => $innomaticLocale->getStr('accountname_label').' (*)')
        ),
        0, 0
    );
    $wuiFormGrid->addChild(new WuiString('name', array('disp' => 'action')), 0, 1);

    $wuiFormGrid->addChild(
        new WuiLabel(
            'hostlabel',
            array('label' => $innomaticLocale->getStr('host_label').' (*)')
        ),
        1, 0
    );
    $wuiFormGrid->addChild(new WuiString('host', array('disp' => 'action')), 1, 1);

    $wuiFormGrid->addChild(
        new WuiLabel(
            'pathlabel',
            array('label' => $innomaticLocale->getStr('path_label').' (*)')
        ),
        2, 0
    );
    $wuiFormGrid->addChild(new WuiString('path', array('disp' => 'action')), 2, 1);

    $wuiFormGrid->addChild(
        new WuiLabel(
            'portlabel',
            array('label' => $innomaticLocale->getStr('port_label').' (*)')
        ),
        3, 0
    );
    $wuiFormGrid->addChild(new WuiString('port', array('disp' => 'action')), 3, 1);

    $wuiFormGrid->addChild(
        new WuiLabel(
            'usernamelabel',
            array('label' => $innomaticLocale->getStr('username_label').' (*)')
        ),
        4, 0
    );
    $wuiFormGrid->addChild(new WuiString('username', array('disp' => 'action')), 4, 1);

    $wuiFormGrid->addChild(
        new WuiLabel(
            'passwordlabel',
            array('label' => $innomaticLocale->getStr('password_label').' (*)')
        ),
        5, 0
    );
    $wuiFormGrid->addChild(
        new WuiString(
            'password',
            array('disp' => 'action', 'password' => 'true')
        ),
        5, 1
    );

    $wuiFormGrid->addChild(
        new WuiLabel(
            'proxylabel',
            array('label' => $innomaticLocale->getStr('proxy_label')
            )
        ),
        6, 0
    );
    $wuiFormGrid->addChild(new WuiString('proxy', array('disp' => 'action')), 6, 1);

    $wuiFormGrid->addChild(
        new WuiLabel(
            'proxyportlabel',
            array('label' => $innomaticLocale->getStr('proxyport_label'))
        ),
        7, 0
    );
    $wuiFormGrid->addChild(new WuiString('proxyport', array('disp' => 'action')), 7, 1);

    $wuiVGroup->addChild($wuiFormGrid);
    $wuiVGroup->addChild(
        new WuiSubmit(
            'submit',
            array('caption' => $innomaticLocale->getStr('createaccount_submit'))
        )
    );

    $wuiVGroup->addChild(new WuiHorizBar('horizbar1'));
    $wuiVGroup->addChild(
        new WuiLabel(
            'reqfieldslabel',
            array('label' => $innomaticLocale->getStr('requiredfields_label'))
        )
    );

    $formEventsCall = new WuiEventsCall();
    $formEventsCall->addEvent(new WuiEvent('action', 'createaccount', ''));
    $formEventsCall->addEvent(new WuiEvent('view', 'accounts', ''));

    $wuiForm = new WuiForm('newdomainform', array('action' => $formEventsCall->getEventsCallString()));
    $wuiForm->addChild($wuiVGroup);

    $wuiMainFrame->addChild($wuiForm);

    $wuiTitleBar->mTitle.= ' - '.$innomaticLocale->getStr('newaccount_title');
}

$viewDispatcher->addEvent('updateaccount', 'main_updateaccount');
function main_updateaccount($eventData)
{
    global $wuiMainFrame, $innomaticLocale, $wuiMainStatus, $wuiTitleBar;

    $accQuery = InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->execute(
        'SELECT * FROM webservices_accounts WHERE id='.$eventData['accountid']
    );

    $accData = $accQuery->getFields();

    $wuiVGroup = new WuiVertGroup('vgroup');

    $wuiFormGrid = new WuiGrid('newaccountgrid', array('rows' => '6', 'cols' => '2'));

    $wuiFormGrid->addChild(
        new WuiLabel(
            'namelabel',
            array('label' => $innomaticLocale->getStr('accountname_label').' (*)')
        ),
        0, 0
    );
    $wuiFormGrid->addChild(
        new WuiString(
            'name',
            array('disp' => 'action', 'value' => $accData['name'])
        ),
        0, 1
    );

    $wuiFormGrid->addChild(
        new WuiLabel(
            'hostlabel',
            array('label' => $innomaticLocale->getStr('host_label').' (*)')
        ),
        1, 0
    );
    $wuiFormGrid->addChild(
        new WuiString(
            'host',
            array('disp' => 'action', 'value' => $accData['host'])
        ),
        1, 1
    );

    $wuiFormGrid->addChild(
        new WuiLabel(
            'pathlabel',
            array('label' => $innomaticLocale->getStr('path_label').' (*)')
        ),
        2, 0
    );
    $wuiFormGrid->addChild(
        new WuiString(
            'path',
            array('disp' => 'action', 'value' => $accData['path'])
        ),
        2, 1
    );

    $wuiFormGrid->addChild(
        new WuiLabel(
            'portlabel',
            array('label' => $innomaticLocale->getStr('port_label').' (*)')
        ),
        3, 0
    );
    $wuiFormGrid->addChild(
        new WuiString(
            'port',
            array('disp' => 'action', 'value' => $accData['port'])
        ),
        3, 1
    );

    $wuiFormGrid->addChild(
        new WuiLabel(
            'usernamelabel',
            array('label' => $innomaticLocale->getStr('username_label').' (*)')
        ),
        4, 0
    );
    $wuiFormGrid->addChild(
        new WuiString(
            'username',
            array('disp' => 'action', 'value' => $accData['username'])
        ),
        4, 1
    );

    $wuiFormGrid->addChild(
        new WuiLabel(
            'passwordlabel',
            array('label' => $innomaticLocale->getStr('password_label').' (*)')
        ),
        5, 0
    );
    $wuiFormGrid->addChild(
        new WuiString(
            'password',
            array('disp' => 'action', 'password' => 'true', 'value' => $accData['password'])
        ),
        5, 1  
    );

    $wuiFormGrid->addChild(
        new WuiLabel(
            'proxylabel',
            array('label' => $innomaticLocale->getStr('proxy_label'))
        ),
        6, 0
    );
    $wuiFormGrid->addChild(
        new WuiString(
            'proxy',
            array('disp' => 'action', 'value' => $accData['proxy'])
        ),
        6, 1
    );

    $wuiFormGrid->addChild(
        new WuiLabel(
            'proxyportlabel',
            array('label' => $innomaticLocale->getStr('proxyport_label'))
        ),
        7, 0
    );
    $wuiFormGrid->addChild(
        new WuiString(
            'proxyport',
            array('disp' => 'action', 'value' => $accData['proxyport'])
        ),
        7, 1
    );

    $wuiVGroup->addChild($wuiFormGrid);
    $wuiVGroup->addChild(
        new WuiSubmit(
            'submit',
            array('caption' => $innomaticLocale->getStr('updateaccount_submit'))
        )
    );

    $wuiVGroup->addChild(new WuiHorizBar('horizbar1'));
    $wuiVGroup->addChild(
        new WuiLabel(
            'reqfieldslabel',
            array('label' => $innomaticLocale->getStr('requiredfields_label'))
        )
    );

    $formEventsCall = new WuiEventsCall();
    $formEventsCall->addEvent(
        new WuiEvent(
            'action',
            'updateaccount',
            array('accountid' => $eventData['accountid'])
        )
    );
    $formEventsCall->addEvent(new WuiEvent('view', 'accounts', ''));

    $wuiForm = new WuiForm('newdomainform', array('action' => $formEventsCall->getEventsCallString()));
    $wuiForm->addChild($wuiVGroup);

    $wuiMainFrame->addChild($wuiForm);

    $wuiTitleBar->mTitle.= ' - '.$accData['name'].' - '.$innomaticLocale->getStr('updateaccount_title');
}

$viewDispatcher->addEvent('showaccount', 'main_showaccount');
function main_showaccount($eventData)
{
    global $wuiMainFrame, $innomaticLocale, $wuiMainStatus, $wuiTitleBar;

    $accQuery = InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->execute(
        'SELECT * FROM webservices_accounts WHERE id='.$eventData['accountid']
    );

    $accData = $accQuery->getFields();

    $wuiVGroup = new WuiVertGroup('vgroup');

    $wuiFormGrid = new WuiGrid('newaccountgrid', array('rows' => '6', 'cols' => '2'));

    $wuiFormGrid->addChild(
        new WuiLabel(
            'namelabel',
            array('label' => $innomaticLocale->getStr('accountname_label'))
        ),
        0, 0
    );
    $wuiFormGrid->addChild(
        new WuiString(
            'name',
            array('disp' => 'action', 'value' => $accData['name'])
        ),
        0, 1
    );

    $wuiFormGrid->addChild(
        new WuiLabel(
            'hostlabel',
            array('label' => $innomaticLocale->getStr('host_label'))
        ),
        1, 0
    );
    $wuiFormGrid->addChild(
        new WuiString(
            'host',
            array('disp' => 'action', 'value' => $accData['host'])
        ),
        1, 1
    );

    $wuiFormGrid->addChild(
        new WuiLabel(
            'pathlabel',
            array('label' => $innomaticLocale->getStr('path_label'))
        ),
        2, 0
    );
    $wuiFormGrid->addChild(
        new WuiString(
            'path',
            array('disp' => 'action', 'value' => $accData['path'])
        ),
        2, 1
    );

    $wuiFormGrid->addChild(
        new WuiLabel(
            'portlabel',
            array('label' => $innomaticLocale->getStr('port_label'))
        ),
        3, 0
    );
    $wuiFormGrid->addChild(
        new WuiString(
            'port',
            array('disp' => 'action', 'value' => $accData['port'])
        ),
        3, 1
    );

    $wuiFormGrid->addChild(
        new WuiLabel(
            'usernamelabel',
            array('label' => $innomaticLocale->getStr('username_label'))
        ),
        4, 0
    );
    $wuiFormGrid->addChild(
        new WuiString(
            'username',
            array('disp' => 'action', 'value' => $accData['username'])
        ),
        4, 1
    );

    $wuiFormGrid->addChild(
        new WuiLabel(
            'passwordlabel',
            array('label' => $innomaticLocale->getStr('password_label'))
        ),
        5, 0
    );
    $wuiFormGrid->addChild(
        new WuiString(
            'password',
            array('disp' => 'action', 'value' => $accData['password'])
        ),
        5, 1
    );

    $wuiFormGrid->addChild(
        new WuiLabel(
            'proxylabel',
            array('label' => $innomaticLocale->getStr('proxy_label'))
        ),
        6, 0
    );
    $wuiFormGrid->addChild(
        new WuiString(
            'proxy',
            array('disp' => 'action', 'value' => $accData['proxy'])
        ),
        6, 1
    );

    $wuiFormGrid->addChild(
        new WuiLabel(
            'proxyportlabel',
            array('label' => $innomaticLocale->getStr('proxyport_label'))
        ),
        7, 0
    );
    $wuiFormGrid->addChild(
        new WuiString(
            'proxyport',
            array('disp' => 'action', 'value' => $accData['proxyport'])
        ),
        7, 1
    );

    $wuiVGroup->addChild($wuiFormGrid);
    $wuiMainFrame->addChild($wuiVGroup);

    $wuiTitleBar->mTitle.= ' - '.$accData['name'].' - '.$innomaticLocale->getStr('showaccount_title');
}

function methods_list_action_builder($pageNumber)
{
    $tmpMainDisp = new WuiDispatcher('view');

    $eventData = $tmpMainDisp->getEventData();
    return WuiEventsCall::buildEventsCallString(
        '',
        array(
            array(
                'view',
                'showmethods',
                array(
                    'methodspage' => $pageNumber,
                    'accountid' => $eventData['accountid']
                )
            )
        )
    );
}

$viewDispatcher->addEvent('showmethods', 'main_showmethods');
function main_showmethods($eventData)
{
    global $wuiMainFrame, $innomaticLocale, $wuiMainStatus, $wuiTitleBar;

    $accQuery = InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->execute(
        'SELECT name FROM webservices_accounts WHERE id='.$eventData['accountid']
    );

    $accData = $accQuery->getFields();

    $wuiVGroup = new WuiVertGroup('vgroup');

    $xmlrpcAccount = new WebServicesAccount(
        InnomaticContainer::instance('innomaticcontainer')->getDataAccess(),
        $eventData['accountid']
    );
    $xmlrpcClient = new XmlRpc_Client($xmlrpcAccount->mPath, $xmlrpcAccount->mHost, $xmlrpcAccount->mPort);
    $xmlrpcClient->setProxy($xmlrpcAccount->mProxy, $xmlrpcAccount->mProxyPort);
    $xmlrpcClient->setCredentials($xmlrpcAccount->mUsername, $xmlrpcAccount->mPassword);

    $xmlrpcMessage = new XmlRpcMsg('system.listMethods');
    $xmlrpcResp = $xmlrpcClient->Send($xmlrpcMessage);

    if ($xmlrpcResp) {
        if (!$xmlrpcResp->FaultCode()) {
            $xv = $xmlrpcResp->Value();
            if (is_object($xv)) {
                $methods = php_xmlrpc_decode($xv);
                //$methods_val = $xv->scalarVal();

                if (is_array($methods)) {
                    $headers[0]['label'] = $innomaticLocale->getStr('method.header');
                    $methodsTable = new WuiTable(
                        'methods',
                        array(
                            'elements' => $elements,
                            'headers' => $headers,
                            'rowsperpage' => '20',
                            'pagesactionfunction' => 'methods_list_action_builder',
                            'pagenumber' => $eventData['methodspage']
                        )
                    );

                    $row = 0;

                    while (list ($key, $val) = each($methods)) {
                        $methodsTable->addChild(new WuiLabel('method', array('label' => $val)), $row, 0);
                        $row ++;
                    }
                    $wuiVGroup->addChild($methodsTable);
                }
            }
        } else {
            $wuiMainStatus->mArgs['status'] = $innomaticLocale->getStr('server_response_error');
        }
    } else {
        $wuiMainStatus->mArgs['status'] = $innomaticLocale->getStr('invalid_server_response');
    }

    //$wuiVGroup

    $wuiMainFrame->addChild($wuiVGroup);

    $wuiTitleBar->mTitle.= ' - '.$accData['name'].' - '.$innomaticLocale->getStr('showmethods_title');
}

$viewDispatcher->addEvent('help', 'main_help');
function main_help($eventData)
{
    global $wuiTitleBar, $wuiMainFrame, $innomaticLocale;
    $wuiTitleBar->mTitle.= ' - '.$innomaticLocale->getStr('help_title');
    $wuiMainFrame->addChild(
        new WuiHelpNode(
            'xmlrpc_help',
            array(
                'base' => 'innomatic',
                'node' => 'innomatic.root.xmlrpc.'.$eventData['node'].'.html',
                'language' => InnomaticContainer::instance('innomaticcontainer')->getLanguage()
            )
        )
    );
}

$viewDispatcher->Dispatch();

// Page render
//
$wuiMainVertGroup->addChild($wuiMainFrame);
$wuiMainVertGroup->addChild($wuiMainStatus);
$wuiPage->addChild($wuiMainVertGroup);
$wui->addChild($wuiPage);
$wui->render();
