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
namespace Innomatic\Dataaccess;

use \Innomatic\Core;

/** @deprecated */
global $dbtypes;
$dbtypes = array();

/**
 * @since 5.0.0 introduced
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 */
class DataAccessFactory
{
    public function __construct()
    {
        $this->retrieveAvailableDrivers();
    }

    private function retrieveAvailableDrivers()
    {
        global $dbtypes;
        $dbtypes = array();
        if (file_exists(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome().'core/conf/dataaccessdrivers.ini')) {
            $dbcfgfile = @parse_ini_file(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome().'core/conf/dataaccessdrivers.ini', false, INI_SCANNER_RAW);
            if ($dbcfgfile !== false) {
                $dbtypes = (array)$dbcfgfile;
            }
        }
    }

    public static function getDrivers()
    {
        return @parse_ini_file(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome().'core/conf/dataaccessdrivers.ini', false, INI_SCANNER_RAW);
    }

    public function addDriver($name, $desc)
    {
        global $dbtypes;
        if (!isset($dbtypes[$name])) {
            $cfg = new \Innomatic\Config\ConfigMan('innomatic', \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome().'core/conf/dataaccessdrivers.ini', \Innomatic\Config\ConfigBase::MODE_DIRECT);
            $cfg->addSegment($name, $name.' = '.$desc."\n");
            $this->retrieveAvailableDrivers();
        }
    }

    public function updateDriver($name, $desc)
    {
        global $dbtypes;
        if (isset($dbtypes[$name])) {
            $cfg = new \Innomatic\Config\ConfigMan('innomatic', \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome().'core/conf/dataaccessdrivers.ini', \Innomatic\Config\ConfigBase::MODE_DIRECT);
            $cfg->changeSegment($name, $name.' = '.$desc."\n");
            $this->retrieveAvailableDrivers();
        }
    }

    public function removeDriver($name)
    {
        global $dbtypes;
        if (isset($dbtypes[$name])) {
            $cfg = new \Innomatic\Config\ConfigMan('innomatic', \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome().'core/conf/dataaccessdrivers.ini', \Innomatic\Config\ConfigBase::MODE_DIRECT);
            $cfg->removeSegment($name);
            $this->retrieveAvailableDrivers();
        }
    }

    /*
     @function getDataAccess

     @abstract Creates a new instance of DataAccess class

     @param params array - Array of database parameters
     */
    public static function getDataAccess(DataAccessSourceName $dasn)
    {
        // Checks for database driver type
        //
        if (!strlen($dasn->getType())) {
            $innomatic = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');
            if ($innomatic->getState() != \Innomatic\Core\InnomaticContainer::STATE_SETUP) {
                $dasn->setType(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getConfig()->value('RootDatabaseType'));
            } else {
                throw new DataAccessException(DataAccessException::ERROR_UNSUPPORTED);
            }
        }

        // Creates a new instance of the specified database driver object
        //
        
        $dataaccess = '\\Innomatic\\Dataaccess\\Drivers\\'.ucfirst(strtolower($dasn->getType())).'\\'.ucfirst(strtolower($dasn->getType())).'DataAccess';
        $dataaccessresult = '\\Innomatic\\Dataaccess\\Drivers\\'.ucfirst(strtolower($dasn->getType())).'\\'.ucfirst(strtolower($dasn->getType())).'DataAccessResult';
        $objname = ucfirst(strtolower(strtolower($dasn->getType()))).'DataAccess';
        if (!class_exists($dataaccess, true)) {
            throw new DataAccessException(DataAccessException::ERROR_UNSUPPORTED);
        }
        return new $dataaccess($dasn);
    }
}
