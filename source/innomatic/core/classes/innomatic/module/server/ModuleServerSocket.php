<?php  

require_once('innomatic/net/socket/SequentialServerSocket.php');
require_once('innomatic/module/server/ModuleServerSocketHandler.php');

/**
 * Module server socket launcher.
 *
 * @author Alex Pagnoni <alex.pagnoni@innoteam.it>
 * @copyright Copyright 2004-2013 Innoteam Srl
 * @since 5.1
 */
class ModuleServerSocket {
    /**
     * Starts the server socket.
     *
     * @access public
     * @since 5.1
     * @return void
     */
    public function start() {
        $context = ModuleServerContext::instance('ModuleServerContext');
        $port = $context->getConfig()->getKey('server_port');
        if (!strlen($port)) {
            $port = '9000';
        }
        $bindAddress = $context->getConfig()->getKey('server_address');
        if (!strlen($bindAddress)) {
            $bindAddress = '127.0.0.1';
        }

        $server = new SequentialServerSocket($bindAddress, $port);
        $server->setHandler(new ModuleServerSocketHandler());
        $server->start();
    }
}

?>