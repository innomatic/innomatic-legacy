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
 * Status bar WUI widget.
 *
 */
class WuiStatusbar extends \Innomatic\Wui\Widgets\WuiWidget
{
    //public $mStatus;
    /*! @public mWidth string - Bar width. */
    //public $mWidth;
    public function __construct (
        $elemName,
        $elemArgs = '',
        $elemTheme = '',
        $dispEvents = ''
    ) {
        parent::__construct($elemName, $elemArgs, $elemTheme, $dispEvents);
        if (!isset($this->mArgs['width'])) {
            $this->mArgs['width'] = "100%";
        }
    }

    protected function generateSource()
    {
        $this->mLayout = ($this->mComments ? '<!-- begin ' . $this->mName . ' statusbar -->' : '');

        if (isset($this->mArgs['status']) and strlen($this->mArgs['status'])) {
            $this->mLayout .= '<table border="0" '
                . (isset($this->mArgs['width']) ? 'width="'.$this->mArgs['width'].'" ' : '')
                . ' cellspacing="0"'
                . ' cellpadding="3" bgcolor="'
                . $this->mThemeHandler->mColorsSet['statusbars']['bgcolor']
                . "\">\n";
            $this->mLayout .= "<tr>\n";
            $this->mLayout .= '<td class="status" nowrap style="white-space: nowrap;'
                . ((isset($this->mArgs['color']) and strlen($this->mArgs['color'])) ? 
                'color:'.$this->mArgs['color'].';' : '').'">'
                . ((isset($this->mArgs['status']) and strlen($this->mArgs['status'])) ?
                \Innomatic\Wui\Wui::utf8_entities($this->mArgs['status']) : '&nbsp;')
                . "</td>\n";
            if ($this->mArgs['width'] == "100%")
                $this->mLayout .= '<td width="100%">&nbsp;</td>';
            $this->mLayout .= "</tr>\n";
            $this->mLayout .= "</table>\n";
        }

        $this->mLayout .= ($this->mComments ? '<!-- end ' . $this->mName . " statusbar -->\n" : '');
        return true;
    }
}
