<?php
namespace Innomatic\Module;

require_once('innomatic/module/ModuleObject.php');
require_once('innomatic/module/ModuleConfig.php');
require_once('innomatic/module/persist/ModuleAccessObject.php');
require_once('innomatic/module/ModuleValueObject.php');
require_once('innomatic/dataaccess/DataAccessFactory.php');
require_once('innomatic/dataaccess/DataAccessSourceName.php');

/**
 * Module persistent object.
 *
 * A persistent Module object offers a persistence storage system.
 *
 * @author Alex Pagnoni <alex.pagnoni@innoteam.it>
 * @copyright Copyright 2004-2013 Innoteam Srl
 * @since 5.1
 */
abstract class ModulePersistentObject extends ModuleObject
{
    /**
     * DataAccessObject object.
     *
     * @access protected
     * @var ModuleAccessObject
     * @since 5.1
     */
    protected $dataAccessObject;
    /**
     * DataAccess object.
     *
     * @access protected
     * @var DataAccess
     * @since 5.1
     */
    protected $dataAccess;

    /**
     * Object constructor.
     *
     * @access public
     * @param ModuleConfig $config Module configuration object.
     * @since 5.1
     */
    public function __construct(ModuleConfig $config)
    {
        parent::__construct($config);

        // Data Access object
        /*
        $dasn = $this->config->getDASN();
        if (!$dasn instanceof DataAccessSourceName) {
            require_once('innomatic/module/ModuleException.php');
            throw new ModuleException('Missing DASN for persistent Module');
        }
        $this->dataAccess = DataAccessFactory::getDataAccess($dasn);
        */

        $this->dataAccess = InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess();

        // Data Access Object object
        $this->dataAccessObject = new ModuleAccessObject($this->dataAccess);
        $this->dataAccessObject->setValueObject($this->valueObject);
        $this->dataAccessObject->setConfig($this->config);
    }

    /**
     * Retrieves the value object from the storage.
     *
     * @access public
     * @param integer $id Id value of the object to be retrieved.
     * @since 5.1
     * @return boolean
     */
    public function moduleRetrieve($id)
    {
        $this->dataAccessObject->get($id);
        return true;
    }

    /**
     * Stores the value object in the storage.
     *
     * @access public
     * @since 5.1
     * @return boolean
     */
    public function moduleStore()
    {
        if ($this->valueObject->getValue($this->config->getIdField())) {
            $this->dataAccessObject->refresh();
        } else {
            $this->dataAccessObject->insert();
        }
        return true;
    }

    /**
     * Removes the value object from the storage and flushes Module's value object.
     *
     * If a particular id is specified, the value object with that id is erased
     * instead of the current Module one.
     *
     * @access public
     * @param integer $id Optional id for value object to be erased.
     * @since 5.1
     * @return boolean
     */
    public function moduleErase($id = false)
    {
        $this->dataAccessObject->delete($id);
        $this->valueObject->flush();
        return true;
    }

    /**
     * Finds a set of Modules whose value object matches the current one.
     *
     * @access public
     * @since 5.1
     * @return ModuleReadOnlyResulSet
     */
    public function moduleFind()
    {
        $sql = 'SELECT * FROM '.$this->config->getTable();
        $where = array ();
        $obj = new \ReflectionObject($this->valueObject);
        $properties = $obj->getProperties();

        foreach ($properties as $property) {
            $name = $property->getName();
            if ($this->valueObject->getValue($name) != '') {
                $where[] = "$name='".$this->valueObject->getValue($name)."'";
            }
        }

        if (count($where) > 0) {
            $sql .= ' WHERE '.implode(' AND ', $where);
        }

        $dar = $this->dataAccessObject->retrieve($sql);
        require_once('innomatic/module/persist/ModuleReadOnlyResultSet.php');
        return new ModuleReadOnlyResultSet($dar);
    }
}
