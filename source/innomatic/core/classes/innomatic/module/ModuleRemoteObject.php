<?php
namespace Innomatic\Module;

/**
 * Class for transparently access a remote Module object.
 *
 * @author Alex Pagnoni <alex.pagnoni@innoteam.it>
 * @copyright Copyright 2004-2014 Innoteam Srl
 * @since 5.1
 */
abstract class ModuleRemoteObject
{
    /**
     * Module locator object.
     *
     * @access protected
     * @var ModuleLocator
     * @since 5.1
     */
    protected $locator;
    /**
     * Module session id.
     *
     * @access protected
     * @var string
     * @since 5.1
     */
    protected $sessionId;

    /**
     * Object constructor.
     *
     * @access public
     * @param ModuleLocator $locator Module locator object.
     * @since 5.1
     */
    public function __construct(\Innomatic\Module\ModuleLocator $locator)
    {
        $this->locator = $locator;
        $this->path = '';
    }

    /**
     * Executes a remote call to the Module server.
     *
     * @access protected
     * @param string $method Remote Module method name to call.
     * @param array $args Optional arguments.
     * @return mixed Remote object result value.
     * @since 5.1
     */
    protected function moduleXmlRpcCall($method, $args = null)
    {
        $method = strtolower($method);
        $request_body = xmlrpc_encode_request($method, $args);
        $content_len = strlen($request_body);

        $fd = @fsockopen($this->locator->getHost(), $this->locator->getPort(), $errno, $errstr, 10);
        if (!$fd) {
            throw new ModuleException('Could not open socket for remote Module [host='.$this->locator->getHost().', port='.$this->locator->getPort().']');
        }

        $http_request = 'INVOKE '.$this->locator->getLocation()." Module/1.0\r\n";
        $http_request .= 'User-Agent: Innomatic Module server 5.1'."\r\n";
        // !!! check host and port
        $http_request .= 'Host: '.$this->locator->getHost().':'.$this->locator->getPort()."\r\n";

        if ($this->sessionId) {
            $http_request .= 'Session: '.$this->sessionId."\r\n";
        }

        if ($this->locator->getUsername() != '' and $this->locator->getPassword() != '') {
            $http_request .= 'User: '.$this->locator->getUsername()."\r\n";
            $http_request .= 'Password: '.$this->locator->getPassword()."\r\n";
            //$http_request .= 'Authorization: Basic '.base64_encode($this->locator->getUsername().':'.$this->locator->getPassword())."\r\n";
        }

        $http_request .= "Content-Type: text/xml\r\n";
        $http_request .= "Content-Length: $content_len\r\n";
        $http_request .= "\r\n";
        $http_request .= $request_body;

        if (fputs($fd, $http_request, strlen($http_request)) != strlen($http_request)) {
            throw new ModuleException('Module server connection closed too early');
        }

        $response_status = 0;
        $headers_parsed = false;
        $response_body = '';
        $response_headers = array ();
        while (!feof($fd)) {
            $line = fgets($fd, 4096);
            if ($headers_parsed == false) {
                if (($line === "\r\n") || ($line === "\n")) {
                    $headers_parsed = true;
                } else {
                    if (ereg('^([.Module/0-9]+) ([0-9]+)', $line, $regs)) {
                        $response_status = $regs[2];
                    } else {
                        $response_headers[substr($line, 0, strpos($line, ':'))] = trim(substr($line, strpos($line, ':') + 1));
                    }
                }
            } else {
                $response_body .= $line;
            }
        }
        fclose($fd);

        if (($headers_parsed == false) || ($response_status == 0)) {
            throw new ModuleException('Invalid response from Module server');
        }

        switch ($response_status) {
            case 200 :
                break;

            case 401 :
                throw new ModuleException('Not authorized');
                break;

            case 403 :
                throw new ModuleException('Forbidden');
                break;

            case 500 :
                throw new ModuleException('Internal server error');
                break;

            default :
                throw new ModuleException('Invalid return status '.$response_status.' from Module server');
        }

        if (isset ($response_headers['Session']))
            $this->sessionId = $response_headers['Session'];

        $return_value = \Innomatic\Webservices\Xmlrpc\php_xmlrpc_decode($response_body);
        if ($return_value == null) {
            throw new ModuleException('XML-RPC decode failed, invalid XML-RPC response from Module server');
        }

        // Todo: process XML-RPC errors here
        return $return_value;
    }

    /**
     * Method that is called before executing a class method.
     *
     * @access public
     * @param string $method Remote Module method name to call.
     * @param array $args Arguments.
     * @return mixed Remote object result value.
     * @since 5.1
     */
    final public function __call($method, $args)
    {
        return $this->moduleXmlRpcCall($method, $args);
    }

    protected function moduleListMethods()
    {
        return $this->moduleXmlRpcCall('system.listMethods');
    }

    protected function moduleMethodSignature($method)
    {
        return $this->moduleXmlRpcCall('system.methodSignature', $method);
    }

    protected function moduleMethodHelp($method)
    {
        return $this->moduleXmlRpcCall('system.methodSignature', $method);
    }

    protected function moduleDescribeMethods()
    {
        return $this->moduleXmlRpcCall('system.describeMethods');
    }

    protected function moduleMultiCall()
    {
        return $this->moduleXmlRpcCall('system.multiCall');
    }

    protected function moduleGetCapabilities()
    {
        return $this->moduleXmlRpcCall('system.getCapabilities');
    }
}
