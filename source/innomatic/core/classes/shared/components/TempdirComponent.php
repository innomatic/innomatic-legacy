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
 * Tempdir component handler.
 */
class TempdirComponent extends ApplicationComponent
{
    function TempdirComponent ($rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
    }
    public static function getType ()
    {
        return 'tempdir';
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
            $result = true;
            if (! file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/temp/' . $params['name']))
                $result = @mkdir(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/temp/' . $params['name'], 0755);
            if (! $result)
                $this->mLog->logEvent('innomatic.tempdircomponent.tempdircomponent.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to create temporary directory', Logger::ERROR);
        } else
            $this->mLog->logEvent('innomatic.tempdircomponent.tempdircomponent.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty temporary directory name', Logger::ERROR);
        return $result;
    }
    function DoUninstallAction ($params)
    {
        $result = false;
        if (strlen($params['name'])) {
            require_once ('innomatic/io/filesystem/DirectoryUtils.php');
            if (DirectoryUtils::unlinkTree(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/temp/' . $params['name']))
                $result = true;
            else
                $this->mLog->logEvent('innomatic.tempdircomponent.tempdircomponent.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to remove temporary directory', Logger::ERROR);
        } else
            $this->mLog->logEvent('innomatic.tempdircomponent.tempdircomponent.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty temporary directory file name', Logger::ERROR);
        return $result;
    }
    function DoUpdateAction ($params)
    {
        return $this->DoInstallAction($params);
    }
}
