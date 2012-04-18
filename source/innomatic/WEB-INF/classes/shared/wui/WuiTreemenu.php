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
class WuiTreeMenu extends WuiWidget
{
    /*! @public mElements array - Array of the treemenu elements. */
    public $mElements;
    /*! @public mWidth int - Width of the treemenu. */
    public $mWidth;
    /*! @public mActiveGroup string - Id of the active group. */
    public $mActiveGroup;
    /*! @public mTarget string - Target frame. */
    public $mTarget;
    /*! @public mAllGroupsActive - Set to 'true' if all groups should be showed as active. */
    public $mAllGroupsActive;
    public function __construct (
        $elemName,
        $elemArgs = '',
        $elemTheme = '',
        $dispEvents = ''
    )
    {
        parent::__construct($elemName, $elemArgs, $elemTheme, $dispEvents);
        $tmp_sess = $this->RetrieveSession();
        if (isset($this->mArgs['elements']))
            $this->mElements = $this->mArgs['elements'];
        if (isset($this->mArgs['width']))
            $this->mWidth = $this->mArgs['width'];
        if (isset($this->mArgs['activegroup']) and strlen($this->mArgs['activegroup']))
            $this->mActiveGroup = $this->mArgs['activegroup'];
        else
            $this->mActiveGroup = $tmp_sess['activegroup'];
        if (isset($this->mArgs['target']))
            $this->mTarget = $this->mArgs['target'];
        if (isset($this->mArgs['allgroupsactive']))
            $this->mAllGroupsActive = $this->mArgs['allgroupsactive'];
        if (isset($this->mArgs['activegroup']) and strlen($this->mActiveGroup)) {
            $this->StoreSession(array('activegroup' => $this->mActiveGroup));
        }
    }
    protected function generateSource ()
    {
        if ($this->mrWuiDisp->getEventName() == 'treemenu-' . $this->mName) {
            $disp_data = $this->mrWuiDisp->getEventData();
            if (isset($disp_data['activegroup'])) {
                $this->mActiveGroup = $disp_data['activegroup'];
                $this->StoreSession(array('activegroup' => $this->mActiveGroup));
            }
        }
        if (is_array($this->mElements)) {
            $this->mLayout = ($this->mComments ? '<!-- begin ' . $this->mName . ' treemenu -->' : '') . "<table border=\"0\"" . (strlen($this->mWidth) ? " width=\"" . $this->mWidth . "\"" : "") . ">\n";
            reset($this->mElements);
            while (list ($key, $val) = each($this->mElements)) {
                // Set default group
                //
                if (! $this->mActiveGroup)
                    $this->mActiveGroup = $key;
                if (($this->mAllGroupsActive == "true") or ($key == $this->mActiveGroup)) {
                    $this->mLayout .= "<tr><td align=\"center\" class=\"boldbig\"><center>" . "           <table width=\"100%\" border=\"0\" bgcolor=\"" . $this->mThemeHandler->mColorsSet['buttons']['selected'] . "\" cellspacing=\"0\" cellpadding=\"3\">
                        <tr>
                        <td><img src=\"" . $this->mThemeHandler->mStyle['arrowright'] . "\"></td>
                        <td valign=\"middle\" align=\"center\" width=\"100%\" class=\"boldbig\"><center>" . $val["groupname"] . "</center></td>
                        </tr>
                        </table>" . '</center></td></tr>';
                    if (isset($val['groupelements']) and is_array($val['groupelements'])) {
                        while (list ($keyitem, $valitem) = each($val['groupelements'])) {
                            $target = '';
                            if (isset($valitem['target']) and strlen($valitem['target']))
                                $target = $valitem['target'];
                            else {
                                if (strlen($this->mTarget))
                                    $target = $this->mTarget;
                            }
                            //if ( !isset($val['themesized'] ) )
                            $this->mLayout .= '<tr><td align="center" class="normal" ' . 'style="cursor: pointer;" ' . " onMouseOver=\"this.style.backgroundColor='" . $this->mThemeHandler->mColorsSet['buttons']['notselected'] . "'\" " . " onMouseOut=\"this.style.backgroundColor='" . $this->mThemeHandler->mColorsSet['pages']['bgcolor'] . "'\" " . ' onClick="this.style.backgroundColor=\'' . $this->mThemeHandler->mColorsSet['pages']['bgcolor'] . '\';' . (((strlen($target) and ($target != '_blank')) ? ($target == '_top' ? '' : 'parent.') . $target . '.' : '') . ($target == '_blank' ? 'window.open(\'' : 'location.href=\'') . $valitem['action'] . ($target == '_blank' ? '\')' : '\'')) . '"><center><img src="' . $valitem['image'] . '" border="0"' . ((isset($valitem['themesized']) and $valitem['themesized'] != 'false') ? ' style="width: 32px; height: 32px;"' : '') . '><br><font color="' . $this->mThemeHandler->mColorsSet['buttons']['text'] . '">' . $valitem['name'] . '</font></center></td></tr>';
                        }
                    }
                } else {
                    $events_call = new WuiEventsCall();
                    $events_call->addEvent(new WuiEvent("wui", "treemenu-" . $this->mName, array("activegroup" => $key)));
                    reset($this->mDispEvents);
                    while (list (, $event) = each($this->mDispEvents)) {
                        $events_call->addEvent($event);
                    }
                    $this->mLayout .= '<tr><td align="center" class="boldbig"><center>' . '           <table width="100%" style="cursor: pointer;" ' . " onMouseOver=\"this.style.backgroundColor='" . $this->mThemeHandler->mColorsSet['buttons']['selected'] . "'\" " . " onMouseOut=\"this.style.backgroundColor='" . $this->mThemeHandler->mColorsSet['buttons']['notselected'] . "'\" " . ' onClick="this.style.backgroundColor=\'' . $this->mThemeHandler->mColorsSet['buttons']['selected'] . '\';' . 'location.href=\'' . $events_call->getEventsCallString() . '\'" ' . "border=\"0\" bgcolor=\"" . $this->mThemeHandler->mColorsSet['buttons']['notselected'] . "\" cellspacing=\"0\" cellpadding=\"3\">
                        <tr>
                        <td><img src=\"" . $this->mThemeHandler->mStyle['arrowdown'] . "\" border=\"0\"></td>
                        <td valign=\"middle\" align=\"center\" width=\"100%\" class=\"boldbig\"><center>" . '<font color="' . $this->mThemeHandler->mColorsSet['buttons']['text'] . '">' . $val['groupname'] . '</font>' . '</center></td>
                        </tr>
                        </table>' . '</center></td></tr>';
                    unset($events_call);
                }
            }
            $this->mLayout .= "</table>\n" . ($this->mComments ? "<!-- end " . $this->mName . " treemenu -->" : "");
        }
        return true;
    }
}
