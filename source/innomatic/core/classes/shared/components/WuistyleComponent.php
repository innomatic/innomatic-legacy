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
 * Wuistyle component handler.
 */
class WuistyleComponent extends \Innomatic\Application\ApplicationComponent
{
    public function __construct($rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
    }
    public static function getType()
    {
        return 'wuistyle';
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
        if (strlen($params['file'])) {
            $params['file'] = $this->basedir . '/core/conf/themes/' . basename($params['file']);
            // Creates themes configuration folder if it doesn't exists
            if (! is_dir($this->container->getHome() . 'core/conf/themes/')) {
                \Innomatic\Io\Filesystem\DirectoryUtils::mktree($this->container->getHome() . 'core/conf/themes/', 0755);
            }
            if (@copy($params['file'], $this->container->getHome() . 'core/conf/themes/' . basename($params['file']))) {
                @chmod($this->container->getHome() . 'core/conf/themes/' . basename($params['file']), 0644);
                $wui_component = new \Innomatic\Wui\Theme\WuiStyle($this->rootda, $params['name']);
                $params['file'] = basename($params['file']);
                if ($wui_component->Install($params)) {
                    $style_components = $wui_component->getStyle();
                    if (! file_exists($this->container->getHome() . 'shared/styles'))
                        @mkdir($this->container->getHome() . 'shared/styles', 0755);
                    if (! file_exists($this->container->getHome() . 'shared/styles/' . $params['name']))
                        @mkdir($this->container->getHome() . 'shared/styles/' . $params['name'], 0755);
                    while (list (, $file) = each($style_components)) {
                        if (strlen($file['value']))
                            @copy($this->basedir . '/shared/styles/' . $params['name'] . '/' . $file['value'], $this->container->getHome() . 'shared/styles/' . $params['name'] . '/' . $file['value']);
                    }
                    $result = true;
                } else
                    $this->mLog->logEvent('innomatic.wuistylecomponent.wuistyle.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to install component', \Innomatic\Logging\Logger::ERROR);
            } else
                $this->mLog->logEvent('innomatic.wuistylecomponent.wuistyle.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to copy wui component file (' . $params['file'] . ') to its destination (' . $this->container->getHome() . 'core/conf/themes/' . basename($params['file']) . ')', \Innomatic\Logging\Logger::ERROR);
        } else
            $this->mLog->logEvent('innomatic.wuistylecomponent.wuistyle.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty component file name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
    public function doUninstallAction($params)
    {
        $result = false;
        if (strlen($params['file'])) {
            $wui_component = new \Innomatic\Wui\Theme\WuiStyle($this->rootda, $params['name']);
            if ($wui_component->Remove($params)) {
                $style_components = $wui_component->getStyle();
                while (list (, $file) = each($style_components)) {
                    if (strlen($file['value']))
                        @unlink($this->container->getHome() . 'shared/styles/' . $params['name'] . '/' . $file['value']);
                }
                if (! file_exists($this->container->getHome() . 'shared/styles/' . $params['name']))
                    @rmdir($this->container->getHome() . 'shared/styles/' . $params['name']);
                if (@unlink($this->container->getHome() . 'core/conf/themes/' . basename($params['file']))) {
                    $result = true;
                }
            } else
                $this->mLog->logEvent('innomatic.wuistylecomponent.wuistyle.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to uninstall component', \Innomatic\Logging\Logger::ERROR);
        } else
            $this->mLog->logEvent('innomatic.wuistylecomponent.wuistyle.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty component file name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
    public function doUpdateAction($params)
    {
        $result = false;
        if (strlen($params['file'])) {
            $params['file'] = $this->basedir . '/core/conf/themes/' . basename($params['file']);
            // Creates themes configuration folder if it doesn't exists
            if (!is_dir($this->container->getHome() . 'core/conf/themes/')) {
                \Innomatic\Io\Filesystem\DirectoryUtils::mktree($this->container->getHome() . 'core/conf/themes/', 0755);
            }
            if (@copy($params['file'], $this->container->getHome() . 'core/conf/themes/' . basename($params['file']))) {
                @chmod($this->container->getHome() . 'core/conf/themes/' . basename($params['file']), 0644);
                $wui_component = new \Innomatic\Wui\Theme\WuiStyle($this->rootda, $params['name']);
                $params['file'] = basename($params['file']);
                if ($wui_component->Update($params)) {
                    $style_components = $wui_component->getStyle();
                    if (! file_exists($this->container->getHome() . 'shared/styles'))
                        @mkdir($this->container->getHome() . 'shared/styles', 0755);
                    if (! file_exists($this->container->getHome() . 'shared/styles/' . $params['name']))
                        @mkdir($this->container->getHome() . 'shared/styles/' . $params['name'], 0755);
                    while (list (, $file) = each($style_components)) {
                        if (strlen($file['value']))
                            @copy($this->basedir . '/shared/styles/' . $params['name'] . '/' . $file['value'], $this->container->getHome() . 'shared/styles/' . $params['name'] . '/' . $file['value']);
                    }
                    $result = true;
                } else
                    $this->mLog->logEvent('innomatic.wuistylecomponent.wuistyle.doupdateaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to update component', \Innomatic\Logging\Logger::ERROR);
            } else
                $this->mLog->logEvent('innomatic.wuistylecomponent.wuistyle.doupdateaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to copy wui component file (' . $params['file'] . ') to its destination (' . $this->container->getHome() . 'core/conf/themes/' . basename($params['file']) . ')', \Innomatic\Logging\Logger::ERROR);
        } else
            $this->mLog->logEvent('innomatic.wuistylecomponent.wuistyle.doupdateaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty component file name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
}
