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

require_once('innomatic/locale/LocaleCatalog.php');
require_once('innomatic/wui/Wui.php');
require_once('innomatic/wui/dispatch/WuiDispatcher.php');

WebAppContainer::instance('webappcontainer')->getProcessor()->getResponse()->addHeader('P3P', 'CP="CUR ADM OUR NOR STA NID"');
WebAppContainer::instance('webappcontainer')->getProcessor()->getResponse()->addHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');
WebAppContainer::instance('webappcontainer')->getProcessor()->getResponse()->addHeader('Last-Modified', gmdate('D, d M Y H:i:s'));
WebAppContainer::instance('webappcontainer')->getProcessor()->getResponse()->addHeader('Cache-control', 'no-cache, must-revalidate');
WebAppContainer::instance('webappcontainer')->getProcessor()->getResponse()->addHeader('Pragma', 'no-cache');

function setup_entry($wui_page, &$progress, $phases, $phaseMark, $phaseCompleted, $phaseName, &$wui_table, $row) {
    if (file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/'.$phaseMark)) {
        $ball_icon = $wui_page->mThemeHandler->mStyle['goldball'];
        $font_color = 'yellow';
        $pre = '<b>';
        $post = '</b>';
    } else if (!file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/'.$phaseCompleted)) {
        $ball_icon = $wui_page->mThemeHandler->mStyle['redball'];
        $font_color = 'black';
        $pre = '';
        $post = '';
    } else {
        $ball_icon = $wui_page->mThemeHandler->mStyle['greenball'];
        $font_color = 'black';
        $pre = '';
        $post = '';
        $progress = $row +1;
    }

    $wui_table->addChild(new WuiImage('statusimage'.$row, array('imageurl' => $ball_icon)), $row, 0);
    $wui_table->addChild(new WuiLabel('phaselabel'.$row, array('label' => $pre.$phaseName.$post, 'nowrap' => 'false')), $row, 1);
}

// Checks if Innomatic is in setup phase
//
/*
 if (InnomaticContainer::instance('innomaticcontainer')->getState() != InnomaticContainer::STATE_SETUP) {
 }
 */
$progress = 0;

$innomaticLocale = new LocaleCatalog('innomatic::setup', InnomaticContainer::instance('innomaticcontainer')->getLanguage());

$wui = Wui::instance('wui', true);
$wui->loadWidget('image');
$wui->loadWidget('label');
$wui->loadWidget('table');
$wui->loadWidget('page');
$wui->loadWidget('vertgroup');
$wui->loadWidget('vertframe');
$wui->loadWidget('treemenu');
$wui->loadWidget('progressbar');

$wuiPage = new WuiPage('page', array('title' => 'Innomatic'. (strlen(WebAppContainer::instance('webappcontainer')->getProcessor()->getRequest()->getServerName()) ? ' - '.WebAppContainer::instance('webappcontainer')->getProcessor()->getRequest()->getServerName(). (strlen(InnomaticContainer::instance('innomaticcontainer')->getPlatformGroup()) ? '.'.InnomaticContainer::instance('innomaticcontainer')->getPlatformGroup() : '') : '')));
$wuiPage->mArgs['background'] = $wuiPage->mThemeHandler->mStyle['menuback'];
$wuiMainVertGroup = new WuiVertGroup('mainvertgroup');

$headers[1]['label'] = $innomaticLocale->getStr('setupphase_header');

$wui_table = new WuiTable('sumtable', array('headers' => $headers));

$phase = 0;
$phases = 13;

setup_entry($wuiPage, $progress, $phases, 'setup_checkingsystem', 'setup_systemchecked', $innomaticLocale->getStr('systemcheckphase_label'), $wui_table, $phase ++);
setup_entry($wuiPage, $progress, $phases, 'setup_installingfiles', 'setup_filesinstalled', $innomaticLocale->getStr('filesinstallphase_label'), $wui_table, $phase ++);
setup_entry($wuiPage, $progress, $phases, 'setup_settingedition', 'setup_editionset', $innomaticLocale->getStr('editionchoicephase_label'), $wui_table, $phase ++);
setup_entry($wuiPage, $progress, $phases, 'setup_creatingdataaccessdrivers', 'setup_dataaccessdriverscreated', $innomaticLocale->getStr('dataaccessdriversphase_label'), $wui_table, $phase ++);
setup_entry($wuiPage, $progress, $phases, 'setup_creatingdb', 'setup_dbcreated', $innomaticLocale->getStr('rootdaphase_label'), $wui_table, $phase ++);
setup_entry($wuiPage, $progress, $phases, 'setup_initializingcomponents', 'setup_componentsinitialized', $innomaticLocale->getStr('componentsphase_label'), $wui_table, $phase ++);
setup_entry($wuiPage, $progress, $phases, 'setup_settinginnomatichost', 'setup_innomatichostset', $innomaticLocale->getStr('innomatichostchoicephase_label'), $wui_table, $phase ++);
setup_entry($wuiPage, $progress, $phases, 'setup_settingcountry', 'setup_countryset', $innomaticLocale->getStr('countrychoicephase_label'), $wui_table, $phase ++);
setup_entry($wuiPage, $progress, $phases, 'setup_settinglanguage', 'setup_languageset', $innomaticLocale->getStr('languagechoicephase_label'), $wui_table, $phase ++);
setup_entry($wuiPage, $progress, $phases, 'setup_settingpassword', 'setup_passwordset', $innomaticLocale->getStr('passwordphase_label'), $wui_table, $phase ++);
//    setup_entry($wui_page, $progress, $phases, 'setup_settingappcentral', 'setup_appcentralset', $innomatic_locale->getStr('appcentralphase_label'), $wui_table, $phase ++);
setup_entry($wuiPage, $progress, $phases, 'setup_cleaningup', 'setup_cleanedup', $innomaticLocale->getStr('cleanupphase_label'), $wui_table, $phase ++);
setup_entry($wuiPage, $progress, $phases, 'setup_finishingsetup', 'setup_setupfinished', $innomaticLocale->getStr('finishphase_label'), $wui_table, $phase ++);

$wuiMainVertGroup->addChild($wui_table);
$wuiMainVertGroup->addChild(new WuiProgressBar('progress', array('progress' => $progress, 'totalsteps' => $phases)));
$wuiPage->addChild($wuiMainVertGroup);
$wui->addChild($wuiPage);
$wui->render();
?>