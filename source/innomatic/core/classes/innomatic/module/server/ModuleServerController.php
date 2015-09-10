<?php
namespace Innomatic\Module\Server;

/**
 * Controls Module server execution.
 *
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 * @copyright Copyright 2004-2014 Innoteam Srl
 * @since 5.1
 */
class ModuleServerController
{
    /**
     * Server socket.
     *
     * @var Socket
     * @since 5.1
     */
    protected $socket;
    /**
     * Hostname bound to the server.
     *
     * @var string
     * @since 5.1
     */
    protected $host;
    /**
     * Port where the server is listening.
     *
     * @var integer
     * @since 5.1
     */
    protected $port;

    /**
     * Class constructor.
     *
     * @since 5.1
     */
    public function __construct()
    {
        $this->host = ModuleServerContext::instance('\Innomatic\Module\Server\ModuleServerContext')->getConfig()->getKey('server_address');
        if (!$this->host) {
            $this->host = 'localhost';
        }
        $this->port = ModuleServerContext::instance('\Innomatic\Module\Server\ModuleServerContext')->getConfig()->getKey('server_port');
        if (!$this->port) {
            $this->port = 9000;
        }
        $this->socket = new \Innomatic\Net\Socket\Socket();
    }

    /**
     * Starts the server.
     *
     * @since 5.1
     * @return void
     */
    public function start()
    {
        $server = new ModuleServerSocket();
        $server->start();
    }

    /**
     * Restarts the server.
     *
     * @since 5.1
     * @return void
     */
    public function restart()
    {
        try {
            $this->shutdown();
        } catch (\Exception $e) {
            print('Module server was not running.'."\n");
        }
        $this->start();
    }

    /**
     * Starts the server watch dog and the server using the watch dog itself.
     *
     * @since 5.1
     * @return void
     */
    public function watchDogStart()
    {
        $wdog = new ModuleServerWatchDog();
        $wdog->watch('php innomatic/core/scripts/moduleserver.php wstart');
    }

    /**
     * Restarts the server watch dog and the server using the watch dog itself.
     *
     * @since 5.1
     * @return void
     */
    public function watchDogRestart()
    {
        try {
            $this->shutdown();
        } catch (\Exception $e) {
            print('Module server was not running.'."\n");
        }
        $wdog = new ModuleServerWatchDog();
        $wdog->watch('php innomatic/core/scripts/moduleserver.php wstart');
    }

    /**
     * Safely shutdowns the server.
     *
     * @since 5.1
     * @return void
     */
    public function shutdown()
    {
        $auth = ModuleServerAuthenticator::instance('ModuleServerAuthenticator');
        $this->socket->connect($this->host, $this->port);
        $request = 'SHUTDOWN Module/1.0'."\r\n";
        $request .= 'User: admin'."\r\n";
        $request .= 'Password: '.$auth->getPassword('admin')."\r\n";
        $this->socket->write($request);
        $this->socket->disconnect();
    }

    /**
     * Retrieves server status.
     *
     * @since 5.1
     * @return string Server status.
     */
    public function status()
    {
        try {
            $result = '';
            $auth = ModuleServerAuthenticator::instance('ModuleServerAuthenticator');
            $this->socket->connect($this->host, $this->port);
            $request = 'STATUS Module/1.0'."\r\n";
            $request .= 'User: admin'."\r\n";
            $request .= 'Password: '.$auth->getPassword('admin')."\r\n";
            $this->socket->write($request);
            $result = $this->socket->readAll();
            $this->socket->disconnect();
        } catch (\Innomatic\Net\Socket\SocketException $e) {
            $result = 'Module server is down.'."\n";
        }
        return $result;
    }

    /**
     * Forces the server to refresh its configuration.
     *
     * @since 5.1
     * @return string Server result string.
     */
    public function refresh()
    {
        try {
            $result = '';
            $auth = ModuleServerAuthenticator::instance('ModuleServerAuthenticator');
            $this->socket->connect($this->host, $this->port);
            $request = 'REFRESH Module/1.0'."\r\n";
            $request .= 'User: admin'."\r\n";
            $request .= 'Password: '.$auth->getPassword('admin')."\r\n";
            $this->socket->write($request);
            $result = $this->socket->readAll();
            $this->socket->disconnect();
        } catch (\Innomatic\Net\Socket\SocketException $e) {
            $result = 'Module server is down.'."\n";
        }
        return $result;
    }
}
