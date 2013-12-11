<?php
namespace Innomatic\Module\Server;

/**
 * Watch dog that monitors Module server execution and restarts it in case
 * of failure.
 *
 * @author Alex Pagnoni <alex.pagnoni@innoteam.it>
 * @copyright Copyright 2004-2013 Innoteam Srl
 * @since 5.1
 */
class ModuleServerWatchDog
{
    /**
     * Launches a server process and watches it.
     *
     * @access public
     * @since 5.1
     * @param string $command Command for launching server to be watched.
     * @return void
     */
    public function watch($command)
    {
        print('Module server started and monitored by watch dog.'."\n");
        while (true) {
            $result = $this->run($command);

            if (!strpos($result, 'Module server failed.') and strpos($result, 'Module server stopped.')) {
                break;
            }

            $context = ModuleServerContext::instance('ModuleServerContext');
            $logger = new ModuleServerLogger($context->getHome().'core/log/module-watchdog.log');
            $logger->logEvent('------------------------------------------------------');
            $logger->logEvent($result);
            print('Module server restarted by watch dog.'."\n");
        }
        print('Module server and watch dog stopped.'."\n");
    }

    /**
     * Runs a command and gets its output.
     *
     * @access protected
     * @since 5.1
     * @param string $command Command to be executed.
     * @return string Command output.
     */
    protected function run($command)
    {
        ob_start();
        system($command);
        $result = ob_get_contents();
        ob_end_clean();
        return $result;
    }
}
