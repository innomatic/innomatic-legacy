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

$wui = Wui::instance('wui', true);
$wui->loadWidget( 'page' );
$wui->loadWidget( 'button' );
$wui->loadWidget( 'label' );
$wui->loadWidget( 'vertgroup' );

$wuiPage = new WuiPage( 'page', array(
                                       'title' => 'Innomatic'.( strlen( InnomaticContainer::instance('innomaticcontainer')->getPlatformName() ) ? ' - '.InnomaticContainer::instance('innomaticcontainer')->getPlatformName().( strlen( InnomaticContainer::instance('innomaticcontainer')->getPlatformGroup() ) ? '.'.InnomaticContainer::instance('innomaticcontainer')->getPlatformGroup() : '' ) : '' ),
                                       'border' => 'false'
                                      ) );

$wuiMainVertGroup = new WuiVertGroup( 'mainvertgroup', array( 'align' => 'center' ) );

$wuiMainVertGroup->addChild(
    new WuiButton(
        'innomaticlogo',
        array(
            'action' => InnomaticContainer::instance('innomaticcontainer')->getBaseUrl().'/',
            'target' => '_top',
            'image' => $wuiPage->mThemeHandler->mStyle['headerlogo'],
            'highlight' => 'false',
            'compact' => 'true'
            )
        )
    );

$label_text = strlen( InnomaticContainer::instance('innomaticcontainer')->getPlatformName() ) ? InnomaticContainer::instance('innomaticcontainer')->getPlatformName().( strlen( InnomaticContainer::instance('innomaticcontainer')->getPlatformGroup() ) ? '.'.InnomaticContainer::instance('innomaticcontainer')->getPlatformGroup() : '' ) : '';
if ( strlen( $label_text ) )
{
    $wuiMainVertGroup->addChild( new WuiLabel( 'label', array( 'label' => $label_text, 'color' => $wuiPage->mThemeHandler->mColorsSet['buttons']['text'] ) ) );
}

$wuiPage->addChild( $wuiMainVertGroup );
$wui->addChild( $wuiPage );
$wui->render();

?>