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
 * Help node component handler.
 */
class HelpnodeComponent extends ApplicationComponent
{
    function HelpnodeComponent (&$rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
    }
    public static function getType ()
    {
        return 'helpnode';
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
            $params['file'] = $this->basedir . '/shared/help/' . $params['file'];
            // Check if the help directory exists and if not, create it.
            //
            if (! is_dir(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/help/')) {
                $old_umask = umask(0);
                @mkdir(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/help/', 0755);
                umask($old_umask);
            }
            if (DirectoryUtils::dirCopy($params['file'] . '/', InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/help/' . basename($params['file']) . '/')) {
                //@chmod( InnomaticContainer::instance('innomaticcontainer')->getHome().'shared/help/'.basename( $params['file'] ), 0644 );
                $result = true;
            }
        } else
            $this->mLog->logEvent('innomatic.helpnodecomponent.helpnodecomponent.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty helpnode file name', Logger::ERROR);
        return $result;
    }
    function DoUninstallAction ($params)
    {
        $result = false;
        if (strlen($params['file'])) {
            if (DirectoryUtils::unlinkTree(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/help/' . basename($params['file']))) {
                $result = true;
            }
        } else
            $this->mLog->logEvent('innomatic.helpnodecomponent.helpnodecomponent.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty helpnode file name', Logger::ERROR);
        return $result;
    }
    function DoUpdateAction ($params)
    {
        DirectoryUtils::unlinkTree(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/help/' . basename($params['file']));
        return $this->DoInstallAction($params);
    }
}