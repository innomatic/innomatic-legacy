<?php        

require_once('innomatic/php/PHPCodeChecker.php');
require_once('innomatic/module/server/ModuleServerContext.php');
require_once('innomatic/module/server/ModuleServerLogger.php');
require_once('innomatic/module/server/ModuleServerAuthenticator.php');
require_once('innomatic/module/util/ModuleXmlConfig.php');
require_once('innomatic/module/ModuleFactory.php');
require_once('innomatic/module/ModuleException.php');
require_once('innomatic/io/archive/Archive.php');

/**
 * Module deployer class.
 *
 * This class deploys Module in the Module server modules directory and manages
 * redeployment and undeployment.
 *
 * @author Alex Pagnoni <alex.pagnoni@innoteam.it>
 * @copyright Copyright 2004-2013 Innoteam S.r.l.
 * @since 5.1
 */
class ModuleDeployer {
    /**
     * Deploys a Module in the Module server.
     *
     * @access public
     * @param string $module Full path of the Module archive.
     * @return boolean
     * @since 5.1
     */
    public function deploy($module) {
        $module = realpath($module);
        if (!file_exists($module)) {
            return false;
        }

        $context = ModuleServerContext::instance('ModuleServerContext');

        // Unpacks the Module archive.
        $arc = new Archive($module, Archive::FORMAT_TAR);
        $tmp_dir = $context->getHome().'temp'.DIRECTORY_SEPARATOR.'deploy_'.rand().DIRECTORY_SEPARATOR;
        mkdir($tmp_dir, 0755, true);
        if (!$arc->extract($tmp_dir)) {
            throw new ModuleException('Unable to extract Module');
        }

        // Checks if module.xml file exists.
        if (!file_exists($tmp_dir.'META-INF/module.xml')) {
            $this->removeDir($tmp_dir);
            throw new ModuleException('Missing module.xml configuration file');
        }

        // Parses Module configuration.
        $cfg = ModuleXmlConfig::getInstance($tmp_dir.'META-INF/module.xml');
        if (!strlen($cfg->getName())) {
            $this->removeDir($tmp_dir);
            throw new ModuleException('Missing Module name in module.xml');
        }

        // Checks if a Module with that name already exists.
        if (is_dir($context->getHome().'modules'.DIRECTORY_SEPARATOR.$cfg->getName())) {
            $this->removeDir($tmp_dir);
            throw new ModuleException('A Module with that name already exists');
        }

        // Checks Module code.
        $code_checker = new PHPCodeChecker();
        if (!$code_checker->checkDirectory($tmp_dir)) {
            $this->removeDir($tmp_dir);
            throw new ModuleException('Module contains errors in code');
        }

        // Deploys the Module.
        $module_dir = $context->getHome().'modules'.DIRECTORY_SEPARATOR.$cfg->getName().DIRECTORY_SEPARATOR;
        mkdir($module_dir, 0755, true);
        $this->copyDir($tmp_dir, $module_dir);
        $this->removeDir($tmp_dir);

        // Runs Module deploy hooked actions.
        $auth = ModuleServerAuthenticator::instance('ModuleServerAuthenticator');
        $module = ModuleFactory::getModule(new ModuleLocator('module://admin:'.$auth->getPassword('admin').'@/'.$cfg->getName()));
        $module->deploy();

        // Logs deployment.
        if ($context->getConfig()->getKey('log_deployer_events') == 1 or $context->getConfig()->useDefaults()) {
            $logger = new ModuleServerLogger($context->getHome().'core/log/module-deployer.log');
            $logger->logEvent($cfg->getName().' deployed');
        }

        return true;
    }

