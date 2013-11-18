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
 * Wuivalidator component handler.
 */
class WuivalidatorComponent extends ApplicationComponent
{
    function WuivalidatorComponent ($rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
    }
    public static function getType ()
    {
        return 'wuivalidator';
    }
    public static function getPriority ()
    {
        return 40;
    }
    public static function getIsDomain ()
    {
        return false;
    }
    public static function getIsOverridable ()
    {
        return false;
    }
    function DoInstallAction ($params)
    {
        $result = false;
        if (strlen($params['file'])) {
            if (@copy($this->basedir . '/core/classes/shared/wui/validators/' . basename($params['file']), InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/classes/shared/wui/validators/' . basename($params['file']))) {
                @chmod(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/classes/shared/wui/validators/' . basename($params['file']), 0644);
                if ($this->rootda->execute('INSERT INTO wui_validators ' . 'VALUES (' . $this->rootda->formatText($params['name']) . ',' . $this->rootda->formatText($params['file']) . ')')) {
                    $result = true;
                } else
                    $this->mLog->logEvent('shared.components.wuivalidator.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to install component', Logger::ERROR);
            } else
                $this->mLog->logEvent('shared.components.wuivalidator.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to copy wui component file (' . $params['file'] . ') to its destination (' . InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/classes/shared/wui/validators/' . basename($params['file']) . ')', Logger::ERROR);
        } else
            $this->mLog->logEvent('shared.components.wuivalidator.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty component file name', Logger::ERROR);
        return $result;
    }
    function DoUninstallAction ($params)
    {
        $result = false;
        if (strlen($params['file'])) {
            if ($this->rootda->execute('DELETE FROM wui_validators ' . 'WHERE name=' . $this->rootda->formatText($params['name']))) {
                if (@unlink(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/classes/shared/wui/validators/' . basename($params['file']))) {
                    $result = true;
                } else
                    $this->mLog->logEvent('shared.components.wuivalidator.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to unlink component', Logger::ERROR);
            } else
                $this->mLog->logEvent('shared.components.wuivalidator.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to uninstall component', Logger::ERROR);
        } else
            $this->mLog->logEvent('shared.components.wuivalidator.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty component file name', Logger::ERROR);
        return $result;
    }
    function DoUpdateAction ($params)
    {
        $result = false;
        if (strlen($params['file'])) {
            if (@copy($this->basedir . '/core/classes/shared/wui/validators/' . basename($params['file']), InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/classes/shared/wui/validators/' . basename($params['file']))) {
                @chmod(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/classes/shared/wui/validators/' . basename($params['file']), 0644);
                $result = true;
            } else
                $this->mLog->logEvent('shared.components.wuivalidator.doupdateaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to copy wui component file (' . $params['file'] . ') to its destination (' . InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/classes/shared/wui/validators/' . basename($params['file']) . ')', Logger::ERROR);
        } else
            $this->mLog->logEvent('shared.components.wuivalidator.doupdateaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty component file name', Logger::ERROR);
        return $result;
    }
}
