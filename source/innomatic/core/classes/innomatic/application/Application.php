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

use \Innomatic\Core\Container;

/**
 * Class for handling basic application operations.
 *
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 */
class Application
{
    /**
     * Innomatic container
     *
     * @var \Innomatic\Core\Container
     * @access protected
     */
    protected $container;

    /*! @public rootda DataAccess class - Innomatic database handler. */
    public $rootda;
    /*! @public domainda DataAccess class - Tenant dataaccess handler. */
    public $domainda;
    /*! @public appname string - Application id name. */
    public $appname;
    /*! @public unmetdeps array - Array of unmet dependencies. */
    public $unmetdeps = array();
    /*! @public unmetsuggs array - Array of unmet suggestions. */
    public $unmetsuggs = array();
    /*! @public eltypes array - Application component types. */
    public $eltypes;
    /*! @public serial int - Application serial. */
    public $serial;
    /*! @public onlyextension bool - True if the application is an extension
only application. */
    public $onlyextension = true;
    public $basedir;

    const INSTALL_MODE_INSTALL = 0;
    const INSTALL_MODE_UNINSTALL = 1;
    const INSTALL_MODE_UPDATE = 2;
    const INSTALL_MODE_ENABLE = 3;
    const INSTALL_MODE_DISABLE = 4;

    const UPDATE_MODE_ADD = 0;
    const UPDATE_MODE_REMOVE = 1;
    const UPDATE_MODE_CHANGE = 2;

    /*!
     @function Application

     @abstract Application constructor.

     @param rootda DataAccess class - Innomatic database handler.
     @param modserial int - serial number of the application.
     */
    public function __construct(\Innomatic\Dataaccess\DataAccess $rootda, $modserial = 0)
    {
        $this->container = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');
        $this->rootda = $rootda;
        $this->serial = $modserial;
        $this->eltypes = new ApplicationComponentFactory($rootda);
        $this->eltypes->FillTypes();
    }

