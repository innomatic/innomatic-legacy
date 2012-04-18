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

/**
 * WUI events call builder.
 * 
 * @package WUI
 */
class WuiEventsCall {
    /*! @var mCall string - Events call url. */
    private $mCall;
    /*! @var mEvents array - Array of events string. */
    private $mEvents = array();
    const ADDEVENT_NOTANOBJECT = -1;
    
    /*!
     @discussion Sets event call. A normal event doesn't need to set eventsCall parameter.
     @param eventsCall string - Events call url. If not defined, the class defaults it to PHP_SELF constant.
     */
    public function __construct($eventsCall = '') {
        $this->setCall($eventsCall);
    }

    /*!
     @discussion Sets event call.
     @param eventsCall string - Events call url. If not defined, the class defaults it to PHP_SELF constant.
     @result Always true.
     */
    public function setCall($eventsCall = '') {
        if (strlen($eventsCall)) {
            $this->mCall = $eventsCall;
        } else {
            //$this->mCall = WebAppContainer::instance('webappcontainer')->getProcessor()->getRequest()->getUrlPath();
            $this->mCall = $_SERVER['REQUEST_URI'];
            if (strpos($this->mCall, '?')) {
                $this->mCall = substr($this->mCall, 0, strpos($this->mCall, '?'));
            }
            //$this->mCall = $_SERVER['PHP_SELF'];
        }
        return true;
    }

    /*!
     @discussion Adds an event string to the call.
     @param revent WuiEvent class - Event to be added.
     @result True if the event is a real object.
     */
    public function addEvent(&$revent) {
        if (is_object($revent)) {
            $this->mEvents[] = $revent->getEventString();
            return true;
        } else {
            return self::ADDEVENT_NOTANOBJECT;
        }
    }

    /*!
     @discussion Builds and returns the event call string.
     @result The event call string. At least it will return PHP_SELF constant.
     */
    public function getEventsCallString() {
        $result = $this->mCall;
        $items_count = count($this->mEvents);
        if ($items_count) {
            $result.= '?';
            reset($this->mEvents);
            $cont = 1;

            while (list ($key, $val) = each($this->mEvents)) {
                $result.= $val;
                if ($cont < $items_count) {
                    $result.= '&';
                }
                $cont ++;
            }
        }
        return $result;
    }

    /*!
     @discussion Resets the event call, removing all events.
     @result Always true.
     */
    public function resetEvents() {
        $this->mEvents = array();
        return true;
    }

    public static function buildEventsCallString($eventsCallUrl, $eventsArray) {
        $tmp_action = new WuiEventsCall($eventsCallUrl);
        if (is_array($eventsArray)) {
            require_once('innomatic/wui/dispatch/WuiEvent.php');
            while (list (, $event) = each($eventsArray)) {
                $tmp_action->addEvent(new WuiEvent($event[0], $event[1], isset($event[2]) ? $event[2] : ''));
            }
        }
        return $tmp_action->getEventsCallString();
    }
}
