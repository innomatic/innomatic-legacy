<?php
namespace Innomatic\Module\Services;

require_once('innomatic/net/socket/SequentialServerSocket.php');
require_once('innomatic/module/services/ModuleServiceSocketHandler.php');

/**
 * Module service socket launcher.
 *
 * @author Alex Pagnoni
 * @copyright Copyright 2005-2013 Innoteam Srl
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
        $context = ModuleServerContext::instance('ModuleServerContext');
        $port = $context->getConfig()->getKey('service_port');
        if (!strlen($port)) {
            $port = '9001';
        }
        $bindAddress = $context->getConfig()->getKey('server_address');
        if (!strlen($bindAddress)) {
            $bindAddress = '127.0.0.1';
        }

        $server = new SequentialServerSocket($bindAddress, $port);
        $server->setHandler(new ModuleServiceSocketHandler());
        $server->start();
    }
}
