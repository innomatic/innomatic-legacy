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
namespace Innomatic\Ajax;

use \Innomatic\Webapp;
use \Innomatic\Core\InnomaticContainer;

class XajaxWebAppHandler extends WebAppHandler
{
    /**
     * Inits the webapp handler.
     */
    public function init()
    {
    }

    public function doGet(WebAppRequest $req, WebAppResponse $res)
    {
        // Start Innomatic and Domain

        $innomatic = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');
        $innomatic->setInterface(\Innomatic\Core\InnomaticContainer::INTERFACE_EXTERNAL);
        $root = \Innomatic\Core\RootContainer::instance('rootcontainer');
        $innomatic_home = $root->getHome().'innomatic/';
        $innomatic->bootstrap($innomatic_home, $innomatic_home.'core/conf/innomatic.ini');
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->startDomain(WebAppContainer::instance('\Innomatic\Webapp\WebAppContainer')->getCurrentWebApp()->getName());

        $request_uri = WebAppContainer::instance('\Innomatic\Webapp\WebAppContainer')->getProcessor()->getRequest()->getUrlPath(true).'/_xajax/call.xajax';
        $xajax = Xajax::instance('Xajax', $request_uri);

        // Set debug mode
        if (\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getState() == \Innomatic\Core\InnomaticContainer::STATE_DEBUG) {
            $xajax->debugOn();
        }

        $xajax->setLogFile(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome()
            . 'core/log/ajax.log'
        );

        $cfg = XajaxConfig :: getInstance(
                WebAppContainer::instance('\Innomatic\Webapp\WebAppContainer')->getCurrentWebApp(),
                WebAppContainer::instance('\Innomatic\Webapp\WebAppContainer')->getCurrentWebApp()->getHome().'core/conf/ajax.xml');

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

    /*
    public function explodeWebAppURI()
    {
        return WebAppContainer::instance('\Innomatic\Webapp\WebAppContainer')->getCurrentWebApp()->getHome().'core/xajax'.substr( $this->sRequestURI,strpos($this->sRequestURI, 'index.php/')+9).'.php';
    }
    */
}
