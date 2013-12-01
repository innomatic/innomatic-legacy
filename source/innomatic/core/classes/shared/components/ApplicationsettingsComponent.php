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
namespace Shared\Components;

/**
 * Applicationsettings component handler.
 */
class ApplicationsettingsComponent extends \Innomatic\Application\ApplicationComponent
{
    public function __construct($rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
    }
    public static function getType()
    {
        return 'applicationsettings';
    }
    public static function getPriority()
    {
        return 10;
    }
    public static function getIsDomain()
    {
        return false;
    }
    public static function getIsOverridable()
    {
        return false;
    }
    public function DoInstallAction($params)
    {
        $result = false;
        if (strlen($params['file']) and isset($params['application']) and isset($params['key'])) {
            $app_cfg = new ApplicationSettings($this->rootda, $params['application']);
            $app_cfg->setKey($params['key'], isset($params['value']) ? $params['value'] : '');
        } else
            $this->mLog->logEvent('innomatic.applicationsettingscomponent.applicationsettingscomponent.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty file argument', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
    public function DoUninstallAction($params)
    {
        $result = false;
        if (strlen($params['file']) and isset($params['application']) and isset($params['key'])) {
            if (! (isset($params['keep']) and $params['keep'] = 'true')) {
                $app_cfg = new ApplicationSettings($this->rootda, $params['application']);
                $app_cfg->DelKey($params['key']);
            }
        } else
            $this->mLog->logEvent('innomatic.applicationsettingscomponent.applicationsettingscomponent.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty file argument', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
    public function DoUpdateAction($params)
    {
        $result = false;
        if (strlen($params['file']) and isset($params['application']) and isset($params['key'])) {
            $app_cfg = new ApplicationSettings($this->rootda, $params['application']);
            if (! (isset($params['keep']) and $params['keep'] = 'true' and $app_cfg->CheckKey($params['key']))) {
                $app_cfg->setKey($params['key'], isset($params['value']) ? $params['value'] : '');
            }
        } else
            $this->mLog->logEvent('innomatic.applicationsettingscomponent.applicationsettingscomponent.doupdateaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty file argument', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
}
