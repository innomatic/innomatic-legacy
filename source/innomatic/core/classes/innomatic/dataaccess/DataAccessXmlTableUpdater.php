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
 * @license    http://www.innomatic.io/license/ New BSD License
 * @link       http://www.innomatic.io
*/
namespace Innomatic\Dataaccess;

/**
 * @since 5.0.0 introduced
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
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
    /*! @var mDiffNewKeys array - Array of the new keys. */
    public $mDiffNewKeys = array();
    /*! @var mDiffOldKeys array - Array of the old keys. */
    public $mDiffOldKeys = array();
    /*! @var mParse boolean - true when the tables have been parsed. */
    public $mParsed = false;

    /*!
     @param rDb DataAccess class - Database handler.
     @param oldTable string - Full path of the old XSQL table file.
     @param newTable string - Full path of the new XSQL table file.
     */
    public function __construct(\Innomatic\Dataaccess\DataAccess $rDb, $oldTable, $newTable)
    {
        $this->mrDb = $rDb;
        if (file_exists($oldTable)) {
            $this->mOldTable = $oldTable;
            $this->mOldTableHandler = new DataAccessXmlTable($this->mrDb, DataAccessXmlTable::SQL_UPDATE_OLD);
            $this->mOldTableHandler->load_DefFile($this->mOldTable);
        } else {
            
            $log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();
            $log->logEvent('innomatic.db.xmldbupdater.xmlupdater', 'Old table file ('.$oldTable.') does not exists', \Innomatic\Logging\Logger::WARNING);
        }

        if (file_exists($newTable)) {
            $this->mNewTable = $newTable;
            $this->mNewTableHandler = new DataAccessXmlTable($this->mrDb, DataAccessXmlTable::SQL_UPDATE_NEW);
            $this->mNewTableHandler->load_DefFile($this->mNewTable);
        } else {
            
            $log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();
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

            // Check diff fields
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

            // Check diff keys
            if (is_array($this->mOldTableHandler->mKeysList)) {
                reset($this->mOldTableHandler->mKeysList);
                while (list (, $old_key) = each($this->mOldTableHandler->mKeysList)) {
                    if (!isset($this->mNewTableHandler->mKeysList[$old_key]))
                        $this->mDiffOldKeys[] = $this->mOldTableHandler->mKeys[$old_key];
                }
            }
            if (is_array($this->mNewTableHandler->mKeysList)) {
                reset($this->mNewTableHandler->mKeysList);
                while (list (, $new_key) = each($this->mNewTableHandler->mKeysList)) {
                    if (!isset($this->mOldTableHandler->mKeysList[$new_key]))
                        $this->mDiffNewKeys[$new_key] = $this->mNewTableHandler->mKeys[$new_key];
                }
            }

            $this->mParsed = $result = true;
        } else {
            
            $log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();
            $log->logEvent('innomatic.db.xmldbupdater.checkdiffs', 'Old and new table files not specified', \Innomatic\Logging\Logger::ERROR);
        }
        return $result;
    }

    /*!
     @discussion Applies the differences between the XSQL tables.
     @result true if the apply has been performed.
     */
    public function applyDiffs($params)
    {
        $result = $this->CheckDiffs();

        if ($result) {

            // Apply diff fields
            $old_columns = $this->getOldColumns();
            if (is_array($old_columns)) {
                while (list (, $column) = each($old_columns)) {
                    $upd_data['tablename'] = $params['name'];
                    $upd_data['column'] = $column;
                    $this->mrDb->RemoveColumn($upd_data);
                }
            }
            $new_columns = $this->getNewColumns();
            if (is_array($new_columns)) {
                while (list (, $column) = each($new_columns)) {
                    $upd_data['tablename'] = $params['name'];
                    $upd_data['columnformat'] = $column;
                    $this->mrDb->AddColumn($upd_data);
                }
            }

            // Apply diff keys
            $old_keys = $this->getOldKeys();
            if (is_array($old_keys)) {
                while (list (, $key) = each($old_keys)) {
                    $upd_data['tablename'] = $params['name'];
                    $upd_data['keyformat'] = $key;
                    $this->mrDb->RemoveKey($upd_data);
                }
            }
            $new_keys = $this->getNewKeys();
            if (is_array($new_keys)) {
                while (list (, $key) = each($new_keys)) {
                    $upd_data['tablename'] = $params['name'];
                    $upd_data['keyformat'] = $key;
                    $this->mrDb->AddKey($upd_data);
                }
            }
               
        } else {

            $log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();
            $log->logEvent('innomatic.db.xmldbupdater.applydiffs', 'Return error from checkDiffs', \Innomatic\Logging\Logger::ERROR);
            
        }

        return true;
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


    /*!
     @abstract Returns the old Keys, if any.
     @result An array of the old Keys.
     */
    public function getOldKeys()
    {
        $result = false;
        if ($this->mParsed) {
            $result = $this->mDiffOldKeys;
        }
        return $result;
    }

    /*!
     @abstract Returns the new Keys, if any.
     @result An array of the new Keys, with the column name in the key and the column definition in the value.
     */
    public function getNewKeys()
    {
        $result = false;
        if ($this->mParsed) {
            $result = $this->mDiffNewKeys;
        }
        return $result;
    }

}
