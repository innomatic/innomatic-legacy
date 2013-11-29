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

require_once('innomatic/maintenance/MaintenanceTask.php');

class InnomaticLogsMaintenance extends MaintenanceTask
{
    public $mApplicationSettings;

    public $mCleanRootLog;
    public $mCleanRootDbLog;
    public $mCleanAccessLog;
    public $mCleanWebServicesLog;
    public $mCleanPhpLog;
    public $mCleanDomainsLogs;
    public $mRotateRootLog;
    public $mRotateRootDbLog;
    public $mRotateAccessLog;
    public $mRotateWebServicesLog;
    public $mRotatePhpLog;
    public $mRotateDomainsLogs;

    public function __construct()
    {
        require_once('innomatic/application/ApplicationSettings.php');

        $this->mApplicationSettings = new ApplicationSettings(
            InnomaticContainer::instance('innomaticcontainer')->getDataAccess(),
            'innomatic'
           );

        $this->mCleanRootLog = $this->mApplicationSettings->getKey('maintenance_cleanrootlog') == '1' ? true : false;
        $this->mCleanRootDbLog = $this->mApplicationSettings->getKey('maintenance_cleanrootdalog') == '1' ? true : false;
        $this->mCleanAccessLog = $this->mApplicationSettings->getKey('maintenance_cleanaccesslog') == '1' ? true : false;
        $this->mCleanWebServicesLog = $this->mApplicationSettings->getKey('maintenance_cleanwebserviceslog') == '1' ? true : false;
        $this->mCleanPhpLog = $this->mApplicationSettings->getKey('maintenance_cleanphplog') == '1' ? true : false;
        $this->mCleanDomainsLogs = $this->mApplicationSettings->getKey('maintenance_cleandomainslogs') == '1' ? true : false;

        $this->mRotateRootLog = $this->mApplicationSettings->getKey('maintenance_rotaterootlog') == '1' ? true : false;
        $this->mRotateRootDbLog = $this->mApplicationSettings->getKey('maintenance_rotaterootdalog') == '1' ? true : false;
        $this->mRotateAccessLog = $this->mApplicationSettings->getKey('maintenance_rotateaccesslog') == '1' ? true : false;
        $this->mRotateWebServicesLog = $this->mApplicationSettings->getKey('maintenance_rotatewebserviceslog') == '1' ? true : false;
        $this->mRotatePhpLog = $this->mApplicationSettings->getKey('maintenance_rotatephplog') == '1' ? true : false;
        $this->mRotateDomainsLogs = $this->mApplicationSettings->getKey('maintenance_rotatedomainslogs') == '1' ? true : false;
    }



    public function setCleanRootLog(
        $clean
    )
    {
        $this->mApplicationSettings->setKey(
            'maintenance_cleanrootlog',
            $clean ? '1' : '0'
           );
        $this->mCleanRootLog = $clean ? true : false;
    }

    public function getCleanRootLog()
    {
        return $this->mCleanRootLog;
    }

    public function setRotateRootLog(
        $clean
    )
    {
        $this->mApplicationSettings->setKey(
            'maintenance_rotaterootlog',
            $clean ? '1' : '0'
           );
        $this->mRotateRootLog = $clean ? true : false;
    }

    public function getRotateRootLog()
    {
        return $this->mRotateRootLog;
    }

    public function setCleanRootDbLog(
        $clean
    )
    {
        $this->mApplicationSettings->setKey(
            'maintenance_cleanrootdalog',
            $clean ? '1' : '0'
           );
        $this->mCleanRootDbLog = $clean ? true : false;
    }

    public function getCleanRootDbLog()
    {
        return $this->mCleanRootDbLog;
    }

    public function setRotateRootDbLog(
        $clean
    )
    {
        $this->mApplicationSettings->setKey(
            'maintenance_rotaterootdalog',
            $clean ? '1' : '0'
           );
        $this->mRotateRootDbLog = $clean ? true : false;
    }

    public function getRotateRootDbLog()
    {
        return $this->mRotateRootDbLog;
    }



