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
require_once 'scripts_container.php';
$script = \Innomatic\Scripts\ScriptContainer::instance('\Innomatic\Scripts\ScriptContainer');

if (!isset($argv[1])) {
    $argv[1] = '';
}

try {
    switch ($argv[1]) {
        case '-h':
            print('Usage: php innomatic/core/scripts/application.php command argument' . "\n");
            print("\n");
            print('Supported commands:' . "\n");
            print('    deploy appfile                  Deploys an application' . "\n");
            print('    undeploy appname                Undeploys an application' . "\n");
            $script->cleanExit();
            break;
        
        case 'deploy':
            $app = new \Innomatic\Application\Application(InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess());
            if (file_exists($argv[2]) and $app->install($argv[2])) {
                print("Application $app->appname deployed\n");
                $script->cleanExit();
            } else {
                print("Application not deployed\n");
                $script->cleanExit(1);
            }
            break;
        
        case 'undeploy':
            $appid = \Innomatic\Application\Application::getAppIdFromName($argv[2]);
            $app = new \Innomatic\Application\Application(InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(), $appid);
            if ($app->uninstall()) {
                print("Application $argv[2] undeployed\n");
                $script->cleanExit();
            } else {
                print("Application $argv[2] not undeployed\n");
                $script->cleanExit(1);
            }
            break;
        
        default:
            print('Usage: php innomatic/core/scripts/application.php command' . "\n");
            print('Type application.php -h for a list of supported commands' . "\n");
    }
} catch (\Exception $e) {
    echo $e;
}

$script->cleanExit();
