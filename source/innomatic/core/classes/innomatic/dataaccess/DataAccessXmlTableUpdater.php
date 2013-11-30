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

require_once('innomatic/dataaccess/DataAccessXmlTable.php');

/*!
 @class DataAccessXmlTableUpdater

 @abstract A class to obtain differences between two xsql table definition files.

 @discussion For now it only report new and old columns.
 */
class DataAccessXmlTableUpdater
{
    /*! @var mDb DataAccess class - Database handler. */
    public $mrDb;
    /*! @var mOldTable string - Old table file full path. */
    public $mOldTable;
    /*! @var mNewTable string - New table file full path. */
    public $mNewTable;
    /*! @var mOldTableHandler DataAccessXmlTable class - DataAccessXmlTable class for old table. */
    public $mOldTableHandler;
    /*! @var mNewTableHandler DataAccessXmlTable class - DataAccessXmlTable class for new table. */
    public $mNewTableHandler;
    /*! @var mDiffNewColumns array - Array of the new columns. The key contains the column name and the value contains the column definition. */
    public $mDiffNewColumns = array();
    /*! @var mDiffOldColumns array - Array of the old columns. */
    public $mDiffOldColumns = array();
    /*! @var mParse boolean - true when the tables have been parsed. */
    public $mParsed = false;

    /*!
     @param rDb DataAccess class - Database handler.
     @param oldTable string - Full path of the old XSQL table file.
     @param newTable string - Full path of the new XSQL table file.
     */
    public function DataAccessXmlTableUpdater(DataAccess $rDb, $oldTable, $newTable)
    {
        $this->mrDb = $rDb;
        if (file_exists($oldTable)) {
            $this->mOldTable = $oldTable;
            $this->mOldTableHandler = new DataAccessXmlTable($this->mrDb, DataAccessXmlTable::SQL_UPDATE_OLD);
            $this->mOldTableHandler->load_DefFile($this->mOldTable);
        } else {
            
            $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
            $log->logEvent('innomatic.db.xmldbupdater.xmlupdater', 'Old table file ('.$oldTable.') does not exists', \Innomatic\Logging\Logger::WARNING);
        }

        if (file_exists($newTable)) {
            $this->mNewTable = $newTable;
            $this->mNewTableHandler = new DataAccessXmlTable($this->mrDb, DataAccessXmlTable::SQL_UPDATE_NEW);
            $this->mNewTableHandler->load_DefFile($this->mNewTable);
        } else {
            
            $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
            $log->logEvent('innomatic.db.xmldbupdater.xmlupdater', 'New table file ('.$newTable.') does not exists', \Innomatic\Logging\Logger::WARNING);
        }
    }

    /*!
     @discussion Checks the differences between the XSQL tables.
     @result true if the check has been performed.
     */
    public function checkDiffs()
    {
        $result = false;

        if (strlen($this->mOldTable) and strlen($this->mNewTable)) {
            $this->mOldTableHandler->Parse($this->mOldTableHandler->mData);
            $this->mNewTableHandler->Parse($this->mNewTableHandler->mData);

            if (is_array($this->mOldTableHandler->mFieldsList)) {
                reset($this->mOldTableHandler->mFieldsList);
                while (list (, $old_column) = each($this->mOldTableHandler->mFieldsList)) {
                    if (!isset($this->mNewTableHandler->mFieldsList[$old_column]))
                        $this->mDiffOldColumns[] = $old_column;
                }
            }

            if (is_array($this->mNewTableHandler->mFieldsList)) {
                reset($this->mNewTableHandler->mFieldsList);
                while (list (, $new_column) = each($this->mNewTableHandler->mFieldsList)) {
                    if (!isset($this->mOldTableHandler->mFieldsList[$new_column]))
                        $this->mDiffNewColumns[$new_column] = $this->mNewTableHandler->mFields[$new_column];
                }
            }

            $this->mParsed = $result = true;
        } else {
            
            $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
            $log->logEvent('innomatic.db.xmldbupdater.checkdiffs', 'Old and new table files not specified', \Innomatic\Logging\Logger::ERROR);
        }
        return $result;
    }

    /*!
     @abstract Returns the old columns, if any.
     @result An array of the old columns.
     */
    public function getOldColumns()
    {
        $result = false;
        if ($this->mParsed) {
            $result = $this->mDiffOldColumns;
        }
        return $result;
    }

    /*!
     @abstract Returns the new columns, if any.
     @result An array of the new columns, with the column name in the key and the column definition in the value.
     */
    public function getNewColumns()
    {
        $result = false;
        if ($this->mParsed) {
            $result = $this->mDiffNewColumns;
        }
        return $result;
    }
}
