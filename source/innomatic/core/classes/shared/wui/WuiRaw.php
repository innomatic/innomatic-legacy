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
class WuiRaw extends \Innomatic\Wui\Widgets\WuiWidget
{
    public function __construct (
        $elemName,
        $elemArgs = '',
        $elemTheme = '',
        $dispEvents = ''
    )
    {
        parent::__construct($elemName, $elemArgs, $elemTheme, $dispEvents);
    }
    protected function generateSource()
    {
        $this->mLayout = ($this->mComments ? '<!-- begin ' . $this->mName
        . " raw -->\n" : '') . $this->mArgs['content'] . "\n"
        . ($this->mComments ? '<!-- end ' . $this->mName . " raw -->\n" : '');
        return true;
    }
}
