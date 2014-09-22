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
 * @since      Class available since Release 5.0
*/
namespace Innomatic\Process;

class Process extends \Innomatic\Util\Singleton
{
    public $pid;

    public function __construct()
    {
        $this->pid = posix_getpid();

        pcntl_signal(SIGTERM, array($this, 'SignalHandler'));
        pcntl_signal(SIGHUP, array($this, 'SignalHandler'));
        pcntl_signal(SIGCHLD, array($this, 'SignalHandler'));
    }

    public function instance()
    {
        return Singleton::instance('process');
    }

    public function getPid()
    {
        return $this->pid;
    }

    public function start()
    {
    }

    public function fork()
    {
        $child = pcntl_fork();

        if ($child == -1) {
            echo "Unable to fork\n";
        } elseif ($child) {
            // Parent
        } else {
            // Child
            $this->pid = posix_getpid();
            $this->_StartChild();
        }

        return $child;
    }

    public function signalHandler($signal)
    {
        switch ($signal) {
            case SIGTERM :
                $this->Shutdown();
                break;

            case SIGHUP :
                $this->Restart();
                break;

            case SIGCHLD :
                while (pcntl_waitpid(-1, $status, WNOHANG) > 0) {
                }
                break;

            default :
                }

        return true;
    }

    public function shutdown()
    {
        exit;
    }

    public function restart()
    {
    }

    public function waitChildren()
    {
        while (pcntl_waitpid(-1, $status, WUNTRACED) > 0) {
        }
    }

    public function _startChild()
    {
    }
}
