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
class WuiGrid extends \Innomatic\Wui\Widgets\WuiContainerWidget
{
    //public $mCells;
    //public $mRows;
    //public $mCols;
    //public $mCompact;
    public function __construct($elemName, $elemArgs = '', $elemTheme = '', $dispEvents = '')
    {
        parent::__construct($elemName, $elemArgs, $elemTheme, $dispEvents);
        if (isset($this->mArgs['compact']))
            $this->mArgs['compact'] = $this->mArgs['compact'] == 'true' ? 'true' : 'false';
        else
            $this->mArgs['compact'] = 'false';
    }
    public function addChild(\Innomatic\Wui\Widgets\WuiWidget $childWidget, $row = '', $col = '', $halign = '', $valign = '', $colspan = 0, $rowspan = 0)
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

        // Rowspan and colspan
        $this->mArgs['cells'][$row][$col]['colspan'] = (int)$colspan;
        $this->mArgs['cells'][$row][$col]['rowspan'] = (int)$rowspan;

        return true;
    }
    public function build(\Innomatic\Wui\Dispatch\WuiDispatcher $rwuiDisp)
    {
        $result = false;
        $spannedCells = array();

        $this->mrWuiDisp = $rwuiDisp;
        if (isset($this->mArgs['rows']) and $this->mArgs['rows'] and isset($this->mArgs['cols']) and $this->mArgs['cols']) {
            $this->mLayout = ($this->mComments ? '<!-- begin ' . $this->mName . " grid -->\n" : '') . '<table'.(isset($this->mArgs['id']) ? ' id="'.$this->mArgs['id'].'"' : '').' border="0"' . ($this->mArgs['compact'] == 'true' ? ' cellpadding="0" cellspacing="0"' : '') . '>';
            for ($row = 0; $row < $this->mArgs['rows']; $row ++) {
                $this->mLayout .= "<tr>\n";
                for ($col = 0; $col < $this->mArgs['cols']; $col ++) {
                    // It this cell is inside a previous cell rowspan, skip it
                    if (isset($spannedCells[$row][$col]) and $spannedCells[$row][$col] === true) {
                        continue;
                    }

                    $this->mLayout .= '<td' .
                        (isset($this->mArgs['cells'][$row][$col]['halign']) ? ' align="' . $this->mArgs['cells'][$row][$col]['halign'] . '"' : '') .
                        (isset($this->mArgs['cells'][$row][$col]['valign']) ? ' valign="' . $this->mArgs['cells'][$row][$col]['valign'] . '"' : '') .
                        (isset($this->mArgs['cells'][$row][$col]['colspan']) and $this->mArgs['cells'][$row][$col]['colspan'] > 0 ? ' colspan="' . $this->mArgs['cells'][$row][$col]['colspan'] . '"' : '') .
                        (isset($this->mArgs['cells'][$row][$col]['rowspan']) and $this->mArgs['cells'][$row][$col]['rowspan'] > 0 ? ' rowspan="' . $this->mArgs['cells'][$row][$col]['rowspan'] . '"' : '') .
                        ">\n";
                    $elem = '';
                    if (isset($this->mArgs['cells'][$row][$col]['widget']) and is_object($this->mArgs['cells'][$row][$col]['widget'])) {
                        if ($this->mArgs['cells'][$row][$col]['widget']->Build($this->mrWuiDisp))
                            $elem = $this->mArgs['cells'][$row][$col]['widget']->render();
                    } else {
                        $elem = '&nbsp;';
                    }
                    $this->mLayout .= $elem;
                    $this->mLayout .= "</td>\n";

                    // Keep track of rowspanned cells
                    if (isset($this->mArgs['cells'][$row][$col]['rowspan']) and $this->mArgs['cells'][$row][$col]['rowspan'] > 1) {
                        for ($i = 0; $i < $this->mArgs['cells'][$row][$col]['rowspan']; $i++) {
                            $spannedCells[$row+$i][$col] = true;
                        }
                    }
                    if (isset($this->mArgs['cells'][$row][$col]['colspan']) and $this->mArgs['cells'][$row][$col]['colspan'] > 0) {
                        $col += $this->mArgs['cells'][$row][$col]['colspan'] - 1;
                    }
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
