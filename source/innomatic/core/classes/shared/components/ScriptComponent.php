<?php
/**
 * Innomatic
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2014 Innoteam Srl
 * @license    http://www.innomatic.io/license/ New BSD License
 * @link       http://www.innomatic.io
 * @since      Class available since Release 5.0
 */
namespace Shared\Components;

/**
 * Script component handler.
 */
class ScriptComponent extends \Innomatic\Application\ApplicationComponent
{
    public function __construct($rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
        // Creates the scripts directory if it doesn't exists.
        if (!is_dir($this->container->getHome() . 'core/scripts/')) {
            \Innomatic\Io\Filesystem\DirectoryUtils::mktree($this->container->getHome() . 'core/scripts/', 0755);
        }
    }
    public static function getType()
    {
        return 'script';
    }
    public static function getPriority()
    {
        return 0;
    }
    public static function getIsDomain()
    {
        return false;
    }
    public static function getIsOverridable()
    {
        return false;
    }
    public function doInstallAction($params)
    {
        // Checks if the name is valid.
        if (! strlen($params['name'])) {
            $this->mLog->logEvent('ScriptComponent::doInstallAction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty script file name', \Innomatic\Logging\Logger::ERROR);
            return false;
        }
        // Checks if the script file exists in application archive.
        if (! file_exists($this->basedir . '/core/scripts/' . $params['name'])) {
            $this->mLog->logEvent('ScriptComponent::doInstallAction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Missing script file', \Innomatic\Logging\Logger::ERROR);
            return false;
        }
        // Cheks that the script file name does not contain malicious code.
        if (\Innomatic\Security\SecurityManager::isAboveBasePath($this->container->getHome() . 'core/scripts/' . $params['name'], $this->container->getHome() . 'core/scripts/')) {
            $this->mLog->logEvent('ScriptComponent::doInstallAction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Malicious script file name', \Innomatic\Logging\Logger::ERROR);
            return false;
        }
        // Checks if the script file name contains a directory.
        $dirname = dirname($params['name']);
        if ($dirname != '.') {
            \Innomatic\Io\Filesystem\DirectoryUtils::mktree($this->container->getHome() . 'core/scripts/' . $params['name'], 0755);
        }
        // Copies the script file.
        if (! copy($this->basedir . '/core/scripts/' . $params['name'], $this->container->getHome() . 'core/scripts/' . $params['name'])) {
            $this->mLog->logEvent('ScriptComponent::doInstallAction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to copy script file', \Innomatic\Logging\Logger::ERROR);
            return false;
        }
        // Updates file permissions.
        chmod($this->container->getHome() . 'core/scripts/' . $params['name'], 0644);
        return true;
    }
    public function doUninstallAction($params)
    {
        // Checks if the name is valid.
        if (! strlen($params['name'])) {
            $this->mLog->logEvent('ScriptComponent::doUninstallAction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty script file name', \Innomatic\Logging\Logger::ERROR);
            return false;
        }
        // Cheks that the script file name does not contain malicious code.
        if (\Innomatic\Security\SecurityManager::isAboveBasePath($this->container->getHome() . 'core/scripts/' . $params['name'], $this->container->getHome() . 'core/scripts/')) {
            $this->mLog->logEvent('ScriptComponent::doUninstallAction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Malicious script file name', \Innomatic\Logging\Logger::ERROR);
            return false;
        }
        // Checks if the script file exists.
        if (! file_exists($this->container->getHome() . '/core/scripts/' . $params['name'])) {
            $this->mLog->logEvent('ScriptComponent::doUninstallAction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Missing script file', \Innomatic\Logging\Logger::ERROR);
            return false;
        }
        if (! @unlink($this->container->getHome() . 'core/scripts/' . $params['name'])) {
            $this->mLog->logEvent('ScriptComponent::doUninstallAction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to remove script file', \Innomatic\Logging\Logger::ERROR);
            return false;
        }
        return true;
    }
    public function doUpdateAction($params)
    {
        return $this->doInstallAction($params);
    }
}
