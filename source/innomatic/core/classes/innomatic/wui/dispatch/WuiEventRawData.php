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
 * @since      Class available since Release 5.0
*/
namespace Innomatic\Wui\Dispatch;

/**
 * Event raw data handler.
 * @package WUI
 */
class WuiEventRawData
{
    private $mDispatcherName;
    private $mKey;
    private $mType;

    /*!
     @function WuiEventRawData
     */
    public function __construct($dispName, $key, $type = '')
    {
        $this->setDispatcherName($dispName);
        $this->setKey($key);
        $this->setType($type);
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
        return true;
    }

    /*!
     @function setKey
     @abstract Sets event dispatcher key name.
     @discussion Sets event dispatcher key name.
     @param key string - Name of the event key.
     @result Always true.
     */
    public function setKey($key)
    {
        $this->mKey = $key;
        return true;
    }

    /*!
     @function setType
     @abstract Sets event dispatcher type.
     @discussion Sets event dispatcher type.
     @param type string - Type.
     @result Always true.
     */
    public function setType($type)
    {
        $this->mType = $type;
        return true;
    }

    public function getDataString()
    {
        $result = 'wui';
        if ($this->mType == 'file') {
            $result .= 'files';
        }
        $result .= '['.$this->mDispatcherName.'][evd]['.$this->mKey.']';
        return $result;
    }
}
