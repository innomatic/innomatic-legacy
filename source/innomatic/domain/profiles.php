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

// NOTE: This is an old-style panel code with a single file
// acting as model, view and controller.

use \Innomatic\Core\InnomaticContainer;
use \Innomatic\Wui\Widgets;
use \Innomatic\Wui\Dispatch;
use \Innomatic\Locale\LocaleCatalog;
use \Innomatic\Domain\User;
use \Shared\Wui;

global $wuiMainFrame, $innomaticLocale, $wuiMainStatus, $wuiTitleBar;

$log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();
$innomaticLocale = new LocaleCatalog(
    'innomatic::domain_profiles',
    \Innomatic\Core\InnomaticContainer::instance(
        '\Innomatic\Core\InnomaticContainer'
    )->getCurrentUser()->getLanguage()
);
$wui = \Innomatic\Wui\Wui::instance('\Innomatic\Wui\Wui');
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

$wuiPage = new WuiPage('page', array('title' => $innomaticLocale->getStr('profiles_title')));
$wuiMainVertGroup = new WuiVertgroup('mainvertgroup');
$wuiTitleBar = new WuiTitleBar(
    'titlebar',
    array('title' => $innomaticLocale->getStr('profiles_title'),
    'icon' => 'user')
);
$wuiMainVertGroup->addChild($wuiTitleBar);

// Profiles bar
//
$wuiProfilesToolBar = new WuiToolBar('profilestoolbar');