    /*!
     @function Install

     @abstract Install a application.

     @discussion If the application has been already installed, it will be updated.

     @param tmpfilepath string - Full path of the temporary application file.

     @result True if the application has been installed.
     */
    public function install($tmpfilepath, $updateOnce = false)
    {
        // Checks if the given path is a directory. This may happen when not
        // giving a file to the application installation page.
        if (is_dir($tmpfilepath)) {
            return false;
        }

        $result = false;

        $innomatic = $this->container;

        if ($innomatic->getState() == \Innomatic\Core\InnomaticContainer::STATE_DEBUG) {
            $innomatic->getLoadTimer()->Mark('applicationinstallstart');
        }

        if (file_exists($tmpfilepath)) {
            // Moves temp file to applications repository and extracts it
            //
            $fname = $this->container->getHome()
                . 'core/applications/' . basename($tmpfilepath);
            @copy($tmpfilepath, $fname);
            $basetmpdir = $tmpdir = $this->container->getHome()
                . 'core/temp/appinst/' . md5(microtime());
            @mkdir($tmpdir, 0755);
            $olddir = getcwd();
            @chdir($tmpdir);

            if (substr($fname, -4) == '.zip') {
            } else {
                try {
                    $appArchive = new \PharData($fname);
                    $tarFileName = substr($fname, 0, strpos($fname, '.')).'.tar';
                    if (file_exists($tarFileName)) {
                        unlink($tarFileName);
                    }
                    $appArchive->decompress();
                } catch (\BadMethodCallException $e) {

                }

                try {
                    $appArchive->extractTo($tmpdir);
                } catch (Exception $e) {

                }
            }

            // Checks if the files are into a directory instead of the root
            //
            if (!@is_dir($tmpdir.'/setup')) {
                $dhandle = opendir($tmpdir);
                while (false != ($file = readdir($dhandle))) {
                    if (
                        $file != '.' && $file != '..' && (
                            is_dir($tmpdir.'/'.$file.'/setup') or is_dir($tmpdir.'/'.$file.'/innomatic/setup')
                        )
                    ) {
                        if (is_dir($tmpdir.'/'.$file.'/setup')) {
                            $tmpdir = $tmpdir.'/'.$file;
                        } else {
                            $tmpdir = $tmpdir.'/'.$file.'/innomatic';
                        }
                        break;
                    }
                }
                closedir($dhandle);
            }

            $this->basedir = $tmpdir;

            // Checks for definition and structure files
            //
            if (file_exists($tmpdir.'/setup/bundle.ini')) {
                $applicationsArray = file($tmpdir.'/setup/bundle.ini');
                $result = true;

                while (list (, $application) = each($applicationsArray)) {
                    $application = trim($application);
                    if (strlen($application) and file_exists($tmpdir.'/applications/'.$application)) {
                        $tempApplication = new Application($this->rootda);
                        if (!$tempApplication->Install($tmpdir.'/applications/'.$application))
                        $result = false;
                    }
                }
            } elseif (file_exists($tmpdir.'/setup/application.xml')) {
                $genconfig = $this->parseApplicationDefinition(
                    $tmpdir . '/setup/application.xml'
                );
                $this->appname = $genconfig['ApplicationIdName'];

                // Checks if the application has been already installed
                //
                $tmpquery = $this->rootda->execute(
                    'SELECT id,appfile FROM applications WHERE appid='
                    . $this->rootda->formatText($this->appname)
                );
                if (!$tmpquery->getNumberRows()) {
                    // Application is new, so it will be installed
                    //

                    // Dependencies check
                    //
                    $this->unmetdeps = array();
                    $this->unmetsuggs = array();

                    $appdeps = new ApplicationDependencies();
                    $deps = $appdeps->explodeDependencies($genconfig['ApplicationDependencies']);
                    $suggs = $appdeps->explodeDependencies($genconfig['ApplicationSuggestions']);

                    if ($deps != false) {
                        $this->unmetdeps = $appdeps->checkApplicationDependencies(0, '', $deps);
                    } else {
                        $this->unmetdeps = false;
                    }

                    // Fetch missing dependencies from AppCentral servers.
                    if ($this->unmetdeps != false) {
                       $appCentral = new AppCentralHelper();
                       $resolveResult = $appCentral->resolveDependencies($this->unmetdeps); 
                       
                       // Refresh the unmet dependencies list.
                       $this->unmetdeps = [];
                       foreach ($resolveResult['missing'] as $missingName => $missingVersion) {
                           $this->unmetdeps[] = $missingName.'['.$missingVersion.']';
                       }
                    }

                    // Suggestions check
                    //
                    if ($suggs != false) {
                        $unmetsuggs = $appdeps->checkApplicationDependencies(0, '', $suggs);
                        if (is_array($unmetsuggs))
                        $this->unmetsuggs = $unmetsuggs;
                    }

                    // If dependencies are ok, go on
                    //
                    if ($this->unmetdeps == false) {
                        // Gets serial number for the application
                        //
                        $this->serial = $this->rootda->getNextSequenceValue(
                            'applications_id_seq'
                        );
                        $this->rootda->execute(
                            'INSERT INTO applications VALUES ( ' .
                            $this->serial .
                            ','.$this->rootda->formatText($genconfig['ApplicationIdName']) .
                            ','.$this->rootda->formatText($genconfig['ApplicationVersion']) .
                            ','.$this->rootda->formatText($genconfig['ApplicationDate']) .
                            ','.$this->rootda->formatText($genconfig['ApplicationDescription']) .
                            ','.$this->rootda->formatText(basename($tmpfilepath)) .
                            ','.$this->rootda->formatText($this->rootda->fmtfalse) .
                            ','.$this->rootda->formatText($genconfig['ApplicationAuthor']) .
                            ','.$this->rootda->formatText($genconfig['ApplicationAuthorEmail']) .
                            ','.$this->rootda->formatText($genconfig['ApplicationAuthorWeb']) .
                            ','.$this->rootda->formatText($genconfig['ApplicationSupportEmail']) .
                            ','.$this->rootda->formatText($genconfig['ApplicationBugsEmail']) .
                            ','.$this->rootda->formatText($genconfig['ApplicationCopyright']) .
                            ','.$this->rootda->formatText($genconfig['ApplicationLicense']) .
                            ','.$this->rootda->formatText($genconfig['ApplicationLicenseFile']) .
                            ','.$this->rootda->formatText($genconfig['ApplicationChangesFile']) .
                            ','.$this->rootda->formatText($genconfig['ApplicationMaintainer']) .
                            ','.$this->rootda->formatText($genconfig['ApplicationMaintainerEmail']) .
                            ','.$this->rootda->formatText($genconfig['ApplicationCategory']) .
                            ','.$this->rootda->formatText($genconfig['ApplicationIconFile']) .
                            ')'
                        );

                        // Application dir creation
                        //
                        @mkdir(
                            $this->container->getHome()
                            . 'core/applications/'
                            . $genconfig['ApplicationIdName'],
                            0755
                        );

                        // Defs files
                        //
                        if ($dhandle = @opendir($tmpdir.'/setup')) {
                            while (false != ($file = readdir($dhandle))) {
                                if ($file != '.' && $file != '..' && is_file($tmpdir.'/setup/'.$file)) {
                                    @copy(
                                        $tmpdir . '/setup/' . $file,
                                        $this->container->getHome()
                                        . 'core/applications/'
                                        . $genconfig['ApplicationIdName']
                                        . '/' . $file
                                    );
                                }
                            }
                            closedir($dhandle);
                        }

                        // Adds applications dependencies
                        //
                        $appdeps->addDependenciesArray(
                            $genconfig['ApplicationIdName'],
                            $deps,
                            ApplicationDependencies::TYPE_DEPENDENCY
                        );
                        $appdeps->addDependenciesArray(
                            $genconfig['ApplicationIdName'],
                            $suggs,
                            ApplicationDependencies::TYPE_SUGGESTION
                        );

                        $this->setOptions(explode(',', trim($genconfig['ApplicationOptions'], ' ,')));

                        $this->HandleStructure(
                            $tmpdir.'/setup/application.xml',
                            Application::INSTALL_MODE_INSTALL,
                            $tmpdir
                        );

                        if (
                            strlen($genconfig['ApplicationLicenseFile'])
                            and file_exists($tmpdir.'/setup/'.$genconfig['ApplicationLicenseFile'])
                        ) {
                            @copy(
                                $tmpdir.'/setup/'.$genconfig['ApplicationLicenseFile'],
                                $this->container->getHome()
                                .'core/applications/'.$genconfig['ApplicationIdName'].'/'
                                .$genconfig['ApplicationLicenseFile']
                            );
                        }
                        if (
                            strlen($genconfig['ApplicationChangesFile'])
                            and file_exists($tmpdir.'/setup/'.$genconfig['ApplicationChangesFile'])
                        ) {
                            @copy(
                                $tmpdir.'/setup/'.$genconfig['ApplicationChangesFile'],
                                $this->container->getHome()
                                .'core/applications/'.$genconfig['ApplicationIdName'].'/'
                                .$genconfig['ApplicationChangesFile']
                            );
                        }
                        if (
                            strlen($genconfig['ApplicationIconFile'])
                            and file_exists($tmpdir.'/setup/'.$genconfig['ApplicationIconFile'])
                        ) {
                            @copy(
                                $tmpdir.'/setup/'.$genconfig['ApplicationIconFile'],
                                $this->container->getHome()
                                .'core/applications/'.$genconfig['ApplicationIdName'].'/'
                                .$genconfig['ApplicationIconFile']
                            );
                        }

                        // Checks if it is an extension application
                        //
                        $genconfig = $this->parseApplicationDefinition($tmpdir.'/setup/application.xml');

                        $ext = $this->rootda->fmtfalse;

                        if ($genconfig['ApplicationIsExtension'] == 'y') {
                            $ext = $this->rootda->fmttrue;
                            $this->onlyextension = true;
                        } elseif ($genconfig['ApplicationIsExtension'] == 'n') {
                            $ext = $this->rootda->fmtfalse;
                            $this->onlyextension = false;
                        } elseif ($this->onlyextension) {
                            $ext = $this->rootda->fmttrue;
                        }

                        $this->rootda->execute(
                            'UPDATE applications SET onlyextension='.$this->rootda->formatText($ext)
                            .' WHERE appid='.$this->rootda->formatText($this->appname)
                        );
                        $result = true;

                        if (
                            $this->container->getConfig()->Value(
                                'SecurityAlertOnApplicationOperation'
                            ) == '1'
                        ) {
                            $innomaticSecurity = new \Innomatic\Security\SecurityManager();
                            $innomaticSecurity->SendAlert('Application '.$this->appname.' has been installed');
                            unset($innomaticSecurity);
                        }

                        if ($result == true) {
                            if (
                                $this->container->getEdition()
                                == \Innomatic\Core\InnomaticContainer::EDITION_SINGLETENANT and $this->appname != 'innomatic'
                                and $ext != $this->rootda->fmttrue
                            ) {
                                $domainsQuery = \Innomatic\Core\InnomaticContainer::instance(
                                    '\Innomatic\Core\InnomaticContainer'
                                )->getDataAccess()->execute('SELECT id FROM domains');
                                if ($domainsQuery->getNumberRows()) {
                                    $this->Enable($domainsQuery->getFields('id'));
                                }
                            }
                            $log = $this->container->getLogger();
                            $log->logEvent('Innomatic', 'Installed application '.$this->appname, \Innomatic\Logging\Logger::NOTICE);
                        }
                    }
                } else {
                    $appdata = $tmpquery->getFields();
                    $this->serial = $appdata['id'];

                    // Application will be updated
                    //
                    if ($this->serial) {
                        // Dependencies check
                        //
                        $this->unmetdeps = array();
                        $this->unmetsuggs = array();

                        $appdeps = new ApplicationDependencies();
                        $deps = $appdeps->explodeDependencies($genconfig['ApplicationDependencies']);
                        $suggs = $appdeps->explodeDependencies($genconfig['ApplicationSuggestions']);

                        if ($deps != false) {
                            $this->unmetdeps = $appdeps->checkApplicationDependencies(0, '', $deps);
                        } else {
                            $this->unmetdeps = false;
                        }

                        // Suggestions check
                        //
                        if ($suggs != false) {
                            $unmetsuggs = $appdeps->checkApplicationDependencies(0, '', $suggs);
                            if (is_array($unmetsuggs))
                            $this->unmetsuggs = $unmetsuggs;
                        }

                        // Fetch missing dependencies from AppCentral servers.
                        if ($this->unmetdeps != false) {
                            $appCentral = new AppCentralHelper();
                            $resolveResult = $appCentral->resolveDependencies($this->unmetdeps); 
                       
                            // Refresh the unmet dependencies list.
                            $this->unmetdeps = [];
                            foreach ($resolveResult['missing'] as $missingName => $missingVersion) {
                                $this->unmetdeps[] = $missingName.'['.$missingVersion.']';
                            }
                        }
                        
                        // If dependencies are ok, go on
                        //
                        if ($this->unmetdeps == false) {
                            // Creates lock file
                            //
                            touch(
                                $this->container->getHome()
                                .'core/temp/upgrading_system_lock'
                            );

                            // :WARNING: evil 20020506: possible problems on Windows systems
                            // It has a 'permission denied'.

                            // Removes old application file
                            //
                            if (
                                (basename($fname) != $appdata['appfile'])
                                and (file_exists(
                                    $this->container->getHome()
                                    .'core/applications/'.$appdata['appfile']
                                )
                                )
                            )
                            @unlink(
                                $this->container->getHome()
                                .'core/applications/'.$appdata['appfile']
                            );

                            // Updates applications table
                            //
                            $this->rootda->execute(
                                'UPDATE applications SET appversion='.
                                $this->rootda->formatText($genconfig['ApplicationVersion']).
                                ', appdate='.$this->rootda->formatText($genconfig['ApplicationDate']).
                                ', appdesc='.$this->rootda->formatText($genconfig['ApplicationDescription']).
                                ', appfile='.$this->rootda->formatText(basename($tmpfilepath)).
                                ', author='.$this->rootda->formatText($genconfig['ApplicationAuthor']).
                                ', authoremail='.$this->rootda->formatText($genconfig['ApplicationAuthorEmail']).
                                ', authorsite='.$this->rootda->formatText($genconfig['ApplicationAuthorWeb']).
                                ', supportemail='.$this->rootda->formatText($genconfig['ApplicationSupportEmail']).
                                ', bugsemail='.$this->rootda->formatText($genconfig['ApplicationBugsEmail']).
                                ', copyright='.$this->rootda->formatText($genconfig['ApplicationCopyright']).
                                ', license='.$this->rootda->formatText($genconfig['ApplicationLicense']).
                                ', licensefile='.$this->rootda->formatText($genconfig['ApplicationLicenseFile']).
                                ', changesfile='.$this->rootda->formatText($genconfig['ApplicationChangesFile']).
                                ', maintainer='.$this->rootda->formatText($genconfig['ApplicationMaintainer']).
                                ', maintaineremail='.
                                $this->rootda->formatText($genconfig['ApplicationMaintainerEmail']).
                                ', category='.$this->rootda->formatText($genconfig['ApplicationCategory']).
                                ', iconfile='.$this->rootda->formatText($genconfig['ApplicationIconFile']).
                                ' WHERE id='. (int) $this->serial
                            );
                            $genconfig = $this->parseApplicationDefinition($tmpdir.'/setup/application.xml');

                            // Script files - only before handlestructure
                            //
                            if ($dhandle = @opendir($tmpdir.'/setup')) {
                                while (false != ($file = readdir($dhandle))) {
                                    if (
                                        $file != '.'
                                        and $file != '..'
                                        and $file != 'application.xml'
                                        and is_file($tmpdir.'/setup/'.$file)
                                    ) {
                                        @copy(
                                            $tmpdir.'/setup/'.$file,
                                            $this->container->getHome().
                                            'core/applications/'.$genconfig['ApplicationIdName'].'/'.$file
                                        );
                                    }
                                }
                                closedir($dhandle);
                            }

                            $this->HandleStructure(
                                $tmpdir.'/setup/application.xml',
                                Application::INSTALL_MODE_UPDATE,
                                $tmpdir
                            );

                            if (
                                strlen($genconfig['ApplicationLicenseFile'])
                                and file_exists($tmpdir.'/setup/'.$genconfig['ApplicationLicenseFile'])
                            ) {
                                @copy(
                                    $tmpdir.'/setup/'.$genconfig['ApplicationLicenseFile'],
                                    $this->container->getHome().
                                    'core/applications/'.$genconfig['ApplicationIdName'].'/'.
                                    $genconfig['ApplicationLicenseFile']
                                );
                            }
                            if (
                                strlen($genconfig['ApplicationChangesFile'])
                                and file_exists($tmpdir.'/setup/'.$genconfig['ApplicationChangesFile'])
                            ) {
                                @copy(
                                    $tmpdir.'/setup/'.$genconfig['ApplicationChangesFile'],
                                    $this->container->getHome().
                                    'core/applications/'.$genconfig['ApplicationIdName'].'/'.
                                    $genconfig['ApplicationChangesFile']
                                );
                            }
                            if (
                                strlen($genconfig['ApplicationIconFile'])
                                and file_exists($tmpdir.'/setup/'.$genconfig['ApplicationIconFile'])
                            ) {
                                @copy(
                                    $tmpdir.'/setup/'.$genconfig['ApplicationIconFile'],
                                    $this->container->getHome().
                                    'core/applications/'.$genconfig['ApplicationIdName'].'/'.
                                    $genconfig['ApplicationIconFile']
                                );
                            }

                            // setup files - only after handlestructure
                            //
                            @copy(
                                $tmpdir.'/setup/application.xml',
                                $this->container->getHome().
                                'core/applications/'.$genconfig['ApplicationIdName'].'/application.xml'
                            );
                            // Checks if it is an extension application
                            //
                            $ext = $this->rootda->fmtfalse;

                            if ($genconfig['ApplicationIsExtension'] == 'y') {
                                $ext = $this->rootda->fmttrue;
                                $this->onlyextension = true;
                            } elseif (
                                $genconfig['ApplicationIsExtension'] == 'n') {
                                $ext = $this->rootda->fmtfalse;
                                $this->onlyextension = false;
                            } elseif (
                                $this->onlyextension) {
                                $ext = $this->rootda->fmttrue;
                            }

                            $this->rootda->execute(
                                'UPDATE applications SET onlyextension='.$this->rootda->formatText($ext).
                                ' WHERE appid='.$this->rootda->formatText($this->appname)
                            );

                            $this->setOptions(explode(',', trim($genconfig['ApplicationOptions'], ' ,')));

                            if ($this->appname != 'innomatic') {
                                // Remove old dependencies
                                //
                                $appdeps->removeAllDependencies($this->serial);

                                // Adds new Applications dependencies
                                //
                                $appdeps->addDependenciesArray(
                                    $genconfig['ApplicationIdName'],
                                    $deps,
                                    ApplicationDependencies::TYPE_DEPENDENCY
                                );
                                $appdeps->addDependenciesArray(
                                    $genconfig['ApplicationIdName'],
                                    $suggs,
                                    ApplicationDependencies::TYPE_SUGGESTION
                                );
                            }

                            $result = true;

                            if (function_exists('apc_reset_cache'))
                            apc_reset_cache();

                            if ($updateOnce == false) {
                                $this->Install($tmpfilepath, true);

                                // Removes lock file
                                //
                                unlink(
                                    $this->container->getHome().
                                    'core/temp/upgrading_system_lock'
                                );

                                if (
                                    \Innomatic\Core\InnomaticContainer::instance(
                                        '\Innomatic\Core\InnomaticContainer'
                                    )->getConfig()->Value('SecurityAlertOnApplicationOperation') == '1'
                                ) {
                                    $innomaticSecurity = new \Innomatic\Security\SecurityManager();
                                    $innomaticSecurity->SendAlert('Application '.$this->appname.' has been updated');
                                    unset($innomaticSecurity);
                                }

                                if ($result == true) {
                                    $log = $this->container->getLogger();
                                    $log->logEvent('Innomatic', 'Updated application '.$this->appname, \Innomatic\Logging\Logger::NOTICE);
                                }
                            }
                        }
                        /*
                         else $this->mLog->logEvent( 'innomatic.applications.applications.install',
                         'Structure definition file for application '.$this->appname.
                         ' does not exists', \Innomatic\Logging\Logger::ERROR );
                         */
                    } else {
                        $log = $this->container->getLogger();

                        $log->logEvent(
                            'innomatic.applications.applications.install',
                            'Empty application serial',
                            \Innomatic\Logging\Logger::ERROR
                        );
                    }
                }
            } else {
                $log = $this->container->getLogger();

                if (!file_exists($tmpdir.'/setup/application.xml'))
                $log->logEvent(
                    'innomatic.applications.applications.install',
                    'Application structure file '.$tmpdir.'/setup/application.xml'.' not found',
                    \Innomatic\Logging\Logger::ERROR
                );
            }

            // Cleans up temp stuff
            //
            @chdir($olddir);
            \Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree($basetmpdir);
            if (file_exists($tmpfilepath))
            @unlink($tmpfilepath);
        } else {
            if (!file_exists($tmpfilepath)) {
                $log = $this->container->getLogger();
                $log->logEvent(
                    'innomatic.applications.applications.install',
                    'Temporary application file ('.$tmpfilepath.') does not exists',
                    \Innomatic\Logging\Logger::ERROR
                );
            }
        }

        if ($innomatic->getState() == \Innomatic\Core\InnomaticContainer::STATE_DEBUG) {
            $innomatic->getLoadTimer()->Mark('applicationinstallend');
            $log = $this->container->getLogger();

            $log->logEvent(
                'innomatic.applications.application.install',
                'Application installation load time: '.
                $innomatic->getLoadTimer()->getSectionLoad('applicationinstallend'),
                \Innomatic\Logging\Logger::DEBUG
            );
        }

        return $result;
    }

