<?php 

/**
 * Class that represents an outcoming response from the Module server.
 *
 * @author Alex Pagnoni <alex.pagnoni@innoteam.it>
 * @copyright Copyright 2004-2013 Innoteam S.r.l.
 * @since 5.1
 */
class ModuleServerResponse {
    /**
     * Response buffer.
     *
     * @var string
     * @access protected
     * @since 5.1
     */
    protected $buffer;
    /**
     * Array of response headers.
     *
     * @var array
     * @access protected
     * @since 5.1
     */
    protected $headers;
    const ERROR_UNKOWN = -1000;
    const ERROR_XMLRPC_NOTINSTALLED = -1001;
    const ERROR_INIT_FAILED = -1002;
    const ERROR_CLASSNAME_MISSING = -1003;
    const ERROR_INVALID_CLASSNAME = -1004;
    const ERROR_INVALID_CLASS = -1005;
    const ERROR_OBJCREATION_FAILED = -1006;
    const ERROR_INVALID_METHOD = -1007;
    const ERROR_XMLRPC_ERROR = -1009;
    /**
     * Status code (200) indicating the request succeeded normally.
     */
    const SC_OK = 200;
    /**
     * Status code (202) indicating that a request was accepted for
     * processing, but was not completed.
     */
    const SC_ACCEPTED = 202;
    /**
     * Status code (204) indicating that the request succeeded but that
     * there was no new information to return.
     */
    const SC_NO_CONTENT = 204;
    /**
     * Status code (400) indicating the request sent by the client was
     * syntactically incorrect.
     */
    const SC_BAD_REQUEST = 400;
    /**
     * Status code (401) indicating that the request requires Module
     * authentication.
     */
    const SC_UNAUTHORIZED = 401;
    /**
     * Status code (403) indicating the server understood the request
     * but refused to fulfill it.
     */
    const SC_FORBIDDEN = 403;
    /**
     * Status code (404) indicating that the requested resource is not
     * available.
     */
    const SC_NOT_FOUND = 404;
    /**
     * Status code (500) indicating an error inside the Module server
     * which prevented it from fulfilling the request.
     */
    const SC_INTERNAL_SERVER_ERROR = 500;
    /**
     * Status code (501) indicating the Module server does not support
     * the functionality needed to fulfill the request.
     */
    const SC_NOT_IMPLEMENTED = 501;
    /**
     * Status code (503) indicating that the Module server is
     * temporarily overloaded, and unable to handle the request.
     */
    const SC_SERVICE_UNAVAILABLE = 503;
    /**
     * Status code (505) indicating that the server does not support
     * or refuses to support the Module protocol version that was used
     * in the request message.
     */
    const SC_Module_VERSION_NOT_SUPPORTED = 505;

    /**
     * Sets buffer content.
     *
     * @access public
     * @since 5.1
     * @param string $buffer Buffer content.
     * @return void
     */
    public function setBuffer($buffer) {
        $this->buffer = $buffer;
    }

    /**
     * Retrieves buffer content.
     *
     * @access public
     * @since 5.1
     * @return string
     */
    public function &getBuffer() {
        return $this->buffer;
    }

    /**
     * Adds an header to the headers array.
     *
     * @access public
     * @since 5.1
     * @param string $header Header to be added.
     * @return void
     */
    public function addHeader($header) {
        $this->headers[] = $header;
    }

    /**
     * Retrieves the full list of headers in text format, ready to be added
     * to the response and sent.
     *
     * @access public
     * @since 5.1
     * @return string
     */
    public function getHeaders() {
        $headers = '';
        // !!! optimize with php functions
        foreach ($this->headers as $header) {
            $headers .= $header."\n";
        }
        reset($this->headers);
        return $headers;
    }

    /**
     * Retrieves the full response text with headers.
     *
     * @access public
     * @since 5.1
     * @return string
     */
    public function getResponse() {
        return $this->getHeaders()."\n".$this->getBuffer();
    }

    /**
     * Format the response as a warning in XML-RPC protocol.
     *
     * @access public
     * @since 5.1
     * @param string $status Status code, as in ModuleServerResponse::SC_* constants.
     * @param string $errstr Error string.
     * @param integer $errcode Error code, as in ModuleServerResponse::ERROR_* constants.
     * @return void
     */
    public function sendWarning($status, $errstr, $errcode = self::ERROR_UNKOWN) {
        $this->addHeader('Module/1.0 '.$status);
        $buffer = "<?xml version=\"1.0\"?>\n";
        $buffer .= "<methodResponse>\n";
        $buffer .= "<fault>\n";
        $buffer .= "<value>\n";
        $buffer .= "<struct>\n";
        $buffer .= "<member>\n";
        $buffer .= "<name>faultCode</name>\n";
        $buffer .= '<value><int>'.$errcode.'</int></value>'."\n";
        $buffer .= "</member>\n";
        $buffer .= "<member>\n";
        $buffer .= "<name>faultString</name>\n";
        $buffer .= '<value><string>'.$errstr.'</string></value>'."\n";
        $buffer .= "</member>\n";
        $buffer .= "</struct>\n";
        $buffer .= "</value>\n";
        $buffer .= "</fault>\n";
        $buffer .= "</methodResponse>\n";
        $this->setBuffer($buffer);
    }
}

?>