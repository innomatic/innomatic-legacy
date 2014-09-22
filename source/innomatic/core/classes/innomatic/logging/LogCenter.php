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
namespace Innomatic\Logging;

/*!
@class LogCenter
@abstract Automatic logging of events in multiple logs.
*/
class LogCenter
{
    /*! @public mApplication string - Application id name. */
    private $mApplication;

    /*!
    @function LogCenter
    @abstract Class constructor.
    */
    public function __construct($application = '')
    {
        $this->mApplication = $application;
    }

    /*!
    @function LogEvent
    @abstract Logs an event
    @param destinations array - Array of the destination logs. Available keys: root, rootda,
    webservices, php, application, domain, domainda.
    @param context string - Event context.
    @param eventString string - String to be logged.
    @param eventType integer - Type of log event.
    @param die boolean - True if Innomatic must die after logging the event.
    @result Always true
    */
    public function logEvent($destinations, $context, $eventString, $eventType = \Innomatic\Logging\Logger::GENERIC, $die = false)
    {
        // Root
        //
        if (isset($destinations['root'])) {
            $tmp_log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();
            $tmp_log->logEvent($context, $eventString, $eventType);
            unset($tmp_log);
        }

        // Root db
        //
        if (isset($destinations['rootda'])) {
            $tmp_log = new \Innomatic\Logging\Logger(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome().'core/log/innomatic_root_db.log');
            $tmp_log->logEvent($context, $eventString, $eventType);
            unset($tmp_log);
        }

        // Web services
        //
        if (isset($destinations['webservices'])) {
            $tmp_log = new \Innomatic\Logging\Logger(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome().'core/log/webservices.log');
            $tmp_log->logEvent($context, $eventString, $eventType);
            unset($tmp_log);
        }

        // PHP
        //
        if (isset($destinations['php'])) {
            if (\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getState() != \Innomatic\Core\InnomaticContainer::STATE_SETUP) {
                $php_log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome().'core/log/php.log';
            } else {
                $php_log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome().'core/log/innomatic.log';
            }
            $tmp_log = new \Innomatic\Logging\Logger($php_log);
            $tmp_log->logEvent($context, $eventString, $eventType);
            unset($tmp_log);
        }

        // Application
        //
        if (isset($destinations['application']) and is_dir(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome().'core/applications/'.$this->mApplication)) {
            $tmp_log = new \Innomatic\Logging\Logger(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome().'core/applications/'.$this->mApplication.'/application.log');
            $tmp_log->logEvent($context, $eventString, $eventType);
            unset($tmp_log);
        }

        // Tenant
        //
        if (isset($destinations['domain'])) {
            $tmp_log = new \Innomatic\Logging\Logger(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome().'core/domains/'.\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->domaindata['domainid'].'/log/domain.log');
            $tmp_log->logEvent($context, $eventString, $eventType);
            unset($tmp_log);
        }

        // Tenant dataaccess
        //
        if (isset($destinations['domainda'])) {
            $tmp_log = new \Innomatic\Logging\Logger(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome().'core/domains/'.\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->domaindata['domainid'].'/logs/dataaccess.log');
            $tmp_log->logEvent($context, $eventString, $eventType);
            unset($tmp_log);
        }

        if ($die)
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->abort($eventString);
        return true;
    }
}
