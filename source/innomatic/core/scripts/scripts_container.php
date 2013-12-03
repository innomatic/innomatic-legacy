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

// PHP 5 check, in order to not show fatal errors
if (substr(phpversion(), 0, 1) < 5) {
    die('ERROR: this application needs PHP 5 or newer.');
}

// Starts the Root Container
require_once(
    dirname(__FILE__) . '/../classes/innomatic/core/RootContainer.php'
);
$rootContainer = RootContainer::instance('rootcontainer');

// Starts the Script Container
require_once(
    dirname(__FILE__)
    . '/../classes/innomatic/scripts/ScriptContainer.php'
);
