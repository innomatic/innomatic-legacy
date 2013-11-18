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
require_once ('innomatic/wui/widgets/WuiWidget.php');
/**
 * @package WUI
 */
class WuiSubmit extends WuiWidget
{
    //public $mCaption;
    //public $mHint;
    //public $mNeedConfirm;
    //public $mConfirmMessage;
    /*! @public mTabIndex integer - Position of the current element in the
    tabbing order. */
    //public $mTabIndex = 0;
    public function __construct (
        $elemName,
        $elemArgs = '',
        $elemTheme = '',
        $dispEvents = ''
    )
    {
        parent::__construct($elemName, $elemArgs, $elemTheme, $dispEvents);
        if (! isset($this->mArgs['tabindex'])) {
            $this->mArgs['tabindex'] = 0;
        }
    }
    protected function generateSource ()
    {
        $result = false;
        $this->mLayout = ($this->mComments ? '<!-- begin ' . $this->mName
            . ' submit -->' : '') . '<input'.(isset($this->mArgs['id']) ? ' id="'.$this->mArgs['id'].'"' : '').$this->getEventsCompleteString().' class="normal" type="submit"'
            . ($this->mArgs['caption'] ? ' value="'
            . Wui::utf8_entities($this->mArgs['caption']) . '"' : '')
            . ' tabindex="' . $this->mArgs['tabindex'] . '"' 
            . ((isset($this->mArgs['needconfirm']) and 
            $this->mArgs['needconfirm'] == 'true') ? 
            ' onclick="return confirm(\'' . $this->mArgs['confirmmessage'] 
            . '\')"' : '') . ((isset($this->mArgs['hint'])
            and $this->mArgs['hint']) ? ' alt="' . $this->mArgs['hint']
            . '"' : '') . '>' . ($this->mComments ? '<!-- end ' . $this->mName
            . " submit -->\n" : '');
        $result = true;
        return $result;
    }
}
