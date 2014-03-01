<?php
namespace Innomatic\Module\Persist;

/**
 * DAO pattern based class for accessing persistence storage.
 *
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 * @copyright Copyright 2004-2014 Innoteam Srl
 * @since 5.1
 */
class ModuleAccessObject extends \Innomatic\Dataaccess\DataAccessObject
{
    /**
     * Value object.
     *
     * @var ModuleValueObject
     * @access protected
     * @since 5.1
     */
    protected $valueObject;
    /**
     * Module configuration.
     *
     * @var ModuleConfig
     * @access protected
     * @since 5.1
     */
    protected $config;

    /**
     * Sets Module configuration object.
     *
     * @access public
     * @param ModuleConfig $config Module configuration.
     * @return void
     * @since 5.1
     */
    public function setConfig(ModuleConfig $config)
    {
        $this->config = $config;
    }

    /**
     * Sets Module value object.
     *
     * @access public
     * @param ModuleValueObject $valueObject Module value object.
     * @return void
     * @since 5.1
     */
    public function setValueObject(\Innomatic\Module\ModuleValueObject $valueObject)
    {
        $this->valueObject = $valueObject;
    }

    /**
     * Retrieves a stored value object from the data access and sets it
     * in the valueObject member.
     *
     * @access public
     * @param integer $id Value object unique identifier.
     * @return void
     * @since 5.1
     */
    public function get($id)
    {
        $dar = $this->retrieve('SELECT * FROM '.$this->config->getTable().' WHERE '.$this->config->getIdField()."='".$id."'");

        if (!$dar instanceof \Innomatic\Dataaccess\DataAccessResult) {
            throw new \Innomatic\Module\ModuleException('Unable to retrieve value object for object with id '.$id);
        }

        if (!$dar->getNumberRows()) {
            throw new \Innomatic\Module\ModuleException('No object with '.$id.' exists');
        }

        $row = $dar->getFields();
        foreach ($row as $key => $value) {
            $this->valueObject->setValue($key, $value);
        }
    }

    /**
     * Inserts an object value in the storage.
     *
     * @access public
     * @return void
     * @since 5.1
     */
    public function insert()
    {
        $fields = array();
        $values = array();

        $sequence_value = $this->_dataAccess->getNextSequenceValue($this->config->getTable().'_'.$this->config->getIdField().'_seq');
        $this->valueObject->setValue($this->config->getIdField(), $sequence_value);

        $obj = new \ReflectionObject($this->valueObject);
        $properties = $obj->getProperties();
        foreach ($properties as $property) {
            $name = $property->getName();
            $fields[] = $name;
            // TODO !!! escape
            $values[] = "'".$this->valueObject->getValue($name)."'";
        }

        $sql = 'INSERT INTO '.$this->config->getTable().' ('.implode(',', $fields).') VALUES ('.implode(',', $values).')';
        $dar = $this->update($sql);
    }

    /**
     * Updates the stored value object.
     *
     * @access public
     * @return void
     * @since 5.1
     */
    public function refresh()
    {
        if (!$this->valueObject->getValue($this->config->getIdField())) {
            return;
        }

        $fields = array();
        $obj = new \ReflectionObject($this->valueObject);
        $properties = $obj->getProperties();
        foreach ($properties as $property) {
            $name = $property->getName();
            if ($name == $this->config->getIdField()) {
                continue;
            }
            // TODO !!! escape
            $fields[] = $name."='".$this->valueObject->getValue($name)."'";
        }

        $sql = 'UPDATE '.$this->config->getTable().' SET '.implode(',', $fields).' WHERE '.$this->config->getIdField()."='".$this->valueObject->getValue($this->config->getIdField())."'";
        $dar = $this->update($sql);
    }

    /**
     * Removes the value object from the storage.
     *
     * @access public
     * @return void
     * @since 5.1
     */
    public function delete($id = false)
    {
        $id = $id != false ? $id : $this->valueObject->getValue($this->config->getIdField());
        if (!$id) {
            return;
        }
        $dar = $this->update('DELETE FROM '.$this->config->getTable().' WHERE '.$this->config->getIdField().'='.$id);
    }
}
