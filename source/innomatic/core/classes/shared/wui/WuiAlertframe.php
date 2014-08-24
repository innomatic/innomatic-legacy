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
 * @license    http://www.innomaticplatform.com/license/   BSD License
 * @link       http://www.innomaticplatform.com
 * @since      Class available since Release 5.0
 */
namespace Shared\Wui;

/**
 * @package WUI
 */
class WuiAlertframe extends \Innomatic\Wui\Widgets\WuiContainerWidget
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
        parent::__construct(
            $elemName,
            $elemArgs,
            $elemTheme,
            $dispEvents
        );
        if (isset($this->mArgs['align'])) {
            switch ($this->mArgs['align']) {
                case 'left':
                    // break was intentionally left
                case 'center':
                    // break was intentionally left
                case 'right':
                    break;
                default:
                    $this->mArgs['align'] = 'left';
            }
        } else {
            $this->mArgs['align'] = 'left';
        }
        if (! isset($this->mArgs['text'])) {
            $this->mArgs['text'] = '';
        }
        if (! isset($this->mArgs['bgcolor'])) {
            $this->mArgs['bgcolor'] = 'white';
        }
    }
    protected function generateSourceBegin()
    {
        $block = ($this->mComments ? '<!-- begin ' . $this->mName . ' vertframe -->' : '');
        $block .= '<table border="0" height="100%" cellspacing="0" ' . ((isset($this->mArgs['width']) and strlen($this->mArgs['width'])) ? 'width="' . $this->mArgs['width'] . '" ' : '') . 'cellpadding="1"><tr><td' . ">\n";
        $block .= '<table border="0" height="100%" cellspacing="0" ' . ((isset($this->mArgs['width']) and strlen($this->mArgs['width'])) ? 'width="' . $this->mArgs['width'] . '" ' : '') . 'cellpadding="2"><tr><td bgcolor="' . $this->mThemeHandler->mColorsSet['alert']['border'] . "\">\n";
        $block .= '<table border="0" height="100%" cellspacing="0" ' . ((isset($this->mArgs['width']) and strlen($this->mArgs['width'])) ? 'width="' . $this->mArgs['width'] . '" ' : '') . 'cellpadding="1" bgcolor="' . $this->mArgs['bgcolor'] . "\">\n";
        $block .= '<tr><td style="padding: 10px; text-align: center; border-bottom: 1px solid ' . $this->mThemeHandler->mColorsSet['alert']['border'] . '" bgcolor="' . $this->mThemeHandler->mColorsSet['alert']['bgcolor'] . '"><font color="' . $this->mThemeHandler->mColorsSet['alert']['text'] . '"><b>' . $this->mArgs['text'] . "</b></font></td></tr>\n";
        return $block;
    }
    protected function generateSourceEnd()
    {
        $block = '<tr><td height="100%" bgcolor="white"></td></tr>' . "\n" . '</table>' . "\n";
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
