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
        $block = ($this->mComments ? '<!-- begin ' . $this->mName . " toolbar -->\n" : '');
        $block .= '<table border="0" cellspacing="1" cellpadding="1"><tr><td width="0%" bgcolor="' . $this->mArgs['bgcolor'] . "\">\n";
        $block .= '<table border="0" width="100%" cellspacing="0" cellpadding="0" bgcolor="' . $this->mArgs['bgcolor'] . "\">\n";
        $block .= "<tr>\n";
        $block .= '<td bgcolor="' . $this->mThemeHandler->mColorsSet['toolbars']['separator'] . '" width="1" style="width: 1px; padding: 0px; spacing: 0px"><img src="' . InnomaticContainer::instance('innomaticcontainer')->getBaseUrl(false) . '/shared/clear.gif" alt=""></td>' . "\n";
        $block .= '<td bgcolor="' . $this->mArgs['bgcolor'] . '" width="1" style="width: 1px; padding: 0px; spacing: 0px"><img src="' . InnomaticContainer::instance('innomaticcontainer')->getBaseUrl(false) . '/shared/clear.gif" alt=""></td>' . "\n";
        $block .= '<td bgcolor="' . $this->mThemeHandler->mColorsSet['toolbars']['separator'] . '" width="1" style="width: 1px; padding: 0px; spacing: 0px"><img src="' . InnomaticContainer::instance('innomaticcontainer')->getBaseUrl(false) . '/shared/clear.gif" alt=""></td>' . "\n";
        $block .= '<td bgcolor="' . $this->mArgs['bgcolor'] . '" width="1" style="width: 1px; padding: 0px; spacing: 0px"><img src="' . InnomaticContainer::instance('innomaticcontainer')->getBaseUrl(false) . '/shared/clear.gif" alt=""></td>' . "\n";
        $block .= '<td bgcolor="' . $this->mThemeHandler->mColorsSet['toolbars']['separator'] . '" width="1" style="width: 1px; padding: 0px; spacing: 0px"><img src="' . InnomaticContainer::instance('innomaticcontainer')->getBaseUrl(false) . '/shared/clear.gif" alt=""></td>' . "\n";
        $block .= '<td bgcolor="' . $this->mArgs['bgcolor'] . '" width="1" style="width: 1px; padding: 0px; spacing: 0px"><img src="' . InnomaticContainer::instance('innomaticcontainer')->getBaseUrl(false) . '/shared/clear.gif" alt=""></td>' . "\n";
        return $block;
    }
    protected function generateSourceEnd ()
    {
        $block = "</tr>\n</table>\n";
        $block .= "</td></tr>\n</table>\n";
        $block .= ($this->mComments ? '<!-- end ' . $this->mName . " toolbar -->\n" : '');
        return $block;
    }
    protected function generateSourceBlockBegin ()
    {
        return '<td width="0%" bgcolor="' . $this->mArgs['bgcolor'] . '">' . "\n";
    }
    protected function generateSourceBlockEnd ()
    {
        return "</td>\n";
    }
}