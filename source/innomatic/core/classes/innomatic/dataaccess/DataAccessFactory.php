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
namespace Innomatic\Dataaccess;

use \Innomatic\Core;

global $dbtypes;
$dbtypes = array();

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
        if (file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/conf/dataaccessdrivers.ini')) {
            $dbcfgfile = @parse_ini_file(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/conf/dataaccessdrivers.ini', false, INI_SCANNER_RAW);
            if ($dbcfgfile !== false) {
                $dbtypes = (array)$dbcfgfile;
            }
        }
    }

    public static function getDrivers()
    {
        return @parse_ini_file(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/conf/dataaccessdrivers.ini', false, INI_SCANNER_RAW);
    }

    public function addDriver($name, $desc)
    {
        global $dbtypes;
        if (!isset($dbtypes[$name])) {
            $cfg = new ConfigMan('innomatic', InnomaticContainer::instance('innomaticcontainer')->getHome().'core/conf/dataaccessdrivers.ini', ConfigBase::MODE_DIRECT);
            $cfg->AddSegment($name, $name.' = '.$desc."\n");
            $this->retrieveAvailableDrivers();
        }
    }

    public function updateDriver($name, $desc)
    {
        global $dbtypes;
        if (isset($dbtypes[$name])) {
            $cfg = new ConfigMan('innomatic', InnomaticContainer::instance('innomaticcontainer')->getHome().'core/conf/dataaccessdrivers.ini', ConfigBase::MODE_DIRECT);
            $cfg->ChangeSegment($name, $name.' = '.$desc."\n");
            $this->retrieveAvailableDrivers();
        }
    }

    public function removeDriver($name)
    {
        global $dbtypes;
        if (isset($dbtypes[$name])) {
            $cfg = new ConfigMan('innomatic', InnomaticContainer::instance('innomaticcontainer')->getHome().'core/conf/dataaccessdrivers.ini', ConfigBase::MODE_DIRECT);
            $cfg->RemoveSegment($name);
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
            $innomatic = InnomaticContainer::instance('innomaticcontainer');
            if ($innomatic->getState() != InnomaticContainer::STATE_SETUP) {
                $dasn->setType(InnomaticContainer::instance('innomaticcontainer')->getConfig()->value('RootDatabaseType'));
            } else {
                throw new DataAccessException(DataAccessException::ERROR_UNSUPPORTED);
            }
        }

        // Creates a new instance of the specified database driver object
        //
        
        $dataaccess = '\\Innomatic\\Dataaccess\\Drivers\\'.ucfirst(strtolower($dasn->getType())).'\\'.ucfirst(strtolower($dasn->getType())).'DataAccess';
        $dataaccessresult = '\\Innomatic\\Dataaccess\\Drivers\\'.ucfirst(strtolower($dasn->getType())).'\\'.ucfirst(strtolower($dasn->getType())).'DataAccessResult';
        $objname = ucfirst(strtolower(strtolower($dasn->getType()))).'DataAccess';
        if (!class_exists($objname, true)) {
            throw new DataAccessException(DataAccessException::ERROR_UNSUPPORTED);
        }
        return new $objname($dasn);
    }
}
