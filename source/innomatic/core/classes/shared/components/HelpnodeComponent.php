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
 * @license    http://www.innomatic.io/license/ New BSD License
 * @link       http://www.innomatic.io
 * @since      Class available since Release 5.0
 */
namespace Shared\Components;

/**
 * Help node component handler.
 */
class HelpnodeComponent extends \Innomatic\Application\ApplicationComponent
{
    public function __construct($rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
    }
    public static function getType()
    {
        return 'helpnode';
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
        if (strlen($params['file'])) {
            $params['file'] = $this->basedir . '/shared/help/' . $params['file'];
            // Check if the help directory exists and if not, create it.
            //
            if (! is_dir($this->container->getHome() . 'shared/help/')) {
                $old_umask = umask(0);
                @mkdir($this->container->getHome() . 'shared/help/', 0755);
                umask($old_umask);
            }
            if (\Innomatic\Io\Filesystem\DirectoryUtils::dirCopy($params['file'] . '/', $this->container->getHome() . 'shared/help/' . basename($params['file']) . '/')) {
                //@chmod( $this->container->getHome().'shared/help/'.basename( $params['file'] ), 0644 );
                $result = true;
            }
        } else
            $this->mLog->logEvent('innomatic.helpnodecomponent.helpnodecomponent.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty helpnode file name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
    public function doUninstallAction($params)
    {
        $result = false;
        if (strlen($params['file'])) {
            if (\Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree($this->container->getHome() . 'shared/help/' . basename($params['file']))) {
                $result = true;
            }
        } else
            $this->mLog->logEvent('innomatic.helpnodecomponent.helpnodecomponent.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty helpnode file name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
    public function doUpdateAction($params)
    {
        \Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree($this->container->getHome() . 'shared/help/' . basename($params['file']));
        return $this->doInstallAction($params);
    }
}
