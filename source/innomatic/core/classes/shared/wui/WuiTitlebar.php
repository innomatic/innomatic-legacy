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
    //public $mCloseWidget; deprecated
    //public $mNewWindowWidget; deprecated
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

		// closewidget is deprecated
        if (isset($this->mArgs['closewidget'])) {
            if ($this->mArgs['closewidget'] == 'false')
                $this->mArgs['closewidget'] = 'false';
            if ($this->mArgs['closewidget'] == 'true')
                $this->mArgs['closewidget'] = 'true';
        } else
            $this->mArgs['closewidget'] = 'false';

		// newwindowwidget is deprecated
        if (isset($this->mArgs['newwindowwidget'])) {
            if ($this->mArgs['newwindowwidget'] == 'false')
                $this->mArgs['newwindowwidget'] = 'false';
            if ($this->mArgs['newwindowwidget'] == 'true')
                $this->mArgs['newwindowwidget'] = 'true';
        } else
            $this->mArgs['newwindowwidget'] = 'false';
        if (isset($this->mArgs['icon']) and strlen($this->mArgs['icon']))
            $this->mIcon = $this->mArgs['icon'];
        else
            $this->mIcon = 'empty_ascii';
    }

    protected function generateSource ()
    {
        if (strlen($this->mIcon)) {
            $icon = '<img id="stoppingAjax" src="' . $this->mThemeHandler->mIconsBase . $this->mThemeHandler->mIconsSet['mini'][$this->mIcon]['base'] . '/mini/' . $this->mThemeHandler->mIconsSet['mini'][$this->mIcon]['file'] . '" alt="" border="0" style="padding-left: 10px; width: 16px; height: 16px;">';
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
        


		
		// <a href=\"".InnomaticContainer::instance('innomaticcontainer')->getBaseUrl().'/'."\">
		
		/*
			$wuiMainVertGroup->addChild(new WuiButton('innomaticlogo', array('action' => InnomaticContainer::instance('innomaticcontainer')->getBaseUrl().'/', 'target' => '_top', 'image' => $this->mThemeHandler->mStyle['headerlogo'], 'highlight' => 'false', 'compact' => 'true')));
			$wuiMainVertGroup->addChild(new WuiLabel('label', array('label' => InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->domaindata['domainname'], 'nowrap' => 'true', 'align' => 'center')));
			$wuiMainVertGroup->addChild(new WuiLabel('labelname', array('label' => $user_data['fname'].' '.$user_data['lname'])));
			*/

$GLOBALS['wui']['titlebar-title'] = 			$icon
			. "<img id=\"loadingAjax\" src=\"".$this->mThemeHandler->mStyle['ajax_mini']."\" border=\"0\" style=\"padding-left: 10px; width:16px; height:16px; display:none;\"></td>\n"
			. "<td nowrap style=\"white-space: nowrap; padding-right: 15px;\" class=\"titlebar\" valign=\"middle\">"
			. "<font color=\"" . $this->mThemeHandler->mColorsSet['titlebars']['textcolor'] . "\">&nbsp;&nbsp;&nbsp;" . $this->mArgs['title']
			. "</font></td>\n";


        $this->mLayout = ($this->mComments ? '<!-- begin ' . $this->mName . ' titlebar -->' : '') . "<table border=\"0\" style=\"border-bottom: 0px solid ".$this->mThemeHandler->mColorsSet['pages']['border'].";\" width=\"100%\" heigth=\"100%\" cellspacing=\"0\" cellpadding=\"10\" bgcolor=\"white\">\n"
			. "<tr>\n" 
			. "<td style=\"background-color: #3274a8;\">Menu</td>"
			. "<td background=\"" . $this->mThemeHandler->mStyle['titlebar'] . "\" align=\"center\" valign=\"middle\" nowrap style=\"white-space: nowrap\">\n" . $icon
			. "<img id=\"loadingAjax\" src=\"".$this->mThemeHandler->mStyle['ajax_mini']."\" border=\"0\" style=\"padding:0; margin:0; width:16px; height:16px; display:none;\"></td>\n"
			. "<td nowrap style=\"white-space: nowrap\" background=\"" . $this->mThemeHandler->mStyle['titlebartitle'] . "\" class=\"titlebar\" valign=\"middle\" bgcolor=\"" . $this->mThemeHandler->mColorsSet['titlebars']['bgcolor'] . "\">"
			. "<font color=\"" . $this->mThemeHandler->mColorsSet['titlebars']['textcolor'] . "\"><a class=\"titlebar\" href=\"#\" onclick=\"javascript:document.getElementById('innomatic_launcher').style.visibility = 'visible';\"><span style=\"font-weight: 500;\">".$domain_name."</span>&nbsp;&nbsp;&nbsp;" . $this->mArgs['title']
			. "  <img src=\"".$this->mThemeHandler->mStyle['arrowdown']."\" alt=\"\"/></a></font></td>\n"
			. '<td width="100%" background="' . $this->mThemeHandler->mStyle['titlebar'] . '">&nbsp;</td>' . "\n"
			. '<td background="' . $this->mThemeHandler->mStyle['titlebar'] . '" align="center" valign="middle" nowrap style="white-space: nowrap" class="titlebar">' . '&nbsp;&nbsp;&nbsp;' . $user_name . "\n"
			. ($this->mArgs['newwindowwidget'] == 'true' ? "<a href=\"#\" onClick=\"WuiWindowOpen('" . $new_window_event->getEventsCallString() . "','" . $win_name
			. "','width=600,height=400,resizable=yes,scrollbars=yes')\"><img src=\"" . $this->mThemeHandler->mStyle['windownew'] . '" border="0" style="width: 16px; height: 16px;" alt=""></a>&nbsp;' : '')
			. ($this->mArgs['closewidget'] == 'true' ? "<a href=\"main\" onClick=\"WuiWindowClose()\"><img src=\"" . $this->mThemeHandler->mStyle['windowclose'] . "\" border=\"0\" style=\"width: 16px; height: 16px;\" alt=\"\"></a>\n" : '')
			. "</td>\n" . //onClick="window.open('http://www.pageresource.com/jscript/jex5.htm','mywindow','width=400,height=200')"
        	"</tr>\n</table>\n" . ($this->mComments ? '<!-- end ' . $this->mName . " titlebar -->\n" : '');

$this->mLayout = '';

        return true;
    }
}
