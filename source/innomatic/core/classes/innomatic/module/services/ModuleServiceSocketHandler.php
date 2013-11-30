<?php
namespace Innomatic\Module\Services;

require_once('innomatic/net/socket/SocketHandler.php');
require_once('innomatic/module/server/ModuleServerXmlRpcProcessor.php');
require_once('innomatic/module/server/ModuleServerContext.php');
require_once('innomatic/module/server/ModuleServerLogger.php');
require_once('innomatic/module/server/ModuleServerRequest.php');
require_once('innomatic/module/server/ModuleServerResponse.php');
require_once('innomatic/module/server/ModuleServerAuthenticator.php');
require_once('innomatic/module/session/ModuleSessionGarbageCollector.php');
require_once('innomatic/module/services/ModuleRegistryHandler.php');

/**
 * Module server socket handler and requests dispatcher.
 *
 * @author Alex Pagnoni
 * @copyright Copyright 2005-2013 Innoteam Srl
 * @since 5.1
 */
class ModuleServiceSocketHandler extends SocketHandler
{
    /**
     * Access log flag.
     *
     * @var bool
     * @access protected
     * @since 5.1
     */
    protected $accessLogEnabled = false;
    /**
     * Server log flag.
     *
     * @var bool
     * @access protected
     * @since 5.1
     */
    protected $serverLogEnabled = false;
    /**
     * Access logger object.
     *
     * @var ModuleServerLogger
     * @access protected
     * @since 5.1
     */
    protected $accessLogger;
    /**
     * Server logger object.
     *
     * @var ModuleServerLogger
     * @access protected
     * @since 5.1
     */
    protected $serverLogger;
    /**
     * Shutdown phase flag.
     *
     * @var bool
     * @access protected
     * @since 5.1
     */
    protected $shutdown = false;
    /**
     * Authenticator object.
     *
     * @var ModuleServerAuthenticator
     * @access protected
     * @since 5.1
     */
    protected $authenticator;

   /**
     * reference to the handler of the registry
     *
     * @var ModuleRegistryHandler
     * @access protected
     * @since 5.1
     */
    protected $registryHandler;

   /**
     * indicates the load of the local node
     * (indicates in real-time the n� of client served)
     *
     * @var int
     * @access protected
     * @since 5.1
     */
    protected $load;


    /**
     * Actions executed at socket server startup.
     *
     * During this method execution, loggers are started and garbage collecting
     * process is launched.
     *
     * @access public
     * @since 5.1
     */
    public function onStart() { //OK
        $context = ModuleServerContext::instance('ModuleServerContext');
        if ($context->getConfig()->getKey('log_server_events') == 1 or $context->getConfig()->useDefaults()) {
            $this->serverLogEnabled = true;
            $this->serverLogger = new ModuleServerLogger($context->getHome().'core/log/module-services.log');
        }
        if ($context->getConfig()->getKey('log_access_events') == 1) {
            $this->accessLogEnabled = true;
            $this->accessLogger = new ModuleServerLogger($context->getHome().'core/log/module-access.log');
        }

        ModuleSessionGarbageCollector::clean();
        $this->authenticator = ModuleServerAuthenticator::instance('ModuleServerAuthenticator');

        if ($this->serverLogEnabled) {
            $this->serverLogger->logEvent('Start');
        }
        //loading net-registry
        $this->registryHandler = new ModuleRegistryHandler();
        $this->registryHandler->parseRegistry();

        Runtime::getInstance()->addShutdownHook($this, 'onHalt');
        print('Module: services-extension started and listening at port '.$this->serversocket->getPort()."\n");
    }

    /**
     * Actions executed at socket server shutdown.
     *
     * @access public
     * @since 5.1
     */
    public function onShutDown()
    {
        if ($this->shutdown) {
            return;
        }
        if ($this->serverLogEnabled) {
            $this->serverLogger->logEvent('Shutdown');
        }
        print('Module: services-extension stopped.'."\n");
        ModuleSessionGarbageCollector::clean();
        $this->shutdown = true;
    }

    /**
     * Actions execute at socket server halt.
     *
     * If this method is called, this means that the Module server has failed.
     *
     * @access public
     * @since 5.1
     */
    public function onHalt()
    {
        if (!$this->shutdown) {
            print('Module: services-extension failed.'."\n");
        }
    }

