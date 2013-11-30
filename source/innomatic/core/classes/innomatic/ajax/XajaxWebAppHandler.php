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
        require_once('innomatic/core/InnomaticContainer.php');
        require_once('innomatic/core/RootContainer.php');

        $innomatic = InnomaticContainer::instance('innomaticcontainer');
        $innomatic->setInterface(InnomaticContainer::INTERFACE_EXTERNAL);
        $root = \Innomatic\Core\RootContainer::instance('rootcontainer');
        $innomatic_home = $root->getHome().'innomatic/';
        $innomatic->bootstrap($innomatic_home, $innomatic_home.'core/conf/innomatic.ini');
        InnomaticContainer::instance('innomaticcontainer')->startDomain(WebAppContainer::instance('webappcontainer')->getCurrentWebApp()->getName());

        require_once('innomatic/ajax/Xajax.php');
        require_once('innomatic/core/InnomaticContainer.php');

        $request_uri = WebAppContainer::instance('webappcontainer')->getProcessor()->getRequest()->getUrlPath(true).'/_xajax/call.xajax';
        $xajax = Xajax::instance('Xajax', $request_uri);

        // Set debug mode
        if (InnomaticContainer::instance('innomaticcontainer')->getState() == InnomaticContainer::STATE_DEBUG) {
            $xajax->debugOn();
        }

        $xajax->setLogFile(
            InnomaticContainer::instance('innomaticcontainer')->getHome()
            . 'core/log/ajax.log'
        );

        $cfg = XajaxConfig :: getInstance(
                WebAppContainer::instance('webappcontainer')->getCurrentWebApp(),
                WebAppContainer::instance('webappcontainer')->getCurrentWebApp()->getHome().'core/conf/ajax.xml');

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
        return WebAppContainer::instance('webappcontainer')->getCurrentWebApp()->getHome().'core/xajax'.substr( $this->sRequestURI,strpos($this->sRequestURI, 'index.php/')+9).'.php';
    }
    */
}
