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
namespace Shared\Components;

require_once ('innomatic/application/ApplicationComponent.php');
require_once ('innomatic/wui/theme/WuiIconsSet.php');
/**
 * Wuiiconsset component handler.
 */
class WuiiconssetComponent extends ApplicationComponent
{
    public function __construct($rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
    }
    public static function getType()
    {
        return 'wuiiconsset';
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
    public function DoInstallAction($params)
    {
        $result = false;
        if (strlen($params['file'])) {
            $params['file'] = $this->basedir . '/core/conf/themes/' . basename($params['file']);
            // Creates themes configuration folder if it doesn't exists
            if (!is_dir(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/conf/themes/')) {
                \Innomatic\Io\Filesystem\DirectoryUtils::mktree(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/conf/themes/', 0755);
            }
            if (@copy($params['file'], InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/conf/themes/' . basename($params['file']))) {
                @chmod(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/conf/themes/' . basename($params['file']), 0644);
                $wui_component = new WuiIconsSet($this->rootda, $params['name']);
                $params['file'] = basename($params['file']);
                if ($wui_component->Install($params)) {
                    $set_components = $wui_component->getIconsSet();
                    if (! file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons'))
                        @mkdir(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons', 0755);
                    if (! file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name']))
                        @mkdir(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'], 0755);
                    if (! file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/actions'))
                        @mkdir(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/actions', 0755);
                    if (! file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/apps'))
                        @mkdir(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/apps', 0755);
                    if (! file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/devices'))
                        @mkdir(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/devices', 0755);
                    if (! file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/filesystems'))
                        @mkdir(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/filesystems', 0755);
                    if (! file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/mimetypes'))
                        @mkdir(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/mimetypes', 0755);
                    if (! file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/mini'))
                        @mkdir(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/mini', 0755);
                    if (! file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/icons'))
                        @mkdir(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/icons', 0755);
                    if (! file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/light'))
                        @mkdir(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/light', 0755);
                    if (is_array($set_components['actions'])) {
                        while (list (, $file) = each($set_components['actions'])) {
                            if (strlen($file['file']))
                                @copy($this->basedir . '/shared/icons/' . $params['name'] . '/actions/' . $file['file'], InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/actions/' . $file['file']);
                        }
                    }
                    if (is_array($set_components['apps'])) {
                        while (list (, $file) = each($set_components['apps'])) {
                            if (strlen($file['file']))
                                @copy($this->basedir . '/shared/icons/' . $params['name'] . '/apps/' . $file['file'], InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/apps/' . $file['file']);
                        }
                    }
                    if (is_array($set_components['devices'])) {
                        while (list (, $file) = each($set_components['devices'])) {
                            if (strlen($file['file']))
                                @copy($this->basedir . '/shared/icons/' . $params['name'] . '/devices/' . $file['file'], InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/devices/' . $file['file']);
                        }
                    }
                    if (is_array($set_components['filesystems'])) {
                        while (list (, $file) = each($set_components['filesystems'])) {
                            if (strlen($file['file']))
                                @copy($this->basedir . '/shared/icons/' . $params['name'] . '/filesystems/' . $file['file'], InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/filesystems/' . $file['file']);
                        }
                    }
                    if (is_array($set_components['mimetypes'])) {
                        while (list (, $file) = each($set_components['mimetypes'])) {
                            if (strlen($file['file']))
                                @copy($this->basedir . '/shared/icons/' . $params['name'] . '/mimetypes/' . $file['file'], InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/mimetypes/' . $file['file']);
                        }
                    }
                    if (is_array($set_components['mini'])) {
                        while (list (, $file) = each($set_components['mini'])) {
                            if (strlen($file['file']))
                                @copy($this->basedir . '/shared/icons/' . $params['name'] . '/mini/' . $file['file'], InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/mini/' . $file['file']);
                        }
                    }
                    if (is_array($set_components['icons'])) {
                        while (list (, $file) = each($set_components['icons'])) {
                            if (strlen($file['file']))
                                @copy($this->basedir . '/shared/icons/' . $params['name'] . '/icons/' . $file['file'], InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/icons/' . $file['file']);
                        }
                    }
                    if (is_array($set_components['light'])) {
                        while (list (, $file) = each($set_components['light'])) {
                            if (strlen($file['file']))
                                @copy($this->basedir . '/shared/icons/' . $params['name'] . '/light/' . $file['file'], InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/light/' . $file['file']);
                        }
                    }

                    $result = true;
                } else
                    $this->mLog->logEvent('innomatic.wuiiconssetcomponent.wuiiconsset.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to install component', \Innomatic\Logging\Logger::ERROR);
            } else
                $this->mLog->logEvent('innomatic.wuiiconssetcomponent.wuiiconsset.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to copy wui component file (' . $params['file'] . ') to its destination (' . InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/conf/themes/' . basename($params['file']) . ')', \Innomatic\Logging\Logger::ERROR);
        } else
            $this->mLog->logEvent('innomatic.wuiiconssetcomponent.wuiiconsset.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty component file name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
    public function DoUninstallAction($params)
    {
        $result = false;
        if (strlen($params['file'])) {
            $wui_component = new WuiIconsSet($this->rootda, $params['name']);
            if ($wui_component->Remove($params)) {
                $set_components = $wui_component->getIconsSet();
                if (is_array($set_components['actions'])) {
                    while (list (, $file) = each($set_components['actions'])) {
                        if (strlen($file['file']))
                            @unlink(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/actions/' . $file['file']);
                    }
                }
                if (is_array($set_components['apps'])) {
                    while (list (, $file) = each($set_components['apps'])) {
                        if (strlen($file['file']))
                            @unlink(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/apps/' . $file['file']);
                    }
                }
                if (is_array($set_components['devices'])) {
                    while (list (, $file) = each($set_components['devices'])) {
                        if (strlen($file['file']))
                            @unlink(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/devices/' . $file['file']);
                    }
                }
                if (is_array($set_components['filesystems'])) {
                    while (list (, $file) = each($set_components['filesystems'])) {
                        if (strlen($file['file']))
                            @unlink(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/filesystems/' . $file['file']);
                    }
                }
                if (is_array($set_components['mimetypes'])) {
                    while (list (, $file) = each($set_components['mimetypes'])) {
                        if (strlen($file['file']))
                            @unlink(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/mimetypes/' . $file['file']);
                    }
                }
                if (is_array($set_components['mini'])) {
                    while (list (, $file) = each($set_components['mini'])) {
                        if (strlen($file['file']))
                            @unlink(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/mini/' . $file['file']);
                    }
                }
                if (is_array($set_components['icons'])) {
                    while (list (, $file) = each($set_components['icons'])) {
                        if (strlen($file['file']))
                            @unlink(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/icons/' . $file['file']);
                    }
                }
                if (is_array($set_components['light'])) {
                    while (list (, $file) = each($set_components['light'])) {
                        if (strlen($file['file']))
                            @unlink(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/light/' . $file['file']);
                    }
                }
                if (! file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/actions'))
                    @rmdir(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/actions');
                if (! file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/apps'))
                    @rmdir(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/apps');
                if (! file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/devices'))
                    @rmdir(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/devices');
                if (! file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/filesystems'))
                    @rmdir(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/filesystems');
                if (! file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/mimetypes'))
                    @rmdir(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/mimetypes');
                if (! file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/mini'))
                    @rmdir(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/mini');
                if (! file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/light'))
                    @rmdir(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/light');
                if (! file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/icons'))
                    @rmdir(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/icons');
                if (! file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name']))
                    @rmdir(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name']);
                if (@unlink(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/conf/themes/' . basename($params['file']))) {
                    $result = true;
                }
            } else
                $this->mLog->logEvent('innomatic.wuiiconssetcomponent.wuiiconsset.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to uninstall component', \Innomatic\Logging\Logger::ERROR);
        } else
            $this->mLog->logEvent('innomatic.wuiiconssetcomponent.wuiiconsset.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty component file name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
    public function DoUpdateAction($params)
    {
        $result = false;
        if (strlen($params['file'])) {
            $params['file'] = $this->basedir . '/core/conf/themes/' . basename($params['file']);
            // Creates themes configuration folder if it doesn't exists
            if (!is_dir(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/conf/themes/')) {
                \Innomatic\Io\Filesystem\DirectoryUtils::mktree(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/conf/themes/', 0755);
            }
            if (@copy($params['file'], InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/conf/themes/' . basename($params['file']))) {
                @chmod(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/conf/themes/' . basename($params['file']), 0644);
                $wui_component = new WuiIconsSet($this->rootda, $params['name']);
                $params['file'] = basename($params['file']);
                if ($wui_component->Update($params)) {
                    $set_components = $wui_component->getIconsSet();
                    if (! file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons'))
                        @mkdir(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons', 0755);
                    if (! file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name']))
                        @mkdir(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'], 0755);
                    if (! file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/actions'))
                        @mkdir(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/actions', 0755);
                    if (! file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/apps'))
                        @mkdir(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/apps', 0755);
                    if (! file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/devices'))
                        @mkdir(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/devices', 0755);
                    if (! file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/filesystems'))
                        @mkdir(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/filesystems', 0755);
                    if (! file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/mimetypes'))
                        @mkdir(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/mimetypes', 0755);
                    if (! file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/mini'))
                        @mkdir(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/mini', 0755);
                    if (! file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/icons'))
                        @mkdir(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/icons', 0755);
                    if (! file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/light'))
                        @mkdir(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/light', 0755);
                    if (is_array($set_components['actions'])) {
                        while (list (, $file) = each($set_components['actions'])) {
                            if (strlen($file['file']))
                                @copy($this->basedir . '/shared/icons/' . $params['name'] . '/actions/' . $file['file'], InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/actions/' . $file['file']);
                        }
                    }
                    if (is_array($set_components['apps'])) {
                        while (list (, $file) = each($set_components['apps'])) {
                            if (strlen($file['file']))
                                @copy($this->basedir . '/shared/icons/' . $params['name'] . '/apps/' . $file['file'], InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/apps/' . $file['file']);
                        }
                    }
                    if (is_array($set_components['devices'])) {
                        while (list (, $file) = each($set_components['devices'])) {
                            if (strlen($file['file']))
                                @copy($this->basedir . '/shared/icons/' . $params['name'] . '/devices/' . $file['file'], InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/devices/' . $file['file']);
                        }
                    }
                    if (is_array($set_components['filesystems'])) {
                        while (list (, $file) = each($set_components['filesystems'])) {
                            if (strlen($file['file']))
                                @copy($this->basedir . '/shared/icons/' . $params['name'] . '/filesystems/' . $file['file'], InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/filesystems/' . $file['file']);
                        }
                    }
                    if (is_array($set_components['mimetypes'])) {
                        while (list (, $file) = each($set_components['mimetypes'])) {
                            if (strlen($file['file']))
                                @copy($this->basedir . '/shared/icons/' . $params['name'] . '/mimetypes/' . $file['file'], InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/mimetypes/' . $file['file']);
                        }
                    }
                    if (is_array($set_components['mini'])) {
                        while (list (, $file) = each($set_components['mini'])) {
                            if (strlen($file['file']))
                                @copy($this->basedir . '/shared/icons/' . $params['name'] . '/mini/' . $file['file'], InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/mini/' . $file['file']);
                        }
                    }
                    if (is_array($set_components['icons'])) {
                        while (list (, $file) = each($set_components['icons'])) {
                            if (strlen($file['file']))
                                @copy($this->basedir . '/shared/icons/' . $params['name'] . '/icons/' . $file['file'], InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/icons/' . $file['file']);
                        }
                    }
                    if (is_array($set_components['light'])) {
                        while (list (, $file) = each($set_components['light'])) {
                            if (strlen($file['file']))
                                @copy($this->basedir . '/shared/icons/' . $params['name'] . '/light/' . $file['file'], InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/icons/' . $params['name'] . '/light/' . $file['file']);
                        }
                    }
                    $result = true;
                } else
                    $this->mLog->logEvent('innomatic.wuiiconssetcomponent.wuiiconsset.doupdateaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to update component', \Innomatic\Logging\Logger::ERROR);
            } else
                $this->mLog->logEvent('innomatic.wuiiconssetcomponent.wuiiconsset.doupdateaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to copy wui component file (' . $params['file'] . ') to its destination (' . InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/conf/themes/' . basename($params['file']) . ')', \Innomatic\Logging\Logger::ERROR);
        } else
            $this->mLog->logEvent('innomatic.wuiiconssetcomponent.wuiiconsset.doupdateaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty component file name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
}
