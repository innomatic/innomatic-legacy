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

/**
 * @package WUI
 */
class WuiVertgroup extends \Innomatic\Wui\Widgets\WuiContainerWidget
{
    /*! @public mAlign string - Alignment of group elements. */
    //public $mAlign;
    /*! @public mGroupAlign string - Group horizontal alignment. */
    //public $mGroupAlign;
    /*! @public mGroupValign string - Group vertical alignment. */
    //public $mGroupValign;
    /*! @public mHeight string - Group height. */
    //public $mHeight;
    /*! @public mWidth string - Group width. */
    //public $mWidth;
    /*!
     @function WuiVertgroup

     @abstract Class constructor.

     @discussion Class constructor.
     */
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
        } else {
            $this->mArgs['align'] = 'left';
        }
        if (isset($this->mArgs['groupalign'])) {
            switch ($this->mArgs['groupalign']) {
                case 'left':
                case 'center':
                case 'right':
                    break;
                default:
                    $this->mArgs['groupalign'] = 'left';
            }
        } else
            $this->mArgs['groupalign'] = 'left';
        if (isset($this->mArgs['groupvalign'])) {
            switch ($this->mArgs['groupvalign']) {
                case 'top':
                case 'middle':
                case 'bottom':
                    break;
                default:
                    $this->mArgs['groupvalign'] = 'middle';
            }
        } else
            $this->mArgs['groupvalign'] = 'middle';

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
        return ($this->mComments ? '<!-- begin ' . $this->mName . " vertgroup -->\n" : '') .
        ($this->mArgs['scrollable'] == 'true' ? '<div style="'.(isset($this->mArgs['height']) ? 'height: '.$this->mArgs['height'].'px; ' : '' ).(isset($this->mArgs['width']) ? 'width: '.$this->mArgs['width'].'px; ' : '').'overflow: auto">' : '').
        '<table border="0" cellspacing="1" cellpadding="0"' . ((isset($this->mArgs['groupalign']) and strlen($this->mArgs['groupalign'])) ? ' align="' . $this->mArgs['groupalign'] . '"' : '') . ((isset($this->mArgs['groupvalign']) and strlen($this->mArgs['groupvalign'])) ? ' valign="' . $this->mArgs['groupvalign'] . '"' : '') . ((isset($this->mArgs['height']) and strlen($this->mArgs['height'])) ? ' height="' . $this->mArgs['height'] . '"' : '') . ((isset($this->mArgs['width']) and strlen($this->mArgs['width'])) ? ' width="' . $this->mArgs['width'] . '"' : '') . ">\n";
    }
    protected function generateSourceEnd()
    {
        return "</table>\n" .
          ($this->mArgs['scrollable'] == 'true' ? '</div>' : '').
         ($this->mComments ? "<!-- end " . $this->mName . " vertgroup -->\n" : '');
    }
    protected function generateSourceBlockBegin()
    {
        return '<tr><td' . ($this->mArgs['align'] ? ' align="' . $this->mArgs['align'] . '"' : '') . ((isset($this->mArgs['width']) and strlen($this->mArgs['width'])) ? ' width="' . $this->mArgs['width'] . '"' : '') . ">\n";
    }
    protected function generateSourceBlockEnd()
    {
        return "</td></tr>\n";
    }
}
