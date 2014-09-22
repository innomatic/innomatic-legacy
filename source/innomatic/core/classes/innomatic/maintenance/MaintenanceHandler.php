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
 * @license    http://www.innomatic.io/license/ New BSD License
 * @link       http://www.innomatic.io
 * @since      Class available since Release 5.0
*/
namespace Innomatic\Maintenance;

class MaintenanceHandler
{
    public $mMaintenanceInterval;
    protected $configurationFile;

    public function __construct()
    {
        $container = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');

        $cfg = @parse_ini_file($container->getConfigurationFile(), false, INI_SCANNER_RAW);
        $result = isset($cfg['MaintenanceInterval']) ? $cfg['MaintenanceInterval'] : '';
        if (!strlen($result))
            $result = 0;
        $this->mMaintenanceInterval = $result;

        // Settings are stored in a dedicated file in order to avoid frequent
        // updates to the critical innomatic.ini file during maintenance execution.
        $this->configurationFile = $container->getHome().'core/conf/maintenance.ini';
    }

    // ----- Settings -----

    public function setMaintenanceInterval($interval)
    {
        $cfg = new \Innomatic\Config\ConfigFile($this->configurationFile);

        $result = $cfg->setValue('MaintenanceInterval', (int) $interval);

        $this->mMaintenanceInterval = (int) $interval;

        return $result;
    }

    public function getMaintenanceInterval()
    {
        return $this->mMaintenanceInterval;
    }

    public function getLastMaintenanceTime()
    {
        $cfg = @parse_ini_file($this->configurationFile, false, INI_SCANNER_RAW);

        return $cfg['MaintenanceLastExecutionTime'];
    }

    public function getTasksList()
    {
        $result = array();
        $tasks_query = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->execute('SELECT * FROM maintenance_tasks');

        while (!$tasks_query->eof) {
            if (strlen($tasks_query->getFields('catalog'))) {
                $locale = new \Innomatic\Locale\LocaleCatalog($tasks_query->getFields('catalog'), \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLanguage());

                $desc = $locale->getStr($tasks_query->getFields('name'));
                unset($locale);
            } else {
                $desc = $tasks_query->getFields('name');
            }

            $result[$tasks_query->getFields('name')] = array('name' => $tasks_query->getFields('name'), 'description' => $desc, 'enabled' => $tasks_query->getFields('enabled') == \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->fmttrue ? true : false);
            $tasks_query->moveNext();
        }

        $tasks_query->free();

        return $result;
    }

    public function EnableTask($taskName)
    {
        return \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->execute('UPDATE maintenance_tasks SET enabled='.\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->formatText(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->fmttrue).' WHERE name='.\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->formatText($taskName));
    }

    public function DisableTask($taskName)
    {
        return \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->execute('UPDATE maintenance_tasks SET enabled='.\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->formatText(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->fmtfalse).' WHERE name='.\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->formatText($taskName));
    }

    // ----- Facilities -----

    public function DoMaintenance()
    {
        $result = array();

        $tasks_query = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->execute('SELECT * FROM maintenance_tasks WHERE enabled='.\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->formatText(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->fmttrue));
        while (!$tasks_query->eof) {
            $class_name = '\\Shared\\Maintenance\\'.substr($tasks_query->getFields('file'), 0, -4);
            if (class_exists($class_name, true)) {
                $obj = new $class_name;
                $result[$tasks_query->getFields('name')] = $obj->execute();
            }
            $tasks_query->moveNext();
        }

        $tasks_query->free();
        $cfg = new \Innomatic\Config\ConfigFile($this->configurationFile);
        $cfg->setValue('MaintenanceLastExecutionTime', time());
        return $result;
    }

    public function EnableReports()
    {
        $cfg = new \Innomatic\Config\ConfigFile($this->configurationFile);
        return $cfg->setValue('MaintenanceReportsEnabled', '1');
    }

    public function DisableReports()
    {
        $cfg = new \Innomatic\Config\ConfigFile($this->configurationFile);
        return $cfg->setValue('MaintenanceReportsEnabled', '0');
    }

    public function getReportsEnableStatus()
    {
        $cfg = @parse_ini_file($this->configurationFile);
        if (isset($cfg['MaintenanceReportsEnabled']) and $cfg['MaintenanceReportsEnabled'] == '1') {
            return true;
        }
        return false;
    }

    public function setReportsEmail($email)
    {
        $cfg = new \Innomatic\Config\ConfigFile($this->configurationFile);
        return $cfg->setValue('MaintenanceReportsEmail', $email);
    }

    public function getReportsEmail()
    {
        $cfg = @parse_ini_file($this->configurationFile);

        return isset($cfg['MaintenanceReportsEmail']) ? $cfg['MaintenanceReportsEmail'] : '';
    }

    public function SendReport($maintenanceResult)
    {
        $result = false;

        $cfg = @parse_ini_file($this->configurationFile);
        $email = isset($cfg['MaintenanceReportsEmail']) ? $cfg['MaintenanceReportsEmail'] : '';

        if (isset($cfg['MaintenanceReportsEnabled']) and $cfg['MaintenanceReportsEnabled'] == '1' and strlen($email) and is_array($maintenanceResult)) {
            $result_text = '';
            $locale = new \Innomatic\Locale\LocaleCatalog('innomatic::maintenance', \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLanguage());
            $tasks_list = $this->getTasksList();

            foreach ($maintenanceResult as $task => $result) {
                $result_text.= "\n".'--> '.$tasks_list[$task]['description']."\n". ($result ? $locale->getStr('report_task_ok.label') : $locale->getStr('report_task_failed.label'))."\n";
            }

            $result = mail($email, '[INNOMATIC MAINTENANCE REPORT] - Scheduled maintenance report about '.\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getPlatformName().'.'.\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getPlatformGroup(), 'This is the scheduled maintenance report about '.\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getPlatformName().'.'.\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getPlatformGroup()."\n\n".'== MAINTENANCE RESULTS =='."\n".$result_text);
        }

        return $result;
    }
}
