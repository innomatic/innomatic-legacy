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
namespace Innomatic\Dataaccess\Drivers\Pgsql;

/*!
@class PgsqlDataAccess

@abstract DataAccess for PostgreSQL.
*/
class PgsqlDataAccess extends \Innomatic\Dataaccess\DataAccess
{
    public $driver = 'pgsql';
    public $fmtquote = "''";
    public $fmttrue = 't';
    public $fmtfalse = 'f';

    //var $suppautoinc      = true;
    //var $suppblob         = true;

    public $lastquery = false;

    public function __construct($params)
    {
        $this->support['affrows'] = true;
        $this->support['transactions'] = true;

        return parent::__construct($params);
    }

    public function listDatabases()
    {
        throw new DataAccessException(DataAccessException::ERROR_UNSUPPORTED);
    }

    public function listTables()
    {
        throw new DataAccessException(DataAccessException::ERROR_UNSUPPORTED);
    }

    public function listColumns($table)
    {
        throw new DataAccessException(DataAccessException::ERROR_UNSUPPORTED);
    }

    public function createDB($params)
    {
        $result = false;

        if (!empty($params['dbname']))
            $result = $this->standaloneQuery($params, 'CREATE DATABASE '.$params['dbname']);

        return $result;
    }

    protected function standaloneQuery($params, $query)
    {
        $result = false;

        if (strlen($params['dbhost']) > 0)
            $options = 'host='.$params['dbhost'];
        if (strlen($params['dbport']) > 0)
            $options.= ' port='.$params['dbport'];
        $options.= ' dbname=template1';
        if (strlen($params['dbuser']) > 0)
            $options.= ' user='.$params['dbuser'];
        if (strlen($params['dbpass']) > 0)
            $options.= ' password='.$params['dbpass'];

        $conn = @pg_connect($options);

        if ($conn != false) {
            $result = @pg_exec($conn, $query);
            @pg_close($conn);
        }

        return $result;
    }

    public function dropDB($params)
    {
        if (!empty($params['dbname'])) {
            @pg_close($this->dbhandler);
            return $this->standalonequery($params, 'DROP DATABASE '.$params['dbname']);
        }

        return false;
    }

    protected function openConnection()
    {
        $result = false;

        if (strlen($this->dasn->getHostSpec()) > 0)
            $options = 'host='.$this->dasn->getHostSpec();
        if (strlen($this->dasn->getPort()) > 0)
            $options.= ' port='.$this->dasn->getPort();
        if (strlen($this->dasn->getDatabase()) > 0)
            $options.= ' dbname='.$this->dasn->getDatabase();
        if (strlen($this->dasn->getUsername()) > 0)
            $options.= ' user='.$this->dasn->getUsername();
        if (strlen($this->dasn->getPassword()) > 0)
            $options.= ' password='.$this->dasn->getPassword();

        $result = @pg_connect($options);

        if ($result != false) {
            $this->dbhandler = $result;
            if (!$this->autocommit)
                $this->doExecute('BEGIN');
        }

        return $result;
    }

    protected function openPersistentConnection()
    {
        $result = false;

        if (strlen($this->dasn->getHostSpec()) > 0)
            $options = 'host='.$this->dasn->getHostSpec();
        if (strlen($this->dasn->getPort()) > 0)
            $options.= ' port='.$this->dasn->getPort();
        if (strlen($this->dasn->getDatabase()) > 0)
            $options.= ' dbname='.$this->dasn->getDatabase();
        if (strlen($this->dasn->getUsername()) > 0)
            $options.= ' user='.$this->dasn->getUsername();
        if (strlen($this->dasn->getPassword()) > 0)
            $options.= ' password='.$this->dasn->getPassword();

        $result = @pg_pconnect($options);

        if ($result != false) {
            $this->dbhandler = $result;
            if (!$this->autocommit)
                $this->doExecute('BEGIN');
        }

        return $result;
    }

    public function dumpDB($params)
    {
        throw new DataAccessException(DataAccessException::ERROR_UNSUPPORTED);
    }

    protected function closeConnection()
    {
        if (!$this->autocommit) {
            $this->doExecute('END');
        }

        return ($this->dbhandler ? @pg_close($this->dbhandler) : true);
    }

    public function createTable($params)
    {
        return false;
    }

    public function dropTable($params)
    {
        $result = false;

        if (!empty($params['tablename']) and $this->opened)
            $result = $this->doExecute('DROP TABLE '.$params['tablename']);

        return $result;
    }

    protected function doExecute($query)
    {
        $this->lastquery = @pg_exec($this->dbhandler, $query);
        return $this->lastquery;
    }

    protected function doGetAffectedRowsCount()
    {
        if ($this->lastquery != false) {
            return @pg_affected_rows($this->lastquery);
        }

        return false;
    }

    public function addColumn($params)
    {
        $result = false;

        if (!empty($params['tablename']) and !empty($params['columnformat']) and $this->opened)
            $result = $this->doExecute('ALTER TABLE '.$params['tablename'].' ADD COLUMN '.$params['columnformat']);

        return $result;
    }

