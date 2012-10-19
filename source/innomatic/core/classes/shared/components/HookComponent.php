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
require_once ('innomatic/process/Hook.php');
/**
 * Hook component handler.
 */
class HookComponent extends ApplicationComponent
{
    function HookComponent ($rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
    }
    public static function getType ()
    {
        return 'hook';
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
        if (strlen($params['name'])) {
            $hook = new Hook($this->rootda, $params['functionapplication'], $params['function']);
            if ($hook->Add($params['event'], $this->appname, $params['hookhandler'], $params['hookmethod']))
                $result = true;
            else
                $this->mLog->logEvent('innomatic.hookcomponent.hookcomponent.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to add hook', Logger::ERROR);
        } else
            $this->mLog->logEvent('innomatic.hookcomponent.hookcomponent.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty hook name', Logger::ERROR);
        return $result;
    }
    function DoUninstallAction ($params)
    {
        $result = false;
        if (strlen($params['name'])) {
            $xm = new Hook($this->rootda, $params['functionapplication'], $params['function']);
            if ($xm->Remove($params['event'], $this->appname, $params['hookhandler'], $params['hookmethod']))
                $result = true;
            else
                $this->mLog->logEvent('innomatic.hookcomponent.hookcomponent.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to remove hook', Logger::ERROR);
        } else
            $this->mLog->logEvent('innomatic.hookcomponent.hookcomponent.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty hook name', Logger::ERROR);
        return $result;
    }
    function DoUpdateAction ($params)
    {
        $result = false;
        if (strlen($params['name'])) {
            $xm = new Hook($this->rootda, $params['functionapplication'], $params['function']);
            if ($xm->Update($params['event'], $this->appname, $params['hookhandler'], $params['hookmethod']))
                $result = true;
            else
                $this->mLog->logEvent('innomatic.hookcomponent.hookcomponent.doupdateaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to update hook', Logger::ERROR);
        } else
            $this->mLog->logEvent('innomatic.hookcomponent.hookcomponent.doupdateaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty hook name', Logger::ERROR);
        return $result;
    }
}
