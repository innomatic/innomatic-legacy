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

require_once ('innomatic/wui/widgets/WuiWidget.php');
/**
 * @package WUI
 */
class WuiTitlebar extends WuiWidget
{
    /*! @public mTitle string - Title shown in the title bar. */
    //public $mTitle;
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

        if (isset($this->mArgs['icon']) and strlen($this->mArgs['icon']))
            $this->mIcon = $this->mArgs['icon'];
        else
            $this->mIcon = 'empty_ascii';
    }

    protected function generateSource()
    {
        if (strlen($this->mIcon)) {
            if (file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'shared/icons/'.$this->mThemeHandler->mIconsSet['icons'][$this->mIcon]['base'] . '/icons/' . $this->mThemeHandler->mIconsSet['icons'][$this->mIcon]['file'])) {
                $iconname = $this->mIcon;
            } else {
                $iconname = 'document';
            }
            $icon = '<img id="stoppingAjax" src="' . $this->mThemeHandler->mIconsBase . $this->mThemeHandler->mIconsSet['icons'][$iconname]['base'] . '/icons/' . $this->mThemeHandler->mIconsSet['icons'][$iconname]['file'] . '" alt="" border="0" style="padding-left: 11px; padding-right: 1px; width: 30px; height: 30px;">';
        } else
            $icon = '';

        $GLOBALS['wui']['titlebar-title'] = $icon
            . "<img id=\"loadingAjax\" src=\"".$this->mThemeHandler->mStyle['ajax_big']."\" border=\"0\" style=\"padding-left: 10px; width:32px; height:32px; display:none;\"></td>\n"
            . "<td nowrap style=\"white-space: nowrap; padding-right: 15px; padding-left: 10px;\" class=\"paneltitle\" valign=\"middle\">"
            . $this->mArgs['title']
            . "</td>\n";

        return true;
    }
}
