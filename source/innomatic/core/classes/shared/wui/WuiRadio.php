<?php
/**
 * Innomatic
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2014 Innomatic Company
 * @license    http://www.innomatic.io/license/ New BSD License
 * @link       http://www.innomatic.io
 * @since      Class available since Release 5.0
 */
namespace Shared\Wui;

/**
 * @package WUI
 */
class WuiRadio extends \Innomatic\Wui\Widgets\WuiWidget
{
    public $mValue;
    public $mDisp;
    public $mChecked;
    public $mLabel;
    public $mReadOnly;
    /*! @public mTabIndex integer - Position of the current element in the tabbing order. */
    public $mTabIndex = 0;
    /*! @public mHint string - Optional hint message. */
    public $mHint;
    public function __construct (
        $elemName,
        $elemArgs = '',
        $elemTheme = '',
        $dispEvents = ''
    )
    {
        parent::__construct($elemName, $elemArgs, $elemTheme, $dispEvents);
        if (isset($this->mArgs['value']))
            $this->mValue = $this->mArgs['value'];
        if (isset($this->mArgs['disp']))
            $this->mDisp = $this->mArgs['disp'];
        if (isset($this->mArgs['label']))
            $this->mLabel = $this->mArgs['label'];
        if (isset($this->mArgs['checked']))
            $this->mChecked = $this->mArgs['checked'];
        if (isset($this->mArgs['readonly']))
            $this->mReadOnly = $this->mArgs['readonly'];
        if (isset($this->mArgs['tabindex']))
            $this->mTabIndex = $this->mArgs['tabindex'];
        if (isset($this->mArgs['hint']))
            $this->mHint = $this->mArgs['hint'];
    }
    protected function generateSource()
    {
        $result = false;
        $event_data = new \Innomatic\Wui\Dispatch\WuiEventRawData($this->mDisp, $this->mName);
        $this->mLayout = ($this->mComments ? '<!-- begin ' . $this->mName . ' radio -->' : '') . '<table border="0" cellpadding="0" cellspacing="0"><tr><td valign="middle"><input'.(isset($this->mArgs['id']) ? ' id="'.$this->mArgs['id'].'"' : '').$this->getEventsCompleteString().' class="normal" ' . (strlen($this->mHint) ? 'onMouseOver="wuiHint(\'' . str_replace("'", "\'", $this->mHint) . '\');" onMouseOut="wuiUnHint();" ' : '') . 'type="radio" ' . 'name="' . $event_data->getDataString() . '"' . (strlen($this->mValue) ? ' value="' . $this->mValue . '"' : '') . ' tabindex="' . $this->mTabIndex . '"' . (strlen($this->mReadOnly) ? ' disabled' : '') . ($this->mChecked == 'true' ? ' checked' : '') . '></td><td valign="middle">' . \Innomatic\Wui\Wui::utf8_entities($this->mLabel) . '</td></tr></table>' . ($this->mComments ? '<!-- end ' . $this->mName . " radio -->\n" : '');
        $result = true;
        return $result;
    }
}
