<?php
namespace Innomatic\Module\Services;

/**
 * Module pinger
 * (pings other peers to retrieve load information
 * and reveal nodes' crashes in order to update the registry)
 * accepts 2 parameters:
 * 1) (int) n. of seconds for ping timeout
 * 2) (int) n. of seconds between 2 consecutives ping cicles
 *
 * @author Alex Pagnoni
 * @copyright Copyright 2005-2014 Innoteam Srl
 * @since 5.1
 */
class ModulePinger
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
     * reference to the handler of the registry
     *
     * @var ModuleRegistryHandler
     * @access protected
     * @since 5.1
     */
    protected $registryHandler;

   /**
     * registry structure.
     *
     * @var array
     * @access protected
     * @since 5.1
     */
    protected $registry;

   /**
     * the socket used to ping other peers
     *
     * @var Socket
     * @access protected
     * @see carthag.net.socket.Socket
     * @since 5.1
     */
    protected $socket;

   /**
     * after "ping_timeout" seconds, the pinged peer is supposed to be down
     *
     * @var int
     * @access protected
     * @since 5.1
     */
    protected $ping_timeout;

   /**
     * n� of seconds between 2 consecutives ping cicles
     *
     * @var int
     * @access protected
     * @since 5.1
     */
    protected $ping_interval;

   /**
     * tells if, after a ping cicle, the ModuleServiceSocketHandler has to refresh the registry
     *
     * @var boolean
     * @access protected
     * @since 5.1
     */
    protected $refresh_needed;

   /**
     * tells if is running the shuting down procedure
     *
     * @var boolean
     * @access protected
     * @since 5.1
     */
    protected $shutdown;

    public function main($args)
    {
           if (!isset ($args[2])) $args[2] = 15; //default ping timeout = 15 sec.
           $this->ping_timeout = $args[2];
           if (!isset ($args[3])) $args[3] = 30; //default wait time = 30 sec.
           $this->ping_interval = $args[3];
           $this->refresh_needed = false;
           $this->shutdown = false;
           $this->authenticator = \Innomatic\Module\ModuleServerAuthenticator::instance('ModuleServerAuthenticator');
           $this->socket = new \Innomatic\Net\Socket\Socket();
        $this->registry = array ();
        $this->registryHandler = new ModuleRegistryHandler();
        $this->registryHandler->parseRegistry();
        $this->registry = $this->registryHandler->getRegistry();

        print("Pinger ready\n");
        sleep ($this->ping_interval);
        while ($this->shutdown==false) {
            $this->pingCicle();
            if ($this->refresh_needed == true) {
                $this->force_refresh();
                $this->refresh_needed = false;
            }
            sleep ($this->ping_interval);
        }
        print("Module Pinger shuting down."."\n");
    }

   /**
     * pings all peers in the registry
     *
     * @access protected
     * @since 5.1
     */
     protected function pingCicle()
     {
         print("Module Pinger: ping cicle started."."\n");
        for($i=0; $i<count($this->registry); $i++) {
            $request = '';
            $result = '';
            $address = $this->registry[$i]['address'];
            $port = $this->registry[$i]['ping_port'];
            print("Ping: ".$address.":".$port."\n");
            try {
                $this->socket->connect($address, $port, null, $this->ping_timeout);
                $request = 'PING Module/1.1'."\r\n";
                $request .= 'User: pinger'."\r\n";
                $request .= 'Password: '.$this->authenticator->getPassword('pinger')."\r\n";
                $this->socket->write($request);
                $result = $this->socket->readAll();
                $this->socket->disconnect();
                print($result);
            } catch (\Innomatic\Net\Socket\SocketException $e) {
                print("Peer ".$address.":".$port. " failure!"."\n");
                $this->deletePeerFromRegistry($address, $port);
            }
        }
    }

   /**
     * invoked after a ping timeout, deletes a peer from the registry
     *
     * @access protected
     * @param string $host the address of the peer to delete
     * @param string $port the port of the peer to delete
     * @since 5.1
     */
    protected function deletePeerFromRegistry($address, $port)
    {
        $context = \Innomatic\Module\Server\ModuleServerContext::instance('\Innomatic\Module\Server\ModuleServerContext');
        $xmldoc = simplexml_load_file($context->getHome().'core/conf/modules-netregistry.xml');
//var_dump($xmldoc);
        $xml_string = "<?xml version=\"1.0\"?>\n<registry>\n\t";

        foreach ($xmldoc->node as $node) {
            if ($node->address != $address || $node->ping_port != $port) {
                $xml_string .= $node->asXML();
            }
        }
        $xml_string .= "\n</registry>";
        print($xml_string);
        $context = \Innomatic\Module\Server\ModuleServerContext::instance('\Innomatic\Module\Server\ModuleServerContext');
        $file_registry = fopen($context->getHome().'core/conf/modules-netregistry.xml', 'x');
        if($file_registry==false) {
            unlink($context->getHome().'core/conf/modules-netregistry.xml');
            $file_registry = fopen($context->getHome().'core/conf/modules-netregistry.xml', 'x');
        }
        fwrite($file_registry, $xml_string);
        fclose($file_registry);
        $this->registryHandler->parseRegistry();
        $this->registry = $this->registryHandler->getRegistry();
        print("Peer ".$address.":".$port. " deleted from registry"."\n");
        $this->refresh_needed = true;
    }

   /**
     * force the ModuleServiceSocketHandler to refresh the registry
     *
     * @access protected
     * @since 5.1
     */
    protected function force_refresh()
    {
        print("force_refresh"."\n");
        $request = '';
        $address = '127.0.0.1';
        $context = \Innomatic\Module\Server\ModuleServerContext::instance('\Innomatic\Module\Server\ModuleServerContext');
        try {
            $port = $context->getConfig()->getKey('service_port');;
            $this->socket->connect($address, $port);
            $request = 'REFRESH Module/1.1'."\r\n";
            $request .= 'User: admin'."\r\n";
            $request .= 'Password: '.$this->authenticator->getPassword('admin')."\r\n";
            $this->socket->write($request);
            $result = $this->socket->readAll();
            $this->socket->disconnect();
        } catch (\Innomatic\Net\Socket\SocketException $e) {
            print("Cannot refresh: server ".$address.":".$port. " down!"."\n");
        }
        print($result);
    }

}