$homeAction = new WuiEventsCall();
$homeAction->addEvent(new WuiEvent('view', 'default', ''));
$wuiHomeButton = new WuiButton(
    'homebutton',
    array('label' => $innomaticLocale->getStr('profiles_button'),
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
$wuiToolBarFrame = new WuiHorizgroup('toolbarframe');

$wuiToolBarFrame->addChild($wuiProfilesToolBar);
$wuiToolBarFrame->addChild($wuiUsersToolBar);

if (
    User::isAdminUser(
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserName(),
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDomainId()
    )
) {
    $motdTb = new WuiToolBar('motdtb');
    $motdAction = new WuiEventsCall();
    $motdAction->addEvent(New WuiEvent('view', 'motd', ''));
    $motdButton = new WuiButton(
        'motdbutton',
        array(
            'label' => $innomaticLocale->getStr('motd.button'),
            'themeimage' => 'documenttext',
            'horiz' => 'true',
            'action' => $motdAction->getEventsCallString()
        )
    );

    $motdTb->addChild($motdButton);
    $wuiToolBarFrame->addChild($motdTb);
}

$wuiToolBarFrame->addChild($wuiHelpToolBar);

$wuiMainVertGroup->addChild($wuiToolBarFrame);

$wuiMainFrame = new WuiHorizframe('mainframe');
$wuiMainStatus = new WuiStatusBar('mainstatusbar');

// Pass dispatcher
//
$actionDispatcher = new WuiDispatcher('action');

$actionDispatcher->addEvent('newgroup', 'pass_newgroup');
function pass_newgroup($eventData)
{
    global $innomaticLocale, $wuiMainStatus;

    $tempGroup = new Group(
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(),
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->domaindata['id']
    );
    $groupData['groupname'] = $eventData['groupname'];
    $tempGroup->createGroup($groupData);
}

$actionDispatcher->addEvent('rengroup', 'pass_rengroup');
function pass_rengroup($eventData)
{
    global $innomaticLocale, $wuiMainStatus;

    $tempGroup = new Group(
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(),
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->domaindata['id'],
        $eventData['gid']
    );
    $groupData['groupname'] = $eventData['groupname'];
    $tempGroup->editGroup($groupData);
}

$actionDispatcher->addEvent('removegroup', 'pass_removegroup');
function pass_removegroup($eventData)
{
    global $innomaticLocale, $wuiMainStatus;

    if ($eventData['userstoo'] == 1)
        $deleteUsersToo = true;
    else
        $deleteUsersToo = false;

    $tempGroup = new Group(
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(),
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->domaindata['id'],
        $eventData['gid']
    );
    $tempGroup->removeGroup($deleteUsersToo);
}

$actionDispatcher->addEvent('adduser', 'pass_adduser');
function pass_adduser($eventData)
{
    global $innomaticLocale, $wuiMainStatus;

    if ($eventData['passworda'] == $eventData['passwordb']) {
        $tempUser = new User(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->domaindata['id']);
        $userData['domainid'] = \Innomatic\Core\InnomaticContainer::instance(
            '\Innomatic\Core\InnomaticContainer'
        )->getCurrentDomain()->domaindata['id'];
        $userData['groupid'] = $eventData['groupid'];
        $userData['username'] = $eventData['username']
            . (\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getEdition() == \Innomatic\Core\InnomaticContainer::EDITION_SAAS ? '@'
            .\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDomainId() : '');
        $userData['password'] = $eventData['passworda'];
        $userData['fname'] = $eventData['fname'];
        $userData['lname'] = $eventData['lname'];
        $userData['email'] = $eventData['email'];
        $userData['otherdata'] = $eventData['other'];

        $tempUser->create($userData);
    }
}

$actionDispatcher->addEvent('edituser', 'pass_edituser');
function pass_edituser($eventData)
{
    global $innomaticLocale, $wuiMainStatus;
        $tempUser = new User(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->domaindata['id'],
            $eventData['uid']
        );
        $userData['groupid'] = $eventData['groupid'];
        $userData['username'] = $eventData['username'];
        $userData['fname'] = $eventData['fname'];
        $userData['lname'] = $eventData['lname'];
        $userData['email'] = $eventData['email'];
        $userData['otherdata'] = $eventData['other'];

        if (!empty($eventData['oldpassword']) and !empty($eventData['passworda']) and !empty($eventData['passwordb'])) {
            if ($eventData['passworda'] == $eventData['passwordb']) {
                $userData['password'] = $eventData['passworda'];
            }
        }

        $tempUser->update($userData);
}

$actionDispatcher->addEvent('chpasswd', 'pass_chpasswd');
function pass_chpasswd($eventData)
{
    global $innomaticLocale, $wuiMainStatus;
        $tempUser = new User(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->domaindata['id'],
            $eventData['uid']
        );
        $tempUser->changePassword($eventData['password']);
}

$actionDispatcher->addEvent('chprofile', 'pass_chprofile');
function pass_chprofile($eventData)
{
    global $innomaticLocale, $wuiMainStatus;

        $tempUser = new User(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->domaindata['id'],
            $eventData['uid']
        );
        $userData['groupid'] = $eventData['profileid'];
        $tempUser->changeGroup($userData);
}

$actionDispatcher->addEvent('removeuser', 'pass_removeuser');
function pass_removeuser($eventData)
{
    global $innomaticLocale, $wuiMainStatus;

        $tempUser = new User(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->domaindata['id'],
            $eventData['uid']
        );
        $tempUser->remove();
}

$actionDispatcher->addEvent('enablenode', 'pass_enablenode');
function pass_enablenode($eventData)
{
    global $innomaticLocale, $wuiMainStatus;

    $tempPerm = new Permissions(
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(),
        $eventData['gid']
    );
    $tempPerm->enable($eventData['node'], $eventData['ntype']);
}

$actionDispatcher->addEvent('disablenode', 'pass_disablenode');
function pass_disablenode($eventData)
{
    global $innomaticLocale, $wuiMainStatus;

    $tempPerm = new Permissions(
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(),
        $eventData['gid']
    );
    $tempPerm->disable($eventData['node'], $eventData['ntype']);
}

if (
    User::isAdminUser(
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserName(),
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDomainId()
    )
) {
    $actionDispatcher->addEvent('setmotd', 'pass_setmotd');
    function pass_setmotd($eventData)
    {
        global $innomaticLocale, $wuiMainStatus;

        $domain = new \Innomatic\Domain\Domain(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDomainId(),
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()
        );

        $domain->setMotd($eventData['motd']);
        $wuiMainStatus->mArgs['status'] = $innomaticLocale->getStr('motd_set.status');
    }
}

$actionDispatcher->Dispatch();

// Main dispatcher
//
$viewDispatcher = new WuiDispatcher('view');

$viewDispatcher->addEvent('default', 'main_default');
function main_default($eventData)
{
    global $innomaticLocale, $wuiMainFrame, $wuiTitleBar;

    $profQuery = \Innomatic\Core\InnomaticContainer::instance(
        '\Innomatic\Core\InnomaticContainer'
    )->getCurrentDomain()->getDataAccess()->execute(
        'SELECT * FROM domain_users_groups ORDER BY groupname'
    );

    $profiles = array();
    while (!$profQuery->eof) {
        $profData = $profQuery->getFields();
        $profiles[$profData['id']] = $profData['groupname'];
        $profQuery->moveNext();
    }

    if (count($profiles)) {
        $headers[0]['label'] = $innomaticLocale->getStr('profilename_header');

        $row = 0;

        $wuiProfilesTable = new WuiTable('profilestable', array('headers' => $headers));

        while (list ($id, $profileName) = each($profiles)) {
            $wuiProfilesTable->addChild(new WuiLabel('profnamelabel'.$row, array('label' => $profileName)), $row, 0);

            $wuiProfileToolbar[$row] = new WuiHorizgroup('applicationtoolbar'.$row);

            $profileAction[$row] = new WuiEventsCall();
            $profileAction[$row]->addEvent(new WuiEvent('view', 'editprofile', array('profileid' => $id)));
            $wuiProfileButton[$row] = new WuiButton(
                'profilebutton'.$row,
                array(
                    'label' => $innomaticLocale->getStr('editprofile_label'),
                    'themeimage' => 'listbulletleft',
                    'themeimagetype' => 'mini',
                    'horiz' => 'true',
                    'action' => $profileAction[$row]->getEventsCallString()
                )
            );
            $wuiProfileToolbar[$row]->addChild($wuiProfileButton[$row]);

            $renameAction[$row] = new WuiEventsCall();
            $renameAction[$row]->addEvent(new WuiEvent('view', 'renameprofile', array('profileid' => $id)));
            $wuiRenameButton[$row] = new WuiButton(
                'renamebutton'.$row,
                array(
                    'label' => $innomaticLocale->getStr('renameprofile_label'),
                    'themeimage' => 'documenttext',
                    'themeimagetype' => 'mini',
                    'horiz' => 'true',
                    'action' => $renameAction[$row]->getEventsCallString()
                )
            );
            $wuiProfileToolbar[$row]->addChild($wuiRenameButton[$row]);

            $removeAction[$row] = new WuiEventsCall();
            $removeAction[$row]->addEvent(new WuiEvent('view', 'deleteprofile', array('profileid' => $id)));
            $wuiRemoveButton[$row] = new WuiButton(
                'removebutton'.$row,
                array(
                    'label' => $innomaticLocale->getStr('removeprofile_label'),
                    'themeimage' => 'trash',
                    'themeimagetype' => 'mini',
                    'horiz' => 'true',
                    'action' => $removeAction[$row]->getEventsCallString()
                )
            );
            $wuiProfileToolbar[$row]->addChild($wuiRemoveButton[$row]);

            $wuiProfilesTable->addChild($wuiProfileToolbar[$row], $row, 1);

            $row ++;
        }

        $wuiMainFrame->addChild($wuiProfilesTable);
    }

    $wuiTitleBar->mTitle.= ' - '.$innomaticLocale->getStr('default_title');
}

$viewDispatcher->addEvent('editprofile', 'main_editprofile');
function main_editprofile($eventData)
{
    global $innomaticLocale, $wuiMainFrame, $wuiTitleBar;

    $profQuery = \Innomatic\Core\InnomaticContainer::instance(
        '\Innomatic\Core\InnomaticContainer'
    )->getCurrentDomain()->getDataAccess()->execute(
        'SELECT * FROM domain_users_groups WHERE id='.$eventData['profileid']
    );

    $profData = $profQuery->getFields();

    $groupsQuery = \Innomatic\Core\InnomaticContainer::instance(
        '\Innomatic\Core\InnomaticContainer'
    )->getCurrentDomain()->getDataAccess()->execute('SELECT * FROM domain_panels_groups ORDER BY name');

    if ($groupsQuery->getNumberRows()) {
        $perm = new Permissions(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(),
            $eventData['profileid']
        );
        $row = 0;

        $headers[0]['label'] = '';
        $headers[1]['label'] = $innomaticLocale->getStr('domaingroup_header');
        $headers[2]['label'] = '';
        $headers[3]['label'] = $innomaticLocale->getStr('domainpanel_header');

        $wuiGroupsTable = new WuiTable('groupsstable', array('headers' => $headers));

        while (!$groupsQuery->eof) {
            $groupData = $groupsQuery->getFields();
            $tempLocale = new LocaleCatalog(
                $groupData['catalog'],
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getLanguage()
            );
            $nodeState = $perm->Check($groupData['id'], Permissions::NODETYPE_GROUP);

            switch ($nodeState) {
                case Permissions::NODE_FULLYENABLED :
                    $icon = $wuiMainFrame->mThemeHandler->mStyle['greenball'];
                    $enabled = true;
                    break;

                case Permissions::NODE_PARTIALLYENABLED :
                    $icon = $wuiMainFrame->mThemeHandler->mStyle['goldball'];
                    $enabled = true;
                    break;

                case Permissions::NODE_NOTENABLED :
                    $icon = $wuiMainFrame->mThemeHandler->mStyle['redball'];
                    $enabled = false;
                    break;
            }

            $wuiGroupsTable->addChild(new WuiImage('statusimage'.$row, array('imageurl' => $icon)), $row, 0);
            $wuiGroupsTable->addChild(
                new WuiLabel(
                    'grouplabel'.$row,
                    array(
                        'label' => $tempLocale->getStr($groupData['name'])
                    )
                ),
                $row, 1
            );

            $wuiGroupToolbar[$row] = new WuiHorizgroup('grouptoolbar'.$row);

            if ($enabled) {
                $disableAction[$row] = new WuiEventsCall();
                $disableAction[$row]->addEvent(
                    new WuiEvent(
                        'view',
                        'editprofile',
                        array(
                            'profileid' => $eventData['profileid']
                        )
                    )
                );
                $disableAction[$row]->addEvent(
                    new WuiEvent(
                        'action',
                        'disablenode',
                        array(
                            'ntype' => Permissions::NODETYPE_GROUP,
                            'node' => $groupData['id'],
                            'gid' => $eventData['profileid']
                        )
                    )
                );
                $wuiDisableButton[$row] = new WuiButton(
                    'disablebutton'.$row,
                    array(
                        label => $innomaticLocale->getStr('disablenode_label'),
                        'themeimage' => 'lock',
                        'themeimagetype' => 'mini',
                        'horiz' => 'true',
                        'action' => $disableAction[$row]->getEventsCallString()
                    )
                );
                $wuiGroupToolbar[$row]->addChild($wuiDisableButton[$row]);
            }

            if (!$enabled or $nodeState == Permissions::NODE_PARTIALLYENABLED) {
                $enableAction[$row] = new WuiEventsCall();
                $enableAction[$row]->addEvent(
                    new WuiEvent(
                        'view',
                        'editprofile',
                        array('profileid' => $eventData['profileid']
                        )
                    )
                );
                $enableAction[$row]->addEvent(
                    new WuiEvent(
                        'action',
                        'enablenode',
                        array(
                            'ntype' => Permissions::NODETYPE_GROUP,
                            'node' => $groupData['id'],
                            'gid' => $eventData['profileid']
                        )
                    )
                );
                $wuiEnableButton[$row] = new WuiButton(
                    'enablebutton'.$row,
                    array(
                        label => $innomaticLocale->getStr('enablenode_label'),
                        'themeimage' => 'unlock',
                        'themeimagetype' => 'mini',
                        'horiz' => 'true',
                        'action' => $enableAction[$row]->getEventsCallString()
                    )
                );
                $wuiGroupToolbar[$row]->addChild($wuiEnableButton[$row]);
            }

            $wuiGroupsTable->addChild($wuiGroupToolbar[$row], $row, 4);

            $row ++;

            $pagesQuery = \Innomatic\Core\InnomaticContainer::instance(
                '\Innomatic\Core\InnomaticContainer'
            )->getCurrentDomain()->getDataAccess()->execute(
                'SELECT * FROM domain_panels WHERE groupid='.$groupData['id'].' ORDER BY name'
            );

            while (!$pagesQuery->eof) {
                $pageData = $pagesQuery->getFields();
                $tempLocale = new LocaleCatalog(
                    $pageData['catalog'],
                    \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getLanguage()
                );
                $nodeState = $perm->Check($pageData['id'], 'page');

                switch ($nodeState) {
                    case Permissions::NODE_FULLYENABLED :
                        $icon = $wuiMainFrame->mThemeHandler->mStyle['greenball'];
                        $enabled = true;
                        break;

                    case Permissions::NODE_NOTENABLED :
                        $icon = $wuiMainFrame->mThemeHandler->mStyle['redball'];
                        $enabled = false;
                        break;
                }

                $wuiGroupsTable->addChild(
                    new WuiImage(
                        'statusimage'.$row,
                        array(
                            'imageurl' => $icon
                        )
                    ),
                    $row, 2
                );
                $wuiGroupsTable->addChild(
                    new WuiLabel(
                        'methodlabel'.$row,
                        array(
                            'label' => $tempLocale->getStr($pageData['name'])
                        )
                    ),
                    $row, 3
                );

                $wuiPageToolbar[$row] = new WuiHorizgroup('pagetoolbar'.$row);

                if ($enabled) {
                    $disableAction[$row] = new WuiEventsCall();
                    $disableAction[$row]->addEvent(
                        new WuiEvent(
                            'view',
                            'editprofile',
                            array(
                                'profileid' => $eventData['profileid']
                            )
                        )
                    );
                    $disableAction[$row]->addEvent(
                        new WuiEvent(
                            'action',
                            'disablenode',
                            array(
                                'ntype' => Permissions::NODETYPE_PAGE,
                                'node' => $pageData['id'],
                                'gid' => $eventData['profileid']
                            )
                        )
                    );
                    $wuiDisableButton[$row] = new WuiButton(
                        'disablebutton'.$row,
                        array(
                            label => $innomaticLocale->getStr('disablenode_label'),
                            'themeimage' => 'lock',
                            'themeimagetype' => 'mini',
                            'horiz' => 'true',
                            'action' => $disableAction[$row]->getEventsCallString()
                        )
                    );
                    $wuiPageToolbar[$row]->addChild($wuiDisableButton[$row]);
                } else {
                    $enableAction[$row] = new WuiEventsCall();
                    $enableAction[$row]->addEvent(
                        new WuiEvent(
                            'view',
                            'editprofile',
                            array(
                                'profileid' => $eventData['profileid']
                            )
                        )
                    );
                    $enableAction[$row]->addEvent(
                        new WuiEvent(
                            'action',
                            'enablenode',
                            array(
                                'ntype' => Permissions::NODETYPE_PAGE,
                                'node' => $pageData['id'],
                                'gid' => $eventData['profileid']
                            )
                        )
                    );
                    $wuiEnableButton[$row] = new WuiButton(
                        'enablebutton'.$row,
                        array(
                            label => $innomaticLocale->getStr('enablenode_label'),
                            'themeimage' => 'unlock',
                            'themeimagetype' => 'mini',
                            'horiz' => 'true',
                            'action' => $enableAction[$row]->getEventsCallString()
                        )
                    );
                    $wuiPageToolbar[$row]->addChild($wuiEnableButton[$row]);
                }

                $wuiGroupsTable->addChild($wuiPageToolbar[$row], $row, 4);

                $row ++;
                $pagesQuery->moveNext();
            }

            $groupsQuery->moveNext();
        }

        $wuiMainFrame->addChild($wuiGroupsTable);
    }

    $wuiTitleBar->mTitle.= ' - '.$profData['groupname'].' - '.$innomaticLocale->getStr('editprofile_title');
}

$viewDispatcher->addEvent('newprofile', 'main_newprofile');
function main_newprofile($eventData)
{
    global $wuiMainFrame, $innomaticLocale, $wuiMainStatus, $wuiTitleBar;

    $wuiVGroup = new WuiVertgroup('vgroup');

    $wuiProfileGrid = new WuiGrid('newgroupgrid', array('rows' => '2', 'cols' => '2'));

    // Group fields
    //
    $wuiProfileGrid->addChild(
        new WuiLabel(
            'namelabel',
            array(
                'label' => $innomaticLocale->getStr('groupname_label').' (*)'
            )
        ),
        0, 0
    );
    $wuiProfileGrid->addChild(new WuiString('groupname', array('disp' => 'action')), 0, 1);

    $wuiVGroup->addChild($wuiProfileGrid);
    $wuiVGroup->addChild(
        new WuiSubmit(
            'submit1',
            array(
                'caption' => $innomaticLocale->getStr('newprofile_submit')
            )
        )
    );

    $wuiVGroup->addChild(
        new WuiHorizBar(
            'horizbar1'
        )
    );
    $wuiVGroup->addChild(
        new WuiLabel(
            'reqfieldslabel',
            array(
                'label' => $innomaticLocale->getStr('requiredfields_label')
            )
        )
    );

    $formEventsCall = new WuiEventsCall();
    $formEventsCall->addEvent(
        new WuiEvent(
            'action',
            'newgroup',
            ''
        )
    );
    $formEventsCall->addEvent(new WuiEvent('view', 'default', ''));

    $wuiForm = new WuiForm('newgroupform', array('action' => $formEventsCall->getEventsCallString()));
    $wuiForm->addChild($wuiVGroup);

    $wuiMainFrame->addChild($wuiForm);

    $wuiTitleBar->mTitle.= ' - '.$innomaticLocale->getStr('newgroup_title');
}

$viewDispatcher->addEvent('deleteprofile', 'main_deleteprofile');
function main_deleteprofile($eventData)
{
    global $wuiMainFrame, $innomaticLocale, $wuiTitleBar;

    $profQuery = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->execute(
        'SELECT * FROM domain_users_groups WHERE id='.$eventData['profileid']
    );

    $profData = $profQuery->getFields();

    $wuiVGroup = new WuiVertgroup('removereqvgroup', array('halign' => 'center', 'groupalign' => 'center'));

    $wuiHGroup1 = new WuiHorizgroup('removereqhgroup', array('align' => 'middle', 'width' => '0%'));
    $wuiHGroup1->addChild(
        new WuiLabel(
            'removereqlabel',
            array(
                'label' => sprintf(
                    $innomaticLocale->getStr('removeprofilequestion_label'),
                    $profData['groupname']
                )
            )
        )
    );

    $wuiVGroup->addChild($wuiHGroup1);

    $wuiHGroup2 = new WuiHorizgroup(
        'removereqhgroup',
        array(
            'align' => 'middle',
            'groupalign' => 'center'
        )
    );

    $removeAction = new WuiEventSCall();
    $removeAction->addEvent(
        new WuiEvent(
            'view',
            'default',
            ''
        )
    );
    $removeAction->addEvent(
        new WuiEvent(
            'action',
            'removegroup',
            array(
                'gid' => $eventData['profileid']
            )
        )
    );
    $removeButton = new WuiButton(
        'removebutton',
        array(
            'label' => $innomaticLocale->getStr('okremoveprofile_button'),
            'horiz' => 'true',
            'themeimage' => 'buttonok',
            'action' => $removeAction->getEventsCallString()
        )
    );
    $wuiHGroup2->addChild($removeButton);

    $wuiHGroup2->addChild($removeFrame);

    $remove2Action = new WuiEventSCall();
    $remove2Action->addEvent(
        new WuiEvent(
            'view',
            'default',
            ''
        )
    );
    $remove2Action->addEvent(
        new WuiEvent(
            'action',
            'removegroup',
            array(
                'gid' => $eventData['profileid'],
                'userstoo' => '1'
            )
        )
    );
    $remove2Button = new WuiButton(
        'remove2button',
        array(
            'label' => $innomaticLocale->getStr('okremoveprofileandusers_button'),
            'horiz' => 'true',
            'themeimage' => 'buttonok',
            'action' => $remove2Action->getEventsCallString()
        )
    );
    $wuiHGroup2->addChild($remove2Button);

    $wuiHGroup2->addChild($remove2Frame);

    $dontRemoveAction = new WuiEventsCall();
    $dontRemoveAction->addEvent(
        new WuiEvent(
            'view',
            'default',
            ''
        )
    );
    $dontRemoveButton = new WuiButton(
        'dontremovebutton',
        array(
            'label' => $innomaticLocale->getStr('dontremoveprofile_button'),
            'horiz' => 'true',
            'themeimage' => 'stop',
            'action' => $dontRemoveAction->getEventsCallString()
        )
    );
    $wuiHGroup2->addChild($dontRemoveButton);

    $okAction = new WuiEventsCall();
    $okAction->addEvent(new WuiEvent('view', 'default', ''));

    $wuiOkForm = new WuiForm('okform', array('action'));

    $wuiVGroup->addChild($wuiHGroup2);

    $wuiMainFrame->addChild($wuiVGroup);

    $wuiTitleBar->mTitle.= ' - '.$profData['profilename'].' - '.$innomaticLocale->getStr('removeprofile_title');
}

$viewDispatcher->addEvent('renameprofile', 'main_renameprofile');
function main_renameprofile($eventData)
{
    global $wuiMainFrame, $innomaticLocale, $wuiMainStatus, $wuiTitleBar;

    $profQuery = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->execute(
        'SELECT * FROM domain_users_groups WHERE id='.$eventData['profileid']
    );

    $profData = $profQuery->getFields();

    $wuiVGroup = new WuiVertgroup('vgroup');

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
            'groupname',
            array(
                'disp' => 'action',
                'value' => $profData['groupname']
            )
        ),
        0, 1
    );

    $wuiVGroup->addChild($wuiProfileGrid);
    $wuiVGroup->addChild(
        new WuiSubmit(
            'submit1',
            array(
                'caption' => $innomaticLocale->getStr('renameprofile_submit')
            )
        )
    );

    $wuiVGroup->addChild(new WuiHorizBar('horizbar1'));
    $wuiVGroup->addChild(
        new WuiLabel(
            'reqfieldslabel',
            array(
                'label' => $innomaticLocale->getStr('requiredfields_label')
            )
        )
    );

    $formEventsCall = new WuiEventsCall();
    $formEventsCall->addEvent(
        new WuiEvent(
            'action',
            'rengroup',
            array(
                'gid' => $eventData['profileid']
            )
        )
    );
    $formEventsCall->addEvent(new WuiEvent('view', 'default', ''));

    $wuiForm = new WuiForm('renameprofileform', array('action' => $formEventsCall->getEventsCallString()));
    $wuiForm->addChild($wuiVGroup);

    $wuiMainFrame->addChild($wuiForm);

    $wuiTitleBar->mTitle.= ' - '.$profData['groupname'].' - '.$innomaticLocale->getStr('renameprofile_title');
}

