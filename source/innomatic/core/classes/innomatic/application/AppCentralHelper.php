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

/**
 * This class provides some helper methods for handling AppCentral operations
 * like retrieving list of all the available applications, updating
 * applications and so on.
 *
 * @since 6.5.0 introduced
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 */
class AppCentralHelper
{
    /* public updateApplications() {{{ */
    /**
     * Updates all the installed applications fetching new application versions
     * found in AppCentral repositories.
     *
     * @access public
     * @return array List of updated applications with their versions.
     */
    public function updateApplications()
    {
    }
    /* }}} */

    /* public getUpdatedApplications() {{{ */
    /**
     * Gets a list of the installed applications for which a new version is
     * available in AppCentral repositories.
     *
     * This method compares the installed applications with the ones found in
     * AppCentral repositories.
     *
     * @access public
     * @return array List of the available updated applications with their versions.
     */
    public function getUpdatedApplications()
    {
    }
    /* }}} */

    /* public getAvailableApplications() {{{ */
    /**
     * Gets a list of all the available applications in the registered
     * AppCentral repositories.
     *
     * @access public
     * @return array
     */
    public function getAvailableApplications($refresh = false)
    {
        $apps = array();

        // Fetch the list of the registered AppCentral servers.
        $dataAccess = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')
            ->getDataAccess();

        $serverList = $dataAccess->execute(
            "SELECT id FROM applications_repositories"
        );

        while (!$serverList->eof) {
            $serverId = $serverList->getFields('id');
            $server = new AppCentralRemoteServer($serverId);

            // Fetch the list of the available repositories, refreshing the cache.
            $repositories = $server->listAvailableRepositories($refresh);

            foreach ($repositories as $repoId => $repoData) {
                // Fetch the list of the available repository applications.
                $repoApplications = $server->listAvailableApplications($repoId, $refresh);

                foreach ($repoApplications as $appId => $appData) {
                    // Fetch the list of the available application versions.
                    $versions = $server->listAvailableApplicationVersions(
                        $repoId,
                        $appId,
                        $refresh
                    );

                    // Add the application version to the applications list.
                    foreach ($versions as $version => $versionData) {
                        $apps[$appData['appid']][$version][] = [
                            'server' => $serverId,
                            'repository' => $repoId
                        ];
                    }
                }
            }
            $serverList->moveNext();
        }

        return $apps;
    }
    /* }}} */

    /* public findApplication($application) {{{ */
    /**
     * Checks if the given application is available in the registered
     * AppCentral servers.
     *
     * @param string $application Application name.
     * @param string $version     Optional minimun version number.
     * @param bool   $refresh     True if the cache must be refreshed.
     * @access public
     * @return mixed false if the applications has not been found or an array of the servers
     * containing the application.
     */
    public function findApplication($application, $version = null, $refresh = false)
    {
        // Get the list of the available applications.
        $apps = $this->getAvailableApplications($refresh);

        // Check if the application has been found.
        if (!isset($apps[$application])) {
            return false;
        }

        $found = $apps[$application];

        // If a minimum version number has been given, remove the application
        // versions under the latter.
        if (!is_null($version)) {
            foreach ($found as $appVersion => $appData) {
                $compare = ApplicationDependencies::compareVersionNumbers($appVersion, $version);
                if ($compare == ApplicationDependencies::VERSIONCOMPARE_LESS) {
                    unset($found[$appVersion]);
                }
            }
        }
        
        if (!count($found)) {
            return false;
        }

        return $found;
    }
    /* }}} */

    public function resolveDependencies($dependencies)
    {
    }

    /* public updateApplicationsList(\Closure $item = null, \Closure $result = null) {{{ */
    /**
     * Refreshes the list of the available repositories and applications from
     * the registered AppCentral servers.
     *
     * @param \Closure $item Optional callback that is called before refreshing the applications list of a repository.
     * @param \Closure $result Optional callback that is called after refreshing each repository.
     * @access public
     * @return void
     */
    public function updateApplicationsList(\Closure $item = null, \Closure $result = null)
    {
        // Fetch the list of the registered AppCentral servers.
        $dataAccess = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')
            ->getDataAccess();

        $serverList = $dataAccess->execute(
            "SELECT id FROM applications_repositories"
        );

        // Refresh each AppCentral server.
        while (!$serverList->eof) {
            $serverId = $serverList->getFields('id');
            $server = new AppCentralRemoteServer($serverId);

            // Fetch the list of the available repositories, refreshing the cache.
            $repositories = $server->listAvailableRepositories(true);

            foreach ($repositories as $repoId => $repoData) {
                // Call the repository refresh data callback.
                if (is_callable($item)) {
                    $item($serverId, $server->getAccount()->getName(), $repoId, $repoData);
                }

                // Fetch the list of the available repository applications.
                $repoApplications = $server->listAvailableApplications($repoId, true);

                foreach ($repoApplications as $appId => $appData) {
                    // Fetch the list of the available application versions.
                    $versions = $server->listAvailableApplicationVersions(
                        $repoId,
                        $appId,
                        true
                    );
                }

                // Call the repository refresh result callback.
                if (is_callable($result)) {
                    $result(true);
                }
            }
            $serverList->moveNext();
        }
    }
    /* }}} */
}
