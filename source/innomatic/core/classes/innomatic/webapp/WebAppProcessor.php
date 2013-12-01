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

require_once('innomatic/webapp/WebAppRequest.php');
require_once('innomatic/webapp/WebAppResponse.php');
require_once('innomatic/webapp/WebApp.php');

class WebAppProcessor
{
    private $webapp;
    private $request;
    private $response;

    public function __construct()
    {
        $this->request = new WebAppRequest();
        $this->response = new WebAppResponse();
    }

    /**
     * Runs the processor
     */
    public function process(WebApp $wa)
    {
        $this->webapp = $wa;
        // Builds request
        $this->parseRequest();
        $this->response->setHeader('Date', gmdate('D, d M Y H:i:s T'));
        // Selects the right webapp handler
        $handler = $this->mapHandler($this->request);
        if ($handler instanceof WebAppHandler) {
            // Webapp handler found, builds the response
            $this->response->startBuffer();
            try {
                $handler->service($this->request, $this->response);
            } catch (Exception $e) {
                $this->response->sendError(WebAppResponse::SC_INTERNAL_SERVER_ERROR, get_class($e), $e);
            }
        }

        if ($this->response->isError()) {
            // Intercepts any error and outputs a report
            $this->response->resetBuffer();
            $this->report($this->request, $this->response);
        } else {
            // If all ok, prints the response
            $this->response->flushBuffer();
        }
    }

    /**
     * Reports errors
     */
    public function report(WebAppRequest $req, WebAppResponse $res)
    {
        require_once('innomatic/core/RootContainer.php');
        $statusReportsTree = simplexml_load_file(RootContainer::instance('rootcontainer')->getHome().'innomatic/core/conf/webapp/statusreports.xml');
        $statusReports = array();
        foreach($statusReportsTree->status as $status) {
            $statusReports[sprintf('%s', $status->statuscode)] = sprintf('%s', $status->statusreport);
        }

        require_once('innomatic/php/PHPTemplate.php');
        $tpl = new PHPTemplate(RootContainer::instance('rootcontainer')->getHome().'innomatic/core/conf/webapp/report.tpl.php');
        $tpl->set('status_code', $res->getStatus());
        $tpl->set('message', htmlspecialchars($res->getMessage()));
        $tpl->set('report', str_replace('{0}', $res->getMessage(), isset($statusReports[$res->getStatus()]) ? $statusReports[$res->getStatus()] : ''));
        $tpl->set('title', $req->getServerName());
        $tpl->set('server_info', $req->getServerName());
        $tpl->set('e', $res->getException());
        $res->startBuffer();
        echo $tpl->parse();
        $res->flushBuffer();
    }

