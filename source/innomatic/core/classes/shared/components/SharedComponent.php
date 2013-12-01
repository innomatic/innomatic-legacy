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
namespace Shared\Components;

/**
 * Shared component handler.
 */
class SharedComponent extends \Innomatic\Application\ApplicationComponent
{
    public function __construct($rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
    }
    public static function getType()
    {
        return 'shared';
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
            $file = $this->basedir . '/shared/' . $params['name'];
            if (is_dir($file)) {
                if (\Innomatic\Io\Filesystem\DirectoryUtils::dirCopy($file.'/', InnomaticContainer::instance('innomaticcontainer')->getHome().'shared/'.basename($file).'/')) {
                    $result = true;
                }
            } else {
                if (@copy($file, InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/' . basename($file))) {
                    @chmod(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/' . basename($file), 0644);
                    $result = true;
                }
            }
        } else
            $this->mLog->logEvent('innomatic.sharedcomponent.sharedcomponent.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty shared file name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
    public function DoUninstallAction($params)
    {
        $result = false;
        if (strlen($params['name'])) {
            if (is_dir(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/' . basename($params['name']))) {
                \Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/' . basename($params['name']));
                $result = true;
            } else {
                if (@unlink(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/' . basename($params['name']))) {
                    $result = true;
                }
            }
        } else
            $this->mLog->logEvent('innomatic.sharedcomponent.sharedcomponent.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty shared file name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
    public function DoUpdateAction($params)
    {
        if (strlen($params['name'])) {
            if (is_dir(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/' . basename($params['name']))) {
                \Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/' . basename($params['name']));
                $result = true;
            } else {
                if (@unlink(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/' . basename($params['name']))) {
                    $result = true;
                }
            }

            $file = $this->basedir . '/shared/' . $params['name'];
            if (is_dir($file)) {
                if (\Innomatic\Io\Filesystem\DirectoryUtils::dirCopy($file.'/', InnomaticContainer::instance('innomaticcontainer')->getHome().'shared/'.basename($file).'/')) {
                    $result = true;
                }
            } else {
                if (@copy($file, InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/' . basename($file))) {
                    @chmod(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'shared/' . basename($file), 0644);
                    $result = true;
                }
            }
        } else
            $this->mLog->logEvent('innomatic.sharedcomponent.sharedcomponent.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty shared file name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
}
