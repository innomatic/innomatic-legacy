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
class WuiFormarg extends \Innomatic\Wui\Widgets\WuiWidget
{
    /*! @public mValue string - Default content. */
    //public $mValue;
    /*! @public mDisp string - Dispatcher for this element. */
    //public $mDisp;
    public function __construct (
        $elemName,
        $elemArgs = '',
        $elemTheme = '',
        $dispEvents = ''
    )
    {
        parent::__construct($elemName, $elemArgs, $elemTheme, $dispEvents);
        if (isset($this->mArgs['value'])) {
            $this->mArgs['value'] = $this->mArgs['value'];
        }
        if (isset($this->mArgs['disp'])) {
            $this->mArgs['disp'] = $this->mArgs['disp'];
        }
    }
    protected function generateSource()
    {
        require_once ('innomatic/wui/dispatch/WuiEventRawData.php');
        $eventData = new WuiEventRawData($this->mArgs['disp'], $this->mName);
        $this->mLayout = ($this->mComments ? '<!-- begin ' . $this->mName
            . ' string -->' : '') . '<input'.(isset($this->mArgs['id']) ? ' id="'.$this->mArgs['id'].'"' : '').' type="hidden" name="'
            . $eventData->getDataString() . '"'
            . (strlen($this->mArgs['value']) ? ' value="'
            . Wui::utf8_entities($this->mArgs['value']) . '"' : '') . '>'
            . ($this->mComments ? '<!-- end ' . $this->mName . " string -->\n"
            : '');
        return true;
    }
}
