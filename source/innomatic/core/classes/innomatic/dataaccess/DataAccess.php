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

require_once('innomatic/dataaccess/DataAccessResult.php');
require_once('innomatic/dataaccess/DataAccessException.php');

/*!
 @class DataAccess
 @abstract Database access abstraction
 */
abstract class DataAccess
{
    // Internal properties
    //
    protected $driver;
    protected $dbhandler = NULL;
    protected $opened = false;
    public $persistent = false;
    public $autocommit = true;
    protected $lastError;
    protected $dasn;
    private $innomatic;
    private $debug = false;

    // Format types
    //
    /*! @var fmtconcat string - SQL concatenation string */
    public $fmtconcat = '+';
    /*! @var fmtdate string - Date formatting string, in PHP syntax */
    public $fmtdate = "'Y-m-d'";
    /*! @var fmttime string - Time formatting string, in PHP syntax */
    public $fmttime = "'H:i:s'";
    /*! @var fmttimestamp string - Time stamp formatting string, in PHP syntax */
    public $fmttimestamp = "'Y-m-d H:i:s'";
    /*! @var fmttrue string - True value */
    public $fmttrue = '1';
    /*! @var fmtfalse string - False value */
    public $fmtfalse = '0';
    /*! @var fmtquote string - Text quote string */
    public $fmtquote = "\\'";

    // Supported features
    //
    public $supp = array();

    // ----------------------------------------------------
    // Internal methods
    // ----------------------------------------------------

    /*!
     @function DataAccess
     @abstract Class constructor
     @discussion It should be called through DataAccessFactory::getDataAccess() method
     @param params array - Database parameters
     */
    public function __construct(DataAccessSourceName $dasn)
    {
        $this->dasn = $dasn;
        require_once('innomatic/core/InnomaticContainer.php');
        $this->innomatic = InnomaticContainer::instance('innomaticcontainer');
        if ($this->innomatic->getConfig()->value('RootDatabaseDebug') == '1') {
            $this->debug = true;
        }
    }

    // ----------------------------------------------------
    // Database methods
    // ----------------------------------------------------

    /*!
     @function ListDatabases
     @abstract Lists all the databases in the database server
     @result Array of the databases or false if none
     */
    abstract public function listDatabases();

    // Lists all tables
    //
    abstract public function listTables();

    // List all columns of a table
    //
    abstract public function listColumns($table);

    // Creates a new database
    //
    abstract public function createDB($params);

    // Drops a database
    //
    abstract public function dropDB($params);

    // Opens connection to the database
    //
    public function connect()
    {
        $result = false;

        if ($this->opened == false) {
            if ($this->dasn->getOption('persistent') == '1') {
                $result = $this->openPersistentConnection();
            } else {
                $result = $this->openConnection();
            }

            if ($result != false) {
                $this->opened = true;
                $this->dbhandler = $result;
                if (isset($params['persistent']) and $params['persistent'] == true) {
                    $this->persistent = true;
                }
                $this->lastError = '';
            }
        } else {
            $this->lastError = 'Unable to connect to database '.$this->dbname;
            
            $this->log = new \Innomatic\Logging\Logger($this->dasn->getOption('logfile'));
            $this->log->logEvent('innomatic.dataaccess.connect', $this->lastError, \Innomatic\Logging\Logger::ERROR);
            throw new DataAccessException(DataAccessException::ERROR_CONNECT_FAILED);
        }


        return $result;
    }

    abstract protected function openConnection();

    abstract protected function openPersistentConnection();

    public function isConnected()
    {
        return $this->opened;
    }

    // Dumps an entire database
    //
    abstract public function dumpDB($params);

    // Closes connection to the database
    //
    public function close()
    {
        $result = false;

        if ($this->opened == true) {
            if ($this->closeConnection() == true) {
                $this->opened = false;
                $result = true;
                $this->lastError = '';
            } else {
                $this->lastError = 'Unable to close database';
                
                $this->log = new \Innomatic\Logging\Logger($this->dasn->getOption('logfile'));
                $this->log->logEvent('innomatic.dataaccess.close', $this->lastError, \Innomatic\Logging\Logger::ERROR);
            }
        } else {
            $this->lastError = 'Tried to close an already closed database';
            
            $this->log = new \Innomatic\Logging\Logger($this->dasn->getOption('logfile'));
            $this->log->logEvent('innomatic.dataaccess.close', $this->lastError, \Innomatic\Logging\Logger::ERROR);
            $result = true;
        }

        return $result;
    }

