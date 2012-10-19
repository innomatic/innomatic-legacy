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

require_once('innomatic/util/Observable.php');
require_once('innomatic/desktop/panel/PanelController.php');

/**
 * Abstract class for implementing a set of actions in a Desktop Panel following
 * the MVC design pattern.
 *
 * @copyright  2000-2012 Innoteam S.r.l.
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
    
    public abstract function beginHelper();
    
    public abstract function endHelper();

    public function getController()
    {
        return $this->_controller;
    }
}
