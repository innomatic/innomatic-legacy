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

require_once('innomatic/util/Observer.php');

/**
 * Abstract class for implementing a controller in a Desktop Panel following
 * the MVC design pattern.
 *
 * @copyright  2000-2012 Innoteam S.r.l.
 * @license    http://www.innomatic.org/license/   BSD License
 * @link       http://www.innomatic.org
 * @since      Class available since Release 5.0
 * @package    Desktop
 */
abstract class PanelController implements Observer
{
    protected $_application;
    protected $_mode;
    protected $_applicationHome;
    protected $_action;
    protected $_view;

    public function __construct($mode, $application)
    {
        require_once('innomatic/wui/dispatch/WuiDispatcher.php');
        require_once('innomatic/core/InnomaticContainer.php');

        // Builds the application home path
        $home = InnomaticContainer::instance('innomaticcontainer')->getHome();
        switch ($mode) {
            case InnomaticContainer::MODE_ROOT:
                $home .= 'root/';
                break;

            case InnomaticContainer::MODE_DOMAIN:
                $home .= 'domain/';
                break;
        }
        $home .= $application . '-panel/';

        // Checks if the application exists and is valid
        if (file_exists($home)) {
            $this->_mode = $mode;
            $this->_applicationHome = $home;
            $this->_application = $application;
        } else {
            require_once('innomatic/wui/WuiException.php');
            throw new WuiException(WuiException::INVALID_APPLICATION);
        }

        // TODO Verificare, dopo questa impostazione, quanto ancora sia utile di WuiDispatcher

        $view = null;
        $action = null;

        // View initialization
        $viewDispatcher = new WuiDispatcher('view');
        $viewEvent = $viewDispatcher->getEventName();
        if (!strlen($viewEvent)) {
            $viewEvent = 'default';
        }
        $viewClassName = ucfirst($this->_application).'PanelViews';

        // Checks if view file and definition exist
        if (!include_once($this->_applicationHome.$viewClassName.'.php')) {
            require_once('innomatic/wui/WuiException.php');
            throw new WuiException(WuiException::MISSING_VIEWS_FILE);
        }
        if (!class_exists($viewClassName)) {
            require_once('innomatic/wui/WuiException.php');
            throw new WuiException(WuiException::MISSING_VIEWS_CLASS);
        }

        // Instantiate views class
        $this->_view = new $viewClassName($this);
        $this->_view->beginHelper();

        // Action initialization
        $actionDispatcher = new WuiDispatcher('action');
        $actionEvent = $actionDispatcher->getEventName();
        if (strlen($actionEvent)) {
            $actionClassName = ucfirst($this->_application).'PanelActions';
                
            // Checks if class file and definition exist
            if (
                !include_once($this->_applicationHome.$actionClassName
                . '.php')
            ) {
                require_once('innomatic/wui/WuiException.php');
                throw new WuiException(WuiException::MISSING_ACTIONS_FILE);
            }
            if (!class_exists($actionClassName)) {
                require_once('innomatic/wui/WuiException.php');
                throw new WuiException(WuiException::MISSING_ACTIONS_CLASS);
            }

            $this->_action = new $actionClassName($this);
            $this->_action->addObserver($this);
            if (is_object($this->_view)) {
                $this->_action->addObserver($this->_view);
            }
            $this->_action->beginHelper();

            // Executes the action
            $actionResult = $this->_action->execute(
                $actionEvent,
                $actionDispatcher->getEventData()
            );
            $this->_action->endHelper();
        }

        // Displays the view result
        if (is_object($this->_view)) {
            $this->_view->execute($viewEvent, $viewDispatcher->getEventData());
            $this->_view->endHelper();
            $this->_view->display();
        } else {
            require_once('innomatic/wui/WuiException.php');
            throw new WuiException(WuiException::NO_VIEW_DEFINED);
        }
    }

    public function getAction()
    {
        return $this->_action;
    }

    public function getView()
    {
        return $this->_view;
    }
}
