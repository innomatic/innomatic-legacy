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

require_once('innomatic/dataaccess/DataAccess.php');

/*!
@class MysqlDataAccess

@abstract DataAccess for MySql.
*/
class MysqlDataAccess extends DataAccess
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
            $tmplink = @mysql_connect($params['dbhost'], $params['dbuser'], $params['dbpass']);
            if ($tmplink) {
                if (mysql_query('CREATE DATABASE '.$params['dbname'].'  DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci', $tmplink))
                    $result = true;
                else
                    $this->lastError = @mysql_error($tmplink);
            }
            //@mysql_close( $tmplink );
        }

        return $result;
    }

    public function dropDB($params)
    {
        if (!empty($params['dbname'])) {
            return @mysql_query('DROP DATABASE '.$params['dbname'], $this->dbhandler);
        }

        return false;
    }

    protected function openConnection()
    {
        $result = @mysql_connect($this->dasn->getHostSpec(), $this->dasn->getUsername(), $this->dasn->getPassword());

        if ($result != false) {
            $this->dbhandler = $result;
            if (!@mysql_select_db($this->dasn->getDatabase(), $this->dbhandler)) {
                $result = false;
            }
            mysql_query('SET CHARACTER SET utf8', $this->dbhandler);
        }

        return $result;
    }

    protected function openPersistentConnection()
    {
        $result = @mysql_pconnect($this->dasn->getHostSpec(), $this->dasn->getUsername(), $this->dasn->getPassword());

        if ($result != false) {
            $this->dbhandler = $result;
            if (!@mysql_select_db($this->dasn->getDatabase(), $this->dbhandler)) {
                $result = false;
            }
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
        //return @mysql_close( $this->dbhandler );
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

    protected function doExecute($query)
    {
        @mysql_select_db($this->dasn->getDatabase(), $this->dbhandler);
        $this->lastquery = @mysql_query($query, $this->dbhandler);
        //if ( defined( 'DEBUG' ) and !$this->lastquery ) echo mysql_error();
        if (@mysql_error($this->dbhandler)) {
            require_once('innomatic/logging/Logger.php');
        $this->log = new \Innomatic\Logging\Logger($this->dasn->getOption('logfile'));
            $this->log->logEvent('innomatic.mysqldataaccess.mysqldataaccess._query', 'Error: '.@mysql_error($this->dbhandler), \Innomatic\Logging\Logger::ERROR);
        }
        return $this->lastquery;
    }

    protected function doGetAffectedRowsCount()
    {
        if ($this->lastquery != false) {
            return @mysql_affected_rows($this->dbhandler);
        }
        return false;
    }

    public function addColumn($params)
    {
        $result = FALSE;

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
            return @mysql_result($result, 0, 0);
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
                $value = intval(mysql_insert_id($this->dbhandler));
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
