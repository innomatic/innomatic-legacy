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
 */
namespace Innomatic\Application;

/**
 * This class handles operations with remote AppCentral repositories.
 *
 * @since 5.0.0 introduced
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 */
class AppCentralRemoteServer
{
    /**
     * Server id.
     *
     * @var integer
     * @access protected
     */
    protected $id;
    /**
     * Innomatic root data access handler.
     *
     * @var \Innomatic\Dataaccess\DataAccess
     * @access protected
     */
    protected $dataAccess;
    /**
     * Innomatic logger handler.
     *
     * @var \Innomatic\Logging\LogCenter
     * @access protected
     */
    protected $log;
    /**
     * Innomatic web services account id.
     *
     * @var integer
     * @access protected
     */
    protected $accountId;
    /**
     * Innomatic web services account handler.
     *
     * @var \Innomatic\Webservices\WebServicesAccount
     * @access protected
     */
    protected $account;
    /**
     * XmlRpc client handler.
     *
     * @var \Innomatic\Webservices\Xmlrpc\XmlRpcClient
     * @access protected
     */
    protected $client;

    /* public __construct($repId = null) {{{ */
    /**
     * Class constructor.
     *
     * @param integer $repId AppCentral repository id.
     * @access public
     * @return void
     */
    public function __construct($repId = null)
    {
        $this->dataAccess = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess();
        $this->log        = new \Innomatic\Logging\LogCenter('appcentral-client');

        if ($repId) {
            $repQuery = $this->dataAccess->execute(
                'SELECT * FROM applications_repositories WHERE id=' . $repId
            );

            if ($repQuery->getNumberRows()) {
                $this->id = $repId;
                $this->accountId = $repQuery->getFields('accountid');
                $this->setClient();
            }
        }
    }
    /* }}} */

    protected function setClient()
    {
        $this->account = new \Innomatic\Webservices\WebServicesAccount(
            $this->dataAccess,
            $this->accountId
        );

        $this->client = new \Innomatic\Webservices\Xmlrpc\XmlRpcClient(
            $this->account->mPath,
            $this->account->mHost,
            $this->account->mPort
        );

        $this->client->setCredentials(
            $this->account->mUsername,
            $this->account->mPassword
        );
    }

    public function add($accountId)
    {
        $result = false;

        if ($accountId) {
            $repId = $this->dataAccess->getNextSequenceValue(
                'applications_repositories_id_seq'
            );

            if (
                $this->dataAccess->execute(
                    'INSERT INTO applications_repositories '.
                    'VALUES ('.$repId.','.$accountId.')'
                )
            ) {
                $this->id = $repId;
                $this->accountId = $accountId;
                $this->setClient();

                $result = true;
            }
        }

        return $result;
    }

    public function Remove()
    {
        $result = false;

        if ( $this->mId ) {
            if (
                $this->mrRootDb->Execute(
                    'DELETE FROM applications_repositories '.
                    'WHERE id='.$this->mId
                )
            ) {
                $cachedItem = new \Innomatic\Datatransfer\Cache\CachedItem(
                    $this->mrRootDb,
                    'appcentral-client',
                    'repositories-'.$this->mId );

                $cachedItem->Destroy();

                $this->mId = 0;
                $result = true;
            }
        }

        return $result;
    }

    public function ListAvailableRepositories($refresh = false)
    {
        $result = false;

        $cachedItem = new \Innomatic\Datatransfer\Cache\CachedItem(
            $this->mrRootDb,
            'appcentral-client',
            'repositories-'.$this->mId );

        $goon = true;

        if ( !$refresh ) {
            $cacheContent = $cachedItem->Retrieve();

            if ( $cacheContent != false ) {
                $goon = false;
                $result = unserialize($cacheContent);
            }
        }

        if ( $goon ) {

            $xmlrpcMessage = new \Innomatic\Webservices\Xmlrpc\XmlRpcMsg(
                'appcentral-server.list_available_repositories'
            );
            $xmlrpcResp = $this->mXClient->Send($xmlrpcMessage);

            if ( $xmlrpcResp ) {

                if ( !$xmlrpcResp->FaultCode() ) {

                    $xv = \Innomatic\Webservices\Xmlrpc\php_xmlrpc_decode($xmlrpcResp->Value());

                    if ( is_array($xv) ) {

                        $cachedItem->Store(serialize($xv));

                        $result = $xv;
                    } else $this->mLogCenter->logEvent(
                        array( 'root' => '' ),
                        'appcentral-client.appcentral-client.appcentralremoteserver.listavailablerepositories',
                        'Not an array from server',
                        \Innomatic\Logging\Logger::ERROR
                    );
                } else $this->mLogCenter->logEvent(
                    array( 'root' => '' ),
                    'appcentral-client.appcentral-client.appcentralremoteserver.listavailablerepositories',
                    'Error in response from server: '.$xmlrpcResp->FaultString(),
                    \Innomatic\Logging\Logger::ERROR
                );
            } else $this->mLogCenter->logEvent(
                array( 'root' => '' ),
                'appcentral-client.appcentral-client.appcentralremoteserver.listavailablerepositories',
                'Invalid response from server',
                \Innomatic\Logging\Logger::ERROR
            );
        }

        return $result;
    }

