<?php
namespace Innomatic\Module\Server;

/**
 * Accesses Module server configuration.
 *
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 * @copyright Copyright 2004-2014 Innoteam Srl
 * @since 5.1
 */
class ModuleServerConfig
{
    private $cfg = array ();
    private $defaults;

    /**
     * Class constructor.
     *
     * @since 5.1
     * @param string $configFile Full path of configuration file.
     */
    public function __construct($configFile)
    {
        if (file_exists($configFile)) {
            $this->cfg = parse_ini_file($configFile, false, INI_SCANNER_RAW);
            $this->defaults = false;
        } else {
            $this->defaults = true;
        }
    }

    /**
     * Retrieves the value for a configuration key.
     *
     * @since 5.1
     * @param string $key Configuration key name.
     * @return string Key value.
     */
    public function getKey($key)
    {
        return isset ($this->cfg[$key]) ? $this->cfg[$key] : false;
    }

    /**
     * Tells if defaults values should be used in place of configuration
     * file stored ones.
     *
     * @since 5.1
     * @return boolean
     */
    public function useDefaults()
    {
        return $this->defaults;
    }
}
