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

require_once('innomatic/core/InnomaticContainer.php');
require_once('innomatic/locale/LocaleCatalog.php');
require_once('innomatic/wui/Wui.php');
require_once('innomatic/wui/dispatch/WuiEvent.php');
require_once('innomatic/wui/dispatch/WuiEventsCall.php');
require_once('innomatic/webapp/WebAppContainer.php');

require_once('innomatic/desktop/layout/DesktopLayout.php');
$layout_mode = DesktopLayout::instance('desktoplayout')->getLayout();

$res = WebAppContainer::instance('webappcontainer')->getProcessor()->getResponse();
$res->addHeader('P3P', 'CP="CUR ADM OUR NOR STA NID"');
$res->addHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');
$res->addHeader('Last-Modified', gmdate('D, d M Y H:i:s'));
$res->addHeader('Cache-control', 'no-cache, must-revalidate');
$res->addHeader('Pragma', 'no-cache');

$root_db = InnomaticContainer::instance('innomaticcontainer')->getDataAccess();

$innomatic_menu_locale = new LocaleCatalog('innomatic::root_menu', InnomaticContainer::instance('innomaticcontainer')->getLanguage());

$wui = Wui::instance('wui');
$wui->loadWidget('empty');
$wui->loadWidget('grid');
$wui->loadWidget('horizframe');
$wui->loadWidget('horizgroup');
$wui->loadWidget('label');
$wui->loadWidget('page');
$wui->loadWidget('sessionkey');
$wui->loadWidget('treemenu');
$wui->loadWidget('tab');
$wui->loadWidget('vertframe');
$wui->loadWidget('vertgroup');
$wui->loadWidget('button');
$wui->loadWidget('horizgroup');

$wuiPage = new WuiPage('page', array('title' => 'Innomatic'. (strlen(WebAppContainer::instance('webappcontainer')->getProcessor()->getRequest()->getServerName()) ? ' - '.WebAppContainer::instance('webappcontainer')->getProcessor()->getRequest()->getServerName(). (strlen(InnomaticContainer::instance('innomaticcontainer')->getPlatformGroup()) ? '.'.InnomaticContainer::instance('innomaticcontainer')->getPlatformGroup() : '') : ''), 'border' => 'false'));
if ($layout_mode == 'horiz') {
    $wuiPage->mArgs['background'] = $wuiPage->mThemeHandler->mStyle['menubackhorizbottom'];
    $wuiPage->mArgs['horizbackground'] = 'true';
} else {
    $wuiPage->mArgs['background'] = $wuiPage->mThemeHandler->mStyle['menuback'];
}
$wuiMainVertGroup = new WuiVertGroup('mainvertgroup');

$groups_query = $root_db->execute('SELECT * FROM root_panels_groups ORDER BY name');
$num_groups = $groups_query->getNumberRows();

if ($layout_mode == 'horiz' ) {
function applications_tab_action_builder($tab) {
    return WuiEventsCall::buildEventsCallString('', array(array('view', 'default', array('activetab' => $tab))));
}

require_once('innomatic/wui/dispatch/WuiDispatcher.php');
$disp = new WuiDispatcher('view');
$eventData = $disp->getEventData();
$tabs = array();
$wui_tab = new WuiTab('applicationstab', array('tabactionfunction' => 'applications_tab_action_builder', 'compact' => 'true', 'tabs' => &$tabs, 'activetab' => (isset($eventData['activetab']) ? $eventData['activetab'] : '')));
$tab_pages = array();
}

