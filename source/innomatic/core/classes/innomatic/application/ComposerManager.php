<?php

namespace Innomatic\Application;
use Innomatic\Core\InnomaticContainer;
use Innomatic\Core\RootContainer;

class ComposerManager
{
    public function getApplicationsWithComposer()
    {
        // Holds the list of the available application providing
        // composer.json file.
        $composerList = [];
        
        $container = InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer'); 
        $rootDA = $container->getDataAccess();
        
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
    
    public function updateDependencies()
    {
        
    }
}