$viewDispatcher->addEvent('users', 'main_users');
function main_users($eventData)
{
    global $innomaticLocale, $wuiMainFrame, $wuiTitleBar;

    $usersQuery = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->execute(
        'SELECT id,username,fname,lname,email,groupid FROM domain_users ORDER BY username'
    );
    $profQuery = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->execute(
        'SELECT id,groupname FROM domain_users_groups ORDER BY groupname'
    );

    $profiles = array();
    while (!$profQuery->eof) {
        $profData = $profQuery->getFields();
        $profiles[$profData['id']] = $profData['groupname'];
        $profQuery->moveNext();
    }

    if ($usersQuery->getNumberRows()) {
        $headers[0]['label'] = $innomaticLocale->getStr('username_header');
        $headers[1]['label'] = $innomaticLocale->getStr('completename_header');
        $headers[2]['label'] = $innomaticLocale->getStr('email_header');
        $headers[3]['label'] = $innomaticLocale->getStr('userprofilename_header');

        $row = 0;

        $wuiUsersTable = new WuiTable('userstable', array('headers' => $headers));

        while (!$usersQuery->eof) {
            $userData = $usersQuery->getFields();

            $wuiUsersTable->addChild(
                new WuiLabel(
                    'usernamelabel'.$row,
                    array('label' => $userData['username'])
                ),
                $row, 0
            );
            $wuiUsersTable->addChild(
                new WuiLabel(
                    'completenamelabel'.$row,
                    array(
                        'label' => strcmp(
                            $userData['username'],
                            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDomainId()
                        )
                        != 0 ? $userData['lname'].' '.$userData['fname']
                        : $innomaticLocale->getStr('superuser_label')
                    )
                ),
                $row, 1
            );
            $wuiUsersTable->addChild(new WuiLabel('emaillabel'.$row, array('label' => $userData['email'])), $row, 2);
            $wuiUsersTable->addChild(
                new WuiLabel(
                    'userprofilelabel'.$row,
                    array(
                        'label' => ($userData['groupid'] != '0' and strlen($userData['groupid']))
                        ? $profiles[$userData['groupid']]
                        : $innomaticLocale->getStr('noprofileid_label')
                    )
                ),
                $row, 3
            );

            if (
                !User::isAdminUser(
                    $userData['username'],
                    \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDomainId()
                )
            ) {
                $wuiUserToolbar[$row] = new WuiHorizgroup('usertoolbar'.$row);

                $profileAction[$row] = new WuiEventsCall();
                $profileAction[$row]->addEvent(new WuiEvent('view', 'chprofile', array('userid' => $userData['id'])));
                $wuiProfileButton[$row] = new WuiButton(
                    'profilebutton'.$row,
                    array(
                        'label' => $innomaticLocale->getStr('chprofile_label'),
                        'themeimage' => 'listbulletleft',
                        'themeimagetype' => 'mini',
                        'horiz' => 'true',
                        'action' => $profileAction[$row]->getEventsCallString()
                    )
                );
                $wuiUserToolbar[$row]->addChild($wuiProfileButton[$row]);

                $chPasswdAction[$row] = new WuiEventsCall();
                $chPasswdAction[$row]->addEvent(
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
                        'themeimage' => 'documenttext',
                        'themeimagetype' => 'mini',
                        'horiz' => 'true', 'action' => $chPasswdAction[$row]->getEventsCallString()
                    )
                );
                $wuiUserToolbar[$row]->addChild($wuiChPasswdButton[$row]);

                $chDataAction[$row] = new WuiEventsCall();
                $chDataAction[$row]->addEvent(
                    new WuiEvent(
                        'view',
                        'edituser',
                        array(
                            'userid' => $userData['id']
                        )
                    )
                );
                $wuiChDataButton[$row] = new WuiButton(
                    'chdatabutton'.$row,
                    array(
                        'label' => $innomaticLocale->getStr('chdata_label'),
                        'themeimage' => 'documenttext',
                        'themeimagetype' => 'mini',
                        'horiz' => 'true',
                        'action' => $chDataAction[$row]->getEventsCallString()
                    )
                );
                $wuiUserToolbar[$row]->addChild($wuiChDataButton[$row]);

                $removeAction[$row] = new WuiEventsCall();
                $removeAction[$row]->addEvent(
                    new WuiEvent(
                        'view',
                        'deleteuser',
                        array(
                            'userid' => $userData['id']
                        )
                    )
                );
                $wuiRemoveButton[$row] = new WuiButton(
                    'removebutton'.$row,
                    array(
                        'label' => $innomaticLocale->getStr('removeuser_label'),
                        'themeimage' => 'trash',
                        'themeimagetype' => 'mini',
                        'horiz' => 'true',
                        'action' => $removeAction[$row]->getEventsCallString()
                    )
                );
                $wuiUserToolbar[$row]->addChild($wuiRemoveButton[$row]);

                $wuiUsersTable->addChild($wuiUserToolbar[$row], $row, 4);
            }

            $usersQuery->moveNext();
            $row ++;
        }

        $wuiMainFrame->addChild($wuiUsersTable);
    }

    $wuiTitleBar->mTitle.= ' - '.$innomaticLocale->getStr('users_title');
}

