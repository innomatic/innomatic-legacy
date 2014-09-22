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
 */
namespace Innomatic\Application;

/**
 * @since 5.0.0 introduced
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 */
class ApplicationDependencies
{
    /*! @public mrRootDb DataAccess class - Innomatic database handler. */
    public $mrRootDb;
    const VERSIONCOMPARE_LESS = -1;
    const VERSIONCOMPARE_EQUAL = 0;
    const VERSIONCOMPARE_MORE = 1;
    const TYPE_ALL = 0; // Both dependency or suggestion
    const TYPE_DEPENDENCY = 1; // Dependency
    const TYPE_SUGGESTION = 2; // Suggestion

    /*!
     @param rrootDb DataAccess class - Innomatic database handler.
     */
    public function __construct()
    {
        $this->mrRootDb = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess();
    }

    public function explodeSingleDependency($appId)
    {
        $result = array();
        if (strstr($appId, '[') and strstr($appId, ']')) {
            $result['appid'] = substr($appId, 0, strpos($appId, '['));
            $result['appversion'] = substr($appId, strpos($appId, '[') + 1, -1);
        } else {
            $result['appid'] = $appId;
            $result['appversion'] = '';
        }
        return $result;
    }

    /*!
     @abstract Explodes a string containing application dependencies.
     @param depstring string - String containing the application dependencies
    in the application1,application2,... format.
     @result An array of the dependencies.
     */
    public function explodeDependencies($depstring)
    {
        if (!empty($depstring)) {
            $strings = explode(',', trim($depstring, ' ,'));
            $result = array();
            while (list (, $dep) = each($strings)) {
                $explodedApplicationString = $this->explodeSingleDependency(
                    trim($dep, ' ,')
                );
                $result[$explodedApplicationString['appid']] =
                    $explodedApplicationString['appversion'];
            }
            return $result;
        }
        return false;
    }

    /*!
     @abstract Adds dependencies for a application, using the array returned by $this->explodeDependencies().
     @param appid int - id name of the application.
     @param modsarray array - array of the applications to be added as dependencies.
     @param deptype int - type of dependency (defined).
     @result True if the dependencies have been added.
     */
    public function addDependenciesArray($appid, $modsarray, $deptype)
    {
        if (!empty($appid) and !empty($modsarray) and !empty($deptype)) {
            $modquery = $this->mrRootDb->execute(
                'SELECT id FROM applications WHERE appid='
                . $this->mrRootDb->formatText($appid)
            );
            if ($modquery->getNumberRows() != 0) {
                $appdata = $modquery->getFields();
                while (list ($key, $val) = each($modsarray)) {
                    $this->addDependency(
                        $appdata['id'],
                        $key . '[' . $val . ']',
                        $deptype
                    );
                }
                return true;
            }
        } else {
            if (empty($appid)) {

                $log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();
                $log->logEvent(
                    'innomatic.applications.appdeps.adddepsarray',
                    'Empty application id',
                    \Innomatic\Logging\Logger::ERROR
                );
            }
        }
        return false;
    }

    /*!
     @abstract Adds a dependency for a application.
     @param modSerial int - serial number of the application.
     @param appId string - id name of the application.
     @param depType int - type of dependency (defined).
     @result True if the dependency has been added.
     */

    // :KLUDGE: evil 20020507: strange appid type
    // It should be an int, but it's used as string

    public function addDependency($modSerial, $appId, $depType)
    {
        if (!empty($modSerial) and !empty($appId) and !empty($depType)) {
            $explodedApplicationString = $this->explodeSingleDependency($appId);
            $appID = $explodedApplicationString['appid'];
            $appVersion = $explodedApplicationString['appversion'];
            return $this->mrRootDb->execute(
                'INSERT INTO applications_dependencies VALUES ('.$this->mrRootDb->formatText($modSerial).','
                .$this->mrRootDb->formatText($appID).','.$this->mrRootDb->formatText($depType).','
                .$this->mrRootDb->formatText($appVersion).')'
            );
        } else {

            $log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();
            $log->logEvent(
                'innomatic.applications.appdeps.adddep',
                'Empty application serial ('.$modSerial.') or application id ('.$appId.') '
                .'or dependency type ('.$depType.')',
                \Innomatic\Logging\Logger::ERROR
            );
            return false;
        }
    }

