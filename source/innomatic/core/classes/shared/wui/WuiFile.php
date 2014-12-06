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
class WuiFile extends \Innomatic\Wui\Widgets\WuiWidget
{
    //public $mHint;
    //public $mDisp;
    //public $mSize;
    /*! @public mTabIndex integer - Position of the current element in the tabbing order. */
    //public $mTabIndex = 0;
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
        $event_data = new \Innomatic\Wui\Dispatch\WuiEventRawData($this->mArgs['disp'], $this->mName, 'file');
        $this->mLayout = ($this->mComments ? '<!-- begin ' . $this->mName . ' file -->' : '') . '<input'.(isset($this->mArgs['id']) ? ' id="'.$this->mArgs['id'].'"' : '').' class="normal" ' . $this->getEventsCompleteString() . ' ' . ((isset($this->mArgs['hint']) and strlen($this->mArgs['hint'])) ? 'onMouseOver="wuiHint(\'' . str_replace("'", "\'", $this->mArgs['hint']) . '\');" onMouseOut="wuiUnHint(); ' : '') . 'type="file" tabindex="' . $this->mArgs['tabindex'] . '"' . ((isset($this->mArgs['size']) and strlen($this->mArgs['size'])) ? ' size="' . $this->mArgs['size'] . '"' : '') . ' name="' . $event_data->getDataString() . '">' . ($this->mComments ? '<!-- end ' . $this->mName . " file -->\n" : '');
        return true;
    }
}
