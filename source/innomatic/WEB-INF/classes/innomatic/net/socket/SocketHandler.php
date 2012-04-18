<?php 
/**
 * Innomatic
 *
 * LICENSE 
 * 
 * This source file is subject to the new BSD license that is bundled 
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2012 Innoteam S.r.l.
 * @license    http://www.innomatic.org/license/   BSD License
 * @link       http://www.innomatic.org
 * @since      Class available since Release 5.0
*/

/**
 * @author Alex Pagnoni <alex.pagnoni@innoteam.it>
 * @copyright Copyright 2003-2012 Innoteam S.r.l.
 * @since 1.0
 */
abstract class SocketHandler extends Object {
    protected $serversocket;

    function setServerSocket(ServerSocket $server) {
        $this->serversocket = $server;
    }

    function getServerSocket() {
        return $this->serversocket;
    }

    abstract function onStart();

    abstract function onShutDown();

    abstract function onConnect($clientId = NULL);

    abstract function onConnectionRefused($clientId = NULL);

    abstract function onClose($clientId = NULL);

    abstract function onReceiveData($clientId = NULL, $data = NULL);
}
