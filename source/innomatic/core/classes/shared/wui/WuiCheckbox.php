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
class WuiCheckbox extends WuiWidget
{
    /*! @public mDisp string - Widget dispatcher. */
    //public $mDisp;
    /*! @public mChecked string - Set to 'true' if the widget is checked. */
    //public $mChecked;
    /*! @public mValue string - Checkbox value, useful for multiple checkboxes with same name. Optional. */
    //public $mValue;
    //public $mReadOnly;
    /*! @public mTabIndex integer - Position of the current element in the tabbing order. */
    //public $mTabIndex = 0;
    /*! @public mHint string - Optional hint message. */
    //public $mHint;
    public function __construct (
        $elemName,
        $elemArgs = '',
        $elemTheme = '',
        $dispEvents = ''
    )
    {
        parent::__construct($elemName, $elemArgs, $elemTheme, $dispEvents);
        if (! isset($this->mArgs['tabindex']))
            $this->mArgs['tabindex'] = 0;
    }
    protected function generateSource()
    {
        $result = false;
        require_once ('innomatic/wui/dispatch/WuiEventRawData.php');
        $event_data = new WuiEventRawData($this->mArgs['disp'], $this->mName);
        $this->mLayout = ($this->mComments ? '<!-- begin ' . $this->mName . ' check box -->' : '') . '<input'.(isset($this->mArgs['id']) ? ' id="'.$this->mArgs['id'].'"' : '').' class="normal" ' . $this->getEventsCompleteString() . ' ' . ((isset($this->mArgs['hint']) and strlen($this->mArgs['hint'])) ? 'onMouseOver="wuiHint(\'' . str_replace("'", "\'", $this->mArgs['hint']) . '\');" onMouseOut="wuiUnHint();" ' : '') . 'type="checkbox" ' . 'name="' . $event_data->getDataString() . '"' . ' tabindex="' . $this->mArgs['tabindex'] . '"' . ((isset($this->mArgs['value']) and strlen($this->mArgs['value'])) ? ' value="' . $this->mArgs['value'] . '"' : '') . ((isset($this->mArgs['readonly']) and strlen($this->mArgs['readonly'])) ? ' disabled' : '') . ($this->mArgs['checked'] == 'true' ? ' checked' : '') . '>' . ($this->mComments ? '<!-- end ' . $this->mName . " check box -->\n" : '');
        $result = true;
        return $result;
    }
}