$viewDispatcher->addEvent('newuser', 'main_newuser');
function main_newuser($eventData)
{
    global $wuiMainFrame, $innomaticLocale, $wuiMainStatus, $wuiTitleBar;

    $profQuery = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->execute(
        'SELECT * FROM domain_users_groups'
    );

    $profiles = array();
    $profiles[0] = $innomaticLocale->getStr('noprofileid_label');
    while (!$profQuery->eof) {
        $profData = $profQuery->getFields();
        $profiles[$profData['id']] = $profData['groupname'];
        $profQuery->moveNext();
    }

    $wuiVGroup = new WuiVertgroup('vgroup');

    $wuiUserGrid = new WuiGrid('newusergrid', array('rows' => '7', 'cols' => '2'));

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
            'passwordalabel',
            array(
                'label' => $innomaticLocale->getStr('userpassworda_label').' (*)'
            )
        ),
        1, 0
    );
    $wuiUserGrid->addChild(
        new WuiString(
            'passworda',
            array(
                'disp' => 'action',
                'password' => 'true'
            )
        ),
        1, 1
    );

    $wuiUserGrid->addChild(
        new WuiLabel(
            'passwordblabel',
            array(
                'label' => $innomaticLocale->getStr('userpasswordb_label').' (*)'
            )
        ),
        2, 0
    );
    $wuiUserGrid->addChild(
        new WuiString(
            'passwordb',
            array(
                'disp' => 'action',
                'password' => 'true'
            )
        ),
        2, 1
    );

    $wuiUserGrid->addChild(
        new WuiLabel(
            'profilelabel',
            array(
                'label' => $innomaticLocale->getStr('usergroup_label').' (*)'
            )
        ),
        3, 0
    );
    $wuiUserGrid->addChild(
        new WuiComboBox(
            'groupid',
            array(
                'disp' => 'action',
                'elements' => $profiles
            )
        ),
        3, 1
    );

    $wuiUserGrid->addChild(
        new WuiLabel(
            'fnamelabel',
            array(
                'label' => $innomaticLocale->getStr('userfname_label')
            )
        ),
        4, 0
    );
    $wuiUserGrid->addChild(
        new WuiString(
            'fname',
            array(
                'disp' => 'action'
            )
        ),
        4, 1
    );

    $wuiUserGrid->addChild(
        new WuiLabel(
            'lnamelabel',
            array(
                'label' => $innomaticLocale->getStr('userlname_label')
            )
        ),
        5, 0
    );
    $wuiUserGrid->addChild(
        new WuiString(
            'lname',
            array(
                'disp' => 'action'
            )
        ),
        5, 1
    );

    $wuiUserGrid->addChild(
        new WuiLabel(
            'emaillabel',
            array(
                'label' => $innomaticLocale->getStr('email_label')
            )
        ),
        6, 0
    );
    $wuiUserGrid->addChild(
        new WuiString(
            'email',
            array(
                'disp' => 'action'
            )
        ),
        6, 1
    );

    $wuiUserGrid->addChild(
        new WuiLabel(
            'otherlabel',
            array(
                'label' => $innomaticLocale->getStr('userother_label')
            )
        ),
        7, 0
    );
    $wuiUserGrid->addChild(
        new WuiText(
            'other',
            array(
                'disp' => 'action',
                'rows' => '5',
                'cols' => '80'
            )
        ),
        7, 1
    );

    $wuiVGroup->addChild($wuiUserGrid);
    $wuiVGroup->addChild(
        new WuiSubmit(
            'submit1',
            array(
                'caption' => $innomaticLocale->getStr('newuser_submit')
            )
        )
    );

    $wuiVGroup->addChild(new WuiHorizBar('horizbar1'));
    $wuiVGroup->addChild(
        new WuiLabel(
            'reqfieldslabel',
            array(
                'label' => $innomaticLocale->getStr('requiredfields_label')
            )
        )
    );

    $formEventsCall = new WuiEventsCall();
    $formEventsCall->addEvent(
        new WuiEvent(
            'action',
            'adduser',
            ''
        )
    );
    $formEventsCall->addEvent(
        new WuiEvent(
            'view',
            'users',
            ''
        )
    );

    $wuiForm = new WuiForm(
        'newuserform',
        array(
            'action' => $formEventsCall->getEventsCallString()
        )
    );
    $wuiForm->addChild($wuiVGroup);

    $wuiMainFrame->addChild($wuiForm);

    $wuiTitleBar->mTitle.= ' - '.$innomaticLocale->getStr('newuser_title');
}

