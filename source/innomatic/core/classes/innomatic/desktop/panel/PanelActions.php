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
namespace Innomatic\Desktop\Panel;

/**
 * Abstract class for implementing a set of actions in a Desktop Panel following
 * the MVC design pattern.
 *
 * @since Class available since Release 5.0
 * @package Desktop
 */
abstract class PanelActions extends \Innomatic\Util\Observable
{

    /**
     *
     * @deprecated
     *
     */
    protected $_controller;

    /**
     * @type \Innomatic\Desktop\Panel\PanelController $controller
     */
    protected $controller;

    public function __construct(\Innomatic\Desktop\Panel\PanelController $controller)
    {
        $this->_controller = $controller;
        $this->controller = $controller;
    }

    /**
     * Executes an action.
     *
     * @param string $action            
     * @param array $eventData            
     */
    public function execute($action, $eventData = array())
    {
        $methodName = 'execute' . $action;
        if (strlen($action) and method_exists($this, $methodName)) {
            $this->$methodName($eventData);
        }
    }

    abstract public function beginHelper();

    abstract public function endHelper();

    /**
     * Returns the controller.
     *
     * @return \Innomatic\Desktop\Panel\PanelController
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     *
     * @deprecated
     *
     */
    public function ajaxInnomaticStickFrame($args)
    {
        $objResponse = new XajaxResponse();
        
        $name = $args[0];
        $top = $args[1];
        $left = $args[2];
        
        $empty = new \Shared\Wui\WuiDivframe($name);
        $session_args = $empty->retrieveSession();
        
        if (isset($session_args['top'])) {
            unset($session_args['top']);
            unset($session_args['left']);
            
            $sScript = "var myImg = document.getElementById('pin_" . $name . "');";
            $sScript .= "myImg.src='" . $empty->mThemeHandler->mIconsBase . $empty->mThemeHandler->mIconsSet['mini']['flag']['base'] . '/mini/' . $empty->mThemeHandler->mIconsSet['mini']['flag']['file'] . "';";
            $objResponse->addScript($sScript);
        } else {
            $session_args['top'] = $top;
            $session_args['left'] = $left;
            
            $sScript = "var myImg = document.getElementById('pin_" . $name . "');";
            $sScript .= "myImg.src='" . $empty->mThemeHandler->mIconsBase . $empty->mThemeHandler->mIconsSet['mini']['lock']['base'] . '/mini/' . $empty->mThemeHandler->mIconsSet['mini']['lock']['file'] . "';";
            $objResponse->addScript($sScript);
        }
        
        $empty->storeSession($session_args);
        
        return $objResponse->getXML();
    }
}
