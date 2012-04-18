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
 * Shared component handler.
 */
class SharedComponent extends ApplicationComponent
{
    function SharedComponent ($rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
    }
    public static function getType ()
    {
        return 'shared';
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
            $file = $this->basedir . '/shared/' . $params['name'];
            if (@copy($file, InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/' . basename($file))) {
                @chmod(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/' . basename($file), 0644);
                $result = true;
            }
        } else
            $this->mLog->logEvent('innomatic.sharedcomponent.sharedcomponent.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty shared file name', Logger::ERROR);
        return $result;
    }
    function DoUninstallAction ($params)
    {
        $result = false;
        if (strlen($params['name'])) {
            if (@unlink(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/' . basename($params['name']))) {
                $result = true;
            }
        } else
            $this->mLog->logEvent('innomatic.sharedcomponent.sharedcomponent.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty shared file name', Logger::ERROR);
        return $result;
    }
    function DoUpdateAction ($params)
    {
        return $this->DoInstallAction($params);
    }
}
