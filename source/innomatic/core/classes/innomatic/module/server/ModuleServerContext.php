<?php
namespace Innomatic\Module\Server;

/**
 * Context where the Module server runs.
 *
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 * @copyright Copyright 2004-2014 Innoteam Srl
 * @since 5.1
 */
class ModuleServerContext extends \Innomatic\Util\Singleton
{
    protected $home;
    protected $config;

    /**
     * Class constructor.
     *
     * @since 5.1
     */
    public function ___construct()
    {
        $home = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome();

        if (substr($home, -1) != '/' and substr($home, -1) != '\\') {
            $home .= DIRECTORY_SEPARATOR;
        }

        $this->home = $home;
        $this->config = new \Innomatic\Module\Server\ModuleServerConfig($this->home.'core/conf/modules.ini');
    }

    /**
     * Gets server home directory.
     *
     * @since 5.1
     * @return string Server home directory.
     */
    public function getHome()
    {
        return $this->home;
    }

    /**
     * Returns server configuration object.
     *
     * @since 5.1
     * @return ModuleServerObject Configuration.
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Returns the list of the available Modules.
     *
     * @since 5.1
     * @return array
     */
    public function &getModuleList() {
        $list = array ();
        if ($dh = opendir($this->home.'core/modules')) {
            while (($file = readdir($dh)) !== false) {
                if ($file != '.' and $file != '..' and is_dir($this->home.'core/modules/'.$file)) {
                    $list[] = $file;
                }
            }
            closedir($dh);
        }
        return $list;
    }
}
