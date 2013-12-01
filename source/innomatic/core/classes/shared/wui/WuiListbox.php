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
class WuiListbox extends WuiWidget
{
    /*! @public mElements array - Array of the elements. */
    //public $mElements;
    /*! @public mDefault string - Id of the default item. */
    //public $mDefault;
    /*! @public $mMultiSelect bool - True is multiple items can be selected. */
    //public $mMultiSelect;
    /*! @public $mSize int - List rows. */
    //public $mSize;
    //public $mDisp;
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
        if ($this->mArgs['size'] < 2)
            $this->mArgs['size'] = 2;
        if (isset($this->mArgs['default']) and is_array($this->mArgs['default'])) {
        } else
            if (isset($this->mArgs['default'])) {
                $def = $this->mArgs['default'];
                $this->mArgs['default'] = array();
                $this->mArgs['default'][] = $def;
            }
    }
    protected function generateSource()
    {
        if (is_array($this->mArgs['elements'])) {
            require_once ('innomatic/wui/dispatch/WuiEventRawData.php');
            $event_data = new WuiEventRawData(isset($this->mArgs['disp']) ? $this->mArgs['disp'] : '', $this->mName);
            $this->mLayout = ($this->mComments ? '<!-- begin ' . $this->mName . " listbox -->\n" : '') . '<select'.(isset($this->mArgs['id']) ? ' id="'.$this->mArgs['id'].'"' : ''). $this->getEventsCompleteString().' ' . ((isset($this->mArgs['hint']) and strlen($this->mArgs['hint'])) ? 'onMouseOver="wuiHint(\'' . str_replace("'", "\'", $this->mArgs['hint']) . '\');" onMouseOut="wuiUnHint();" ' : '') . 'name="' . $event_data->getDataString() . ((isset($this->mArgs['multiselect']) and $this->mArgs['multiselect'] == 'true') ? '[]' : '') . '" size="' . $this->mArgs['size'] . '"' . ((isset($this->mArgs['multiselect']) and $this->mArgs['multiselect'] == 'true') ? ' multiple' : '') . ' tabindex="' . $this->mArgs['tabindex'] . '"' . ">\n";
            reset($this->mArgs['elements']);
            if (sizeof($this->mArgs['elements'])) {
                while (list ($key, $val) = each($this->mArgs['elements'])) {
                    $this->mLayout .= '<option value="' . $key . '"' . ((isset($this->mArgs['default']) and is_array($this->mArgs['default']) and in_array($key, $this->mArgs['default'])) ? ' selected' : '') . '>' . Wui::utf8_entities($val) . "</option>\n";
                }
            } else {
                $this->mLayout .= '<option value=""> </option>' . "\n";
            }
            $this->mLayout .= "</select>\n" . ($this->mComments ? '<!-- end ' . $this->mName . " listbox -->\n" : '');
        }
        return true;
    }
}
