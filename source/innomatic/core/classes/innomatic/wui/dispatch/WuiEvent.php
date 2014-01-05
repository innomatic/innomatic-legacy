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
 * @license    http://www.innomatic.org/license/   BSD License
 * @link       http://www.innomatic.org
 * @since      Class available since Release 5.0
*/
namespace Innomatic\Wui\Dispatch;

/**
 * WUI event.
 * @package WUI
 */
class WuiEvent
{
    /*! @var mDispatcherName string - Dispatcher name for this event. */
    private $mDispatcherName;
    /*! @var mName string - Event name. */
    private $mName;
    /*! @var mData array - Event key value pairs array. */
    private $mData = array();

    /*!
    @function WuiEvent
    @abstract Class constructor.
    @discussion Class constructor.
    @param eventDispatcherName string - Name of the dispatcher that handles this event.
    @param eventName string - Name of the event.
    @param eventData array - Event key value pairs array.
    */
    public function __construct($eventDispatcherName, $eventName, $eventData = '')
    {
        $this->setDispatcherName($eventDispatcherName);
        $this->setName($eventName);
        $this->setData($eventData);
    }

    /*!
    @function setDispatcherName
    @abstract Sets event dispatcher name.
    @discussion Sets event dispatcher name.
    @param eventDispatcherName string - Name of the dispatcher that handles this event.
    @result Always true.
    */
    public function setDispatcherName($eventDispatcherName)
    {
        $this->mDispatcherName = $eventDispatcherName;
    }

    /*!
    @function setName
    @abstract Sets event name.
    @discussion Sets event name.
    @param eventName string - Name of the event.
    @result Always true.
    */
    public function setName($eventName)
    {
        $this->mName = $eventName;
    }

    /*!
    @function setData
    @abstract Sets event data.
    @discussion Sets event data.
    @param eventData array - Event key value pairs array.
    @result Always true.
    */
    public function setData($eventData)
    {
        if (is_array($eventData)) {
            $this->mData = $eventData;
        }
    }

    /*!
    @function getEventString
    @abstract Gets event string.
    @discussion Gets event string.
    @result Event string.
    */
    public function getEventString()
    {
        $result = false;

        if (strlen($this->mDispatcherName) and strlen($this->mName)) {
            $items_count = count($this->mData);
            $result = 'wui['.$this->mDispatcherName.'][evn]='.$this->mName;

            if ($items_count) {
                $result.= '&amp;';
                reset($this->mData);
                $cont = 1;

                while (list ($key, $val) = each($this->mData)) {
                    $result.= 'wui['.$this->mDispatcherName.'][evd]['.$key.']='.urlencode($val);
                    if ($cont < $items_count) {
                        $result.= '&amp;';
                    }
                    $cont ++;
                }
            }

        }
        return $result;
    }
}
