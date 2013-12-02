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

class MaintenanceHandler
{
    public $mApplicationSettings;
    public $mMaintenanceInterval;

    public function MaintenanceHandler()
    {
        require_once('innomatic/application/ApplicationSettings.php');
        $this->mApplicationSettings = new ApplicationSettings(InnomaticContainer::instance('innomaticcontainer')->getDataAccess(), 'innomatic');

        $cfg = @parse_ini_file(InnomaticContainer::instance('innomaticcontainer')->getConfigurationFile(), false, INI_SCANNER_RAW);
        $result = $cfg['MaintenanceInterval'];
        if (!strlen($result))
            $result = 0;
        $this->mMaintenanceInterval = $result;
    }

    // ----- Settings -----

    public function setMaintenanceInterval($interval)
    {
        require_once('innomatic/config/ConfigFile.php');
        $cfg = new ConfigFile(InnomaticContainer::instance('innomaticcontainer')->getConfigurationFile());

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
        $cfg = @parse_ini_file(InnomaticContainer::instance('innomaticcontainer')->getConfigurationFile(), false, INI_SCANNER_RAW);

        return $cfg['MaintenanceLastExecutionTime'];
    }

    public function getTasksList()
    {
        $result = array();

        require_once('innomatic/locale/LocaleCatalog.php');

        $tasks_query = InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->execute('SELECT * FROM maintenance_tasks');

        while (!$tasks_query->eof) {
            if (strlen($tasks_query->getFields('catalog'))) {
                $locale = new LocaleCatalog($tasks_query->getFields('catalog'), InnomaticContainer::instance('innomaticcontainer')->getLanguage());

                $desc = $locale->getStr($tasks_query->getFields('name'));
                unset($locale);
            } else
                $desc = $tasks_query->getFields('name');

            $result[$tasks_query->getFields('name')] = array('name' => $tasks_query->getFields('name'), 'description' => $desc, 'enabled' => $tasks_query->getFields('enabled') == InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->fmttrue ? true : false);

            $tasks_query->moveNext();
        }

        $tasks_query->free();

        return $result;
    }

    public function EnableTask($taskName)
    {
        return InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->execute('UPDATE maintenance_tasks SET enabled='.InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->formatText(InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->fmttrue).' WHERE name='.InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->formatText($taskName));
    }

    public function DisableTask($taskName)
    {
        return InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->execute('UPDATE maintenance_tasks SET enabled='.InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->formatText(InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->fmtfalse).' WHERE name='.InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->formatText($taskName));
    }

    // ----- Facilities -----

    public function DoMaintenance()
    {
        $result = array();

        $tasks_query = InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->execute('SELECT * FROM maintenance_tasks WHERE enabled='.InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->formatText(InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->fmttrue));
        while (!$tasks_query->eof) {
            if (include_once('shared/maintenance/'.$tasks_query->getFields('file'))) {
                $class_name = substr($tasks_query->getFields('file'), 0, -4);
                if (class_exists($class_name, false)) {
                    $obj = new $class_name;
                    $result[$tasks_query->getFields('name')] = $obj->execute();
                }
            }
            $tasks_query->moveNext();
        }

        $tasks_query->free();
        require_once('innomatic/config/ConfigFile.php');
        $cfg = new ConfigFile(InnomaticContainer::instance('innomaticcontainer')->getConfigurationFile());
        $cfg->setValue('MaintenanceLastExecutionTime', time());
        return $result;
    }

    public function EnableReports()
    {
        require_once('innomatic/config/ConfigFile.php');
        $cfg = new ConfigFile(InnomaticContainer::instance('innomaticcontainer')->getConfigurationFile());
        return $cfg->setValue('MaintenanceReportsEnabled', '1');
    }

    public function DisableReports()
    {
        require_once('innomatic/config/ConfigFile.php');
        $cfg = new ConfigFile(InnomaticContainer::instance('innomaticcontainer')->getConfigurationFile());
        return $cfg->setValue('MaintenanceReportsEnabled', '0');
    }

    public function getReportsEnableStatus()
    {
        $cfg = @parse_ini_file(InnomaticContainer::instance('innomaticcontainer')->getConfigurationFile());
        if ($cfg['MaintenanceReportsEnabled'] == '1') {
            return true;
        }
        return false;
    }

    public function setReportsEmail($email)
    {
        require_once('innomatic/config/ConfigFile.php');
        $cfg = new ConfigFile(InnomaticContainer::instance('innomaticcontainer')->getConfigurationFile());
        return $cfg->setValue('MaintenanceReportsEmail', $email);
    }

    public function getReportsEmail()
    {
        $cfg = @parse_ini_file(InnomaticContainer::instance('innomaticcontainer')->getConfigurationFile());

        return $cfg['MaintenanceReportsEmail'];
    }

    public function SendReport($maintenanceResult)
    {
        $result = false;

        $cfg = @parse_ini_file(InnomaticContainer::instance('innomaticcontainer')->getConfigurationFile());
        $email = $cfg['MaintenanceReportsEmail'];

        if ($cfg['MaintenanceReportsEnabled'] == '1' and strlen($email) and is_array($maintenanceResult)) {
            $result_text = '';

            require_once('innomatic/locale/LocaleCatalog.php');
            $locale = new LocaleCatalog('innomatic::maintenance', InnomaticContainer::instance('innomaticcontainer')->getLanguage());

            $tasks_list = $this->getTasksList();

            foreach ($maintenanceResult as $task => $result) {
                $result_text.= "\n".'--> '.$tasks_list[$task]['description']."\n". ($result ? $locale->getStr('report_task_ok.label') : $locale->getStr('report_task_failed.label'))."\n";
            }

            $result = mail($email, '[INNOMATIC MAINTENANCE REPORT] - Scheduled maintenance report about '.InnomaticContainer::instance('innomaticcontainer')->getPlatformName().'.'.InnomaticContainer::instance('innomaticcontainer')->getPlatformGroup(), 'This is the scheduled maintenance report about '.InnomaticContainer::instance('innomaticcontainer')->getPlatformName().'.'.InnomaticContainer::instance('innomaticcontainer')->getPlatformGroup()."\n\n".'== MAINTENANCE RESULTS =='."\n".$result_text);
        }

        return $result;
    }
}
