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
 * Webserviceshandler component handler.
 */
class WebserviceshandlerComponent extends ApplicationComponent
{
    function WebserviceshandlerComponent ($rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
    }
    public static function getType ()
    {
        return 'webserviceshandler';
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
        if (strlen($params['file'])) {
            $params['file'] = $this->basedir . '/core/classes/shared/webservices/' . $params['file'];
            if (@copy($params['file'], InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/classes/shared/webservices/' . basename($params['file']))) {
                @chmod(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/classes/shared/webservices/' . basename($params['file']), 0644);
                $result = true;
            } else
                $this->mLog->logEvent('shared.components.webserviceshandlercomponent.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to copy handler file', Logger::ERROR);
        } else
            $this->mLog->logEvent('shared.components.webserviceshandlercomponent.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty xmlrpc handler file name', Logger::ERROR);
        return $result;
    }
    function DoUninstallAction ($params)
    {
        $result = false;
        if (strlen($params['file'])) {
            if (@unlink(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/classes/shared/webservices/' . basename($params['file']))) {
                // :NOTE: 20020614 teg - Excessive delete.
                // It deletes all permissions, not only the ones referring to this handler.
                $this->rootda->execute('DELETE FROM webservices_permissions WHERE application=' . $this->rootda->formatText($this->appname));
                $result = true;
            } else
                $this->mLog->logEvent('shared.components.webserviceshandlercomponent.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to remove ' . InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/classes/shared/webservices/' . basename($params['file']), Logger::ERROR);
        } else
            $this->mLog->logEvent('shared.components.webserviceshandlercomponent.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty xmlrpc handler file name', Logger::ERROR);
        return $result;
    }
    function DoUpdateAction ($params)
    {
        return $this->DoInstallAction($params);
    }
}