    abstract protected function closeConnection();

    // ----------------------------------------------------
    // Tables methods
    // ----------------------------------------------------

    // Creates a table
    //
    abstract public function createTable($params);

    // Drops a table
    //
    abstract public function dropTable($params);

    /*!
     @function AddColumn
     @abstract Adds a column to a table.
     @param params array - Array of paratemers.
     */
    abstract public function addColumn($params);

    /*!
     @function RemoveColumn
     @abstract Removes a column to a table.
     @param params array - Array of paratemers.
     */
    abstract public function removeColumn($params);

    // Alters a table
    //
    abstract public function alterTable($params);

    // ----------------------------------------------------
    // Sequences methods
    // ----------------------------------------------------

    // Creates a sequence
    //
    abstract public function createSequence($params);

    abstract public function getCreateSequenceQuery($params);

    // Drops a sequence
    //
    abstract public function dropSequence($params);

    abstract public function getDropSequenceQuery($params);

    // Gets current sequence value
    //
    abstract public function getSequenceValue($params);

    abstract public function getSequenceValueQuery($params);

    // Advances sequence to the next value and returns it
    //
    abstract public function getNextSequenceValue($params);

    abstract public function getNextSequenceValueQuery($params);

    // ----------------------------------------------------
    // Query methods
    // ----------------------------------------------------

    // Splits sql queries to single queries. This comes from PHPMyAdmin
    //
    protected function splitSQL($sql)
    {
        $sql = trim($sql);
        $sqlLen = strlen($sql);
        $char = '';
        $stringStart = '';
        $buffer = array();
        $ret = array();
        $inString = false;

        for ($i = 0; $i < $sqlLen; ++ $i) {
            $char = $sql[$i];

            // We are in a string, check for not escaped end of strings except for
            // backquotes that can't be escaped
            if ($inString) {
                for (;;) {
                    $i = strpos($sql, $stringStart, $i);
                    // No end of string found->add the current substring to the
                    // returned array
                    if (!$i) {
                        $ret[] = $sql;
                        return $ret;
                    }
                    // Backquotes or no backslashes before quotes: it's indeed the
                    // end of the string->exit the loop
                    else if ($stringStart == '`' || $sql[$i -1] != '\\') {
                        $stringStart = '';
                        $inString = false;
                        break;
                    }
                    // one or more Backslashes before the presumed end of string...
                    else {
                        // ... first checks for escaped backslashes
                        $j = 2;
                        $escapedBackslash = false;
                        while ($i - $j > 0 && $sql[$i - $j] == '\\') {
                            $escapedBackslash = !$escapedBackslash;
                            $j ++;
                        }
                        // ... if escaped backslashes: it's really the end of the
                        // string->exit the loop
                        if ($escapedBackslash) {
                            $stringStart = '';
                            $inString = false;
                            break;
                        }
                        // ... else loop
                        else {
                            $i ++;
                        }
                    } // end if...else if...else
                } // end for
            } // end if (in string)

            // We are not in a string, first check for delimiter...
            else
            if ($char == ';') {
                // if delimiter found, add the parsed part to the returned array
                $ret[] = substr($sql, 0, $i);
                $sql = ltrim(substr($sql, min($i +1, $sqlLen)));
                $sqlLen = strlen($sql);
                if ($sqlLen) {
                    $i = -1;
                } else {
                    // The submited statement(s) end(s) here
                    return $ret;
                }
            } // end else if (is delimiter)

            // ... then check for start of a string,...
            else
            if (($char == '"') || ($char == '\'') || ($char == '`')) {
                $inString = true;
                $stringStart = $char;
            } // end else if (is start of string)

            // ... for start of a comment (and remove this comment if found)...
            else
            if ($char == '#' || ($char == ' ' && $i > 1 && $sql[$i -2].$sql[$i -1] == '--')) {
                // starting position of the comment depends on the comment type
                $start_of_comment = (($sql[$i] == '#') ? $i : $i -2);
                // if no "\n" exits in the remaining string, checks for "\r"
                // (Mac eol style)
                $end_of_comment = (strpos(' '.$sql, "\012", $i +2)) ? strpos(' '.$sql, "\012", $i +2) : strpos(' '.$sql, "\015", $i +2);
                if (!$end_of_comment) {
                    // no eol found after '#', add the parsed part to the returned
                    // array and exit
                    $ret[] = trim(substr($sql, 0, $i -1));
                    return $ret;
                } else {
                    $sql = substr($sql, 0, $start_of_comment).ltrim(substr($sql, $end_of_comment));
                    $sqlLen = strlen($sql);
                    $i --;
                } // end if...else
            } // end else if (is comment)
        }

        // add any rest to the returned array
        if (!empty($sql) && ereg('[^[:space:]]+', $sql)) {
            $ret[] = $sql;
        }

        return $ret;
    }