    /*!
     @function Uninstall

     @abstract Uninstall a application.

     @result True if successfully uninstalled.
     */
    public function uninstall()
    {
        $result = false;

        if ($this->serial) {
            // Checks if the application exists in applications table
            //
            $modquery = $this->rootda->execute('SELECT * FROM applications WHERE id='. (int) $this->serial);

            if ($modquery->getNumberRows() == 1) {
                $appdata = $modquery->getFields();

                // Checks if the application is Innomatic itself
                //
                if ($appdata['appid'] != 'innomatic') {
                    // Checks if the structure file still exists
                    //
                    if (
                        file_exists(
                            $this->container->getHome().
                            'core/applications/'.$appdata['appid'].'/application.xml'
                        )
                    ) {
                        $this->appname = $appdata['appid'];

                        // Checks if there are depengind applications
                        //
                        $appdeps = new ApplicationDependencies();
                        $pendingdeps = $appdeps->CheckDependingApplications($appdata['appid']);

                        // If dependencies are ok, go on
                        //
                        if ($pendingdeps == false) {
                            if ($appdata['onlyextension'] != $this->rootda->fmttrue)
                            $this->disableFromAllDomains($appdata['appid']);

                            $this->HandleStructure(
                                $this->container->getHome().
                                'core/applications/'.$appdata['appid'].'/application.xml',
                                Application::INSTALL_MODE_UNINSTALL,
                                $this->container->getHome().'core/temp/appinst/'
                            );

                            // Removes application archive and directory
                            //
                            if (
                                file_exists(
                                    $this->container->getHome().
                                    'core/applications/'.$appdata['appfile']
                                )
                            ) {
                                @unlink(
                                    $this->container->getHome().
                                    'core/applications/'.$appdata['appfile']
                                );
                            }
                            \Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree(
                                $this->container->getHome().
                                'core/applications/'.$appdata['appid']
                            );

                            // Application rows in applications table
                            //
                            $this->rootda->execute('DELETE FROM applications WHERE id='. (int) $this->serial);

                            // Remove cached items
                            //
                            $cacheGC = new \Innomatic\Datatransfer\Cache\CacheGarbageCollector();
                            $cacheGC->removeApplicationItems($appdata['appid']);

                            // Remove pending actions
                            \Innomatic\Scripts\PendingActionsUtils::removeByApplication($appdata['appid']);

                            // Remove dependencies
                            //
                            $appdeps->removeAllDependencies($this->serial);
                            $this->serial = 0;
                            $result = true;

                            if (
                                $this->container->getConfig()
                                ->Value('SecurityAlertOnApplicationOperation') == '1'
                            ) {
                                $innomaticSecurity = new \Innomatic\Security\SecurityManager();
                                $innomaticSecurity->SendAlert('Application '.$appdata['appid'].' has been removed');
                                unset($innomaticSecurity);
                            }
                        } else {
                            $this->unmetdeps = $pendingdeps;
                        }

                        if ($result == true) {
                            $log = $this->container->getLogger();
                            $log->logEvent('Innomatic', 'Uninstalled application '.$this->appname, \Innomatic\Logging\Logger::NOTICE);
                        }
                    } else {
                        $log = $this->container->getLogger();
                        $log->logEvent(
                            'innomatic.applications.applications.uninstall',
                            'Structure file '.$this->container->getHome().
                            'core/applications/'.$appdata['appid'].'/application.xml'.' for application '.
                            $appdata['appid'].' was not found',
                            \Innomatic\Logging\Logger::ERROR
                        );
                    }
                } else {
                    $log = $this->container->getLogger();
                    $log->logEvent(
                        'innomatic.applications.applications.uninstall',
                        'Cannot uninstall Innomatic',
                        \Innomatic\Logging\Logger::ERROR
                    );
                }
            } else {
                $log = $this->container->getLogger();
                $log->logEvent(
                    'innomatic.applications.applications.uninstall',
                    'A application with serial '.$this->serial.' was not found in applications table',
                    \Innomatic\Logging\Logger::ERROR
                );
            }

            $modquery->free();
        } else {
            $log = $this->container->getLogger();
            $log->logEvent(
                'innomatic.applications.applications.uninstall',
                'Empty application serial',
                \Innomatic\Logging\Logger::ERROR
            );
        }
        return $result;
    }

