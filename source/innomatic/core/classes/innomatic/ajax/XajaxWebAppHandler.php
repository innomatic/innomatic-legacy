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
        require_once('innomatic/ajax/Xajax.php');
        require_once('innomatic/core/InnomaticContainer.php');
        $xajax = Xajax::instance('Xajax');
        
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

        foreach($cfg->functions as $name => $functionData) {
        	$xajax->registerExternalFunction(array($name, $functionData['classname'], $functionData['method']), $functionData['classfile']);
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