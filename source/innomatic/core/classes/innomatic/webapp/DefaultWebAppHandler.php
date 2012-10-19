<?php    
/**
 * Innomatic
 *
 * LICENSE 
 * 
 * This source file is subject to the new BSD license that is bundled 
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2012 Innoteam S.r.l.
 * @license    http://www.innomatic.org/license/   BSD License
 * @link       http://www.innomatic.org
 * @since      Class available since Release 5.0
*/

require_once('innomatic/webapp/WebAppHandler.php');
require_once('innomatic/webapp/WebAppProcessor.php');

/**
 * @since 5.0
 * @author Alex Pagnoni <alex.pagnoni@innoteam.it>
 * @copyright Copyright 2012 Innoteam S.r.l.
 */
class DefaultWebAppHandler extends WebAppHandler
{
    protected $listings = true;
    protected $welcomeFiles = array();

    public function init()
    {
        $this->listings = $this->getInitParameter('listings');
        $this->welcomeFiles = WebAppContainer::instance('webappcontainer')->getCurrentWebApp()->getWelcomeFiles();
    }

    public function destroy()
    {
    }

    public function doGet(WebAppRequest $req, WebAppResponse $res)
    {
        $this->serveResource($req, $res, true);
    }

    public function doPost(WebAppRequest $req, WebAppResponse $res)
    {
        $this->doGet($req, $res);
    }

    protected function getRelativePath(WebAppRequest $request)
    {
        $result = $request->getPathInfo();
        require_once('innomatic/io/filesystem/DirectoryUtils.php');
        return DirectoryUtils::normalize(strlen($result) ? $result : '/');
    }

    protected function findWelcomeFile($path)
    {
        if (substr($path, -1) != '/')
            $path .= '/';

        reset($this->welcomeFiles);
        foreach ($this->welcomeFiles as $welcomefile) {
            if (file_exists(substr(WebAppContainer::instance('webappcontainer')->getCurrentWebApp()->getHome(), 0, -1).$path.$welcomefile))
                return $path.$welcomefile;
        }

        return null;
    }

    /**
     * Prefix the context path, our webapp emulator and append the request
     * parameters to the redirection string before calling sendRedirect.
     *
     * @param $request WebAppRequest
     * @param $redirectPath string
     * @return string
     * @access protected
     */
    protected function getURL(WebAppRequest $request, $redirectPath)
    {
        $result = '';

        $container = WebAppContainer::instance('webappcontainer');
        $processor = $container->getProcessor();
        $webAppPath = $request->getUrlPath();
        if (!is_null($webAppPath) && $webAppPath != '/') {
            $result = $request->generateControllerPath($webAppPath, true);
        }

        $result .= $redirectPath;

        $query = $request->getQueryString();
        if (!is_null($query)) {
            $result .= '?'.$query;
        }

        return $result;
    }

    protected function serveResource(WebAppRequest $request, WebAppResponse $response, $content)
    {
        //$webAppPath = $request->getUrlPath();

        // identify the requested resource path
        $path = $this->getRelativePath($request);
        
        $resource = substr(WebAppContainer::instance('webappcontainer')->getCurrentWebApp()->getHome(), 0, -1).$path;
        
        // make sure that this path exists on disk
        if (!file_exists($resource)) {
            $response->sendError(WebAppResponse::SC_NOT_FOUND, $request->getRequestURI());
            return;
        }

        // if this is a directory, first check welcome files...if that fails
        // see if we can do a listing
        if (is_dir($resource)) {
            $welcomeFile = $this->findWelcomeFile($path);
            if ($welcomeFile != null) {
                $response->sendRedirect($this->getURL($request, $welcomeFile));
                $response->flushBuffer();
                return;
            }

            if ($this->listings == 'false') {
                $response->sendError(WebAppResponse::SC_FORBIDDEN, $request->getRequestURI());
                return;
            } else {
                if ($content) {
                    // serve up the directory listing
                    $response->setContentType('text/html');
                    echo $this->renderListing($request, $webAppPath, $path, $resource);
                    return;
                }
            }
        }

        if ($content) {
            // we are serving up an actual file here, which we know exists
            $contentType = WebAppContainer::instance('webappcontainer')->getCurrentWebApp()->getMimeType($resource);
            if (!is_null($contentType)) {
                $response->setContentType($contentType);
            }

            $response->addDateHeader('Last-Modified', filemtime($resource));
            readfile($resource);
        }
    }

