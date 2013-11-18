<?php
/**
 * Innomatic
 *
 * LICENSE 
 * 
 * This source file is subject to the new BSD license that is bundled 
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2012 Innoteam Srl
 * @license    http://www.innomatic.org/license/   BSD License
 * @link       http://www.innomatic.org
 * @since      Class available since Release 5.0
*/

class WebAppResponse {
    private $committed = false;
    private $status = null;
    private $characterEncoding = 'UTF-8';
    private $headers = array ();
    private $format = 'D, d M Y H:i:s T';
    private $contentType = null;
    private $contentLength = null;
    private $cookies = array ();
    private $message = null;
    private $buffer = null;
    private $bufferActive = false;
    private $error = false;
    private $exception = null;
	private $redirect = false;
        
    /*
     * Server status codes; see RFC 2068.
     */

    /**
     * Status code (100) indicating the client can continue.
     */
    const SC_CONTINUE = 100;
    /**
     * Status code (101) indicating the server is switching protocols
     * according to Upgrade header.
     */
    const SC_SWITCHING_PROTOCOLS = 101;
    /**
     * Status code (200) indicating the request succeeded normally.
     */
    const SC_OK = 200;
    /**
     * Status code (201) indicating the request succeeded and created
     * a new resource on the server.
     */
    const SC_CREATED = 201;
    /**
     * Status code (202) indicating that a request was accepted for
     * processing, but was not completed.
     */
    const SC_ACCEPTED = 202;
    /**
     * Status code (203) indicating that the meta information presented
     * by the client did not originate from the server.
     */
    const SC_NON_AUTHORITATIVE_INFORMATION = 203;
    /**
     * Status code (204) indicating that the request succeeded but that
     * there was no new information to return.
     */
    const SC_NO_CONTENT = 204;
    /**
     * Status code (205) indicating that the agent <em>SHOULD</em> reset
     * the document view which caused the request to be sent.
     */
    const SC_RESET_CONTENT = 205;
    /**
     * Status code (206) indicating that the server has fulfilled
     * the partial GET request for the resource.
     */
    const SC_PARTIAL_CONTENT = 206;
    /**
     * Status code (300) indicating that the requested resource
     * corresponds to any one of a set of representations, each with
     * its own specific location.
     */
    const SC_MULTIPLE_CHOICES = 300;
    /**
     * Status code (301) indicating that the resource has permanently
     * moved to a new location, and that future references should use a
     * new URI with their requests.
     */
    const SC_MOVED_PERMANENTLY = 301;
    /**
     * Status code (302) indicating that the resource has temporarily
     * moved to another location, but that future references should
     * still use the original URI to access the resource.
     *
     * This definition is being retained for backwards compatibility.
     * SC_FOUND is now the preferred definition.
     */
    const SC_MOVED_TEMPORARILY = 302;
    /**
    * Status code (302) indicating that the resource reside
    * temporarily under a different URI. Since the redirection might
    * be altered on occasion, the client should continue to use the
    * Request-URI for future requests.(HTTP/1.1) To represent the
    * status code (302), it is recommended to use this variable.
    */
    const SC_FOUND = 302;
    /**
     * Status code (303) indicating that the response to the request
     * can be found under a different URI.
     */
    const SC_SEE_OTHER = 303;
    /**
     * Status code (304) indicating that a conditional GET operation
     * found that the resource was available and not modified.
     */
    const SC_NOT_MODIFIED = 304;
    /**
     * Status code (305) indicating that the requested resource
     * <em>MUST</em> be accessed through the proxy given by the
     * <code><em>Location</em></code> field.
     */
    const SC_USE_PROXY = 305;
    /**
    * Status code (307) indicating that the requested resource 
    * resides temporarily under a different URI. The temporary URI
    * <em>SHOULD</em> be given by the <code><em>Location</em></code> 
    * field in the response.
    */
    const SC_TEMPORARY_REDIRECT = 307;
    /**
     * Status code (400) indicating the request sent by the client was
     * syntactically incorrect.
     */
    const SC_BAD_REQUEST = 400;
    /**
     * Status code (401) indicating that the request requires HTTP
     * authentication.
     */
    const SC_UNAUTHORIZED = 401;
    /**
     * Status code (402) reserved for future use.
     */
    const SC_PAYMENT_REQUIRED = 402;
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
     * Status code (405) indicating that the method specified in the
     * <code><em>Request-Line</em></code> is not allowed for the resource
     * identified by the <code><em>Request-URI</em></code>.
     */
    const SC_METHOD_NOT_ALLOWED = 405;
    /**
     * Status code (406) indicating that the resource identified by the
     * request is only capable of generating response entities which have
     * content characteristics not acceptable according to the accept
     * headers sent in the request.
     */
    const SC_NOT_ACCEPTABLE = 406;
    /**
     * Status code (407) indicating that the client <em>MUST</em> first
     * authenticate itself with the proxy.
     */
    const SC_PROXY_AUTHENTICATION_REQUIRED = 407;
    /**
     * Status code (408) indicating that the client did not produce a
     * request within the time that the server was prepared to wait.
     */
    const SC_REQUEST_TIMEOUT = 408;
    /**
     * Status code (409) indicating that the request could not be
     * completed due to a conflict with the current state of the
     * resource.
     */
    const SC_CONFLICT = 409;
    /**
     * Status code (410) indicating that the resource is no longer
     * available at the server and no forwarding address is known.
     * This condition <em>SHOULD</em> be considered permanent.
     */
    const SC_GONE = 410;
    /**
     * Status code (411) indicating that the request cannot be handled
     * without a defined <code><em>Content-Length</em></code>.
     */
    const SC_LENGTH_REQUIRED = 411;
    /**
     * Status code (412) indicating that the precondition given in one
     * or more of the request-header fields evaluated to false when it
     * was tested on the server.
     */
    const SC_PRECONDITION_FAILED = 412;
    /**
     * Status code (413) indicating that the server is refusing to process
     * the request because the request entity is larger than the server is
     * willing or able to process.
     */
    const SC_REQUEST_ENTITY_TOO_LARGE = 413;
    /**
     * Status code (414) indicating that the server is refusing to service
     * the request because the <code><em>Request-URI</em></code> is longer
     * than the server is willing to interpret.
     */
    const SC_REQUEST_URI_TOO_LONG = 414;
    /**
     * Status code (415) indicating that the server is refusing to service
     * the request because the entity of the request is in a format not
     * supported by the requested resource for the requested method.
     */
    const SC_UNSUPPORTED_MEDIA_TYPE = 415;
    /**
     * Status code (416) indicating that the server cannot serve the
     * requested byte range.
     */
    const SC_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    /**
     * Status code (417) indicating that the server could not meet the
     * expectation given in the Expect request header.
     */
    const SC_EXPECTATION_FAILED = 417;
    /**
     * Status code (500) indicating an error inside the HTTP server
     * which prevented it from fulfilling the request.
     */
    const SC_INTERNAL_SERVER_ERROR = 500;
    /**
     * Status code (501) indicating the HTTP server does not support
     * the functionality needed to fulfill the request.
     */
    const SC_NOT_IMPLEMENTED = 501;
    /**
     * Status code (502) indicating that the HTTP server received an
     * invalid response from a server it consulted when acting as a
     * proxy or gateway.
     */
    const SC_BAD_GATEWAY = 502;
    /**
     * Status code (503) indicating that the HTTP server is
     * temporarily overloaded, and unable to handle the request.
     */
    const SC_SERVICE_UNAVAILABLE = 503;
    /**
     * Status code (504) indicating that the server did not receive
     * a timely response from the upstream server while acting as
     * a gateway or proxy.
     */
    const SC_GATEWAY_TIMEOUT = 504;
    /**
     * Status code (505) indicating that the server does not support
     * or refuses to support the HTTP protocol version that was used
     * in the request message.
     */
    const SC_HTTP_VERSION_NOT_SUPPORTED = 505;
    