    /*!
     @function Update

     @abstract Updates a application.

     @discussion Alias for Application->Install.

     @param tmpfilepath string - Full path of the temporary application file.

     @result True if successfully updated.
     */
    public function update($tmpfilepath)
    {
        return $this->Install($tmpfilepath);
    }

    /*!
     @function setup

     @abstract Setup Innomatic structure.

     @discussion This method is called only once at Innomatic setup phase.

     @param tmpdir string - temporary directory.

     @result True if it's all right.
     */
    public function setup($tmpdir)
    {
        $result = false;

        // Checks for definition and structure files
        //
        if (file_exists($tmpdir.'setup/application.xml')) {
            $genconfig = $this->parseApplicationDefinition($tmpdir.'/setup/application.xml');
            $this->appname = $genconfig['ApplicationIdName'];

            // Checks if Innomatic has been already installed
            //
            $tmpquery = $this->rootda->execute(
                'SELECT id FROM applications WHERE appid='.$this->rootda->formatText($this->appname)
            );

            if (!$tmpquery->getNumberRows()) {
                // Gets serial number for the application
                //
                $this->serial = $this->rootda->getNextSequenceValue('applications_id_seq');

                if (
                    $this->rootda->execute(
                        'INSERT INTO applications VALUES ( '.$this->serial.
                        ','.$this->rootda->formatText($genconfig['ApplicationIdName']).
                        ','.$this->rootda->formatText($genconfig['ApplicationVersion']).
                        ','.$this->rootda->formatText($genconfig['ApplicationDate']).
                        ','.$this->rootda->formatText($genconfig['ApplicationDescription']).
                        ','.$this->rootda->formatText('').
                        ','.$this->rootda->formatText($this->rootda->fmtfalse).
                        ','.$this->rootda->formatText($genconfig['ApplicationAuthor']).
                        ','.$this->rootda->formatText($genconfig['ApplicationAuthorEmail']).
                        ','.$this->rootda->formatText($genconfig['ApplicationAuthorWeb']).
                        ','.$this->rootda->formatText($genconfig['ApplicationSupportEmail']).
                        ','.$this->rootda->formatText($genconfig['ApplicationBugsEmail']).
                        ','.$this->rootda->formatText($genconfig['ApplicationCopyright']).
                        ','.$this->rootda->formatText($genconfig['ApplicationLicense']).
                        ','.$this->rootda->formatText($genconfig['ApplicationLicenseFile']).
                        ','.$this->rootda->formatText($genconfig['ApplicationChangesFile']).
                        ','.$this->rootda->formatText($genconfig['ApplicationMaintainer']).
                        ','.$this->rootda->formatText($genconfig['ApplicationMaintainerEmail']).
                        ','.$this->rootda->formatText($genconfig['ApplicationCategory']).
                        ','.$this->rootda->formatText($genconfig['ApplicationIconFile']).
                        ')'
                    )
                ) {
                // Application dir creation
                //
                if (
                    !file_exists(
                        $this->container->getHome().
                        'core/applications/'.$genconfig['ApplicationIdName']
                    )
                )
                @mkdir(
                    $this->container->getHome().
                    'core/applications/'.$genconfig['ApplicationIdName'], 0755
                );

                // setup files
                //
                $dhandle = @opendir($tmpdir.'/setup');
                if ($dhandle) {
                    while (false != ($file = readdir($dhandle))) {
                        if ($file != '.' && $file != '..' && is_file($tmpdir.'/setup/'.$file)) {
                            @copy(
                                $tmpdir.'/setup/'.$file,
                                $this->container->getHome().
                                'core/applications/'.$genconfig['ApplicationIdName'].'/'.$file
                            );
                        }
                    }
                    closedir($dhandle);
                }

                $result = $this->HandleStructure(
                    $tmpdir.'setup/application.xml', Application::INSTALL_MODE_INSTALL, $tmpdir, 0, true
                );

                if (
                    strlen($genconfig['ApplicationLicenseFile'])
                    and file_exists($tmpdir.'/setup/'.$genconfig['ApplicationLicenseFile'])
                ) {
                    @copy(
                        $tmpdir.'/setup/'.$genconfig['ApplicationLicenseFile'],
                        $this->container->getHome().
                        'core/applications/'.$genconfig['ApplicationIdName'].
                        '/'.$genconfig['ApplicationLicenseFile']
                    );
                } else {
                    $log = $this->container->getLogger();
                    $log->logEvent('Innomatic', 'Unable to install Innomatic', \Innomatic\Logging\Logger::ERROR);
                }
                } else {
                    $log = $this->container->getLogger();
                    $log->logEvent(
                        'innomatic.applications.applications.setup',
                        'Unable to insert Innomatic application row in applications table',
                        \Innomatic\Logging\Logger::ERROR
                    );
                }
            } else {
                $log = $this->container->getLogger();
                $log->logEvent(
                    'innomatic.applications.applications.setup',
                    'Attempted to resetup Innomatic',
                    \Innomatic\Logging\Logger::ERROR
                );
            }
        } else {
            $log = $this->container->getLogger();

            if (!file_exists($tmpdir.'setup/application.xml'))
            $log->logEvent(
                'innomatic.applications.applications.setup',
                'Innomatic structure file '.$tmpdir.'setup/application.xml not found',
                \Innomatic\Logging\Logger::ERROR
            );
        }
        return $result;
    }

