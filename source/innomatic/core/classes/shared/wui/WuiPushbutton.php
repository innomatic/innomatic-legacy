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
class WuiPushbutton extends WuiWidget
{
    public $mValue;
    public $mDisp;
    public $mLabel;
    public $mImage;
    public $mHint;
    public $mType;
    public $mNeedConfirm;
    public $mConfirmMessage;
    /*! @public mTabIndex integer - Position of the current element in the tabbing order. */
    public $mTabIndex = 0;
    public function __construct (
        $elemName,
        $elemArgs = '',
        $elemTheme = '',
        $dispEvents = ''
    )
    {
        parent::__construct($elemName, $elemArgs, $elemTheme, $dispEvents);
        if (isset($this->mArgs['value']))
            $this->mValue = $this->mArgs["value"];
        if (isset($this->mArgs['disp']))
            $this->mDisp = $this->mArgs["disp"];
        if (isset($this->mArgs['label']))
            $this->mLabel = $this->mArgs["label"];
        if (isset($this->mArgs['image']))
            $this->mImage = $this->mArgs["image"];
        if (isset($this->mArgs['hint']))
            $this->mHint = $this->mArgs["hint"];
        if (isset($this->mArgs['tabindex']))
            $this->mTabIndex = $this->mArgs['tabindex'];
        if (isset($this->mArgs['type']) and ($this->mArgs["type"] == "submit" or $this->mArgs["type"] == "reset"))
            $this->mType = $this->mArgs["type"];
    }
    protected function generateSource()
    {
        $result = false;
        $event_data = new WuiEventRawData($this->mDisp, $this->mName);
        $this->mLayout = ($this->mComments ? "<!-- begin " . $this->mName . " push button -->" : "") . '<button'.(isset($this->mArgs['id']) ? ' id="'.$this->mArgs['id'].'"' : ''). $this->getEventsCompleteString() . ' class="normal" ' . "name=\"" . $event_data->getDataString() . "\"" . (strlen($this->mValue) ? " value=\"" . Wui::utf8_entities($this->mValue) . "\"" : "") . ' tabindex="' . $this->mTabIndex . '"' . (strlen($this->mType) ? " type=\"" . $this->mType . "\"" : "") . ($this->mNeedConfirm == 'true' ? ' onclick="return confirm(\'' . $this->mConfirmMessage . '\')"' : '') . ">" . $this->mLabel . (strlen($this->mImage) ? "<img src=\"" . $this->mImage . "\"" . (strlen($this->mHint) ? " alt=\"" . Wui::utf8_entities($this->mHint) . "\"" : "") . ">" : "") . "</button>" . ($this->mComments ? "<!-- end " . $this->mName . " push button -->\n" : "");
        $result = true;
        return $result;
    }
}
