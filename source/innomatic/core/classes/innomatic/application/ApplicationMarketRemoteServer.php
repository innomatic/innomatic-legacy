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

class ApplicationMarketRemoteServer
{
    var $mId;
    var $mrRootDb;
    var $mLogCenter;
    var $mAccountId;
    var $mXAccount;
    var $mXClient;

    public function __construct($rrootDb, $repId)
    {
        $this->mrRootDb = $rrootDb;
        require_once('innomatic/logging/LogCenter.php');
        $this->mLogCenter = new LogCenter('appcentral-client');

        if ( $repId ) {
            $repQuery = $this->mrRootDb->execute(
                'SELECT * FROM applications_repositories WHERE id=' . $repId
            );

            if ( $repQuery->getNumberRows() ) {
                $this->mId = $repId;
                $this->mAccountId = $repQuery->getFields('accountid');
                $this->SetClient();
            }
        }
    }

    function SetClient()
    {
        require_once('innomatic/webservices/WebServicesAccount.php');
        $this->mXAccount = new WebServicesAccount(
            $this->mrRootDb,
            $this->mAccountId
        );

        require_once('innomatic/webservices/xmlrpc/XmlRpc_Client.php');
        $this->mXClient = new XmlRpc_Client(
            $this->mXAccount->mPath,
            $this->mXAccount->mHost,
            $this->mXAccount->mPort
        );

        $this->mXClient->SetCredentials(
            $this->mXAccount->mUsername,
            $this->mXAccount->mPassword
        );

        //$this->mXClient->SetDebug( true );
    }

    function Add( $accountId )
    {
        $result = false;

        if ( $accountId ) {
            $repId = $this->mrRootDb->getNextSequenceValue(
                'applications_repositories_id_seq'
            );

            if (
                $this->mrRootDb->Execute(
                    'INSERT INTO applications_repositories '.
                    'VALUES ('.$repId.','.$accountId.')'
                )
            ) {
                $this->mId = $repId;
                $this->mAccountId = $accountId;
                $this->SetClient();

                $result = true;
            }
        }

        return $result;
    }

