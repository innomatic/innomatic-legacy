<?php
namespace Innomatic\Module;

/**
 * Base class for encapsulating business logic.
 *
 * An Module is a package for incapsulating business
 * logic in a well defined domain and application infrastructure. Modules aren't
 * useful nor working until they are deployed in a Module server; once deployed,
 * they can be locally and remotely accessed.
 *
 * The ModuleObject class is the Module entry point, the front class for accessing
 * and running business logic. Business logic code can be organized in standard
 * classes, but it must be exposed through this object only.
 *
 * Applications must access logic only through this object, and in most
 * situations this is the only way, like when accessing remote Modules.
 *
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 * @copyright Copyright 2004-2014 Innoteam Srl
 * @since 5.1
 */
abstract class ModuleObject implements \Serializable
{
    /**
     * Config object.
     * @var ModuleConfig
     * @since 5.1
     */
    protected $config;
    /**
     * Value object.
     * @var ModuleValueObject
     * @since 5.1
     */
    protected $valueObject;

    /**
     * Object constructor.
     *
     * Builds the object depending on the Module configuration, given to the object
     * itself as an instance of a ModuleConfig class.
     *
     * @since 5.1
     * @param ModuleConfig $config Configuration object.
     */
    public function __construct(\Innomatic\Module\ModuleConfig $config)
    {
        // Assigns the config and retrieves fully qualified name for value object.
        $this->config = $config;
        $vo_fqcn = $this->config->getValueObjectClass();
        if (!strlen($vo_fqcn)) {
            $vo_fqcn = '\Innomatic\Module\Util\ModuleEmptyValueObject';
        }

        // Imports value object.

        require_once($vo_fqcn.'.php');
        $vo_class = strpos($vo_fqcn, '/') ? substr($vo_fqcn, strrpos($vo_fqcn, '/') + 1) : $vo_fqcn;
        if (!class_exists($vo_class, true)) {
            throw new ModuleException('Value object class '.$vo_class.' does not exists');
        }

        // Instantiates the value object.
        $this->valueObject = new $vo_class ();

        // If there are user defined fields, adds them to the value object.
        // This is useful for value objects whose structure is stored in
        // configuration files or determined at runtime.
        $vo_fields = $this->config->getValueObjectFields();
        if (count($vo_fields)) {
            foreach ($vo_fields as $field) {
                $this->valueObject->addField($field);
            }
        }
    }

    /**
     * Flushes the value object.
     *
     * @since 5.1
     * @return void
     */
    public function moduleFlush()
    {
        $this->valueObject->flush();
    }

    /**
     * Returns the value object instance.
     *
     * @since 5.1
     * @return ValueObject
     */
    public function moduleGetVO()
    {
        return $this->valueObject;
    }

    /**
     * Sets the value object, overwriting the existing one.
     *
     * Setting a new value object is done at memory level, no action is done
     * at the persistence storage layer.
     *
     * @since 5.1
     * @return void
     */
    public function moduleSetVO(ModuleValueObject $valueObject)
    {
        $this->valueObject = $valueObject;
    }

    /**
     * Sets a value object as an array.
     *
     * Setting a value object as array means passing value object fields and
     * theirs value inside an associative array, instead of using a
     * ModuleValueObject class.
     *
     * This is useful when transferring a value object using remote calls.
     *
     * @since 5.1
     * @return boolean
     */
    public function moduleSetVA($valueArray)
    {
        $this->valueObject->setValueArray($valueArray);
        return true;
    }

    public function serialize()
    {
        return serialize($this);
    }

    public function unserialize($data)
    {
        return unserialize($data);
    }

    /**
     * Abstract function that is called when the Module is deployed.
     *
     * This can be used when some actions must be performed when the
     * Module is deployed, like preparing a database.
     *
     * @since 5.1
     * @return void
     */
    abstract public function deploy();

    /**
     * Abstract function that is called when the Module is redeployed.
     *
     * This can be used when some actions must be performed when the
     * Module is redeployed, like updating a database.
     *
     * @since 5.1
     * @return void
     */
    abstract public function redeploy();

    /**
     * Abstract function that is called when the Module is undeployed.
     *
     * This can be used when some actions must be performed when the
     * Module is undeployed, like removing a database.
     *
     * @since 5.1
     * @return void
     */
    abstract public function undeploy();
}
