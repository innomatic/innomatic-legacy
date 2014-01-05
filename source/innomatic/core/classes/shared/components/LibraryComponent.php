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
 * @license    http://www.innomatic.org/license/   BSD License
 * @link       http://www.innomatic.org
 * @since      Class available since Release 5.0
 */
namespace Shared\Components;

/**
 * Library component handler.
 *
 * @copyright  1999-2014 Innoteam Srl
 * @license    http://www.innomatic.org/license/   BSD License
 * @link       http://www.innomatic.org
 * @since      Class available since Release 5.0
 * @deprecated Class deprecated in Release 5.0
 */
class LibraryComponent extends \Innomatic\Application\ApplicationComponent
{
    public function __construct($rootda, $domainda, $appname, $name, $basedir)
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
    public function doInstallAction($params)
    {
        $result = false;
        if (strlen($params['file'])) {
            $params['file'] = $this->basedir . '/core/lib/' . $params['file'];
            if (@copy($params['file'], \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/lib/' . basename($params['file']))) {
                @chmod(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/lib/' . basename($params['file']), 0644);
                $result = true;
            }
        } else
            $this->mLog->logEvent('innomatic.phpcomponent.phpcomponent.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty library file name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
    public function doUninstallAction($params)
    {
        $result = false;
        if (strlen($params['file'])) {
            if (@unlink(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/lib/' . basename($params['file']))) {
                $result = true;
            } else
                $this->mLog->logEvent('innomatic.phpcomponent.phpcomponent.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to remove ' . \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/lib/' . basename($params['file']), \Innomatic\Logging\Logger::ERROR);
        } else
            $this->mLog->logEvent('innomatic.phpcomponent.phpcomponent.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty library file name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
    public function doUpdateAction($params)
    {
        return $this->doInstallAction($params);
    }
}