    /*!
     @abstract Removes a certain dependency of the given application.
     @param modserial int - serial number of the application.
     @param appid string - id name of the application.
     @param deptype int - type of dependency (defined).
     @result True if the dependency has been removed.
     */

    // :KLUDGE: evil 20020507: strange appid type
    // It should be an int, but it's used as string

    public function removeDependency($modserial, $appid, $deptype)
    {
        if (!empty($modserial) and !empty($appid) and !empty($deptype)) {
            return $this->mrRootDb->execute(
                'DELETE FROM applications_dependencies WHERE appid='.$this->mrRootDb->formatText($modserial)
                .' AND moddep='.$this->mrRootDb->formatText($appid)
                .' AND deptype='.$this->mrRootDb->formatText($deptype)
            );
        } else {

            $log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();
            $log->logEvent(
                'innomatic.applications.appdeps.remdep',
                'Empty application serial ('.$modserial.') or application id ('.$appid.') or dependency type',
                \Innomatic\Logging\Logger::ERROR
            );
            return false;
        }
    }

    /*!
     @abstract Removes all dependencies of the given application.
     @param modserial int - serial number of the application.
     @result True if the dependencies have been removed.
     */
    public function removeAllDependencies($modserial)
    {
        if (!empty($modserial)) {
            return $this->mrRootDb->execute(
                'DELETE FROM applications_dependencies WHERE appid='.$this->mrRootDb->formatText($modserial)
            );
        } else {

            $log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();
            $log->logEvent('innomatic.applications.appdeps.remalldep', 'Empty application serial', \Innomatic\Logging\Logger::ERROR);
            return false;
        }
    }

    /*!
     @abstract Checks if a application has been installed.
     @param appId string - id of the application to be checked for existance.
     @result The query index if the application has been installed.
     */
    public function isInstalled($appId)
    {
        if (!empty($appId)) {
            $explodedApplicationString = $this->explodeSingleDependency($appId);
            $appID = $explodedApplicationString['appid'];
            $appVersion = $explodedApplicationString['appversion'];
            $applicationCheck = $this->mrRootDb->execute(
                'SELECT id,appversion,onlyextension FROM applications WHERE appid='
                .$this->mrRootDb->formatText($appID)
            );

            if ($appID == 'php') {
                $applicationCheck->resultrows = 1;
                $applicationCheck->currfields['id'] = '0';
                $applicationCheck->currfields['appversion'] = PHP_VERSION;
                $applicationCheck->currfields['onlyextension'] = $this->mrRootDb->fmtfalse;
            } elseif (
                strpos($appID, '.extension')
            ) {
                $appID = substr($appID, 0, strpos($appID, '.extension'));
                if (extension_loaded($appID)) {
                    $applicationCheck->resultrows = 1;
                    $applicationCheck->currfields['id'] = '0';
                    $applicationCheck->currfields['appversion'] = PHP_VERSION;
                    $applicationCheck->currfields['onlyextension'] = $this->mrRootDb->fmtfalse;
                }
            }

            if ($applicationCheck->getNumberRows()) {
                if (
                    $this->compareVersionNumbers(
                        $applicationCheck->getFields('appversion'),
                        $appVersion
                    ) != ApplicationDependencies::VERSIONCOMPARE_LESS
                ) {
                    return $applicationCheck;
                }
            }
        } else {

            $log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();
            $log->logEvent('innomatic.applications.appdeps.isinstalled', 'Empty application id', \Innomatic\Logging\Logger::ERROR);
        }
        return false;
    }

    /*!
     @abstract Lists the applications a certain application depends on.
     @param appid string - id name of the application.
     @result Array of the applications the application depends on or false if it does not have dependencies.
     */
    public function dependsOn($appid)
    {
        $result = false;

        if (!empty($appid)) {
            $mquery = $this->IsInstalled($appid);
            if ($mquery != false) {
                $mdata = $mquery->getFields();

                $mdquery = $this->mrRootDb->execute(
                    'SELECT * FROM applications_dependencies WHERE appid='.$this->mrRootDb->formatText($mdata['id'])
                );
                $nummd = $mdquery->getNumberRows();

                if ($nummd > 0) {
                    $depmods = array();
                    $m = 0;

                    while (!$mdquery->eof) {
                        $mddata = $mdquery->getFields();

                        $depmods[$m]['moddep'] = $mddata['moddep'];
                        $depmods[$m]['deptype'] = $mddata['deptype'];
                        $depmods[$m]['version'] = $mddata['version'];
                        $mdquery->moveNext();
                        $m ++;
                    }
                    $result = $depmods;
                }
            } else {

                $log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();
                $log->logEvent(
                    'innomatic.applications.appdeps.dependson',
                    'Application $appid is not installed',
                    \Innomatic\Logging\Logger::ERROR
                );
            }
        } else {

            $log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();
            $log->logEvent('innomatic.applications.appdeps.dependson', 'Empty application id', \Innomatic\Logging\Logger::ERROR);
        }
        return $result;
    }