    /**
     * Generate an HTML directory list showing the contents of the directory matching
     * the path following the webapp pattern.
     *
     * @todo I really would like to see this method refactored...it is very procedural!!
     * @todo instroduce ResourceInfo or some object handler for the resource we are working with
     *
     * NOTE: I am unsure how to proceed when the webapp path is not '/'.  Tomcat leaves it
     * off for the links on each listing, which interrupts the browsing cycle.  If I add it
     * in each time, then pages which would otherwise be caught by other webapps reveal their code,
     * such as PSP files.
     */
    function renderListing($request, $webAppPath, $path, $resource)
    {
        $container = WebAppContainer::instance('webappcontainer');
        $processor = $container->getProcessor();
        $contextPath = $request->getUrlPath();
        // build our base context path with the controller info
        //$basePath = $request->generateControllerPath($contextPath) . $webAppPath;
        $basePath = $processor->generateControllerPath($contextPath);

        // directories should end in a '/'
        if (substr($path, strlen($path) - 1) != '/') {
            $path .= '/';
        }

        $out = '<html>';
        $out .= '<head>';
        $out .= '<title>Directory Listing For '.$path.'</title>';
        $out .= '<STYLE><!--
        h1 { font-family: Tahoma,Arial,sans-serif; color: white; background-color: #525D76; font-size: 22px; }
        h2 { font-family: Tahoma,Arial,sans-serif; color: white; background-color: #525D76; font-size: 16px; }
        h3 { font-family: Tahoma,Arial,sans-serif; color: white; background-color: #525D76; font-size: 14px; }
        body { font-family: Tahoma,Arial,sans-serif; color: black; background-color: white; }
        b { font-family: Tahoma,Arial,sans-serif; color: white; background-color: #525D76; }
        p { font-family: Tahoma,Arial,sans-serif; background: white; color: black; font-size: 12px; }
        a { color: black; }
        a.name { color: black; }
        hr { color : #525D76; }
        th { font-size: 17px; }
        --></STYLE>';
        $out .= '</head>';
        $out .= '<body>';
        $out .= '<h1>Directory Listing For '.$path.'</h1>';
        $out .= '<hr size="1" noshade="noshade" />';
        $out .= '<table width="100%" cellspacing="0" cellpadding="5">';
        $out .= '<tr>';
        $out .= '<th style="text-align: left;">Filename</th>';
        $out .= '<th style="text-align: center;">Size</th>';
        $out .= '<th style="text-align: right;">Last Modified</th>';
        $out .= '</tr>';

        $shade = false;
        // if the path is not '/', then we have a parent
        if ($path != '/') {
            $parentPath = substr($path, 0, strrpos(rtrim($path, '/'), '/') + 1);
            $out .= '<tr>';
            $out .= '<td style="text-align: left;"><a href="'.$basePath.$parentPath.'"><tt>';
            $out .= '[Up to parent directory]';
            $out .= '</tt></a></td>';
            $out .= '<td style="text-align: right;"><tt>';
            $out .= '</tt></td>';
            $out .= '<td style="text-align: right;"><tt>';
            $out .= gmdate('D, d M Y H:i:s T', filemtime($resource));
            $out .= '</tt></td>';
            $out .= '</tr>';
            $shade = true;
        }

        // TODO sistemare
        $receiver = WebAppContainer::instance('webappcontainer')->getCurrentWebApp()->getInitParameter('receiverfile');
        if (!strlen($receiver))
            $receiver = 'index.php';

        $dh = opendir($resource);
        while (($child = readdir($dh)) !== false) {
            // don't accept parent, self, or special protected directories
            if (preg_match(';^(\.|\.\.|core|setup|setup|\.htaccess)$;i', $child)) {
                continue;
            }

            // don't allow the controller to be seen
            // @todo make the controller script a constant!!

            if ($path == '/' && $child == $receiver) {
                continue;
            }

            $childResource = $resource.DIRECTORY_SEPARATOR.$child;
            // add trailing slash for directories
            if (is_dir($childResource)) {
                $child .= '/';
            }

            $out .= '<tr'. ($shade ? ' style="background-color: #EEEEEE;"' : '').'>';
            $out .= '<td style="text-align: left;"><a href="'.$basePath.$path.$child.'"><tt>';
            $out .= $child;
            $out .= '</tt></a></td>';
            $out .= '<td style="text-align: right;"><tt>';
            $out .= (is_file($childResource) ? $this->renderSize(filesize($childResource)) : '');
            $out .= '</tt></td>';
            $out .= '<td style="text-align: right;"><tt>';
            $out .= gmdate('D, d M Y H:i:s T', filemtime($childResource));
            $out .= '</tt></td>';
            $out .= '</tr>';
            $shade = !$shade;
        }

        closedir($dh);

        $out .= '</table>';
        $out .= '<hr size="1" noshade="noshade" />';
        $out .= '<h3>'.$request->getServerName().'</h3>';
        $out .= '</body>';
        $out .= '</html>';
        return $out;
    }

    /**
     * Given a file length in bytes, convert those bytes to a human readable
     * format in kb.
     *
     * @param int $size The size in bytes
     */
    function renderSize($size)
    {
        return (round(($size / 1024) * 10) / 10).' kb';
    }

}
