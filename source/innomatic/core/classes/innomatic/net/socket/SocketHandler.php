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
 * @copyright Copyright 2003-2013 Innoteam Srl
 * @since 1.0
 */
abstract class SocketHandler
{
    protected $serversocket;

    public function setServerSocket(ServerSocket $server)
    {
        $this->serversocket = $server;
    }

    public function getServerSocket()
    {
        return $this->serversocket;
    }

    abstract public function onStart();

    abstract public function onShutDown();

    abstract public function onConnect($clientId = NULL);

    abstract public function onConnectionRefused($clientId = NULL);

    abstract public function onClose($clientId = NULL);

    abstract public function onReceiveData($clientId = NULL, $data = NULL);
}
