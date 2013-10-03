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
require_once ('innomatic/wui/widgets/WuiContainerWidget.php');
/**
 * @package WUI
 */
class WuiToolbar extends WuiContainerWidget
{
    //public $mBgColor;
    public function __construct (
        $elemName,
        $elemArgs = '',
        $elemTheme = '',
        $dispEvents = ''
    )
    {
        $this->WuiContainerWidget($elemName, $elemArgs, $elemTheme, $dispEvents);
        if (! isset($this->mArgs['bgcolor']) or ! strlen($this->mArgs['bgcolor'])) {
            $this->mArgs['bgcolor'] = 'white';
        }
    }
    protected function generateSourceBegin ()
    {
		$block = '<![WUITOOLBAR[';
        $block .= ($this->mComments ? '<!-- begin ' . $this->mName . " toolbar -->\n" : '');
        $block .= '<td><table border="0" cellspacing="0" cellpadding="0"><tr><td width="0%"' . ">\n";
        $block .= '<table class="toolbar" border="0" width="100%" cellspacing="0" cellpadding="0" style="background-color: ' . $this->mThemeHandler->mColorsSet['titlebars']['bgcolor'] . ';"' . ">\n";
        $block .= "<tr>\n";
        $block .= '<td bgcolor="' . $this->mThemeHandler->mColorsSet['toolbars']['separator'] . '" width="1" style="width: 1px; padding: 0px; spacing: 0px"><img src="' . InnomaticContainer::instance('innomaticcontainer')->getBaseUrl(false) . '/shared/clear.gif" alt=""></td>' . "\n";
		/*
        $block .= '<td bgcolor="' . $this->mThemeHandler->mColorsSet['toolbars']['separator'] . '" width="1" style="width: 1px; padding: 0px; spacing: 0px"><img src="' . InnomaticContainer::instance('innomaticcontainer')->getBaseUrl(false) . '/shared/clear.gif" alt=""></td>' . "\n";
        $block .= '<td bgcolor="' . $this->mArgs['bgcolor'] . '" width="1" style="width: 1px; padding: 0px; spacing: 0px"><img src="' . InnomaticContainer::instance('innomaticcontainer')->getBaseUrl(false) . '/shared/clear.gif" alt=""></td>' . "\n";
        $block .= '<td bgcolor="' . $this->mThemeHandler->mColorsSet['toolbars']['separator'] . '" width="1" style="width: 1px; padding: 0px; spacing: 0px"><img src="' . InnomaticContainer::instance('innomaticcontainer')->getBaseUrl(false) . '/shared/clear.gif" alt=""></td>' . "\n";
        $block .= '<td bgcolor="' . $this->mArgs['bgcolor'] . '" width="1" style="width: 1px; padding: 0px; spacing: 0px"><img src="' . InnomaticContainer::instance('innomaticcontainer')->getBaseUrl(false) . '/shared/clear.gif" alt=""></td>' . "\n";
*/
        return $block;
    }
    protected function generateSourceEnd ()
    {
        $block = "</tr>\n</table>\n";
        $block .= "</td></tr>\n</table>\n</td>";
        $block .= ($this->mComments ? '<!-- end ' . $this->mName . " toolbar -->\n" : '');
		$block .= ']]>';
        return $block;
    }
    protected function generateSourceBlockBegin ()
    {
        return '<td width="0%">' . "\n";
    }
    protected function generateSourceBlockEnd ()
    {
        return "</td>\n";
    }
}