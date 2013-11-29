<?php
/**
 * Innomatic
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2012 Innoteam Srl
 * @license    http://www.innomatic.org/license/   BSD License
 * @link       http://www.innomatic.org
 * @since      Class available since Release 5.0
*/

/*!
 @class DataAccessResult

 @abstract Class returned by Execute member of DataAccess class with the result records
 */
abstract class DataAccessResult
{
    /*! @var resultid int - Result number */
    protected $resultid = -1;
    /*! @var resultrows int - Number of rows in the record set */
    public $resultrows = -1;
    /*! @var resultfields int - Number of fields in a row */
    public $resultfields = -1;
    /*! @var currfields array - Array of the fields in the current row */
    public $currfields;
    /*! @var opened bool - False if the DataAccessResult has been flushed by DataAccessResult->free */
    protected $opened = TRUE;
    /*! @var currentrow int - Current row pointer */
    public $currentrow = -1;
    /*! @var eof bool - True if the row pointer is at the end of record set */
    public $eof = FALSE;
    /*! @var supp array - Array of the supported functions */
    public $supp = array();

    /*!
     @function DataAccessResult

     @abstract Class constructor

     @param resultid integer - DataAccess->Execute return value
     */
    public function __construct($resultid)
    {
        $this->resultid = $resultid;

        if ($this->resultid) {
            $this->init();
        } else {
            $this->resultrows = 0;
            $this->resultfields = 0;
        }

        if ($this->resultrows != 0 && $this->resultfields != 0 && $this->currentrow == -1) {
            $this->currentrow = 0;
            $this->eof = ($this->fetch() === FALSE);
        } else
            $this->eof = TRUE;
    }

    /*!
     @abstract Frees the record set from memory
     */
    public function free()
    {
        if ($this->opened) {
            if ( $this->doFree() ) {
                $this->opened = FALSE;
                return true;
            }
        }

        return false;
    }

    abstract protected function doFree();

    public function close()
    {
        return $this->free();
    }

    public function isOpened()
    {
        return $this->opened;
    }

    // ----------------------------------------------------
    // Records navigation methods
    // ----------------------------------------------------

    /*!
     @abstract Moves the record pointer to the first entry
     */
    public function moveFirst()
    {
        if ($this->opened) {
            if ($this->currentrow == 0) {
                return true;
            }
            return $this->move(0);
        } else {
            return false;
        }
    }

    /*!
     @abstract Moves the record pointer to the next entry, if any
     */
    public function moveNext()
    {
        if ($this->opened) {
            if ($this->resultrows != 0) {
                $this->currentrow++;
                if ($this->currentrow < $this->resultrows) {
                    if ($this->fetch()) {
                        return true;
                    }
                }
            }

            $this->eof = true;
            return false;
        } else {
            return false;
        }
    }

    /*!
     @abstract Moves the record pointer to an absolute row
     @param row integer - Row number
     */
    public function move($row = 0)
    {
        if ($this->opened) {
            // Checks if it is already positioned in the requested row
            //
            if ($row == $this->currentrow)
                return true;

            // Checks if it is asked to position beyond the number of rows
            //
            if ($row > $this->resultrows)
                if ($this->resultrows != -1)
                    $row = $this->resultrows - 1;

            if ($this->supp['seek']) {
                if ($this->seek($row)) {
                    $this->currentrow = $row;
                    if ($this->fetch()) {
                        $this->eof = FALSE;
                        return TRUE;
                    }
                } else
                    return FALSE;
            } else {
                if ($row < $this->currentrow)
                    return FALSE;

                while (!$this->eof && $this->currentrow < $row) {
                    $this->currentrow++;

                    if (!$this->fetch())
                        $this->eof = TRUE;
                }
                if ($this->eof)
                    return FALSE;
                return TRUE;
            }
            $this->currfields = null;
            $this->eof = TRUE;
            return FALSE;
        } else
            return FALSE;
    }

    /*!
     @abstract Moves the record pointer to the last entry
     */
    public function moveLast()
    {
        if ($this->opened) {
            if ($this->resultrows >= 0)
                return $this->move($this->resultrows - 1);

            while (!$this->eof)
                $this->movenext();
            return TRUE;
        } else
            return FALSE;
    }

    // ----------------------------------------------------
    // Data fetch methods
    // ----------------------------------------------------

    /*!
     @abstract Returns the number of rows in the record set
     */
    public function getNumberRows()
    {
        return $this->opened ? $this->resultrows : FALSE;
    }

    /*!
     @abstract Returns the number of a row fields
     */
    public function getNumberFields()
    {
        return $this->opened ? $this->resultfields : FALSE;
    }

    /*!
     @abstract Returns the current row
     */
    public function getCurrentRow()
    {
        return $this->opened ? $this->currentrow : FALSE;
    }

    /*!
     @abstract Returns the current row columns
     @param column string - Optional column name. Defaults to FALSE
     @result An array of the fields. If the column argument is given, only the field with that name is returned
     */
    public function getFields($column = FALSE)
    {
        if ($this->opened) {
            if ($column !== false and strlen($column))
                return $this->currfields[$column];
            else
                return $this->currfields;
        }

        return false;
    }

    abstract protected function init();

    abstract protected function fetch();

    abstract protected function seek($row);
}