    // Executes a query and returns a DataAccessResult
    //
    public function execute($query)
    {
        $result = false;
        if ($this->opened) {
            $pieces = $this->splitSQL($query);
            for ($i = 0; $i < count($pieces); $i ++) {
                if ($this->innomatic->getState() == InnomaticContainer::STATE_DEBUG) {
                    require_once('innomatic/debug/InnomaticDump.php');
                    $dump = InnomaticDump::instance('innomaticdump');
                    $dump->dataAccess['queries'][] = $pieces[$i];

                    $debugCounter = $this->innomatic->getDbLoadTimer()->AdvanceCounter();
                    $this->innomatic->getDbLoadTimer()->Start($debugCounter.': '.$pieces[$i]);

                    $resid = $this->doExecute($pieces[$i]);

                    $this->innomatic->getDbLoadTimer()->Stop($debugCounter.': '.$pieces[$i]);

                    if ($this->debug == true) {
                        
                        $this->log = new \Innomatic\Logging\Logger($this->dasn->getOption('logfile'));
                        $this->log->logEvent('innomatic.dataaccess.execute', 'Executed query '.$pieces[$i], \Innomatic\Logging\Logger::DEBUG);
                    }
                } else $resid = $this->doExecute($pieces[$i]);

                if ($resid == false) {
                    $this->lastError = 'Unable to execute query '.$pieces[$i];
                    
                    $this->log = new \Innomatic\Logging\Logger($this->dasn->getOption('logfile'));
                    $this->log->logEvent('innomatic.dataaccess.execute', $this->lastError, \Innomatic\Logging\Logger::ERROR);
                    $result = false;
                } elseif (($i == count($pieces) - 1) and ($resid != 1)) {
                    $rsname = $this->driver.'DataAccessResult';
                    $result = new $rsname($resid);
                    $this->lastError = '';
                } else {
                    $result = true;
                    $this->lastError = '';
                }
            }
        } else {
            $this->lastError = 'Database not connected';
            
            $this->log = new \Innomatic\Logging\Logger($this->dasn->getOption('logfile'));
            $this->log->logEvent('innomatic.dataaccess.execute', $this->lastError, \Innomatic\Logging\Logger::ERROR);
        }

        return $result;
    }

    abstract protected function doExecute($query);

    // Returns number of affected rows
    //
    public function getAffectedRowsCount()
    {
        $result = false;

        if ($this->supp['affrows'] != false) {
            $result = $this->doGetAffectedRowsCount();
        }

        return $result;
    }

    abstract protected function doGetAffectedRowsCount();

    // ----------------------------------------------------
    // Transaction mehods
    // ----------------------------------------------------

    // Sets autocommit mode
    //
    public function setTransactionAutocommit($autocommit)
    {
        $result = false;

        if ($this->supp['affrows'] != false and $this->autocommit != $autocommit) {
            $result = $this->doSetTransactionAutocommit();
            $this->autocommit = $autocommit;
        }

        return $result;
    }

    abstract protected function doSetTransactionAutocommit();

    // Commits a transaction
    //
    public function transactionCommit()
    {
        if ($this->supp['transactions'] != false and $this->opened and !$this->autocommit) {
            return $this->doTransactionCommit();
        } else {
            return false;
        }
    }

    abstract protected function doTransactionCommit();
    // Rolls back a transaction
    //
    public function transactionRollback()
    {
        if ($this->supp['transactions'] != false and $this->opened and !$this->autocommit) {
            return $this->doTransactionRollback();
        } else {
            return false;
        }
    }

    abstract protected function doTransactionRollback();

    // ----------------------------------------------------
    // Fields formatting methods
    // ----------------------------------------------------

    public function formatText($string)
    {
        //return "'".str_replace( "'", $this->fmtquote, $string )."'";
        if (get_magic_quotes_gpc() == 1)
        $string = stripslashes($string);
        return "'".str_replace("'", "''", $string)."'";
    }

    public function formatInteger($value)
    {
        if (!strlen($value)) {
            $value = 'NULL';
        }
        return $value;
    }

    public function formatTimestamp($timestamp)
    {
    }

    public function formatDate($date)
    {
        return date($this->fmtdate, $date);
    }

