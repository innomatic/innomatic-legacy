<?php
namespace Innomatic\Module\Server;

/**
 * Module server socket launcher.
 *
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 * @copyright Copyright 2004-2014 Innoteam Srl
 * @since 5.1
 */
class ModuleServerSocket
{
    /**
     * Starts the server socket.
     * 
     * @since 5.1
     * @return void
     */
    public function start()
    {
        $context = ModuleServerContext::instance('\Innomatic\Module\Server\ModuleServerContext');
        $port = $context->getConfig()->getKey('server_port');
        if (!strlen($port)) {
            $port = '9000';
        }
        $bindAddress = $context->getConfig()->getKey('server_address');
        if (!strlen($bindAddress)) {
            $bindAddress = '127.0.0.1';
        }

        $server = new \Innomatic\Net\Socket\SequentialServerSocket($bindAddress, $port);
        $server->setHandler(new ModuleServerSocketHandler());
        $server->start();
    }
}
