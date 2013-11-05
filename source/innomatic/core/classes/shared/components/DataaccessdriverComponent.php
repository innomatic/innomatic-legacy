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
require_once ('innomatic/application/ApplicationComponent.php');
require_once ('innomatic/dataaccess/DataAccessFactory.php');
/**
 * DataAccess driver component handler.
 */
class DataaccessdriverComponent extends ApplicationComponent
{
    function DataaccessdriverComponent (&$rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
    }
    public static function getType ()
    {
        return 'dataaccessdriver';
    }
    public static function getPriority ()
    {
        return 110;
    }
    public static function getIsDomain ()
    {
        return false;
    }
    public static function getIsOverridable ()
    {
        return false;
    }
    function DoInstallAction ($params)
    {
        $result = false;
        if (strlen($params['file'])) {
            $db_fact = new DataAccessFactory();
            $db_fact->addDriver($params['name'], $params['desc']);
            $result = true;
        } else
            $this->mLog->logEvent('innomatic.dataaccessdrivercomponent.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty DataAccess driver file name', Logger::ERROR);
        return $result;
    }
    function DoUninstallAction ($params)
    {
        $result = false;
        if (strlen($params['file'])) {
            $db_fact = new DataAccessFactory();
            $db_fact->removeDriver($params['name']);
            $result = true;
        } else
            $this->mLog->logEvent('innomatic.dataaccessdrivercomponent.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty DataAccess driver file name', Logger::ERROR);
        return $result;
    }
    function DoUpdateAction ($params)
    {
        $result = false;
        if (strlen($params['file'])) {
            $db_fact = new DataAccessFactory();
            $db_fact->updateDriver($params['name'], $params['desc']);
            $result = true;
        } else
            $this->mLog->logEvent('innomatic.dataaccessdrivercomponent.doupdateaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty DataAccess driver file name', Logger::ERROR);
        return $result;
    }
}