    /**
     * Redeploys a Module in the Module server.
     *
     * @access public
     * @param string $module Full path of the Module archive.
     * @return boolean
     * @since 5.1
     */
    public function redeploy($module) {
        $module = realpath($module);
        if (!file_exists($module)) {
            throw new ModuleException('Unable to find Module');
        }

        $context = ModuleServerContext::instance('ModuleServerContext');

        // Unpacks the Module archive.
        $arc = new Archive($module, Archive::FORMAT_TAR);
        $tmp_dir = $context->getHome().'temp'.DIRECTORY_SEPARATOR.'deploy_'.rand().DIRECTORY_SEPARATOR;
        mkdir($tmp_dir, 0755, true);
        if (!$arc->extract($tmp_dir)) {
            throw new ModuleException('Unable to extract Module');
        }

        // Checks if cmb.xml file exists.
        if (!file_exists($tmp_dir.'META-INF/module.xml')) {
            $this->removeDir($tmp_dir);
            throw new ModuleException('Missing module.xml configuration file');
        }

        // Parses Module configuration.
        $cfg = ModuleXmlConfig::getInstance($tmp_dir.'META-INF/module.xml');
        if (!strlen($cfg->getName())) {
            $this->removeDir($tmp_dir);
            throw new ModuleException('Missing Module name in module.xml');
        }

        // Checks if the Module to be redeployed exists in modules directory.
        if (!is_dir($context->getHome().'modules'.DIRECTORY_SEPARATOR.$cfg->getName())) {
            $this->removeDir($tmp_dir);
            throw new ModuleException('Module to be redeployed does not exists');
        }

        // Checks Module code.
        $code_checker = new PHPCodeChecker();
        if (!$code_checker->checkDirectory($tmp_dir)) {
            $this->removeDir($tmp_dir);
            throw new ModuleException('Module contains errors in code');
        }

        // Removes old Module.
        if (is_dir($context->getHome().'modules'.DIRECTORY_SEPARATOR.$cfg->getName())) {
            $this->removeDir($context->getHome().'modules'.DIRECTORY_SEPARATOR.$cfg->getName());
        }

        // Deploys new Module.
        $module_dir = $context->getHome().'modules'.DIRECTORY_SEPARATOR.$cfg->getName().DIRECTORY_SEPARATOR;
        mkdir($module_dir, 0755, true);
        $this->copyDir($tmp_dir, $module_dir);
        $this->removeDir($tmp_dir);

        // Executes Module redeploy hooked actions.
        $auth = ModuleServerAuthenticator::instance('ModuleServerAuthenticator');
        $module = ModuleFactory::getModule(new ModuleLocator('module://admin:'.$auth->getPassword('admin').'@/'.$cfg->getName()));
        $module->redeploy();

        // Logs redeployment.
        if ($context->getConfig()->getKey('log_deployer_events') == 1 or $context->getConfig()->useDefaults()) {
            $logger = new ModuleServerLogger($context->getHome().'core/log/module-deployer.log');
            $logger->logEvent($cfg->getName().' redeployed');
        }

        return true;
    }

    /**
     * Undeploys a Module in the Module server.
     *
     * @access public
     * @param string $location Module name.
     * @return boolean
     * @since 5.1
     */
    public function undeploy($location) {
        // Checks if the specified Module exists.
        $context = ModuleServerContext::instance('ModuleServerContext');
        if (!is_dir($context->getHome().'modules'.DIRECTORY_SEPARATOR.$location)) {
            throw new ModuleException('No such Module');
        }

        // Executes Module undeploy hooked actions.
        $auth = ModuleServerAuthenticator::instance('ModuleServerAuthenticator');
        $module = ModuleFactory::getModule(new ModuleLocator('module://admin:'.$auth->getPassword('admin').'@/'.$location));
        $module->undeploy();

        // Removes Module.
        $this->removeDir($context->getHome().'modules'.DIRECTORY_SEPARATOR.$location);

        // Logs undeployment.
        if ($context->getConfig()->getKey('log_deployer_events') == 1 or $context->getConfig()->useDefaults()) {
            $logger = new ModuleServerLogger($context->getHome().'core/log/module-deployer.log');
            $logger->logEvent($location.' undeployed');
        }

        return true;
    }

    /**
     * Recursively removes a directory.
     *
     * @access protected
     * @param string $dirname Directory name.
     * @return boolean
     */
    protected static function removeDir($dirname) {
        $result = true;
        if (file_exists($dirname)) {
            if ($dhandle = @ opendir($dirname)) {
                while (false != ($file = @ readdir($dhandle))) {
                    if ($file != '.' && $file != '..') {
                        if (is_file($dirname.'/'.$file))
                            $result = @ unlink($dirname.'/'.$file);
                        elseif (is_dir($dirname.'/'.$file)) $result = self::removeDir($dirname.'/'.$file);
                    }
                }
                @ closedir($dhandle);
                @ rmdir($dirname);
            }
        }
        return $result;
    }

    /**
     * Recursively copies a directory.
     *
     * @access protected
     * @param string $from_path Source directory path.
     * @param string $to_path Destination directory path.
     * @return boolean
     */
    protected static function copyDir($from_path, $to_path) {
        $result = true;

        $this_path = getcwd();
        if (!is_dir($to_path)) {
            if (strpos(strtolower($_ENV['OS']), 'windows') !== false)
                $to_path = str_replace('/', '\\', $to_path);
            mkdir($to_path, 0775, true);
        }

        if (is_dir($from_path)) {
            chdir($from_path);
            $handle = opendir('.');
            while (($file = readdir($handle)) !== false) {
                if (($file != ".") && ($file != "..")) {
                    if (is_dir($file)) {
                        chdir($this_path);
                        $result = self::copyDir($from_path.$file.DIRECTORY_SEPARATOR, $to_path.$file.DIRECTORY_SEPARATOR);
                        chdir($this_path);
                        chdir($from_path);
                    }
                    if (is_file($file)) {
                        chdir($this_path);
                        $result = copy($from_path.$file, $to_path.$file);
                        chdir($from_path);
                    }
                }
            }
            closedir($handle);
        }
        chdir($this_path);

        return $result;
    }
}

?>