    /*
     * Applications installation/disinstallation dependencies routines
     *
     */

    /*!
    @function CheckApplicationDeps

    @abstract Checks if all dependencies or suggestions are met.

    @param appid string - id name of the application to check.
    @param deptype int - type of dep: dependency, suggestion or both (defined).
                         Not meaningful when using $depsarray argument.
    @param depsarray string - array of the deps. Used when checking deps before installing. Defaults to nothing.
    If used, it takes precedence over $deptype. It doesn't understand difference between dep and suggestion,
    since it is passed an array of applications with no information about if they are suggestions or deps.

    @result False if the dependencies are met, an array of the unmet deps if them are not all met
                  or true if something went wrong.
    */
    public function checkApplicationDependencies($appid, $deptype = '', $depsarray = '')
    {
        $result = true;

        if (!empty($depsarray) or (!(empty($appid) and empty($deptype)))) {
            if (empty($depsarray)) {
                $appdeps = $this->DependsOn($appid);
                if ($appdeps == false) {
                    $result = false;
                }
            } else
                $appdeps = $depsarray;

            //else $appdeps = $this->dependson( $appid );

            // If there are no dependencies, automatically these are
            // assumed to be met
            //
            if ($result != false) {
                // We must set this to be true in case all deps are instead
                // only suggestions, or viceversa. useful when $deftype is not
                // DEFTYPE_ALL
                //
                $inst = true;
                $unmetdeps = array();

                foreach ($appdeps as $appID => $appVersion) {
                    if (
                        !empty($depsarray) or $deptype == ApplicationDependencies::TYPE_ALL
                        or (isset($appVersion['deptype']) and $appVersion['deptype'] == $deptype)
                    ) {
                        if (!empty($depsarray)) {
                            $inst = $this->IsInstalled($appID.'['.$appVersion.']');
                            if ($inst == false)
                                array_push($unmetdeps, $appID.'['.$appVersion.']');
                        } else {
                            $inst = $this->IsInstalled($appVersion['moddep']);
                            if ($inst == false)
                                array_push($unmetdeps, $appVersion['moddep'].'['.$appVersion['version'].']');
                        }
                    }
                }

                // All applications are installed
                if ($result) {
                    $result = $unmetdeps;
                }
            }
        } else {

            $log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();
            $log->logEvent(
                'innomatic.applications.appdeps.checkapplicationdeps',
                'Empty application id ('.$appid.') and dependency type or dependencies array',
                \Innomatic\Logging\Logger::ERROR
            );
        }
        return $result;
    }

    /*!
     @function CheckDependingApplications
     @abstract Checks if installed applications depends on the given application.
     @param appid string - id name of the application to check.
     @param deptype int - type of dependency (defined).
     @result False if no application depends on this one, the array of the applications which depends on
                   this one if some application depends on this one or true if something is not ok.
     */
    public function checkDependingApplications($appid, $deptype = ApplicationDependencies::TYPE_DEPENDENCY)
    {
        $result = true;

        if (!empty($appid)) {
            $modquery = $this->IsInstalled($appid);
            if ($modquery != false) {
                $dquery = $this->mrRootDb->execute(
                    'SELECT * FROM applications_dependencies WHERE moddep='.$this->mrRootDb->formatText($appid)
                    .' AND deptype='.$this->mrRootDb->formatText($deptype)
                );

                if ($dquery->getNumberRows() == 0) {
                    // No dependencies
                    //
                    $result = false;
                } else {
                    $pendingdeps = array();
                    $d = 0;

                    while (!$dquery->eof) {
                        $modquery = $this->mrRootDb->execute(
                            'SELECT appid FROM applications WHERE id='
                            .$this->mrRootDb->formatText($dquery->getFields('appid'))
                        );
                        $pendingdeps[$d ++] = $modquery->getFields('appid');
                        $dquery->moveNext();
                    }
                    $result = $pendingdeps;
                }
            }
        } else {

            $log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();
            $log->logEvent(
                'innomatic.applications.appdeps.checkdependingapplications',
                'Empty application id',
                \Innomatic\Logging\Logger::ERROR
            );
        }
        return $result;
    }

