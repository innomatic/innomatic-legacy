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
namespace Innomatic\Dataaccess\Drivers\Mysql;

/**
 * @since 5.0.0 introduced
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 */
class MysqlDataAccess extends \Innomatic\Dataaccess\DataAccess
{
    public $driver = 'mysql';
    public $fmtquote = "''";
    public $fmttrue = 't';
    public $fmtfalse = 'f';

    //public $suppautoinc      = true;
    //public $suppblob         = true;

    private $lastquery = false;

    public function __construct($params)
    {
        $this->support['affrows'] = true;
        $this->support['transactions'] = false;
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

        if (!empty($params['dbname'])) {
            $tmplink = new \mysqli($params['dbhost'], $params['dbuser'], $params['dbpass']);
            if ($tmplink) {
                if ($tmplink->query('CREATE DATABASE '.$params['dbname'].'  DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci')) {
                    $result = true;
                } else {
                    $this->lastError = $tmplink->error;
                }
                $tmplink->close();
            }
        }

        return $result;
    }

    public function dropDB($params)
    {
        if (!empty($params['dbname'])) {
            return $this->dbhandler->query('DROP DATABASE '.$params['dbname']);
        }

        return false;
    }

    protected function openConnection()
    {
        $result = new \mysqli($this->dasn->getHostSpec(), $this->dasn->getUsername(), $this->dasn->getPassword(), $this->dasn->getDatabase());

        if (!$result->connect_error) {
            $this->dbhandler = $result;
            $this->dbhandler->set_charset('utf8');
        }

        return $result;
    }

    protected function openPersistentConnection()
    {
        $result = new \mysqli('p:'.$this->dasn->getHostSpec(), $this->dasn->getUsername(), $this->dasn->getPassword(), $this->dasn->getDatabase());

        if (!$result->connect_error) {
            $this->dbhandler = $result;
            $this->dbhandler->set_charset('utf8');
        }

        return $result;
    }

    public function dumpDB($params)
    {
        throw new DataAccessException(DataAccessException::ERROR_UNSUPPORTED);
    }

    protected function closeConnection()
    {
        return true;
        return $this->dbhandler->close();
    }

    public function createTable($params)
    {
        throw new DataAccessException(DataAccessException::ERROR_UNSUPPORTED);
    }

    public function dropTable($params)
    {
        $result = false;

        if (!empty($params['tablename']) and $this->opened)
            $result = $this->doExecute('DROP TABLE '.$params['tablename']);

        return $result;
    }

    public function truncateTable($params)
    {
        $result = false;

        if (!empty($params['tablename']) and $this->opened)
            $result = $this->doExecute('TRUNCATE '.$params['tablename']);

        return $result;
    }

    protected function doExecute($query)
    {
        $this->dbhandler->select_db($this->dasn->getDatabase());
        $this->lastquery = $this->dbhandler->query($query);
        if (strlen($this->dbhandler->error)) {
            $this->log = new \Innomatic\Logging\Logger($this->dasn->getOption('logfile'));
            $this->log->logEvent('innomatic.mysqldataaccess.mysqldataaccess._query', 'Error: '.$this->dbhandler->error, \Innomatic\Logging\Logger::ERROR);
        }
        return $this->lastquery;
    }

    protected function doGetAffectedRowsCount()
    {
        if ($this->lastquery != false) {
            return $this->dbhandler->affected_rows;
        }
        return false;
    }

    public function addColumn($params)
    {
        $result = false;

        if (!empty($params['tablename']) and !empty($params['columnformat']) and $this->opened){
            $result = $this->doExecute('ALTER TABLE '.$params['tablename'].' ADD COLUMN '.$params['columnformat']);
        }

        return $result;
    }

    public function removeColumn($params)
    {
        if (!empty($params['tablename']) and !empty($params['column']) and $this->opened) {
            return $this->doExecute('ALTER TABLE '.$params['tablename'].' DROP COLUMN '.$params['column']);
        }

        return false;
    }

