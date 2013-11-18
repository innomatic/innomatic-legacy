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

class WebAppRequest {
    private $parameters;
    private $attributes;
    private $remoteAddr;
    private $sicure = false;
    private $requestURI;
    private $serverName;
    private $serverPort;
    private $scheme;
    private $protocol;
    private $queryString;
    private $input;
    private $locales = array ();
    private $defaultLocale = 'en_US';
    private $pathInfo;
    private $webAppPath;
    private $urlPath;
    const RECEIVER = 'index.php';
    const RECEIVER_LENGHT = 9;
    
    public function getParameter($name) {
        return isset ($this->parameters[$name]) ? $this->parameters[$name][0] : null;
    }

    public function getParameterValues($name) {
        return isset ($this->parameters[$name]) ? $this->parameters[$name] : null;
    }

    public function getParameterNames() {
        return array_keys($this->parameters);
    }

    public function getParameterMap() {
        return $this->parameters;
    }

    public function setParameter($name, &$value) {
        if (is_null($value)) {
            $this->removeParameter($name);
            return;
        }

        $this->parameters[$name] = &$value;
    }

    public function setParameters(&$parameters) {
        $this->parameters = &$parameters;
    }

    public function removeParameter($name) {
        unset ($this->parameters[$name]);
    }

    public function parameterExists($name) {
        return isset ($this->parameters[$name]);
    }

    public function setAttribute($attr, $value) {
        $this->attributes[$attr] = $value;
    }

    public function getAttribute($attr) {
        return isset ($this->attributes[$attr]) ? $this->attributes[$attr] : null;
    }

    public function removeAttribute($attr) {
        if (isset ($this->attributes[$attr]))
            unset ($this->attributes[$attr]);
    }

    public function setMethod($method) {
        $this->method = $method;
    }

    public function getMethod() {
        return $this->method;
    }

    public function getSession() {
        // TODO
        //return WebAppSession::instance('webappsession');
    }

    public function getRequestURI() {
        return $this->requestURI;
    }

    public function setRequestURI($requestURI) {
        $this->requestURI = $requestURI;
    }

    public function isSecure() {
        return $this->secure;
    }

    public function setSecure($secure) {
        $this->secure = $secure;
    }

    public function getRequestURL() {
        $url = $this->scheme.'://'.$this->getServerName();
        if (($this->scheme == 'http' && $this->serverPort != 80) || ($this->scheme == 'https' && $this->serverPort != 443))
            $url .= ':'.$this->serverPort;
        return $url .= $this->getRequestURI();
    }

    public function getServerName() {
        return $this->serverName;
    }

    public function setServerName($serverName) {
        $this->serverName = $serverName;
    }

    public function getServerPort() {
        return $this->serverPort;
    }

    public function setServerPort($serverPort) {
        $this->serverPort = $serverPort;
    }

    public function getScheme() {
        return $this->scheme;
    }

    public function setScheme($scheme) {
        $this->scheme = $scheme;
    }

    public function getProtocol() {
        return $this->protocol;
    }

    public function setProtocol($protocol) {
        $this->protocol = $protocol;
    }

    public function getQueryString() {
        return $this->queryString;
    }

    public function setQueryString($queryString) {
        $this->queryString = $queryString;
    }

    public function &getInput() {
        return $this->input;
    }

    public function setInput(&$input) {
        $this->input = &$input;
    }

    public function getRemoteHost() {
        return gethostbyname($this->remoteAddr);
    }

    public function getRemoteAddr() {
        return $this->remoteAddr;
    }

    public function setRemoteAddr($remoteAddr) {
        $this->remoteAddr = $remoteAddr;
    }

    public function addLocale($locale) {
        $this->locales[] = $locale;
    }

    public function getLocale() {
        return count($this->locales) > 0 ? $this->locales[0] : $this->defaultLocale;
    }

    public function getLocales() {
        return count($this->locales) > 0 ? $this->locales : $this->defaultLocale;
    }

    /**
     * Returns the information after the script name and before the query string
     * If the uri is /webapp/index.php/hello.do?foo=bar it would return /hello.do
     * @return string
     */
    public function getPathInfo() {
        return $this->pathInfo;
    }

    public function setPathInfo($pathInfo) {
        $this->pathInfo = $pathInfo;
    }

    /**
     * Returns any extra path information after the webapp name but before the
     * query string, and translates it to a real path.
     *
     * @return string
     */
    public function getPathTranslated($baseDir) {
        if (is_null($this->pathInfo))
            return null;
        return $baseDir.$this->pathInfo;
    }

    /**
     * Returns the value of the specified request header. If the request did
     * not include a header of the specified name, this method returns null.
     * @return string
     */
    function getHeader($name) {
        return isset ($this->headers[$name]) ? $this->headers[$name] : null;
    }

    /**
     * Return the cookies as an array
     * @return array
     */
    function &getCookies() {
        return $_COOKIE;
    }

    /**
     * Returns the login of the user making this request, if the user has been
     * authenticated, or null if the user has not been authenticated.
     * @return string
     */
    function getWebServicesUser() {
        return null;
    }

    public function setWebAppPath($path) {
        $this->webAppPath = $path;
    }
    
    public function getWebAppPath($controller = false) {
        return $controller ? $this->generateControllerPath($this->webAppPath) : $this->webAppPath;
    }
    
    public function setUrlPath($url_path) {
        $this->urlPath = $url_path;
    }
    
    public function getUrlPath($controller = false) {
        return $controller ? $this->generateControllerPath($this->urlPath) : $this->urlPath;
    }
    
    /**
     * The controller path is that base url that routes requests to the
     * webapp container. The context-param controlAllResources is checked to see if
     * apache is handling the serving of these resources (no passthru to the
     * container) or the DefaultWebAppHAndler is going to serve them up.
     *
     * @param $context string The context path
     * @param $requiresDispatch boolean (optional) instructs whether or not the
     *        path must always go through the container (perhaps a PHP file or a
     *        webapp path)
     *
     */
    public function generateControllerPath($webAppPath, $requiresDispatch = true) {
        $useContainer = true;

        // check to see if we need to hand this off directly or go through the
        // front controller
        if (!$requiresDispatch) {
            // TODO $useContainer = (bool)$this->webapp->getInitParameter('controlAllResources');
        }

        if ($useContainer) {
            if (!$this->isUrlRewriteOn()) {
                // TODO $receiver = $this->webapp->getInitParameter('receiverfile');
                // Temporary fix:
                $receiver = '';
                $webAppPath .= '/'. (strlen($receiver) ? $receiver : self::RECEIVER);
            }
        }

        return $webAppPath;
    }

    public function isUrlRewriteOn() {
        return WebAppContainer::instance('webappcontainer')->getCurrentWebApp()->getInitParameter('useRewrite') == 'true' ? true : false;
        /*
        if (substr($_SERVER['REQUEST_URI'], 0, strlen($_SERVER['SCRIPT_NAME'])) == $_SERVER['SCRIPT_NAME']
            or $_SERVER['REQUEST_URI'].self::RECEIVER == $_SERVER['SCRIPT_NAME']) {
            return false;
        } else {
            return false;
        }
*/
    }
}
