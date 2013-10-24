<?php 

/**
 * Accesses Module server configuration.
 *
 * @author Alex Pagnoni <alex.pagnoni@innoteam.it>
 * @copyright Copyright 2004-2013 Innoteam S.r.l.
 * @since 5.1
 */
class ModuleServerConfig {
	private $cfg = array ();
	private $defaults;

    /**
     * Class constructor.
     *
     * @access public
     * @since 5.1
     * @param string $configFile Full path of configuration file.
     */
	public function __construct($configFile) {
		if (file_exists($configFile)) {
			$this->cfg = parse_ini_file($configFile);
			$this->defaults = false;
		} else {
			$this->defaults = true;
		}
	}

    /**
     * Retrieves the value for a configuration key.
     *
     * @access public
     * @since 5.1
     * @param string $key Configuration key name.
     * @return string Key value.
     */
	public function getKey($key) {
		return isset ($this->cfg[$key]) ? $this->cfg[$key] : false;
	}

    /**
     * Tells if defaults values should be used in place of configuration
     * file stored ones.
     *
     * @access public
     * @since 5.1
     * @return boolean
     */
	public function useDefaults() {
		return $this->defaults;
	}
}

?>