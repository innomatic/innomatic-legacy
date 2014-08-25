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
 * @license    http://www.innomaticplatform.com/license/ New BSD License
 * @link       http://www.innomaticplatform.com
 */

// Saves webapp home.
$webAppHome = getcwd().'/';

// Checks if this receiver script has been called directly.
if ($webAppHome == dirname(__FILE__).'/') {
    // Redirects to innomatic webapp.
    header('Location: innomatic/');
    exit;
}

// Starts the Root Container.
require_once(dirname(__FILE__).'/innomatic/core/classes/innomatic/core/RootContainer.php');
$rootContainer = \Innomatic\Core\RootContainer::instance('\Innomatic\Core\RootContainer');

// Starts the WebAppContainer.
$container = \Innomatic\Webapp\WebAppContainer::instance('\Innomatic\Webapp\WebAppContainer');

// Starts the WebApp. This is where all the real stuff is done.
$container->startWebApp($webAppHome);

// Stops the Root Container so that the instance is marked as cleanly exited.
$rootContainer->stop();
