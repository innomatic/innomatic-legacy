<?php
/**
 * Innomatic
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2014 Innomatic Company
 * @license    http://www.innomatic.io/license/ New BSD License
 * @link       http://www.innomatic.io
 * @since      Class available since Release 5.0
 */
namespace Shared\Wui;

/**
 * @package WUI
 */
class WuiTreevmenu extends \Innomatic\Wui\Widgets\WuiWidget
{
    public $mMenu;
    public function __construct (
        $elemName,
        $elemArgs = '',
        $elemTheme = '',
        $dispEvents = ''
    )
    {
        parent::__construct($elemName, $elemArgs, $elemTheme, $dispEvents);
        if (isset($this->mArgs['menu']))
            $this->mMenu = $this->mArgs['menu'];
    }
    protected function generateSource()
    {
        $registry = \Innomatic\Util\Registry::instance();
        if (! $registry->isGlobalObject('singleton xlayersmenu')) {
            $mid = new \Innomatic\Wui\Widgets\Layersmenu\XLayersMenu();
            $registry->setGlobalObject('singleton xlayersmenu', $mid);
        } else {
            $mid = $registry->getGlobalObject('singleton xlayersmenu');
        }
        
        $container = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');
        $mid->libdir = $container->getHome() . 'core/lib/';
        $mid->libwww = $container->getBaseUrl(false) . '/shared/';
        //$mid->tpldir = $container->getHome().'core/conf/';
        $mid->imgdir = $this->mThemeHandler->mStyleDir;
        $mid->imgwww = $this->mThemeHandler->mStyleBase . $this->mThemeHandler->mStyleName . '/';
        //$mid->imgdir = $container->getHome().'shared/';
        //$mid->imgwww = $container->getBaseUrl(false).'/shared/';
        $mid->setMenuStructureString($this->mMenu);
        $mid->setDownArrowImg(basename($this->mThemeHandler->mStyle['arrowdownshadow']));
        $mid->setForwardArrowImg(basename($this->mThemeHandler->mStyle['arrowrightshadow']));
        $mid->ParseStructureForMenu($this->mName);
        //$mid->NewHorizontalMenu( $this->mName );
        $mid->newPlainMenu($this->mName);
        //$mid->setPHPTreeMenuDefaultExpansion("67|68|82");
        $mid->newPHPTreeMenu($this->mName);
        $mid->newTreeMenu($this->mName);
        //$mid->newVerticalMenu("vermenu1", 12);
        $mid->newVerticalMenu($this->mName);
        //$mid->setMenuStructureString($menustring);
        //$mid->parseStructureForMenu("vermenu1");
        $this->mLayout = ($this->mComments ? '<!-- begin ' . $this->mName . ' treevmenu -->' : '') . ((isset($GLOBALS['gEnv']['runtime']['wui_menu']['header'])) ? '' : $mid->MakeHeader()) . $mid->getTreeMenu($this->mName) . //$mid->MakeFooter().
        ($this->mComments ? '<!-- end ' . $this->mName . ' treevmenu -->' . "\n" : '');
        $GLOBALS['gEnv']['runtime']['wui_menu']['header'] = true;
        $GLOBALS['gEnv']['runtime']['wui_menu']['footer'] = $mid->MakeFooter();
        return true;
    }
}
