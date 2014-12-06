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

use \Innomatic\Dataaccess;

/**
 * Domaintable component handler.
 */
class DomaintableComponent extends \Innomatic\Application\ApplicationComponent
{
    public function __construct($rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
    }
    public static function getType()
    {
        return 'domaintable';
    }
    public static function getPriority()
    {
        return 130;
    }
    public static function getIsDomain()
    {
        return true;
    }
    public static function getIsOverridable()
    {
        return false;
    }
    public function doInstallAction($params)
    {
        $result = false;
        if (strlen($params['file'])) {
            $params['file'] = $this->basedir . '/core/db/' . $params['file'];
            if (@copy($params['file'], $this->container->getHome() . 'core/db/' . basename($params['file']))) {
                @chmod($this->container->getHome() . 'core/db/' . basename($params['file']), 0644);
                $result = true;
            } else
                $this->mLog->logEvent('innomatic.domaintablecomponent.domaintablecomponent.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to copy ' . $params['file'] . ' to destination ' . $this->container->getHome() . 'core/db/' . basename($params['file']), \Innomatic\Logging\Logger::ERROR);
        } else
            $this->mLog->logEvent('innomatic.domaintablecomponent.domaintablecomponent.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty table file name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
    public function doUninstallAction($params)
    {
        $result = false;
        if (strlen($params['file'])) {
            if (@unlink($this->container->getHome() . 'core/db/' . basename($params['file']))) {
                $result = true;
            } else
                $this->mLog->logEvent('innomatic.domaintablecomponent.domaintablecomponent.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to remove ' . $this->container->getHome() . 'core/db/' . basename($params['file']), \Innomatic\Logging\Logger::ERROR);
        } else
            $this->mLog->logEvent('innomatic.domaintablecomponent.domaintablecomponent.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty table file name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
    public function doUpdateAction($params)
    {
        $result = false;
        if (strlen($params['file'])) {
            $params['file'] = $this->basedir . '/core/db/' . $params['file'];
            if (file_exists($this->container->getHome() . 'core/db/' . basename($params['file']) . '.old'))
                @copy($this->container->getHome() . 'core/db/' . basename($params['file']) . '.old', $this->container->getHome() . 'core/db/' . basename($params['file']) . '.old2');
            @copy($this->container->getHome() . 'core/db/' . basename($params['file']), $this->container->getHome() . 'core/db/' . basename($params['file']) . '.old');
            if (@copy($params['file'], $this->container->getHome() . 'core/db/' . basename($params['file']))) {
                @chmod($this->container->getHome() . 'core/db/' . basename($params['file']), 0644);
                $result = true;
            } else
                $this->mLog->logEvent('innomatic.domaintablecomponent.domaintablecomponent.doupdateaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to copy ' . $params['file'] . ' to destination ' . $this->container->getHome() . 'core/db/' . basename($params['file']), \Innomatic\Logging\Logger::ERROR);
        } else
            $this->mLog->logEvent('innomatic.domaintablecomponent.domaintablecomponent.doupdateaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty table file name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
    public function doEnableDomainAction($domainid, $params)
    {
        $result = false;
        if (strlen($params['file'])) {
            $xmldb = new \Innomatic\Dataaccess\DataAccessXmlTable($this->domainda, \Innomatic\Dataaccess\DataAccessXmlTable::SQL_CREATE);
            $xmldb->load_deffile($this->container->getHome() . 'core/db/' . $params['file']);
            if ($this->domainda->execute($xmldb->getSQL())) {
                $result = true;
            } else
                $this->mLog->logEvent('innomatic.domaintablecomponent.domaintablecomponent.doenabledomainaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to create table from ' . $params['file'] . ' table file', \Innomatic\Logging\Logger::ERROR);
            $xmldb->free();
        } else
            $this->mLog->logEvent('innomatic.domaintablecomponent.domaintablecomponent.doenabledomainaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty table file name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
    public function doDisableDomainAction($domainid, $params)
    {
        $result = false;
        if (strlen($params['file'])) {
            $xmldb = new \Innomatic\Dataaccess\DataAccessXmlTable($this->domainda, \Innomatic\Dataaccess\DataAccessXmlTable::SQL_DROP);
            $xmldb->load_deffile($this->container->getHome() . 'core/db/' . $params['file']);
            if ($this->domainda->execute($xmldb->getSQL())) {
                $result = true;
            } else
                $this->mLog->logEvent('innomatic.domaintablecomponent.domaintablecomponent.dodisabledomainaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to remove table from ' . $params['file'] . ' table file', \Innomatic\Logging\Logger::ERROR);
        } else
            $this->mLog->logEvent('innomatic.domaintablecomponent.domaintablecomponent.dodisabledomainaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty table file name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
    public function doUpdateDomainAction($domainid, $params)
    {
        $result = true;
        if (strlen($params['file'])) {
            $params['file'] = $this->basedir . '/core/db/' . $params['file'];
            $xml_upd = new \Innomatic\Dataaccess\DataAccessXmlTableUpdater($this->domainda, $this->container->getHome() . 'core/db/' . basename($params['file']) . '.old', $params['file']);
            $xml_upd->applyDiffs($params); 
        } else
            $this->mLog->logEvent('innomatic.domaintablecomponent.domaintablecomponent.doupdatedomainaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty table file name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
}