    public function removeColumn($params)
    {
        if (!empty($params['tablename']) and !empty($params['column']) and $this->opened) {
            return $this->doExecute('ALTER TABLE '.$params['tablename'].' DROP COLUMN '.$params['column']);
        }
        return false;
    }

    public function alterTable($params)
    {
        throw new DataAccessException(DataAccessException::ERROR_UNSUPPORTED);
    }

    protected function doSetTransactionAutocommit($autocommit)
    {
        if ($this->opened) {
            return $this->doExecute($autocommit ? 'END' : 'BEGIN');
        }
        return false;
    }

    protected function doTansactionCommit()
    {
        return ($this->doExecute('COMMIT') && $this->doExecute('BEGIN'));
    }

    protected function doTransactionRollback()
    {
        return ($this->doExecute('ROLLBACK') && $this->doExecute('BEGIN'));
    }

    // ----------------------------------------------------
    // Sequences
    // ----------------------------------------------------

    public function createSequence($params)
    {
        if (!empty($params['name']) and !empty($params['start']) and $this->opened) {

            return $this->doExecute($this->getCreateSequenceQuery($params));
        } else
            return false;
    }

    public function getCreateSequenceQuery($params)
    {
        if (!empty($params['name']) and !empty($params['start'])) {
            return 'CREATE SEQUENCE '.$params['name'].' INCREMENT 1'. ($params['start'] < 1 ? ' MINVALUE '.$params['start'] : '').' START '.$params['start'].';';
        } else
            return false;
    }

    public function dropSequence($params)
    {
        if (!empty($params['name']) and $this->opened)
            return $this->doExecute($this->getDropSequenceQuery($params));
        else
            return false;
    }

    public function getDropSequenceQuery($params)
    {
        if (!empty($params['name'])) {
            return 'DROP SEQUENCE '.$params['name'].';';
        } else {
            return false;
        }
    }

    public function getSequenceValue($name)
    {
        if (!empty($name) and $this->opened) {
            $result = $this->doExecute($this->getSequenceValueQuery($name));
            return @pg_result($result, 0, 0);
        }
        return false;
    }

    public function getSequenceValueQuery($name)
    {
        if (!empty($name)) {
            return 'SELECT last_value from '.$name;
        }
        return false;
    }

    public function getNextSequenceValue($name)
    {
        if (!empty($name) and $this->opened) {
            $result = $this->doExecute($this->getNextSequenceValueQuery($name));
            return @pg_result($result, 0, 0);
        }
        return false;
    }

    public function getNextSequenceValueQuery($name)
    {
        if (!empty($name)) {
            return "SELECT NEXTVAL ( '".$name."' )";
        } else
            return false;
    }

    // ----------------------------------------------------
    // SQL fields abstraction
    // ----------------------------------------------------

    public function getTextFieldTypeDeclaration($name, &$field)
    {
        return ((IsSet($field['length']) ? "$name VARCHAR (".$field['length'].')' : $name.' TEXT'). (IsSet($field['default']) ? " DEFAULT '".$field['default']."'" : ''). (IsSet($field['notnull']) ? ' NOT NULL' : ''));
    }

    public function getTextFieldValue($value)
    {
        return ("'".AddSlashes($value)."'");
    }

    public function getDateFieldTypeDeclaration($name, &$field)
    {
        return ($name." DATE". (IsSet($field['default']) ? " DEFAULT '".$field['default']."'" : ''). (IsSet($field['notnull']) ? " NOT NULL" : ""));
    }

    public function getTimeFieldTypeDeclaration($name, &$field)
    {
        return ($name." TIME". (IsSet($field['default']) ? " DEFAULT '".$field['default']."'" : ""). (IsSet($field['notnull']) ? " NOT NULL" : ""));
    }

    public function getFloatFieldTypeDeclaration($name, &$field)
    {
        return ("$name FLOAT8 ". (IsSet($field['default']) ? " DEFAULT ".$this->getFloatFieldValue($field["default"]) : ""). (IsSet($field["notnull"]) ? " NOT NULL" : ""));
    }

    /*public function getDecimalFieldTypeDeclaration($name, &$field)
    {
        return( "$name INT8 ".( isset($field["default"] ) ? " DEFAULT ".$this->getDecimalFieldValue( $field["default"] ) : "" ).( isset($field["notnull"] ) ? " NOT NULL" : "" ) );
    }*/

    public function getDecimalFieldTypeDeclaration($name, &$field)
    {
        return ("$name DECIMAL ". (IsSet($field["length"]) ? " (".$field["length"].") " : ""). (IsSet($field["default"]) ? " DEFAULT ".$this->getDecimalFieldValue($field["default"]) : ""). (IsSet($field["notnull"]) ? " NOT NULL" : ""));
    }

    public function getFloatFieldValue($value)
    {
        return (!strcmp($value, "NULL") ? "NULL" : "$value");
    }

    /*public function getDecimalFieldValue($value)
    {
        return( !strcmp( $value,"NULL" ) ? "NULL" : strval( intval( $value * $this->decimal_factor ) ) );
    }*/
}