    /*!
     @function Enable

     @abstract Enables a application to a domain.

     @param domainid string - id name of the domain.

     @result True if successfully enabled.
     */
    public function enable($domainid)
    {
        $result = false;
        $hook = new \Innomatic\Process\Hook($this->rootda, 'innomatic', 'application.enable');
        if (
            $hook->callHooks(
                'calltime',
                $this,
                array(
                    'domainserial' => $domainid,
                    'modserial' => $this->serial
                )
            ) == \Innomatic\Process\Hook::RESULT_OK
        ) {
            if ($this->serial) {
                // Checks if the application exists in applications table
                //
                $modquery = $this->rootda->execute('SELECT * FROM applications WHERE id='. (int) $this->serial);

                if ($modquery->getNumberRows() == 1) {
                    $appdata = $modquery->getFields();

                    if ($appdata['onlyextension'] != $this->rootda->fmttrue) {
                        // Checks if the structure file still exists
                        //
                        if (
                            file_exists(
                                $this->container->getHome().
                                'core/applications/'.$appdata['appid'].'/application.xml'
                            )
                        ) {
                            $this->appname = $appdata['appid'];

                            $domainquery = $this->rootda->execute(
                                'SELECT * FROM domains WHERE id='.$this->rootda->formatText((int) $domainid)
                            );
                            $domaindata = $domainquery->getFields();

                            // Connects to the tenant database if Innomatic has been installed in ASP edition.
                            if (
                                $this->container->getEdition()
                                == \Innomatic\Core\InnomaticContainer::EDITION_MULTITENANT
                            ) {
                                $args['dbtype'] = $domaindata['dataaccesstype'];
                                $args['dbname'] = $domaindata['domaindaname'];
                                $args['dbhost'] = $domaindata['dataaccesshost'];
                                $args['dbport'] = $domaindata['dataaccessport'];
                                $args['dbuser'] = $domaindata['dataaccessuser'];
                                $args['dbpass'] = $domaindata['dataaccesspassword'];
                                $args['dblog'] = $this->container->getHome().
                                'core/domains/'.$domaindata['domainid'].'/log/dataaccess.log';

                                $dasnString = $args['dbtype'].'://'.
                                $args['dbuser'].':'.
                                $args['dbpass'].'@'.
                                $args['dbhost'].':'.
                                $args['dbport'].'/'.
                                $args['dbname'].'?'.
                                'logfile='.$args['dblog'];

                                $this->domainda = \Innomatic\Dataaccess\DataAccessFactory::getDataAccess(
                                    new \Innomatic\Dataaccess\DataAccessSourceName($dasnString)
                                );
                                $this->domainda->connect();
                            } else {
                                $this->domainda = $this->rootda;
                            }

                            // Dependencies check
                            //
                            $this->unmetdeps = array();
                            $this->unmetsuggs = array();

                            $appdeps = new ApplicationDependencies();
                            $modenabled = $appdeps->IsEnabled($this->appname, $domaindata['domainid']);

                            $unmetdeps = $appdeps->checkDomainApplicationDependencies(
                                $this->appname,
                                $domaindata['domainid'],
                                ApplicationDependencies::TYPE_DEPENDENCY
                            );
                            
                            // Recursively enable application dependencies.
                            if (is_array($unmetdeps)) {
                                foreach ($unmetdeps as $depId => $depName) {
                                    $appQuery = $this->rootda->execute(
                                        'SELECT id FROM applications WHERE appid=' .
                                        $this->rootda->formatText($depName)
                                        );
                                    
                                    // Check if the application has been already enabled.
                                    // This happens when an application before in the dependencies list
                                    // as already enabled the current item.
                                    if ($appdeps->isEnabled($depName, $domaindata['domainid'])) {
                                        unset($unmetdeps[$depId]);
                                        continue;
                                    }

                                    // Enable the application.
                                    $app = new Application($this->rootda, $appQuery->getFields('id'));
                                    if ($app->enable($domainid)) {
                                        unset($unmetdeps[$depId]);
                                    } 
                                }
                            }

                            $unmetsuggs = $appdeps->checkDomainApplicationDependencies(
                                $this->appname,
                                $domaindata['domainid'],
                                ApplicationDependencies::TYPE_SUGGESTION
                            );

                            // Suggestions check
                            //
                            if (is_array($unmetsuggs))
                            $this->unmetsuggs = $unmetsuggs;

                            // If dependencies are ok, go on
                            //
                            if ($unmetdeps == false and !$modenabled) {
                                $result = $this->HandleStructure(
                                    $this->container->getHome().
                                    'core/applications/'.$appdata['appid'].'/application.xml',
                                    Application::INSTALL_MODE_ENABLE,
                                    $this->container->getHome().
                                    'core/applications/'.$appdata['appid'].'/',
                                    $domainid
                                );
                                $modquery = $this->rootda->execute(
                                    'SELECT id FROM applications WHERE appid='.
                                    $this->rootda->formatText($this->appname)
                                );
                                $this->rootda->execute(
                                    'INSERT INTO applications_enabled VALUES ('.
                                    $this->serial.','.$this->rootda->formatText($domainid).','.
                                    $this->rootda->formatDate(time()).','.$this->rootda->formatDate(time()).','.
                                    $this->rootda->formatText($this->rootda->fmttrue).')'
                                );

                                if (
                                    \Innomatic\Core\InnomaticContainer::instance(
                                        '\Innomatic\Core\InnomaticContainer'
                                    )->getConfig()->Value('SecurityAlertOnApplicationDomainOperation') == '1'
                                ) {
                                    $innomaticSecurity = new \Innomatic\Security\SecurityManager();
                                    $innomaticSecurity->SendAlert(
                                        'Application '.$appdata['appid'].' has been enabled to domain '.
                                        $domaindata['domainid']
                                    );
                                    unset($innomaticSecurity);
                                }

                                if (
                                    $hook->callHooks(
                                        'applicationenabled',
                                        $this,
                                        array('domainserial' => $domainid, 'modserial' => $this->serial)
                                    ) != \Innomatic\Process\Hook::RESULT_OK
                                )
                                $result = false;
                            } else {
                                $this->unmetdeps = $unmetdeps;
                            }
                            //if ( $result == true ) $this->mLog->logEvent(
                            //    'Innomatic',
                            //    'Uninstalled application '.$this->appname,
                            //    \Innomatic\Logging\Logger::NOTICE
                            //);

                            $domainquery->free();
                        } else {
                            $log = $this->container->getLogger();
                            $log->logEvent(
                                'innomatic.applications.applications.enable',
                                'Structure file '.$this->container->getHome().
                                'core/applications/'.$appdata['appid'].'/application.xml'.' for application '.
                                $appdata['appid'].' was not found',
                                \Innomatic\Logging\Logger::ERROR
                            );
                        }
                    } else {
                        $log = $this->container->getLogger();
                        $log->logEvent(
                            'innomatic.applications.applications.enable',
                            'Tried to enable application '.$appdata['appid'].
                            ', but it is an extension only application',
                            \Innomatic\Logging\Logger::ERROR
                        );
                    }
                } else {
                    $log = $this->container->getLogger();
                    $log->logEvent(
                        'innomatic.applications.applications.enable',
                        'A application with serial '.$this->serial.
                        ' was not found in applications table',
                        \Innomatic\Logging\Logger::ERROR
                    );
                }
                $modquery->free();
            } else {
                $log = $this->container->getLogger();
                $log->logEvent(
                    'innomatic.applications.applications.enable',
                    'Empty application serial',
                    \Innomatic\Logging\Logger::ERROR
                );
            }
        }
        return $result;
    }

    /*!
     @function EnableToAllDomains

     @abstract Enables a application for all domains.

     @result True if successfully enabled.
     */
    public function enableToAllDomains()
    {
        $result = false;

        $domainsquery = $this->rootda->execute('SELECT id FROM domains');

        if ($domainsquery->getNumberRows() > 0) {
            while (!$domainsquery->eof) {
                $this->Enable($domainsquery->getFields('id'));
                $domainsquery->moveNext();
            }
        }

        $domainsquery->free();

        return $result;
    }

    /*!
     @function Disable

     @abstract Disables a application for a domain.

     @param domainid string - id name of the domain.

     @result True if successfully disabled.
     */
    public function disable($domainid)
    {
        $result = false;

        $hook = new \Innomatic\Process\Hook($this->rootda, 'innomatic', 'application.disable');
        if (
            $hook->callHooks(
                'calltime',
                $this,
                array('domainserial' => $domainid, 'modserial' => $this->serial)
            ) == \Innomatic\Process\Hook::RESULT_OK
        ) {
            if ($this->serial) {
                // Checks if the application exists in applications table
                //
                $modquery = $this->rootda->execute('SELECT * FROM applications WHERE id='. (int) $this->serial);

                if ($modquery->getNumberRows() == 1) {
                    $appdata = $modquery->getFields();

                    if ($appdata['onlyextension'] != $this->rootda->fmttrue) {
                        // Checks if the structure file still exists
                        //
                        if (
                            file_exists(
                                $this->container->getHome().
                                'core/applications/'.$appdata['appid'].'/application.xml'
                            )
                        ) {
                            $this->appname = $appdata['appid'];

                            $domainquery = $this->rootda->execute(
                                'SELECT * FROM domains WHERE id='.$this->rootda->formatText((int) $domainid)
                            );
                            $domaindata = $domainquery->getFields();

                            if (
                                $this->container->getEdition()
                                == \Innomatic\Core\InnomaticContainer::EDITION_MULTITENANT
                            ) {
                                $args['dbtype'] = $domaindata['dataaccesstype'];
                                $args['dbname'] = $domaindata['domaindaname'];
                                $args['dbhost'] = $domaindata['dataaccesshost'];
                                $args['dbport'] = $domaindata['dataaccessport'];
                                $args['dbuser'] = $domaindata['dataaccessuser'];
                                $args['dbpass'] = $domaindata['dataaccesspassword'];
                                $args['dblog'] = $this->container->getHome().
                                'core/domains/'.$domaindata['domainid'].'/log/dataaccess.log';

                                $dasnString = $args['dbtype'].'://'.
                                $args['dbuser'].':'.
                                $args['dbpass'].'@'.
                                $args['dbhost'].':'.
                                $args['dbport'].'/'.
                                $args['dbname'].'?'.
                        'logfile='.$args['dblog'];

                                $this->domainda = \Innomatic\Dataaccess\DataAccessFactory::getDataAccess(
                                    new \Innomatic\Dataaccess\DataAccessSourceName($dasnString)
                                );
                                $this->domainda->Connect();
                            } else {
                                $this->domainda = $this->rootda;
                            }

                            // Dependencies check
                            //
                            $this->unmetdeps = array();
                            $this->unmetsuggs = array();

                            $appdeps = new ApplicationDependencies();
                            $pendingdeps = $appdeps->checkDomainDependingApplications(
                                $this->appname,
                                $domaindata['domainid'],
                                false
                            );
                            $modenabled = $appdeps->isEnabled($this->appname, $domaindata['domainid']);

                            // If dependencies are ok, go on
                            //
                            if (($pendingdeps == false) and ($modenabled == true)) {
                                $result = $this->HandleStructure(
                                    $this->container->getHome().
                                    'core/applications/'.$appdata['appid'].'/application.xml',
                                    Application::INSTALL_MODE_DISABLE,
                                    $this->container->getHome().
                                    'core/applications/'.$appdata['appid'].'/',
                                    $domainid
                                );

                                $modquery = $this->rootda->execute(
                                    'SELECT id FROM applications WHERE appid='.$this->rootda->formatText($this->appname)
                                );
                                $this->rootda->execute(
                                    'DELETE FROM applications_enabled WHERE applicationid='. (int) $this->serial.
                                    ' AND domainid='.$this->rootda->formatText($domainid)
                                );
                                $this->rootda->execute(
                                    'DELETE FROM applications_options_disabled WHERE applicationid='.
                                    (int) $this->serial.' AND domainid='. (int) $domainid
                                );

                                if (
                                    $this->container->getConfig()->Value(
                                        'SecurityAlertOnApplicationDomainOperation'
                                    ) == '1'
                                ) {
                                    $innomaticSecurity = new \Innomatic\Security\SecurityManager();
                                    $innomaticSecurity->sendAlert(
                                        'Application '.$appdata['appid'].' has been disabled from domain '.
                                        $domaindata['domainid']
                                    );
                                    unset($innomaticSecurity);
                                }

                                if (
                                    $hook->callHooks(
                                        'applicationdisabled',
                                        $this, array('domainserial' => $domainid, 'modserial' => $this->serial)
                                    ) != \Innomatic\Process\Hook::RESULT_OK
                                )
                                $result = false;
                            } elseif ($modenabled == false) {
                            } else {
                                $this->unmetdeps = $pendingdeps;
                            }
                            //if ( $result == true ) $this->mLog->logEvent(
                            //    'Innomatic',
                            //    'Uninstalled application '.$this->appname,
                            //    \Innomatic\Logging\Logger::NOTICE
                            //);

                            $domainquery->free();
                        } else {
                            $log = $this->container->getLogger();
                            $log->logEvent(
                                'innomatic.applications.applications.disable',
                                'Structure file '.$this->container->getHome().
                                'core/applications/'.$appdata['appid'].'/application.xml'.' for application '.
                                $appdata['appid'].' was not found',
                                \Innomatic\Logging\Logger::ERROR
                            );
                        }
                    } else {
                        $log = $this->container->getLogger();
                        $log->logEvent(
                            'innomatic.applications.applications.disable',
                            'Tried to disable application '.$appdata['appid'].
                            ', but it is an extension only application',
                            \Innomatic\Logging\Logger::ERROR
                        );
                    }
                } else {
                    $log = $this->container->getLogger();
                    $log->logEvent(
                        'innomatic.applications.applications.disable',
                        'A application with serial '.$this->serial.' was not found in applications table',
                        \Innomatic\Logging\Logger::ERROR
                    );
                }
                $modquery->free();
            } else {
                $log = $this->container->getLogger();
                $log->logEvent(
                    'innomatic.applications.applications.disable',
                    'Empty application serial',
                    \Innomatic\Logging\Logger::ERROR
                );
            }
        }

        return $result;
    }

