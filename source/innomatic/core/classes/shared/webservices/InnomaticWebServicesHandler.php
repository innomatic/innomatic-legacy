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
namespace Shared\Webservices;

use \Innomatic\Core\InnomaticContainer;

class InnomaticWebServicesHandler extends \Innomatic\Webservices\WebServicesHandler
{
    // Returns Innomatic main log file content
    //
    public static function log_root_get()
    {
        

        $log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();

        return new \Innomatic\Webservices\Xmlrpc\XmlRpcResp(new \Innomatic\Webservices\Xmlrpc\XmlRpcVal($log->RawReadLog()));
    }

    // Returns Innomatic main log file content
    //
    public static function log_root_erase()
    {
        

        $log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();

        return new \Innomatic\Webservices\Xmlrpc\XmlRpcResp(new \Innomatic\Webservices\Xmlrpc\XmlRpcVal($log->cleanLog()));
    }

    // Writes an event in the Innomatic main log file.
    //
    public static function log_root_logevent()
    {
        global $xmlrpcerruser;

        

        $log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();

        $event_caller = $m->getParam(0);
        $event_string = $m->getParam(1);
        $event_type = $m->getParam(2);

        return new \Innomatic\Webservices\Xmlrpc\XmlRpcResp(
            new \Innomatic\Webservices\Xmlrpc\XmlRpcVal(
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
        

        $log = new \Innomatic\Logging\Logger(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome()
            . 'core/log/webservices.log'
        );

        return new \Innomatic\Webservices\Xmlrpc\XmlRpcResp(new \Innomatic\Webservices\Xmlrpc\XmlRpcVal($log->rawReadLog()));
    }

    // Returns Innomatic web services log file content
    //
    public static function log_webservices_erase()
    {
        
        $log = new \Innomatic\Logging\Logger(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome()
            . 'core/log/webservices.log'
        );
        return new \Innomatic\Webservices\Xmlrpc\XmlRpcResp(new \Innomatic\Webservices\Xmlrpc\XmlRpcVal($log->cleanLog()));
    }

    // Writes an event in the Innomatic main log file.
    //
    public static function log_webservices_logevent()
    {
        

        $log = new \Innomatic\Logging\Logger(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome()
            . 'core/log/webservices.log'
        );

        $event_caller = $m->getParam(0);
        $event_string = $m->getParam(1);
        $event_type = $m->getParam(2);

        return new \Innomatic\Webservices\Xmlrpc\XmlRpcResp(
            new \Innomatic\Webservices\Xmlrpc\XmlRpcVal(
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
        
        $log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();
        return new \Innomatic\Webservices\Xmlrpc\XmlRpcResp(new \Innomatic\Webservices\Xmlrpc\XmlRpcVal($log->rawReadLog()));
    }

    // Returns Innomatic database log file content
    //
    public static function log_db_erase()
    {
        

        $log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();

        return new \Innomatic\Webservices\Xmlrpc\XmlRpcResp(new \Innomatic\Webservices\Xmlrpc\XmlRpcVal($log->cleanLog()));
    }

    // Writes an event in the Innomatic database log file.
    //
    public static function log_db_logevent()
    {
        
        $log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();

        $event_caller = $m->getParam(0);
        $event_string = $m->getParam(1);
        $event_type = $m->getParam(2);

        return new \Innomatic\Webservices\Xmlrpc\XmlRpcResp(
            new \Innomatic\Webservices\Xmlrpc\XmlRpcVal(
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

        $query = \Innomatic\Core\InnomaticContainer::instance(
            '\Innomatic\Core\InnomaticContainer'
        )->getDataAccess()->execute(
            'SELECT appversion FROM applications WHERE appid='
            . $this->mrRootDb->formatText('innomatic')
        );

        if ($query) {
            $result = $query->getFields('appversion');
        }

        return new \Innomatic\Webservices\Xmlrpc\XmlRpcResp(new \Innomatic\Webservices\Xmlrpc\XmlRpcVal($result));
    }

    // Returns the list of current applications
    //
    public static function applications_list()
    {
        $result = array();

        $query = \Innomatic\Core\InnomaticContainer::instance(
            '\Innomatic\Core\InnomaticContainer'
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

        return new \Innomatic\Webservices\Xmlrpc\XmlRpcResp(new \Innomatic\Webservices\Xmlrpc\XmlRpcVal($result));
    }

    // Installs a new Application
    //
    public static function applications_application_install($m)
    {
        //return new \Innomatic\Webservices\Xmlrpc\XmlRpcResp(
        //  new \Innomatic\Webservices\Xmlrpc\XmlRpcVal($innomatic->InstallApplication($appfile))
        //);
        return new \Innomatic\Webservices\Xmlrpc\XmlRpcResp(new \Innomatic\Webservices\Xmlrpc\XmlRpcVal(''));
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
            return new \Innomatic\Webservices\Xmlrpc\XmlRpcResp(0, $xmlrpcerruser, 'Wrong parameters');
            /*
            return new \Innomatic\Webservices\Xmlrpc\XmlRpcResp(
                new \Innomatic\Webservices\Xmlrpc\XmlRpcVal(
                    $innomatic->UninstallApplication($appid->scalarVal())
                )
            );
            */
        } else {
            return new \Innomatic\Webservices\Xmlrpc\XmlRpcResp(0, $xmlrpcerruser, 'Wrong parameters');
        }
    }

    // Returns the list of current domains
    //
    public static function domains_list()
    {
        $result = array();
        $query = \Innomatic\Core\InnomaticContainer::instance(
            '\Innomatic\Core\InnomaticContainer'
        )->getDataAccess()->execute('SELECT domainid,domainname FROM domains');

        if ($query) {
            $result = array();

            while (!$query->eof) {
                $result[] = $query->getFields();
                $query->moveNext();
            }
        }

        return new \Innomatic\Webservices\Xmlrpc\XmlRpcResp(new \Innomatic\Webservices\Xmlrpc\XmlRpcVal($result));
    }

    // Returns the list of the applications enabled to a given domain
    //
    public static function domains_domain_enabledapplications($m)
    {
        global $xmlrpcerruser;

        $domainid = $m->getParam(0);

        if (isset($domainid) and ($domainid->ScalarTyp() == 'string')) {
            $result = array();

            $query = \Innomatic\Core\InnomaticContainer::instance(
                '\Innomatic\Core\InnomaticContainer'
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

            return new \Innomatic\Webservices\Xmlrpc\XmlRpcResp(new \Innomatic\Webservices\Xmlrpc\XmlRpcVal($result));
        } else {
            return new \Innomatic\Webservices\Xmlrpc\XmlRpcResp(0, $xmlrpcerruser, 'Wrong parameters');
        }
    }
}
