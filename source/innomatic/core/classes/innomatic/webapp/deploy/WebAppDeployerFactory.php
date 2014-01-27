<?php
/**
 * Innomatic
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @copyright  2004-2014 Innoteam Srl
 * @license    http://www.innomatic.org/license/   BSD License
 * @link       http://www.innomatic.org
 * @since      Class available since Release 6.3.0
*/
namespace Innomatic\Webapp\Deploy;

/**
 *
 * @since 6.3.0
 * @author Alex Pagnoni <alex.pagnoni@innoteam.it>
 */
class WebAppDeployerFactory
{

    public static function getDeployer($method)
    {
        $ascontext = \Innomatic\WebApp\WebAppContainer::instance('\Innomatic\WebApp\WebAppContainer');
        $deployer = '';
        if ($ascontext->getConfig()->isKey('deployers.deployer.' . $method . '.class')) {
            $deployer = $ascontext->getConfig()->getKey('deployers.deployer.' . $method . '.class');
        } elseif ($ascontext->getConfig()->useDefaults()) {
            switch ($method) {
                case 'filesystem':
                    $deployer = 'WebAppFilesystemDeployer';
                    break;
                case 'archive':
                    $deployer = 'WebAppArchiveDeployer';
                    break;
                case 'cvs':
                    $deployer = 'WebAppCvsDeployer';
                    break;
                case 'web':
                    $deployer = 'WebAppWebDeployer';
                    break;
            }
            
            $deployer = '\\Innomatic\\Shared\\Deployers\\WebApp\\'.$deployer;
        }
        
        if (strlen($deployer)) {
            $classname = strpos($deployer, '.') ? substr($deployer, strrpos($deployer, '.') + 1) : $deployer;
            return new $classname();
        }
    }
}

?>