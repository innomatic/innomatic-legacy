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
namespace Innomatic\Wui\Dispatch;

use \Innomatic\Wui\Wui;

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
            
            $log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();
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
            
            $log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();
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
            if (count($this->mEvents) and isset(\Innomatic\Wui\Wui::instance('\Innomatic\Wui\Wui')->parameters['wui'][$this->mName]['evn']) and isset($this->mEvents[\Innomatic\Wui\Wui::instance('\Innomatic\Wui\Wui')->parameters['wui'][$this->mName]['evn']])) {
                if (function_exists($this->mEvents[\Innomatic\Wui\Wui::instance('\Innomatic\Wui\Wui')->parameters['wui'][$this->mName]['evn']])) {
                    $func = $this->mEvents[\Innomatic\Wui\Wui::instance('\Innomatic\Wui\Wui')->parameters['wui'][$this->mName]['evn']];
                    $func (isset(\Innomatic\Wui\Wui::instance('\Innomatic\Wui\Wui')->parameters['wui'][$this->mName]['evd']) ? \Innomatic\Wui\Wui::instance('\Innomatic\Wui\Wui')->parameters['wui'][$this->mName]['evd'] : array());
                    return true;
                } else {
                    
                    $log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();
                    $log->logEvent('innomatic.wui.wuidispatcher.dispatch', 'Function '.$this->mEvents[\Innomatic\Wui\Wui::instance('\Innomatic\Wui\Wui')->parameters['wui'][$this->mName]['evn']]." doesn't exists", \Innomatic\Logging\Logger::ERROR);
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
        return isset(\Innomatic\Wui\Wui::instance('\Innomatic\Wui\Wui')->parameters['wui'][$this->mName]['evn']) ? \Innomatic\Wui\Wui::instance('\Innomatic\Wui\Wui')->parameters['wui'][$this->mName]['evn'] : '';
    }

    /*!
     @function getEventData
     @abstract Gets current event data for this dispatcher.
     @discussion Gets current event data for this dispatcher.
     @result Current event data.
     */
    public function getEventData()
    {
        return isset(\Innomatic\Wui\Wui::instance('\Innomatic\Wui\Wui')->parameters['wui'][$this->mName]['evd']) ? \Innomatic\Wui\Wui::instance('\Innomatic\Wui\Wui')->parameters['wui'][$this->mName]['evd'] : '';
    }

    public static function dispatchersList()
    {
        $result = array();
        if (isset(\Innomatic\Wui\Wui::instance('\Innomatic\Wui\Wui')->parameters['wui']) and is_array(\Innomatic\Wui\Wui::instance('\Innomatic\Wui\Wui')->parameters['wui'])) {
            reset(\Innomatic\Wui\Wui::instance('\Innomatic\Wui\Wui')->parameters['wui']);
            while (list ($dispatcher_name) = each(\Innomatic\Wui\Wui::instance('\Innomatic\Wui\Wui')->parameters['wui'])) {
                $result[] = $dispatcher_name;
            }
            reset(\Innomatic\Wui\Wui::instance('\Innomatic\Wui\Wui')->parameters['wui']);
        }
        return $result;
    }
}
