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
require_once('innomatic/application/ApplicationSettings.php');
require_once('innomatic/domain/Domain.php');

$wui = Wui::instance('wui');
$wui->loadWidget('button');
$wui->loadWidget('empty');
$wui->loadWidget('grid');
$wui->loadWidget('horizframe');
$wui->loadWidget('horizgroup');
$wui->loadWidget('image');
$wui->loadWidget('label');
$wui->loadWidget('link');
$wui->loadWidget('page');
$wui->loadWidget('vertframe');
$wui->loadWidget('vertgroup');

$app_cfg = new ApplicationSettings(InnomaticContainer::instance('innomaticcontainer')->getDataAccess(), 'innomatic');

$wuiPage = new WuiPage('page', array('title' => 'Innomatic', 'border' => 'false'));
$wui_vertgroup = new WuiVertGroup('vertgroup', array('align' => 'center', 'groupalign' => 'center', 'groupvalign' => 'middle', 'height' => '100%'));
$wui_buttons_group = new WuiVertGroup('buttons_group', array('align' => 'center', 'groupalign' => 'center', 'groupvalign' => 'middle', 'height' => '0%'));
if ($app_cfg->getKey('innomatic-biglogo-disabled') != '1') {
    if (InnomaticContainer::instance('innomaticcontainer')->getEdition() == InnomaticContainer::EDITION_ASP)
        $edition = '_asp';
    else
        $edition = '_enterprise';

    if (isset($wuiPage->mThemeHandler->mStyle['biglogo'.$edition]))
        $biglogo_image = $wuiPage->mThemeHandler->mStyle['biglogo'.$edition];
    else
        $biglogo_image = $wuiPage->mThemeHandler->mStyle['biglogo'];

    $wui_button = new WuiButton('button', array('action' => ' http://www.innomatic.org', 'target' => '_top', 'image' => $biglogo_image, 'highlight' => 'false'));
    $wui_buttons_group->addChild($wui_button);
}

// Service Provider personalization
//
$serviceprovider_biglogo_filename = $app_cfg->getKey('serviceprovider-biglogo-filename');
$serviceprovider_url = $app_cfg->getKey('serviceprovider-url');

if ($app_cfg->getKey('serviceprovider-biglogo-disabled') != '1') {
    if (strlen($serviceprovider_biglogo_filename) and file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'shared/'.$serviceprovider_biglogo_filename)) {
        $serviceprovider_button = new WuiButton('serviceproviderbutton', array('action' => strlen($serviceprovider_url) ? $serviceprovider_url : ' http://www.innoteam.it', 'target' => '_top', 'image' => InnomaticContainer::instance('innomaticcontainer')->getBaseUrl(false).'/shared/'.$serviceprovider_biglogo_filename, 'highlight' => 'false'));
        $wui_buttons_group->addChild($serviceprovider_button);
    }
}

// MOTD
//
$domain = InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain();
$motd = $domain->getMotd();

$wui_buttons_group->addChild(new WuiLabel('motd', array('nowrap' => 'false', 'bold' => 'true', 'label' => $motd)));

$wui_vertgroup->addChild($wui_buttons_group);

$wuiPage->addChild($wui_vertgroup);
$wui->addChild($wuiPage);
$wui->render();
?>