    public function setCleanAccessLog(
        $clean
    )
    {
        $this->mApplicationSettings->setKey(
            'maintenance_cleanaccesslog',
            $clean ? '1' : '0'
           );
        $this->mCleanAccessLog = $clean ? true : false;
    }

    public function getCleanAccessLog()
    {
        return $this->mCleanAccessLog;
    }

    public function setRotateAccessLog(
        $clean
    )
    {
        $this->mApplicationSettings->setKey(
            'maintenance_rotateaccesslog',
            $clean ? '1' : '0'
           );
        $this->mRotateAccessLog = $clean ? true : false;
    }

    public function getRotateAccessLog()
    {
        return $this->mRotateAccessLog;
    }

    public function setCleanWebServicesLog(
        $clean
    )
    {
        $this->mApplicationSettings->setKey(
            'maintenance_cleanwebserviceslog',
            $clean ? '1' : '0'
           );
        $this->mCleanWebServicesLog = $clean ? true : false;
    }

    public function getCleanWebServicesLog()
    {
        return $this->mCleanWebServicesLog;
    }

    public function setRotateWebServicesLog(
        $clean
    )
    {
        $this->mApplicationSettings->setKey(
            'maintenance_rotatewebserviceslog',
            $clean ? '1' : '0'
           );
        $this->mRotateWebServicesLog = $clean ? true : false;
    }

    public function getRotateWebServicesLog()
    {
        return $this->mRotateWebServicesLog;
    }

    public function setCleanPhpLog(
        $clean
    )
    {
        $this->mApplicationSettings->setKey(
            'maintenance_cleanphplog',
            $clean ? '1' : '0'
        );
        $this->mCleanPhpLog = $clean ? true : false;
    }

    public function getCleanPhpLog()
    {
        return $this->mCleanPhpLog;
    }

    public function setRotatePhpLog(
        $clean
    )
    {
        $this->mApplicationSettings->setKey(
            'maintenance_rotatephplog',
            $clean ? '1' : '0'
           );
        $this->mRotatePhpLog = $clean ? true : false;
    }

    public function getRotatePhpLog()
    {
        return $this->mRotatePhpLog;
    }

    public function setCleanDomainsLogs(
        $clean
    )
    {
        $this->mApplicationSettings->setKey(
            'maintenance_cleandomainslogs',
            $clean ? '1' : '0'
           );
        $this->mCleanDomainsLogs = $clean ? true : false;
    }

    public function getCleanDomainsLogs()
    {
        return $this->mCleanDomainsLogs;
    }

    public function setRotateDomainsLogs(
        $clean
    )
    {
        $this->mApplicationSettings->setKey(
            'maintenance_rotatedomainslogs',
            $clean ? '1' : '0'
           );
        $this->mRotateDomainsLogs = $clean ? true : false;
    }

    public function getRotateDomainsLogs()
    {
        return $this->mRotateDomainsLogs;
    }

    // ----- Logs -----

    public function getSystemLogsSize()
    {
        $total = 0;
        $reg = \Innomatic\Util\Registry::instance();
        if (file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/log/innomatic.log')) $total += filesize(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/log/innomatic.log');
        if (file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/log/innomatic_root_db.log')) $total += filesize(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/log/innomatic_root_db.log');
        if (file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/log/access.log')) $total += filesize(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/log/access.log');
        if (file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/log/webservices.log')) $total += filesize(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/log/webservices.log');
        if (file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/log/php.log')) $total += filesize(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/log/php.log');

        return $total;
    }