    public function formatTime($time)
    {
        return date($this->fmttime, $time);
    }

    public function formatUnixTimestamp($timestamp)
    {
        return date($this->fmttimestamp, $timestamp);
    }

    public function concatenate()
    {
        $first = true;
        $s = '';
        $arr = func_get_args();
        $concat = $this->fmtconcat;
        foreach ($arr as $a) {
            if ($first) {
                $s = (string) $a;
                $first = false;
            } else
            $s.= $concat.$a;
        }
        return $s;
    }

    public function getTimestampFromDateArray($date)
    {
        if (!isset($date['year']))
        $date['year'] = '';
        if (!isset($date['mon']))
        $date['mon'] = '';
        if (!isset($date['mday']))
        $date['mday'] = '';
        if (!isset($date['hours']))
        $date['hours'] = '';
        if (!isset($date['minutes']))
        $date['minutes'] = '';
        if (!isset($date['seconds']))
        $date['seconds'] = '';

        switch (strlen($date['year'])) {
            case '0' :
                $date['year'] = '2000';
                break;
            case '1' :
                $date['year'] = '200'.$date['year'];
                break;
            case '2' :
                $date['year'] = '20'.$date['year'];
                break;
            case '3' :
                $date['year'] = '2'.$date['year'];
                break;
        }

        $date['year'] = str_pad($date['year'], 4, '0', STR_PAD_LEFT);
        $date['mon'] = str_pad($date['mon'], 2, '0', STR_PAD_LEFT);
        $date['mday'] = str_pad($date['mday'], 2, '0', STR_PAD_LEFT);
        $date['hours'] = str_pad($date['hours'], 2, '0', STR_PAD_LEFT);
        $date['minutes'] = str_pad($date['minutes'], 2, '0', STR_PAD_LEFT);
        $date['seconds'] = str_pad($date['seconds'], 2, '0', STR_PAD_LEFT);

        return sprintf("%s-%s-%s %s:%s:%s", $date['year'], $date['mon'], $date['mday'], $date['hours'], $date['minutes'], $date['seconds']);
    }

    public function getDateArrayFromTimestamp($timestamp)
    {
        $timestamp = str_replace(',', '', $timestamp);
        $date_elements = explode(' ', $timestamp);
        list ($date['year'], $date['mon'], $date['mday']) = explode('-', $date_elements[0]);
        list ($date['hours'], $date['minutes'], $date['seconds']) = explode(':', $date_elements[1]);

        if (isset($date_elements[2])) {
            if ($date_elements[2] == 'PM' and $date['hours'] != 12)
            $date['hours'] += 12;
            if ($date_elements[2] == 'AM' and $date['hours'] == 12)
            $date['hours'] = 0;
        }

        return $date;
    }

    // ----------------------------------------------------
    // Data types abstraction
    // ----------------------------------------------------

    public function getIntegerFieldTypeDeclaration($name, &$field)
    {
        return ("$name INT". (IsSet($field['default']) ? ' DEFAULT '.$field['default'] : ''). (IsSet($field['notnull']) ? ' NOT NULL' : ''));
    }

    public function getTextFieldTypeDeclaration($name, &$field)
    {
        return ((IsSet($field['length']) ? "$name CHAR (".$field['length'].')' : "$name TEXT"). (IsSet($field['default']) ? ' DEFAULT '.$this->getTextFieldValue($field['default']) : ''). (IsSet($field['notnull']) ? ' NOT NULL' : ''));
    }

    public function getBooleanFieldTypeDeclaration($name, &$field)
    {
        return ("$name CHAR (1)". (IsSet($field['default']) ? ' DEFAULT '.$this->getBooleanFieldValue($field['default']) : ''). (IsSet($field['notnull']) ? ' NOT NULL' : ''));
    }

    public function getUnixTimestampFieldTypeDeclaration($name, &$field)
    {
        return ("$name INT ". (IsSet($field['default']) ? ' DEFAULT '.$this->getTimestampFieldValue($field['default']) : ''). (IsSet($field['notnull']) ? ' NOT NULL' : ''));
    }

    public function getDateFieldTypeDeclaration($name, &$field)
    {
        return ("$name CHAR (".strlen('YYYY-MM-DD').')'. (IsSet($field['default']) ? ' DEFAULT '.$this->getDateFieldValue($field['default']) : ''). (IsSet($field['notnull']) ? ' NOT NULL' : ''));
    }

