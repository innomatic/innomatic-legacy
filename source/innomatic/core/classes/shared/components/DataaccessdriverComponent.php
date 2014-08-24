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
 * @license    http://www.innomaticplatform.com/license/   BSD License
 * @link       http://www.innomaticplatform.com
 * @since      Class available since Release 5.0
 */
namespace Shared\Components;

use \Innomatic\Dataaccess\DataAccessFactory;

/**
 * DataAccess driver component handler.
 */
class DataaccessdriverComponent extends \Innomatic\Application\ApplicationComponent
{
    public function __construct(&$rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
    }
    public static function getType()
    {
        return 'dataaccessdriver';
    }
    public static function getPriority()
    {
        return 110;
    }
    public static function getIsDomain()
    {
        return false;
    }
    public static function getIsOverridable()
    {
        return false;
    }
    public function doInstallAction($params)
    {
        $result = false;
        if (strlen($params['file'])) {
            $db_fact = new DataAccessFactory();
            $db_fact->addDriver($params['name'], $params['desc']);
            $result = true;
        } else
            $this->mLog->logEvent('innomatic.dataaccessdrivercomponent.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty DataAccess driver file name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
    public function doUninstallAction($params)
    {
        $result = false;
        if (strlen($params['file'])) {
            $db_fact = new DataAccessFactory();
            $db_fact->removeDriver($params['name']);
            $result = true;
        } else
            $this->mLog->logEvent('innomatic.dataaccessdrivercomponent.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty DataAccess driver file name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
    public function doUpdateAction($params)
    {
        $result = false;
        if (strlen($params['file'])) {
            $db_fact = new DataAccessFactory();
            $db_fact->updateDriver($params['name'], $params['desc']);
            $result = true;
        } else
            $this->mLog->logEvent('innomatic.dataaccessdrivercomponent.doupdateaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty DataAccess driver file name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
}