    /*!
     @function disableFromAllDomains

     @abstract Disables a application for all domains.

     @result True if successfully disabled.
     */
    public function disableFromAllDomains()
    {
        $result = false;

        $domainsquery = $this->rootda->execute('SELECT id FROM domains');

        if ($domainsquery->getNumberRows() > 0) {
            while (!$domainsquery->eof) {
                $this->Disable($domainsquery->getFields('id'));
                $domainsquery->moveNext();
            }
        }

        $domainsquery->free();

        return $result;
    }

    public function setOptions($options)
    {
        $currentOptions = $this->getOptions();

        while (list (, $optionName) = each($options)) {
            $optionName = trim($optionName);

            if (strlen($optionName)) {
                $key = array_search($optionName, $currentOptions);

                if ($key != false)
                unset($currentOptions[$key]);
                else {
                    $this->rootda->execute(
                        'INSERT INTO applications_options VALUES ('
                        . $this->serial
                        . ',' . $this->rootda->formatText($optionName)
                        . ')'
                    );
                }
            }
        }

        while (list (, $oldOptionName) = each($currentOptions)) {
            $this->removeOption($oldOptionName);
        }

        return true;
    }

    public function getOptions()
    {
        $result = array();

        $subQuery = $this->rootda->execute(
            'SELECT name FROM applications_options WHERE applicationid='
            . (int) $this->serial . ' ORDER BY name'
        );

        $row = 1;
        while (!$subQuery->eof) {
            $result[$row ++] = $subQuery->getFields('name');
            $subQuery->moveNext();
        }

        $subQuery->free();

        return $result;
    }

    public function removeOption($option)
    {
        $this->rootda->execute(
            'DELETE FROM applications_options WHERE applicationid='. (int) $this->serial.
            ' AND name='.$this->rootda->formatText($option)
        );
        $this->rootda->execute(
            'DELETE FROM applications_options_disabled WHERE applicationid='. (int) $this->serial.
            ' AND optionname='.$this->rootda->formatText($option)
        );
        return true;
    }

    public function enableOption($option, $domainId)
    {
        $this->rootda->execute(
            'DELETE FROM applications_options_disabled WHERE applicationid='. (int) $this->serial.
            ' AND domainid='. (int) $domainId.' AND optionname='.$this->rootda->formatText($option)
        );
        return true;
    }

    public function disableOption($option, $domainId)
    {
        if ($this->checkIfOptionEnabled($option, $domainId)) {
            $this->rootda->execute(
                'INSERT INTO applications_options_disabled VALUES ('.
                $this->serial.','.$this->rootda->formatText($option).','.$domainId.')'
            );
        }
        return true;
    }

    public function checkIfOptionEnabled($option, $domainId)
    {
        $result = true;

        $subCheck = $this->rootda->execute(
            'SELECT optionname FROM applications_options_disabled '
            . 'WHERE applicationid='
            . (int) $this->serial
            . ' AND domainid=' . (int) $domainId
            . ' AND optionname=' . $this->rootda->formatText($option)
        );
        if ($subCheck->getNumberRows())
        $result = false;
        $subCheck->free();

        return $result;
    }

