<?php
/**
 * Innomatic
 *
 * LICENSE 
 * 
 * This source file is subject to the new BSD license that is bundled 
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2012 Innoteam S.r.l.
 * @license    http://www.innomatic.org/license/   BSD License
 * @link       http://www.innomatic.org
 * @since      Class available since Release 5.0
*/

require_once('innomatic/dataaccess/DataAccess.php');
require_once('innomatic/dataaccess/DataAccessException.php');
require_once('innomatic/dataaccess/DataAccessSourceName.php');

global $dbtypes;
$dbtypes = array();

class DataAccessFactory
{
    public function __construct()
    {
        require_once('innomatic/config/ConfigBase.php');
        require_once('innomatic/config/ConfigFile.php');
        require_once('innomatic/config/ConfigMan.php');
        $this->retrieveAvailableDrivers();
    }

    private function retrieveAvailableDrivers()
    {
        global $dbtypes;
        $dbtypes = array();
        if (file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'WEB-INF/conf/dataaccessdrivers.ini')) {
            $dbcfgfile = @parse_ini_file(InnomaticContainer::instance('innomaticcontainer')->getHome().'WEB-INF/conf/dataaccessdrivers.ini');
            if ($dbcfgfile !== FALSE) {
                $dbtypes = (array)$dbcfgfile;
            }
        }
    }

    public static function getDrivers()
    {
        return @parse_ini_file(InnomaticContainer::instance('innomaticcontainer')->getHome().'WEB-INF/conf/dataaccessdrivers.ini');
    }

    public function addDriver($name, $desc)
    {
        global $dbtypes;
        if (!isset($dbtypes[$name])) {
            $cfg = new ConfigMan('innomatic', InnomaticContainer::instance('innomaticcontainer')->getHome().'WEB-INF/conf/dataaccessdrivers.ini', ConfigBase::MODE_DIRECT);
            $cfg->AddSegment($name, $name.' = '.$desc."\n");
            $this->retrieveAvailableDrivers();
        }
    }

    public function updateDriver($name, $desc)
    {
        global $dbtypes;
        if (isset($dbtypes[$name])) {
            $cfg = new ConfigMan('innomatic', InnomaticContainer::instance('innomaticcontainer')->getHome().'WEB-INF/conf/dataaccessdrivers.ini', ConfigBase::MODE_DIRECT);
            $cfg->ChangeSegment($name, $name.' = '.$desc."\n");
            $this->retrieveAvailableDrivers();
        }
    }

    public function removeDriver($name)
    {
        global $dbtypes;
        if (isset($dbtypes[$name])) {
            $cfg = new ConfigMan('innomatic', InnomaticContainer::instance('innomaticcontainer')->getHome().'WEB-INF/conf/dataaccessdrivers.ini', ConfigBase::MODE_DIRECT);
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
            require_once('innomatic/core/InnomaticContainer.php');
            $innomatic = InnomaticContainer::instance('innomaticcontainer');
            if ($innomatic->getState() != InnomaticContainer::STATE_SETUP) {
                $dasn->setType(InnomaticContainer::instance('innomaticcontainer')->getConfig()->value('RootDatabaseType'));
            } else {
                throw new DataAccessException(DataAccessException::ERROR_UNSUPPORTED);
            }
        }

        // Creates a new instance of the specified database driver object
        //
        require_once('innomatic/dataaccess/drivers/'.strtolower($dasn->getType()).'/'.ucfirst(strtolower($dasn->getType())).'DataAccess.php');
        require_once('innomatic/dataaccess/drivers/'.strtolower($dasn->getType()).'/'.ucfirst(strtolower($dasn->getType())).'DataAccessResult.php');
        $objname = ucfirst(strtolower(strtolower($dasn->getType()))).'DataAccess';
        if (!class_exists($objname)) {
            throw new DataAccessException(DataAccessException::ERROR_UNSUPPORTED);
        }
        return new $objname($dasn);
    }
}
