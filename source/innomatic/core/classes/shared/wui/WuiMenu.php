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
 * @license    http://www.innomatic.io/license/ New BSD License
 * @link       http://www.innomatic.io
 * @since      Class available since Release 5.0
 */
namespace Shared\Wui;

/**
 * @package WUI
 */
class WuiMenu extends \Innomatic\Wui\Widgets\WuiWidget
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
    protected function generateSource()
    {
        /*
        $registry = \Innomatic\Util\Registry::instance();
        if (! $registry->isGlobalObject('singleton xlayersmenu')) {
            $mid = new XLayersMenu();
            $registry->setGlobalObject('singleton xlayersmenu', $mid);
        } else {
            $mid = $registry->getGlobalObject('singleton xlayersmenu');
        }
        $mid->libdir = \Innomatic\Core\InnomaticContainer::instance(
            '\Innomatic\Core\InnomaticContainer'
        )->getHome() . 'core/lib/';
        $mid->libwww = \Innomatic\Core\InnomaticContainer::instance(
            '\Innomatic\Core\InnomaticContainer'
        )->getBaseUrl(false) . '/shared/';
        $mid->tpldir = \Innomatic\Core\InnomaticContainer::instance(
            '\Innomatic\Core\InnomaticContainer'
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
