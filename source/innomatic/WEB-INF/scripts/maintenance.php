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

require_once('scripts_container.php');

$script = ScriptContainer::instance('scriptcontainer');

require_once('innomatic/core/InnomaticContainer.php');
$innomatic = InnomaticContainer::instance('innomaticcontainer');
$innomatic->startMaintenance();

$script->cleanExit();