$viewDispatcher->addEvent('deleteuser', 'main_deleteuser');
function main_deleteuser($eventData)
{
    global $wuiMainFrame, $innomaticLocale, $wuiTitleBar;

    $userQuery = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->execute(
        'SELECT * FROM domain_users WHERE id='.$eventData['userid']
    );

    $userData = $userQuery->getFields();

    $wuiVGroup = new WuiVertgroup(
        'removereqvgroup',
        array(
            'halign' => 'center',
            'groupalign' => 'center'
        )
    );

    $wuiHGroup1 = new WuiHorizgroup(
        'removereqhgroup',
        array(
            'align' => 'middle',
            'width' => '0%'
        )
    );
    $wuiHGroup1->addChild(
        new WuiLabel(
            'removereqlabel',
            array(
                'label' => sprintf(
                    $innomaticLocale->getStr('removeuserquestion_label'),
                    $userData['username']
                )
            )
        )
    );

    $wuiVGroup->addChild($wuiHGroup1);

    $wuiHGroup2 = new WuiHorizgroup(
        'removereqhgroup',
        array(
            'align' => 'middle',
            'groupalign' => 'center'
        )
    );

    $removeAction = new WuiEventSCall();
    $removeAction->addEvent(new WuiEvent('view', 'users', ''));
    $removeAction->addEvent(new WuiEvent('action', 'removeuser', array('uid' => $eventData['userid'])));
    $removeButton = new WuiButton(
        'removebutton',
        array(
            'label' => $innomaticLocale->getStr('okremoveuser_button'),
            'horiz' => 'true',
            'themeimage' => 'buttonok',
            'action' => $removeAction->getEventsCallString()
        )
    );
    $wuiHGroup2->addChild($removeButton);

    $dontRemoveAction = new WuiEventsCall();
    $dontRemoveAction->addEvent(new WuiEvent('view', 'users', ''));
    $dontRemoveButton = new WuiButton(
        'dontremovebutton',
        array(
            'label' => $innomaticLocale->getStr('dontremoveuser_button'),
            'horiz' => 'true',
            'themeimage' => 'stop',
            'action' => $dontRemoveAction->getEventsCallString()
        )
    );
    $wuiHGroup2->addChild($dontRemoveButton);

    $okAction = new WuiEventsCall();
    $okAction->addEvent(new WuiEvent('view', 'default', ''));

    $wuiOkForm = new WuiForm('okform', array('action'));

    $wuiVGroup->addChild($wuiHGroup2);

    $wuiMainFrame->addChild($wuiVGroup);

    $wuiTitleBar->mTitle.= ' - '.$userData['username'].' - '.$innomaticLocale->getStr('removeuser_title');
}

