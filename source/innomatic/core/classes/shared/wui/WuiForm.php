<?php
/**
 * Innomatic
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2014 Innoteam Srl
 * @license    http://www.innomatic.io/license/ New BSD License
 * @link       http://www.innomatic.io
 * @since      Class available since Release 5.0
 */
namespace Shared\Wui;

/**
 * @package WUI
 */
class WuiForm extends \Innomatic\Wui\Widgets\WuiContainerWidget
{
    /*
     * action string - Submit URL
     * method string - POST or GET
     * disableenter boolean - Set to disable submit at enter
     */

    public function __construct (
        $elemName,
        $elemArgs = '',
        $elemTheme = '',
        $dispEvents = ''
    )
    {
        parent::__construct($elemName, $elemArgs, $elemTheme, $dispEvents);
        if (isset($this->mArgs['method']) and (strtolower($this->mArgs['method']) == 'get' or strtolower($this->mArgs['method']) == 'post'))
            $this->mArgs['method'] = $this->mArgs['method'];
        else
            $this->mArgs['method'] = 'POST';

        if (isset($this->mArgs['disableenter']) and $this->mArgs['disableenter'] == 'true') {
            $this->mArgs['disableenter'] = true;
        } else {
            $this->mArgs['disableenter'] = false;
        }
    }
    protected function generateSourceBegin()
    {
        return ($this->mComments ? '<!-- begin ' . $this->mName . " form -->\n" : '') . '<form'.(isset($this->mArgs['id']) ? ' id="'.$this->mArgs['id'].'"' : '').' name="' . $this->mName . '" '.($this->mArgs['disableenter'] ? 'onSubmit="return false" ' : '').'action="' . $this->mArgs['action'] . '" enctype="multipart/form-data" method="' . $this->mArgs['method'] . "\">\n" . '<table border="0" cellspacing="0" cellpadding="0">' . "\n";
    }
    protected function generateSourceEnd()
    {
        return "</table>\n</form>\n" . ($this->mComments ? '<!-- end ' . $this->mName . " form -->\n" : '');
    }
    protected function generateSourceBlockBegin()
    {
        return "<tr><td>\n";
    }
    protected function generateSourceBlockEnd()
    {
        return "</td></tr>\n";
    }
}
