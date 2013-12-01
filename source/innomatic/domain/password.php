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

use \Innomatic\Core\InnomaticContainer;
use \Innomatic\Wui\Wui;
use \Innomatic\Wui\Widgets;
use \Innomatic\Wui\Dispatch;
use \Innomatic\Locale\LocaleCatalog;
use \Innomatic\Domain\User;
use \Shared\Wui;

global $wuiMainStatus, $wuiMainFrame, $innomaticLocale, $wuiTitleBar;

$log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
$innomaticLocale = new LocaleCatalog(
    'innomatic::domain_password',
    InnomaticContainer::instance(
        'innomaticcontainer'
    )->getCurrentUser()->getLanguage()
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

$wuiPage = new WuiPage(
    'page',
    array('title' => $innomaticLocale->getStr('password_title'))
);
$wuiMainVertGroup = new WuiVertgroup('mainvertgroup');
$wuiTitleBar = new WuiTitleBar(
    'titlebar',
    array(
        'title' => $innomaticLocale->getStr('password_title'),
        'icon' => 'key'
    )
);
$wuiMainVertGroup->addChild($wuiTitleBar);

// Main tool bar
//
$wuiMainToolbar = new WuiToolBar('maintoolbar');

$homeAction = new WuiEventsCall();
$homeAction->addEvent(new WuiEvent('view', 'default', ''));
$wuiHomeButton = new WuiButton(
    'homebutton',
    array(
        'label' => $innomaticLocale->getStr('chpasswd_button'),
        'themeimage' => 'password',
        'horiz' => 'true',
        'action' => $homeAction->getEventsCallString()
    )
);
$wuiMainToolbar->addChild($wuiHomeButton);

// Help tool bar
//
$wuiHelpToolBar = new WuiToolBar('helpbar');

$viewDispatcher = new WuiDispatcher('view');
$eventName = $viewDispatcher->getEventName();

if (strcmp($eventName, 'help')) {
    $helpAction = new WuiEventsCall();
    $helpAction->addEvent(
        new WuiEvent('view', 'help', array('node' => $eventName))
    );
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

$wuiToolBarFrame->addChild($wuiMainToolbar);
$wuiToolBarFrame->addChild($wuiHelpToolBar);
$wuiMainVertGroup->addChild($wuiToolBarFrame);

$wuiMainFrame = new WuiHorizframe('mainframe');
$wuiMainStatus = new WuiStatusBar('mainstatusbar');

// Pass dispatcher
//
$actionDispatcher = new WuiDispatcher('action');

$actionDispatcher->addEvent('edit', 'pass_edit');
function pass_edit($eventData)
{
    global $wuiMainStatus, $innomaticLocale;

    if ($eventData['newpassworda'] == $eventData['newpasswordb']) {
        if (strlen($eventData['newpassworda'])) {
            $tempUser = new User(
                InnomaticContainer::instance(
                    'innomaticcontainer'
                )->getCurrentDomain()->domaindata['id']
            );
            $tempUser->setUserIdByUsername(
                InnomaticContainer::instance(
                    'innomaticcontainer'
                )->getCurrentUser()->getUserName()
            );

            $userData = $tempUser->getUserData();

            if (md5($eventData['oldpassword']) == $userData['password']) {
                $tempUser->changePassword($eventData['newpassworda']);
                $wuiMainStatus->mArgs['status'] = $innomaticLocale->getStr(
                    'passwordchanged_status'
                );
            } else
                $wuiMainStatus->mArgs['status'] = $innomaticLocale->getStr(
                    'wrongoldpassword_status'
                );
        } else
            $wuiMainStatus->mArgs['status'] = $innomaticLocale->getStr(
                'newpasswordisempty_status'
            );
    } else
        $wuiMainStatus->mArgs['status'] = $innomaticLocale->getStr(
            'newpasswordnomatch_status'
        );
}

$actionDispatcher->Dispatch();

// Main dispatcher
//
$viewDispatcher = new WuiDispatcher('view');

$viewDispatcher->addEvent('default', 'main_default');
function main_default($eventData)
{
    global $wuiMainFrame, $innomaticLocale, $wuiTitleBar;

    $wuiGrid = new WuiGrid('grid', array('rows' => '3', 'cols' => '2'));

    $wuiGrid->addChild(
        new WuiLabel(
            'oldpasswordlabel',
            array(
                'label' => $innomaticLocale->getStr('rootpasswordold_label')
            )
        ),
        0,
        0
    );
    $wuiGrid->addChild(new WuiString('oldpassword', array('disp' => 'action', 'password' => 'true')), 0, 1);

    $wuiGrid->addChild(
        new WuiLabel(
            'newpasswordalabel',
            array(
                'label' => $innomaticLocale->getStr('rootpassworda_label')
            )
        ),
        1,
        0
    );
    $wuiGrid->addChild(new WuiString('newpassworda', array('disp' => 'action', 'password' => 'true')), 1, 1);

    $wuiGrid->addChild(
        new WuiLabel(
            'newpasswordblabel',
            array(
                'label' => $innomaticLocale->getStr('rootpasswordb_label')
            )
        ),
        2,
        0
    );
    $wuiGrid->addChild(new WuiString('newpasswordb', array('disp' => 'action', 'password' => 'true')), 2, 1);

    $wuiVGroup = new WuiVertgroup('vertgroup', array('align' => 'center'));
    $wuiVGroup->addChild($wuiGrid);

    $wuiVGroup->addChild(
        new WuiSubmit(
            'submit',
            array(
                'caption' => $innomaticLocale->getStr('rootpasschange_submit')
            )
        )
    );

    $formEventsCall = new WuiEventsCall();
    $formEventsCall->addEvent(new WuiEvent('view', 'default', ''));
    $formEventsCall->addEvent(new WuiEvent('action', 'edit', ''));

    $wuiForm = new WuiForm(
        'form',
        array('action' => $formEventsCall->getEventsCallString())
    );
    $wuiForm->addChild($wuiVGroup);

    $wuiMainFrame->addChild($wuiForm);
}

$viewDispatcher->addEvent('help', 'main_help');
function main_help($eventData)
{
    global $wuiTitleBar, $wuiMainFrame, $innomaticLocale;
    $wuiTitleBar->mTitle.= ' - '.$innomaticLocale->getStr('help_title');
    $wuiMainFrame->addChild(
        new WuiHelpNode(
            'password_help',
            array(
                'base' => 'innomatic',
                'node' => 'innomatic.domain.password.' . $eventData['node']
                . '.html',
                'language' => InnomaticContainer::instance(
                    'innomaticcontainer'
                )->getCurrentUser()->getLanguage()
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
