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

/**
 * Abstract class for implementing a set of views in a Desktop Panel following
 * the MVC design pattern.
 *
 * @copyright  2000-2012 Innoteam Srl
 * @license    http://www.innomatic.org/license/   BSD License
 * @link       http://www.innomatic.org
 * @since      Class available since Release 5.0
 * @package    Desktop
 */
abstract class PanelViews implements \Innomatic\Util\Observer
{
    protected $_controller;
    protected $_helper;
    protected $_wuiContainer;

    public function __construct(PanelController $controller)
    {
        $this->_controller = $controller;

        // Sets Wui container instance
        $this->_wuiContainer = \Innomatic\Wui\Wui::instance('wui');
    }

    abstract public function beginHelper();

    abstract public function endHelper();

    public function execute($view = 'default', $eventData = array())
    {
        // Cheks view method and executes it if exists
        $methodName = 'view'.$view;
        if (!method_exists($this, $methodName)) {
            $methodName = 'viewDefault';
        }
        $this->$methodName($eventData);
    }

    public function display()
    {
        // Outputs the Wui source
        $this->_wuiContainer->render();
    }

    abstract public function viewDefault($eventData);

    public function update($observable, $arg = '')
    {
    }

    public function getController()
    {
        return $this->_controller;
    }

      public function getWuiContainer()
      {
          return $this->_wuiContainer;
      }
}