    public function ListAvailableApplications($repId, $refresh = false)
    {
        $result = false;

        $cachedItem = new \Innomatic\Datatransfer\Cache\CachedItem(
            $this->mrRootDb,
            'appcentral-client',
            'repository_applications-'.$this->mId.'-'.$repId
        );

        $goon = true;

        if ( !$refresh ) {
            $cacheContent = $cachedItem->Retrieve();

            if ( $cacheContent != false ) {
                $goon = false;
                $result = unserialize($cacheContent);
            }
        }

        if ( $goon ) {
            $xmlrpcMessage = new \Innomatic\Webservices\Xmlrpc\XmlRpcMsg(
                'appcentral-server.list_available_applications',
                array(
                    new \Innomatic\Webservices\Xmlrpc\XmlRpcVal($repId, 'int')
                )
            );

            $xmlrpcResp = $this->mXClient->Send($xmlrpcMessage);

            if ( $xmlrpcResp ) {
                if ( !$xmlrpcResp->FaultCode() ) {
                    $xv = \Innomatic\Webservices\Xmlrpc\php_xmlrpc_decode($xmlrpcResp->Value());

                    if ( is_array($xv) ) {
                        $cachedItem->Store(serialize($xv));

                        $result = $xv;
                    } else $this->mLogCenter->logEvent(
                        array('root' => ''),
                        'appcentral-client.appcentral-client.appcentralremoteserver.listavailableapplications',
                        'Not an array from server',
                        \Innomatic\Logging\Logger::ERROR
                    );
                } else $this->mLogCenter->logEvent(
                    array('root' => ''),
                    'appcentral-client.appcentral-client.appcentralremoteserver.listavailableapplications',
                    'Error in response from server: '.$xmlrpcResp->FaultString(),
                    \Innomatic\Logging\Logger::ERROR
                );
            } else $this->mLogCenter->logEvent(
                array('root' => ''),
                'appcentral-client.appcentral-client.appcentralremoteserver.listavailableapplications',
                'Invalid response from server',
                \Innomatic\Logging\Logger::ERROR
            );
        }

        return $result;
    }

    public function ListAvailableApplicationVersions($repId, $applicationId, $refresh = false)
    {
        $result = false;

        $cachedItem = new \Innomatic\Datatransfer\Cache\CachedItem(
            $this->mrRootDb,
            'appcentral-client',
            'repository_application_versions-'.$this->mId.'-'.$repId.'-'.$applicationId );

        $goon = true;

        if ( !$refresh ) {
            $cacheContent = $cachedItem->Retrieve();

            if ( $cacheContent != false ) {
                $goon = false;
                $result = unserialize($cacheContent);
            }
        }

        if ( $goon ) {
            $xmlrpcMessage = new \Innomatic\Webservices\Xmlrpc\XmlRpcMsg(
                'appcentral-server.list_available_application_versions',
                array(
                    new \Innomatic\Webservices\Xmlrpc\XmlRpcVal($repId, 'int'),
                    new \Innomatic\Webservices\Xmlrpc\XmlRpcVal($applicationId, 'int')
                )
            );

            $xmlrpcResp = $this->mXClient->Send($xmlrpcMessage);

            if ( $xmlrpcResp ) {
                if ( !$xmlrpcResp->FaultCode() ) {
                    $xv = \Innomatic\Webservices\Xmlrpc\php_xmlrpc_decode($xmlrpcResp->Value());

                    if ( is_array($xv) ) {
                        $cachedItem->Store(serialize($xv));
                        $result = $xv;
                    } else $this->mLogCenter->logEvent(
                        array('root' => ''),
                        'appcentral-client.appcentral-client.appcentralremoteserver.listavailableapplications',
                        'Not an array from server',
                        \Innomatic\Logging\Logger::ERROR
                    );
                } else $this->mLogCenter->logEvent(
                    array('root' => ''),
                    'appcentral-client.appcentral-client.appcentralremoteserver.listavailableapplications',
                    'Error in response from server: '.$xmlrpcResp->FaultString(),
                    \Innomatic\Logging\Logger::ERROR
                );
            } else $this->mLogCenter->logEvent(
                array('root' => ''),
                'appcentral-client.appcentral-client.appcentralremoteserver.listavailableapplications',
                'Invalid response from server',
                \Innomatic\Logging\Logger::ERROR
            );
        }

        return $result;
    }

    public function RetrieveApplication($repId, $applicationId, $applicationVersion = '')
    {
        $result = false;

        $xmlrpcMessage = new \Innomatic\Webservices\Xmlrpc\XmlRpcMsg(
            'appcentral-server.retrieve_application',
            array(
                new \Innomatic\Webservices\Xmlrpc\XmlRpcVal($repId, 'int'),
                new \Innomatic\Webservices\Xmlrpc\XmlRpcVal($applicationId, 'int'),
                new \Innomatic\Webservices\Xmlrpc\XmlRpcVal($applicationVersion, 'string')
            )
        );

        $xmlrpcResp = $this->mXClient->Send($xmlrpcMessage);

        if ( $xmlrpcResp ) {
            if ( !$xmlrpcResp->FaultCode() ) {
                $xv = $xmlrpcResp->Value();

                $tmpFilename = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome()
                .'core/temp/appcentral-client/'.md5(uniqid(rand())).'.tgz';

                $fh = fopen($tmpFilename, 'wb');
                if ( $fh ) {
                    fputs($fh, $xv->scalarVal());
                    fclose($fh);

                    unset($xv);
                    unset($xmlrpcResp);

                    $tmpApplication = new \Innomatic\Application\Application($this->mrRootDb, '');
                    if ( $tmpApplication->Install($tmpFilename) ) $result = true;
                }
            } else $this->mLogCenter->logEvent(
                array('root' => ''),
                'appcentral-client.appcentral-client.appcentralremoteserver.retrieveapplication',
                'Error in response from server: '.$xmlrpcResp->FaultString(),
                \Innomatic\Logging\Logger::ERROR
            );
        } else $this->mLogCenter->logEvent(
            array('root' => ''),
            'appcentral-client.appcentral-client.appcentralremoteserver.retrieveapplication',
            'Invalid response from server',
            \Innomatic\Logging\Logger::ERROR
        );

        return $result;
    }
}