    /**
     * Actions executed when a connection starts.
     *
     * No real action is executed.
     *
     * @access public
     * @since 5.1
     */
    public function onConnect($clientId = null)
    {
    }

    /**
     * Actions executed when a connection is refused.
     *
     * No real action is executed.
     *
     * @access public
     * @since 5.1
     */
    public function onConnectionRefused($clientId = null)
    {
    }

    /**
     * Actions executed when a connection is closed.
     *
     * No real action is executed.
     *
     * @access public
     * @since 5.1
     */
    public function onClose($clientId = null)
    {
    }

    /**
     * Actions executed when receiving data.
     *
     * The current implementation of the server recognizes the following
     * commands:
     *
     * - SHUTDOWN: the shutdown procedure is called;
     * - STATUS: a message about the status of the server is sent;
     * - REFRESH: refreshes the loaded Module configuration;
     * - INVOKE: a command is requested to be executed.
     *
     * @access public
     * @since 5.1
     */
    public function onReceiveData($clientId = null, $data = null)
    {
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
                case 'GET_REGISTRY' : //OK
                    if ($this->authenticator->authorizeAction($headers['User'], 'get_registry')) {
                        $this->logAccess($clientId, $headers['User'], $command_line);
                        $context = ModuleServerContext::instance('ModuleServerContext');
                           if($file_content = file_get_contents($context->getHome().'core/conf/modules-netregistry.xml')) {
                            $response->setBuffer($file_content);
                           } else {
                            $response->setBuffer("Net Registry not found");
                        }

                    } else {
                        $response->sendWarning(ModuleServerResponse::SC_FORBIDDEN, 'Action not authorized');
                    }
                    break;

                case 'SHUTDOWN' :
                    if ($this->authenticator->authorizeAction($headers['User'], 'shutdown')) {
                        $this->serversocket->sendData($clientId, "Shutdown requested.\n", true);
                        $this->logAccess($clientId, $headers['User'], $command_line);
                        $this->serversocket->shutDown();
                        return;
                    } else {
                        $response->sendWarning(ModuleServerResponse::SC_FORBIDDEN, 'Action not authorized');
                    }
                    break;

                case 'STATUS' :
                    if ($this->authenticator->authorizeAction($headers['User'], 'status')) {
                        $response->setBuffer("Module: services-extension up and ready.\n");
                    } else {
                        $response->sendWarning(ModuleServerResponse::SC_FORBIDDEN, 'Action not authorized');
                    }
                    break;

                case 'REFRESH' : //refresh configuration and net-registry (following ini-file parameters)
                    if ($this->authenticator->authorizeAction($headers['User'], 'status')) {
                        $this->authenticator->parseConfig();
                        $registryHandler = new ModuleRegistryHandler();
                        $registryHandler->parseRegistry();
                        $response->setBuffer("Module: services-extension registry reloaded successfully.\n");
                        print("Module: services-extension registry reloaded successfully.\n");
                    } else {
                        $response->sendWarning(ModuleServerResponse::SC_FORBIDDEN, 'Action not authorized');
                    }
                    break;

                case 'INVOKE' :
                    if ($this->authenticator->authorizeModule($headers['User'], $command[1])) {
                        $request = new ModuleServerRequest();
                        $request->setCommand($command_line);
                        $request->setHeaders($headers);
                        $request->setPayload($body);

                        $server = new ModuleServerXmlRpcProcessor();
                        $server->process($request, $response);
                    } else {
                        $response->sendWarning(ModuleServerResponse::SC_FORBIDDEN, 'Module not authorized');
                    }
                    break;
            }
        } else {
            $response->sendWarning(ModuleServerResponse::SC_UNAUTHORIZED, 'Authentication needed');
        }

        $this->serversocket->sendData($clientId, $response->getResponse(), true);
        $this->logAccess($clientId, $headers['User'], $command_line);
        $this->serversocket->closeConnection();
    }

    /**
    * Logs an access to the server socket.
    *
    * This method is executed only if the access logger flag is set to true.
    *
    * @access protected
    * @since 5.1
    * @param integer $clientId Client id.
    * @param string $user Username given by the client.
    * @param string $command Command that the client asked to execute.
    */
    protected function logAccess($clientId, $user, $command)
    {
        if ($this->accessLogEnabled) {
            $client = $this->serversocket->getClientInfo($clientId);
            $this->accessLogger->logEvent($user.'@'.$client['host'].' "'.$command.'"');
        }
    }
}