    public function addKey($params)
    {
        $result = false;

        if (!empty($params['tablename']) and !empty($params['keyformat']) and $this->opened){
            $result = $this->doExecute('ALTER TABLE '.$params['tablename'].' ADD '.$params['keyformat']);
        }   
        return $result;
    }

    public function removeKey($params)
    {
        if (!empty($params['tablename']) and !empty($params['keyformat']) and $this->opened) {
            return $this->doExecute('ALTER TABLE '.$params['tablename'].' DROP '.$params['keyformat']);
        }
        return false;
    }

    public function alterTable($params)
    {
        throw new DataAccessException(DataAccessException::ERROR_UNSUPPORTED);
    }

    public function createSequence($params)
    {
        $result = false;

        if (!empty($params['name']) and !empty($params['start']) and $this->opened) {
            echo 'CREATE TABLE _sequence_'.$params['name'].' (sequence INT NOT NULL AUTO_INCREMENT, PRIMARY KEY (sequence))'."\n<br>";
            $result = $this->execute('CREATE TABLE _sequence_'.$params['name'].' (sequence INT NOT NULL AUTO_INCREMENT, PRIMARY KEY (sequence))');
            if ($result and ($params['start'] > 0)) {
                $this->execute('INSERT INTO _sequence_'.$params['name'].' (sequence) VALUES ('. ($params['start'] - 1).')');
            }
        }

        return $result;
    }

    public function getCreateSequenceQuery($params)
    {
        $result = false;

        if (!empty($params['name']) and !empty($params['start'])) {
            $query = 'CREATE TABLE _sequence_'.$params['name'].' (sequence INT NOT NULL AUTO_INCREMENT, PRIMARY KEY (sequence));';
            if ($params['start'] > 0) {
                $query.= 'INSERT INTO _sequence_'.$params['name'].' (sequence) VALUES ('. ($params['start'] - 1).');';
            }
            return $query;
        }

        return $result;
    }

    public function resetSequence($params)
    {
        if (!empty($params['name'])) {
            $ris_update = $this->doExecute('UPDATE _sequence_'.$params['name'].' SET sequence = 0');
            $ris_alter = $this->doExecute('ALTER TABLE _sequence_'.$params['name'].' AUTO_INCREMENT 1');
            return ($ris_update and $ris_alter);
        } else {
            return false;

        }
    }

    public function getResetSequenceQuery($params)
    {
        if (!empty($params['name'])) {
            $result = 'UPDATE _sequence_'.$params['name'].' SET sequence = 0; ';
            $result .= 'ALTER TABLE _sequence_'.$params['name'].' AUTO_INCREMENT 1;';
            return $result;
        } else {
            return false;

        }
    }

    public function dropSequence($params)
    {
        if (!empty($params['name']))
            return $this->doExecute('DROP TABLE _sequence_'.$params['name']);
        else
            return false;
    }

    public function getDropSequenceQuery($params)
    {
        if (!empty($params['name']))
            return 'DROP TABLE _sequence_'.$params['name'].';';
        else
            return false;
    }

    public function getSequenceValue($name)
    {
        if (!empty($name)) {
            $result = $this->doExecute('SELECT MAX(sequence) FROM _sequence_'.$name);
            $result->data_seek(0);
            $result->field_seek(0);
            return $result->fetch_field();
        } else
            return false;
    }

    public function getSequenceValueQuery($name)
    {
        $result = false;

        if (!empty($name)) {
            $result = 'SELECT MAX(sequence) FROM _sequence_'.$name.';';
        }

        return $result;
    }

    public function getNextSequenceValue($name)
    {
        if (!empty($name)) {
            if ($this->doExecute('INSERT INTO _sequence_'.$name.' (sequence) VALUES (NULL)')) {
                $value = intval($this->dbhandler->insert_id);
                $this->doExecute('DELETE FROM _sequence_'.$name.' WHERE sequence<'.$value);
            }
            return $value;
        } else {
            return false;
        }
    }

