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
 * @license    http://www.innomaticplatform.com/license/ New BSD License
 * @link       http://www.innomaticplatform.com
 * @since      Class available since Release 5.0
 */
namespace Shared\Components;

/**
 * Binary component handler.
 */
class BinaryComponent extends \Innomatic\Application\ApplicationComponent
{
    public function __construct($rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
        // Creates the binaries directory if it doesn't exists.
        if (!is_dir(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/bin/')) {
            \Innomatic\Io\Filesystem\DirectoryUtils::mktree(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/bin/', 0755);
        }
    }
    public static function getType()
    {
        return 'binary';
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
            $this->mLog->logEvent('BinaryComponent::doInstallAction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty binary file name', \Innomatic\Logging\Logger::ERROR);
            return false;
        }
        // Checks if the binary file exists in application archive.
        if (!file_exists($this->basedir . '/core/bin/' . $params['name'])) {
            $this->mLog->logEvent('BinaryComponent::doInstallAction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Missing binary file', \Innomatic\Logging\Logger::ERROR);
            return false;
        }
        // Cheks that the binary file name does not contain malicious code.
        if (\Innomatic\Security\SecurityManager::isAboveBasePath(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/bin/' . $params['name'], \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/bin/')) {
            $this->mLog->logEvent('BinaryComponent::doInstallAction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Malicious binary file name', \Innomatic\Logging\Logger::ERROR);
            return false;
        }
        // Checks if the binary file name contains a directory.
        $dirname = dirname($params['name']);
        if ($dirname != '.') {
            \Innomatic\Io\Filesystem\DirectoryUtils::mktree(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/bin/' . $params['name'], 0755);
        }
        // Copies the binary file.
        if (! copy($this->basedir . '/core/bin/' . $params['name'], \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/bin/' . $params['name'])) {
            $this->mLog->logEvent('BinaryComponent::doInstallAction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to copy binary file', \Innomatic\Logging\Logger::ERROR);
            return false;
        }
        // Updates file permissions.
        chmod(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/bin/' . $params['name'], 0644);
        return true;
    }
    public function doUninstallAction($params)
    {
        // Checks if the name is valid.
        if (! strlen($params['name'])) {
            $this->mLog->logEvent('BinaryComponent::doUninstallAction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty binary file name', \Innomatic\Logging\Logger::ERROR);
            return false;
        }
        // Cheks that the binary file name does not contain malicious code.
        if (\Innomatic\Security\SecurityManager::isAboveBasePath(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/bin/' . $params['name'], \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/bin/')) {
            $this->mLog->logEvent('BinaryComponent::doUninstallAction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Malicious binary file name', \Innomatic\Logging\Logger::ERROR);
            return false;
        }
        // Checks if the binary file exists.
        if (! file_exists(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . '/core/bin/' . $params['name'])) {
            $this->mLog->logEvent('BinaryComponent::doUninstallAction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Missing binary file', \Innomatic\Logging\Logger::ERROR);
            return false;
        }
        if (! @unlink(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/bin/' . $params['name'])) {
            $this->mLog->logEvent('BinaryComponent::doUninstallAction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to remove binary file', \Innomatic\Logging\Logger::ERROR);
            return false;
        }
        return true;
    }
    public function doUpdateAction($params)
    {
        return $this->doInstallAction($params);
    }
}
