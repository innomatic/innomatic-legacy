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
namespace Innomatic\Desktop\Panel;

require_once('innomatic/util/Observable.php');
require_once('innomatic/desktop/panel/PanelController.php');

/**
 * Abstract class for implementing a set of actions in a Desktop Panel following
 * the MVC design pattern.
 *
 * @copyright  2000-2012 Innoteam Srl
 * @license    http://www.innomatic.org/license/   BSD License
 * @link       http://www.innomatic.org
 * @since      Class available since Release 5.0
 * @package    Desktop
 */
abstract class PanelActions extends Observable
{
    protected $_controller;

    public function __construct(PanelController $controller)
    {
        $this->_controller = $controller;
    }

    public function execute($action, $eventData = array())
    {
        $methodName = 'execute'.$action;
        if (strlen($action) and method_exists($this, $methodName)) {
            $this->$methodName($eventData);
        }
    }

    abstract public function beginHelper();

    abstract public function endHelper();

    public function getController()
    {
        return $this->_controller;
    }

    public function ajaxInnomaticStickFrame($args)
    {
            $objResponse = new XajaxResponse();

            $name = $args[0];
            $top = $args[1];
            $left = $args[2];

            require_once('shared/wui/WuiDivframe.php');
            $empty = new WuiDivframe($name);
            $session_args = $empty->retrieveSession();

            if( isset($session_args['top']) ) {
                unset( $session_args['top'] );
                unset( $session_args['left'] );

                $sScript  = "var myImg = document.getElementById('pin_".$name."');";
                $sScript .= "myImg.src='".$empty->mThemeHandler->mIconsBase . $empty->mThemeHandler->mIconsSet['mini']['flag']['base'] . '/mini/' . $empty->mThemeHandler->mIconsSet['mini']['flag']['file']."';";
                $objResponse->addScript($sScript);
            } else {
                $session_args['top'] = $top;
                $session_args['left'] = $left;

                $sScript  = "var myImg = document.getElementById('pin_".$name."');";
                $sScript .= "myImg.src='".$empty->mThemeHandler->mIconsBase . $empty->mThemeHandler->mIconsSet['mini']['lock']['base'] . '/mini/' . $empty->mThemeHandler->mIconsSet['mini']['lock']['file']."';";
                $objResponse->addScript($sScript);
            }

            $empty->storeSession($session_args);

            return $objResponse->getXML();

    }
}