    /**
     * Builds the webapp request
     */
    private function parseRequest()
    {
        // URL path part
        //TODO ipotizza che ci sia il receiver nella URL
        $url_path = substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '/'));
        $this->request->setUrlPath($url_path);

        // Remote address
        $this->request->setRemoteAddr($_SERVER['REMOTE_ADDR']);

        // Server name
        list($servername) = explode(':', $_SERVER['HTTP_HOST']);
        $this->request->setServerName($servername);

        // Server port
        $this->request->setServerPort($_SERVER['SERVER_PORT']);

        // Request URI
        if ($this->request->isUrlRewriteOn()) {
            $path_info = substr($_SERVER['REQUEST_URI'], strlen($url_path));
        } else {
            $path_info = $_SERVER['PATH_INFO'];
        }

        if (strpos($path_info, '?')) {
            $path_info = substr($path_info, 0, strpos($path_info, '?'));
        }

        $requestURI = $url_path.$path_info;
        require_once('innomatic/io/filesystem/DirectoryUtils.php');
        $normalizedURI = DirectoryUtils::normalize($requestURI);
        if ($url_path != '/' && $url_path == $normalizedURI) {
            $normalizedURI .= '/';
        }
        $this->request->setRequestURI($normalizedURI);

        // Request method
        $this->request->setMethod($_SERVER['REQUEST_METHOD']);

        // Request protocol
        $this->request->setProtocol($_SERVER['SERVER_PROTOCOL']);
        $this->response->setProtocol($_SERVER['SERVER_PROTOCOL']);

        // Query string
        if (strlen($_SERVER['QUERY_STRING']) > 0)
            $this->request->setQueryString($_SERVER['QUERY_STRING']);

        // Scheme and secure flag
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            $this->request->setScheme('https');
            $this->request->setSecure(true);
        } else {
            $this->request->setScheme('http');
        }

        // POST, GET and FILES parameters
        $parameters = array();
        parse_str($this->request->getQueryString(), $results);
        foreach($results as $name => $values) {
            settype($values, 'array');
            $parameters[$name] = array_values($values);
        }
        foreach($_POST as $name => $values) {
            settype($values, 'array');
            $parameters[$name] = array_values($values);
        }
        foreach($_FILES as $name => $values) {
            settype($values, 'array');
            $parameters[$name] = array_values($values);
        }
        $this->request->setParameters($parameters);

        // Input
        $this->request->setInput($_POST);

        // Accepted languages
        $value = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : 'en_US';
        $locales = array ();
        $value = str_replace(array (' ', "\t"), '', $value);
        $entries = explode(',', $value);
        foreach($entries as $entry) {
            // extract the quality factor
            $quality = 1.0;
            if (($semi = strpos($entry, ';q=')) !== false) {
                $quality = (float) substr($entry, $semi +3);
                $entry = substr($entry, 0, $semi);
            }

            $localeParts = explode('-', $entry);
            $language = $locale = $localeParts[0];
            $country = '';
            $varient = '';
            if (count($localeParts) > 1) {
                $country = $localeParts[1];
                $locale .= '_'.strtoupper($country);

                if (count($localeParts) > 2) {
                    $variant = $localeParts[2];
                    $locale .= '_'.$variant;
                }
            }

            $key = - $quality;
            if (empty($locales["$key"])) {
                $locales["$key"] = array ();
            }

            $locales["$key"][] = $locale;
        }
        ksort($locales);
        foreach($locales as $localeSet) {
            foreach($localeSet as $locale) {
                $this->request->addLocale($locale);
            }
        }
    }

    /**
     * Maps the request to the right webapp handler
     */
    public function mapHandler(WebAppRequest $request)
    {
        $url_path = $request->getUrlPath();
        // NOTE: the requestURI comes from the pathInfo that follows our fusebox file (index.php)
        // initially the pathInfo is null since we don't know what constitutes the extra information
        // until we section off the webapp mapping, and then we can say that the rest is pathInfo
        $requestURI = $request->getRequestURI();
        $relativeURI = substr($requestURI, strlen($url_path));
        if ($relativeURI == '/'.WebAppRequest::RECEIVER) {
            $relativeURI = '/';
        }

        $fqclassname = null;
        $webAppPath = $relativeURI;
        $pathInfo = null;
        $handlerName = null;
        $match = 'none';

        // Rule 1: Exact Match
        if (is_null($fqclassname)) {
            if ($relativeURI != '/') {
                $handlerName = $this->webapp->getHandlerMapping($relativeURI);
            }

            if (!is_null($handlerName)) {
                $fqclassname = $this->webapp->getHandler($handlerName);
            }

            if (!is_null($fqclassname)) {
                $webAppPath = $relativeURI;
                $pathInfo = null;
                $match = 'exact';
            }
        }

        // Rule 2: Prefix Match
        if (is_null($fqclassname)) {
            $tmpPath = $relativeURI;
            while (true) {
                $handlerName = $this->webapp->getHandlerMapping($tmpPath."/*");
                if (!is_null($handlerName)) {
                    $fqclassname = $this->webapp->getHandler($handlerName);
                }

                if (!is_null($fqclassname)) {
                    $pathInfo = substr($relativeURI, strlen($tmpPath));
                    if (strlen($pathInfo) == 0) {
                        $pathInfo = null;
                    }

                    $match = 'prefix';
                    break;
                }

                $slash = strrpos($tmpPath, '/');
                if ($slash === false) {
                    break;
                }

                $tmpPath = substr($tmpPath, 0, $slash);
            }
        }

        // Rule 3: Extension Match
        if (is_null($fqclassname)) {
            $slash = strrpos($relativeURI, '/');
            if ($slash !== false) {
                $last = substr($relativeURI, $slash);
                $period = strrpos($last, '.');
                if ($period !== false) {
                    $pattern = '*'.substr($last, $period);
                    $handlerName = $this->webapp->getHandlerMapping($pattern);
                    if (!is_null($handlerName)) {
                        $fqclassname = $this->webapp->getHandler($handlerName);
                    }

                    if (!is_null($fqclassname)) {
                        $webAppPath = $relativeURI;
                        $pathInfo = null;
                        $match = 'extension';
                    }
                }
            }
        }

        // Rule 4: Default Match
        if (is_null($fqclassname)) {
            $handlerName = $this->webapp->getHandlerMapping('/');
            if (!is_null($handlerName)) {
                $fqclassname = $this->webapp->getHandler($handlerName);
            }

            if (!is_null($fqclassname)) {
                $webAppPath = $relativeURI;
                //$pathInfo = $relativeURI;
                $pathInfo = null;
                $match = 'default';
            }
        }

        //echo 'Match: '.$match.' - Name: '.$handlerName.' - Web app path: '.$webAppPath.' - Path info: '.$pathInfo."<br>\n";
        $request->setWebAppPath($webAppPath);
        $request->setPathInfo($pathInfo);

        if ($fqclassname == null) {
            $this->response->sendError(WebAppResponse::SC_INTERNAL_SERVER_ERROR, 'No matching handler found for current request');
            return;
        }

        if (!@include_once($fqclassname)) {
            $this->response->sendError(WebAppResponse::SC_INTERNAL_SERVER_ERROR, 'No handler found');
            return;
        }

        // Loads Webapp Handler class
        $classname = substr($fqclassname, strrpos($fqclassname, '/') + 1, -4);
        if (!class_exists($classname, false)) {
            $this->response->sendError(WebAppResponse::SC_INTERNAL_SERVER_ERROR, 'Malformed handler found');
            return;
        }

        // Instantiate Webapp Handler
        $handler = new $classname();
        $handler->setInitParameters($this->webapp->getHandlerParameters($handlerName));
        $handler->init();
        return $handler;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function getRequest()
    {
        return $this->request;
    }
}
