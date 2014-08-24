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
 * @license    http://www.innomaticplatform.com/license/   BSD License
 * @link       http://www.innomaticplatform.com
 * @since      Class available since Release 5.1
 */
namespace Shared\Wui;

/**
 * @package WUI
 */
class WuiVoid extends \Innomatic\Wui\Widgets\WuiWidget
{
    public function __construct(
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
        $layout = '';

        if ($this->mComments) $layout .= '<!-- begin ' . $this->mName
            . " void -->\n";

        $layout .= '<hr style="display:none; visibility:hidden;"/>';

        if ($this->mComments) $layout .= '<!-- end ' . $this->mName
        . " void -->\n";
    }
}