$viewDispatcher->addEvent('edituser', 'main_edituser');
function main_edituser($eventData)
{
    global $wuiMainFrame, $innomaticLocale, $wuiMainStatus, $wuiTitleBar;

    $userQuery = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->execute(
        'SELECT * FROM domain_users WHERE id='.$eventData['userid']
    );
    $userData = $userQuery->getFields();

    $profQuery = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->execute(
        'SELECT * FROM domain_users_groups'
    );

    $profiles = array();
    $profiles[0] = $innomaticLocale->getStr('noprofileid_label');
    while (!$profQuery->eof) {
        $profData = $profQuery->getFields();
        $profiles[$profData['id']] = $profData['groupname'];
        $profQuery->moveNext();
    }

    $wuiVGroup = new WuiVertgroup('vgroup');

    $wuiUserGrid = new WuiGrid('editusergrid', '');

    // User fields
    //
    $wuiUserGrid->addChild(
        new WuiLabel(
            'fnamelabel',
            array(
                'label' => $innomaticLocale->getStr('userfname_label')
            )
        ),
        0, 0
    );
    $wuiUserGrid->addChild(
        new WuiString(
            'fname',
            array(
                'disp' => 'action',
                'value' => $userData['fname']
            )
        ),
        0, 1
    );

    $wuiUserGrid->addChild(
        new WuiLabel(
            'lnamelabel',
            array(
                'label' => $innomaticLocale->getStr('userlname_label')
            )
        ),
        1, 0
    );
    $wuiUserGrid->addChild(
        new WuiString(
            'lname',
            array(
                'disp' => 'action',
                'value' => $userData['lname']
            )
        ),
        1, 1
    );

    $wuiUserGrid->addChild(
        new WuiLabel(
            'emaillabel',
            array(
                'label' => $innomaticLocale->getStr('email_label')
            )
        ),
        2, 0
    );
    $wuiUserGrid->addChild(
        new WuiString(
            'email',
            array(
                'disp' => 'action',
                'value' => $userData['email']
            )
        ),
        2, 1
    );

    $wuiUserGrid->addChild(
        new WuiLabel(
            'otherlabel',
            array(
                'label' => $innomaticLocale->getStr('userother_label')
            )
        ),
        3, 0
    );
    $wuiUserGrid->addChild(
        new WuiText(
            'other',
            array(
                'disp' => 'action',
                'rows' => '5',
                'cols' => '80',
                'value' => $userData['otherdata']
            )
        ),
        3, 1
    );

    $wuiVGroup->addChild($wuiUserGrid);
    $wuiVGroup->addChild(
        new WuiSubmit(
            'submit1',
            array(
                'caption' => $innomaticLocale->getStr('edituser_submit')
            )
        )
    );

    $wuiVGroup->addChild(new WuiHorizBar('horizbar1'));
    $wuiVGroup->addChild(
        new WuiLabel(
            'reqfieldslabel',
            array(
                'label' => $innomaticLocale->getStr('requiredfields_label')
            )
        )
    );

    $formEventsCall = new WuiEventsCall();
    $formEventsCall->addEvent(
        new WuiEvent(
            'action',
            'edituser',
            array(
                'uid' => $eventData['userid'],
                'groupid' => $userData['groupid'],
                'username' => $userData['username']
            )
        )
    );
    $formEventsCall->addEvent(new WuiEvent('view', 'users', ''));

    $wuiForm = new WuiForm(
        'newuserform',
        array(
            'action' => $formEventsCall->getEventsCallString()
        )
    );
    $wuiForm->addChild($wuiVGroup);

    $wuiMainFrame->addChild($wuiForm);

    $wuiTitleBar->mTitle.= ' - '.$userData['username'].' - '.$innomaticLocale->getStr('edituser_title');
}

