<?php
namespace Innomatic\Module\Server;

use \Innomatic\Module;

/**
 * Processor for Module server XmlRpc messages.
 *
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 * @copyright Copyright 2004-2014 Innoteam Srl
 * @since 5.1
 */
class ModuleServerXmlRpcProcessor
{
    /**
     * Module object.
     *
     * @var ModuleObject
     * @access protected
     * @since 5.1
     */
    protected $module;

    /**
     * Processes an incoming request, executes it and builds a response.
     *
     * @access public
     * @since 5.1
     * @param ModuleServerRequest $request Incoming request.
     * @param ModuleServerResponse $response Outcoming response.
     * @return void
     */
    public function process(ModuleServerRequest $request, ModuleServerResponse $response)
    {
        $command = explode(' ', $request->getCommand());
        $module_location = $command[1];
        if (!strlen($module_location)) {
            $response->sendWarning(ModuleServerResponse::SC_NOT_FOUND, 'Module location not defined.', ModuleServerRsponse::ERROR_CLASSNAME_MISSING);
            return;
        }

        try {
            $locator = new ModuleLocator('module://'.$request->getHeader('User').':'.$request->getHeader('Password').'@/'.$module_location);
            $sessionId = $request->getHeader('Session');
            if ($sessionId) {
                $this->module = ModuleFactory::getSessionModule($locator, $sessionId);
            } else {
                $this->module = ModuleFactory::getModule($locator);
            }
        } catch (ModuleException $e) {
            $response->sendWarning(ModuleServerResponse::SC_INTERNAL_SERVER_ERROR, $e->__toString());
            return;
        } catch (\Exception $e) {
            $response->sendWarning(ModuleServerResponse::SC_INTERNAL_SERVER_ERROR, $e->__toString());
            return;
        }

        if (!$xmlrpc_server = xmlrpc_server_create()) {
            $response->sendWarning(ModuleServerResponse::SC_INTERNAL_SERVER_ERROR, 'Internal error: Could not create an XML-RPC server.', ModuleServerResponse::ERROR_XMLRPC_ERROR);
            return;
        }

        $theClass = new \ReflectionObject($this->module);
        $methods = $theClass->getMethods();
        foreach ($methods as $method) {
            // Ignore private methods
            $theMethod = new \ReflectionMethod($theClass->getName(), $method->getName());
            if (!$theMethod->isPublic()) {
                continue;
            }
            // Expose only methods beginning with "module" prefix
            if (!(substr($method->getName(), 0, 6) == 'module')) {
                continue;
            }
            xmlrpc_server_register_method($xmlrpc_server, strtolower($method->getName()), array ($this, 'xmlrpcGateway'));
        }

        xmlrpc_server_register_introspection_callback($xmlrpc_server, array ($this, 'introspectionGateway'));
        try {
            $buffer = xmlrpc_server_call_method($xmlrpc_server, $request->getPayload(), '', array ('output_type' => 'xml'));
            $response->addHeader('Module/1.0 '.ModuleServerResponse::SC_OK);
            $response->setBuffer($buffer);
        } catch (\Exception $e) {
            $response->addHeader('Module/1.0 '.ModuleServerResponse::SC_INTERNAL_ERROR);
            $response->setBuffer($buffer);
        }
        xmlrpc_server_destroy($xmlrpc_server);

        $context = new ModuleContext($module_location);
        $session = new \Innomatic\Module\Session\ModuleSession($context, $sessionId);
        $session->save($this->module);
        $response->addHeader('Session: '.$session->getId());
    }

    /**
     * Calls a Module method passing the given parameters.
     *
     * @access private
     * @since 5.1
     * @param string $method_name Method to be called.
     * @param array $params Parameters
     * @param string $app_data
     * return mixed Module method result.
     */
    private function xmlrpcGateway($method_name, $params, $app_data)
    {
        return call_user_func_array(array ($this->module, $method_name), $params);
    }

    /**
     * Calls the Module::moduleIntrospect method if it exists.
     *
     * A class can be documented as describer here:
     * http://xmlrpc-epi.sourceforge.net/specs/rfc.system.describeMethods.php
     *
     * @access private
     * @since 5.1
     * @param array $userData
     * @return mixed
     */
    private function introspectionGateway($userData)
    {
        if (method_exists($this->module, 'moduleIntrospect')) {
            return $this->module->moduleIntrospect();
        }
        return false;
    }

}
