<?php
namespace Innomatic\Module\Services;

/**
 * Module service socket launcher.
 *
 * @author Alex Pagnoni
 * @copyright Copyright 2005-2014 Innoteam Srl
 * @since 5.1
 */
class ModuleServiceSocket extends ModuleServerSocket
{
    /**
     * Starts the server socket.
     *
     * @access public
     * @since 5.1
     * @return void
     */
    public function start()
    {
        print('service socket: start'."\n");
        $context = ModuleServerContext::instance('\Innomatic\Module\Server\ModuleServerContext');
        $port = $context->getConfig()->getKey('service_port');
        if (!strlen($port)) {
            $port = '9001';
        }
        $bindAddress = $context->getConfig()->getKey('server_address');
        if (!strlen($bindAddress)) {
            $bindAddress = '127.0.0.1';
        }

        $server = new \Innomatic\Net\Socket\SequentialServerSocket($bindAddress, $port);
        $server->setHandler(new ModuleServiceSocketHandler());
        $server->start();
    }
}
