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
 * Extras component handler.
 */
class ExtrasComponent extends ApplicationComponent
{
    function ExtrasComponent ($rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
    }
    public static function getType ()
    {
        return 'extras';
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
    function DoInstallAction ($params)
    {
        $result = false;
        if (strlen($params['name'])) {
            $params['name'] = $this->basedir . '/core/extras/' . $params['name'];
            if (@copy($params['name'], InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/applications/' . $this->appname . '/' . basename($params['name']))) {
                @chmod(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/applications/' . $this->appname . '/' . basename($params['name']), 0644);
                $result = true;
            }
        } else
            $this->mLog->logEvent('innomatic.extrascomponent.extrascomponent.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty extras file name', Logger::ERROR);
        return $result;
    }
    function DoUninstallAction ($params)
    {
        $result = false;
        if (strlen($params['name'])) {
            if (@unlink(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/applications/' . $this->appname . '/' . basename($params['name']))) {
                $result = true;
            } else
                $this->mLog->logEvent('innomatic.extrascomponent.extrascomponent.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to remove ' . InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/applications/' . $this->appname . '/' . basename($params['name']), Logger::ERROR);
        } else
            $this->mLog->logEvent('innomatic.extrascomponent.extrascomponent.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty extras file name', Logger::ERROR);
        return $result;
    }
    function DoUpdateAction ($params)
    {
        return $this->DoInstallAction($params);
    }
}