$viewDispatcher->addEvent('chpassword', 'main_chpassword');
function main_chpassword($eventData)
{
    global $wuiMainFrame, $innomaticLocale, $wuiMainStatus, $wuiTitleBar;

    $userQuery = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->execute(
        'SELECT * FROM domain_users WHERE id='.$eventData['userid']
    );
    $userData = $userQuery->getFields();

    $wuiVGroup = new WuiVertgroup('vgroup');

    $wuiUserGrid = new WuiGrid('chpasswdgrid', array('rows' => '2', 'cols' => '2'));

    // User fields
    //
    $wuiUserGrid->addChild(
        new WuiLabel(
            'pwdlabel',
            array(
                'label' => $innomaticLocale->getStr('chpassword_label').' (*)'
            )
        ),
        0, 0
    );
    $wuiUserGrid->addChild(
        new WuiString(
            'password',
            array(
                'disp' => 'action',
                'password' => 'true'
            )
        ),
        0, 1
    );

    $wuiVGroup->addChild($wuiUserGrid);
    $wuiVGroup->addChild(
        new WuiSubmit(
            'submit1',
            array(
                'caption' => $innomaticLocale->getStr('chpasswd_submit')
            )
        )
    );

    $wuiVGroup->addChild(new WuiHorizBar('horizbar1'));
    $wuiVGroup->addChild(
        new WuiLabel(
            'reqfieldslabel',
            array(
                'label' => $innomaticLocale->getStr('requiredfields_label')
            )
        )
    );

    $formEventsCall = new WuiEventsCall();
    $formEventsCall->addEvent(
        new WuiEvent(
            'action',
            'chpasswd',
            array(
                'uid' => $eventData['userid']
            )
        )
    );
    $formEventsCall->addEvent(new WuiEvent('view', 'users', ''));

    $wuiForm = new WuiForm(
        'chpasswdform',
        array(
            'action' => $formEventsCall->getEventsCallString()
        )
    );
    $wuiForm->addChild($wuiVGroup);

    $wuiMainFrame->addChild($wuiForm);

    $wuiTitleBar->mTitle.= ' - '.$userData['username'].' - '.$innomaticLocale->getStr('chpasswd_title');
}

