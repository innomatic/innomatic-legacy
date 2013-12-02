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

/**
 * @author Alex Pagnoni <alex.pagnoni@innoteam.it>
 * @copyright Copyright 2003-2012 Innoteam Srl
 * @since 1.0
 */
abstract class ServerSocket
{
    protected $port;
    protected $bindAddr;
    protected $maxClients;
    protected $fd = array();
    protected $readBufSize;
    protected $readEndChar;
    protected $maxQueue;
    protected $handler;

    /**
     * empty array, used for socket_select
     *
     * @access private
     * @var array $null
     */
    protected $null = array();

    /**
     * needed to store client information
     *
     * @access private
     * @var array $clientInfo
     */
    protected $clientInfo = array();

    public function __construct($host, $port)
    {
        $this->bindAddr = $host;
        $this->port = (int) $port;
        $this->maxClients = -1;
        $this->readBufSize = 512;
        $this->readEndChar = "\n";
        $this->maxQueue = 500;
    }

    public function __destruct()
    {
        $this->shutdown();
    }

    public function setHandler(SocketHandler $handler)
    {
        $this->handler = $handler;
        $this->handler->setServerSocket($this);
    }

    public function setMaxClients($maxClients)
    {
        $this->maxClients = $maxClients;
    }

    /**
     * read from a socket
     *
     * @access private
     * @param integer $clientId internal id of the client to read from
     * @return string $data        data that was read
     */
    public function readFromSocket($clientId = 0)
    {
        $data = '';
        // read data from socket
        while ($buf = socket_read($this->clientFD[$clientId], $this->readBufSize, PHP_BINARY_READ)) {
            $data .= $buf;

            if ($buf == NULL || strlen($buf) < $this->readBufSize) {
                break;
            }

            /*
            if (substr($buf, - strlen($this->readEndChar)) == $this->readEndChar) {
                break;
            }
            */

            if ($buf === false) {
            }
        }

        return $data;
    }

    public function sendData($clientId, $data)
    {
        if (!isset($this->clientFD[$clientId]) || $this->clientFD[$clientId] == null) {
            throw new RuntimeException("Client does not exist.");
        }

        if (!@socket_write($this->clientFD[$clientId], $data)) {
        }
    }

    public function getLastSocketError(& $fd)
    {
        if (!is_resource($fd)) {
            return '';
        }
        $lastError = socket_last_error($fd);
        return 'Msg: '.socket_strerror($lastError).' / Code: '.$lastError;
    }

    public function getClientInfo($id)
    {
            if (!isset($this->clientFD[$id]) || $this->clientFD[$id] == NULL) {
            return NULL;
        }
        return $this->clientInfo[$id];
    }

    public function getPort()
    {
        return $this->port;
    }

    public function getBindAddr()
    {
        return $this->bindAddr;
    }

    abstract public function start();
    abstract public function isConnected($id);
    abstract public function getClients();
    abstract public function broadcastData($data, $exclude=array());
    abstract public function closeConnection($id = 0);
    abstract public function shutdown();
}
