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
 * Extras component handler.
 */
class ExtrasComponent extends ApplicationComponent
{
    public function ExtrasComponent($rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
    }
    public static function getType()
    {
        return 'extras';
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
        if (strlen($params['name'])) {
            $file = $this->basedir . '/core/extras/' . $params['name'];
            if (is_dir($file)) {
                require_once ('innomatic/io/filesystem/DirectoryUtils.php');
                if (DirectoryUtils::dirCopy($file.'/', InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/applications/' . $this->appname . '/' .basename($file).'/')) {
                    $result = true;
                }
            } else {
                if (@copy($file, InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/applications/' . $this->appname . '/' .basename($file))) {
                    @chmod(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/applications/' . $this->appname . '/' . basename($file), 0644);
                    $result = true;
                }
            }
        } else
            $this->mLog->logEvent('innomatic.extrascomponent.extrascomponent.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty extras file name', Logger::ERROR);
        return $result;
    }
    public function DoUninstallAction($params)
    {
        $result = false;
        if (strlen($params['name'])) {
            if (is_dir(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/applications/' . $this->appname . '/' . basename($params['name']))) {
                require_once ('innomatic/io/filesystem/DirectoryUtils.php');
                DirectoryUtils::unlinkTree(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/applications/' . $this->appname . '/' . basename($params['name']));
                $result = true;
            } else {
                if (@unlink(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/applications/' . $this->appname . '/' . basename($params['name']))) {
                    $result = true;
                }
            }
        } else
            $this->mLog->logEvent('innomatic.extrascomponent.extrascomponent.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty extras file name', Logger::ERROR);
        return $result;
    }
    public function DoUpdateAction($params)
    {
        if (strlen($params['name'])) {
            if (is_dir(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/applications/' . $this->appname . '/' . basename($params['name']))) {
                require_once ('innomatic/io/filesystem/DirectoryUtils.php');
                DirectoryUtils::unlinkTree(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/applications/' . $this->appname . '/' . basename($params['name']));
                $result = true;
            } else {
                if (@unlink(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/applications/' . $this->appname . '/' . basename($params['name']))) {
                    $result = true;
                }
            }

            $file = $this->basedir . '/core/extras/' . $params['name'];
            if (is_dir($file)) {
                require_once ('innomatic/io/filesystem/DirectoryUtils.php');
                if (DirectoryUtils::dirCopy($file.'/', InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/applications/' . $this->appname . '/' .basename($file).'/')) {
                    $result = true;
                }
            } else {
                if (@copy($file, InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/applications/' . $this->appname . '/' . basename($file))) {
                    @chmod(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/applications/' . $this->appname . '/' . basename($file), 0644);
                    $result = true;
                }
            }
        } else
            $this->mLog->logEvent('innomatic.extrascomponent.extrascomponent.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty extras file name', Logger::ERROR);
        return $result;
    }
}
