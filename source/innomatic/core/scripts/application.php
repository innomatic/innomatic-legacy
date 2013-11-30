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

require_once('innomatic/application/Application.php');

if (!isset ($argv[1])) $argv[1] = '';

try {
    switch ($argv[1]) {
        case '-h' :
            print('Usage: php innomatic/core/scripts/application.php command argument'."\n");
            print("\n");
            print('Supported commands:'."\n");
            print('    deploy appfile                  Deploys an application'."\n");
            print('    undeploy appname                Undeploys an application'."\n");
            $script->cleanExit();
            break;

        case 'deploy' :
            $app = new Application(InnomaticContainer::instance('innomaticcontainer')->getDataAccess());
            if (file_exists($argv[2]) and $app->install($argv[2])) {
                print("Application $app->appname deployed\n");
                $script->cleanExit();
            } else {
                print("Application not deployed\n");
                $script->cleanExit(1);
            }
            break;

        case 'undeploy' :
            $appid = Application::getAppIdFromName($argv[2]);
            $app = new Application(InnomaticContainer::instance('innomaticcontainer')->getDataAccess(), $appid);
            if ($app->uninstall()) {
                print("Application $argv[2] undeployed\n");
                $script->cleanExit();
            } else {
                print("Application $argv[2] not undeployed\n");
                $script->cleanExit(1);
            }
            break;

        default :
            print('Usage: php innomatic/core/scripts/application.php command'."\n");
            print('Type application.php -h for a list of supported commands'."\n");
    }
} catch (\Exception $e) {
    echo $e;
}

$script->cleanExit();
