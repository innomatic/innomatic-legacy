<?php
namespace Innomatic\Module;

/**
 * This is the class for handling in-memory Module configuration.
 *
 * Configuration values have to be manually set.
 *
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 * @copyright Copyright 2004-2014 Innoteam Srl
 * @since 5.1
 */
class ModuleConfig
{
    /**
     * Module name.
     *
     * @access protected
     * @var string
     * @since 5.1
     */
    protected $name;
    /**
     * Module version.
     *
     * @access protected
     * @var string
     * @since 5.1
     */
    protected $version;
    /**
     * Module Fully Qualified Class Name.
     *
     * @access protected
     * @var string
     * @since 5.1
     */
    protected $fqcn;
    /**
     * Module optional value object Fully Qualified Class Name.
     *
     * @access protected
     * @var string
     * @since 5.1
     */
    protected $voClass;
    /**
     * Module optional value object fields list.
     *
     * @access protected
     * @var array
     * @since 5.1
     */
    protected $voFields = array();
    /**
     * Module optional Data Access Source Name.
     *
     * @access protected
     * @var DataAccessSourceName
     * @since 5.1
     */
    protected $dasn;
    /**
     * Module optional table name.
     *
     * @access protected
     * @var string
     * @since 5.1
     */
    protected $table;
    /**
     * Module optional id field.
     *
     * @access protected
     * @var string
     * @since 5.1
     */
    protected $idField;

    /**
     * Object constructor.
     *
     * @param string $name Module name.
     * @param string $version Module version.
     * @param string $fqcn Module Fully Qualified Class Name.
     * @param string $voClass Module optional value object Fully Qualified Class Name.
     * @param DataAccessSourceName $dasn Module optional Data Access Source Name.
     * @param string $table Module optional value table name.
     * @param string $idField Module optional id field name.
     * @access public
     * @since 5.1
     */
    public function __construct($name, $version, $fqcn, $voClass = '', $dasn = '', $table = '', $idField = '')
    {
        $this->name = $name;
        $this->version = $version;
        $this->fqcn = $fqcn;
        $this->voClass = $voClass;
        if ($dasn instanceof \Innomatic\Dataaccess\DataAccessSourceName) {
            $this->dasn = $dasn;
        }
        $this->table = $table;
        $this->idField = $idField;
    }

    /**
     * Sets Module name.
     *
     * @access public
     * @param string $name Module name.
     * @since 5.1
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Gets Module name.
     *
     * @access public
     * @since 5.1
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets Module version.
     *
     * @access public
     * @param string $version Module version.
     * @since 5.1
     * @return void
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * Gets Module version.
     *
     * @access public
     * @since 5.1
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Sets Module Fully Qualified Class Name.
     *
     * @access public
     * @param string $fqcn Module Fully Qualified Class Name.
     * @since 5.1
     * @return void
     */
    public function setFQCN($fqcn)
    {
        $this->fqcn = $fqcn;
    }

    /**
     * Gets Module Fully Qualified Class Name.
     *
     * @access public
     * @since 5.1
     * @return string
     */
    public function getFQCN()
    {
        return $this->fqcn;
    }

    /**
     * Sets Module optional Data Access Source Name.
     *
     * @access public
     * @param DataAccessSourceName $dasn Module optional Data Access Source Name.
     * @since 5.1
     * @return void
     */
    public function setDASN(\Innomatic\Dataaccess\DataAccessSourceName $dasn)
    {
        $this->dasn = $dasn;
    }

    /**
     * Gets Module optional Data Access Source Name.
     *
     * @access public
     * @since 5.1
     * @return DataAccessSourceName
     */
    public function getDASN()
    {
        return $this->dasn;
    }

    /**
     * Sets Module optional table name.
     *
     * @access public
     * @param string $table Module optional table name.
     * @since 5.1
     * @return void
     */
    public function setTable($table)
    {
        $this->table = $table;
    }

    /**
     * Gets Module optional table name.
     *
     * @access public
     * @since 5.1
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Sets Module optional value object Fully Qualified Class Name.
     *
     * @access public
     * @param string $class Module value object Fully Qualified Class Name.
     * @since 5.1
     * @return void
     */
    public function setValueObjectClass($class)
    {
        $this->voClass = $class;
    }

    /**
     * Gets Module optional value object Fully Qualified Class Name.
     *
     * @access public
     * @since 5.1
     * @return string
     */
    public function getValueObjectClass()
    {
        return $this->voClass;
    }

    /**
     * Sets Module optional value object fields.
     *
     * @access public
     * @param string $fields Module optional value object fields.
     * @since 5.1
     * @return void
     */
    public function setValueObjectFields($fields)
    {
        $this->voFields = $fields;
    }

    /**
     * Gets Module optional value object fields.
     *
     * @access public
     * @since 5.1
     * @return string
     */
    public function getValueObjectFields()
    {
        return $this->voFields;
    }

    /**
     * Sets Module optional id field.
     *
     * @access public
     * @param string $idField Module optional id field.
     * @since 5.1
     * @return void
     */
    public function setIdField($idField)
    {
        $this->idField = $idField;
    }

    /**
     * Gets Module optional id field.
     *
     * @access public
     * @since 5.1
     * @return string
     */
    public function getIdField()
    {
        return $this->idField;
    }
}
