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
class WebAppFilesystemDeployer extends WebAppDeployer
{

    public function deploy(\Innomatic\WebApp\WebAppLocator $locator)
    {
        $from = $locator->getLocation();
        if (strrpos($from, '/') == strlen($from) - 1 or strrpos($from, "\\") == strlen($from) - 1)
            $from = substr($from, 0, - 1);
        $name = basename($from);
        $context = \Innomatic\WebApp\WebAppContainer::instance('\Innomatic\WebApp\WebAppContainer');
        $this->name = $name;
        
        if (is_dir($context->getWebAppsHome() . $name)) {
            throw new \Innomatic\WebApp\Deploy\WebAppDeployerException(\Innomatic\WebApp\Deploy\WebAppDeployerException::ERROR_WEBAPP_ALREADY_EXISTS);
        }
        
        if (! is_dir($from)) {
            throw new \Innomatic\WebApp\Deploy\WebAppDeployerException(\Innomatic\WebApp\Deploy\WebAppDeployerException::ERROR_UNABLE_TO_FIND_WEBAPP);
        }
        
        if (! file_exists($from . '/WEB-INF/web.xml')) {
            throw new \Innomatic\WebApp\Deploy\WebAppDeployerException(\Innomatic\WebApp\Deploy\WebAppDeployerException::ERROR_NOT_A_WEBAPP);
        }
        
        if (! WebAppDirUtils::dirCopy($from . '/', $context->getWebAppsHome() . $name . '/')) {
            throw new \Innomatic\WebApp\Deploy\WebAppDeployerException(\Innomatic\WebApp\Deploy\WebAppDeployerException::ERROR_UNABLE_TO_DEPLOY_WEBAPP);
        }
        
        $this->saveLocator($name, $locator);
    }

    public function redeploy(\Innomatic\WebApp\WebAppLocator $locator)
    {
        $from = $locator->getLocation();
        if (strrpos($from, '/') == strlen($from) - 1 or strrpos($from, "\\") == strlen($from) - 1)
            $from = substr($from, 0, - 1);
        $name = basename($from);
        $context = \Innomatic\WebApp\WebAppContainer::instance('\Innomatic\WebApp\WebAppContainer');
        $this->name = $name;
        
        if (! is_dir($from)) {
            throw new \Innomatic\WebApp\Deploy\WebAppDeployerException(\Innomatic\WebApp\Deploy\WebAppDeployerException::ERROR_UNABLE_TO_FIND_WEBAPP);
        }
        
        if (! file_exists($from . '/WEB-INF/web.xml')) {
            throw new \Innomatic\WebApp\Deploy\WebAppDeployerException(\Innomatic\WebApp\Deploy\WebAppDeployerException::ERROR_NOT_A_WEBAPP);
        }
        
        $this->undeploy($name);
        
        if (! WebAppDirUtils::dirCopy($from . '/', $context->getWebAppsHome() . $name . '/')) {
            throw new \Innomatic\WebApp\Deploy\WebAppDeployerException(\Innomatic\WebApp\Deploy\WebAppDeployerException::ERROR_UNABLE_TO_DEPLOY_WEBAPP);
        }
        
        $this->saveLocator($name, $locator);
    }
}

?>