    public function getDomainsLogsSize()
    {
        $total = 0;
        $domains_query = InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->execute(
            'SELECT domainid '.
            'FROM domains'
           );

        while (!$domains_query->eof) {
            $log_file = InnomaticContainer::instance('innomaticcontainer')->getHome().'core/domains/'.$domains_query->getFields('domainid').'/log/dataaccess.log';
            if (file_exists($log_file)) $total += filesize($log_file);
            if (file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/domains/'.$domains_query->getFields('domainid').'/log/domain.log'))
                $total += filesize(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/domains/'.$domains_query->getFields('domainid').'/log/domain.log');

            $domains_query->moveNext();
        }

        return $total;
    }

    public function cleanSystemLogs()
    {
        $reg = \Innomatic\Util\Registry::instance();

        if (
            $this->mCleanRootLog
            and file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/log/innomatic.log')
           )
        {
            $log = new \Innomatic\Logging\Logger(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/log/innomatic.log');
            $log->Rotate(0);
            unlink(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/log/innomatic.log');
        }
        if (
            $this->mCleanRootDbLog
            and file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/log/innomatic_root_db.log')
           )
        {
            $log = new \Innomatic\Logging\Logger(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/log/innomatic_root_db.log');
            $log->Rotate(0);
            unlink(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/log/innomatic_root_db.log');
        }
        if (
            $this->mCleanPhpLog
            and file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/log/php.log')
           )
        {
            $log = new \Innomatic\Logging\Logger(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/log/php.log');
            $log->Rotate(0);
            unlink(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/log/php.log');
        }
        if (
            $this->mCleanWebServicesLog
            and file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/log/webservices.log')
           )
        {
            $log = new \Innomatic\Logging\Logger(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/log/webservices.log');
            $log->Rotate(0);
            unlink(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/log/webservices.log');
        }
        if (
            $this->mCleanAccessLog
            and file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/log/access.log')
           )
        {
            $log = new \Innomatic\Logging\Logger(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/log/access.log');
            $log->Rotate(0);
            unlink(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/log/access.log');
        }

        if (
            $this->mRotateRootLog
           )
        {
            $log = new \Innomatic\Logging\Logger(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/log/innomatic.log');
            $log->Rotate(7);
        }
        if (
            $this->mRotateRootDbLog
           )
        {
            $log = new \Innomatic\Logging\Logger(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/log/innomatic_root_db.log');
            $log->Rotate(7);
        }
        if (
            $this->mRotatePhpLog
           )
        {
            $log = new \Innomatic\Logging\Logger(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/log/php.log');
            $log->Rotate(7);
        }
        if (
            $this->mRotateWebServicesLog
           )
        {
            $log = new \Innomatic\Logging\Logger(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/log/webservices.log');
            $log->Rotate(7);
        }
        if (
            $this->mRotateAccessLog
           )
        {
            $log = new \Innomatic\Logging\Logger(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/log/access.log');
            $log->Rotate(7);
        }

        return true;
    }

    public function CleanDomainsLogs()
    {
        $domains_query = InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->execute(
            'SELECT domainid '.
            'FROM domains'
           );

        while (!$domains_query->eof) {
            $da_log_file = InnomaticContainer::instance('innomaticcontainer')->getHome().'core/domains/'.$domains_query->getFields('domainid').'/log/dataaccess.log';
            if (file_exists($da_log_file)) {
                if ($this->mCleanDomainsLogs) {
                    $log = new \Innomatic\Logging\Logger($da_log_file);
                    $log->Rotate(0);
                    unlink($da_log_file);
                } elseif ($this->mRotateDomainsLogs) {
                    $log = new \Innomatic\Logging\Logger($da_log_file);
                    $log->Rotate(7);
                }
            }

            if (file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/domains/'.$domains_query->getFields('domainid').'/log/domain.log')) {
                if ($this->mCleanDomainsLogs) {
                    $log = new \Innomatic\Logging\Logger(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/domains/'.$domains_query->getFields('domainid').'/log/domain.log');
                    $log->Rotate(0);
                    unlink(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/domains/'.$domains_query->getFields('domainid').'/log/domain.log');
                } elseif ($this->mRotateDomainsLogs) {
                    $log = new \Innomatic\Logging\Logger(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/domains/'.$domains_query->getFields('domainid').'/log/domain.log');
                    $log->Rotate(7);
                }
            }

            $domains_query->moveNext();
        }

        return true;
    }

    /*
    public function RotateSystemLogs()
    {
    }

    public function RotateDomainsLogs()
    {
    }
    */

    // ----- Facilities -----

    public function execute()
    {
        $this->CleanSystemLogs();
        $this->CleanDomainsLogs();

        return true;
    }

    public function getCleanableDiskSize()
    {
        $total = 0;

        $total += $this->getSystemLogsSize();
        $total += $this->getDomainsLogsSize();

        return $total;
    }
}
