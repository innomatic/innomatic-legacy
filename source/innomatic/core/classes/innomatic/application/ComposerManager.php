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
 */
namespace Innomatic\Application;

use Innomatic\Core\InnomaticContainer;
use Innomatic\Core\RootContainer;
use Composer\Console\Application as ComposerApplication;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * ComposerManager handles Composer basic operations for Innomatic applications.
 * 
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 * @since 7.0.0
 */
class ComposerManager
{
    /**
     * Returns a list of the Innomatic legacy applications providing
     * a composer.json file.
     *  
     * @return array
     */
    public function getApplicationsWithComposer()
    {
        // Holds the list of the available application providing
        // composer.json file.
        $composerList = [];
        
        $container = InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer'); 
        $rootDA    = $container->getDataAccess();
        
        // Get the list of the installed applications.
        $applications = $rootDA->execute(
            'SELECT appid FROM applications'
        );
        
        // Home of the applications directories.
        $applicationsHome = RootContainer::instance('\Innomatic\Core\RootContainer')
            ->getLegacyHome()
            . 'innomatic/core/applications/';

        // Scan the applications.
        while (!$applications->eof) {
            $composerFile = $applicationsHome . $applications->getFields('appid') . '/composer.json';
            if (file_exists($composerFile)) {
                $composerList[] = ['name' => $applications->getFields('appid'), 'path' => $composerFile];
            }
            $applications->moveNext();
        }
        
        return $composerList;
    }
    
    /**
     * Updates composer dependencies.
     */
    public function updateDependencies()
    {
        $container = InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer'); 

        // Keep track of the current directory
        $previousDirectory = getcwd();
        
        // Retrieve the Innomatic Platform root directory
        $platformHome = RootContainer::instance('\Innomatic\Core\RootContainer')
            ->getPlatformHome();

        // Switch current directory to the Innomatic Platform root
        chdir($platformHome);
        
        // Call composer install command
        $input = new ArrayInput(array('command' => 'install'));
        if ($container->getInterface() == InnomaticContainer::INTERFACE_CONSOLE) {
            $output = new ConsoleOutput();
        } else {
            $output = new BufferedOutput();
        }
        $application = new ComposerApplication();
        // Prevent application run method from exiting the script
        $application->setAutoExit(false);
        $application->run($input, $output);
        
        // Switch back to the previous current directory
        chdir($previousDirectory);
        
        // Switch back to the standard Innomatic PHP error handler
        set_error_handler(array($container, 'errorHandler'));
    }
}