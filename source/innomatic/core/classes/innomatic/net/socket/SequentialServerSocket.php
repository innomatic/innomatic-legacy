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
 * @license    http://www.innomaticplatform.com/license/ New BSD License
 * @link       http://www.innomaticplatform.com
 * @since      Class available since Release 5.0
*/
namespace Innomatic\Net\Socket;

/**
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 * @copyright Copyright 2003-2012 Innoteam Srl
 * @since 1.0
 */
class SequentialServerSocket extends \Innomatic\Net\Socket\ServerSocket
{
    protected $clients = 0;
    protected $initFD;
    protected $clientFD;

    public function __construct($host, $port)
    {
        parent::__construct($host, $port);
    }
    
    /**
     * Starts the server.
     *
     */
    public function start()
    {
        $this->initFD = @socket_create(AF_INET, SOCK_STREAM, 0);
        if (!$this->initFD) {
            throw new \RuntimeException('Could not create socket.');
        }
        // adress may be reused
        socket_setopt($this->initFD, SOL_SOCKET, SO_REUSEADDR, 1);
        // bind the socket
        if (!@socket_bind($this->initFD, $this->bindAddr, $this->port)) {
            $error = $this->getLastSocketError($this->initFd);
            @socket_close($this->initFD);
            throw new \RuntimeException("Could not bind socket to ".$this->bindAddr." on port ".$this->port." (".$error.").");
        }
        // listen on selected port
        if (!@socket_listen($this->initFD, $this->maxQueue)) {
            $error = $this->getLastSocketError($this->initFd);
            @socket_close($this->initFD);
            throw new \RuntimeException("Could not listen (".$error.").");
        }

        // this allows the shutdown function to check whether the server is already shut down
        $GLOBALS["_Net_Server_Status"] = "running";

        $this->handler->onStart();

        while (true) {
            $readFDs = array ();
            array_push($readFDs, $this->initFD);
            // fetch all clients that are awaiting connections
            for ($i = 0; $i < count($this->clientFD); $i ++) {
                if (isset ($this->clientFD[$i]))
                    array_push($readFDs, $this->clientFD[$i]);
            }
            // block and wait for data or new connection
            $ready = @socket_select($readFDs, $this->null, $this->null, null);

            if ($ready === false) {
                $this->shutdown();
            }
            // check for new connection
            if (in_array($this->initFD, $readFDs)) {
                $newClient = $this->acceptConnection($this->initFD);
                // check for maximum amount of connections
                if ($this->maxClients > 0) {
                    if ($this->clients > $this->maxClients) {

                        $this->handler->onConnectionRefused($newClient);

                        $this->closeConnection($newClient);
                    }
                }

                if (-- $ready <= 0) {
                    continue;
                }
            }
            // check all clients for incoming data
            for ($i = 0; $i < count($this->clientFD); $i ++) {
                if (!isset ($this->clientFD[$i])) {
                    continue;
                }

                if (in_array($this->clientFD[$i], $readFDs)) {
                    $data = $this->readFromSocket($i);
                    // empty data => connection was closed
                    if (!$data) {
                        $this->closeConnection($i);
                    } else {

                        $this->handler->onReceiveData($i, $data);
                    }
                }
            }
        }
    }

    /**
     * Accepts a new connection.
     *
     * @param resource $ &$socket    socket that received the new connection
     * @return int $clientID   internal ID of the client
     */
    public function acceptConnection(& $socket)
    {
        for ($i = 0; $i <= count($this->clientFD); $i ++) {
            if (!isset ($this->clientFD[$i]) || $this->clientFD[$i] == null) {
                $this->clientFD[$i] = socket_accept($socket);
                socket_setopt($this->clientFD[$i], SOL_SOCKET, SO_REUSEADDR, 1);
                $peer_host = "";
                $peer_port = "";
                socket_getpeername($this->clientFD[$i], $peer_host, $peer_port);
                $this->clientInfo[$i] = array ("host" => $peer_host, "port" => $peer_port, "connectOn" => time());
                $this->clients++;

                $this->handler->onConnect($i);
                return $i;
            }
        }
    }

    /**
     * Checks whether a client is still connected.
     *
     * @param integer $id client id
     * @return boolean $connected  true if client is connected, false otherwise
     */
    public function isConnected($id)
    {
        if (!isset($this->clientFD[$id])) {
            return false;
        }
        return true;
    }

    /**
     * get current amount of clients
     *
     * @return int $clients    amount of clients
     */
    public function getClients()
    {
        return $this->clients;
    }

    /**
     * send data to all clients
     *
     * @param string $data data to send
     * @param array $exclude client ids to exclude
     */
    public function broadcastData($data, $exclude = array ())
    {
        if (!empty ($exclude) && !is_array($exclude)) {
            $exclude = array ($exclude);
        }

        for ($i = 0; $i < count($this->clientFD); $i ++) {
            if (isset ($this->clientFD[$i]) && $this->clientFD[$i] != null && !in_array($i, $exclude)) {
                if (!@socket_write($this->clientFD[$i], $data)) {
                }
            }
        }
    }

    /**
     * close connection to a client
     *
     * @param int $clientID internal ID of the client
     */
    public function closeConnection($id = 0)
    {
        if (!isset ($this->clientFD[$id])) {
            throw new \RuntimeException("Connection already has been closed.");
        }

        $this->handler->onClose($id);

        @socket_close($this->clientFD[$id]);
        $this->clientFD[$id] = null;
        unset ($this->clientInfo[$id]);
        $this->clients--;
    }

    /**
     * shutdown server
     *
     */
    public function shutDown()
    {
        if (isset ($GLOBALS["_Net_Server_Status"]) and $GLOBALS["_Net_Server_Status"] != "running") {
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->halt();
        }
        $GLOBALS["_Net_Server_Status"] = "stopped";

        $this->handler->onShutdown();

        $maxFD = count($this->clientFD);
        for ($i = 0; $i < $maxFD; $i ++) {
            $this->closeConnection($i);
        }

        @socket_close($this->initFD);

        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->halt();
    }
}
