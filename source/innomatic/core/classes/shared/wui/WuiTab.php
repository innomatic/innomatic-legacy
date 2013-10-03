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
class WuiTab extends WuiContainerWidget
{
    //public $mTabs;
    //public $mActiveTab;
    //public $mTabPages = array();
    //public $mTabActionFunction;
    //public $mTabRows = 1;
    public function __construct (
        $elemName,
        $elemArgs = '',
        $elemTheme = '',
        $dispEvents = ''
    )
    {
        $this->WuiContainerWidget($elemName, $elemArgs, $elemTheme, $dispEvents);
        $tmp_sess = $this->RetrieveSession();
        if (! isset($this->mArgs['tabrows'])) {
            $this->mArgs['tabrows'] = 1;
        }
        if (! isset($this->mArgs['compact'])) {
            $this->mArgs['compact'] = 'false';
        }
        if (isset($this->mArgs['activetab']) and strlen($this->mArgs['activetab']))
            $this->mArgs['activetab'] = $this->mArgs['activetab'];
        else 
            if (isset($tmp_sess['activetab']) and strlen($tmp_sess['activetab']))
                $this->mArgs['activetab'] = $tmp_sess['activetab'];
            else
                $this->mArgs['activetab'] = 0;
        $this->StoreSession(array('activetab' => $this->mArgs['activetab']));
    }
    public function addChild (WuiWidget $childWidget)
    {
        $this->mArgs['tabpages'][] = $childWidget;
        return true;
    }
    public function build (WuiDispatcher $rwuiDisp)
    {
        $result = false;
        $this->mrWuiDisp = $rwuiDisp;
        $this->mLayout = ($this->mComments ? '<!-- begin ' . $this->mName . " tab -->\n" : '');
        $this->mLayout .= '<table border="0" width="100%" cellspacing="' . ($this->mArgs['compact'] == 'true' ? 1 : 2) . '" cellpadding="0"><tr><td bgcolor="' . $this->mThemeHandler->mColorsSet['tables']['gridcolor'] . "\">\n";
        $this->mLayout .= '<table border="0" width="100%" cellspacing="1" cellpadding="' . ($this->mArgs['compact'] == 'true' ? 1 : 6) . '" bgcolor="' . $this->mThemeHandler->mColorsSet['tables']['gridcolor'] . "\">\n<tr>";
        $rel_width = 100;
        if (count($this->mArgs['tabs']) and strlen($this->mArgs['tabactionfunction'])) {
            //$rel_width = 100 / ceil(count($this->mArgs['tabs']) / $this->mArgs['tabrows']) + 1;
            $rel_width = 0;
            if ($this->mArgs['activetab'] > (count($this->mArgs['tabs']) - 1))
                $this->mArgs['activetab'] = count($this->mArgs['tabs']) - 1;
            $tab_counter = 0;
            $elem = '';
            $curr_tab_row = 0;
            $tab_row_cell = 0;
            $rows_start = true;
            while (list (, $tab) = each($this->mArgs['tabs'])) {
                $func_name = $this->mArgs['tabactionfunction'];
                $this->mLayout .= '<td style="cursor: pointer;' . ($tab_counter == $this->mArgs['activetab'] ? ' border-top: solid ' . $this->mThemeHandler->mColorsSet['tables']['gridcolor'] . ' 2px;' : '') . '" bgcolor="' . ($tab_counter == $this->mArgs['activetab'] ? $this->mThemeHandler->mColorsSet['buttons']['selected'] : $this->mThemeHandler->mColorsSet['pages']['bgcolor']) . '" width="' . $rel_width . '%" align="center" nowrap' . ($tab_counter != $this->mArgs['activetab'] ? ' onMouseOver="this.style.backgroundColor=\'' . $this->mThemeHandler->mColorsSet['buttons']['notselected'] . '\';wuiHint(\'' . str_replace("'", "\'", $this->mArgs['tabs'][$tab_counter]['label']) . '\')" onMouseOut="this.style.backgroundColor=\'' . $this->mThemeHandler->mColorsSet['pages']['bgcolor'] . '\';wuiUnHint()" onClick="this.style.backgroundColor=\'' . $this->mThemeHandler->mColorsSet['buttons']['selected'] . '\';location.href=\'' . $func_name($tab_counter) . '\'"' : '') . '><table cellpadding="0" cellspacing="1" width="100%"><tr>';
                if ($tab_counter == $this->mArgs['activetab']) {
                    $this->mLayout .= '<td width="100%" align="center" valign="middle" nowrap style="white-space: nowrap;" class="boldbig">' . $this->mArgs['tabs'][$tab_counter]['label'] . '</td>';
                } else {
                    $this->mLayout .= '<td width="100%" align="center" valign="middle" nowrap style="white-space: nowrap" class="normalbig"><font color="' . $this->mThemeHandler->mColorsSet['buttons']['text'] . '">' . $this->mArgs['tabs'][$tab_counter]['label'] . '</font></td>';
                }
                if ($tab_counter == $this->mArgs['activetab'] and $this->mArgs['tabpages'][$tab_counter]->Build($this->mrWuiDisp))
                    $elem = $this->mArgs['tabpages'][$tab_counter]->render();
                $this->mLayout .= '</tr></table></td>';
                $tab_counter ++;
                if ($this->mArgs['tabrows'] > 1) {
                    $tab_row_cell ++;
                    if ($tab_row_cell == (ceil(count($this->mArgs['tabs']) / $this->mArgs['tabrows']))) {
                        if ($rows_start)
                            $this->mLayout .= '<td bgcolor="white" width="' . $rel_width . '%" rowspan="' . $this->mArgs['tabrows'] . '">&nbsp;</td></tr>';
                        $this->mLayout .= '</tr><tr>';
                        $tab_row_cell = 0;
                        $rows_start = false;
                    }
                }
            }
            if ($tab_row_cell) {
                $this->mLayout .= '<td bgcolor="white" colspan="' . (ceil(count($this->mArgs['tabs']) / $this->mArgs['tabrows']) - $tab_row_cell + 1) . '">&nbsp;</td></tr>';
            }
        }
        if ($this->mArgs['tabrows'] == 1)
            $this->mLayout .= '<td bgcolor="white" width="100%">&nbsp;</td></tr>';
        $this->mLayout .= '<tr><td bgcolor="white" colspan="' . (ceil(count($this->mArgs['tabs']) / $this->mArgs['tabrows']) + 1) . '">';
        $this->mLayout .= $elem;
        $this->mLayout .= '</td></tr>';
        $this->mLayout .= "</table></td></tr></table>\n";
        $this->mLayout .= ($this->mComments ? '<!-- end ' . $this->mName . " tab -->\n" : '');
        $this->mBuilt = true;
        $result = true;
        return $result;
    }
}
