<?php
namespace Innomatic\Module;

/**
 * Value object class.
 *
 * The value object contains Module values in memory. All value object actions
 * take place in memory only and its content gets lost when the application
 * finishes or the object iself is destroyed (like when Module execution
 * finishes).
 *
 * Value object content can be saved and retrieved between different instances
 * manually or using the optional persistence storage system offered when
 * using ModulePersistentObject as Module parent class.
 *
 * Field names can only contains letters. Numbers, signs and other characters
 * are not allowed.
 *
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 * @copyright Copyright 2004-2014 Innoteam Srl
 * @since 5.1
 */
abstract class ModuleValueObject implements \Serializable
{
    /**
     * Returns value for a field.
     *
     * @access public
     * @since 5.1
     * @param string $key Field name.
     * @return mixed
     */
    public function getValue($key)
    {
        return isset($this->$key) ? $this->$key : false;
    }

    /**
     * Sets value for a field.
     *
     * @access public
     * @since 5.1
     * @param string $key Field name.
     * @param string $value Value to set.
     * @return void
     */
    public function setValue($key, $value)
    {
        if (isset ($this->$key)) {
            $this->$key = $value;
        }
    }

    /**
     * Sets value for many fields.
     *
     * @access public
     * @since 5.1
     * @param array $valueArray Associative array of values in key-value format.
     * @return void
     */
    public function setValueArray($valueArray)
    {
        foreach ($valueArray as $key => $value) {
            if (isset ($this->$key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Flushes in-memory value object content.
     *
     * @access public
     * @since 5.1
     * @return void
     */
    public function flush()
    {
        $obj = new \ReflectionObject($this);
        $properties = $obj->getProperties();

        foreach ($properties as $property) {
            $name = $property->getName();
            $this->$name = false;
        }
    }

    /**
     * Runtime adds a field to the value object.
     *
     * Useful when value object can be defined only at runtime, like when
     * value object structure is defined in a configuration file or is
     * dynamically determined.
     *
     * @access public
     * @since 5.1
     * @param string $field Name of the field to add.
     */
    public function addField($field)
    {
        $this->$field = false;
    }

    public function serialize()
    {
        return serialize($this);
    }

    public function unserialize($data)
    {
        return unserialize($data);
    }
}
