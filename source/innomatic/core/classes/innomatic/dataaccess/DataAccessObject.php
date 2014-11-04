<?php
/**
 * Innomatic
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2014 Innomatic Company
 * @license    http://www.innomatic.io/license/ New BSD License
 * @link       http://www.innomatic.io
 */
namespace Innomatic\Dataaccess;

/**
 * This class implements the Data Access Object (DAO) pattern.
 *
 * @since 5.0.0
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 */
abstract class DataAccessObject
{
    /**
     * Data access object.
     *
     * @var \Innomatic\Dataaccess\DataAccess;
     * @deprecated 6.8.0 Replaced by $dataAccess.
     * @access protected
     */
    protected $_dataAccess;
    /**
     * Data access object.
     *
     * @var \Innomatic\Dataaccess\DataAccess
     * @access protected
     */
    protected $dataAccess;

    /**
     * Constructor.
     * The constructor needs a DataAccess object.
     * @since 1.0
     * @param DataAccess $dataAccess
     * @return void
     */
    public function __construct(\Innomatic\Dataaccess\DataAccess $dataAccess)
    {
        $this->dataAccess = $dataAccess;
        $this->_dataAccess = $this->dataAccess;
    }

    public function retrieve($query)
    {
        $result = $this->dataAccess->execute($query);
        if (!$this->dataAccess->isError()) {
            return $result;
        }
        return null;
    }

    public function update($query)
    {
        $this->dataAccess->execute($query);
        // :TODO: Alex Pagnoni - to be implemented
        // isError() is still to be implemented
        if (!$this->dataAccess->isError()) {
            return true;
        }
        return false;
    }

    /*
    abstract public function create(Object $object);

    abstract public function retrieve($queryString);

    abstract public function update(Object $object);

    abstract public function delete(Object $object);
    */

    public function close()
    {
        $this->dataAccess->close();
    }
}