    public function __construct() {
        $this->status = self::SC_OK;
    }
    
    public function setCharacterEncoding($encoding) {
        $this->characterEncoding = $encoding;
    }

    public function getCharacterEncoding() {
        return $this->characterEncoding;
    }

    public function setProtocol($protocol) {
        $this->protocol = $protocol;
    }

    public function getProtocol() {
        return $this->protocol;
    }

    public function setContentLength($len) {
        if ($this->committed)
            return;
        $this->contentLength = $len;
    }

    public function getContentLength() {
        return $this->contentLength;
    }

    public function getContentCount() {
        return ob_get_length();
    }

    public function setContentType($type) {
        if ($this->committed)
            return;
        $this->contentType = $type;
    }

    public function getContentType() {
        return $this->contentType;
    }

    public function addCookie(& $cookie) {
        if ($this->committed)
            return;
        $this->cookies[] = &$cookie;
    }

    public function &getCookies() {
        return $this->cookies;
    }

    public function setHeader($header, $value) {
        if ($this->committed) {
            return;
        }

        $this->headers[$header] = array ($value);
    }

    public function addHeader($header, $value) {
        if ($this->committed) {
            return;
        }

        $values = &$this->headers[$header];
        if (!isset ($values))
            $values = array ();

        $values[] = $value;
    }

