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
    public function LogCenter($application = '')
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
            $tmp_log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
            $tmp_log->logEvent($context, $eventString, $eventType);
            unset($tmp_log);
        }

        // Root db
        //
        if (isset($destinations['rootda'])) {
            $tmp_log = new \Innomatic\Logging\Logger(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/log/innomatic_root_db.log');
            $tmp_log->logEvent($context, $eventString, $eventType);
            unset($tmp_log);
        }

        // Web services
        //
        if (isset($destinations['webservices'])) {
            $tmp_log = new \Innomatic\Logging\Logger(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/log/webservices.log');
            $tmp_log->logEvent($context, $eventString, $eventType);
            unset($tmp_log);
        }

        // PHP
        //
        if (isset($destinations['php'])) {
            if (InnomaticContainer::instance('innomaticcontainer')->getState() != InnomaticContainer::STATE_SETUP) {
                $php_log = InnomaticContainer::instance('innomaticcontainer')->getHome().'core/log/php.log';
            } else {
                $php_log = InnomaticContainer::instance('innomaticcontainer')->getHome().'core/log/innomatic.log';
            }
            $tmp_log = new \Innomatic\Logging\Logger($php_log);
            $tmp_log->logEvent($context, $eventString, $eventType);
            unset($tmp_log);
        }

        // Application
        //
        if (isset($destinations['application']) and is_dir(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/applications/'.$this->mApplication)) {
            $tmp_log = new \Innomatic\Logging\Logger(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/applications/'.$this->mApplication.'/application.log');
            $tmp_log->logEvent($context, $eventString, $eventType);
            unset($tmp_log);
        }

        // Domain
        //
        if (isset($destinations['domain'])) {
            $tmp_log = new \Innomatic\Logging\Logger(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/domains/'.InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->domaindata['domainid'].'/log/domain.log');
            $tmp_log->logEvent($context, $eventString, $eventType);
            unset($tmp_log);
        }

        // Domain dataaccess
        //
        if (isset($destinations['domainda'])) {
            $tmp_log = new \Innomatic\Logging\Logger(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/domains/'.InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->domaindata['domainid'].'/logs/dataaccess.log');
            $tmp_log->logEvent($context, $eventString, $eventType);
            unset($tmp_log);
        }

        if ($die)
            InnomaticContainer::instance('innomaticcontainer')->abort($eventString);
        return true;
    }
}
