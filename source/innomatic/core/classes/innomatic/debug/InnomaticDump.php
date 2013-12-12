<?php
/**
 * Innomatic
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2012 Innoteam Srl
 * @license    http://www.innomatic.org/license/   BSD License
 * @link       http://www.innomatic.org
 * @since      Class available since Release 5.0
*/
namespace Innomatic\Debug;

/**
 * Stores a dump of the main Innomatic instance information.
 *
 * @since 5.0
 */
class InnomaticDump extends \Innomatic\Util\Singleton
{
    // Snapshot time filled information

    /**
     * This array holds information about the environment (operating system,
     * PHP, etc.) and it is filled during a call to snapshot() method.
     *
     * @var array
     */
    public $environment = array();
    /**
     * Information about Innomatic platform and current instance.
     *
     * @var array
     */
    public $innomatic = array();
    /**
     * Main load timer.
     *
     * @var LoadTime
     */
    public $loadTimer;
    /**
     * Data access load timer.
     *
     * @var LoadTime
     */
    public $dbLoadTimer;

    // Run time filled information

    /**
     * DataAccess debug information.
     *
     * @var array
     */
    public $dataAccess = array();
    /**
     * Logging debug information. A key for each logfile.
     *
     * @var array
     */
    public $logs = array();
    /**
     * Hooks debug information.
     *
     * @var array
     */
    public $hooks  = array();
    /**
     * Miscellaneous debug information.
     *
     * @var array
     */
    public $misc = array();
    /**
     * Domains debug information. A key for each domain.
     *
     * @var unknown_type
     */
    public $domains = array();
    /**
     * Applications debug information. A key for each application.
     *
     * @var array
     */
    public $applications = array();
    /**
     * Desktop application, if any.
     *
     * @var string
     */
    public $desktopApplication = array();

    /**
     * Esecutes a snapshot of the system information.
     *
     */
    public function snapshot()
    {
        // (PHP 5 >= 5.2.0)
        $this->environment['memory_peak_usage'] = function_exists(
            'memory_get_peak_usage'
        ) ? memory_get_peak_usage() : '';

        $this->environment['memory_usage'] = memory_get_usage();
        $this->environment['defined_constants'] = get_defined_constants();
        $definedFunctions = get_defined_functions();
        $this->environment['defined_functions'] = $definedFunctions['user'];
        $this->environment['include_path'] = get_include_path();
        $this->environment['included_files'] = get_included_files();
        $this->environment['loaded_extensions'] = get_loaded_extensions();
        $this->environment['ini_all'] = ini_get_all();
        $this->environment['phpversion'] = phpversion();
        $this->environment['declared_classes'] = get_declared_classes();

        $innomaticContainer = \Innomatic\Core\InnomaticContainer::instance(
            '\Innomatic\Core\InnomaticContainer'
        );
        $this->innomatic['state'] = $innomaticContainer->getState();
        $this->innomatic['interface'] = $innomaticContainer->getInterface();
        $this->innomatic['mode'] = $innomaticContainer->getMode();
        $this->innomatic['edition'] = $innomaticContainer->getEdition();

        $this->loadTimer = $innomaticContainer->getLoadTimer();
        $this->dbLoadTimer = $innomaticContainer->getDbLoadTimer();
    }
}
