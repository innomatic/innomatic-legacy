<?php
/**
 * Innomatic
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2013 Innoteam Srl
 * @license    http://www.innomatic.org/license/   BSD License
 * @link       http://www.innomatic.org
 * @since      Class available since Release 5.0
*/
namespace Innomatic\Php;

require_once('innomatic/webapp/WebAppHandler.php');
require_once('innomatic/webapp/WebAppProcessor.php');

/**
 * @since 5.0
 * @author Alex Pagnoni <alex.pagnoni@innoteam.it>
 * @copyright Copyright 2012-2013 Innoteam Srl
 */
class PHPWebAppHandler extends WebAppHandler
{
    public function init()
    {
    }

    public function doGet(WebAppRequest $req, WebAppResponse $res)
    {
        $resource = substr(
            WebAppContainer::instance(
                'webappcontainer'
            )->getCurrentWebApp()->getHome(), 0, -1
        ) . $req->getPathInfo();

        // If this is a directory, check that a welcome file exists
        if (is_dir($resource)) {
            $this->welcomeFiles = WebAppContainer::instance('webappcontainer')->getCurrentWebApp()->getWelcomeFiles();

            $path = $this->getRelativePath($req);
            $welcomeFile = $this->findWelcomeFile($path);
            if ($welcomeFile != null) {
                $resource = $resource.$welcomeFile;
            } else {
                $res->sendError(
                        WebAppResponse::SC_FORBIDDEN,
                        $req->getRequestURI()
                );
                return;
            }
        }

        // Make sure that this path exists on disk
        if (
            $req->getPathInfo() == '/index'
            or !file_exists($resource . '.php')
        ) {
            $res->sendError(
                WebAppResponse::SC_NOT_FOUND,
                $req->getRequestURI()
                );
            return;
        }

        // Core directory is private
        if (substr($req->getPathInfo(), 0, 6) == '/core/') {
            $res->sendError(
                WebAppResponse::SC_FORBIDDEN,
                $req->getRequestURI()
                );
            return;
        }

        // Resource must reside inside the webapp
        require_once('innomatic/security/SecurityManager.php');
        if (SecurityManager::isAboveBasePath($resource,  WebAppContainer::instance(
                'webappcontainer'
            )->getCurrentWebApp()->getHome())) {
            $res->sendError(
                    WebAppResponse::SC_FORBIDDEN,
                    $req->getRequestURI()
            );
            return;
        }

        include($resource.'.php');
    }

    public function doPost(WebAppRequest $req, WebAppResponse $res)
    {
        $this->doGet($req, $res);
    }

    public function destroy()
    {
    }

    protected function findWelcomeFile($path)
    {
        if (substr($path, -1) != '/')
            $path .= '/';

        reset($this->welcomeFiles);
        foreach ($this->welcomeFiles as $welcomefile) {
            if (file_exists(substr(WebAppContainer::instance('webappcontainer')->getCurrentWebApp()->getHome(), 0, -1).$path.$welcomefile.'.php'))
                return $welcomefile;
        }

        return null;
    }

    protected function getRelativePath(WebAppRequest $request)
    {
        $result = $request->getPathInfo();
        return \Innomatic\Io\Filesystem\DirectoryUtils::normalize(strlen($result) ? $result : '/');
    }
}
