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
        $mid->libdir = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/lib/';
        $mid->libwww = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getBaseUrl(false) . '/shared/';
        //$mid->tpldir = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome().'core/conf/';
        $mid->imgdir = $this->mThemeHandler->mStyleDir;
        $mid->imgwww = $this->mThemeHandler->mStyleBase . $this->mThemeHandler->mStyleName . '/';
        //$mid->imgdir = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome().'shared/';
        //$mid->imgwww = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getBaseUrl(false).'/shared/';
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
