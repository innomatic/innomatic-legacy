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

/**
 * Webservicesmethod component handler.
 */
class WebservicesmethodComponent extends \Innomatic\Application\ApplicationComponent
{
    public function __construct($rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
    }
    public static function getType()
    {
        return 'webservicesmethod';
    }
    public static function getPriority()
    {
        return 0;
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
        if (! strlen($params['name']) or ! strlen($params['function']) or ! strlen($params['handler']) or ! strlen($this->appname)) {
            $this->mLog->logEvent('shared.components.webservicesmethod.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Wrong parameters', \Innomatic\Logging\Logger::ERROR);
            return false;
        }
        if (! isset($params['signature']))
            $params['signature'] = '';
        if (! isset($params['unsecure']))
            $params['unsecure'] = 0;
        if (! isset($params['catalog']))
            $params['catalog'] = '';
        if (! isset($params['docstring']))
            $params['docstring'] = '';
            // :TODO: Alex Pagnoni 010712: add check
        // The function should check if the method already exists.
        $result = $this->rootda->execute('INSERT INTO webservices_methods ' . 'VALUES (' . $this->rootda->getNextSequenceValue('webservices_methods_id_seq') . ',' . $this->rootda->formatText($params['name']) . ',' . $this->rootda->formatText($params['function']) . ',' . $this->rootda->formatText($params['signature']) . ',' . $this->rootda->formatText($params['docstring']) . ',' . $this->rootda->formatText($params['handler']) . ',' . $this->rootda->formatText($this->appname) . ',' . $this->rootda->formatText($params['unsecure'] ? $this->rootda->fmttrue : $this->rootda->fmtfalse) . ',' . $this->rootda->formatText($params['catalog']) . ')');
        if (! $result) {
            $this->mLog->logEvent('shared.components.xmlrpccomponent.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to install xmlrpc method', \Innomatic\Logging\Logger::ERROR);
            return false;
        }
        return true;
    }
    public function doUninstallAction($params)
    {
        if (! strlen($params['name'])) {
            $this->mLog->logEvent('shared.components.xmlrpccomponent.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty xmlrpc handler file name', \Innomatic\Logging\Logger::ERROR);
            return false;
        }
        // Removes permissions
        //
        $this->rootda->execute('DELETE FROM webservices_permissions ' . 'WHERE method=' . $this->rootda->formatText($params['name']));
        $result = $this->rootda->execute('DELETE FROM webservices_methods ' . 'WHERE name=' . $this->rootda->formatText($params['name']));
        if (! $result) {
            $this->mLog->logEvent('innomatic.webservicesmethod.uninstall', 'Unable to remove method row from webservices_methods table', \Innomatic\Logging\Logger::ERROR);
            return false;
        }
        return true;
    }
    public function doUpdateAction($params)
    {
        if (! strlen($params['name']) or ! strlen($params['function']) or ! strlen($params['handler'])) {
            $this->mLog->logEvent('shared.components.webservicesmethod.doupdateaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Wrong parameters', \Innomatic\Logging\Logger::ERROR);
            return false;
        }
        if (! isset($params['signature']))
            $params['signature'] = '';
        if (! isset($params['unsecure']))
            $params['unsecure'] = '';
        if (! isset($params['catalog']))
            $params['catalog'] = '';
        if (! isset($params['docstring']))
            $params['docstring'] = '';
        $result = $this->rootda->execute('UPDATE webservices_methods ' . 'SET function=' . $this->rootda->formatText($params['function']) . ',' . 'signature=' . $this->rootda->formatText($params['signature']) . ',' . 'docstring=' . $this->rootda->formatText($params['docstring']) . ',' . 'handler=' . $this->rootda->formatText($params['handler']) . ',' . 'catalog=' . $this->rootda->formatText($params['catalog']) . ',' . 'unsecure=' . $this->rootda->formatText($params['unsecure'] ? $this->rootda->fmttrue : $this->rootda->fmtfalse) . ' WHERE name=' . $this->rootda->formatText($params['name']));
        if (! $result) {
            $this->mLog->logEvent('shared.components.xmlrpccomponent.doupdateaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to update web services method', \Innomatic\Logging\Logger::ERROR);
            return false;
        }
        return true;
    }
}