    /*!
     @function HandleStructure

     @abstract Handles a given structure.

     @param deffilepath string - file path.
     @param installmode int - install mode (defined).
     @param tmpdir string - temporary directory.
     @param domainid string - id name of the domain.
     @param setup boolean - setup flag.

     @result True.
     */
    public function handleStructure($deffilepath, $installmode, $tmpdir, $domainid = 0, $setup = false)
    {
        $result = false;
        $this->onlyextension = true;

        // When executing operations with applications, Innomatic must ignore
        // PHPExecutionTimeLimit parameter in innomatic.ini and set it to 0
        // in order to avoid interruptions during the operation
        set_time_limit(0);

        // Installation mode depending variables initializazion
        //
        switch ($installmode) {
            case Application::INSTALL_MODE_INSTALL :
                $sortmode = 'cmp';
                $scriptdir = $tmpdir.'/setup/';
                $prescript = 'generalpreinstall';
                $postscript = 'generalpostinstall';
                break;

            case Application::INSTALL_MODE_UNINSTALL :
                $sortmode = 'rcmp';
                $scriptdir = $tmpdir.'/setup/';
                $prescript = 'generalpreuninstall';
                $postscript = 'generalpostuninstall';
                break;

            case Application::INSTALL_MODE_UPDATE :
                $sortmode = 'cmp';
                $scriptdir = $tmpdir.'/setup/';
                $prescript = 'generalpreupdate';
                $postscript = 'generalpostupdate';
                $domainprescript = $domainpostscript = '';
                break;

            case Application::INSTALL_MODE_ENABLE :
                $sortmode = 'cmp';
                $scriptdir = $tmpdir.'/';
                $prescript = 'domainpreinstall';
                $postscript = 'domainpostinstall';
                break;

            case Application::INSTALL_MODE_DISABLE :
                $sortmode = 'rcmp';
                $scriptdir = $tmpdir.'/';
                $prescript = 'domainpreuninstall';
                $postscript = 'domainpostuninstall';
                break;

            default :
                break;
        }

        // Parse structure file
        //
        switch ($installmode) {
            case Application::INSTALL_MODE_UPDATE :
                $structure = $this->MergeStructureFiles(
                    $deffilepath,
                    $this->container->getHome().
                    'core/applications/'.$this->appname.'/application.xml',
                    $tmpdir
                );
                break;

            default:
                $deffile = new ApplicationStructureDefinition($tmpdir);
                $deffile->load_DefFile($deffilepath);
                $structure = $deffile->getStructure();
        }

        // Sort structure components by priority
        //
        uksort($structure, array($this, $sortmode));

        // Check for domain update scripts

        if (isset($structure['domainpreupdate'])) {
            $domainprescript = $scriptdir.$structure['domainpreupdate'];
        }
        if (isset($structure['domainpostupdate'])) {
            $domainpostscript = $scriptdir.$structure['domainpostupdate'];
        }

        // Check for preinstallation jobs
        //
        if (isset($structure[$prescript]) and sizeof($structure[$prescript]))
        include($scriptdir.$structure[$prescript]);

        // Install components
        //
        while (list ($eltype, $arraycontent) = each($structure)) {
            // Checks if it is a component and skips scripts
            //
            switch ($eltype) {
                case 'generalpreinstall' :
                case 'generalpreuninstall' :
                case 'generalpostinstall' :
                case 'generalpostuninstall' :
                case 'domainpreinstall' :
                case 'domainpreuninstall' :
                case 'domainpostinstall' :
                case 'domainpostuninstall' :
                case 'generalpreupdate' :
                case 'generalpostupdate' :
                case 'domainpreupdate' :
                case 'domainpostupdate' :
                    break;

                default :
                    // Checks if the component type file exists
                    //
                    if (
                        file_exists(
                            $this->container->getHome().
                            'core/classes/shared/components/'.ucfirst(strtolower($eltype)).'Component.php'
                        )
                    ) {
                        while (list (, $val) = each($arraycontent)) {
                            // If the component type file was not already included, include it
                            //
                            require_once(
                                $this->container->getHome().
                                'core/classes/shared/components/'.ucfirst(strtolower($eltype)).'Component.php'
                            );

                            // Creates a new instance of the component type class and installs the component
                            //
                            $tmpclassname = isset($this->eltypes->types[$eltype]) ? $this->eltypes->types[$eltype]['classname'] : '';
                            //if ( !$tmpclassname ) $tmpclassname = $eltype;
                            if ($tmpclassname) {
                            	$tmpclassname = $tmpclassname;
                                $tmpcomponent = new $tmpclassname(
                                    $this->rootda, $this->domainda, $this->appname, $val['name'], $tmpdir
                                );

                                /*
                                 {
                                 unset( $component );
                                 if (
                                 file_exists(
                                     $this->container->getHome().
                                     'core/classes/shared/components/'.$data['file']
                                  ) )
                                 {
                                 include_once( 'shared/components/'.$data['file'] );
                                 } else $this->mLog->logEvent(
                                    'innomatic.applications.applicationcomponentfactory.filltypes',
                                    'Component file '.$data['file'].' doesn't exists in handlers directory',
                                    \Innomatic\Logging\Logger::WARNING
                                );

                                 $this->types[$component['type']] = $component;
                                 }
                                 */

                                if ($setup) {
                                    $tmpcomponent->setup = true;
                                }

                                /*
                                 * Checks if this is a domain type component
                                 * of if a domain override has been set for this particular component.
                                 */
                                if (
                                    $tmpcomponent->getIsDomain() == true
                                    or (
                                        isset($val['override'])
                                        and $val['override'] == ApplicationComponent::OVERRIDE_DOMAIN
                                    )
                                ) {
                                    $this->onlyextension = false;
                                }

                                // Calls appropriate method
                                //
                                switch ($installmode) {
                                    case Application::INSTALL_MODE_INSTALL :
                                        $tmpcomponent->Install($val);
                                        break;
                                    case Application::INSTALL_MODE_UNINSTALL :
                                        $tmpcomponent->UnInstall($val);
                                        break;
                                    case Application::INSTALL_MODE_UPDATE :
                                        $tmpcomponent->Update(
                                            $val['updatemode'],
                                            $val,
                                            $domainprescript,
                                            $domainpostscript
                                        );
                                        break;
                                    case Application::INSTALL_MODE_ENABLE :
                                        $tmpcomponent->Enable($domainid, $val);
                                        break;
                                    case Application::INSTALL_MODE_DISABLE :
                                        $tmpcomponent->Disable($domainid, $val);
                                        break;
                                    default :
                                        $log = $this->container->getLogger();
                                        $log->logEvent(
                                            'innomatic.applications.applications.handlestructure',
                                            'Invalid installation method for component of '
                                            . $eltype . ' type in ' .$this->appname
                                            . ' application',
                                            \Innomatic\Logging\Logger::ERROR
                                        );
                                        break;
                                }

                                // There may be changes in component types, so we refill eltypes array
                                //
                                if ($eltype == 'component') {
                                    $this->eltypes->FillTypes();
                                }

                                unset($tmpcomponent);
                            } else {
                                $log = $this->container->getLogger();
                                $log->logEvent(
                                    'innomatic.applications.applications.handlestructure',
                                    'Component class (' . $tmpclassname
                                    . ') for component ' . $eltype
                                    . ' in ' . $this->appname
                                    . " application doesn't exists",
                                    \Innomatic\Logging\Logger::WARNING
                                );
                            }
                        }
                    } else {
                        $log = $this->container->getLogger();
                        $log->logEvent(
                            'innomatic.applications.applications.handlestructure',
                            'Component handler for component ' . $eltype
                            . ' in ' . $this->appname
                            . " application doesn't exists",
                            \Innomatic\Logging\Logger::WARNING
                        );
                    }
                    break;
            }
        }

        // Checks for postinstallation jobs
        //
        if (isset($structure[$postscript]) and sizeof($structure[$postscript])) {
            include($scriptdir.$structure[$postscript]);
        }

        $result = true;

        return $result;
    }

    /*!
     @function getLastActionUnmetDeps

     @abstract Gets last unmet dependencies.

     @result Array of unmet dependencies.
     */
    public function getLastActionUnmetDeps()
    {
        return (array) $this->unmetdeps;
    }

    /*!
     @function getLastActionUnmetSuggs

     @abstract Gets last unmet suggestions.

     @result Array of unmet suggestions.
     */
    public function getLastActionUnmetSuggs()
    {
        return (array) $this->unmetsuggs;
    }

    /*!
     @function MergeStructureFiles

     @abstract Merges two structure files into a new one, handling differences
    between them.

     @param filea string - New structure file.
     @param fileb string - Old structure file.
     @param tmpdir string - New application base dir.

     @result Merged array.
     */
    public function mergeStructureFiles($filea, $fileb, $tmpdir = '')
    {
        $result = array();

        if (file_exists($filea) and file_exists($fileb)) {
            // Load structure files
            //
            $deffilea = new ApplicationStructureDefinition(
                $tmpdir
            );
            $deffilea->load_DefFile($filea);
            $structurea = $deffilea->getStructure();

            $deffileb = new ApplicationStructureDefinition();
            $deffileb->load_DefFile($fileb);
            $structureb = $deffileb->getStructure();

            // Fill scripts array
            //
            $scripts = array();

            if (isset($structureb['generalpreinstall']))
            $scripts['generalpreinstall'] = $structureb['generalpreinstall'];
            if (isset($structureb['generalpreuninstall']))
            $scripts['generalpreuninstall'] = $structureb['generalpreuninstall'];
            if (isset($structureb['generalpostinstall']))
            $scripts['generalpostinstall'] = $structureb['generalpostinstall'];
            if (isset($structureb['generalpostuninstall']))
            $scripts['generalpostuninstall'] = $structureb['generalpostuninstall'];
            if (isset($structureb['domainpreinstall']))
            $scripts['domainpreinstall'] = $structureb['domainpreinstall'];
            if (isset($structureb['domainpreuninstall']))
            $scripts['domainpreuninstall'] = $structureb['domainpreuninstall'];
            if (isset($structureb['domainpostinstall']))
            $scripts['domainpostinstall'] = $structureb['domainpostinstall'];
            if (isset($structureb['domainpostuninstall']))
            $scripts['domainpostuninstall'] = $structureb['domainpostuninstall'];
            if (isset($structureb['generalpreupdate']))
            $scripts['generalpreupdate'] = $structureb['generalpreupdate'];
            if (isset($structureb['generalpostupdate']))
            $scripts['generalpostupdate'] = $structureb['generalpostupdate'];
            if (isset($structureb['domainpreupdate']))
            $scripts['domainpreupdate'] = $structureb['domainpreupdate'];
            if (isset($structureb['domainpostupdate']))
            $scripts['domainpostupdate'] = $structureb['domainpostupdate'];

            // Remove scripts and odd entries
            //
            while (list ($key, $val) = each($structurea)) {
                if (!is_array($val))
                unset($structurea[$key]);
            }
            reset($structurea);

            while (list ($key, $val) = each($structureb)) {
                if (!is_array($val))
                unset($structureb[$key]);
            }
            reset($structureb);

            $tmpstructure = array();

            // Scan structure a
            //
            while (list ($eltypea, $arraycontenta) = each($structurea)) {
                if (isset($structureb[$eltypea])) {
                    // This component type is in both structures
                    //
                    $arraycontentb = $structureb[$eltypea];

                    reset($arraycontenta);

                    // Checks every component in current structure a
                    // component type
                    //
                    while (list ($keya, $vala) = each($arraycontenta)) {
                        reset($arraycontentb);
                        $found = false;

                        while (list ($keyb, $valb) = each($arraycontentb)) {
                            if ($valb['name'] == $vala['name']) {
                                $found = true;
                                $tmpkey = $keyb;
                            }
                        }

                        if ($found) {
                            // This component must be updated
                            //
                            $tmparray = array();
                            $tmparray = $vala;
                            $tmparray['updatemode'] =
                                Application::UPDATE_MODE_CHANGE;

                            $tmpstructure[$eltypea][] = $tmparray;

                            unset($structurea[$eltypea][$keya]);
                            unset($structureb[$eltypea][$tmpkey]);
                        } else {
                            // This component must be added
                            //
                            $tmparray = array();
                            $tmparray = $vala;
                            $tmparray['updatemode'] =
                                Application::UPDATE_MODE_ADD;

                            $tmpstructure[$eltypea][] = $tmparray;
                        }
                    }
                } else {
                    // It is a completely new component type for structure
                    // file b, so add it
                    array_walk($arraycontenta, array($this, '_elem_add'));

                    $tmpstructure[$eltypea] = $arraycontenta;
                    unset($structurea[$eltypea]);
                }
            }

            reset($structureb);

            // Scan structure b
            //
            while (list ($eltypeb, $arraycontentb) = each($structureb)) {
                if (isset($structurea[$eltypeb])) {
                    // This component type is in both structures
                    //
                    $arraycontenta = $structurea[$eltypeb];

                    reset($arraycontentb);

                    // Check every remaining component in current structure b
                    // component type
                    while (list ($keyb, $valb) = each($arraycontentb)) {
                        reset($arraycontenta);
                        $found = false;

                        // This is just a check
                        //
                        while (list ($keya, $vala) = each($arraycontenta)) {
                            if ($vala['file'] == $valb['file']) {
                                $found = true;
                            }
                        }

                        if ($found) {
                            // Should never happen
                            //
                            $tmparray = array();
                            $tmparray = $valb;
                            $tmparray['updatemode'] =
                                Application::UPDATE_MODE_CHANGE;

                            $tmpstructure[$eltypeb][] = $tmparray;
                        } else {
                            // This component must be removed
                            //
                            $tmparray = array();
                            $tmparray = $valb;
                            $tmparray['updatemode'] =
                                Application::UPDATE_MODE_REMOVE;

                            $tmpstructure[$eltypeb][] = $tmparray;
                        }

                        if (isset($structurea[$eltypea][$keya]))
                        unset($structurea[$eltypea][$keya]);
                        if (isset($structureb[$eltypea][$keya]))
                        unset($structureb[$eltypea][$keya]);
                    }
                } else {
                    // It is a completely old component type for structure
                    // file b, so remove it
                    array_walk($arraycontentb, array($this, '_elem_remove'));

                    $tmpstructure[$eltypeb] = $arraycontentb;
                }
            }

            $result = array_merge($tmpstructure, $scripts);
        } else {
            $log = \Innomatic\Core\InnomaticContainer::instance(
                '\Innomatic\Core\InnomaticContainer'
            )->getLogger();

            if (!file_exists($filea))
            $log->logEvent(
                'innomatic.applications.application.mergestructurefiles',
                'Structure file ' . $filea . ' not found',
                \Innomatic\Logging\Logger::ERROR
            );

            if (!file_exists($fileb))
            $log->logEvent(
                'innomatic.applications.application.mergestructurefiles',
                'Structure file ' . $fileb . ' not found',
                \Innomatic\Logging\Logger::ERROR
            );
        }

        return $result;
    }

