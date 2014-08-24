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
 * @license    http://www.innomaticplatform.com/license/   BSD License
 * @link       http://www.innomaticplatform.com
 * @since      Class available since Release 5.0
*/
namespace Innomatic\Logging;

use \Innomatic\Core\InnomaticContainer;

/*!
 @class Logger
 @abstract Logging facilities
 */
class Logger
{
    /*! @var mLogFile string - Log file complete path. */
    private $mLogFile;
    const NOTICE = 1;
    const WARNING = 2;
    const ERROR = 3;
    const FAILURE = 4;
    const GENERIC = 5;
    const DEBUG = 6;

    /*!
     @function Logger
     @abstract Class constructor
     @param logFile string - Full path of the log file
     */
    public function __construct($logFile)
    {
        if (!empty($logFile)) {
            $this->mLogFile = $logFile;
        } else {
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->abort('innomatic.logger.logger.logger : Missing logfile');
        }
    }

    /*!
     @function LogEvent
     @abstract Logs an event in the log file
     @param contest string - Contest where the event has been generated, e.g. the name of the function.
     @param eventstring string - Description of the event
     @param type integer - Event type. Defaults to \Innomatic\Logging\Logger::GENERIC
     */
    public function logEvent($contest, $eventstring, $type = \Innomatic\Logging\Logger::GENERIC)
    {
        $result = false;
        if (strlen($this->mLogFile) > 0) {
            $timestamp = time();
            $date = getdate($timestamp);
            $log_event = false;

            switch ($type) {
                case \Innomatic\Logging\Logger::NOTICE :
                    $evtype = 'NOTICE';
                    $log_event = true;
                    break;

                case \Innomatic\Logging\Logger::WARNING :
                    $evtype = 'WARNING';
                    $innomatic = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');

                    switch ($innomatic->getState()) {
                        case \Innomatic\Core\InnomaticContainer::STATE_DEBUG :
                        case \Innomatic\Core\InnomaticContainer::STATE_SETUP :
                            $log_event = true;
                    }
                    break;

                case \Innomatic\Logging\Logger::ERROR :
                    $evtype = 'ERROR';
                    $log_event = true;
                    break;

                case \Innomatic\Logging\Logger::FAILURE :
                    $evtype = 'FAILURE';
                    $log_event = true;
                    break;

                case \Innomatic\Logging\Logger::GENERIC :
                    $evtype = 'GENERIC';
                    $log_event = true;
                    break;

                case \Innomatic\Logging\Logger::DEBUG :
                    $evtype = 'DEBUG';
                    $innomatic = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');
                    if ($innomatic->getState() == \Innomatic\Core\InnomaticContainer::STATE_DEBUG)
                        $log_event = true;
                    break;

                default :
                    $evtype = 'UNDEFINED';
                    $log_event = true;
                    break;
            }

            $logstr = '';
            if ($log_event) {
                $logstr = sprintf("%04s/%02s/%02s - %02s:%02s:%02s - %s - %s : %s", $date['year'], $date['mon'], $date['mday'], $date['hours'], $date['minutes'], $date['seconds'], $evtype, $contest, $eventstring);
                //$logstr = "$date[mday]/$date[mon]/$date[year] - $date[hours]:$date[minutes]:$date[seconds] - ".$evtype." - ".$contest." : ".$eventstring;
                @error_log($logstr."\n", 3, $this->mLogFile);

                $innomatic = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');
                if ($innomatic->getState() == \Innomatic\Core\InnomaticContainer::STATE_DEBUG) {
                    $dump = \Innomatic\Debug\InnomaticDump::instance('\Innomatic\Debug\InnomaticDump');
                    $dump->logs[$this->mLogFile][] = $logstr;
                }
            }
            $result = $logstr;

            if ($evtype == \Innomatic\Logging\Logger::FAILURE)
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->abort($logstring);
        }
        return $result;
    }

    /*!
     @function LogDie
     @abstract Logs an event and dies
     @param contest string - Contest where the event has been generated, e.g. the name of the function.
     @param eventstring string - Description of the event
     @param type integer - Event type. Defaults to \Innomatic\Logging\Logger::FAILURE
     */
    public function logDie($contest, $eventstring, $type = \Innomatic\Logging\Logger::FAILURE)
    {
        $logstring = $this->logEvent($contest, $eventstring, $type);
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->abort($logstring);
        die('');
    }

    /*!
     @function CleanLog
     @abstract Erases the logfile.
     */
    public function cleanLog()
    {
        $result = false;
        if (file_exists($this->mLogFile)) {
            $result = @unlink($this->mLogFile);
        }
        return $result;
    }

    /*!
     @function RawReadLog
     @abstract Reads the log file and returns it
     */
    public function rawReadLog()
    {
        $result = false;
        if (file_exists($this->mLogFile)) {
            if (file_exists($this->mLogFile)) {
                $result = file_get_contents($this->mLogFile);
            }
        }
        return $result;
    }

    /*!
     @function RawDisplayLog
     @abstract Reads the log file and displays it to the stdout
     @discussion This function is deprecated
     */
    public function rawDisplayLog()
    {
        if (file_exists($this->mLogFile)) {
            return @readfile($this->mLogFile);
        } else
            return false;
    }

    /*!
     @function RawDisplayFilterLog
     @abstract Reads the log file and displays the rows containing a certain word to the stdout
     @discussion This function is deprecated
     @param filter string - Word to be contained in the log rows
     */
    public function rawDisplayFilterLog($filter)
    {
        $result = false;
        if (file_exists($this->mLogFile)) {
            if ($fh = @fopen($this->mLogFile, 'r')) {
                $row = fgets($fh, 1000);
                while ($row != false) {
                    if (strstr($row, $filter) != false)
                        echo $row;
                    $row = fgets($fh, 1000);
                }
                fclose($fh);
                $result = true;
            }
        }
        return $result;
    }

    public function rotate($logsNumber)
    {
        $result = false;
        if (strlen($this->mLogFile) and is_file($this->mLogFile)) {
            $dir = dirname($this->mLogFile);
            $log_name = basename($this->mLogFile);
            $old_logs = array();

            if ($handle = opendir($dir)) {
                // Search for rotated logs

                while (($file = readdir($handle)) !== false) {
                    if (substr($file, 0, strlen($log_name) + 1) == $log_name.'.') {
                        $old_logs[substr($file, strlen($log_name) + 1)] = $file;
                    }
                }

                if (count($old_logs)) {
                    // Remove old logs

                    if (count($old_logs) > $logsNumber -1) {
                        foreach ($old_logs as $id => $log) {
                            if ($id > $logsNumber -1) {
                                unlink($dir.'/'.$old_logs[$id]);
                                unset($old_logs[$id]);
                            }
                        }
                    }
                }

                krsort($old_logs);

                // Move logs to be rotated

                if ($logsNumber) {
                    foreach ($old_logs as $id => $log) {
                        copy($dir.'/'.$log, $dir.'/'.$log_name.'.'. ($id +1));
                    }
                }

                // Rotate current log

                if ($logsNumber)
                    copy($dir.'/'.$log_name, $dir.'/'.$log_name.'.1');
                unlink($dir.'/'.$log_name);
                touch($dir.'/'.$log_name);

                closedir($handle);
                $result = true;
            }
        }
        return $result;
    }
}
