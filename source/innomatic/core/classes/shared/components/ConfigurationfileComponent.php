<?php
/**
 * Innomatic
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2014 Innomatic Company
 * @license    http://www.innomatic.io/license/ New BSD License
 * @link       http://www.innomatic.io
 * @since      Class available since Release 5.0
 */
namespace Shared\Components;

/**
 * Configurationfile component handler.
 */
class ConfigurationfileComponent extends \Innomatic\Application\ApplicationComponent
{
    public function __construct($rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
        // Creates the configuration directory if it doesn't exists.
        if (!is_dir($this->container->getHome() . 'core/conf/')) {
            \Innomatic\Io\Filesystem\DirectoryUtils::mktree($this->container->getHome() . 'core/conf/', 0755);
        }
    }
    public static function getType()
    {
        return 'configurationfile';
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
            $this->mLog->logEvent('ConfigurationfileComponent::doInstallAction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty configuration file name', \Innomatic\Logging\Logger::ERROR);
            return false;
        }
        // Checks if the configuration file exists in application archive.
        if (! file_exists($this->basedir . '/core/conf/' . $params['name'])) {
            $this->mLog->logEvent('ConfigurationfileComponent::doInstallAction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Missing configuration file', \Innomatic\Logging\Logger::ERROR);
            return false;
        }
        // Cheks that the configuration file name does not contain malicious code.
        if (\Innomatic\Security\SecurityManager::isAboveBasePath($this->container->getHome() . 'core/conf/' . $params['name'], $this->container->getHome() . 'core/conf/')) {
            $this->mLog->logEvent('ConfigurationfileComponent::doInstallAction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Malicious configuration file name', \Innomatic\Logging\Logger::ERROR);
            return false;
        }
        // Checks if the configuration file name contains a directory.
        $dirname = dirname($params['name']);
        if ($dirname != '.') {
            \Innomatic\Io\Filesystem\DirectoryUtils::mktree($this->container->getHome() . 'core/conf/' . $params['name'], 0755);
        }
        // Copies the configuration file.
        if (! copy($this->basedir . '/core/conf/' . $params['name'], $this->container->getHome() . 'core/conf/' . $params['name'])) {
            $this->mLog->logEvent('ConfigurationfileComponent::doInstallAction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to copy configuration file', \Innomatic\Logging\Logger::ERROR);
            return false;
        }
        // Updates file permissions.
        chmod($this->container->getHome() . 'core/conf/' . $params['name'], 0644);
        return true;
    }
    public function doUninstallAction($params)
    {
        // Checks if the name is valid.
        if (! strlen($params['name'])) {
            $this->mLog->logEvent('ConfigurationfileComponent::doUninstallAction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty configuration file name', \Innomatic\Logging\Logger::ERROR);
            return false;
        }
        // Cheks that the configuration file name does not contain malicious code.
        if (\Innomatic\Security\SecurityManager::isAboveBasePath($this->container->getHome() . 'core/conf/' . $params['name'], $this->container->getHome() . 'core/conf/')) {
            $this->mLog->logEvent('ConfigurationfileComponent::doUninstallAction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Malicious configuration file name', \Innomatic\Logging\Logger::ERROR);
            return false;
        }
        // Checks if the configuration file exists.
        if (! file_exists($this->container->getHome() . '/core/conf/' . $params['name'])) {
            $this->mLog->logEvent('ConfigurationfileComponent::doUninstallAction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Missing configuration file', \Innomatic\Logging\Logger::ERROR);
            return false;
        }
        if (! @unlink($this->container->getHome() . 'core/conf/' . $params['name'])) {
            $this->mLog->logEvent('ConfigurationfileComponent::doUninstallAction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to remove configuration file', \Innomatic\Logging\Logger::ERROR);
            return false;
        }
        return true;
    }
    public function doUpdateAction($params)
    {
        // Checks if the "keep" parameter is set to true.
        // If so, the configuration file will not be overwritten.
        if (isset($params['keep']) and $params['keep'] == true and file_exists($this->container->getHome() . 'core/conf/' . $params['name'])) {
            return true;
        } else {
            return $this->doInstallAction($params);
        }
    }
}
