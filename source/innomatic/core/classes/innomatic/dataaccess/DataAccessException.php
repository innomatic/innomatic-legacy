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
*/
namespace Innomatic\Dataaccess;

/**
 * @since 5.0.0 introduced
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 */
class DataAccessException extends \RuntimeException
{
    private $_errcode;
    const OK = 1;
    const ERROR = -1;
    const ERROR_SYNTAX = -2;
    const ERROR_CONSTRAINT = -3;
    const ERROR_NOT_FOUND = -4;
    const ERROR_ALREADY_EXISTS = -5;
    const ERROR_UNSUPPORTED = -6;
    const ERROR_MISMATCH = -7;
    const ERROR_INVALID = -8;
    const ERROR_NOT_CAPABLE = -9;
    const ERROR_TRUNCATED = -10;
    const ERROR_INVALID_NUMBER = -11;
    const ERROR_INVALID_DATE = -12;
    const ERROR_DIVZERO = -13;
    const ERROR_NODBSELECTED = -14;
    const ERROR_CANNOT_CREATE = -15;
    const ERROR_CANNOT_DELETE = -16;
    const ERROR_CANNOT_DROP = -17;
    const ERROR_NOSUCHTABLE = -18;
    const ERROR_NOSUCHFIELD = -19;
    const ERROR_NEED_MORE_DATA = -20;
    const ERROR_NOT_LOCKED = -21;
    const ERROR_VALUE_COUNT_ON_ROW = -22;
    const ERROR_INVALID_DASN = -23;
    const ERROR_CONNECT_FAILED = -24;
    const ERROR_EXTENSION_NOT_FOUND = -25;
    const ERROR_ACCESS_VIOLATION = -26;
    const ERROR_NOSUCHDB = -27;
    const ERROR_CONSTRAINT_NOT_NULL = -29;
    const ERROR_NOSUCHCOLUMN = -30;
    const ERROR_NOSUCHFOREIGNKEY = -31;
    const ERROR_NOSUCHINDEX = -32;

    public function __construct($code)
    {
        $this->_errcode = $code;
        parent::__construct($this->getErrorMessage($code));
    }

    public function __toString()
    {
        return $this->getMessage();
    }

    public function getErrorCode()
    {
        return $this->_errcode;
    }

    public function getErrorMessage()
    {
        $errorMessages = array(
        self::ERROR => 'unknown error',
        self::ERROR_ALREADY_EXISTS => 'already exists',
        self::ERROR_CANNOT_CREATE => 'can not create',
        self::ERROR_CANNOT_DELETE => 'can not delete',
        self::ERROR_CANNOT_DROP => 'can not drop',
        self::ERROR_CONSTRAINT => 'constraint violation',
        self::ERROR_CONSTRAINT_NOT_NULL => 'null value violates not-null '
        . 'constraint',
        self::ERROR_DIVZERO => 'division by zero',
        self::ERROR_INVALID => 'invalid',
        self::ERROR_INVALID_DATE => 'invalid date or time',
        self::ERROR_INVALID_NUMBER => 'invalid number',
        self::ERROR_MISMATCH => 'mismatch',
        self::ERROR_NODBSELECTED => 'no database selected',
        self::ERROR_NOSUCHFIELD => 'no such field',
        self::ERROR_NOSUCHTABLE => 'no such table',
        self::ERROR_NOT_CAPABLE => 'DB backend not capable',
        self::ERROR_NOT_FOUND => 'not found',
        self::ERROR_NOT_LOCKED => 'not locked',
        self::ERROR_SYNTAX => 'syntax error',
        self::ERROR_UNSUPPORTED => 'not supported',
        self::ERROR_VALUE_COUNT_ON_ROW => 'value count on row',
        self::ERROR_INVALID_DASN => 'invalid DASN',
        self::ERROR_CONNECT_FAILED => 'connect failed',
        self::OK => 'no error',
        self::ERROR_NEED_MORE_DATA => 'insufficient data supplied',
        self::ERROR_EXTENSION_NOT_FOUND => 'extension not found',
        self::ERROR_NOSUCHDB => 'no such database',
        self::ERROR_ACCESS_VIOLATION => 'insufficient permissions',
        self::ERROR_TRUNCATED => 'truncated',
        self::ERROR_NOSUCHCOLUMN => 'no such column',
        self::ERROR_NOSUCHFOREIGNKEY => 'no such foreign key',
        self::ERROR_NOSUCHINDEX => 'nosuchindex'
        );

        return isset(
                $errorMessages[$this->getErrorCode()]
            ) ?
            $errorMessages[$this->getErrorCode()] : $errorMessages[self::ERROR];
    }
}
