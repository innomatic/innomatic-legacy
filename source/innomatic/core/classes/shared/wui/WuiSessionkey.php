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
class WuiSessionkey extends \Innomatic\Wui\Widgets\WuiWidget
{
    public $mValue;
    public function __construct (
        $elemName,
        $elemArgs = '',
        $elemTheme = '',
        $dispEvents = ''
    )
    {
        parent::__construct($elemName, $elemArgs, $elemTheme, $dispEvents);
        $tempSession = $this->retrieveSession();
        if (! isset($this->mArgs['value'])) {
            $this->mArgs['value'] = $tempSession['value'];
        }
        $this->storeSession(array('value' => $this->mArgs['value']));
        $this->mValue = &$this->mArgs['value'];
    }
    public function getValue()
    {
        return $this->mValue;
    }
}
