<?php
namespace Innomatic\Module\Services;

use \Innomatic\Module\Server;

/**
 * Controls Module server execution with service support.
 *
 * @author Alex Pagnoni
 * @copyright Copyright 2005-2014 Innomatic Company
 * @since 5.1
 */
class ModuleServiceController extends ModuleServerController
{
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
        $this->port = ModuleServerContext::instance('\Innomatic\Module\Server\ModuleServerContext')->getConfig()->getKey('service_port');
        if (!$this->port) {
            $this->port = 9001;
        }
        $this->socket = new Socket();
    }

    /**
     * Starts the server with generic-service support.
     * (also pinger inizialization)
     *
     * @since 5.1
     * @return void
     */
    public function start()
    {
        print('ModuleServiceController: start'."\n");

        $this->startPinger();

        $server = new ModuleServiceSocket();
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
            print('Module: services-extension was not running.'."\n");
        }
        $this->start();
    }

    /**
     * Starts the server watch dog and the server using the watch dog itself.
     * No need to use another watchdog, the ModuleServerWatchDog can watch also this server
     *
     * @since 5.1
     * @return void
     */
    public function watchDogStart()
    {
        $wdog = new ModuleServerWatchDog();
        $wdog->watch('php core/scripts/moduleservices.php wstart');
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
            print('Module: services-extension was not running.'."\n");
        }
        $wdog = new ModuleServerWatchDog();
        $wdog->watch('php core/scripts/moduleservices.php wstart');
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
        $this->stopPinger();
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
            $result = 'Module: services-extension is down.';
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
            $result = 'Module: service-extension is down.';
        }
        return $result;
    }

    /**
     * pinger inizialization
     * (runs a new console that executes the piger)
     *
     * @see ModulePinger.php
     * @since 5.1
     * @return string Server result string.
     */
    private function startPinger()
    {
        // TODO
           if (strtoupper(substr(PHP_OS, 0, 3) == 'WIN')) {
             $context = ModuleServerContext::instance('\Innomatic\Module\Server\ModuleServerContext');
            $path = $context->getHome().'classes\\it\\innoteam\\module\\services\\';
            $WshShell1 = new COM("WScript.Shell");
            $WshShell2 = new COM("WScript.Shell");
            $oExec1 = $WshShell1->Run('cmd /c carthag '.$path.'ModulePingerServer.php', 1, false);
            $oExec2 = $WshShell2->Run('cmd /c carthag '.$path.'ModulePinger.php 5 15', 1, false);
        } else {
            //ADD UNIX NATIVE INSTRUCTIONS HERE
        }
    }

    /**
     * stops the pinger
     * (starts the pinger shuting down sequence)
     *
     * @see ModulePinger.php
     * @since 5.1
     * @return string Server result string.
     */
    private function stopPinger()
    {
            $result = '';
            $auth = ModuleServerAuthenticator::instance('ModuleServerAuthenticator');
            $context = ModuleServerContext::instance('\Innomatic\Module\Server\ModuleServerContext');
            $this->socket->connect('127.0.0.1', $context->getConfig()->getKey('pinger_port'));
            $request = 'SHUTDOWN Module/1.0'."\r\n";
            $request .= 'User: admin'."\r\n";
            $request .= 'Password: '.$auth->getPassword('admin')."\r\n";
            $this->socket->write($request);
            $result = $this->socket->readAll();
            $this->socket->disconnect();
    }

}