    public function getNextSequenceValueQuery($name)
    {
        // TODO verificare nel vecchio codice
    }

    protected function doSetTransactionAutocommit()
    {
        throw new DataAccessException(DataAccessException::ERROR_UNSUPPORTED);
    }

    protected function doTransactionCommit()
    {
        throw new DataAccessException(DataAccessException::ERROR_UNSUPPORTED);
    }

    protected function doTransactionRollback()
    {
        throw new DataAccessException(DataAccessException::ERROR_UNSUPPORTED);
    }
    // ----------------------------------------------------
    //
    // ----------------------------------------------------

    public function getTextFieldTypeDeclaration($name, &$field)
    {
        return (((IsSet($field['length']) and ($field['length'] <= 255)) ? "$name VARCHAR (".$field["length"].")" : "$name TEXT"). (IsSet($field["default"]) ? " DEFAULT '".$field["default"]."'" : ""). (IsSet($field["notnull"]) ? " NOT NULL" : ""));
    }

    public function getTextFieldValue($value)
    {
        return ("'".AddSlashes($value)."'");
    }

    public function getDateFieldTypeDeclaration($name, &$field)
    {
        return ($name." DATE". (IsSet($field["default"]) ? " DEFAULT '".$field["default"]."'" : ""). (IsSet($field["notnull"]) ? " NOT NULL" : ""));
    }

    public function getTimeFieldTypeDeclaration($name, &$field)
    {
        return ($name." TIME". (IsSet($field["default"]) ? " DEFAULT '".$field["default"]."'" : ""). (IsSet($field["notnull"]) ? " NOT NULL" : ""));
    }

    /**
     * The DATETIME type is used for values that contain both date and time parts. 
     * MySQL retrieves and displays DATETIME values in 'YYYY-MM-DD HH:MM:SS' format. 
     * The supported range is '1000-01-01 00:00:00' to '9999-12-31 23:59:59'.
     */
    public function getDatetimeFieldTypeDeclaration($name, &$field)
    {
        return ($name." DATETIME". (IsSet($field["default"]) ? " DEFAULT '".$field["default"]."'" : ""). (IsSet($field["notnull"]) ? " NOT NULL" : ""));
    }

    /**
     * The TIMESTAMP data type is used for values that contain both date and time parts. 
     * TIMESTAMP has a range of '1970-01-01 00:00:01' UTC to '2038-01-19 03:14:07' UTC.
     */
    public function getTimestampFieldTypeDeclaration($name, &$field)
    {
        return ($name." TIMESTAMP". (IsSet($field["default"]) ? " DEFAULT '".$field["default"]."'" : ""). (IsSet($field["notnull"]) ? " NOT NULL" : ""));
    }

    public function getFloatFieldTypeDeclaration($name, &$field)
    {
        return ("$name FLOAT8 ". (IsSet($field["default"]) ? " DEFAULT ".$this->getFloatFieldValue($field["default"]) : ""). (IsSet($field["notnull"]) ? " NOT NULL" : ""));
    }

    public function getDecimalFieldTypeDeclaration($name, &$field)
    {
        return ("$name DECIMAL ". (IsSet($field["length"]) ? " (".$field["length"].") " : ""). (IsSet($field["default"]) ? " DEFAULT ".$this->getDecimalFieldValue($field["default"]) : ""). (IsSet($field["notnull"]) ? " NOT NULL" : ""));
    }

    public function getFloatFieldValue($value)
    {
        return (!strcmp($value, "NULL") ? "NULL" : "$value");
    }

    public function getDecimalFieldValue($value)
    {
        return (!strcmp($value, "NULL") ? "NULL" : strval(intval($value * $this->decimal_factor)));
    }
}
