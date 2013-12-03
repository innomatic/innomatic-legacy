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
 * Library component handler.
 *
 * @copyright  1999-2012 Innoteam Srl
 * @license    http://www.innomatic.org/license/   BSD License
 * @link       http://www.innomatic.org
 * @since      Class available since Release 5.0
 * @deprecated Class deprecated in Release 5.0
 */
class LibraryComponent extends ApplicationComponent
{
    public function LibraryComponent($rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
    }
    public static function getType()
    {
        return 'library';
    }
    public static function getPriority()
    {
        return 110;
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
            $params['file'] = $this->basedir . '/core/lib/' . $params['file'];
            if (@copy($params['file'], InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/lib/' . basename($params['file']))) {
                @chmod(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/lib/' . basename($params['file']), 0644);
                $result = true;
            }
        } else
            $this->mLog->logEvent('innomatic.phpcomponent.phpcomponent.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty library file name', Logger::ERROR);
        return $result;
    }
    public function DoUninstallAction($params)
    {
        $result = false;
        if (strlen($params['file'])) {
            if (@unlink(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/lib/' . basename($params['file']))) {
                $result = true;
            } else
                $this->mLog->logEvent('innomatic.phpcomponent.phpcomponent.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to remove ' . InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/lib/' . basename($params['file']), Logger::ERROR);
        } else
            $this->mLog->logEvent('innomatic.phpcomponent.phpcomponent.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty library file name', Logger::ERROR);
        return $result;
    }
    public function DoUpdateAction($params)
    {
        return $this->DoInstallAction($params);
    }
}
