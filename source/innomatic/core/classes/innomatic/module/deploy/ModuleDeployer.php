<?php
namespace Innomatic\Module\Deploy;

/**
 * Module deployer class.
 *
 * This class deploys Module in the Module server modules directory and manages
 * redeployment and undeployment.
 *
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 * @copyright Copyright 2004-2014 Innoteam Srl
 * @since 5.1
 */
class ModuleDeployer
{
    /**
     * Deploys a Module in the Module server.
     *
     * @param string $module Full path of the Module archive.
     * @return boolean
     * @since 5.1
     */
    public function deploy($module)
    {
        $module = realpath($module);
        if (!file_exists($module)) {
            return false;
        }

        $context = \Innomatic\Module\Server\ModuleServerContext::instance('\Innomatic\Module\Server\ModuleServerContext');

        $tmp_dir = $context->getHome().'core/temp/appinst/deploy_'.rand().DIRECTORY_SEPARATOR;
        mkdir($tmp_dir, 0755, true);

        if (is_dir($module)) {
            // Copies the Module directory
            \Innomatic\Io\Filesystem\DirectoryUtils::dirCopy($module.'/', $tmp_dir);
        } else {
            // Unpacks the Module archive.
            $arc = new \Innomatic\Io\Archive\Archive($module, \Innomatic\Io\Archive\Archive::FORMAT_TAR);
            if (!$arc->extract($tmp_dir)) {
                \Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree($tmp_dir);
                throw new \Innomatic\Module\ModuleException('Unable to extract Module');
            }
        }

        // Checks if module.xml file exists.
        if (!file_exists($tmp_dir.'setup/module.xml')) {
            \Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree($tmp_dir);
            throw new \Innomatic\Module\ModuleException('Missing module.xml configuration file');
        }

        // Parses Module configuration.
        $cfg = \Innomatic\Module\Util\ModuleXmlConfig::getInstance($tmp_dir.'setup/module.xml');
        if (!strlen($cfg->getName())) {
            \Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree($tmp_dir);
            throw new \Innomatic\Module\ModuleException('Missing Module name in module.xml');
        }

        // Checks if a Module with that name already exists.
        if (is_dir($context->getHome().'core/modules'.DIRECTORY_SEPARATOR.$cfg->getName())) {
            \Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree($tmp_dir);
            throw new \Innomatic\Module\ModuleException('A Module with that name already exists');
        }

        // Checks Module code.
        $code_checker = new \Innomatic\Php\PHPCodeChecker();
        if (!$code_checker->checkDirectory($tmp_dir)) {
            \Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree($tmp_dir);
            throw new \Innomatic\Module\ModuleException('Module contains errors in code');
        }

        // Deploys the Module.
        $module_dir = $context->getHome().'core/modules'.DIRECTORY_SEPARATOR.$cfg->getName().DIRECTORY_SEPARATOR;
        mkdir($module_dir, 0755, true);
        \Innomatic\Io\Filesystem\DirectoryUtils::dirCopy($tmp_dir.'/', $module_dir);
        \Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree($tmp_dir);

        // Runs Module deploy hooked actions.
        //$auth = \Innomatic\Module\Server\ModuleServerAuthenticator::instance('ModuleServerAuthenticator');
        //$module = \Innomatic\Module\ModuleFactory::getModule(new ModuleLocator('module://admin:'.$auth->getPassword('admin').'@/'.$cfg->getName()));
        //$module->deploy();

        // Logs deployment.
        if ($context->getConfig()->getKey('log_deployer_events') == 1 or $context->getConfig()->useDefaults()) {
            $logger = new \Innomatic\Module\Server\ModuleServerLogger($context->getHome().'core/log/module-deployer.log');
            $logger->logEvent($cfg->getName().' deployed');
        }

        return true;
    }

