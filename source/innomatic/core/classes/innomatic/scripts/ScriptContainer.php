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
 * @since      Class available since Release 5.0
*/
namespace Innomatic\Scripts;

use \Innomatic\Core;

/**
 * Container for Innomatic scripts.
 *
 * Innomatic scripts are PHP programs launched from the console inside the
 * Innomatic container. Such scripts must instance this class before executing
 * their code and must end with a call to the cleanExit() method.
 *
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 * @copyright  1999-2014 Innoteam Srl
 * @since      5.0
 */
class ScriptContainer extends \Innomatic\Util\Singleton
{
    /* public ___construct() {{{ */
    /**
     * Class constructor.
     *
     * This method sets the Innomatic interface type to console and starts
     * Innomatic.
     *
     * @access public
     * @return void
     */
    public function ___construct()
    {
        $innomatic = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');

        // Set the Innomatic interface type to console
        $innomatic->setInterface(\Innomatic\Core\InnomaticContainer::INTERFACE_CONSOLE);
        $home = RootContainer::instance('\Innomatic\Core\RootContainer')->getHome()
            . 'innomatic/';

        // Start Innomatic
        $innomatic->bootstrap($home, $home . 'core/conf/innomatic.ini');
    }
    /* }}} */

    /* public cleanExit($status = 0) {{{ */
    /**
     * Executes a clean exit of the script.
     *
     * This method must be called by scripts when they end (eg. at the end of
     * the script or in cases where execution must be terminated before
     * script's end).
     *
     * Scripts must avoid terminating their execution with a direct call to PHP
     * exit() or die().
     *
     * A clean exit tells Innomatic to properly shutdown and mark the process
     * as cleanly terminated.
     *
     * @param int $status Optional status code to be returned to the shell.
     * @static
     * @access public
     * @return void
     */
    public static function cleanExit($status = 0)
    {
        RootContainer::instance('\Innomatic\Core\RootContainer')->stop();
        exit($status);
    }
    /* }}} */
}
