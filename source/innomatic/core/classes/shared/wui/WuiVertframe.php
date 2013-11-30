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

require_once ('innomatic/wui/widgets/WuiContainerWidget.php');
/**
 * @package WUI
 */
class WuiVertframe extends WuiContainerWidget
{
    /*! @public mAlign string - Frame alignment. */
    //public $mAlign;
    /*! @public mWidth string - Frame widht, defaults to nothing. */
    //public $mWidth;
    //public $mBgColor;
    public function __construct (
        $elemName,
        $elemArgs = '',
        $elemTheme = '',
        $dispEvents = ''
    )
    {
        parent::__construct($elemName, $elemArgs, $elemTheme, $dispEvents);
        if (isset($this->mArgs['align'])) {
            switch ($this->mArgs['align']) {
                case 'left':
                case 'center':
                case 'right':
                    break;
                default:
                    $this->mArgs['align'] = 'left';
            }
        } else
            $this->mArgs['align'] = 'left';
        if (! isset($this->mArgs['bgcolor']))
            $this->mArgs['bgcolor'] = 'white';

        if (!isset($this->mArgs['width'])) {
            $this->mArgs['width'] = "100%";
        }

        if (isset($this->mArgs['scrollable'])) {
            switch ($this->mArgs['scrollable']) {
                case 'true':
                case 'false':
                    break;
                default:
                    $this->mArgs['scrollable'] = 'false';
            }
        } else {
            $this->mArgs['scrollable'] = 'false';
        }
    }
    protected function generateSourceBegin()
    {
        $block = ($this->mComments ? '<!-- begin ' . $this->mName . ' vertframe -->' : '');
        $block .= '<table border="0" height="100%" cellspacing="0" ' . ((isset($this->mArgs['width']) and strlen($this->mArgs['width'])) ? 'width="' . $this->mArgs['width'] . '" ' : '') . 'cellpadding="1"><tr><td bgcolor="' . $this->mThemeHandler->mColorsSet['bars']['color'] . "\">\n";
        $block .= '<table border="0" height="100%" cellspacing="0" ' . ((isset($this->mArgs['width']) and strlen($this->mArgs['width'])) ? 'width="' . $this->mArgs['width'] . '" ' : '') . 'cellpadding="1"><tr><td bgcolor="' . $this->mThemeHandler->mColorsSet['bars']['shadow'] . "\">\n";
        $block .= ($this->mArgs['scrollable'] == 'true' ? '<div style="'.(isset($this->mArgs['height']) ? 'height: '.$this->mArgs['height'].'px; ' : '' ).(isset($this->mArgs['width']) ? 'width: '.$this->mArgs['width'].'px; ' : '').'overflow: auto">' : '');
        $block .= '<table border="0" height="100%" cellspacing="0" ' . ((isset($this->mArgs['width']) and strlen($this->mArgs['width'])) ? 'width="' . $this->mArgs['width'] . '" ' : '') . 'cellpadding="0" bgcolor="' . $this->mArgs['bgcolor'] . "\">\n";
        return $block;
    }
    protected function generateSourceEnd()
    {
        $block = '<tr><td height="100%" bgcolor="white"></td></tr>' . "\n" . '</table>' . "\n";
        $block .= ($this->mArgs['scrollable'] == 'true' ? '</div>' : '');
        $block .= '</td></tr>' . "\n" . '</table>' . "\n";
        $block .= '</td></tr>' . "\n" . '</table>' . "\n";
        $block .= ($this->mComments ? '<!-- end ' . $this->mName . ' vertframe -->' . "\n" : '');
        return $block;
    }
    protected function generateSourceBlockBegin()
    {
        return '<tr><td' . ($this->mArgs['align'] ? ' align="' . $this->mArgs['align'] . '"' : '') . ((isset($this->mArgs['width']) and strlen($this->mArgs['width'])) ? ' width="' . $this->mArgs['width'] . '"' : '') . '>';
    }
    protected function generateSourceBlockEnd()
    {
        return '</td></tr>' . "\n";
    }
}
