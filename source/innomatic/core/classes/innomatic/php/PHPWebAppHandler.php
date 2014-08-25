<?php
/**
 * Innomatic
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2014 Innoteam Srl
 * @license    http://www.innomaticplatform.com/license/ New BSD License
 * @link       http://www.innomaticplatform.com
 * @since      Class available since Release 5.0
*/
namespace Innomatic\Php;

use \Innomatic\Webapp;

/**
 * @since 5.0
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 * @copyright Copyright 2012-2014 Innoteam Srl
 */
class PHPWebAppHandler extends WebAppHandler
{
    public function init()
    {
    }

    public function doGet(WebAppRequest $req, WebAppResponse $res)
    {
        $resource = substr(
            \Innomatic\Webapp\WebAppContainer::instance(
                '\Innomatic\Webapp\WebAppContainer'
            )->getCurrentWebApp()->getHome(), 0, -1
        ) . $req->getPathInfo();

        // If this is a directory, check that a welcome file exists
        if (is_dir($resource)) {
            $this->welcomeFiles = \Innomatic\Webapp\WebAppContainer::instance('\Innomatic\Webapp\WebAppContainer')->getCurrentWebApp()->getWelcomeFiles();

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
        if (\Innomatic\Security\SecurityManager::isAboveBasePath($resource, \Innomatic\Webapp\WebAppContainer::instance(
                '\Innomatic\Webapp\WebAppContainer'
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
            if (file_exists(substr(\Innomatic\Webapp\WebAppContainer::instance('\Innomatic\Webapp\WebAppContainer')->getCurrentWebApp()->getHome(), 0, -1).$path.$welcomefile.'.php'))
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
