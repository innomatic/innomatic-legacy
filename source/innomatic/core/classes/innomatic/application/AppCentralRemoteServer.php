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
 * This class handles operations with remote AppCentral servers.
 *
 * An AppCentral server can host multiple repositories (at least one).
 *
 * AppCentral was once called AmpCentral.
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
     * @param integer $repId AppCentral server id.
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

    /* protected setClient() {{{ */
    /**
     * Internal method that setups the AppCentral client.
     *
     * @access protected
     * @return void
     */
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
    /* }}} */

    /* public getAccount() {{{ */
    /**
     * Gets webservices account for the current server.
     *
     * @access public
     * @return \Innomatic\Webservices\WebServicesAccount
     */
    public function getAccount()
    {
        return $this->account;
    }
    /* }}} */

    /* public add($accountId) {{{ */
    /**
     * Adds a new AppCentral remote server.
     *
     * @param \Innomatic\Webservices\WebServicesAccount $accountId Innomatic WebServices account pointing to an AppCentral server.
     * @access public
     * @return void
     */
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
    /* }}} */

    /* public remove() {{{ */
    /**
     * Removes the current AppCentral server from the servers list.
     *
     * @access public
     * @return void
     */
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
    /* }}} */

    /* public listAvailableRepositories($refresh = false) {{{ */
    /**
     * List all the available repositories for the current AppCentral server.
     *
     * Since an AppCentral server can host multiple repositories, this method
     * has been made available in order to retrieve a complete list of the
     * server repositories.
     *
     * @param bool $refresh Set to true if a cache refresh is requested.
     * @access public
     * @return array
     */
    public function listAvailableRepositories($refresh = false)
    {
        $cachedItem = new \Innomatic\Datatransfer\Cache\CachedItem(
            $this->dataAccess,
            'appcentral-client',
            'repositories-'.$this->id
        );

        if (!$refresh) {
            // Retrieve the repositories list from the cache.
            $cacheContent = $cachedItem->retrieve();

            if ($cacheContent != false) {
                return unserialize($cacheContent);
            }
        }

        // Retrieve the list of the available repositories from the AppCentral server.
        $xmlrpcMessage = new \Innomatic\Webservices\Xmlrpc\XmlRpcMsg(
            'appcentral-server.list_available_repositories'
        );
        $xmlrpcResp = $this->client->send($xmlrpcMessage);

        if ($xmlrpcResp) {
            if (!$xmlrpcResp->faultCode()) {
                $xv = \Innomatic\Webservices\Xmlrpc\php_xmlrpc_decode($xmlrpcResp->value());

                if (is_array($xv)) {
                    // Store the repositories list in the cache.
                    $cachedItem->store(serialize($xv));

                    // Set the result.
                    return $xv;
                } else $this->log->logEvent(
                    ['root' => ''],
                    'innomatic.appcentralremoteserver.listavailablerepositories',
                    'Not an array from server',
                    \Innomatic\Logging\Logger::ERROR
                );
            } else $this->log->logEvent(
                ['root' => ''],
                'innomatic.appcentralremoteserver.listavailablerepositories',
                'Error in response from server: '.$xmlrpcResp->faultString(),
                \Innomatic\Logging\Logger::ERROR
            );
        } else $this->log->logEvent(
            ['root' => ''],
            'innomatic.appcentralremoteserver.listavailablerepositories',
            'Invalid response from server',
            \Innomatic\Logging\Logger::ERROR
        );

        return false;
    }
    /* }}} */

    /* public listAvailableApplications($repId, $refresh = false) {{{ */
    /**
     * Lists the available applications in an AppCentral server repository.
     *
     * @param integer $repId Repository id.
     * @param bool $refresh Set to true when a cache refresh is requested.
     * @access public
     * @return array
     */
    public function listAvailableApplications($repId, $refresh = false)
    {
        $cachedItem = new \Innomatic\Datatransfer\Cache\CachedItem(
            $this->dataAccess,
            'appcentral-client',
            'repository_applications-'.$this->id.'-'.$repId
        );

        if (!$refresh) {
            $cacheContent = $cachedItem->retrieve();

            if ($cacheContent != false) {
                $goon = false;
                return unserialize($cacheContent);
            }
        }

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

                    return $xv;
                } else $this->log->logEvent(
                    ['root' => ''],
                    'innomatic.appcentralremoteserver.listavailableapplications',
                    'Not an array from server',
                    \Innomatic\Logging\Logger::ERROR
                );
            } else $this->log->logEvent(
                ['root' => ''],
                'innomatic.appcentralremoteserver.listavailableapplications',
                'Error in response from server: '.$xmlrpcResp->faultString(),
                \Innomatic\Logging\Logger::ERROR
            );
        } else $this->log->logEvent(
            ['root' => ''],
            'innomatic.appcentralremoteserver.listavailableapplications',
            'Invalid response from server',
            \Innomatic\Logging\Logger::ERROR
        );

        return false;
    }
    /* }}} */

    /* public listAvailableApplicationVersions($repId, $applicationId, $refresh = false) {{{ */
    /**
     * Lists the available application versions.
     *
     * @param integer $repId Repository id.
     * @param integer $applicationId Application id.
     * @param bool $refresh Set to true if a cache refresh is requested.
     * @access public
     * @return array
     */
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
                        'innomatic.appcentralremoteserver.listavailableapplications',
                        'Not an array from server',
                        \Innomatic\Logging\Logger::ERROR
                    );
                } else $this->log->logEvent(
                    ['root' => ''],
                    'innomatic.appcentralremoteserver.listavailableapplications',
                    'Error in response from server: '.$xmlrpcResp->faultString(),
                    \Innomatic\Logging\Logger::ERROR
                );
            } else $this->log->logEvent(
                ['root' => ''],
                'innomatic.appcentralremoteserver.listavailableapplications',
                'Invalid response from server',
                \Innomatic\Logging\Logger::ERROR
            );
        }

        return $result;
    }
    /* }}} */

    /* public retrieveApplication($repId, $applicationId, $applicationVersion = '') {{{ */
    /**
     * Retrieves an application archive from the AppCentral server and installs it.
     *
     * @param int $repId Repository id
     * @param int $applicationId Application id.
     * @param string $applicationVersion Required application version.
     * @access public
     * @return void
     */
    public function retrieveApplication($repId, $applicationId, $applicationVersion = '')
    {
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

                    // Install the application.
                    $tmpApplication = new \Innomatic\Application\Application($this->dataAccess, '');
                    if ($tmpApplication->install($tmpFilename)) {
                        return true;
                    }
                }
            } else $this->log->logEvent(
                ['root' => ''],
                'innomatic.appcentralremoteserver.retrieveapplication',
                'Error in response from server: '.$xmlrpcResp->faultString(),
                \Innomatic\Logging\Logger::ERROR
            );
        } else $this->log->logEvent(
            ['root' => ''],
            'innomatic.appcentralremoteserver.retrieveapplication',
            'Invalid response from server',
            \Innomatic\Logging\Logger::ERROR
        );

        return false;
    }
    /* }}} */
}
