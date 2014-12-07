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

    public function remove()
    {
        $result = false;

        if ($this->id) {
            if (
                $this->dataAccess->execute(
                    'DELETE FROM applications_repositories '.
                    'WHERE id='.$this->id
                )
            ) {
                // Destroy the cache for this repository.
                $cachedItem = new \Innomatic\Datatransfer\Cache\CachedItem(
                    $this->dataAccess,
                    'appcentral-client',
                    'repositories-'.$this->id
                );

                $cachedItem->destroy();

                $this->id = 0;
                $result = true;
            }
        }

        return $result;
    }

    public function listAvailableRepositories($refresh = false)
    {
        $result = false;

        $cachedItem = new \Innomatic\Datatransfer\Cache\CachedItem(
            $this->dataAccess,
            'appcentral-client',
            'repositories-'.$this->id
        );

        $goon = true;

        if (!$refresh) {
            $cacheContent = $cachedItem->retrieve();

            if ($cacheContent != false) {
                $goon = false;
                $result = unserialize($cacheContent);
            }
        }

        if ($goon) {
            $xmlrpcMessage = new \Innomatic\Webservices\Xmlrpc\XmlRpcMsg(
                'appcentral-server.list_available_repositories'
            );
            $xmlrpcResp = $this->client->send($xmlrpcMessage);

            if ($xmlrpcResp) {
                if (!$xmlrpcResp->faultCode()) {
                    $xv = \Innomatic\Webservices\Xmlrpc\php_xmlrpc_decode($xmlrpcResp->value());

                    if (is_array($xv)) {
                        $cachedItem->store(serialize($xv));

                        $result = $xv;
                    } else $this->log->logEvent(
                        ['root' => ''],
                        'appcentral-client.appcentral-client.appcentralremoteserver.listavailablerepositories',
                        'Not an array from server',
                        \Innomatic\Logging\Logger::ERROR
                    );
                } else $this->log->logEvent(
                    ['root' => ''],
                    'appcentral-client.appcentral-client.appcentralremoteserver.listavailablerepositories',
                    'Error in response from server: '.$xmlrpcResp->faultString(),
                    \Innomatic\Logging\Logger::ERROR
                );
            } else $this->log->logEvent(
                ['root' => ''],
                'appcentral-client.appcentral-client.appcentralremoteserver.listavailablerepositories',
                'Invalid response from server',
                \Innomatic\Logging\Logger::ERROR
            );
        }

        return $result;
    }

    public function listAvailableApplications($repId, $refresh = false)
    {
        $result = false;

        $cachedItem = new \Innomatic\Datatransfer\Cache\CachedItem(
            $this->dataAccess,
            'appcentral-client',
            'repository_applications-'.$this->id.'-'.$repId
        );

        $goon = true;

        if (!$refresh) {
            $cacheContent = $cachedItem->retrieve();

            if ($cacheContent != false) {
                $goon = false;
                $result = unserialize($cacheContent);
            }
        }

        if ($goon) {
            $xmlrpcMessage = new \Innomatic\Webservices\Xmlrpc\XmlRpcMsg(
                'appcentral-server.list_available_applications',
                [new \Innomatic\Webservices\Xmlrpc\XmlRpcVal($repId, 'int')]
            );

            $xmlrpcResp = $this->client->send($xmlrpcMessage);

            if ($xmlrpcResp) {
                if (!$xmlrpcResp->faultCode()) {
                    $xv = \Innomatic\Webservices\Xmlrpc\php_xmlrpc_decode($xmlrpcResp->value());

                    if (is_array($xv)) {
                        $cachedItem->store(serialize($xv));

                        $result = $xv;
                    } else $this->log->logEvent(
                        ['root' => ''],
                        'appcentral-client.appcentral-client.appcentralremoteserver.listavailableapplications',
                        'Not an array from server',
                        \Innomatic\Logging\Logger::ERROR
                    );
                } else $this->log->logEvent(
                    ['root' => ''],
                    'appcentral-client.appcentral-client.appcentralremoteserver.listavailableapplications',
                    'Error in response from server: '.$xmlrpcResp->faultString(),
                    \Innomatic\Logging\Logger::ERROR
                );
            } else $this->log->logEvent(
                ['root' => ''],
                'appcentral-client.appcentral-client.appcentralremoteserver.listavailableapplications',
                'Invalid response from server',
                \Innomatic\Logging\Logger::ERROR
            );
        }

        return $result;
    }

    public function listAvailableApplicationVersions($repId, $applicationId, $refresh = false)
    {
        $result = false;

        $cachedItem = new \Innomatic\Datatransfer\Cache\CachedItem(
            $this->dataAccess,
            'appcentral-client',
            'repository_application_versions-'.$this->id.'-'.$repId.'-'.$applicationId);

        $goon = true;

        if (!$refresh) {
            $cacheContent = $cachedItem->retrieve();

            if ($cacheContent != false) {
                $goon = false;
                $result = unserialize($cacheContent);
            }
        }

        if ($goon) {
            $xmlrpcMessage = new \Innomatic\Webservices\Xmlrpc\XmlRpcMsg(
                'appcentral-server.list_available_application_versions',
                array(
                    new \Innomatic\Webservices\Xmlrpc\XmlRpcVal($repId, 'int'),
                    new \Innomatic\Webservices\Xmlrpc\XmlRpcVal($applicationId, 'int')
                )
            );

            $xmlrpcResp = $this->client->send($xmlrpcMessage);

            if ($xmlrpcResp) {
                if (!$xmlrpcResp->faultCode()) {
                    $xv = \Innomatic\Webservices\Xmlrpc\php_xmlrpc_decode($xmlrpcResp->value());

                    if (is_array($xv)) {
                        $cachedItem->store(serialize($xv));
                        $result = $xv;
                    } else $this->log->logEvent(
                        ['root' => ''],
                        'appcentral-client.appcentral-client.appcentralremoteserver.listavailableapplications',
                        'Not an array from server',
                        \Innomatic\Logging\Logger::ERROR
                    );
                } else $this->log->logEvent(
                    ['root' => ''],
                    'appcentral-client.appcentral-client.appcentralremoteserver.listavailableapplications',
                    'Error in response from server: '.$xmlrpcResp->faultString(),
                    \Innomatic\Logging\Logger::ERROR
                );
            } else $this->log->logEvent(
                ['root' => ''],
                'appcentral-client.appcentral-client.appcentralremoteserver.listavailableapplications',
                'Invalid response from server',
                \Innomatic\Logging\Logger::ERROR
            );
        }

        return $result;
    }

    public function retrieveApplication($repId, $applicationId, $applicationVersion = '')
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

        $xmlrpcResp = $this->client->send($xmlrpcMessage);

        if ($xmlrpcResp) {
            if (!$xmlrpcResp->faultCode()) {
                $xv = $xmlrpcResp->value();

                $tmpFilename = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome()
                .'core/temp/appcentral-client/'.md5(uniqid(rand())).'.tgz';

                $fh = fopen($tmpFilename, 'wb');
                if ($fh) {
                    fputs($fh, $xv->scalarVal());
                    fclose($fh);

                    unset($xv);
                    unset($xmlrpcResp);

                    $tmpApplication = new \Innomatic\Application\Application($this->dataAccess, '');
                    if ($tmpApplication->install($tmpFilename)) $result = true;
                }
            } else $this->log->logEvent(
                ['root' => ''],
                'appcentral-client.appcentral-client.appcentralremoteserver.retrieveapplication',
                'Error in response from server: '.$xmlrpcResp->faultString(),
                \Innomatic\Logging\Logger::ERROR
            );
        } else $this->log->logEvent(
            ['root' => ''],
            'appcentral-client.appcentral-client.appcentralremoteserver.retrieveapplication',
            'Invalid response from server',
            \Innomatic\Logging\Logger::ERROR
        );

        return $result;
    }
}