    /*
     * Applications abilitation/disabilitation dependencies routines
     *
     */

    /*!
     @abstract Checks if a application has been enabled to a certain domain.
     @param appid string - id name of the application to be checked.
     @param domainid string - id name of the domain to be checked.
     @result True if the application has been enabled to the given domain.
     */
    public function isEnabled($appid, $domainid, $considerExtensions = true)
    {
        $result = false;

        if (empty($appid) or empty($domainid)) {
            $log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();
            $log->logEvent(
                'innomatic.applications.appdeps.isenabled',
                'Empty application id ('.$appid.') or domain id ('.$domainid.')',
                \Innomatic\Logging\Logger::ERROR
            );
            return false;
        }

            // Check if the given dependency is a PHP extension
            if (strpos($appid, '.extension')) {
                $appid = substr($appid, 0, strpos($appid, '.extension'));
                return extension_loaded($appid);
            }

            // Looks if the given application has been installed
            //
            $modquery = $this->IsInstalled($appid);
            if ($modquery != false) {
                $appdata = $modquery->getFields();

                // If the application is a global extension, we can be sure
                // it is automatically enabled for all domains
                //
                if (strcmp($appdata['onlyextension'], $this->mrRootDb->fmttrue) == 0) {
                   return $considerExtensions;
                } else {
                    // Checks if the given domain id exists
                    //
                    $stquery = $this->mrRootDb->execute(
                        'SELECT id FROM domains WHERE domainid='.$this->mrRootDb->formatText($domainid)
                    );

                if ($stquery->getNumberRows() != 0) {
                    // Checks if the application has been enabled
                    //
                    $amquery = $this->mrRootDb->execute(
                        'SELECT applicationid FROM applications_enabled WHERE applicationid='
                        .$this->mrRootDb->formatText($appdata['id'])
                        .' AND domainid='.$this->mrRootDb->formatText($stquery->getFields('id'))
                    );

                    return $amquery->getNumberRows() != 0;
                }
            }
        }
        return $result;
    }

    public function isOptionEnabled($applicationId, $option, $domainId)
    {
        $subCheck = $this->mrRootDb->execute(
            'SELECT optionname FROM applications_options_disabled,applications WHERE applications.appid='
            .$this->mrRootDb->formatText($applicationId)
            .' AND applications.id=applications_options_disabled.applicationid AND domainid='
            .$domainId.' AND optionname='.$this->mrRootDb->formatText($option)
        );
        if ($subCheck->getNumberRows())
            return false;
        return true;
    }

    /*!
     @abstract Checks if all dependencies or suggestions for the domain are met.
     @param appid string - id name of the application to check.
     @param domainid string - id name of the domain to check.
     @param deptype int - type of dep: dependency, suggestion or both (defined).
     @result False if dependencies are met, an array of the unmet deps if them are not all met
             or true if something went wrong.
     */
    public function checkDomainApplicationDependencies($appid, $domainid, $deptype)
    {
        $result = true;

        if (!empty($appid) and !empty($domainid) and !empty($deptype)) {
            $appdeps = $this->DependsOn($appid);

            if ($appdeps == false)
                $result = false;

            if ($result != false) {
                $inst = true;
                $unmetdeps = array();

                while (list (, $deps) = each($appdeps)) {
                    if (($deps['deptype'] == $deptype) or ($deptype == ApplicationDependencies::TYPE_ALL)) {
                        $tmpInst = $this->IsEnabled($deps['moddep'], $domainid);
                        if ($tmpInst == false) {
                            $inst = false;
                            $unmetdeps[] = $deps['moddep'];
                        }
                    }
                }

                // All applications are installed
                //
                if ($inst != false)
                    $result = false;
                else
                    $result = $unmetdeps;
            }
        } else {

            $log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();
            $log->logEvent(
                'innomatic.applications.appdeps.checkdomainapplicationdeps',
                'Empty application id ('.$appid.') or domain id ('.$domainid.') or dependency type',
                \Innomatic\Logging\Logger::ERROR
            );
        }
        return $result;
    }

