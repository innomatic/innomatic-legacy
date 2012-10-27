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
require_once ('innomatic/wui/widgets/WuiContainerWidget.php');
/**
 * @package WUI
 */
class WuiForm extends WuiContainerWidget
{
    //public $mAction;
    //public $mMethod;
    public function __construct (
        $elemName,
        $elemArgs = '',
        $elemTheme = '',
        $dispEvents = ''
    )
    {
        $this->WuiContainerWidget($elemName, $elemArgs, $elemTheme, $dispEvents);
        if (isset($this->mArgs['method']) and (strtolower($this->mArgs['method']) == 'get' or strtolower($this->mArgs['method']) == 'post'))
            $this->mArgs['method'] = $this->mArgs['method'];
        else
            $this->mArgs['method'] = 'POST';
    }
    protected function generateSourceBegin ()
    {
        return ($this->mComments ? '<!-- begin ' . $this->mName . " form -->\n" : '') . '<form name="' . $this->mName . '" action="' . $this->mArgs['action'] . '" enctype="multipart/form-data" method="' . $this->mArgs['method'] . "\">\n" . '<table border="0" cellspacing="0" cellpadding="0">' . "\n";
    }
    protected function generateSourceEnd ()
    {
        return "</table>\n</form>\n" . ($this->mComments ? '<!-- end ' . $this->mName . " form -->\n" : '');
    }
    protected function generateSourceBlockBegin ()
    {
        return "<tr><td>\n";
    }
    protected function generateSourceBlockEnd ()
    {
        return "</td></tr>\n";
    }
}