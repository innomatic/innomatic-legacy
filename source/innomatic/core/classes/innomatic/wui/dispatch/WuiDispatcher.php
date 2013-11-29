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

/**
 * WUI events dispatcher.
 *
 * @package WUI
 */
class WuiDispatcher
{
    /*! @var mName string - Dispatcher name. */
    private $mName;
    /*! @var mEvents array - Array of the event functions. */
    public $mEvents = array();
    /*! @var mDispatched bool - True when the events have been dispatched. */
    private $mDispatched;

    /*!
     @function WuiDispatcher
     @abstract Class constructor.
     @discussion Class constructor.
     @param dispName string - Dispatcher name.
     */
    public function __construct($dispName)
    {
        if (strlen($dispName)) {
            $this->mName = $dispName;
        } else {
            require_once('innomatic/logging/Logger.php');
            $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
            $log->logEvent('innomatic.wui.wuidispatcher', 'Empty dispatcher name', \Innomatic\Logging\Logger::ERROR);
        }
        $this->mDispatched = false;

    }

    /*!
     @function AddEvent
     @abstract Adds an event to the dispatcher list.
     @discussion Adds an event to the dispatcher list. The event is not added if already exists an event with that name.
     @param eventName string - Event to be added to the dispatcher list.
     @param functionName string - Function that handles the eventName event.
     @result True if the event has been added. May return false if the event already exists.
     */
    public function addEvent($eventName, $functionName)
    {
        if (strlen($eventName) and strlen($functionName) and !isset($this->mEvents[$eventName])) {
            $this->mEvents[$eventName] = $functionName;
            return true;
        } else {
            require_once('innomatic/logging/Logger.php');
            $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
            if (!strlen($eventName)) {
                $log->logEvent('innomatic.wui.wuidispatcher.addevent', 'Empty event name', \Innomatic\Logging\Logger::ERROR);
            }
            if (!strlen($functionName)) {
                $log->logEvent('innomatic.wui.wuidispatcher.addevent', 'Empty function name', \Innomatic\Logging\Logger::ERROR);
            }
            if (isset($this->mEvents[$eventName]) and strlen($this->mEvents[$eventName])) {
                $log->logEvent('innomatic.wui.wuidispatcher.addevent', 'Event '.$eventName.' already exists', \Innomatic\Logging\Logger::ERROR);
            }
            return false;
        }
    }

    /*!
     @function Dispatch
     @abstract Dispatches the incoming event.
     @discussion Dispatches the incoming event to the event assigned function. It first checks if the function
     exists.
     @result True if the event has been dispatched. Returns false if the function for the event doesn't exists.
     */
    public function dispatch()
    {
        if (!$this->mDispatched) {
            require_once('innomatic/wui/Wui.php');
            if (count($this->mEvents) and isset(Wui::instance('wui')->parameters['wui'][$this->mName]['evn']) and isset($this->mEvents[Wui::instance('wui')->parameters['wui'][$this->mName]['evn']])) {
                if (function_exists($this->mEvents[Wui::instance('wui')->parameters['wui'][$this->mName]['evn']])) {
                    $func = $this->mEvents[Wui::instance('wui')->parameters['wui'][$this->mName]['evn']];
                    $func (isset(Wui::instance('wui')->parameters['wui'][$this->mName]['evd']) ? Wui::instance('wui')->parameters['wui'][$this->mName]['evd'] : array());
                    return true;
                } else {
                    require_once('innomatic/logging/Logger.php');
                    $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
                    $log->logEvent('innomatic.wui.wuidispatcher.dispatch', 'Function '.$this->mEvents[Wui::instance('wui')->parameters['wui'][$this->mName]['evn']]." doesn't exists", \Innomatic\Logging\Logger::ERROR);
                    return false;
                }
            }
        }
    }

    /*!
     @function getEventName
     @abstract Gets current event name for this dispatcher.
     @discussion Gets current event name for this dispatcher.
     @result Current event name.
     */
    public function getEventName()
    {
        require_once('innomatic/wui/Wui.php');
        return isset(Wui::instance('wui')->parameters['wui'][$this->mName]['evn']) ? Wui::instance('wui')->parameters['wui'][$this->mName]['evn'] : '';
    }

    /*!
     @function getEventData
     @abstract Gets current event data for this dispatcher.
     @discussion Gets current event data for this dispatcher.
     @result Current event data.
     */
    public function getEventData()
    {
        require_once('innomatic/wui/Wui.php');
        return isset(Wui::instance('wui')->parameters['wui'][$this->mName]['evd']) ? Wui::instance('wui')->parameters['wui'][$this->mName]['evd'] : '';
    }

    public static function dispatchersList()
    {
        require_once('innomatic/wui/Wui.php');
        $result = array();
        if (isset(Wui::instance('wui')->parameters['wui']) and is_array(Wui::instance('wui')->parameters['wui'])) {
            reset(Wui::instance('wui')->parameters['wui']);
            while (list ($dispatcher_name) = each(Wui::instance('wui')->parameters['wui'])) {
                $result[] = $dispatcher_name;
            }
            reset(Wui::instance('wui')->parameters['wui']);
        }
        return $result;
    }
}
