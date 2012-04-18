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

// PHP 5 check, in order to not show fatal errors.
if (substr(phpversion(), 0, 1) < 5) {
    die('ERROR: this application requires PHP 5 or newer.');
}

// Saves webapp home.
$webAppHome = getcwd().'/';

// Checks if this receiver script has been called directly.
if ($webAppHome == dirname(__FILE__).'/') {
    // Redirects to innomatic webapp.
    header('Location: innomatic/');
    exit;
}

// Starts the Root Container.
require_once(dirname(__FILE__).'/innomatic/WEB-INF/classes/innomatic/core/RootContainer.php');
$rootContainer = RootContainer::instance('rootcontainer');

// Starts the WebAppContainer.
require_once('innomatic/webapp/WebAppContainer.php');
$container = WebAppContainer::instance('webappcontainer');

// Starts the WebApp. This is where all the real stuff is done.
$container->startWebApp($webAppHome);

// Stops the Root Container so that the instance is marked as cleanly exited.
$rootContainer->stop();
