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
 * Catalog component handler.
 */
class CatalogComponent extends \Innomatic\Application\ApplicationComponent
{
    public function __construct($rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
    }
    public static function getType()
    {
        return 'catalog';
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
        if (! strlen($params['name'])) {
            $this->mLog->logEvent('innomatic.catalogcomponent.catalogcomponent.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty catalog name', \Innomatic\Logging\Logger::ERROR);
            return false;
        }
        $catalog = $this->basedir . '/core/locale/catalogs/' . $params['name'];
        // Check if the help directory exists and if not, create it.
        //
        if (! is_dir(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/locale/catalogs/')) {
            $old_umask = umask(0);
            @mkdir(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/locale/catalogs/', 0755);
            umask($old_umask);
        }
        return \Innomatic\Io\Filesystem\DirectoryUtils::dirCopy($catalog . '/', \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/locale/catalogs/' . basename($catalog) . '/');
    }
    public function doUninstallAction($params)
    {
        $result = false;
        if (! strlen($params['name'])) {
            $this->mLog->logEvent('innomatic.catalogcomponent.catalogcomponent.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty catalog name', \Innomatic\Logging\Logger::ERROR);
            return false;
        }
        return \Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/locale/catalogs/' . basename($params['name']));
    }
    public function doUpdateAction($params)
    {
        \Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/locale/catalogs/' . basename($params['name']));
        return $this->doInstallAction($params);
    }
}
