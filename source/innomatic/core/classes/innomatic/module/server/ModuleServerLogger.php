<?php
namespace Innomatic\Module\Server;

/**
 * Logger for Module server events.
 *
 * @author Alex Pagnoni <alex.pagnoni@innoteam.it>
 * @copyright Copyright 2004-2013 Innoteam Srl
 * @since 5.1
 */
class ModuleServerLogger
{
    /**
     * Log file name.
     *
     * @var string
     * @access private
     * @since 5.1
     */
    private $logfile;

    /**
     * Class constructor.
     *
     * @access public
     * @since 5.1
     * @param string $logfile Log file full path.
     */
    public function __construct($logfile)
    {
        $this->logfile = $logfile;
    }

    /**
     * Logs an event.
     *
     * @access public
     * @since 5.1
     * @param string $message Message to be logged.
     * @return void
     */
    public function logEvent($message)
    {
        if ($fh = fopen($this->logfile, 'a')) {
            fwrite($fh, date('Y/m/d').' - '.date('H:i:s').': '.$message."\n");
            fclose($fh);
        }
    }

    /**
     * Erases the log file
     *
     * @access public
     * @since 5.1
     * @return void
     */
    public function eraseLog()
    {
        if (file_exists($this->logfile))
            unlink($this->logfile);
    }
}
