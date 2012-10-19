<?php
/**
 * Innomatic
 *
 * LICENSE 
 * 
 * This source file is subject to the new BSD license that is bundled 
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2012 Innoteam S.r.l.
 * @license    http://www.innomatic.org/license/   BSD License
 * @link       http://www.innomatic.org
 * @since      Class available since Release 5.0
 */
require_once ('innomatic/application/ApplicationComponent.php');
/**
 * Configurationfile component handler.
 */
class ConfigurationfileComponent extends ApplicationComponent
{
    function __construct ($rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
        // Creates the configuration directory if it doesn't exists.
        if (! is_dir(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/conf/')) {
            require_once ('innomatic/io/filesystem/DirectoryUtils.php');
            DirectoryUtils::mktree(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/conf/', 0755);
        }
    }
    public static function getType ()
    {
        return 'configurationfile';
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
            $this->mLog->logEvent('ConfigurationfileComponent::doInstallAction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty configuration file name', Logger::ERROR);
            return false;
        }
        // Checks if the configuration file exists in application archive.
        if (! file_exists($this->basedir . '/core/conf/' . $params['name'])) {
            $this->mLog->logEvent('ConfigurationfileComponent::doInstallAction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Missing configuration file', Logger::ERROR);
            return false;
        }
        // Cheks that the configuration file name does not contain malicious code.
        require_once ('innomatic/security/SecurityManager.php');
        if (SecurityManager::isAboveBasePath(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/conf/' . $params['name'], InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/conf/')) {
            $this->mLog->logEvent('ConfigurationfileComponent::doInstallAction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Malicious configuration file name', Logger::ERROR);
            return false;
        }
        // Checks if the configuration file name contains a directory. 
        $dirname = dirname($params['name']);
        if ($dirname != '.') {
            require_once ('innomatic/io/filesystem/DirectoryUtils.php');
            DirectoryUtils::mktree(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/conf/' . $params['name'], 0755);
        }
        // Copies the configuration file.
        if (! copy($this->basedir . '/core/conf/' . $params['name'], InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/conf/' . $params['name'])) {
            $this->mLog->logEvent('ConfigurationfileComponent::doInstallAction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to copy configuration file', Logger::ERROR);
            return false;
        }
        // Updates file permissions.
        chmod(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/conf/' . $params['name'], 0644);
        return true;
    }
    public function doUninstallAction ($params)
    {
        // Checks if the name is valid.
        if (! strlen($params['name'])) {
            $this->mLog->logEvent('ConfigurationfileComponent::doUninstallAction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty configuration file name', Logger::ERROR);
            return false;
        }
        // Cheks that the configuration file name does not contain malicious code.
        require_once ('innomatic/security/SecurityManager.php');
        if (SecurityManager::isAboveBasePath(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/conf/' . $params['name'], InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/conf/')) {
            $this->mLog->logEvent('ConfigurationfileComponent::doUninstallAction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Malicious configuration file name', Logger::ERROR);
            return false;
        }
        // Checks if the configuration file exists.
        if (! file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome() . '/core/conf/' . $params['name'])) {
            $this->mLog->logEvent('ConfigurationfileComponent::doUninstallAction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Missing configuration file', Logger::ERROR);
            return false;
        }
        if (! @unlink(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/conf/' . $params['name'])) {
            $this->mLog->logEvent('ConfigurationfileComponent::doUninstallAction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to remove configuration file', Logger::ERROR);
            return false;
        }
        return true;
    }
    public function doUpdateAction ($params)
    {
        // Checks if the "keep" parameter is set to true.
        // If so, the configuration file will not be overwritten.
        if (isset($params['keep']) and $params['keep'] == true and file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/conf/' . $params['name'])) {
            return true;
        } else {
            return $this->doInstallAction($params);
        }
    }
}
