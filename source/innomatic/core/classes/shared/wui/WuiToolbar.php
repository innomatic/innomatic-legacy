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
class WuiToolbar extends \Innomatic\Wui\Widgets\WuiContainerWidget
{
    //public $mBgColor;
    public function __construct (
        $elemName,
        $elemArgs = '',
        $elemTheme = '',
        $dispEvents = ''
    )
    {
        parent::__construct($elemName, $elemArgs, $elemTheme, $dispEvents);
        if (! isset($this->mArgs['bgcolor']) or ! strlen($this->mArgs['bgcolor'])) {
            $this->mArgs['bgcolor'] = 'white';
        }
    }
    protected function generateSourceBegin()
    {
        $container = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');
        
        $block = '<![WUITOOLBAR[';
        $block .= ($this->mComments ? '<!-- begin ' . $this->mName . " toolbar -->\n" : '');
        $block .= '<td><table border="0" cellspacing="0" cellpadding="0"><tr><td width="0%"' . ">\n";
        $block .= '<table class="toolbar" border="0" width="100%" cellspacing="0" cellpadding="0"' . ">\n";
        $block .= "<tr>\n";
        $block .= '<td bgcolor="' . $this->mThemeHandler->mColorsSet['toolbars']['separator'] . '" width="1" style="width: 1px; padding: 0px; spacing: 0px"><img src="' . $container->getBaseUrl(false) . '/shared/clear.gif" alt=""></td>' . "\n";
        /*
        $block .= '<td bgcolor="' . $this->mThemeHandler->mColorsSet['toolbars']['separator'] . '" width="1" style="width: 1px; padding: 0px; spacing: 0px"><img src="' . $container->getBaseUrl(false) . '/shared/clear.gif" alt=""></td>' . "\n";
        $block .= '<td bgcolor="' . $this->mArgs['bgcolor'] . '" width="1" style="width: 1px; padding: 0px; spacing: 0px"><img src="' . $container->getBaseUrl(false) . '/shared/clear.gif" alt=""></td>' . "\n";
        $block .= '<td bgcolor="' . $this->mThemeHandler->mColorsSet['toolbars']['separator'] . '" width="1" style="width: 1px; padding: 0px; spacing: 0px"><img src="' . $container->getBaseUrl(false) . '/shared/clear.gif" alt=""></td>' . "\n";
        $block .= '<td bgcolor="' . $this->mArgs['bgcolor'] . '" width="1" style="width: 1px; padding: 0px; spacing: 0px"><img src="' . $container->getBaseUrl(false) . '/shared/clear.gif" alt=""></td>' . "\n";
*/
        return $block;
    }
    protected function generateSourceEnd()
    {
        $block = "</tr>\n</table>\n";
        $block .= "</td></tr>\n</table>\n</td>";
        $block .= ($this->mComments ? '<!-- end ' . $this->mName . " toolbar -->\n" : '');
        $block .= ']]>';
        return $block;
    }
    protected function generateSourceBlockBegin()
    {
        return '<td width="0%">' . "\n";
    }
    protected function generateSourceBlockEnd()
    {
        return "</td>\n";
    }
}
