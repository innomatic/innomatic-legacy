<?php
namespace Innomatic\Module\Services;

require_once('innomatic/net/socket/SocketHandler.php');
require_once('innomatic/module/server/ModuleServerContext.php');
require_once('innomatic/net/socket/SequentialServerSocket.php');
require_once('innomatic/module/server/ModuleServerResponse.php');
require_once('innomatic/module/server/ModuleServerAuthenticator.php');

/**
 * Module pinger service
 * (wait for "ping" requests from other peers and replays with "pong" message
 *
 * @author Alex Pagnoni
 * @copyright Copyright 2005-2013 Innoteam Srl
 * @since 5.1
 */
class ModulePingerServer extends SocketHandler
{
    /**
     * Authenticator object.
     *
     * @var ModuleServerAuthenticator
     * @access protected
     * @since 5.1
     */
    protected $authenticator;

    /**
     * local address
     *
     * @var string
     * @access protected
     * @since 5.1
     */
    protected $bindAddress;

    /**
     * the port where the pinger server is running
     *
     * @var string
     * @access protected
     * @since 5.1
     */
    protected $port;

    public function main($args)
    {
        $context = ModuleServerContext::instance('ModuleServerContext');
        $this->port = $context->getConfig()->getKey('pinger_port');
        $this->bindAddress = $context->getConfig()->getKey('server_address');
        $server = new SequentialServerSocket($this->bindAddress, $this->port);
        $server->setHandler($this);
        $server->start();
    }

    public function onStart()
    {
        print('Pinger Server ready ('.$this->bindAddress.":".$this->port.")\n");
        $this->authenticator = ModuleServerAuthenticator::instance('ModuleServerAuthenticator');
    }

    public function onShutDown()
    {
    }

    public function onConnect($clientId = null)
    {
    }

    public function onConnectionRefused($clientId = null)
    {
    }

    public function onClose($clientId = null)
    {
    }

    public function onReceiveData($clientId = null, $data = null)
    {
//print('dati ricevuti');
        $response = new ModuleServerResponse();
        $raw_request = explode("\n", $data);
        $headers = array ();
        $body = '';
        $body_start = false;
        $command_line = '';

        foreach ($raw_request as $line) {
            $line = trim($line);
            if (!$body_start and $line == '') {
                $body_start = true;
                continue;
            }
            if ($body_start) {
                $body .= $line."\n";
            } else {
                if (strlen($command_line)) {
                    $headers[substr($line, 0, strpos($line, ':'))] = trim(substr($line, strpos($line, ':') + 1));
                } else {
                    $command_line = $line;
                }
            }
        }

        if (!isset($headers['User'])) {
            $headers['User'] = '';
        }
        if (!isset($headers['Password'])) {
            $headers['Password'] = '';
        }

        if ($this->authenticator->authenticate($headers['User'], $headers['Password'])) {
            $command = explode(' ', $command_line);
            switch ($command[0]) {
                case 'PING' :
                    if ($this->authenticator->authorizeAction($headers['User'], 'ping')) {
                        $response->setBuffer("pong. (from ".$this->bindAddress.":".$this->port.")\n");
                    } else {
                        $response->sendWarning(ModuleServerResponse::SC_FORBIDDEN, 'Action not authorized');
                    }
                    print("I've been pinged"."\n");
                    break;

                case 'SHUTDOWN' :
                    if ($this->authenticator->authorizeAction($headers['User'], 'shutdown')) {
                        $this->serversocket->sendData($clientId, "pinger server: shuting down.\n", true);
                        print("pinger server: shuting down"."\n");
                        Carthag::instance()->halt("pinger server: terminated.");

                    } else {
                        $response->sendWarning(ModuleServerResponse::SC_FORBIDDEN, 'Action not authorized');
                    }
                    break;
                default :
                    $response->sendWarning(ModuleServerResponse::SC_BAD_REQUEST, 'Cannot hunderstand command');
            }
        } else {
            $response->sendWarning(ModuleServerResponse::SC_UNAUTHORIZED, 'Authentication needed');
        }

        $this->serversocket->sendData($clientId, $response->getResponse(), true);
        $this->serversocket->closeConnection();
     }
}
