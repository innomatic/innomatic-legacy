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
require_once ('innomatic/wui/widgets/WuiContainerWidget.php');
/**
 * @package WUI
 */
class WuiTable extends WuiContainerWidget
{
    //public $mHeaders;
    //public $mCells;
    public $mRows;
    public $mCols;
    //public $mTopHeader;
    public $mRowsPerPage;
    public $mPageNumber;
    //public $mPagesActionFunction;
    //public $mPagesNavigatorPosition;
    //public $mPages;
    //public $mWidth;
    //public $mSortBy;
    //public $mSortDirection;
    public function __construct (
        $elemName,
        $elemArgs = '',
        $elemTheme = '',
        $dispEvents = ''
    )
    {
        $this->WuiContainerWidget($elemName, $elemArgs, $elemTheme, $dispEvents);
        $tmp_sess = $this->RetrieveSession();
        //if ( isset($this->mArgs['headers'] ) ) $this->mArgs['headers']        = $this->mArgs['headers'];
        $this->mRows = $this->mCols = 0;
        if (isset($this->mArgs['rows']))
            $this->mRows = $this->mArgs['rows'];
        if (isset($this->mArgs['cols']))
            $this->mCols = $this->mArgs['cols'];
        if (isset($this->mArgs['topheader']))
            $this->mArgs['topheader'] = $this->mArgs['topheader'];
            //if ( isset($this->mArgs['width'] ) ) $this->mArgs['width'] = $elemArgs['width'];
        if (isset($this->mArgs['pagenumber']) and strlen($this->mArgs['pagenumber']))
            $this->mPageNumber = $this->mArgs['pagenumber'];
        else 
            if (isset($tmp_sess['pagenumber']) and strlen($tmp_sess['pagenumber']))
                $this->mPageNumber = $tmp_sess['pagenumber'];
            else
                $this->mPageNumber = 1;
            //if ( isset($this->mArgs['pages'] ) ) $this->mPages = $elemArgs['pages'];
        if (isset($this->mArgs['pagesactionfunction'])) {
            $this->mArgs['pagesactionfunction'] = $this->mArgs['pagesactionfunction'];
            if (isset($this->mArgs['rowsperpage']))
                $this->mRowsPerPage = $this->mArgs['rowsperpage'];
            else 
                if (isset($tmp_sess['rowsperpage']))
                    $this->mRowsPerPage = $tmp_sess['rowsperpage'];
        }
        if (! isset($this->mArgs['pagesnavigatorposition']))
            $this->mArgs['pagesnavigatorposition'] = '';
        switch ($this->mArgs['pagesnavigatorposition']) {
            case 'top':
            case 'bottom':
                $this->mArgs['pagesnavigatorposition'] = $this->mArgs['pagesnavigatorposition'];
                break;
            default:
                $this->mArgs['pagesnavigatorposition'] = 'bottom';
        }
        if (isset($this->mArgs['sortby']) and strlen($this->mArgs['sortby']))
            $this->mArgs['sortby'] = $this->mArgs['sortby'];
        else 
            if (isset($tmp_sess['sortby']) and strlen($tmp_sess['sortby']))
                $this->mArgs['sortby'] = $tmp_sess['sortby'];
            else
                $this->mArgs['sortby'] = '';
        if (isset($this->mArgs['sortdirection']) and strlen($this->mArgs['sortdirection']))
            $this->mArgs['sortdirection'] = $this->mArgs['sortdirection'];
        else 
            if (isset($tmp_sess['sortdirection']) and strlen($tmp_sess['sortdirection']))
                $this->mArgs['sortdirection'] = $tmp_sess['sortdirection'];
            else
                $this->mArgs['sortdirection'] = 'down';
        $this->StoreSession(array('pagenumber' => $this->mPageNumber , 'sortby' => $this->mArgs['sortby'] , 'sortdirection' => $this->mArgs['sortdirection']));

		if (!isset($this->mArgs['width'])) {
			$this->mArgs['width'] = "100%";
		}
    }
    public function addChild (WuiWidget $childWidget, $row, $col, $halign = '', $valign = '', $nowrap = 'false', $width = '')
    {
        if ($row >= $this->mRows)
            $this->mRows = $row + 1;
        if ($col >= $this->mCols)
            $this->mCols = $col + 1;
        $this->mArgs['cells'][$row][$col]['widget'] = $childWidget;
        if ($halign == 'left' or $halign == 'center' or $halign == 'right')
            $this->mArgs['cells'][$row][$col]['halign'] = $halign;
        if ($valign == 'top' or $valign == 'middle' or $valign == 'bottom')
            $this->mArgs['cells'][$row][$col]['valign'] = $valign;
        $this->mArgs['cells'][$row][$col]['nowrap'] = $nowrap;
        $this->mArgs['cells'][$row][$col]['width'] = $width;
        return true;
    }
    public function build (WuiDispatcher $rwuiDisp)
    {
        $result = false;
        $this->mrWuiDisp = $rwuiDisp;
        if ($this->mRows and $this->mCols) {
            $this->mLayout = ($this->mComments ? '<!-- begin ' . $this->mName . ' table -->' . "\n" : '');
            $this->mLayout .= '<table'.(isset($this->mArgs['id']) ? ' id="'.$this->mArgs['id'].'"' : '').' border="0" cellspacing="2" cellpadding="1" ' . ((isset($this->mArgs['width']) and strlen($this->mArgs['width'])) ? 'width="' . $this->mArgs['width'] . '" ' : '') . "><tr><td bgcolor=\"" . $this->mThemeHandler->mColorsSet['tables']['gridcolor'] . "\">\n";
            $this->mLayout .= '<table border="0" width="100%" cellspacing="1" cellpadding="3" bgcolor="' . $this->mThemeHandler->mColorsSet['tables']['bgcolor'] . "\">\n";
            if (isset($this->mArgs['topheader']) and strlen($this->mArgs['topheader'])) {
                $this->mLayout .= '<tr><td colspan="' . $this->mCols . '" valign="top" align="center" bgcolor="' . $this->mThemeHandler->mColorsSet['tables']['headerbgcolor'] . '" class="bold">' . $this->mArgs['topheader'] . '</td></tr>' . "\n";
            }
            if (isset($this->mArgs['headers']) and count($this->mArgs['headers'])) {
                $this->mLayout .= "<tr>\n";
                for ($col = 0; $col < $this->mCols; $col ++) {
                    $this->mLayout .= '<td valign="top"><table cellpadding="4" cellspacing="1" width="100%"><tr>';
                    if (isset($this->mArgs['headers'][$col]['link']) and strlen($this->mArgs['headers'][$col]['link'])) {
                        $this->mLayout .= '<td valign="top" bgcolor="' . $this->mThemeHandler->mColorsSet['tables']['headerbgcolor'] . '"><a href="' . $this->mArgs['headers'][$col]['link'] . '"><img src="' . ($this->mArgs['sortby'] == $col ? ($this->mArgs['sortdirection'] == 'up' ? $this->mThemeHandler->mStyle['arrowup'] : $this->mThemeHandler->mStyle['arrowdown']) : $this->mThemeHandler->mStyle['arrowrightshadow']) . '" border="0" style="width: 11px; height: 11px;"></a></td>';
                    } else {
                        $this->mLayout .= '<td></td>';
                    }
                    $this->mLayout .= '<td width="100%" valign="top" align="center" bgcolor="' . $this->mThemeHandler->mColorsSet['tables']['headerbgcolor'] . '" class="bold">' . ((isset($this->mArgs['headers'][$col]['link']) and strlen($this->mArgs['headers'][$col]['link'])) ? '<a href="' . $this->mArgs['headers'][$col]['link'] . '">' : '') . ((isset($this->mArgs['headers'][$col]['label']) and strlen($this->mArgs['headers'][$col]['label'])) ? $this->mArgs['headers'][$col]['label'] : '&nbsp;') . ((isset($this->mArgs['headers'][$col]['link']) and strlen($this->mArgs['headers'][$col]['link'])) ? '</a>' : '') . "</td>\n";
                    $this->mLayout .= '</tr></table></td>';
                }
                $this->mLayout .= "</tr>\n";
            }
            if ($this->mRowsPerPage > 0)
                if ($this->mPageNumber > ceil($this->mRows / $this->mRowsPerPage))
                    $this->mPageNumber = ceil($this->mRows / $this->mRowsPerPage);
            $page_navigator = '';
            if ($this->mRowsPerPage and $this->mRowsPerPage < ($this->mRows)) {
                $page_navigator .= '<tr width="0"><td colspan="' . $this->mCols . '" width="0">';
                $func_name = $this->mArgs['pagesactionfunction'];
                if ($this->mPageNumber > 1)
                    $page_navigator .= '<a href="' . $func_name($this->mPageNumber - 1) . '">&#171;</a> ';
                for ($i = 1; $i <= ceil($this->mRows / $this->mRowsPerPage); $i ++) {
                    if ($this->mPageNumber == $i)
                        $page_navigator .= $i . ' ';
                    else
                        $page_navigator .= '<a href="' . $func_name($i) . '">' . $i . '</a> ';
                }
                if ($this->mPageNumber < ceil($this->mRows / $this->mRowsPerPage))
                    $page_navigator .= '<a href="' . $func_name($this->mPageNumber + 1) . '">&#187;</a>&nbsp;';
                $page_navigator .= '</td></tr>' . "\n";
            }
            if ($this->mArgs['pagesnavigatorposition'] == 'top')
                $this->mLayout .= $page_navigator;
            $from = ($this->mRowsPerPage * ($this->mPageNumber - 1));
            $to = ($this->mRowsPerPage ? $this->mRowsPerPage + ($this->mRowsPerPage * ($this->mPageNumber - 1)) : $this->mRows);
            if ($this->mRowsPerPage > 0 and $this->mPageNumber == ceil($this->mRows / $this->mRowsPerPage))
                $to = ($this->mRowsPerPage * ($this->mPageNumber - 1)) + ($this->mRows - ($this->mRowsPerPage * ($this->mPageNumber - 1)));
            for ($row = $from; $row < $to; $row ++) {
                $this->mLayout .= "<tr>\n";
                for ($col = 0; $col < $this->mCols; $col ++) {
                    $this->mLayout .= '<td bgcolor="white"' . (isset($this->mArgs['cells'][$row][$col]['halign']) ? ' align="' . $this->mArgs['cells'][$row][$col]['halign'] . "\"" : "") . (isset($this->mArgs['cells'][$row][$col]['valign']) ? ' valign="' . $this->mArgs['cells'][$row][$col]['valign'] . "\"" : "") . ((isset($this->mArgs['cells'][$row][$col]['nowrap']) and $this->mArgs['cells'][$row][$col]['nowrap'] == 'true') ? ' nowrap style="white-space: nowrap"' : '') . ((isset($this->mArgs['cells'][$row][$col]['width']) and strlen($this->mArgs['cells'][$row][$col]['width'])) ? ' width="' . $this->mArgs['cells'][$row][$col]['width'] . '"' : '') . ">\n";
                    $elem = '';
                    if (isset($this->mArgs['cells'][$row][$col]['widget']) and is_object($this->mArgs['cells'][$row][$col]['widget'])) {
                        if ($this->mArgs['cells'][$row][$col]['widget']->Build($this->mrWuiDisp))
                            $elem = $this->mArgs['cells'][$row][$col]['widget']->render();
                        $this->mArgs['cells'][$row][$col]['widget']->Destroy();
                    } else {
                        $elem = '&nbsp;';
                    }
                    $this->mLayout .= $elem;
                    $this->mLayout .= "</td>\n";
                }
                $this->mLayout .= "</tr>\n";
            }
            if ($this->mArgs['pagesnavigatorposition'] == 'bottom')
                $this->mLayout .= $page_navigator;
            $this->mLayout .= '</table></td></tr>' . "\n" . '</table>' . "\n";
            $this->mLayout .= ($this->mComments ? '<!-- end ' . $this->mName . ' table -->' . "\n" : '');
            $this->mBuilt = true;
            $result = true;
        }
        return $result;
    }
}
