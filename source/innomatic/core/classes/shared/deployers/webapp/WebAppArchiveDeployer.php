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
class WebAppArchiveDeployer extends WebAppDeployer
{

    public function deploy(\Innomatic\WebApp\WebAppLocator $locator)
    {
        $context = \Innomatic\WebApp\WebAppContainer::instance('\Innomatic\WebApp\WebAppContainer');
        $from = $locator->getLocation();
        
        if (! file_exists($from)) {
            throw new \Innomatic\WebApp\Deploy\WebAppDeployerException(\Innomatic\WebApp\Deploy\WebAppDeployerException::ERROR_UNABLE_TO_FIND_WEBAPP);
        }
        
        $arc = new \Innomatic\Io\Archive\Archive($from, \Innomatic\Io\Archive\Archive::FORMAT_TGZ);
        $tmp_dir = $context->getHome() . 'temp/inst_' . rand() . '/';
        mkdir($tmp_dir);
        
        if (! $arc->extract($tmp_dir)) {
            \Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree($tmp_dir);
            throw new \Innomatic\WebApp\Deploy\WebAppDeployerException(\Innomatic\WebApp\Deploy\WebAppDeployerException::ERROR_NOT_A_WEBAPP);
        }
        
        $name = '';
        $handle = opendir($tmp_dir);
        while (($file = readdir($handle)) !== false) {
            if (($file != ".") && ($file != "..")) {
                if (is_dir($tmp_dir . $file)) {
                    $name = $file;
                }
            }
        }
        closedir($handle);
        
        if (! strlen($name)) {
            \Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree($tmp_dir);
            throw new \Innomatic\WebApp\Deploy\WebAppDeployerException(\Innomatic\WebApp\Deploy\WebAppDeployerException::ERROR_NOT_A_WEBAPP);
        }
        
        $this->name = $name;
        
        if (! file_exists($tmp_dir . $name . '/WEB-INF/web.xml')) {
            \Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree($tmp_dir);
            throw new \Innomatic\WebApp\Deploy\WebAppDeployerException(\Innomatic\WebApp\Deploy\WebAppDeployerException::ERROR_NOT_A_WEBAPP);
        }
        
        if (is_dir($context->getWebAppsHome() . $name)) {
            \Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree($tmp_dir);
            throw new \Innomatic\WebApp\Deploy\WebAppDeployerException(\Innomatic\WebApp\Deploy\WebAppDeployerException::ERROR_WEBAPP_ALREADY_EXISTS);
        }
        
        if (! WebAppDirUtils::dirCopy($tmp_dir . $name . '/', $context->getWebAppsHome() . $name . '/')) {
            \Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree($tmp_dir);
            throw new \Innomatic\WebApp\Deploy\WebAppDeployerException(\Innomatic\WebApp\Deploy\WebAppDeployerException::ERROR_UNABLE_TO_DEPLOY_WEBAPP);
        }
        
        \Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree($tmp_dir);
        $this->saveLocator($name, $locator);
    }

    public function redeploy(\Innomatic\WebApp\WebAppLocator $locator)
    {
        $context = \Innomatic\WebApp\WebAppContainer::instance('\Innomatic\WebApp\WebAppContainer');
        $from = $locator->getLocation();
        
        if (! file_exists($from)) {
            throw new \Innomatic\WebApp\Deploy\WebAppDeployerException(\Innomatic\WebApp\Deploy\WebAppDeployerException::ERROR_UNABLE_TO_FIND_WEBAPP);
        }
        
        $arc = new \Innomatic\Io\Archive\Archive($from, \Innomatic\Io\Archive\Archive::FORMAT_TGZ);
        $tmp_dir = $context->getHome() . 'temp/inst_' . rand() . '/';
        mkdir($tmp_dir);
        
        if (! $arc->extract($tmp_dir)) {
            \Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree($tmp_dir);
            throw new \Innomatic\WebApp\Deploy\WebAppDeployerException(\Innomatic\WebApp\Deploy\WebAppDeployerException::ERROR_NOT_A_WEBAPP);
        }
        
        $name = '';
        $handle = opendir($tmp_dir);
        while (($file = readdir($handle)) !== false) {
            if (($file != ".") && ($file != "..")) {
                if (is_dir($tmp_dir . $file)) {
                    $name = $file;
                }
            }
        }
        closedir($handle);
        
        if (! strlen($name)) {
            \Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree($tmp_dir);
            throw new \Innomatic\WebApp\Deploy\WebAppDeployerException(\Innomatic\WebApp\Deploy\WebAppDeployerException::ERROR_NOT_A_WEBAPP);
        }
        
        $this->name = $name;
        
        if (! is_dir($context->getWebAppsHome() . $name)) {
            \Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree($tmp_dir);
            throw new \Innomatic\WebApp\Deploy\WebAppDeployerException(\Innomatic\WebApp\Deploy\WebAppDeployerException::ERROR_WEBAPP_DOES_NOT_EXISTS);
        }
        
        if (! file_exists($tmp_dir . $name . '/WEB-INF/web.xml')) {
            \Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree($tmp_dir);
            throw new \Innomatic\WebApp\Deploy\WebAppDeployerException(\Innomatic\WebApp\Deploy\WebAppDeployerException::ERROR_NOT_A_WEBAPP);
        }
        
        $this->undeploy($name);
        
        if (! WebAppDirUtils::dirCopy($tmp_dir . $name . '/', $context->getWebAppsHome() . $name . '/')) {
            \Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree($tmp_dir);
            throw new \Innomatic\WebApp\Deploy\WebAppDeployerException(\Innomatic\WebApp\Deploy\WebAppDeployerException::ERROR_UNABLE_TO_DEPLOY_WEBAPP);
        }
        
        \Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree($tmp_dir);
        $this->saveLocator($name, $locator);
    }
}

?>