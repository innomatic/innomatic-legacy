<?php
namespace Innomatic\Module\Server;

/**
 * Class that represents an incoming request to the Module server.
 *
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 * @copyright Copyright 2004-2014 Innomatic Company
 * @since 5.1
 */
class ModuleServerRequest
{
    /**
     * Command to be executed.
     *
     * @var string
     * @since 5.1
     */
    protected $command;
    /**
     * XML-RPC payload.
     *
     * @var string
     * @since 5.1
     */
    protected $payload;
    /**
     * Array of request headers.
     *
     * @var array
     * @since 5.1
     */
    protected $headers;

    /**
     * Sets request payload.
     *
     * @since 5.1
     * @param string $payload Payload.
     * @return void
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;
    }

    /**
     * Gets request payload.
     *
     * @since 5.1
     * @return string
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * Sets request command.
     *
     * @since 5.1
     * @param string $command Command.
     * @return void
     */
    public function setCommand($command)
    {
        $this->command = $command;
    }

    /**
     * Gets request command.
     *
     * @since 5.1
     * @return void
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * Sets an array of headers.
     *
     * @since 5.1
     * @param array $headersArray Headers.
     * @return void
     */
    public function setHeaders($headersArray)
    {
        $this->headers = $headersArray;
    }

    /**
     * Gets a specific header.
     *
     * @since 5.1
     * @param $header Header name.
     * @return array
     */
    public function getHeader($header)
    {
        return isset($this->headers[$header]) ? $this->headers[$header] : null;
    }
}