    public function getTimestampFieldTypeDeclaration($name, &$field)
    {
        return ("$name CHAR (".strlen('YYYY-MM-DD HH:MM:SS').')'. (IsSet($field['default']) ? ' DEFAULT '.$this->getTimestampFieldValue($field['default']) : ''). (IsSet($field['notnull']) ? ' NOT NULL' : ''));
    }

    public function getTimeFieldTypeDeclaration($name, &$field)
    {
        return ("$name CHAR (".strlen('HH:MM:SS').')'. (IsSet($field['default']) ? ' DEFAULT '.$this->getTimeFieldValue($field['default']) : ''). (IsSet($field['notnull']) ? ' NOT NULL' : ''));
    }

    public function getFloatFieldTypeDeclaration($name, &$field)
    {
        return ("$name TEXT ". (IsSet($field['default']) ? ' DEFAULT '.$this->getFloatFieldValue($field['default']) : ''). (IsSet($field['notnull']) ? ' NOT NULL' : ''));
    }

    public function getDecimalFieldTypeDeclaration($name, &$field)
    {
        return ("$name TEXT ". (IsSet($field['default']) ? ' DEFAULT '.$this->getDecimalFieldValue($field['default']) : ''). (IsSet($field['notnull']) ? ' NOT NULL' : ''));
    }

    public function getIntegerFieldValue($value)
    {
        return (!strcmp($value, 'NULL') ? 'NULL' : $value);
    }

    public function getTextFieldValue($value)
    {
        return ("'$value'");
    }

    public function getBooleanFieldValue($value)
    {
        return (!strcmp($value, 'NULL') ? 'NULL' : ($value == 'true' ? "'".$this->fmttrue."'" : "'".$this->fmtfalse."'"));
    }

    public function getUnixTimestampFieldValue($value)
    {
        return (!strcmp($value, 'NULL') ? 'NULL' : "'$value'");
    }

    public function getDateFieldValue($value)
    {
        return (!strcmp($value, 'NULL') ? 'NULL' : "'$value'");
    }

    public function getTimestampFieldValue($value)
    {
        return (!strcmp($value, 'NULL') ? 'NULL' : "'$value'");
    }

    public function getTimeFieldValue($value)
    {
        return (!strcmp($value, 'NULL') ? 'NULL' : "'$value'");
    }

    public function getFloatFieldValue($value)
    {
        return (!strcmp($value, 'NULL') ? 'NULL' : "'$value'");
    }

    public function getDecimalFieldValue($value)
    {
        return (!strcmp($value, 'NULL') ? 'NULL' : "'$value'");
    }

    public function getFieldTypeDeclaration($name, &$field)
    {
        switch ($field['type']) {
            case 'integer' :
                return ($this->getIntegerFieldTypeDeclaration($name, $field));
            case 'text' :
                // break was intentionally omitted
            case 'string' :
                return ($this->getTextFieldTypeDeclaration($name, $field));
            case 'boolean' :
                return ($this->getBooleanFieldTypeDeclaration($name, $field));
            case 'date' :
                return ($this->getDateFieldTypeDeclaration($name, $field));
            case 'unixtimestamp' :
                return ($this->getUnixTimestampFieldTypeDeclaration($name, $field));
            case 'timestamp' :
                return ($this->getTimestampFieldTypeDeclaration($name, $field));
            case 'time' :
                return ($this->getTimeFieldTypeDeclaration($name, $field));
            case 'float' :
                return ($this->getFloatFieldTypeDeclaration($name, $field));
            case 'decimal' :
                return ($this->getDecimalFieldTypeDeclaration($name, $field));
        }
        return ('');
    }

    public function getFieldValue($type, $value)
    {
        switch ($type) {
            case 'integer' :
                return ($this->getIntegerFieldValue($value));
            case 'text' :
                // break was intentionally omitted
            case 'string' :
                return ($this->getTextFieldValue($value));
            case 'boolean' :
                return ($this->getBooleanFieldValue($value));
            case 'unixtimestamp' :
                return ($this->getUnixTimestampFieldValue($value));
            case 'date' :
                return ($this->getDateFieldValue($value));
            case 'timestamp' :
                return ($this->getTimestampFieldValue($value));
            case 'time' :
                return ($this->getTimeFieldValue($value));
            case 'float' :
                return ($this->getFloatFieldValue($value));
            case 'decimal' :
                return ($this->getDecimalFieldValue($value));
        }
        return ('');
    }

    public function getLastError()
    {
        return $this->lastError;
    }

    public function isError()
    {
        return strlen($this->lastError) > 0 ? true : false;
    }
}