$viewDispatcher->addEvent('chprofile', 'main_chprofile');
function main_chprofile($eventData)
{
    global $wuiMainFrame, $innomaticLocale, $wuiMainStatus, $wuiTitleBar;

    $userQuery = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->execute(
        'SELECT * FROM domain_users WHERE id='.$eventData['userid'].' '
    );
    $userData = $userQuery->getFields();

    $profQuery = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->execute(
        'SELECT * FROM domain_users_groups ORDER BY groupname'
    );

    $profiles = array();
    $profiles[0] = $innomaticLocale->getStr('noprofileid_label');
    while (!$profQuery->eof) {
        $profData = $profQuery->getFields();
        $profiles[$profData['id']] = $profData['groupname'];
        $profQuery->moveNext();
    }

    $wuiVGroup = new WuiVertgroup('vgroup');

    $wuiUserGrid = new WuiGrid('chprofilegrid', array('rows' => '2', 'cols' => '2'));

    // User fields
    //
    $wuiUserGrid->addChild(
        new WuiLabel(
            'profilelabel',
            array(
                'label' => $innomaticLocale->getStr('changeprofile_label').' (*)'
            )
        ),
        0, 0
    );
    $wuiUserGrid->addChild(
        new WuiComboBox(
            'profileid',
            array(
                'disp' => 'action',
                'elements' => $profiles,
                'default' => $userData['groupid']
            )
        ),
        0, 1
    );

    $wuiVGroup->addChild($wuiUserGrid);
    $wuiVGroup->addChild(
        new WuiSubmit(
            'submit1',
            array(
                'caption' => $innomaticLocale->getStr('chprofile_submit')
            )
        )
    );

    $wuiVGroup->addChild(new WuiHorizBar('horizbar1'));
    $wuiVGroup->addChild(
        new WuiLabel(
            'reqfieldslabel',
            array(
                'label' => $innomaticLocale->getStr('requiredfields_label')
            )
        )
    );

    $formEventsCall = new WuiEventsCall();
    $formEventsCall->addEvent(new WuiEvent('action', 'chprofile', array('uid' => $eventData['userid'])));
    $formEventsCall->addEvent(new WuiEvent('view', 'users', ''));

    $wuiForm = new WuiForm('chprofileform', array('action' => $formEventsCall->getEventsCallString()));
    $wuiForm->addChild($wuiVGroup);

    $wuiMainFrame->addChild($wuiForm);

    $wuiTitleBar->mTitle.= ' - '.$userData['username'].' - '.$innomaticLocale->getStr('chprofile_title');
}

if (
    User::isAdminUser(
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserName(),
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDomainId()
    )
) {
    $viewDispatcher->addEvent('motd', 'main_motd');
    function main_motd($eventData)
    {
        global $wuiTitleBar, $wuiMainFrame, $innomaticLocale;

        $domain = new \Innomatic\Domain\Domain(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDomainId(),
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()
        );

        $xmlDef = '<vertgroup><name>motd</name>
          <children>

            <form><name>motd</name>
              <args>
                <method>post</method>
                <action type="encoded">'
                .urlencode(
                    WuiEventsCall::buildEventsCallString(
                        '',
                        array(
                            array('view', 'motd', ''),
                            array('action', 'setmotd', '')
                        )
                    )
                )
                .'</action>
              </args>
              <children>

                <grid><name>motd</name>

                  <children>

                    <label row="0" col="0" halign="" valign="top"><name>label</name>
                      <args>
                        <label type="encoded">'.urlencode($innomaticLocale->getStr('motd.label')).'</label>
                      </args>
                    </label>

                    <text row="0" col="1"><name>motd</name>
                      <args>
                        <rows>10</rows>
                        <cols>80</cols>
                        <disp>action</disp>
                        <value type="encoded">'.urlencode($domain->getMotd()).'</value>
                      </args>
                    </text>

                  </children>

                </grid>

              </children>
            </form>

            <horizbar><name>hb</name></horizbar>

            <button>
              <name>apply</name>
              <args>
                <horiz>true</horiz>
                <frame>false</frame>
                <themeimage>buttonok</themeimage>
                <label type="encoded">'.urlencode($innomaticLocale->getStr('set_motd.submit')).'</label>
                <formsubmit>motd</formsubmit>
                <action type="encoded">'
                .urlencode(
                    WuiEventsCall::buildEventsCallString(
                        '',
                        array(
                            array('view', 'motd', ''),
                            array('action', 'setmotd', '')
                        )
                    )
                )
                .'</action>
              </args>
            </button>

          </children>
        </vertgroup>';
        $wuiMainFrame->addChild(new WuiXml('page', array('definition' => $xmlDef)));

        $wuiTitleBar->mTitle.= ' - '.$innomaticLocale->getStr('motd.title');
    }
}

$viewDispatcher->addEvent('help', 'main_help');
function main_help($eventData)
{
    global $wuiTitleBar, $wuiMainFrame, $innomaticLocale;
    $wuiTitleBar->mTitle.= ' - '.$innomaticLocale->getStr('help_title');
    $wuiMainFrame->addChild(
        new WuiHelpNode(
            'profiles_help',
            array(
                'base' => 'innomatic',
                'node' => 'innomatic.domain.profiles.'.$eventData['node'].'.html',
                'language' => \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getLanguage()
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
