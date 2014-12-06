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
 * Shared component handler.
 */
class SharedComponent extends \Innomatic\Application\ApplicationComponent
{
    public function __construct($rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
    }
    public static function getType()
    {
        return 'shared';
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
        $result = false;
        if (strlen($params['name'])) {
            $file = $this->basedir . '/shared/' . $params['name'];
            if (is_dir($file)) {
                if (\Innomatic\Io\Filesystem\DirectoryUtils::dirCopy($file.'/', $this->container->getHome().'shared/'.basename($file).'/')) {
                    $result = true;
                }
            } else {
                if (@copy($file, $this->container->getHome() . 'shared/' . basename($file))) {
                    @chmod($this->container->getHome() . 'shared/' . basename($file), 0644);
                    $result = true;
                }
            }
        } else
            $this->mLog->logEvent('innomatic.sharedcomponent.sharedcomponent.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty shared file name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
    public function doUninstallAction($params)
    {
        $result = false;
        if (strlen($params['name'])) {
            if (is_dir($this->container->getHome() . 'shared/' . basename($params['name']))) {
                \Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree($this->container->getHome() . 'shared/' . basename($params['name']));
                $result = true;
            } else {
                if (@unlink($this->container->getHome() . 'shared/' . basename($params['name']))) {
                    $result = true;
                }
            }
        } else
            $this->mLog->logEvent('innomatic.sharedcomponent.sharedcomponent.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty shared file name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
    public function doUpdateAction($params)
    {
        if (strlen($params['name'])) {
            if (is_dir($this->container->getHome() . 'shared/' . basename($params['name']))) {
                \Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree($this->container->getHome() . 'shared/' . basename($params['name']));
                $result = true;
            } else {
                if (@unlink($this->container->getHome() . 'shared/' . basename($params['name']))) {
                    $result = true;
                }
            }

            $file = $this->basedir . '/shared/' . $params['name'];
            if (is_dir($file)) {
                if (\Innomatic\Io\Filesystem\DirectoryUtils::dirCopy($file.'/', $this->container->getHome().'shared/'.basename($file).'/')) {
                    $result = true;
                }
            } else {
                if (@copy($file, $this->container->getHome() . 'shared/' . basename($file))) {
                    @chmod($this->container->getHome() . 'shared/' . basename($file), 0644);
                    $result = true;
                }
            }
        } else
            $this->mLog->logEvent('innomatic.sharedcomponent.sharedcomponent.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty shared file name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
}
