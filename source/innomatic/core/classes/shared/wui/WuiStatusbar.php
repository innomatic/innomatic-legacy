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
class WuiStatusbar extends WuiWidget
{
    //public $mStatus;
    public function __construct (
        $elemName,
        $elemArgs = '',
        $elemTheme = '',
        $dispEvents = ''
    )
    {
        parent::__construct($elemName, $elemArgs, $elemTheme, $dispEvents);
    }
    protected function generateSource()
    {
        $this->mLayout = ($this->mComments ? '<!-- begin ' . $this->mName
            . ' statusbar -->' : '');
        if (isset($this->mArgs['status']) and
        strlen($this->mArgs['status'])) {
        $this->mLayout .= '<table border="0" width="100%" cellspacing="0" '
            . 'cellpadding="3" bgcolor="'
            . $this->mThemeHandler->mColorsSet['statusbars']['bgcolor']
            . "\">\n";
        $this->mLayout .= "<tr>\n";
        $this->mLayout .= '<td class="status" nowrap style="white-space: '
            . 'nowrap">' . ((isset($this->mArgs['status']) and
            strlen($this->mArgs['status'])) ?
            Wui::utf8_entities($this->mArgs['status']) : '&nbsp;')
            . "</td>\n";
        $this->mLayout .= '<td width="100%">&nbsp;</td></tr>' . "\n"
            . '</table>' . "\n";
        }

        $this->mLayout .= ($this->mComments ? '<!-- end ' . $this->mName
            . " statusbar -->\n" : '');
        return true;
    }
}
