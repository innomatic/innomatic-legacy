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
 * @license    http://www.innomatic.org/license/   BSD License
 * @link       http://www.innomatic.org
 */
require_once ('scripts_container.php');
$script = \Innomatic\Scripts\ScriptContainer::instance('\Innomatic\Scripts\ScriptContainer');

if (! isset($argv[1]))
    $argv[1] = '';
try {
    switch ($argv[1]) {
        case '-h':
            print('Usage: php innomatic/core/scripts/moduleserver.php command' . "\n");
            print('' . "\n");
            print('Supported commands:' . "\n");
            print('    start       Starts the server' . "\n");
            print('    stop        Shutdowns the server' . "\n");
            print('    restart     Restarts the server' . "\n");
            print('    refresh     Reload server configuration' . "\n");
            print('    status      Shows server status' . "\n");
            print('    wstart      Starts the server without the watch dog' . "\n");
            $script->cleanExit();
            break;
        case 'wstart':
            $controller = new \Innomatic\Module\Server\ModuleServerController();
            $controller->start();
            break;
        case 'stop':
            $controller = new \Innomatic\Module\Server\ModuleServerController();
            $controller->shutdown();
            break;
        case 'wrestart':
            $controller = new \Innomatic\Module\Server\ModuleServerController();
            $controller->restart();
            break;
        case 'refresh':
            $controller = new \Innomatic\Module\Server\ModuleServerController();
            print($controller->refresh());
            break;
        case 'status':
            $controller = new \Innomatic\Module\Server\ModuleServerController();
            print($controller->status());
            break;
        case 'start':
            $controller = new \Innomatic\Module\Server\ModuleServerController();
            $controller->watchDogStart();
            break;
        case 'restart':
            $controller = new \Innomatic\Module\Server\ModuleServerController();
            $controller->watchDogRestart();
            break;
        default:
            print('Usage: php innomatic/core/scripts/moduleserver.php command' . "\n");
            print('Type moduleserver.php -h for a list of supported commands' . "\n");
    }
} catch (\Exception $e) {
    echo $e;
}

$script->cleanExit();
