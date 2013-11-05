<?php  

require_once('innomatic/module/ModuleConfig.php');

/**
 * XML based configuration parser.
 *
 * This class parses Module configuration stored in an XML file, that is usually
 * located in the setup/module.xml file.
 *
 * This class must be instanced using the ModuleLXmlConfig::getInstance() method.
 *
 * @author Alex Pagnoni <alex.pagnoni@innoteam.it>
 * @copyright Copyright 2004-2013 Innoteam Srl
 * @since 5.1
 */
class ModuleXmlConfig extends ModuleConfig {
    /**
     * Value object structure.
     *
     * @access protected
     * @var array
     * @since 5.1
     */
    protected $valueObjectDefinition;
    
    /**
     * Returns an instance of the class.
     *
     * This has been implemented so that the configuration file for a Module
     * stored in a Module server is parsed only once and not for each Module
     * request.
     *
     * @access public
     * @param string $configFile full path of Xml configuration file.
     * @since 5.1
     * @return ModuleXmlConfig
     */
    public static function getInstance($configFile) {
        static $configs = array ();
        if (!isset ($configs[$configFile])) {
            $configs[$configFile] = new ModuleXmlConfig($configFile);
        }
        return $configs[$configFile];
    }

    /**
     * Construct the object.
     *
     * @access public
     * @param string $configFile full path of Xml configuration file.
     * @since 5.1
     */
    public function __construct($configFile) {
        // Checks if the configuration file exists.
        if (!file_exists($configFile)) {
            require_once('innomatic/module/ModuleException.php');
            throw new ModuleException('Cannot find '.$configFile.' configuration file');
        }

        // Parses the configuration file.
        $cfg = simplexml_load_file($configFile);
        $this->name = "$cfg->name";
        $this->version = "$cfg->version";
        $this->fqcn = "$cfg->class";
        $this->dasn = "$cfg->dasn";
        $this->table = "$cfg->table";
        $this->idField = "$cfg->idfield";

        // Sets value object class.
        if (strlen($cfg->valueobject->voclass)) {
            $this->voClass = $cfg->valueobject->voclass;
        } elseif (count($cfg->valueobject->vofields)) {
            $this->voClass = 'innomatic/module/util/ModuleGenericValueObject';
        }

        if (isset($this->voClass)) {
          foreach ($cfg->valueobject->vofields->vofield as $field) {
            $this->voFields[] = "$field";
          }
        } else {
          $this->voClass = 'innomatic/module/util/ModuleEmptyValueObject';
        }
    }
}

?>