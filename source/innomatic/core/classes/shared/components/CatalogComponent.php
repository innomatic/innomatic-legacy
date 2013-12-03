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
 * Catalog component handler.
 */
class CatalogComponent extends ApplicationComponent
{
    public function CatalogComponent($rootda, $domainda, $appname, $name, $basedir)
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
            $this->mLog->logEvent('innomatic.catalogcomponent.catalogcomponent.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty catalog name', Logger::ERROR);
            return false;
        }
        $catalog = $this->basedir . '/core/locale/catalogs/' . $params['name'];
        // Check if the help directory exists and if not, create it.
        //
        if (! is_dir(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/locale/catalogs/')) {
            $old_umask = umask(0);
            @mkdir(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/locale/catalogs/', 0755);
            umask($old_umask);
        }
        require_once ('innomatic/io/filesystem/DirectoryUtils.php');
        return DirectoryUtils::dirCopy($catalog . '/', InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/locale/catalogs/' . basename($catalog) . '/');
    }
    public function doUninstallAction($params)
    {
        $result = false;
        if (! strlen($params['name'])) {
            $this->mLog->logEvent('innomatic.catalogcomponent.catalogcomponent.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty catalog name', Logger::ERROR);
            return false;
        }
        require_once ('innomatic/io/filesystem/DirectoryUtils.php');
        return DirectoryUtils::unlinkTree(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/locale/catalogs/' . basename($params['name']));
    }
    public function doUpdateAction($params)
    {
        require_once ('innomatic/io/filesystem/DirectoryUtils.php');
        DirectoryUtils::unlinkTree(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/locale/catalogs/' . basename($params['name']));
        return $this->DoInstallAction($params);
    }
}
