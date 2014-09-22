<?php
/**
 * Innomatic
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2014 Innoteam Srl
 * @license    http://www.innomatic.io/license/ New BSD License
 * @link       http://www.innomatic.io
*/
namespace Innomatic\Desktop\Panel;

use \Innomatic\Core\InnomaticContainer;

/**
 * Abstract class for implementing a controller in a Desktop Panel following
 * the MVC design pattern.
 *
 * @since 5.0.0
 * @package Desktop
 */
abstract class PanelController implements \Innomatic\Util\Observer
{

    protected $application;

    protected $mode;

    protected $applicationHome;

    protected $action;

    /**
     * @deprecated
     */
    protected $_action;

    protected $view;

    /**
     * @deprecated
     */
    protected $_view;

    protected $ajax;

    public function __construct($mode, $application)
    {
        $container = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');

        // Builds the application home path
        $home = $container->getHome();
        switch ($mode) {
            case \Innomatic\Core\InnomaticContainer::MODE_ROOT:
                $home .= 'root/';
                break;
            
            case \Innomatic\Core\InnomaticContainer::MODE_DOMAIN:
                $home .= 'domain/';
                break;
        }
        $home .= $application . '-panel/';
        
        // Checks if the application exists and is valid
        if (file_exists($home)) {
            $this->mode = $mode;
            $this->applicationHome = $home;
            $this->application = $application;
        } else {
            throw new \Innomatic\Wui\WuiException(\Innomatic\Wui\WuiException::INVALID_APPLICATION);
        }
        
        // TODO Verificare, dopo questa impostazione, quanto ancora sia utile di WuiDispatcher
        
        $view = null;
        $action = null;
        
        // View initialization
        $viewDispatcher = new \Innomatic\Wui\Dispatch\WuiDispatcher('view');
        $viewEvent = $viewDispatcher->getEventName();
        if (! strlen($viewEvent)) {
            $viewEvent = 'default';
        }
        $viewClassName = ucfirst($this->application) . 'PanelViews';
        
        // Checks if view file and definition exist
        // @todo update to new namespaces model
        if (! include_once ($this->applicationHome . $viewClassName . '.php')) {
            throw new \Innomatic\Wui\WuiException(\Innomatic\Wui\WuiException::MISSING_VIEWS_FILE);
        }
        if (! class_exists($viewClassName, true)) {
            throw new \Innomatic\Wui\WuiException(\Innomatic\Wui\WuiException::MISSING_VIEWS_CLASS);
        }
        
        // Instantiate views class
        $this->view = new $viewClassName($this);
        $this->_view = $this->view;
        $this->view->beginHelper();
        
        // Action initialization
        $actionClassName = ucfirst($this->application) . 'PanelActions';
        
        // Checks if class file and definition exist
        if (! include_once ($this->applicationHome . $actionClassName . '.php')) {
            throw new \Innomatic\Wui\WuiException(\Innomatic\Wui\WuiException::MISSING_ACTIONS_FILE);
        }
        if (! class_exists($actionClassName, true)) {
            throw new \Innomatic\Wui\WuiException(\Innomatic\Wui\WuiException::MISSING_ACTIONS_CLASS);
        }
        
        // AJAX
        $ajax_request_uri = $_SERVER['REQUEST_URI'];
        if (strpos($ajax_request_uri, '?')) {
            $ajax_request_uri = substr($ajax_request_uri, 0, strpos($ajax_request_uri, '?'));
        }
        
        $this->ajax = \Innomatic\Ajax\Xajax::instance('Xajax', $ajax_request_uri);
        
        // Set debug mode
        if ($container->getState() == \Innomatic\Core\InnomaticContainer::STATE_DEBUG) {
            $this->ajax->debugOn();
        }
        $this->ajax->setLogFile($container->getHome() . 'core/log/ajax.log');
        
        // Register action ajax calls
        $this->registerClassAjaxCalls($actionClassName, $this->applicationHome . $actionClassName . '.php');
        
        // Register WUI widgets ajax calls
        $wui = \Innomatic\Wui\Wui::instance('\Innomatic\Wui\Wui');
        $wui->loadAllWidgets();
        foreach ($wui->mLoadedWidgets as $widget) {
            $this->registerClassAjaxCalls('\Shared\Wui\Wui' . ucfirst($widget), $container->getHome() . 'core/classes/shared/wui/Wui' . ucfirst($widget) . '.php', 'Wui' . ucfirst($widget));
        }
        
        // Process ajax requests, if any (if so, then it exits)
        $this->ajax->processRequests();
        
        // Action execution, if set
        $actionDispatcher = new \Innomatic\Wui\Dispatch\WuiDispatcher('action');
        $actionEvent = $actionDispatcher->getEventName();
        if (strlen($actionEvent)) {
            
            $this->action = new $actionClassName($this);
            $this->_action = $this->action;
            $this->action->addObserver($this);
            if (is_object($this->view)) {
                $this->action->addObserver($this->view);
            }
            $this->action->beginHelper();
            
            // Executes the action
            $actionResult = $this->action->execute($actionEvent, $actionDispatcher->getEventData());
            $this->action->endHelper();
        }
        
        // Displays the view result
        if (is_object($this->view)) {
            $this->view->execute($viewEvent, $viewDispatcher->getEventData());
            $this->view->endHelper();
            $this->view->display();
        } else {
            throw new \Innomatic\Wui\WuiException(\Innomatic\Wui\WuiException::NO_VIEW_DEFINED);
        }
    }

    public function registerClassAjaxCalls($className, $classFile, $prefix = '')
    {
        $theClass = new \ReflectionClass($className);
        $methods = $theClass->getMethods();
        foreach ($methods as $method) {
            // Ignore private methods
            $theMethod = new \ReflectionMethod($theClass->getName(), $method->getName());
            if (! $theMethod->isPublic()) {
                continue;
            }
            
            // Expose only methods beginning with "ajax" prefix
            if (! (substr($method->getName(), 0, 4) == 'ajax')) {
                continue;
            }
            // Register the ajax call
            $call_name = $prefix . substr($method->getName(), 4);
            $this->view->getWuiContainer()->registerAjaxCall($call_name);
            $this->ajax->registerExternalFunction(array(
                $call_name,
                $className,
                $method->getName()
            ), $classFile);
        }
    }

    public function getAction()
    {
        return $this->action;
    }

    public function getView()
    {
        return $this->view;
    }

    public function getAjax()
    {
        return $this->ajax;
    }
}