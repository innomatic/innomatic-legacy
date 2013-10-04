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
require_once('innomatic/wui/dispatch/WuiEvent.php');
require_once('innomatic/wui/dispatch/WuiEventsCall.php');
require_once('innomatic/domain/user/Permissions.php');
require_once('innomatic/locale/LocaleCatalog.php');

require_once('innomatic/desktop/layout/DesktopLayout.php');
$layout_mode = DesktopLayout::instance('desktoplayout')->getLayout();

WebAppContainer::instance('webappcontainer')->getProcessor()->getResponse()->addHeader('P3P', 'CP="CUR ADM OUR NOR STA NID"');
WebAppContainer::instance('webappcontainer')->getProcessor()->getResponse()->addHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');
WebAppContainer::instance('webappcontainer')->getProcessor()->getResponse()->addHeader('Last-Modified', gmdate('D, d M Y H:i:s'));
WebAppContainer::instance('webappcontainer')->getProcessor()->getResponse()->addHeader('Cache-control', 'no-cache, must-revalidate');
WebAppContainer::instance('webappcontainer')->getProcessor()->getResponse()->addHeader('Pragma', 'no-cache');

$innomatic_menu_locale = new LocaleCatalog('innomatic::root_menu', InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getLanguage());

$wui = Wui::instance('wui');
    $wui->loadWidget( 'horizframe' );
    $wui->loadWidget( 'horizgroup' );
    $wui->loadWidget( 'page' );
    $wui->loadWidget( 'treemenu' );
    $wui->loadWidget( 'vertframe' );
    $wui->loadWidget( 'vertgroup' );
$wui->loadWidget('button');
$wui->loadWidget('horizgroup');
$wui->loadWidget('tab');

$wuiPage = new WuiPage( 'page', array(
                                       'title' => 'Innomatic - '.InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->domaindata['domainname'],
                                       'border' => 'false'
                                      ) );

$wuiMainVertGroup = new WuiVertGroup( 'mainvertgroup' );

$tmpperm = new Permissions( InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess(), InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getGroup() );

$groupsquery = InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess()->execute( 'select * from domain_panels_groups order by name' );
$numgroups   = $groupsquery->getNumberRows();

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

if ( $numgroups > 0 ) {
    $prefs_id = 0;
    $tools_id = 0;

    $cont = 0;
    unset( $el );

    while ( !$groupsquery->eof )
    {
        $group_apps = false;
        $groupdata = $groupsquery->getFields();

        if ( $tmpperm->check( $groupdata['id'], 'group' ) != Permissions::NODE_NOTENABLED )
        {
            if ( $groupdata['name'] == 'tools' ) $tools_id = $groupdata['id'];
            if ( $groupdata['name'] == 'preferences' ) $prefs_id = $groupdata['id'];

            if ( strlen( $groupdata['catalog'] ) > 0 )
            {
                $tmploc = new LocaleCatalog( $groupdata['catalog'], InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getLanguage() );
                $descstr = $tmploc->getStr( $groupdata['name'] );
                $el[$groupdata['id']]['groupname'] = $descstr;
            if ($layout_mode == 'horiz') {
                $tabs[]['label'] = $tmploc->getStr($groupdata['name']);
            }
        } else {
            $el[$group_data['id']]['groupname'] = $groupdata['name'];
            if ($layout_mode == 'horiz') {
                $tabs[]['label'] = $groupdata['name'];
            }
        }

            $pagesquery = &InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess()->execute( 'select * from domain_panels where groupid = '.$groupdata['id'].' order by name' );
            $pagesnum = $pagesquery->getNumberRows();

            if ( $pagesnum > 0 )
            {
        if ($layout_mode == 'horiz') {
            $tab_pages[$cont] = new WuiHorizGroup('hg');
            $wui_tab->addChild($tab_pages[$cont]);
        }

                $group_apps = true;
                $contb = 0;

                while ( !$pagesquery->eof )
                {
                    $pagedata = $pagesquery->getFields();

                    if ( $tmpperm->check( $pagedata['id'], 'page' ) != Permissions::NODE_NOTENABLED )
                    {
                        if ( strlen( $pagedata['catalog'] ) > 0 )
                        {
                            $tmploc = new LocaleCatalog( $pagedata['catalog'], InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getLanguage() );
                            $descstr = $tmploc->getStr( $pagedata['name'] );

                            $tmp_eventscall = new WuiEventsCall($pagedata['name']);
                            $tmp_eventscall->addEvent( new WuiEvent( 'view', 'default', '' ) );

                            if ( strlen( $pagedata['themeicontype'] ) ) $imageType = $pagedata['themeicontype'];
                            else $imageType = 'apps';

                            strlen( $pagedata['themeicon'] ) ? $imageUrl = $wuiPage->mThemeHandler->mIconsBase.$wuiPage->mThemeHandler->mIconsSet[$imageType][$pagedata['themeicon']]['base'].'/'.$imageType.'/'.$wuiPage->mThemeHandler->mIconsSet[$imageType][$pagedata['themeicon']]['file'] : $imageUrl = $pagedata['iconfile'];

                            $el[$groupdata['id']]['groupelements'][$contb]['name'] = $descstr;
                            $el[$groupdata['id']]['groupelements'][$contb]['image'] = $imageUrl;
                            $el[$groupdata['id']]['groupelements'][$contb]['action'] = $tmp_eventscall->getEventsCallString();
                            $el[$groupdata['id']]['groupelements'][$contb]['themesized'] = 'true';

                    if ($layout_mode == 'horiz') {
                        $tab_pages[$cont]->addChild(new WuiButton('', array('label' => $descstr, 'action' => $tmp_eventscall->getEventsCallString(), 'image' => $imageUrl, 'target' => 'main', 'horiz' => 'true', 'width' => 32, 'height' => 32)));
                    }
                            unset( $tmp_eventscall );
                        }
                    }

                    $pagesquery->movenext();
                    $contb++;
                }
            }
        }

 //$cont++;
 /**/
        if ($group_apps) {
            $cont++;
        } else {
            unset($el[$groupdata['id']]);
            if ($layout_mode == 'horiz') {
                array_pop($tabs);
            }
        }
        /**/
        $groupsquery->movenext();
    }

/*
    // Trick to show tools and settings groups as last ones when in vertical layout mode
    if ( $tools_id != 0 )
    {
        $tmp_tools = $el[$tools_id];
        unset( $el[$tools_id] );
        $el[$tools_id] = &$tmp_tools;
    }

    if ( $prefs_id != 0 )
    {
        $tmp_prefs = $el[$prefs_id];
        unset( $el[$prefs_id] );
        $el[$prefs_id] = &$tmp_prefs;
    }
*/
    //if ( $action == 'open' ) $menu->id = $id;
    //$menu->show();
}

if ($layout_mode == 'horiz') {
    $wuiMainVertGroup->addChild($wui_tab);
} else {
    $wui_vertframe = new WuiVertgroup('vertframe');
    $wui_vertframe->addChild(new WuiTreeMenu('treemenu', array('elements' => $el, 'width' => '120', 'target' => 'main', 'allgroupsactive' => 'false')));
    $wuiMainVertGroup->addChild($wui_vertframe);
}

$wuiPage->addChild( $wuiMainVertGroup );
$wui->addChild( $wuiPage );
$wui->render();

?>