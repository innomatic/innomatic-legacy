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

use \Shared\Wui;
use \Innomatic\Core\InnomaticContainer;

$wui = \Innomatic\Wui\Wui::instance('\Innomatic\Wui\Wui');

$app_cfg = new \Innomatic\Application|ApplicationSettings(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(), 'innomatic');

$wuiPage = new WuiPage('page', array('title' => 'Innomatic'));
$wui_vertgroup = new WuiVertgroup('vertgroup', array('align' => 'center', 'groupalign' => 'center', 'groupvalign' => 'middle', 'height' => '100%'));
$wui_buttons_group = new WuiVertgroup('buttons_group', array('align' => 'center', 'groupalign' => 'center', 'groupvalign' => 'middle', 'height' => '0%'));
if ($app_cfg->getKey('innomatic-biglogo-disabled') != '1') {
    if (\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getEdition() == \Innomatic\Core\InnomaticContainer::EDITION_SAAS)
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
    if (strlen($serviceprovider_biglogo_filename) and file_exists(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome().'shared/'.$serviceprovider_biglogo_filename)) {
        $serviceprovider_button = new WuiButton('serviceproviderbutton', array('action' => strlen($serviceprovider_url) ? $serviceprovider_url : ' http://www.innomatic.io', 'target' => '_top', 'image' => \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getBaseUrl(false).'/shared/'.$serviceprovider_biglogo_filename, 'highlight' => 'false'));
        $wui_buttons_group->addChild($serviceprovider_button);
    }
}

// MOTD
//
$domain = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain();
$motd = $domain->getMotd();

$wui_buttons_group->addChild(new WuiLabel('motd', array('nowrap' => 'false', 'bold' => 'true', 'label' => $motd)));

$wui_vertgroup->addChild($wui_buttons_group);

$wuiPage->addChild($wui_vertgroup);
$wui->addChild($wuiPage);
$wui->render();
