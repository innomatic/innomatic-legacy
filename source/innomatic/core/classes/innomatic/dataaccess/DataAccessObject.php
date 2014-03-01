<?php
/**
 * Innomatic
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2014 Innoteam Srl
 * @license    http://www.innomatic.org/license/   BSD License
 * @link       http://www.innomatic.org
 * @since      Class available since Release 5.0
*/
namespace Innomatic\Dataaccess;

/**
 * This class implements the Data Access Object (DAO) pattern.
 * @since 1.0
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 */
abstract class DataAccessObject
{
    protected $_dataAccess;

    /**
     * Constructor.
     * The constructor needs a DataAccess object.
     * @since 1.0
     * @access public
     * @param DataAccess $dataAccess
     * @return void
     */
    public function __construct(\Innomatic\Dataaccess\DataAccess $dataAccess)
    {
        $this->_dataAccess = $dataAccess;
    }

    public function retrieve($query)
    {
        $result = $this->_dataAccess->execute($query);
        if (!$this->_dataAccess->isError()) {
            return $result;
        }
        return null;
    }

    public function update($query)
    {
        $this->_dataAccess->execute($query);
        // :TODO: Alex Pagnoni - to be implemented
        // isError() is still to be implemented
        if (!$this->_dataAccess->isError()) {
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
        $this->_dataAccess->close();
    }
}
