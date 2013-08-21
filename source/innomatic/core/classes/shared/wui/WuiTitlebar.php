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
require_once ('innomatic/wui/widgets/WuiWidget.php');
/**
 * @package WUI
 */
class WuiTitleBar extends WuiWidget
{
    /*! @public mTitle string - Title shown in the title bar. */
    //public $mTitle;
    //public $mCloseWidget;
    //public $mNewWindowWidget;
    public $mIcon;
    public function __construct (
        $elemName,
        $elemArgs = '',
        $elemTheme = '',
        $dispEvents = ''
    )
    {
        parent::__construct($elemName, $elemArgs, $elemTheme, $dispEvents);
        if (isset($this->mArgs['title']))
            $this->mArgs['title'] = $this->mArgs['title'];
        if (isset($this->mArgs['closewidget'])) {
            if ($this->mArgs['closewidget'] == 'false')
                $this->mArgs['closewidget'] = 'false';
            if ($this->mArgs['closewidget'] == 'true')
                $this->mArgs['closewidget'] = 'true';
        } else
            $this->mArgs['closewidget'] = 'true';
        if (isset($this->mArgs['newwindowwidget'])) {
            if ($this->mArgs['newwindowwidget'] == 'false')
                $this->mArgs['newwindowwidget'] = 'false';
            if ($this->mArgs['newwindowwidget'] == 'true')
                $this->mArgs['newwindowwidget'] = 'true';
        } else
            $this->mArgs['newwindowwidget'] = 'true';
        if (isset($this->mArgs['icon']) and strlen($this->mArgs['icon']))
            $this->mIcon = $this->mArgs['icon'];
        else
            $this->mIcon = 'empty_ascii';
    }
    protected function generateSource ()
    {
        if (strlen($this->mIcon)) {
            $icon = '<img id="stoppingAjax" src="' . $this->mThemeHandler->mIconsBase . $this->mThemeHandler->mIconsSet['mini'][$this->mIcon]['base'] . '/mini/' . $this->mThemeHandler->mIconsSet['mini'][$this->mIcon]['file'] . '" alt="" border="0" style="width: 16px; height: 16px;">';
        } else
            $icon = '';
        require_once ('innomatic/wui/dispatch/WuiEventsCall.php');
        $new_window_event = new WuiEventsCall();
        require_once ('innomatic/wui/dispatch/WuiEvent.php');
        $new_window_event->addEvent(new WuiEvent('view', 'default', ''));
        $win_name = eregi_replace('[./_-]', '', 'detached' . $_SERVER['PHP_SELF']);
        /*
                        "<table border=\"0\" width=\"100%\" cellspacing=\"0\" cellpadding=\"1\"><tr><td bgcolor=\"".$this->mThemeHandler->mColorsSet['frames']['border']."\">\n".
                        "</td></tr>\n</table>\n".
            */
        
        $this->mLayout = ($this->mComments ? '<!-- begin ' . $this->mName . ' titlebar -->' : '') . "<table border=\"0\" width=\"100%\" cellspacing=\"0\" cellpadding=\"2\" bgcolor=\"white\">\n" . "<tr>\n" . "<td background=\"" . $this->mThemeHandler->mStyle['titlebar'] . "\" align=\"center\" valign=\"middle\" nowrap style=\"white-space: nowrap\">\n" . $icon . "<img id=\"loadingAjax\" src=\"".$this->mThemeHandler->mStyle['ajax_mini']."\" border=\"0\" style=\"padding:0; margin:0; width:16px; height:16px; display:none;\"></td>\n" . "<td nowrap style=\"white-space: nowrap\" background=\"" . $this->mThemeHandler->mStyle['titlebartitle'] . "\" class=\"titlebar\" valign=\"middle\" bgcolor=\"" . $this->mThemeHandler->mColorsSet['titlebars']['bgcolor'] . "\">" . "<font color=\"" . $this->mThemeHandler->mColorsSet['titlebars']['textcolor'] . "\">&nbsp;" . $this->mArgs['title'] . "</font></td>\n" . '<td width="100%" background="' . $this->mThemeHandler->mStyle['titlebar'] . '">&nbsp;</td>' . "\n" . '<td background="' . $this->mThemeHandler->mStyle['titlebar'] . '" align="center" valign="middle" nowrap style="white-space: nowrap">' . "\n" . ($this->mArgs['newwindowwidget'] == 'true' ? "<a href=\"#\" onClick=\"WuiWindowOpen('" . $new_window_event->getEventsCallString() . "','" . $win_name . "','width=600,height=400,resizable=yes,scrollbars=yes')\"><img src=\"" . $this->mThemeHandler->mStyle['windownew'] . '" border="0" style="width: 16px; height: 16px;" alt=""></a>&nbsp;' : '') . ($this->mArgs['closewidget'] == 'true' ? "<a href=\"main\" onClick=\"WuiWindowClose()\"><img src=\"" . $this->mThemeHandler->mStyle['windowclose'] . "\" border=\"0\" style=\"width: 16px; height: 16px;\" alt=\"\"></a>\n" : '') . "</td>\n" . //onClick="window.open('http://www.pageresource.com/jscript/jex5.htm','mywindow','width=400,height=200')"
        "</tr>\n</table>\n" . ($this->mComments ? '<!-- end ' . $this->mName . " titlebar -->\n" : '');
        return true;
    }
}
