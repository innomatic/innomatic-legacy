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
require_once ('innomatic/wui/widgets/layersmenu/XLayersMenu.php');
require_once ('innomatic/wui/widgets/WuiWidget.php');
/**
 * @package WUI
 */
class WuiMenu extends WuiWidget
{
    //public $mMenu;
    /*!
     @function WuiMenu
    
     @abstract Class constructor.
     */
    public function __construct (
        $elemName,
        $elemArgs = '',
        $elemTheme = '',
        $dispEvents = ''
    )
    {
        parent::__construct($elemName, $elemArgs, $elemTheme, $dispEvents);
    }
    protected function generateSource ()
    {
    	/*
        require_once ('innomatic/util/Registry.php');
        $registry = Registry::instance();
        if (! $registry->isGlobalObject('singleton xlayersmenu')) {
            $mid = new XLayersMenu();
            $registry->setGlobalObject('singleton xlayersmenu', $mid);
        } else {
            $mid = $registry->getGlobalObject('singleton xlayersmenu');
        }
        $mid->libdir = InnomaticContainer::instance(
            'innomaticcontainer'
        )->getHome() . 'core/lib/';
        $mid->libwww = InnomaticContainer::instance(
            'innomaticcontainer'
        )->getBaseUrl(false) . '/shared/';
        $mid->tpldir = InnomaticContainer::instance(
            'innomaticcontainer'
        )->getHome() . 'core/conf/layersmenu/';
        $mid->imgdir = $this->mThemeHandler->mStyleDir;
        $mid->imgwww = $this->mThemeHandler->mStyleBase
            . $this->mThemeHandler->mStyleName . '/';
        $mid->setMenuStructureString($this->mArgs['menu']);
        $mid->setDownArrowImg(
            basename($this->mThemeHandler->mStyle['arrowdownshadow'])
        );
        $mid->setForwardArrowImg(
            basename($this->mThemeHandler->mStyle['arrowrightshadow'])
        );
        $mid->parseStructureForMenu($this->mName);
        $mid->newHorizontalMenu($this->mName);
        $this->mLayout = ($this->mComments ? '<!-- begin ' . $this->mName
            . ' menu -->' : '')
            . ((isset($GLOBALS['gEnv']['runtime']['wui_menu']['header'])) ? ''
            : $mid->MakeHeader()) . $mid->getMenu($this->mName)
            . //$mid->MakeFooter().
            ($this->mComments ? '<!-- end ' . $this->mName
            . ' menu -->' . "\n" : '');
        $GLOBALS['gEnv']['runtime']['wui_menu']['header'] = true;
        $GLOBALS['gEnv']['runtime']['wui_menu']['footer'] = $mid->MakeFooter();
        return true;
        */
    }
}