    /*!
     @abstract Checks which applications enabled on this domain depends on specified application.
     @param appid string - id name of the application to check.
     @param domainid string - id name of the domain to check.
     @result Array of depending applications, false if not enabled or no dependency found or true if wrong appid.
     */
    public function checkDomainDependingApplications($appid, $domainid, $considerExtensions = true)
    {
            // :KLUDGE: evil 20020507: strange appid type
        // It should be an int, but it's used as string
        $result = true;

        if (!empty($appid)) {
            $modquery = $this->IsEnabled($appid, $domainid);
            if ($modquery != false) {
                $dquery = $this->mrRootDb->execute(
                    'SELECT * FROM applications_dependencies WHERE moddep='.$this->mrRootDb->formatText($appid)
                    .' AND deptype='.$this->mrRootDb->formatText(ApplicationDependencies::TYPE_DEPENDENCY)
                );

                if ($dquery->getNumberRows() == 0) {
                    // No dependencies
                    //
                    $result = false;
                } else {
                    $pendingdeps = array();
                    $d = 0;

                    while (!$dquery->eof) {
                        $modquery = $this->mrRootDb->execute(
                            'SELECT appid FROM applications WHERE id='
                            .$this->mrRootDb->formatText($dquery->getFields('appid'))
                        );
                        $appdata = $modquery->getFields();

                        if ($this->IsEnabled($appdata['appid'], $domainid, $considerExtensions)) {
                            $pendingdeps[$d ++] = $appdata['appid'];
                        }

                        $dquery->moveNext();
                    }

                    if (count($pendingdeps) == 0)
                        $result = false;
                    else
                        $result = $pendingdeps;
                }
            } else
                $result = false;
        } else {

            $log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();
            $log->logEvent(
                'innomatic.applications.appdeps.checkdomaindependingapplications',
                'Empty application id ('.$appid.') or domain id ('.$domainid.')',
                \Innomatic\Logging\Logger::ERROR
            );
        }
        return $result;
    }

    /*!
     @abstract Checks the domains having a certain application enabled.
     @param modserial integer - serial id of the application to check.
     @result An array of the enabled domains if any, false if there aren't domains with that applications enabled.
     */
    public function checkEnabledDomains($modserial)
    {
        $result = false;

        if (!empty($modserial)) {
            $endomains = array();
            $query = $this->mrRootDb->execute(
                'SELECT domains.domainid FROM domains,applications_enabled '
                .'WHERE applications_enabled.domainid=domains.id AND applications_enabled.applicationid='.$modserial
            );

            if ($query->getNumberRows()) {
                while (!$query->eof) {
                    $endomains[] = $query->getFields('domainid');
                    $query->moveNext();
                }
                $result = $endomains;
            }
        } else {

            $log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();
            $log->logEvent(
                'innomatic.applications.appdeps.checkenableddomains',
                'Empty application serial',
                \Innomatic\Logging\Logger::ERROR
            );
        }
        return $result;
    }

    public static function compareVersionNumbers($a, $b)
    {
        $a = strtr($a, '-', '.');
        $b = strtr($b, '-', '.');

        $aDots = substr_count($a, '.');
        $bDots = substr_count($b, '.');

        $dots = max($aDots, $bDots);

        if ($aDots != $bDots) {
            if ($aDots == $dots) {
                for ($i = 0; $i < ($dots - $bDots); $i ++) {
                    $b.= '.0';
                }
            } else {
                for ($i = 0; $i < ($dots - $aDots); $i ++) {
                    $a.= '.0';
                }
            }
        }

        $aNumbers = explode('.', $a);
        $bNumbers = explode('.', $b);

        for ($i = 0; $i <= $dots; $i ++) {
            if ($aNumbers[$i] > $bNumbers[$i])
                return ApplicationDependencies::VERSIONCOMPARE_MORE;
            else
                if ($aNumbers[$i] < $bNumbers[$i])
                    return ApplicationDependencies::VERSIONCOMPARE_LESS;
        }

        return ApplicationDependencies::VERSIONCOMPARE_EQUAL;
    }
}
