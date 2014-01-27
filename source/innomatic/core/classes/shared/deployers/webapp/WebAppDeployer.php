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
 * @since 6.3.0
 * @author Alex Pagnoni <alex.pagnoni@innoteam.it>
 */
abstract class WebAppDeployer
{

    protected $name;

    public abstract function deploy(\Innomatic\WebApp\WebAppLocator $locator);

    public abstract function redeploy(\Innomatic\WebApp\WebAppLocator $locator);

    public function undeploy($name)
    {
        $context = \Innomatic\WebApp\WebAppContainer::instance('\Innomatic\WebApp\WebAppContainer');
        if (! strlen($name) or ! is_dir($context->getWebAppsHome() . $name)) {
            throw new \Innomatic\WebApp\Deploy\WebAppDeployerException(\Innomatic\WebApp\Deploy\WebAppDeployerException::ERROR_WEBAPP_DOES_NOT_EXISTS);
        }
        $this->name = $name;
        // Should check with realpath()
        if (! \Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree($context->getWebAppsHome() . $name)) {
            throw new \Innomatic\WebApp\Deploy\WebAppDeployerException(\Innomatic\WebApp\Deploy\WebAppDeployerException::ERROR_UNABLE_TO_UNDEPLOY_WEBAPP);
        }
    }

    public function getName()
    {
        return $this->name;
    }

    public function saveLocator($name, \Innomatic\WebApp\WebAppLocator $locator)
    {
        $context = \Innomatic\WebApp\WebAppContainer::instance('\Innomatic\WebApp\WebAppContainer');
        if (! strlen($name) or ! is_dir($context->getWebAppsHome() . $name)) {
            throw new \Innomatic\WebApp\Deploy\WebAppDeployerException(\Innomatic\WebApp\Deploy\WebAppDeployerException::ERROR_WEBAPP_DOES_NOT_EXISTS);
        }
        
        if (! is_dir($context->getWebAppsHome() . $name . '/core/temp/cache/')) {
            mkdir($context->getWebAppsHome() . $name . '/core/temp/cache/');
        }
        file_put_contents($context->getWebAppsHome() . $name . '/core/temp/cache/WebAppLocator.ser', $locator->serialize());
    }

    public function retrieveLocator($name)
    {
        $context = \Innomatic\WebApp\WebAppContainer::instance('\Innomatic\WebApp\WebAppContainer');
        if (! strlen($name) or ! is_dir($context->getWebAppsHome() . $name)) {
            throw new \Innomatic\WebApp\Deploy\WebAppDeployerException(\Innomatic\WebApp\Deploy\WebAppDeployerException::ERROR_WEBAPP_DOES_NOT_EXISTS);
        }
        
        if (file_exists($context->getWebAppsHome() . $name . '/core/temp/cache/WebAppLocator.ser')) {
            return unserialize(file_get_contents($context->getWebAppsHome() . $name . '/core/temp/cache/WebAppLocator.ser'));
        } else {
            return false;
        }
    }
}

?>