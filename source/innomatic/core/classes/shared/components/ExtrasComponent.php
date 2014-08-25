<?php
/**
 * Innomatic
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2014 Innoteam Srl
 * @license    http://www.innomaticplatform.com/license/ New BSD License
 * @link       http://www.innomaticplatform.com
 * @since      Class available since Release 5.0
 */
namespace Shared\Components;

/**
 * Extras component handler.
 */
class ExtrasComponent extends \Innomatic\Application\ApplicationComponent
{
    public function __construct($rootda, $domainda, $appname, $name, $basedir)
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
    public function doInstallAction($params)
    {
        $result = false;
        if (strlen($params['name'])) {
            $file = $this->basedir . '/core/extras/' . $params['name'];
            if (is_dir($file)) {
                if (\Innomatic\Io\Filesystem\DirectoryUtils::dirCopy($file.'/', \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/applications/' . $this->appname . '/' .basename($file).'/')) {
                    $result = true;
                }
            } else {
                if (@copy($file, \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/applications/' . $this->appname . '/' .basename($file))) {
                    @chmod(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/applications/' . $this->appname . '/' . basename($file), 0644);
                    $result = true;
                }
            }
        } else
            $this->mLog->logEvent('innomatic.extrascomponent.extrascomponent.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty extras file name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
    public function doUninstallAction($params)
    {
        $result = false;
        if (strlen($params['name'])) {
            if (is_dir(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/applications/' . $this->appname . '/' . basename($params['name']))) {
                \Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/applications/' . $this->appname . '/' . basename($params['name']));
                $result = true;
            } else {
                if (@unlink(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/applications/' . $this->appname . '/' . basename($params['name']))) {
                    $result = true;
                }
            }
        } else
            $this->mLog->logEvent('innomatic.extrascomponent.extrascomponent.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty extras file name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
    public function doUpdateAction($params)
    {
        if (strlen($params['name'])) {
            if (is_dir(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/applications/' . $this->appname . '/' . basename($params['name']))) {
                \Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/applications/' . $this->appname . '/' . basename($params['name']));
                $result = true;
            } else {
                if (@unlink(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/applications/' . $this->appname . '/' . basename($params['name']))) {
                    $result = true;
                }
            }

            $file = $this->basedir . '/core/extras/' . $params['name'];
            if (is_dir($file)) {
                if (\Innomatic\Io\Filesystem\DirectoryUtils::dirCopy($file.'/', \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/applications/' . $this->appname . '/' .basename($file).'/')) {
                    $result = true;
                }
            } else {
                if (@copy($file, \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/applications/' . $this->appname . '/' . basename($file))) {
                    @chmod(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/applications/' . $this->appname . '/' . basename($file), 0644);
                    $result = true;
                }
            }
        } else
            $this->mLog->logEvent('innomatic.extrascomponent.extrascomponent.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty extras file name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
}