    function Remove()
    {
        $result = false;

        if ( $this->mId ) {
            if (
                $this->mrRootDb->Execute(
                    'DELETE FROM applications_repositories '.
                    'WHERE id='.$this->mId
                )
            ) {
                require_once('innomatic/datatransfer/cache/CachedItem.php');
                $cachedItem = new CachedItem(
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

    function ListAvailableRepositories( $refresh = false )
    {
        $result = false;

        require_once('innomatic/datatransfer/cache/CachedItem.php');
        $cachedItem = new CachedItem(
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
            
            $xmlrpcMessage = new XmlRpcMsg(
                'appcentral-server.list_available_repositories'
            );
            $xmlrpcResp = $this->mXClient->Send($xmlrpcMessage);

            if ( $xmlrpcResp ) {
                
                if ( !$xmlrpcResp->FaultCode() ) {
                    
                    $xv = php_xmlrpc_decode($xmlrpcResp->Value());

                    if ( is_array($xv) ) {
                        
                        $cachedItem->Store(serialize($xv));

                        $result = $xv;
                    }
                    else $this->mLogCenter->logEvent(
                        array( 'root' => '' ),
                        'appcentral-client.appcentral-client.appcentralremoteserver.listavailablerepositories',
                        'Not an array from server',
                        Logger::ERROR
                    );
                }
                else $this->mLogCenter->logEvent(
                    array( 'root' => '' ),
                    'appcentral-client.appcentral-client.appcentralremoteserver.listavailablerepositories',
                    'Error in response from server: '.$xmlrpcResp->FaultString(),
                    Logger::ERROR
                );
            }
            else $this->mLogCenter->logEvent(
                array( 'root' => '' ),
                'appcentral-client.appcentral-client.appcentralremoteserver.listavailablerepositories',
                'Invalid response from server',
                Logger::ERROR
            );
        }

        return $result;
    }

    function ListAvailableApplications( $repId, $refresh = false )
    {
        $result = false;

        require_once('innomatic/datatransfer/cache/CachedItem.php');
        $cachedItem = new CachedItem(
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
            $xmlrpcMessage = new XmlRpcMsg(
                'appcentral-server.list_available_applications',
                array(
                    new XmlRpcVal($repId, 'int')
                )
            );

            $xmlrpcResp = $this->mXClient->Send($xmlrpcMessage);

            if ( $xmlrpcResp ) {
                if ( !$xmlrpcResp->FaultCode() ) {
                    $xv = php_xmlrpc_decode($xmlrpcResp->Value());

                    if ( is_array($xv) ) {
                        $cachedItem->Store(serialize($xv));

                        $result = $xv;
                    }
                    else $this->mLogCenter->logEvent(
                        array('root' => ''),
                        'appcentral-client.appcentral-client.appcentralremoteserver.listavailableapplications',
                        'Not an array from server',
                        Logger::ERROR
                    );
                }
                else $this->mLogCenter->logEvent(
                    array('root' => ''),
                    'appcentral-client.appcentral-client.appcentralremoteserver.listavailableapplications',
                    'Error in response from server: '.$xmlrpcResp->FaultString(),
                    Logger::ERROR
                );
            }
            else $this->mLogCenter->logEvent(
                array('root' => ''),
                'appcentral-client.appcentral-client.appcentralremoteserver.listavailableapplications',
                'Invalid response from server',
                Logger::ERROR
            );
        }

        return $result;
    }

    function ListAvailableApplicationVersions( $repId, $applicationId, $refresh = false )
    {
        $result = false;

        require_once('innomatic/datatransfer/cache/CachedItem.php');
        $cachedItem = new CachedItem(
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
            $xmlrpcMessage = new XmlRpcMsg(
                'appcentral-server.list_available_application_versions',
                array(
                    new XmlRpcVal($repId, 'int'),
                    new XmlRpcVal($applicationId, 'int')
                )
            );

            $xmlrpcResp = $this->mXClient->Send($xmlrpcMessage);

            if ( $xmlrpcResp ) {
                if ( !$xmlrpcResp->FaultCode() ) {
                    $xv = php_xmlrpc_decode($xmlrpcResp->Value());

                    if ( is_array($xv) ) {
                        $cachedItem->Store(serialize($xv));
                        $result = $xv;
                    }
                    else $this->mLogCenter->logEvent(
                        array('root' => ''),
                        'appcentral-client.appcentral-client.appcentralremoteserver.listavailableapplications',
                        'Not an array from server',
                        Logger::ERROR
                    );
                }
                else $this->mLogCenter->logEvent(
                    array('root' => ''),
                    'appcentral-client.appcentral-client.appcentralremoteserver.listavailableapplications',
                    'Error in response from server: '.$xmlrpcResp->FaultString(),
                    Logger::ERROR
                );
            }
            else $this->mLogCenter->logEvent(
                array('root' => ''),
                'appcentral-client.appcentral-client.appcentralremoteserver.listavailableapplications',
                'Invalid response from server',
                Logger::ERROR
            );
        }

        return $result;
    }

    function RetrieveApplication($repId, $applicationId, $applicationVersion = '')
    {
        $result = false;

        $xmlrpcMessage = new XmlRpcMsg(
            'appcentral-server.retrieve_application',
            array(
                new XmlRpcVal($repId, 'int'),
                new XmlRpcVal($applicationId, 'int'),
                new XmlRpcVal($applicationVersion, 'string')
            )
        );

        $xmlrpcResp = $this->mXClient->Send($xmlrpcMessage);

        if ( $xmlrpcResp ) {
            if ( !$xmlrpcResp->FaultCode() ) {
                $xv = $xmlrpcResp->Value();

                $tmpFilename = InnomaticContainer::instance('innomaticcontainer')->getHome()
                .'core/temp/appcentral-client/'.md5(uniqid(rand()));

                $fh = fopen($tmpFilename, 'wb');
                if ( $fh ) {
                    require_once('innomatic/application/Application.php');

                    fputs($fh, $xv->scalarVal());
                    fclose($fh);

                    unset($xv);
                    unset($xmlrpcResp);

                    $tmpApplication = new Application($this->mrRootDb, '');
                    if ( $tmpApplication->Install($tmpFilename) ) $result = true;
                }
            }
            else $this->mLogCenter->logEvent(
                array('root' => ''),
                'appcentral-client.appcentral-client.appcentralremoteserver.retrieveapplication',
                'Error in response from server: '.$xmlrpcResp->FaultString(),
                Logger::ERROR
            );
        }
        else $this->mLogCenter->logEvent(
            array('root' => ''),
            'appcentral-client.appcentral-client.appcentralremoteserver.retrieveapplication',
            'Invalid response from server',
            Logger::ERROR
        );

        return $result;
    }
}