    public function addDateHeader($header, $value) {
        if ($this->committed)
            return;
        $this->addHeader($header, gmdate($this->format, $value));
    }

    public function setDateHeader($header, $value) {
        if ($this->committed)
            return;
        $this->setHeader($header, gmdate($this->format, $value));
    }

    public function getHeader($name) {
        if (!isset ($this->headers[$name])) {
            return null;
        }

        return $this->headers[$name][0];
    }

    public function getHeaderNames() {
        return array_keys($this->headers);
    }

    public function getHeaderValues() {
        return array_values($this->headers);
    }

    public function containsHeader($name) {
        return isset ($this->headers[$name]) and count($this->headers[$name]) > 0;
    }

    public function setStatus($status, $message = null) {
        $this->status = $status;
        $this->message = is_null($message) ? $this->getStatusMessage($status) : $message;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getMessage() {
        return $this->message;
    }

    public function getStatusMessage($status) {
        switch ($status) {
            case self::SC_CONTINUE :
                return 'Continue';
            case self::SC_OK :
                return 'OK';
            case self::SC_MOVED_PERMANENTLY :
                return 'Moved Permanently';
            case self::SC_MOVED_TEMPORARILY :
                return 'Moved Temporarily';
            case self::SC_BAD_REQUEST :
                return 'Bad Request';
            case self::SC_UNAUTHORIZED :
                return 'Unauthorized';
            case self::SC_FORBIDDEN :
                return 'Forbidden';
            case self::SC_NOT_FOUND :
                return 'Not Found';
            case self::SC_INTERNAL_SERVER_ERROR :
                return 'Internal Server Error';
            case self::SC_SERVICE_UNAVAILABLE :
                return 'Service Unavailable';
            default:
                return 'HTTP Response Status '.$status;
        }
    }

    public function isCommitted() {
        return $this->committed;
    }

    public function reset() {
        $this->headers = array ();
        $this->cookies = array ();
        $this->message = null;
        $this->resetBuffer();
        $this->status = self::SC_OK;
    }

    public function startBuffer() {
        if (!$this->bufferActive) {
            ob_start();
        }
    }

    public function resetBuffer() {
        if ($this->committed or ob_get_length() == 0) {
            return;
        }
        ob_clean();
    }

    public function flushBuffer() {
        if (!$this->committed) {
            $this->sendHeaders();
        }
        $this->buffer .= ob_get_contents();
        ob_end_clean();

	if (!$this->redirect) {
	    echo $this->buffer;
	}
        $this->bufferActive = false;
    }

    public function sendHeaders() {
        if ($this->committed) {
            return;
        }
        $status = $this->getProtocol().' '.$this->status. (is_null($this->message) ? '' : ' '.$this->message);
        header($status);
        if (!is_null($this->contentType)) {
            $content_type = 'Content-Type: '.$this->contentType;
            if (!is_null($this->characterEncoding)) {
                $content_type .= '; charset='.$this->characterEncoding;
            }
            header($content_type);
        }
        if (!is_null($this->contentLength)) {
            header('Content-Length: '.$this->contentLength);
        }
        foreach ($this->headers as $header => $values) {
            foreach ($values as $value) header($header.': '.$value, false);
        }
        $this->committed = true;
    }

    public function sendRedirect($location) {
        if ($this->committed)
            return;
        $this->resetBuffer();
        $this->setStatus(self::SC_MOVED_TEMPORARILY);
        $this->setHeader('Location', $location);
$this->redirect = true; 
   }

    public function sendError($sc, $msg = null, Exception $exception = NULL) {
        if ($this->committed) {
            return;
        }
        $this->setError();
        $this->setStatus($sc, $msg);
        $this->exception = $exception;
        $this->resetBuffer();
    }

    public function setError() {
        $this->error = true;
    }

    public function isError() {
        return $this->error;
    }
    
    public function getException() {
        return $this->exception;
    }
}