    /*!
     @function _elem_add

     @abstract Sets update mode to 'add'.

     @param item array - array containing component informations.
     @param key string - key name.
     */
    public function _elem_add(&$item, $key)
    {
        $item['updatemode'] = Application::UPDATE_MODE_ADD;
    }

    /*!
     @function _elem_remove

     @abstract Sets update mode to 'remove'.

     @param item array - array containing component informations.
     @param key string - key name.
     */
    public function _elem_remove(&$item, $key)
    {
        $item['updatemode'] = Application::UPDATE_MODE_REMOVE;
    }

    /*!
     @function cmp

     @abstract Compares priorities between two types.

     @param a mixed - type a.
     @param b mixed - type b.

     @result 0 if equal, -1 if typea > typeb, 1 if typea < typeb.
     */
    public function cmp($a, $b)
    {
        switch ($a) {
            case 'generalpreinstall' :
            case 'generalpreuninstall' :
            case 'generalpostinstall' :
            case 'generalpostuninstall' :
            case 'domainpreinstall' :
            case 'domainpreuninstall' :
            case 'domainpostinstall' :
            case 'domainpostuninstall' :
            case 'generalpreupdate' :
            case 'generalpostupdate' :
            case 'domainpreupdate' :
            case 'domainpostupdate' :
                return -1;
                break;
        }

        switch ($b) {
            case 'generalpreinstall' :
            case 'generalpreuninstall' :
            case 'generalpostinstall' :
            case 'generalpostuninstall' :
            case 'domainpreinstall' :
            case 'domainpreuninstall' :
            case 'domainpostinstall' :
            case 'domainpostuninstall' :
            case 'generalpreupdate' :
            case 'generalpostupdate' :
            case 'domainpreupdate' :
            case 'domainpostupdate' :
                return 1;
                break;
        }

        if ($this->eltypes->types[$a]['priority'] == $this->eltypes->types[$b]['priority'])
        return 0;
        return (($this->eltypes->types[$a]['priority'] > $this->eltypes->types[$b]['priority']) ? -1 : 1);
    }

    /*!
     @function rcmp

     @abstract Reverse compares priorities between two types.

     @param a mixed - type a.
     @param b mixed - type b.

     @result 0 if equal, -1 if typea < typeb, 1 if typea > typeb.
     */
    public function rcmp($a, $b)
    {
        switch ($a) {
            case 'generalpreinstall' :
            case 'generalpreuninstall' :
            case 'generalpostinstall' :
            case 'generalpostuninstall' :
            case 'domainpreinstall' :
            case 'domainpreuninstall' :
            case 'domainpostinstall' :
            case 'domainpostuninstall' :
            case 'generalpreupdate' :
            case 'generalpostupdate' :
            case 'domainpreupdate' :
            case 'domainpostupdate' :
                return -1;
                break;
        }

        switch ($b) {
            case 'generalpreinstall' :
            case 'generalpreuninstall' :
            case 'generalpostinstall' :
            case 'generalpostuninstall' :
            case 'domainpreinstall' :
            case 'domainpreuninstall' :
            case 'domainpostinstall' :
            case 'domainpostuninstall' :
            case 'generalpreupdate' :
            case 'generalpostupdate' :
            case 'domainpreupdate' :
            case 'domainpostupdate' :
                return 1;
                break;
        }

        if ($this->eltypes->types[$a]['priority'] == $this->eltypes->types[$b]['priority'])
        return 0;
        return (($this->eltypes->types[$a]['priority'] < $this->eltypes->types[$b]['priority']) ? -1 : 1);
    }

    public static function parseApplicationDefinition($file)
    {
        $xml = simplexml_load_file($file);
        $config['ApplicationIdName'] = sprintf('%s', $xml->definition[0]->idname);
        $config['ApplicationVersion'] = sprintf('%s', $xml->definition[0]->release[0]->version);
        $config['ApplicationDate'] = sprintf('%s', $xml->definition->release->date[0]);
        $config['ApplicationDescription'] = sprintf('%s', $xml->definition->description[0]);
        $config['ApplicationAuthor'] = sprintf('%s', $xml->definition->legal->author->name[0]);
        $config['ApplicationAuthorEmail'] = sprintf('%s', $xml->definition->legal->author->email[0]);
        $config['ApplicationAuthorWeb'] = sprintf('%s', $xml->definition->legal->author->web[0]);
        $config['ApplicationSupportEmail'] = sprintf('%s', $xml->definition->support->supportemail[0]);
        $config['ApplicationBugsEmail'] = sprintf('%s', $xml->definition->support->bugsemail[0]);
        $config['ApplicationCopyright'] = sprintf('%s', $xml->definition->legal->copyright[0]);
        $config['ApplicationLicense'] = sprintf('%s', $xml->definition->legal->license[0]);
        $config['ApplicationLicenseFile'] = sprintf('%s', $xml->definition->legal->licensefile[0]);
        $config['ApplicationChangesFile'] = sprintf('%s', $xml->definition->release->changesfile[0]);
        $config['ApplicationMaintainer'] = sprintf('%s', $xml->definition->support->maintainer->name[0]);
        $config['ApplicationMaintainerEmail'] = sprintf('%s', $xml->definition->support->maintainer->email[0]);
        $config['ApplicationCategory'] = sprintf('%s', $xml->definition->category[0]);
        $config['ApplicationIconFile'] = sprintf('%s', $xml->definition->iconfile[0]);
        $config['ApplicationIsExtension'] = sprintf('%s', $xml->definition->isextension[0]);

        $depsStart = true;
        $config['ApplicationDependencies'] = '';
        foreach ($xml->definition->dependencies->dependency as $dependency) {
            if (!$depsStart) {
                $config['ApplicationDependencies'] .= ',';
            } else {
                $depsStart = false;
            }

            $config['ApplicationDependencies'] .= $dependency;
        }

        $suggStart = true;
        $config['ApplicationSuggestions'] = '';
        foreach ($xml->definition->dependencies->suggestion as $suggestion) {
            if (!$suggStart) {
                $config['ApplicationSuggestions'] .= ',';
            } else {
                $suggStart = false;
            }

            $config['ApplicationSuggestions'] .= $suggestion;
        }

        $submodStart = true;
        $config['ApplicationOptions'] = '';
        if (isset($xml->definition->options)) {
            foreach ($xml->definition->options->option as $option) {
                if (!$submodStart) {
                    $config['ApplicationOptions'] .= ',';
                } else {
                    $submodStart = false;
                }

                $config['ApplicationOptions'] .= $option;
            }
        }

        return $config;
    }

    public static function getAppIdFromName($name)
    {
        if (!strlen($name)) return false;

        $da = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess();
        $query = $da->execute('SELECT id FROM applications WHERE appid='.$da->formatText($name));

        if ($query->getNumberRows() != 1) return false;

        return $query->getFields('id');
    }
}
