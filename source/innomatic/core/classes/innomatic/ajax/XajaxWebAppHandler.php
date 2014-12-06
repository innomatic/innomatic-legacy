<?php
/**
 * Innomatic
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2014 Innomatic Company
 * @license    http://www.innomatic.io/license/ New BSD License
 * @link       http://www.innomatic.io
 */
namespace Innomatic\Ajax;

use \Innomatic\Core\InnomaticContainer;

/**
 * This is the webapp handler for Xajax calls.
 *
 * @since 5.0.0 introduced
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 */
class XajaxWebAppHandler extends \Innomatic\Webapp\WebAppHandler
{
    /**
     * Initializes the webapp handler.
     */
    public function init()
    {
    }

    public function doGet(WebAppRequest $req, WebAppResponse $res)
    {
        // Start Innomatic and Tenant

        $innomatic = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');
        $innomatic->setInterface(\Innomatic\Core\InnomaticContainer::INTERFACE_EXTERNAL);
        $root = \Innomatic\Core\RootContainer::instance('\Innomatic\Core\RootContainer');
        $innomatic_home = $root->getHome().'innomatic/';
        $innomatic->bootstrap($innomatic_home, $innomatic_home.'core/conf/innomatic.ini');
        $innomatic->startDomain(\Innomatic\Webapp\WebAppContainer::instance('\Innomatic\Webapp\WebAppContainer')->getCurrentWebApp()->getName());

        $request_uri = \Innomatic\Webapp\WebAppContainer::instance('\Innomatic\Webapp\WebAppContainer')->getProcessor()->getRequest()->getUrlPath(true).'/_xajax/call.xajax';
        $xajax = Xajax::instance('Xajax', $request_uri);

        // Set debug mode
        if ($innomatic->getState() == \Innomatic\Core\InnomaticContainer::STATE_DEBUG) {
            $xajax->debugOn();
        }

        $xajax->setLogFile(
            $innomatic->getHome()
            . 'core/log/ajax.log'
        );

        $cfg = XajaxConfig :: getInstance(
                \Innomatic\Webapp\WebAppContainer::instance('\Innomatic\Webapp\WebAppContainer')->getCurrentWebApp(),
                \Innomatic\Webapp\WebAppContainer::instance('\Innomatic\Webapp\WebAppContainer')->getCurrentWebApp()->getHome().'core/conf/ajax.xml');

        if (isset($cfg->functions)) {
            foreach($cfg->functions as $name => $functionData) {
                $xajax->registerExternalFunction(array($name, $functionData['classname'], $functionData['method']), $functionData['classfile']);
            }
        }

        $xajax->processRequests();
    }

    public function doPost(WebAppRequest $req, WebAppResponse $res)
    {
        $this->doGet($req, $res);
    }

    /**
     * Destroys the webapp handler.
     */
    public function destroy()
    {
    }
}
