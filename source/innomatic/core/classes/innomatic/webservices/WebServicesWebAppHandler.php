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
namespace Innomatic\Webservices;

use \Innomatic\Core\InnomaticContainer;
use \Innomatic\Webservices;

/**
 * @since 5.0
 * @author Alex Pagnoni <alex.pagnoni@innoteam.it>
 * @copyright Copyright 2012 Innoteam Srl
 */
class WebServicesWebAppHandler extends \Innomatic\Webapp\WebAppHandler
{
    public function init()
    {
    }

    public function doGet(\Innomatic\Webapp\WebAppRequest $req, \Innomatic\Webapp\WebAppResponse $res)
    {
        // Identify the requested resource path
        $path = $this->getRelativePath($req);

        // Bootstraps Innomatic
        $container = \Innomatic\Webapp\WebAppContainer::instance('\Innomatic\Webapp\WebAppContainer');
        $home = $container->getCurrentWebApp()->getHome();

        $innomatic = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');
        $innomatic->bootstrap($home, $home.'core/conf/innomatic.ini');
        $innomatic->setMode(\Innomatic\Core\InnomaticContainer::MODE_ROOT);
        $innomatic->setInterface(\Innomatic\Core\InnomaticContainer::INTERFACE_WEBSERVICES);

        if (\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getState() == \Innomatic\Core\InnomaticContainer::STATE_SETUP) {
            $innomatic->abort('Setup phase');
        }

        $xuser = new WebServicesUser(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess());
        if ($xuser->setByAccount($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
            $container = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');
            $container->setWebServicesUser($_SERVER['PHP_AUTH_USER']);
            $container->setWebServicesProfile($xuser->mProfileId);

            if ($xuser->mDomainId) {
                $domain_query = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->execute('SELECT domainid FROM domains WHERE id='.$xuser->mDomainId);
                if ($domain_query->getNumberRows()) {
                    $innomatic = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');
                    $innomatic->startDomain($domain_query->getFields('domainid'));
                }
            }

            $xprofile = new WebServicesProfile(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(), $container->getWebServicesProfile());
            $container->setWebServicesMethods($xprofile->AvailableMethods());
        } else {
            if (\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getConfig()->Value('SecurityAlertOnWrongWebServicesLogin') == '1') {
                $innomatic_security = new \Innomatic\Security\SecurityManager();
                $innomatic_security->sendAlert('Wrong web services login for user '.$_SERVER['PHP_AUTH_USER'].' from remote address '.$_SERVER['REMOTE_ADDR']);
                unset($innomatic_security);
            }
        }
        $structure = array();

        $methods = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getWebServicesMethods();
        while (list (, $tmpdata) = each($methods)) {
            if ($tmpdata['handler'] and $tmpdata['name'] and $tmpdata['function']) {
                // TODO Fixare gestione handler servizi remoti
                if (!defined(strtoupper($tmpdata['handler']).'_XMLRPCMETHOD')) {
                    require_once(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome().'core/classes/shared/webservices/'.ucfirst($tmpdata['handler']).'WebServicesHandler.php');
                }

                $structure[$tmpdata['name']]['function'] = $tmpdata['function'];
                if (isset($tmpdata['signature'])) {
                    $structure[$tmpdata['name']]['signature'] = $tmpdata['signature'];
                }
                if (isset($tmpdata['docstring'])) {
                    $structure[$tmpdata['name']]['docstring'] = $tmpdata['docstring'];
                }
            }
        }

        $xs = new \Innomatic\Webservices\Xmlrpc\XmlRpc_Server($structure);
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

        $container = \Innomatic\Webapp\WebAppContainer::instance('\Innomatic\Webapp\WebAppContainer');
        $processor = $container->getProcessor();
        $webAppPath = $request->getUrlPath();

        if (!is_null($webAppPath) && $webAppPath != '/') {
            $result = $request->generateControllerPath($webAppPath, true);
        }

        $result .= '/webservices'.$redirectPath;

        $query = $request->getQueryString();
        if (!is_null($query)) {
            $result .= '?'.$query;
        }

        return $result;
    }
}
