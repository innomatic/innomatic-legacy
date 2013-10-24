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
class WuiGrid extends WuiContainerWidget
{
    //public $mCells;
    //public $mRows;
    //public $mCols;
    //public $mCompact;
    public function __construct ($elemName, $elemArgs = '', $elemTheme = '', $dispEvents = '')
    {
        $this->WuiContainerWidget($elemName, $elemArgs, $elemTheme, $dispEvents);
        if (isset($this->mArgs['compact']))
            $this->mArgs['compact'] = $this->mArgs['compact'] == 'true' ? 'true' : 'false';
        else
            $this->mArgs['compact'] = 'false';
    }
    public function addChild (WuiWidget $childWidget, $row, $col, $halign = '', $valign = '')
    {
        if (! isset($this->mArgs['rows']) or $row >= $this->mArgs['rows']) {
            $this->mArgs['rows'] = $row + 1;
        }
        if (! isset($this->mArgs['cols']) or $col >= $this->mArgs['cols']) {
            $this->mArgs['cols'] = $col + 1;
        }
        $this->mArgs['cells'][$row][$col]['widget'] = $childWidget;
        if ($halign == 'left' or $halign == 'center' or $halign == 'right')
            $this->mArgs['cells'][$row][$col]['halign'] = $halign;
        if ($valign == 'top' or $valign == 'middle' or $valign == 'bottom')
            $this->mArgs['cells'][$row][$col]['valign'] = $valign;
        return true;
    }
    public function build (WuiDispatcher $rwuiDisp)
    {
        $result = false;
        $this->mrWuiDisp = $rwuiDisp;
        if (isset($this->mArgs['rows']) and $this->mArgs['rows'] and isset($this->mArgs['cols']) and $this->mArgs['cols']) {
            $this->mLayout = ($this->mComments ? '<!-- begin ' . $this->mName . " grid -->\n" : '') . '<table'.(isset($this->mArgs['id']) ? ' id="'.$this->mArgs['id'].'"' : '').' border="0"' . ($this->mArgs['compact'] == 'true' ? ' cellpadding="0" cellspacing="0"' : '') . '>';
            for ($row = 0; $row < $this->mArgs['rows']; $row ++) {
                $this->mLayout .= "<tr>\n";
                for ($col = 0; $col < $this->mArgs['cols']; $col ++) {
                    $this->mLayout .= '<td' . (isset($this->mArgs['cells'][$row][$col]['halign']) ? ' align="' . $this->mArgs['cells'][$row][$col]['halign'] . '"' : '') . (isset($this->mArgs['cells'][$row][$col]['valign']) ? ' valign="' . $this->mArgs['cells'][$row][$col]['valign'] . '"' : '') . ">\n";
                    $elem = '';
                    if (isset($this->mArgs['cells'][$row][$col]['widget']) and is_object($this->mArgs['cells'][$row][$col]['widget'])) {
                        if ($this->mArgs['cells'][$row][$col]['widget']->Build($this->mrWuiDisp))
                            $elem = $this->mArgs['cells'][$row][$col]['widget']->render();
                    } else {
                        $elem = '&nbsp;';
                    }
                    $this->mLayout .= $elem;
                    $this->mLayout .= "</td>\n";
                }
                $this->mLayout .= "</tr>\n";
            }
            $this->mLayout .= "</table>\n" . ($this->mComments ? '<!-- end ' . $this->mName . " grid -->\n" : '');
            $this->mBuilt = true;
            $result = true;
        }
        return $result;
    }
}
