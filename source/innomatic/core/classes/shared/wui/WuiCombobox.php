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
class WuiCombobox extends \Innomatic\Wui\Widgets\WuiWidget
{
    /*! @public mElements array - Array of the elements. */
    //public $mElements;
    /*! @public mDefault string - Id of the default item. */
    //public $mDefault;
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
    }
    protected function generateSource()
    {
        $result = false;
        if (isset($this->mArgs['elements']) and is_array($this->mArgs['elements']) and count($this->mArgs['elements'])) {
            $event_data = new \Innomatic\Wui\Dispatch\WuiEventRawData($this->mArgs['disp'], $this->mName);
            $this->mLayout = ($this->mComments ? '<!-- begin ' . $this->mName . " combobox -->\n" : '') . '<select'.(isset($this->mArgs['id']) ? ' id="'.$this->mArgs['id'].'"' : ''). $this->getEventsCompleteString() .' ' . ((isset($this->mArgs['hint']) and strlen($this->mArgs['hint'])) ? 'onMouseOver="wuiHint(\'' . str_replace("'", "\'", $this->mArgs['hint']) . '\');" onMouseOut="wuiUnHint();" ' : '') . 'name="' . $event_data->getDataString() . "\"" . ' tabindex="' . $this->mArgs['tabindex'] . '"' . ">\n";
            reset($this->mArgs['elements']);
            while (list ($key, $val) = each($this->mArgs['elements'])) {
                $this->mLayout .= '<option value="' . $key . '"' . ((isset($this->mArgs['default']) and $this->mArgs['default'] == $key) ? ' selected' : '') . '>' . \Innomatic\Wui\Wui::utf8_entities($val) . "</option>\n";
            }
            $this->mLayout .= ($this->mComments ? "</select>\n<!-- end " . $this->mName . " combobox -->\n" : '');
            $result = true;
        }
        return true;
    }
}
