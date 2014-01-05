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
 * @license    http://www.innomatic.org/license/   BSD License
 * @link       http://www.innomatic.org
 * @since      Class available since Release 5.0
*/
namespace Innomatic\Desktop\Webapp;

use \Innomatic\Core\InnomaticContainer;

/**
 * WebApp Handler for the base desktop.
 *
 * WebApp Handler for the base desktop, that is the one shown at the base
 * Innomatic URL where the user can choose between the Control Panel and the
 * Domain destkop.
 *
 * The base desktop supports the output buffering through the
 * CompressedOutputBuffering parameter in the core/conf/innomatic.ini
 * configuration file.
 *
 * @copyright  2000-2012 Innoteam Srl
 * @license    http://www.innomatic.org/license/   BSD License
 * @link       http://www.innomatic.org
 * @since      Class available since Release 5.0
 * @package    Desktop
 */
class DesktopBaseWebAppHandler extends \Innomatic\Webapp\WebAppHandler
{
    public function init()
    {
    }

    public function doGet(\Innomatic\Webapp\WebAppRequest $req, \Innomatic\Webapp\WebAppResponse $res)
    {
        // identify the requested resource path
        $resource = substr(\Innomatic\Webapp\WebAppContainer::instance('\Innomatic\Webapp\WebAppContainer')->getCurrentWebApp()->getHome(), 0, -1).$req->getPathInfo();

        // make sure that this path exists on disk
        switch (substr($resource, strrpos($resource, '/') + 1)) {
            case 'main':
            case 'menu':
            case 'logo':
                break;

            default:
                if (substr($resource, -1, 1) != '/' and !file_exists($resource.'.php') and !is_dir($resource.'-panel')) {
                    $res->sendError(\Innomatic\Webapp\WebAppResponse::SC_NOT_FOUND, $req->getRequestURI());
                    return;
                }
        }

        // Bootstraps Innomatic
        $innomatic = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');

        // Sets Innomatic base URL
        $baseUrl = '';
        $webAppPath = $req->getUrlPath();
        if (!is_null($webAppPath) && $webAppPath != '/') {
            $baseUrl = $req->generateControllerPath($webAppPath, true);
        }
        $innomatic->setBaseUrl($baseUrl);

        $innomatic->setInterface(\Innomatic\Core\InnomaticContainer::INTERFACE_WEB);
        $home = \Innomatic\Webapp\WebAppContainer::instance('\Innomatic\Webapp\WebAppContainer')->getCurrentWebApp()->getHome();
        $innomatic->bootstrap($home, $home.'core/conf/innomatic.ini');

        if (!headers_sent()) {
            // Starts output compression.
            if (
                \Innomatic\Core\InnomaticContainer::instance(
                    '\Innomatic\Core\InnomaticContainer'
                )->getConfig()->value('CompressedOutputBuffering') == '1'
            ) {
                ini_set('zlib.output_compression', 'on');
                ini_set('zlib.output_compression_level', 6);
            }
        }

        \Innomatic\Desktop\Controller\DesktopFrontController::instance('\Innomatic\Desktop\Controller\DesktopFrontController')->execute(\Innomatic\Core\InnomaticContainer::MODE_BASE, $resource);
    }

    public function doPost(\Innomatic\Webapp\WebAppRequest $req, \Innomatic\Webapp\WebAppResponse $res)
    {
        $this->doGet($req, $res);
    }

    public function destroy()
    {
    }

    protected function getRelativePath(\Innomatic\Webapp\WebAppRequest $request)
    {
        $result = $request->getPathInfo();
        return \Innomatic\Io\Filesystem\DirectoryUtils::normalize(strlen($result) ? $result : '/');
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
    protected function getURL(\Innomatic\Webapp\WebAppRequest $request, $redirectPath)
    {
        $result = '';
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
}
