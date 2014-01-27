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
namespace Innomatic\Shared\Deployers\Webapp;

/**
 *
 * @since 6.3.0
 * @author Alex Pagnoni <alex.pagnoni@innoteam.it>
 */
class WebAppCvsDeployer extends WebAppDeployer
{

    public function deploy(\Innomatic\WebApp\WebAppLocator $locator)
    {
        $context = \Innomatic\WebApp\WebAppContainer::instance('\Innomatic\WebApp\WebAppContainer');
        $args = explode(':', $locator->getLocation());
        // connection:user:password:host:repository:module
        $connection = $args[0];
        $user = $args[1];
        $password = $args[2];
        $host = $args[3];
        $rep = $args[4];
        $module = $args[5];
        $name = $module;
        
        if (substr($name, - 1) == '/')
            $name = substr($name, 0, - 1);
        if (strpos($name, '/'))
            $name = substr($name, strrpos($name, '/') + 1);
        $this->name = $name;
        
        if (is_dir($context->getWebAppsHome() . $name)) {
            throw new \Innomatic\WebApp\Deploy\WebAppDeployerException(\Innomatic\WebApp\Deploy\WebAppDeployerException::ERROR_WEBAPP_ALREADY_EXISTS);
        }
        
        $login_command = 'cvs -d :' . $connection . ':' . $user . '@' . $host . ':' . $rep . ' login';
        $co_command = 'cvs -d :' . $connection . ':' . $user . '@' . $host . ':' . $rep . ' co -d ' . $name . ' ' . $module;
        
        /*
         * // Create a pseudo terminal for the child process $descriptorspec = array (0 => array ('pipe', 'r'), 1 => array ('pipe', 'w'), 2 => array ('pipe', 'w')); $process = proc_open($login_command, $descriptorspec, $pipes); if (is_resource($process)) { echo fgets($pipes[1], 1024); //echo fgets($pipes[1], 1024); echo fgets($pipes[2], 1024); echo fgets($pipes[2], 1024); fwrite($pipes[0], $password); fflush($pipes[0]); echo fgets($pipes[1], 1024); echo fgets($pipes[1], 1024); echo "fine"; fclose($pipes[0]); fclose($pipes[1]); fclose($pipes[2]); $return_value = proc_close($process); echo "command returned $return_value\n"; }
         */
        
        $cd = getcwd();
        chdir($context->getWebAppsHome());
        if ($fh = fopen($context->getHome() . 'logs/cvs.log', 'a')) {
            fwrite($fh, "------------------------------------------------------------------------------\n");
            fwrite($fh, $co_command . "\n");
            fclose($fh);
        }
        exec($co_command . ' 2>>' . $context->getHome() . 'logs/cvs.log', $output, $success);
        chdir($cd);
        
        if ($success) {
            throw new \Innomatic\WebApp\Deploy\WebAppDeployerException(\Innomatic\WebApp\Deploy\WebAppDeployerException::ERROR_UNABLE_TO_DEPLOY_WEBAPP);
        }
        
        if (! file_exists($context->getWebAppsHome() . $name . '/WEB-INF/web.xml')) {
            \Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree($context->getWebAppsHome() . $name . '/WEB-INF/web.xml');
            throw new \Innomatic\WebApp\Deploy\WebAppDeployerException(\Innomatic\WebApp\Deploy\WebAppDeployerException::ERROR_NOT_A_WEBAPP);
        }
        
        $this->saveLocator($name, $locator);
    }

    public function redeploy(\Innomatic\WebApp\WebAppLocator $locator)
    {
        $context = \Innomatic\WebApp\WebAppContainer::instance('\Innomatic\WebApp\WebAppContainer');
        $args = explode(':', $locator->getLocation());
        // connection:user:password:host:repository:module
        $connection = $args[0];
        $user = $args[1];
        $password = $args[2];
        $host = $args[3];
        $rep = $args[4];
        $module = $args[5];
        $name = $module;
        
        if (substr($name, - 1) == '/')
            $name = substr($name, 0, - 1);
        if (strpos($name, '/'))
            $name = substr($name, strrpos($name, '/') + 1);
        $this->name = $name;
        
        if (! is_dir($context->getWebAppsHome() . $name)) {
            throw new \Innomatic\WebApp\Deploy\WebAppDeployerException(\Innomatic\WebApp\Deploy\WebAppDeployerException::ERROR_UNABLE_TO_FIND_WEBAPP);
        }
        
        $login_command = 'cvs -d :' . $connection . ':' . $user . '@' . $host . ':' . $rep . ' login';
        $update_command = 'cvs -d :' . $connection . ':' . $user . '@' . $host . ':' . $rep . ' update';
        
        /*
         * // Create a pseudo terminal for the child process $descriptorspec = array (0 => array ('pipe', 'r'), 1 => array ('pipe', 'w'), 2 => array ('pipe', 'w')); $process = proc_open($login_command, $descriptorspec, $pipes); if (is_resource($process)) { echo fgets($pipes[1], 1024); //echo fgets($pipes[1], 1024); echo fgets($pipes[2], 1024); echo fgets($pipes[2], 1024); fwrite($pipes[0], $password); fflush($pipes[0]); echo fgets($pipes[1], 1024); echo fgets($pipes[1], 1024); echo "fine"; fclose($pipes[0]); fclose($pipes[1]); fclose($pipes[2]); $return_value = proc_close($process); echo "command returned $return_value\n"; }
         */
        
        $cd = getcwd();
        chdir($context->getWebAppsHome() . $name);
        if ($fh = fopen($context->getHome() . 'logs/cvs.log', 'a')) {
            fwrite($fh, "------------------------------------------------------------------------------\n");
            fwrite($fh, $update_command . "\n");
            fclose($fh);
        }
        exec($update_command . ' 2>>' . $context->getHome() . 'logs/cvs.log', $output, $success);
        chdir($cd);
        
        if ($success) {
            throw new \Innomatic\WebApp\Deploy\WebAppDeployerException(\Innomatic\WebApp\Deploy\WebAppDeployerException::ERROR_UNABLE_TO_DEPLOY_WEBAPP);
        }
        
        if (! file_exists($context->getWebAppsHome() . $name . '/WEB-INF/web.xml')) {
            \Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree($context->getWebAppsHome() . $name . '/WEB-INF/web.xml');
            throw new \Innomatic\WebApp\Deploy\WebAppDeployerException(\Innomatic\WebApp\Deploy\WebAppDeployerException::ERROR_NOT_A_WEBAPP);
        }
        
        $wacontext = new WebAppContext($context->getWebAppsHome() . $name . '/');
        $wacontext->refresh();
        
        $this->saveLocator($name, $locator);
    }
}

?>