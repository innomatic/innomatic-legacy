<?php        
/**
 * Innomatic
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2013 Innoteam Srl
 * @license    http://www.innomatic.org/license/   BSD License
 * @link       http://www.innomatic.org
 * @since      Class available since Release 5.1
 */

require_once('scripts_container.php');
$script = ScriptContainer::instance('scriptcontainer');

require_once('innomatic/module/server/ModuleServerController.php');

        if (!isset ($argv[1]))
            $argv[1] = '';
        try {
            switch ($argv[1]) {
                case '-h' :
                    print('Usage: php innomatic/core/scripts/moduleserver.php command'."\n");
                    print(''."\n");
                    print('Supported commands:'."\n");
                    print('    start       Starts the server'."\n");
                    print('    stop        Shutdowns the server'."\n");
                    print('    restart     Restarts the server'."\n");
                    print('    refresh     Reload server configuration'."\n");
                    print('    status      Shows server status'."\n");
                    print('    wstart      Starts the server without the watch dog'."\n");
                    $script->cleanExit();
                    break;
                case 'wstart' :
                    $controller = new ModuleServerController();
                    $controller->start();
                    break;
                case 'stop' :
                    $controller = new ModuleServerController();
                    $controller->shutdown();
                    break;
                case 'wrestart' :
                    $controller = new ModuleServerController();
                    $controller->restart();
                    break;
                case 'refresh' :
                    $controller = new ModuleServerController();
                    print($controller->refresh());
                    break;
                case 'status' :
                    $controller = new ModuleServerController();
                    print($controller->status());
                    break;
                case 'start' :
                    $controller = new ModuleServerController();
                    $controller->watchDogStart();
                    break;
                case 'restart' :
                    $controller = new ModuleServerController();
                    $controller->watchDogRestart();
                    break;
                default :
                    print('Usage: php innomatic/core/scripts/moduleserver.php command'."\n");
                    print('Type moduleserver.php -h for a list of supported commands'."\n");
            }
        } catch (Exception $e) {
            echo $e;
        }

$script->cleanExit();