    /**
     * Redeploys a Module in the Module server.
     *
     * @param string $module Full path of the Module archive.
     * @return boolean
     * @since 5.1
     */
    public function redeploy($module)
    {
        $module = realpath($module);
        if (!file_exists($module)) {
            throw new \Innomatic\Module\ModuleException('Unable to find Module');
        }

        $context = \Innomatic\Module\Server\ModuleServerContext::instance('\Innomatic\Module\Server\ModuleServerContext');

        $tmp_dir = $context->getHome().'core/temp/appinst/deploy_'.rand().DIRECTORY_SEPARATOR;
        mkdir($tmp_dir, 0755, true);

        if (is_dir($module)) {
            // Copies the Module directory
            \Innomatic\Io\Filesystem\DirectoryUtils::dirCopy($module.'/', $tmp_dir);
        } else {
            // Unpacks the Module archive.
            $arc = new \Innomatic\Io\Archive\Archive($module, \Innomatic\Io\Archive\Archive::FORMAT_TAR);
            if (!$arc->extract($tmp_dir)) {
                \Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree($tmp_dir);
                throw new \Innomatic\Module\ModuleException('Unable to extract Module');
            }
        }

        // Checks if cmb.xml file exists.
        if (!file_exists($tmp_dir.'setup/module.xml')) {
            \Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree($tmp_dir);
            throw new \Innomatic\Module\ModuleException('Missing module.xml configuration file');
        }

        // Parses Module configuration.
        $cfg = \Innomatic\Module\Util\ModuleXmlConfig::getInstance($tmp_dir.'setup/module.xml');
        if (!strlen($cfg->getName())) {
            \Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree($tmp_dir);
            throw new \Innomatic\Module\ModuleException('Missing Module name in module.xml');
        }

        // Checks if the Module to be redeployed exists in modules directory.
        if (!is_dir($context->getHome().'core/modules'.DIRECTORY_SEPARATOR.$cfg->getName())) {
            \Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree($tmp_dir);
            throw new \Innomatic\Module\ModuleException('Module to be redeployed does not exists');
        }

        // Checks Module code.
        $code_checker = new \Innomatic\Php\PHPCodeChecker();
        if (!$code_checker->checkDirectory($tmp_dir)) {
            \Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree($tmp_dir);
            throw new \Innomatic\Module\ModuleException('Module contains errors in code');
        }

        // Removes old Module.
        if (is_dir($context->getHome().'core/modules'.DIRECTORY_SEPARATOR.$cfg->getName())) {
            \Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree($context->getHome().'core/modules'.DIRECTORY_SEPARATOR.$cfg->getName());
        }

        // Deploys new Module.
        $module_dir = $context->getHome().'core/modules'.DIRECTORY_SEPARATOR.$cfg->getName().DIRECTORY_SEPARATOR;
        mkdir($module_dir, 0755, true);
        \Innomatic\Io\Filesystem\DirectoryUtils::dirCopy($tmp_dir.'/', $module_dir);
        \Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree($tmp_dir);

        // Executes Module redeploy hooked actions.
        //$auth = \Innomatic\Module\Server\ModuleServerAuthenticator::instance('ModuleServerAuthenticator');
        //$module = \Innomatic\Module\ModuleFactory::getModule(new ModuleLocator('module://admin:'.$auth->getPassword('admin').'@/'.$cfg->getName()));
        //$module->redeploy();

        // Logs redeployment.
        if ($context->getConfig()->getKey('log_deployer_events') == 1 or $context->getConfig()->useDefaults()) {
            $logger = new \Innomatic\Module\Server\ModuleServerLogger($context->getHome().'core/log/module-deployer.log');
            $logger->logEvent($cfg->getName().' redeployed');
        }

        return true;
    }

    /**
     * Undeploys a Module in the Module server.
     *
     * @param string $location Module name.
     * @return boolean
     * @since 5.1
     */
    public function undeploy($location)
    {
        // Checks if the specified Module exists.
        $context = \Innomatic\Module\Server\ModuleServerContext::instance('\Innomatic\Module\Server\ModuleServerContext');
        if (!is_dir($context->getHome().'core/modules'.DIRECTORY_SEPARATOR.$location)) {
            throw new \Innomatic\Module\ModuleException('No such Module');
        }

        // Executes Module undeploy hooked actions.
        $auth = \Innomatic\Module\Server\ModuleServerAuthenticator::instance('ModuleServerAuthenticator');
        $module = \Innomatic\Module\ModuleFactory::getModule(new ModuleLocator('module://admin:'.$auth->getPassword('admin').'@/'.$location));
        $module->undeploy();

        // Removes Module.
        \Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree($context->getHome().'core/modules'.DIRECTORY_SEPARATOR.$location);

        // Logs undeployment.
        if ($context->getConfig()->getKey('log_deployer_events') == 1 or $context->getConfig()->useDefaults()) {
            $logger = new \Innomatic\Module\Server\ModuleServerLogger($context->getHome().'core/log/module-deployer.log');
            $logger->logEvent($location.' undeployed');
        }

        return true;
    }
}
