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
 * @license    http://www.innomaticplatform.com/license/   BSD License
 * @link       http://www.innomaticplatform.com
 */
namespace Innomatic\Dataaccess;

/**
 * @since 5.0.0 introduced
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 */
class DataAccessSourceName
{
    private $type = false;
    private $dbsyntax = false;
    private $username = false;
    private $password = false;
    private $protocol = false;
    private $hostspec = false;
    private $port = false;
    private $socket = false;
    private $database = false;
    private $options = array();

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getDbSyntax()
    {
        return $this->dbsyntax;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getProtocol()
    {
        return $this->protocol;
    }

    public function getHostSpec()
    {
        return $this->hostspec;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function getSocket()
    {
        return $this->socket;
    }

    public function getDatabase()
    {
        return $this->database;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getOption($key)
    {
        if (isset($this->options[$key])) {
            return $this->options[$key];
        }
    }

    /**
     * Parses a data access source name.
     *
     * Additional keys can be added by appending a URI query string to the
     * end of the DASN.
     *
     * The format of the supplied DASN is in its fullest form:
     * <code>
     *  type(dbsyntax)://username:password@protocol+hostspec/database?option=8&another=true
     * </code>
     *
     * Most variations are allowed:
     * <code>
     *  type://username:password@protocol+hostspec:110//usr/db_file.db?mode=0644
     *  type://username:password@hostspec/database_name
     *  type://username:password@hostspec
     *  type://username@hostspec
     *  type://hostspec/database
     *  type://hostspec
     *  type(dbsyntax)
     *  type
     * </code>
     *
     * @param string $dasn Data Access Source Name to be parsed
     * @author Tomas V.V.Cox <cox@idecnet.com>
     * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
     * @copyright Copyright (c) 1997-2004 The PHP Group
     * @copyright Copyright 2004-2012 Innoteam Srl
     */
    public function __construct($dasn)
    {
        $parsed = array(
            'type'  => false,
            'phptype' => false,
            'dbsyntax' => false,
            'username' => false,
            'password' => false,
            'protocol' => false,
            'hostspec' => false,
            'port'     => false,
            'socket'   => false,
            'database' => false,
        );

        if (is_array($dasn)) {
            $dasn = array_merge($parsed, $dasn);
            if (!$dasn['dbsyntax']) {
                $dasn['dbsyntax'] = isset($dasn['phptype']) ? $dasn['phptype'] : $dasn['type'];
            }
            return $this->setDASN($dasn);
        }

        // Find phptype and dbsyntax
        if (($pos = strpos($dasn, '://')) !== false) {
            $str = substr($dasn, 0, $pos);
            $dasn = substr($dasn, $pos + 3);
        } else {
            $str = $dasn;
            $dasn = null;
        }

        // Get phptype and dbsyntax
        // $str => phptype(dbsyntax)
        if (preg_match('|^(.+?)\((.*?)\)$|', $str, $arr)) {
            $parsed['phptype']  = $arr[1];
            $parsed['dbsyntax'] = !$arr[2] ? $arr[1] : $arr[2];
        } else {
            $parsed['phptype']  = $str;
            $parsed['dbsyntax'] = $str;
        }

        if (!count($dasn)) {
            return $this->setDASN($parsed);
        }

        // Get (if found): username and password
        // $dasn => username:password@protocol+hostspec/database
        if (($at = strrpos($dasn,'@')) !== false) {
            $str = substr($dasn, 0, $at);
            $dasn = substr($dasn, $at + 1);
            if (($pos = strpos($str, ':')) !== false) {
                $parsed['username'] = rawurldecode(substr($str, 0, $pos));
                $parsed['password'] = rawurldecode(substr($str, $pos + 1));
            } else {
                $parsed['username'] = rawurldecode($str);
            }
        }

        // Find protocol and hostspec

        // $dasn => proto(proto_opts)/database
        if (preg_match('|^([^(]+)\((.*?)\)/?(.*?)$|', $dasn, $match)) {
            $proto       = $match[1];
            $proto_opts  = $match[2] ? $match[2] : false;
            $dasn         = $match[3];

        // $dasn => protocol+hostspec/database (old format)
        } else {
            if (strpos($dasn, '+') !== false) {
                list($proto, $dasn) = explode('+', $dasn, 2);
            }
            if (strpos($dasn, '/') !== false) {
                list($proto_opts, $dasn) = explode('/', $dasn, 2);
            } else {
                $proto_opts = $dasn;
                $dasn = null;
            }
        }

        // process the different protocol options
        $parsed['protocol'] = (!empty($proto)) ? $proto : 'tcp';
        $proto_opts = rawurldecode($proto_opts);
        if ($parsed['protocol'] == 'tcp') {
            if (strpos($proto_opts, ':') !== false) {
                list($parsed['hostspec'], $parsed['port']) = explode(':', $proto_opts);
            } else {
                $parsed['hostspec'] = $proto_opts;
            }
        } elseif ($parsed['protocol'] == 'unix') {
            $parsed['socket'] = $proto_opts;
        }

        // Get database if any
        // $dasn => database
        if ($dasn) {
            // /database
            if (($pos = strpos($dasn, '?')) === false) {
                $parsed['database'] = rawurldecode($dasn);
            // /database?param1=value1&param2=value2
            } else {
                $parsed['database'] = rawurldecode(substr($dasn, 0, $pos));
                $dasn = substr($dasn, $pos + 1);
                if (strpos($dasn, '&') !== false) {
                    $opts = explode('&', $dasn);
                } else { // database?param1=value1
                    $opts = array($dasn);
                }
                foreach ($opts as $opt) {
                    list($key, $value) = explode('=', $opt);
                    if (!isset($parsed[$key])) {
                        // don't allow params overwrite
                        $parsed[$key] = rawurldecode($value);
                    }
                }
            }
        }

        return $this->setDASN($parsed);
    }

    private function setDASN($dasn)
    {
        $this->type = isset($dasn['phptype']) ? $dasn['phptype'] : $dasn['type'];
        unset($dasn['phptype']);
        unset($dasn['type']);
        $this->dbsyntax = $dasn['dbsyntax'];
        unset($dasn['dbsyntax']);
        $this->username = $dasn['username'];
        unset($dasn['username']);
        $this->password = $dasn['password'];
        unset($dasn['password']);
        $this->protocol = $dasn['protocol'];
        unset($dasn['protocol']);
        $this->hostspec = $dasn['hostspec'];
        unset($dasn['hostspec']);
        $this->port = $dasn['port'];
        unset($dasn['port']);
        $this->socket = $dasn['socket'];
        unset($dasn['socket']);
        $this->database = $dasn['database'];
        unset($dasn['database']);
        $this->options = $dasn;
        return true;
    }

    /**
     * Returns an associative array containig data access source name data.
     *
     * @return array an associative array with the following keys:
     *  + phptype:  Database backend used in PHP (mysql, odbc etc.)
     *  + dbsyntax: Database used with regards to SQL syntax etc.
     *  + protocol: Communication protocol to use (tcp, unix etc.)
     *  + hostspec: Host specification (hostname[:port])
     *  + database: Database to use on the DBMS server
     *  + username: User name for login
     *  + password: Password for login
     */
    public function getDASN()
    {
        return array(
            'type' => $this->type,
            'phptype' => $this->type,
            'dbsyntax' => $this->dbsyntax,
            'username' => $this->username,
            'password' => $this->password,
            'protocol' => $this->protocol,
            'hostspec' => $this->hostspec,
            'port' => $this->port,
            'socket' => $this->socket,
            'database' => $this->database,
            'options' => $this->options
        );
    }
}
