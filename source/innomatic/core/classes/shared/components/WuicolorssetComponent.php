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
require_once ('innomatic/wui/theme/WuiColorsSet.php');
/**
 * Wuicolorsset component handler.
 */
class WuicolorssetComponent extends ApplicationComponent
{
    public function __construct(&$rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
    }
    public static function getType()
    {
        return 'wuicolorsset';
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
            if (! is_dir(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/conf/themes/')) {
                \Innomatic\Io\Filesystem\DirectoryUtils::mktree(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/conf/themes/', 0755);
            }
            if (@copy($params['file'], InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/conf/themes/' . basename($params['file']))) {
                @chmod(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/conf/themes/' . basename($params['file']), 0644);
                $wui_component = new WuiColorsSet($this->rootda, $params['name']);
                $params['file'] = basename($params['file']);
                if ($wui_component->Install($params)) {
                    $result = true;
                } else
                    $this->mLog->logEvent('innomatic.wuicolorssetcomponent.wuicolorsset.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to install component', \Innomatic\Logging\Logger::ERROR);
            } else
                $this->mLog->logEvent('innomatic.wuicolorssetcomponent.wuicolorsset.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to copy wui component file (' . $params['file'] . ') to its destination (' . InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/conf/themes/' . basename($params['file']) . ')', \Innomatic\Logging\Logger::ERROR);
        } else
            $this->mLog->logEvent('innomatic.wuicolorssetcomponent.wuicolorsset.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty component file name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
    public function DoUninstallAction($params)
    {
        $result = false;
        if (strlen($params['file'])) {
            $wui_component = new WuiColorsSet($this->rootda, $params['name']);
            if ($wui_component->Remove($params)) {
                if (@unlink(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/conf/themes/' . basename($params['file']))) {
                    $result = true;
                }
            } else
                $this->mLog->logEvent('innomatic.wuicolorssetcomponent.wuicolorsset.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to uninstall component', \Innomatic\Logging\Logger::ERROR);
        } else
            $this->mLog->logEvent('innomatic.wuicolorssetcomponent.wuicolorsset.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty component file name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
    public function DoUpdateAction($params)
    {
        $result = false;
        if (strlen($params['file'])) {
            $params['file'] = $this->basedir . '/core/conf/themes/' . basename($params['file']);
            // Creates themes configuration folder if it doesn't exists
            if (! is_dir(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/conf/themes/')) {
                \Innomatic\Io\Filesystem\DirectoryUtils::mktree(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/conf/themes/', 0755);
            }
            if (@copy($params['file'], InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/conf/themes/' . basename($params['file']))) {
                @chmod(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/conf/themes/' . basename($params['file']), 0644);
                $wui_component = new WuiColorsSet($this->rootda, $params['name']);
                $params['file'] = basename($params['file']);
                if ($wui_component->Update($params)) {
                    $result = true;
                } else
                    $this->mLog->logEvent('innomatic.wuicolorssetcomponent.wuicolorsset.doupdateaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to update component', \Innomatic\Logging\Logger::ERROR);
            } else
                $this->mLog->logEvent('innomatic.wuicolorssetcomponent.wuicolorsset.doupdateaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to copy wui component file (' . $params['file'] . ') to its destination (' . InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/conf/themes/' . basename($params['file']) . ')', \Innomatic\Logging\Logger::ERROR);
        } else
            $this->mLog->logEvent('innomatic.wuicolorssetcomponent.wuicolorsset.doupdateaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty component file name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
}
