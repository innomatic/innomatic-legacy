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
class WuiHorizFrame extends WuiContainerWidget
{
    //public $mAlign;
    //public $mBgColor;
    public function __construct (
        $elemName,
        $elemArgs = '',
        $elemTheme = '',
        $dispEvents = ''
    )
    {
        $this->WuiContainerWidget($elemName, $elemArgs, $elemTheme, $dispEvents);
        if (isset($this->mArgs['align'])) {
            switch ($this->mArgs['align']) {
                case 'top':
                case 'middle':
                case 'bottom':
                    break;
                default:
                    $this->mArgs['align'] = 'middle';
            }
        } else
            $this->mArgs['align'] = 'left';
        if (! isset($this->mArgs['bgcolor']))
            $this->mArgs['bgcolor'] = 'white';

		if (!isset($this->mArgs['width'])) {
			$this->mArgs['width'] = "100%";
		}
    }
    protected function generateSourceBegin ()
    {
        $block = ($this->mComments ? '<!-- begin ' . $this->mName . ' horizframe -->' : '');
        $block .= '<table border="0" width="100%" cellspacing="0" cellpadding="1"><tr><td bgcolor="' . $this->mThemeHandler->mColorsSet['bars']['color'] . "\">\n";
        $block .= '<table border="0" width="100%" cellspacing="0" cellpadding="1"><tr><td bgcolor="' . $this->mThemeHandler->mColorsSet['bars']['shadow'] . "\">\n";
        $block .= '<table border="0" width="100%" cellspacing="0" cellpadding="0" bgcolor="' . $this->mArgs['bgcolor'] . "\">\n";
        $block .= "<tr>\n";
        return $block;
    }
    protected function generateSourceEnd ()
    {
        $block = '<td width="100%" bgcolor="white">&nbsp;</td></tr>' . "\n" . '</table>' . "\n";
        $block .= '</td></tr>' . "\n" . '</table>' . "\n";
        $block .= '</td></tr>' . "\n" . '</table>' . "\n";
        $block .= ($this->mComments ? '<!-- end ' . $this->mName . " horizframe -->\n" : '');
        return $block;
    }
    protected function generateSourceBlockBegin ()
    {
        return '<td width="100%"' . ($this->mArgs['align'] ? ' align="' . $this->mArgs['align'] . '"' : '') . '>';
    }
    protected function generateSourceBlockEnd ()
    {
        return "</td>\n";
    }
}