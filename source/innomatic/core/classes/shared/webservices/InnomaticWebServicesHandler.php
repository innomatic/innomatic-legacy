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

require_once('innomatic/webservices/xmlrpc/XmlRpc_Client.php');
require_once('innomatic/webservices/WebServicesHandler.php');

class InnomaticWebServicesHandler extends WebServicesHandler
{
    // Returns Innomatic main log file content
    //
    public static function log_root_get()
    {
        require_once('innomatic/logging/Logger.php');

        $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();

        return new XmlRpcResp(new XmlRpcVal($log->RawReadLog()));
    }

    // Returns Innomatic main log file content
    //
    public static function log_root_erase()
    {
        require_once('innomatic/logging/Logger.php');

        $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();

        return new XmlRpcResp(new XmlRpcVal($log->cleanLog()));
    }

    // Writes an event in the Innomatic main log file.
    //
    public static function log_root_logevent()
    {
        global $xmlrpcerruser;

        require_once('innomatic/logging/Logger.php');

        $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();

        $event_caller = $m->getParam(0);
        $event_string = $m->getParam(1);
        $event_type = $m->getParam(2);

        return new XmlRpcResp(
            new XmlRpcVal(
                $log->logEvent(
                    $event_caller->scalarVal(),
                    $event_string->scalarVal(),
                    $event_type->scalarVal()
                )
            )
        );
    }

    // Returns Innomatic web services log file content
    //
    public static function log_webservices_get()
    {
        require_once('innomatic/logging/Logger.php');

        $log = new Logger(
            InnomaticContainer::instance('innomaticcontainer')->getHome()
            . 'core/log/webservices.log'
        );

        return new XmlRpcResp(new XmlRpcVal($log->rawReadLog()));
    }

    // Returns Innomatic web services log file content
    //
    public static function log_webservices_erase()
    {
        require_once('innomatic/logging/Logger.php');
        $log = new Logger(
            InnomaticContainer::instance('innomaticcontainer')->getHome()
            . 'core/log/webservices.log'
        );
        return new XmlRpcResp(new XmlRpcVal($log->cleanLog()));
    }

    // Writes an event in the Innomatic main log file.
    //
    public static function log_webservices_logevent()
    {
        require_once('innomatic/logging/Logger.php');

        $log = new Logger(
            InnomaticContainer::instance('innomaticcontainer')->getHome()
            . 'core/log/webservices.log'
        );

        $event_caller = $m->getParam(0);
        $event_string = $m->getParam(1);
        $event_type = $m->getParam(2);

        return new XmlRpcResp(
            new XmlRpcVal(
                $log->logEvent(
                    $event_caller->scalarVal(),
                    $event_string->scalarVal(),
                    $event_type->scalarVal()
                )
            )
        );
    }

    // Returns Innomatic database log file content
    //
    public static function log_db_get()
    {
        require_once('innomatic/logging/Logger.php');
        $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
        return new XmlRpcResp(new XmlRpcVal($log->rawReadLog()));
    }

    // Returns Innomatic database log file content
    //
    public static function log_db_erase()
    {
        require_once('innomatic/logging/Logger.php');

        $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();

        return new XmlRpcResp(new XmlRpcVal($log->cleanLog()));
    }

    // Writes an event in the Innomatic database log file.
    //
    public static function log_db_logevent()
    {
        require_once('innomatic/logging/Logger.php');
        $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();

        $event_caller = $m->getParam(0);
        $event_string = $m->getParam(1);
        $event_type = $m->getParam(2);

        return new XmlRpcResp(
            new XmlRpcVal(
                $log->logEvent(
                    $event_caller->scalarVal(),
                    $event_string->scalarVal(),
                    $event_type->scalarVal()
                )
            )
        );
    }

    // Returns Innomatic version
    //
    public static function version()
    {
        $result = '';

        $query = InnomaticContainer::instance(
            'innomaticcontainer'
        )->getDataAccess()->execute(
            'SELECT appversion FROM applications WHERE appid='
            . $this->mrRootDb->formatText('innomatic')
        );

        if ($query) {
            $result = $query->getFields('appversion');
        }

        return new XmlRpcResp(new XmlRpcVal($result));
    }

    // Returns the list of current applications
    //
    public static function applications_list()
    {
        $result = array();

        $query = InnomaticContainer::instance(
            'innomaticcontainer'
        )->getDataAccess()->execute(
            'SELECT appid,appversion,appdate '
            . 'FROM applications'
        );

        if ($query) {
            $result = array();

            while (!$query->eof) {
                $result[] = $query->getFields();
                $query->moveNext();
            }
        }

        return new XmlRpcResp(new XmlRpcVal($result));
    }

    // Installs a new Application
    //
    public static function applications_application_install($m)
    {
        //return new XmlRpcResp(
        //  new XmlRpcVal($innomatic->InstallApplication($appfile))
        //);
        return new XmlRpcResp(new XmlRpcVal(''));
    }

    // Removes a application
    //
    public static function applications_application_remove($m)
    {
        global $xmlrpcerruser;

        $appid = $m->getParam(0);

        if (
            isset($appid)
            and ($appid->ScalarTyp() == 'string')
        )
        {
            return new XmlRpcResp(0, $xmlrpcerruser, 'Wrong parameters');
            /*
            return new XmlRpcResp(
                new XmlRpcVal(
                    $innomatic->UninstallApplication($appid->scalarVal())
                )
            );
            */
        } else {
            return new XmlRpcResp(0, $xmlrpcerruser, 'Wrong parameters');
        }
    }

    // Returns the list of current domains
    //
    public static function domains_list()
    {
        $result = array();
        $query = InnomaticContainer::instance(
            'innomaticcontainer'
        )->getDataAccess()->execute('SELECT domainid,domainname FROM domains');

        if ($query) {
            $result = array();

            while (!$query->eof) {
                $result[] = $query->getFields();
                $query->moveNext();
            }
        }

        return new XmlRpcResp(new XmlRpcVal($result));
    }

    // Returns the list of the applications enabled to a given domain
    //
    public static function domains_domain_enabledapplications($m)
    {
        global $xmlrpcerruser;

        $domainid = $m->getParam(0);

        if (isset($domainid) and ($domainid->ScalarTyp() == 'string')) {
            $result = array();

            $query = InnomaticContainer::instance(
                'innomaticcontainer'
            )->getDataAccess()->execute(
                'SELECT applications.appid ' .
                'FROM applications_enabled,domains,applications ' .
                'WHERE applications_enabled.domainid=domains.id ' .
                'AND domains.domainid='
                . $this->mrRootDb->formatText($domainid->scalarVal()) . ' ' .
                'AND applications_enabled.applicationid=applications.id'
            );

            if (is_object($query)) {
                while (!$query->eof) {
                    $result[] = $query->getFields();
                    $query->moveNext();
                }
            }

            return new XmlRpcResp(new XmlRpcVal($result));
        } else {
            return new XmlRpcResp(0, $xmlrpcerruser, 'Wrong parameters');
        }
    }
}
