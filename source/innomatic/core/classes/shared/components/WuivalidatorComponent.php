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
 * Wuivalidator component handler.
 */
class WuivalidatorComponent extends \Innomatic\Application\ApplicationComponent
{
    public function __construct($rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
    }
    public static function getType()
    {
        return 'wuivalidator';
    }
    public static function getPriority()
    {
        return 40;
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
            if (@copy($this->basedir . '/core/classes/shared/wui/validators/' . basename($params['file']), $this->container->getHome() . 'core/classes/shared/wui/validators/' . basename($params['file']))) {
                @chmod($this->container->getHome() . 'core/classes/shared/wui/validators/' . basename($params['file']), 0644);
                if ($this->rootda->execute('INSERT INTO wui_validators ' . 'VALUES (' . $this->rootda->formatText($params['name']) . ',' . $this->rootda->formatText($params['file']) . ')')) {
                    $result = true;
                } else
                    $this->mLog->logEvent('shared.components.wuivalidator.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to install component', \Innomatic\Logging\Logger::ERROR);
            } else
                $this->mLog->logEvent('shared.components.wuivalidator.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to copy wui component file (' . $params['file'] . ') to its destination (' . $this->container->getHome() . 'core/classes/shared/wui/validators/' . basename($params['file']) . ')', \Innomatic\Logging\Logger::ERROR);
        } else
            $this->mLog->logEvent('shared.components.wuivalidator.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty component file name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
    public function doUninstallAction($params)
    {
        $result = false;
        if (strlen($params['file'])) {
            if ($this->rootda->execute('DELETE FROM wui_validators ' . 'WHERE name=' . $this->rootda->formatText($params['name']))) {
                if (@unlink($this->container->getHome() . 'core/classes/shared/wui/validators/' . basename($params['file']))) {
                    $result = true;
                } else
                    $this->mLog->logEvent('shared.components.wuivalidator.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to unlink component', \Innomatic\Logging\Logger::ERROR);
            } else
                $this->mLog->logEvent('shared.components.wuivalidator.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to uninstall component', \Innomatic\Logging\Logger::ERROR);
        } else
            $this->mLog->logEvent('shared.components.wuivalidator.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty component file name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
    public function doUpdateAction($params)
    {
        $result = false;
        if (strlen($params['file'])) {
            if (@copy($this->basedir . '/core/classes/shared/wui/validators/' . basename($params['file']), $this->container->getHome() . 'core/classes/shared/wui/validators/' . basename($params['file']))) {
                @chmod($this->container->getHome() . 'core/classes/shared/wui/validators/' . basename($params['file']), 0644);
                $result = true;
            } else
                $this->mLog->logEvent('shared.components.wuivalidator.doupdateaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to copy wui component file (' . $params['file'] . ') to its destination (' . $this->container->getHome() . 'core/classes/shared/wui/validators/' . basename($params['file']) . ')', \Innomatic\Logging\Logger::ERROR);
        } else
            $this->mLog->logEvent('shared.components.wuivalidator.doupdateaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty component file name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
}
