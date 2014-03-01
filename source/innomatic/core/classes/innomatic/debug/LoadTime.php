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
namespace Innomatic\Debug;

/**
 * Code load tracking.
 *
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 * @copyright Copyright 2012 Innoteam Srl
 * @since 1.0
 */
class LoadTime
{
    public $history = array ();
    protected $startTime;
    protected $mode;
    protected $item = 0;
    const LOADTIME_MODE_CONTINUOUS = 1;
    const LOADTIME_MODE_STARTSTOP = 2;

    public function __construct($mode = self::LOADTIME_MODE_CONTINUOUS)
    {
        switch ($mode) {
            case self::LOADTIME_MODE_CONTINUOUS :
            case self::LOADTIME_MODE_STARTSTOP :
                $this->mode = $mode;
                break;
        }
    }

    // Marks the line
    //
    public function mark($section)
    {
        if (
            $this->mode == self::LOADTIME_MODE_CONTINUOUS
            and empty ($this->history[$section])
        ) {
            $exacttime = $this->getExactTime();
            if (empty ($this->startTime))
                $this->startTime = $exacttime;
            $this->history[$section] = $exacttime - $this->startTime;
        }
    }

    public function start($section)
    {
        if ($this->mode == self::LOADTIME_MODE_STARTSTOP)
            $this->history[$section] = $this->getExactTime();
    }

    public function stop($section)
    {
        if (
            $this->mode == self::LOADTIME_MODE_STARTSTOP
            and isset ($this->history[$section])
        ) {
            $this->history[$section] = $this->getExactTime()
                - $this->history[$section];
        }
    }

    public function advanceCounter()
    {
        return ++$this->item;
    }

    // Returns historical log of the marks
    //
    public function getHistory()
    {
        return $this->history;
    }

    // Returns load value of a certain section
    //
    public function getSectionLoad($section)
    {
        return $this->history[$section];
    }

    // Returns total load time
    //
    public function getTotalTime()
    {
        if ($this->mode == self::LOADTIME_MODE_CONTINUOUS) {
            end($this->history);
            return current($this->history);
        } else {
            $total = 0;

            while (list ($section, $load) = each($this->history)) {
                $total += $load;
            }

            return $total;
        }
        //return $this->history[sizeof( $this->history )-1];
    }

    // Returns current time from the first mark
    //
    public function getCurrentTime()
    {
        return $this->getExactTime() - $this->startTime;
    }

    public function getExactTime()
    {
        $currtime = explode(' ', microtime());
        return ($currtime[1] + $currtime[0]);
    }
}
