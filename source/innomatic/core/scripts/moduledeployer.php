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

require_once('innomatic/module/deploy/ModuleDeployer.php');

        if (!isset ($argv[1]))
            $argv[1] = '';
        try {
            switch ($argv[1]) {
                case '-h' :
                    print('Usage: php innomatic/core/scripts/moduledeployer.php command argument'."\n");
                    print("\n");
                    print('Supported commands:'."\n");
                    print('    deploy modulefile      Deploys a module'."\n");
                    print('    redeploy modulefile    Redeploys a mdoule'."\n");
                    print('    undeploy modulename    Undeploys a module'."\n");
                    $script->cleanExit();
                    break;
                case 'deploy' :
                    $deployer = new ModuleDeployer();
                    if ($deployer->deploy($argv[2])) {
                        print('Module deployed'."\n");
                    } else {
                        print('Unable to deploy module'."\n");
                        $script->cleanExit(1);
                    }
                    break;
                case 'redeploy' :
                    $deployer = new ModuleDeployer();
                    if ($deployer->redeploy($argv[2])) {
                        print('Module redeployed'."\n");
                    } else {
                        print('Unable to redeploy module'."\n");
                        $script->cleanExit(1);
                    }
                    break;
                case 'undeploy' :
                    $deployer = new ModuleDeployer();
                    if ($deployer->undeploy($argv[2])) {
                        print('Module undeployed'."\n");
                    } else {
                        print('Unable to undeploy module'."\n");
                        $script->cleanExit(1);
                    }
                    break;
                default :
                    print('Usage: php innomatic/core/scripts/moduledeployer.php command'."\n");
                    print('Type moduledeployer.php -h for a list of supported commands'."\n");
            }
        } catch (Exception $e) {
            echo $e;
        }

$script->cleanExit();
