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
 * @license    http://www.innomaticplatform.com/license/   BSD License
 * @link       http://www.innomaticplatform.com
 * @since      Class available since Release 5.0
*/

function main_page()
{
    $innomatic_locale = new \Innomatic\Locale\LocaleCatalog('innomatic::root_menu', \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLanguage());
    $app_cfg = new \Innomatic\Application\ApplicationSettings('innomatic');

    if (is_object( \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess() ) and !(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getState() == \Innomatic\Core\InnomaticContainer::STATE_SETUP)) {
        $app_cfg = new \Innomatic\Application\ApplicationSettings('innomatic');
        $innomatic_logo_disabled = $app_cfg->getKey( 'innomatic-biglogo-disabled' );
    } else {
        $innomatic_logo_disabled = 0;
    }

    $wui = \Innomatic\Wui\Wui::instance('\Innomatic\Wui\Wui', true);
    $wui->loadWidget( 'page' );
    $wui->loadWidget( 'vertgroup' );
    $wui->loadWidget( 'button' );
    $wui->loadWidget( 'horizbar' );
    $wui->loadWidget( 'horizgroup' );
    $wui->loadWidget( 'label' );

    $page_params['title'] = 'Innomatic'.( strlen( \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getPlatformName() ) ? ' - '.\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getPlatformName().( strlen( \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getPlatformGroup() ) ? '.'.\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getPlatformGroup() : '' ) : '' );
    $page_params['border'] = 'false';

    $wui_page = new WuiPage( 'page', $page_params );
    $wui_vertgroup = new WuiVertgroup( 'vertgroup', array( 'align' => 'center', 'groupalign' => 'center', 'groupvalign' => 'middle', 'height' => '100%', 'width' => '0%' ) );
    $wui_center_group = new WuiVertgroup('center_group', array( 'align' => 'center', 'groupalign' => 'center', 'groupvalign' => 'middle', 'height' => '0%'));
    $wui_buttons_group = new WuiHorizgroup('buttons', array('align' => 'middle', 'groupalign' => 'center', 'width' => '0%'));

    $query = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->execute('SELECT count(id) AS domains FROM domains');
    if ($query->getFields('domains') > 0) {
        $wui_buttons_group->addChild(new WuiButton('domain', array('label' => $innomatic_locale->getStr('domainadmin'),
            'image' => $wui_page->mThemeHandler->mStyle['domainaccess'],
            'horiz' => 'true',
            'action' => 'domain/',
            'width' => 32,
            'height' => 32)));
    }

        $wui_buttons_group->addChild(new WuiButton('root', array('label' => $innomatic_locale->getStr('rootadmin'),
            'image' => $wui_page->mThemeHandler->mStyle['rootaccess'],
            'horiz' => 'true',
            'action' => 'root/',
            'width' => 32,
            'height' => 32)));

    if ($app_cfg->getKey('innomatic-link-disabled') != '1') {
        $wui_buttons_group->addChild(new WuiButton('innomaticlogo', array('label' => $innomatic_locale->getStr('innomatichome'),
            'image' => $wui_page->mThemeHandler->mStyle['innomaticminilogo'],
            'horiz' => 'true',
            'action' => 'http://www.innomaticplatform.com/',
            'width' => 32,
            'height' => 32)));
    }

    if ($app_cfg->getKey('serviceprovider-link-disabled') != '1') {
        $serviceprovider_link_filename = $app_cfg->getKey('serviceprovider-link-filename');

        if (strlen($serviceprovider_link_filename) and file_exists(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome().'shared/'.$serviceprovider_link_filename)) {
        $wui_buttons_group->addChild(new WuiButton('userlogo', array('label' => $app_cfg->getKey('serviceprovider-name'),
            'image' => \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getBaseUrl(false).'/shared/'.$serviceprovider_link_filename,
            'horiz' => 'true',
            'action' => $app_cfg->getKey('serviceprovider-url'))));
        }
    }

    $wui_logos_group = new WuiVertgroup( 'buttons_group', array( 'align' => 'center', 'groupalign' => 'center', 'groupvalign' => 'middle', 'height' => '0%' ) );
    if ( $innomatic_logo_disabled != '1' ) {
        if (\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getEdition() == \Innomatic\Core\InnomaticContainer::EDITION_MULTITENANT ) $edition = '_asp';
        else $edition = '_enterprise';

        if ( isset($wui_page->mThemeHandler->mStyle['biglogo'.$edition] ) ) $biglogo_image = $wui_page->mThemeHandler->mStyle['biglogo'.$edition];
        else $biglogo_image = $wui_page->mThemeHandler->mStyle['biglogo'];

        $wui_button = new WuiButton( 'button', array( 'action' => ' http://www.innomaticplatform.com', 'target' => '_top', 'image' => $biglogo_image, 'highlight' => 'false' ) );
        $wui_logos_group->addChild( $wui_button );
    }

    if (is_object( \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess() ) and \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getState() != \Innomatic\Core\InnomaticContainer::STATE_SETUP) {
        // Service Provider personalization
        //
        $serviceprovider_biglogo_filename = $app_cfg->getKey( 'serviceprovider-biglogo-filename' );
        $serviceprovider_url  = $app_cfg->getKey( 'serviceprovider-url' );

        if ( $app_cfg->getKey( 'serviceprovider-biglogo-disabled' ) != '1' ) {
            if ( strlen( $serviceprovider_biglogo_filename ) and file_exists( \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome().'shared/'.$serviceprovider_biglogo_filename ) ) {
                $serviceprovider_button = new WuiButton( 'serviceproviderbutton', array( 'action' => strlen( $serviceprovider_url ) ? $serviceprovider_url : ' http://www.innomatic.io', 'target' => '_top', 'image' => \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getBaseUrl(false).'/shared/'.$serviceprovider_biglogo_filename, 'highlight' => 'false' ) );
                $wui_logos_group->addChild( $serviceprovider_button );
            }
        }
    }

    $wui_center_group->addChild($wui_buttons_group);
    $wui_center_group->addChild(new WuiHorizBar('hb'));
    $wui_center_group->addChild($wui_logos_group);

$label_text = strlen( \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getPlatformName() ) ? \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getPlatformName().( strlen( \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getPlatformGroup() ) ? '.'.\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getPlatformGroup() : '' ) : '';
if ( strlen( $label_text ) ) {
    //$wui_center_group->addChild(new WuiHorizBar('hb'));
    $wui_center_group->addChild( new WuiLabel( 'label', array( 'label' => $label_text, 'color' => $wui_page->mThemeHandler->mColorsSet['buttons']['text'] ) ) );
}

    $wui_vertgroup->addChild( $wui_center_group );
    $wui_page->addChild( $wui_vertgroup );
    $wui->addChild( $wui_page );
    $wui->render();
}

// Checks if Innomatic is in setup phase
//
if (\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getState() != \Innomatic\Core\InnomaticContainer::STATE_SETUP) {
    main_page();
}