if ($num_groups > 0) {
    $cont_a = 0;
    unset($el);
    while (!$groups_query->eof) {
        $group_apps = false;
        $group_data = $groups_query->getFields();

        if (strlen($group_data['catalog'])) {
            $tmp_locale = new LocaleCatalog($group_data['catalog'], InnomaticContainer::instance('innomaticcontainer')->getLanguage());
            $el[$group_data['id']]['groupname'] = $tmp_locale->getStr($group_data['name']);
            if ($layout_mode == 'horiz') {
                $tabs[]['label'] = $tmp_locale->getStr($group_data['name']);
            }
        } else {
            $el[$group_data['id']]['groupname'] = $group_data['name'];
            if ($layout_mode == 'horiz') {
                $tabs[]['label'] = $group_data['name'];
            }
        }

        $pagesquery = $root_db->execute('SELECT * FROM root_panels WHERE groupid='.$group_data['id'].' ORDER BY name');
        if ($pagesquery) {
            $pagesnum = $pagesquery->getNumberRows();

            if ($pagesnum > 0) {
        if ($layout_mode == 'horiz') {
            $tab_pages[$cont_a] = new WuiHorizGroup('hg');
            $wui_tab->addChild($tab_pages[$cont_a]);
        }
                $group_apps = true;
                $cont_b = 0;
                while (!$pagesquery->eof) {
                    $pagedata = $pagesquery->getFields();

                    if (strlen($pagedata['catalog']) > 0) {
                        $tmploc = new LocaleCatalog($pagedata['catalog'], InnomaticContainer::instance('innomaticcontainer')->getLanguage());
                        $descstr = $tmploc->getStr($pagedata['name']);
                    }

                    $tmp_eventscall = new WuiEventsCall($pagedata['name']);
                    $tmp_eventscall->addEvent(new WuiEvent('view', 'default', ''));

                    if (strlen($pagedata['themeicontype']))
                        $imageType = $pagedata['themeicontype'];
                    else
                        $imageType = 'apps';

                    strlen($pagedata['themeicon']) ? $imageUrl = $wuiPage->mThemeHandler->mIconsBase.$wuiPage->mThemeHandler->mIconsSet[$imageType][$pagedata['themeicon']]['base'].'/'.$imageType.'/'.$wuiPage->mThemeHandler->mIconsSet[$imageType][$pagedata['themeicon']]['file'] : $imageUrl = $pagedata['iconfile'];

                    $el[$group_data['id']]['groupelements'][$cont_b]['name'] = $descstr;
                    $el[$group_data['id']]['groupelements'][$cont_b]['image'] = $imageUrl;
                    $el[$group_data['id']]['groupelements'][$cont_b]['action'] = $tmp_eventscall->getEventsCallString();
                    $el[$group_data['id']]['groupelements'][$cont_b]['themesized'] = 'true';

                    if ($layout_mode == 'horiz') {
                        $tab_pages[$cont_a]->addChild(new WuiButton('', array('label' => $descstr, 'action' => $tmp_eventscall->getEventsCallString(), 'image' => $imageUrl, 'target' => 'main', 'horiz' => 'true', 'width' => 32, 'height' => 32)));
                    }
                    unset($tmp_eventscall);
                    $cont_b ++;
                    $pagesquery->moveNext();
                }
            }
        }

        // TODO Check if this section is for compatibility only - and remove it
        if ($group_data['name'] == 'innomatic') {
            $pagesquery = $root_db->execute('SELECT * FROM root_panels WHERE groupid=0 OR groupid IS NULL ORDER BY name');
            if ($pagesquery) {
                $pagesnum = $pagesquery->getNumberRows();

                if ($pagesnum > 0) {
                    $group_apps = true;
                    while (!$pagesquery->eof) {
                        $pagedata = $pagesquery->getFields();

                        if (strlen($pagedata['catalog']) > 0) {
                            $tmploc = new LocaleCatalog($pagedata['catalog'], InnomaticContainer::instance('innomaticcontainer')->getLanguage());
                            $descstr = $tmploc->getStr($pagedata['name']);
                        }

                        $tmp_eventscall = new WuiEventsCall($pagedata['name']);
                        $tmp_eventscall->addEvent(new WuiEvent('view', 'default', ''));

                        $el[$group_data['id']]['groupelements'][$cont_b]['name'] = $descstr;
                        $el[$group_data['id']]['groupelements'][$cont_b]['image'] = $pagedata['iconfile'];
                        $el[$group_data['id']]['groupelements'][$cont_b]['action'] = $tmp_eventscall->getEventsCallString();
                        $el[$group_data['id']]['groupelements'][$cont_b]['themesized'] = 'true';

                        if ($layout_mode == 'horiz') {
                            $tab_pages[$cont_a]->addChild(new WuiButton('', array('label' => $descstr, 'action' => $tmp_eventscall->getEventsCallString(), 'image' => $pagedata['iconfile'], 'target' => 'main', 'horiz' => 'true', 'width' => 32, 'height' => 32)));
                        }
                        unset($tmp_eventscall);
                        $cont_b ++;
                        $pagesquery->moveNext();
                    }
                }
            }
        }

        $groups_query->moveNext();
        
        if ($group_apps) {
            $cont_a ++;
        } else {
            unset($el[$group_data['id']]);
            if ($layout_mode == 'horiz') {
                array_pop($tabs);
            }
        }
    }
    

}
if ($layout_mode == 'horiz') {
    $wuiMainVertGroup->addChild($wui_tab);
} else {
    $wui_vertframe = new WuiVertFrame('vertframe');
    $wui_vertframe->addChild(new WuiTreeMenu('treemenu', array('elements' => $el, 'width' => '120', 'target' => 'main', 'allgroupsactive' => 'false')));
    $wuiMainVertGroup->addChild($wui_vertframe);
}

$wuiPage->addChild($wuiMainVertGroup);
$wui->addChild($wuiPage);
$wui->render();
?>