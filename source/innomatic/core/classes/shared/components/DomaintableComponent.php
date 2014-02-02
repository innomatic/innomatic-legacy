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
 * @license    http://www.innomatic.org/license/   BSD License
 * @link       http://www.innomatic.org
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
            if (@copy($params['file'], \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/db/' . basename($params['file']))) {
                @chmod(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/db/' . basename($params['file']), 0644);
                $result = true;
            } else
                $this->mLog->logEvent('innomatic.domaintablecomponent.domaintablecomponent.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to copy ' . $params['file'] . ' to destination ' . \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/db/' . basename($params['file']), \Innomatic\Logging\Logger::ERROR);
        } else
            $this->mLog->logEvent('innomatic.domaintablecomponent.domaintablecomponent.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty table file name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
    public function doUninstallAction($params)
    {
        $result = false;
        if (strlen($params['file'])) {
            if (@unlink(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/db/' . basename($params['file']))) {
                $result = true;
            } else
                $this->mLog->logEvent('innomatic.domaintablecomponent.domaintablecomponent.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to remove ' . \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/db/' . basename($params['file']), \Innomatic\Logging\Logger::ERROR);
        } else
            $this->mLog->logEvent('innomatic.domaintablecomponent.domaintablecomponent.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty table file name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
    public function doUpdateAction($params)
    {
        $result = false;
        if (strlen($params['file'])) {
            $params['file'] = $this->basedir . '/core/db/' . $params['file'];
            if (file_exists(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/db/' . basename($params['file']) . '.old'))
                @copy(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/db/' . basename($params['file']) . '.old', \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/db/' . basename($params['file']) . '.old2');
            @copy(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/db/' . basename($params['file']), \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/db/' . basename($params['file']) . '.old');
            if (@copy($params['file'], \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/db/' . basename($params['file']))) {
                @chmod(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/db/' . basename($params['file']), 0644);
                $result = true;
            } else
                $this->mLog->logEvent('innomatic.domaintablecomponent.domaintablecomponent.doupdateaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to copy ' . $params['file'] . ' to destination ' . \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/db/' . basename($params['file']), \Innomatic\Logging\Logger::ERROR);
        } else
            $this->mLog->logEvent('innomatic.domaintablecomponent.domaintablecomponent.doupdateaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty table file name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
    public function doEnableDomainAction($domainid, $params)
    {
        $result = false;
        if (strlen($params['file'])) {
            $xmldb = new \Innomatic\Dataaccess\DataAccessXmlTable($this->domainda, \Innomatic\Dataaccess\DataAccessXmlTable::SQL_CREATE);
            $xmldb->load_deffile(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/db/' . $params['file']);
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
            $xmldb->load_deffile(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/db/' . $params['file']);
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
            $xml_upd = new \Innomatic\Dataaccess\DataAccessXmlTableUpdater($this->domainda, \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/db/' . basename($params['file']) . '.old', $params['file']);
            $xml_upd->CheckDiffs();
            $old_columns = $xml_upd->getOldColumns();
            if (is_array($old_columns)) {
                while (list (, $column) = each($old_columns)) {
                    $upd_data['tablename'] = $params['name'];
                    $upd_data['column'] = $column;
                    $this->domainda->RemoveColumn($upd_data);
                }
            }
            $new_columns = $xml_upd->getNewColumns();
            if (is_array($new_columns)) {
                while (list (, $column) = each($new_columns)) {
                    $upd_data['tablename'] = $params['name'];
                    $upd_data['columnformat'] = $column;
                    $this->domainda->AddColumn($upd_data);
                }
            }
        } else
            $this->mLog->logEvent('innomatic.domaintablecomponent.domaintablecomponent.doupdatedomainaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty table file name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
}
