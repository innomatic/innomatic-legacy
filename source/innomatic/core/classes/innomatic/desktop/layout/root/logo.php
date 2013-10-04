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

require_once('innomatic/wui/Wui.php');

$wui = Wui::instance('wui');
$wui->loadWidget('button');
$wui->loadWidget('horizframe');
$wui->loadWidget('horizgroup');
$wui->loadWidget('label');
$wui->loadWidget('link');
$wui->loadWidget('page');
$wui->loadWidget('vertframe');
$wui->loadWidget('vertgroup');

require_once('innomatic/desktop/layout/DesktopLayout.php');
$layout_mode = DesktopLayout::instance('desktoplayout')->getLayout();

$wuiPage = new WuiPage('page', array('title' => 'Innomatic'. (strlen(InnomaticContainer::instance('innomaticcontainer')->getPlatformName()) ? ' - '.InnomaticContainer::instance('innomaticcontainer')->getPlatformName(). (strlen(InnomaticContainer::instance('innomaticcontainer')->getPlatformGroup()) ? '.'.InnomaticContainer::instance('innomaticcontainer')->getPlatformGroup() : '') : ''), 'border' => 'false'));
if ($layout_mode == 'horiz') {
    $wuiMainVertGroup = new WuiHorizGroup('mainvertgroup', array('groupvalign' => 'middle', 'width' => '100%'));
} else {
    $wuiMainVertGroup = new WuiVertGroup('mainvertgroup', array('align' => 'center'));
}

$wuiMainVertGroup->addChild(new WuiButton('innomaticlogo', array('action' => InnomaticContainer::instance('innomaticcontainer')->getBaseUrl().'/', 'target' => '_top', 'image' => $wuiPage->mThemeHandler->mStyle['headerlogo'], 'highlight' => 'false', 'compact' => 'true')));

$label_text = strlen(InnomaticContainer::instance('innomaticcontainer')->getPlatformName()) ? InnomaticContainer::instance('innomaticcontainer')->getPlatformName(). (strlen(InnomaticContainer::instance('innomaticcontainer')->getPlatformGroup()) ? '.'.InnomaticContainer::instance('innomaticcontainer')->getPlatformGroup() : '') : '';
if (strlen($label_text)) {
    $wuiMainVertGroup->addChild(new WuiLabel('label', array('label' => $label_text)));
}

if ($layout_mode == 'vert') {
        $wuiMainVertGroup->addChild(new WuiLabel('logout', array('label' => ' ')));
}

require_once('innomatic/wui/dispatch/WuiEvent.php');
    require_once('innomatic/wui/dispatch/WuiEventsCall.php');
    require_once('innomatic/locale/LocaleCatalog.php');
    $innomatic_menu_locale = new LocaleCatalog('innomatic::root_menu', InnomaticContainer::instance('innomaticcontainer')->getLanguage());

    $buttons_group = new WuiHorizGroup('buttons', array('groupalign' => 'center'));
if ($layout_mode == 'horiz') {
    $buttons_group->addChild(new WuiButton('logout', array('label' => $innomatic_menu_locale->getStr('domainadmin'), 'horiz' => 'true', 'action' => InnomaticContainer::instance('innomaticcontainer')->getBaseUrl().'/domain/', 'target' => 'parent', 'compact' => 'true', 'themeimage' => 'desktop', 'themeimagetype' => 'mini', 'highlight' => 'false')));
}
    $logout_events_call = new WuiEventsCall(WebAppContainer::instance('webappcontainer')->getProcessor()->getRequest()->getUrlPath().'/root');
    $logout_events_call->addEvent(new WuiEvent('login', 'logout', ''));

    $buttons_group->addChild(new WuiButton('logout', array('label' => $innomatic_menu_locale->getStr('logout'), 'horiz' => 'true', 'action' => $logout_events_call->getEventsCallString(), 'target' => 'parent', 'compact' => 'true', 'themeimage' => 'exit', 'themeimagetype' => 'mini', 'highlight' => 'false')));

$wuiMainVertGroup->addChild($buttons_group);
$wuiPage->addChild($wuiMainVertGroup);
$wui->addChild($wuiPage);
$wui->render();
?>
