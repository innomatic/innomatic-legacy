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
namespace Innomatic\Scripts;

use \Innomatic\Core;

class ScriptContainer extends \Innomatic\Util\Singleton
{
    public function ___construct()
    {
        $innomatic = InnomaticContainer::instance('innomaticcontainer');
        $innomatic->setInterface(InnomaticContainer::INTERFACE_CONSOLE);
        $home = RootContainer::instance('rootcontainer')->getHome()
            . 'innomatic/';
        $innomatic->bootstrap($home, $home . 'core/conf/innomatic.ini');
    }

    public static function cleanExit($status = 0)
    {
        RootContainer::instance('rootcontainer')->stop();
        exit($status);
    }
}
