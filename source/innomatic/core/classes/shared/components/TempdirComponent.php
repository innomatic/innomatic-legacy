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
 * Tempdir component handler.
 */
class TempdirComponent extends \Innomatic\Application\ApplicationComponent
{
    public function __construct($rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
    }
    public static function getType()
    {
        return 'tempdir';
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
            $result = true;
            if (! file_exists(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/temp/' . $params['name']))
                $result = @mkdir(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/temp/' . $params['name'], 0755);
            if (! $result)
                $this->mLog->logEvent('innomatic.tempdircomponent.tempdircomponent.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to create temporary directory', \Innomatic\Logging\Logger::ERROR);
        } else
            $this->mLog->logEvent('innomatic.tempdircomponent.tempdircomponent.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty temporary directory name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
    public function doUninstallAction($params)
    {
        $result = false;
        if (strlen($params['name'])) {
            if (\Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/temp/' . $params['name'])) {
                $result = true;
            } else {
                $this->mLog->logEvent('innomatic.tempdircomponent.tempdircomponent.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to remove temporary directory', \Innomatic\Logging\Logger::ERROR);
            }
        } else
            $this->mLog->logEvent('innomatic.tempdircomponent.tempdircomponent.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty temporary directory file name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
    public function doUpdateAction($params)
    {
        return $this->doInstallAction($params);
    }
}
