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
 * @since      Class available since Release 5.0
*/
namespace Innomatic\Net\Socket;

/**
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 * @copyright Copyright 2003-2014 Innoteam Srl
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

    abstract public function onConnect($clientId = null);

    abstract public function onConnectionRefused($clientId = null);

    abstract public function onClose($clientId = null);

    abstract public function onReceiveData($clientId = null, $data = null);
}
