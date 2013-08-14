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
require_once ('innomatic/wui/widgets/WuiWidget.php');
/**
 * @package WUI
 */
class WuiLabel extends WuiWidget
{
    /*! @public mLabel string - Label text. */
    //public $mLabel;
    /*! @public mNoWrap string - 'true' if the text may be automatically wrapped when necessary. Defaults to 'true'. */
    //public $mNoWrap;
    /*! @public mAlign string - Text alignment, may be one of 'left', 'center', 'right'. */
    //public $mAlign;
    /*! @public mBold string - 'true' if the text should be rendered in bold style. */
    //public $mBold;
    /*! @public mUnderline string - 'true' if the text should be rendered in underline style. */
    //public $mUnderline;
    //public $mCompact;
    /*! @public mHint string - Optional hint message. */
    //public $mHint;
    //public $mColor;
    public function __construct (
        $elemName,
        $elemArgs = '',
        $elemTheme = '',
        $dispEvents = ''
    )
    {
        parent::__construct($elemName, $elemArgs, $elemTheme, $dispEvents);
        if (isset($this->mArgs['nowrap'])) {
            switch ($this->mArgs['nowrap']) {
                case 'true':
                case 'false':
                    break;
                default:
                    $this->mArgs['nowrap'] = 'true';
            }
        } else {
            $this->mArgs['nowrap'] = 'true';
        }
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
    }
    protected function generateSource ()
    {
        $this->mLayout = ($this->mComments ? '<!-- begin ' . $this->mName . ' label -->' : '') . '<table'.(isset($this->mArgs['id']) ? ' id="'.$this->mArgs['id'].'"' : '').' ' . ((isset($this->mArgs['hint']) and strlen($this->mArgs['hint'])) ? 'onMouseOver="wuiHint(\'' . str_replace("'", "\'", $this->mArgs['hint']) . '\');" onMouseOut="wuiUnHint(); ' : '') . ' border="0" ' . ($this->mArgs['nowrap'] == 'true' ? 'width="0%"' : '') . ' height="0%"' . ((isset($this->mArgs['compact']) and $this->mArgs['compact'] == 'true') ? ' cellpadding="1" cellspacing="0"' : '') . '><tr><td align="' . $this->mArgs['align'] . '" class="normal" ' . ($this->mArgs['nowrap'] == 'true' ? 'nowrap style="white-space: nowrap"' : '') . '>' . ((isset($this->mArgs['bold']) and $this->mArgs['bold'] == 'true') ? '<strong>' : '') . ((isset($this->mArgs['underline']) and $this->mArgs['underline'] == 'true') ? '<u>' : '') . (isset($this->mArgs['color']) ? '<font color="' . $this->mArgs['color'] . '">' : '') . $this->mArgs['label'] . (isset($this->mArgs['color']) ? '</font>' : '') . ((isset($this->mArgs['underline']) and $this->mArgs['underline'] == 'true') ? '</u>' : '') . ((isset($this->mArgs['bold']) and $this->mArgs['bold'] == 'true') ? '</strong>' : '') . '</td></tr></table>' . ($this->mComments ? '<!-- end ' . $this->mName . " label -->\n" : '');
        return true;
    }
}
