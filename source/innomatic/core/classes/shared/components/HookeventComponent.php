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
 * @since      Class available since Release 5.0
 */
namespace Shared\Components;

/**
 * Hookevent component handler.
 */
class HookeventComponent extends \Innomatic\Application\ApplicationComponent
{
    public function __construct($rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
    }
    public static function getType()
    {
        return 'hookevent';
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
    public function doInstallAction($params)
    {
        $result = false;
        if (strlen($params['name'])) {
            if (!isset($params['function'])) {
                $params['function'] = '';
            }
            $hook = new \Innomatic\Process\Hook($this->rootda, $this->appname, $params['function']);
            if ($hook->addEvent($params['event']))
                $result = true;
            else
                $this->mLog->logEvent('innomatic.hookeventcomponent.hookeventcomponent.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to add hookevent', \Innomatic\Logging\Logger::ERROR);
        } else
            $this->mLog->logEvent('innomatic.hookeventcomponent.hookeventcomponent.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty hookevent name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
    public function doUninstallAction($params)
    {
        $result = false;
        if (strlen($params['name'])) {
            if (!isset($params['function'])) {
                $params['function'] = '';
            }
            $xm = new \Innomatic\Process\Hook($this->rootda, $this->appname, $params['function']);
            if ($xm->RemoveEvent($params['event']))
                $result = true;
            else
                $this->mLog->logEvent('innomatic.hookcomponent.hookcomponent.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to remove hookevent', \Innomatic\Logging\Logger::ERROR);
        } else
            $this->mLog->logEvent('innomatic.hookcomponent.hookcomponent.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty hook nameevent', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
    public function doUpdateAction($params)
    {
        $result = false;
        $result = true;
        /*
        if ( strlen( $params['name'] ) ) {
            $xm = new WebServicesMethod( $this->rootda, $params['name'] );
            if ( $xm->Update( $params['function'], $params['handler'], $params['signature'], $params['docstring'] ) ) $result = true;
            else $this->mLog->logEvent( 'innomatic.xmlrpccomponent.xmlrpccomponent.doupdateaction', 'In application '.$this->appname.', component '.$params['name'].': Unable to update xmlrpc method', \Innomatic\Logging\Logger::ERROR );
        } else $this->mLog->logEvent( 'innomatic.xmlrpccomponent.xmlrpccomponent.doupdateaction', 'In application '.$this->appname.', component '.$params['name'].': Empty xmlrpc handler file name', \Innomatic\Logging\Logger::ERROR );
        */
        return $result;
    }
}
