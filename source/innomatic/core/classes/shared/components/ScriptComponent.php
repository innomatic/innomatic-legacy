<?php
/**
 * Innomatic
 *
 * LICENSE 
 * 
 * This source file is subject to the new BSD license that is bundled 
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2012 Innoteam Srl
 * @license    http://www.innomatic.org/license/   BSD License
 * @link       http://www.innomatic.org
 * @since      Class available since Release 5.0
 */
require_once ('innomatic/application/ApplicationComponent.php');
/**
 * Script component handler.
 */
class ScriptComponent extends ApplicationComponent
{
    public function __construct ($rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
        // Creates the scripts directory if it doesn't exists.
        if (! is_dir(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/scripts/')) {
            require_once ('innomatic/io/filesystem/DirectoryUtils.php');
            DirectoryUtils::mktree(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/scripts/', 0755);
        }
    }
    public static function getType ()
    {
        return 'script';
    }
    public static function getPriority ()
    {
        return 0;
    }
    public static function getIsDomain ()
    {
        return false;
    }
    public static function getIsOverridable ()
    {
        return false;
    }
    public function doInstallAction ($params)
    {
        // Checks if the name is valid.
        if (! strlen($params['name'])) {
            $this->mLog->logEvent('ScriptComponent::doInstallAction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty script file name', Logger::ERROR);
            return false;
        }
        // Checks if the script file exists in application archive.
        if (! file_exists($this->basedir . '/core/scripts/' . $params['name'])) {
            $this->mLog->logEvent('ScriptComponent::doInstallAction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Missing script file', Logger::ERROR);
            return false;
        }
        // Cheks that the script file name does not contain malicious code.
        require_once ('innomatic/security/SecurityManager.php');
        if (SecurityManager::isAboveBasePath(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/scripts/' . $params['name'], InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/scripts/')) {
            $this->mLog->logEvent('ScriptComponent::doInstallAction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Malicious script file name', Logger::ERROR);
            return false;
        }
        // Checks if the script file name contains a directory. 
        $dirname = dirname($params['name']);
        if ($dirname != '.') {
            require_once ('innomatic/io/filesystem/DirectoryUtils.php');
            DirectoryUtils::mktree(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/scripts/' . $params['name'], 0755);
        }
        // Copies the script file.
        if (! copy($this->basedir . '/core/scripts/' . $params['name'], InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/scripts/' . $params['name'])) {
            $this->mLog->logEvent('ScriptComponent::doInstallAction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to copy script file', Logger::ERROR);
            return false;
        }
        // Updates file permissions.
        chmod(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/scripts/' . $params['name'], 0644);
        return true;
    }
    public function doUninstallAction ($params)
    {
        // Checks if the name is valid.
        if (! strlen($params['name'])) {
            $this->mLog->logEvent('ScriptComponent::doUninstallAction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty script file name', Logger::ERROR);
            return false;
        }
        // Cheks that the script file name does not contain malicious code.
        require_once ('innomatic/security/SecurityManager.php');
        if (SecurityManager::isAboveBasePath(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/scripts/' . $params['name'], InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/scripts/')) {
            $this->mLog->logEvent('ScriptComponent::doUninstallAction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Malicious script file name', Logger::ERROR);
            return false;
        }
        // Checks if the script file exists.
        if (! file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome() . '/core/scripts/' . $params['name'])) {
            $this->mLog->logEvent('ScriptComponent::doUninstallAction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Missing script file', Logger::ERROR);
            return false;
        }
        if (! @unlink(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/scripts/' . $params['name'])) {
            $this->mLog->logEvent('ScriptComponent::doUninstallAction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to remove script file', Logger::ERROR);
            return false;
        }
        return true;
    }
    public function doUpdateAction ($params)
    {
        return $this->doInstallAction($params);
    }
}
