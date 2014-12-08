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
 * @since      Class available since Release 5.0
*/
namespace Innomatic\Webservices;

/**
 * This class handles accounts to external web services.
 *
 */
class WebServicesAccount
{
    /**
     * Innomatic log handler.
     *
     * @var \Innomatic\Logging\Logger
     * @access public
     */
    public $mLog;
    /**
     * Web services log handler.
     *
     * @var \Innomatic\Logging\Logger
     * @access public
     */
    public $mWebServicesLog;
    /**
     * Innomatic root data access handler.
     *
     * @var \Innomatic\Dataaccess\DataAccess
     * @access public
     */
    public $dataAccess;
    /**
     * Account id.
     *
     * @var integer
     * @access public
     */
    public $mId;
    /**
     * Account name.
     *
     * @var string
     * @access public
     */
    public $mName;
    /**
     * Account server hostname.
     *
     * @var string
     * @access public
     */
    public $mHost;
    /**
     * Account server port.
     *
     * @var integer
     * @access public
     */
    public $mPort;
    /**
     * Account server receiver path.
     *
     * @var string
     * @access public
     */
    public $mPath;
    /**
     * Account username.
     *
     * @var string
     * @access public
     */
    public $mUsername;
    /**
     * Account password.
     *
     * @var string
     * @access public
     */
    public $mPassword;
    /**
     * Account optional proxy server hostname.
     *
     * @var string
     * @access public
     */
    public $mProxy;
    /**
     * Account optional proxy server port.
     *
     * @var integer
     * @access public
     */
    public $mProxyPort;
    // WebServicesAccount::Create
    //
    const CREATE_UNABLE_TO_INSERT_ACCOUNT =  '-1'; // Unable to insert account into webservices_accounts table.
    const CREATE_EMPTY_ACCOUNT_NAME = '-2'; // Empty account name.

    // WebServicesAccount::Remove
    //
    const REMOVE_UNABLE_TO_REMOVE_ACCOUNT = '-1'; // Unable to remove account from webservices_accounts table.
    const REMOVE_EMPTY_ACCOUNT_ID = '-2'; // Empty account id.

    // WebServicesAccount::Update
    //
    const UDATE_UNABLE_TO_UPDATE_ACCOUNT = '-1'; // Unable to update account int webservices_accounts table.
    const UPDATE_EMPTY_ACCOUNT_ID = '-2'; // Empty account id.
    const UPDATE_EMPTY_ACCOUNT_NAME = '-3'; // Empty account name.

    /* public __construct($rrootDb, $id = '') {{{ */
    /**
     * Class constructor.
     *
     * @param integer $id Account id
     * @access public
     * @return void
     */
    public function __construct($id = '')
    {
        $container             = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');
        $this->dataAccess      = $container->getDataAccess();
        $this->mLog            = $container->getLogger();
        $this->mWebServicesLog = new \Innomatic\Logging\Logger($container->getHome().'core/log/webservices.log');

        $this->mId = $id;

        if ( is_object( $rrootDb ) ) $this->dataAccess = $rrootDb;
        else $this->mLog->logEvent( 'innomatic.webservicesaccount',
                                   'Invalid Innomatic database handler', \Innomatic\Logging\Logger::ERROR );

        if ( $this->mId ) {
            $acc_query = $this->dataAccess->execute( 'SELECT * '.
                                                  'FROM webservices_accounts '.
                                                  'WHERE id='.(int)$this->mId );

            if ( $acc_query->getNumberRows() ) {
                $acc_data = $acc_query->getFields();

                $this->mName        = $acc_data['name'];
                $this->mHost        = $acc_data['host'];
                $this->mPort        = $acc_data['port'];
                $this->mPath        = $acc_data['path'];
                $this->mUsername    = $acc_data['username'];
                $this->mPassword    = $acc_data['password'];
                $this->mProxy       = $acc_data['proxy'];
                $this->mProxyPort   = $acc_data['proxyport'];
            } else $this->mLog->logEvent( 'innomatic.webservicesaccount',
                                       'Invalid account id', \Innomatic\Logging\Logger::ERROR );
        }
    }
    /* }}} */

    /**
     * Creates a new account.
     */
    public function create(
        $name,
        $host = 'localhost',
        $port = '80',
        $path = '',
        $username = '',
        $password = '',
        $proxy = '',
        $proxyPort = ''
        )
    {
        $result = false;

        $hook = new \Innomatic\Process\Hook( $this->dataAccess, 'innomatic', 'webservicesaccount.create' );
        if ( $hook->callHooks( 'calltime', $this, array( 'name' => $name, 'host' => $host, 'port' => $port, 'path' => $path, 'username' => $username, 'password' => $password ) ) == \Innomatic\Process\Hook::RESULT_OK ) {
            if ( strlen( $name ) ) {
                $acc_seq = $this->dataAccess->getNextSequenceValue( 'webservices_accounts_id_seq' );

                $result = $this->dataAccess->execute(
                    'INSERT INTO webservices_accounts '.
                    'VALUES ('.
                    $acc_seq.','.
                    $this->dataAccess->formatText( $name ).','.
                    $this->dataAccess->formatText( $host ).','.
                    $this->dataAccess->formatText( $path ).','.
                    $this->dataAccess->formatText( $port ).','.
                    $this->dataAccess->formatText( $username ).','.
                    $this->dataAccess->formatText( $password ).','.
                    $this->dataAccess->formatText( $proxy ).','.
                    $this->dataAccess->formatText( $proxyPort ).')'
                );

                if ( $result ) {
                    $this->mLog->logEvent(
                        'Innomatic',
                        'Created new web services profile account',
                        \Innomatic\Logging\Logger::NOTICE
                        );

                    $this->mId = $acc_seq;
                    $this->mName = $name;
                    $this->mHost = $host;
                    $this->mPath = $path;
                    $this->mPort = $port;
                    $this->mUsername = $username;
                    $this->mPassword = $password;
                    $this->mProxy = $proxy;
                    $this->mProxyPort = $proxyPort;

                    if (
                        $hook->callHooks(
                            'accountcreated',
                            $this,
                            array(
                                'name' => $name,
                                'host' => $host,
                                'port' => $port,
                                'path' => $path,
                                'username' => $username,
                                'password' => $password,
                                'proxy' => $proxy,
                                'proxyport' => $proxyPort,
                                'id' => $this->mId
                                )
                            ) != \Innomatic\Process\Hook::RESULT_OK
                        ) $result = false;
                } else $result = WebServicesAccount::CREATE_UNABLE_TO_INSERT_ACCOUNT;
            } else {
                $result = WebServicesAccount::CREATE_EMPTY_ACCOUNT_NAME;
            }
        }

        return $result;
    }

    /* public remove() {{{ */
    /**
     * Removes the account.
     *
     * @access public
     * @return bool True if the account has been removed.
     */
    public function remove()
    {
        $result = false;

        $hook = new \Innomatic\Process\Hook( $this->dataAccess, 'innomatic', 'webservicesaccount.remove' );
        if ( $hook->callHooks( 'calltime', $this, array( 'id' => $this->mId ) ) == \Innomatic\Process\Hook::RESULT_OK ) {
            if ( $this->mId ) {
                $result = $this->dataAccess->execute( 'DELETE FROM webservices_accounts WHERE id='.(int)$this->mId );

                if ( $result ) {
                    $this->mLog->logEvent( 'Innomatic',
                                           'Removed web services profile account', \Innomatic\Logging\Logger::NOTICE );

                    if ( $hook->callHooks( 'accountremoved', $this, array( 'id' => $this->mId ) ) != \Innomatic\Process\Hook::RESULT_OK ) $result = false;
                    $this->mId = '';
                } else $result = WebServicesAccount::REMOVE_UNABLE_TO_REMOVE_ACCOUNT;
            } else $result = WebServicesAccount::REMOVE_EMPTY_ACCOUNT_ID;
        }

        return $result;
    }
    /* }}} */

    /**
     * Updates the account information.
     */
    public function update(
        $name,
        $host = 'localhost',
        $port = '80',
        $path = '',
        $username = '',
        $password = '',
        $proxy = '',
        $proxyPort = ''
        )
    {
        $result = false;

        $hook = new \Innomatic\Process\Hook( $this->dataAccess, 'innomatic', 'webservicesaccount.update' );
        if ( $hook->callHooks( 'calltime', $this, array( 'name' => $name, 'host' => $host, 'port' => $port, 'path' => $path, 'username' => $username, 'password' => $password ) ) == \Innomatic\Process\Hook::RESULT_OK ) {
            if ( $this->mId ) {
                if ( strlen( $name ) ) {
                    $result = $this->dataAccess->execute(
                        'UPDATE webservices_accounts '.
                        'SET '.
                        'name='.$this->dataAccess->formatText( $name ).','.
                        'host='.$this->dataAccess->formatText( $host ).','.
                        'path='.$this->dataAccess->formatText( $path ).','.
                        'port='.$this->dataAccess->formatText( $port ).','.
                        'username='.$this->dataAccess->formatText( $username ).','.
                        'password='.$this->dataAccess->formatText( $password ).','.
                        'proxy='.$this->dataAccess->formatText( $proxy ).','.
                        'proxyport='.$this->dataAccess->formatText( $proxyPort ).' '.
                        'WHERE id='.(int)$this->mId
                    );

                    if ( $result ) {
                        if ( $hook->callHooks( 'accountudpated', $this, array( 'name' => $name, 'host' => $host, 'port' => $port, 'path' => $path, 'username' => $username, 'password' => $password, 'id' => $this->mId ) ) != \Innomatic\Process\Hook::RESULT_OK ) $result = false;
                    } else $result = WebServicesAccount::UPDATE_UNABLE_TO_UPDATE_ACCOUNT;
                } else {
                    $result = WebServicesAccount::UPDATE_EMPTY_ACCOUNT_NAME;
                }
            } else $result = WebServicesAccount::REMOVE_EMPTY_ACCOUNT_ID;
        }

        return $result;
    }

    /* public getId() {{{ */
    /**
     * Gets account id.
     *
     * @since 6.5.0 introduced
     * @access public
     * @return integer
     */
    public function getId()
    {
        return $this->mId;
    }
    /* }}} */

    /* public getName() {{{ */
    /**
     * Gets account name.
     *
     * @since 6.5.0 introduced
     * @access public
     * @return string
     */
    public function getName()
    {
        return $this->mName;
    }
    /* }}} */

    /* public getHost() {{{ */
    /**
     * Gets account server hostname.
     *
     * @since 6.5.0 introduced
     * @access public
     * @return string
     */
    public function getHost()
    {
        return $this->mHost;
    }
    /* }}} */

    /* public getPort() {{{ */
    /**
     * Gets account server port.
     *
     * @since 6.5.0 introduced
     * @access public
     * @return integer
     */
    public function getPort()
    {
        return $this->mPort;
    }
    /* }}} */

    /* public getPath() {{{ */
    /**
     * Gets account server receiver path.
     *
     * @since 6.5.0 introduced
     * @access public
     * @return string
     */
    public function getPath()
    {
        return $this->mPath;
    }
    /* }}} */

    /* public getUsername() {{{ */
    /**
     * Gets account username.
     *
     * @since 6.5.0 introduced
     * @access public
     * @return string
     */
    public function getUsername()
    {
        return $this->mUsername;
    }
    /* }}} */

    /* public getPassword() {{{ */
    /**
     * Gets account password.
     *
     * @since 6.5.0 introduced
     * @access public
     * @return string
     */
    public function getPassword()
    {
        return $this->mPassword;
    }
    /* }}} */

    /* public getProxy() {{{ */
    /**
     * Gets account proxy server hostname.
     *
     * @since 6.5.0 introduced
     * @access public
     * @return string
     */
    public function getProxy()
    {
        return $this->mProxy;
    }
    /* }}} */

    /* public getProxyPort() {{{ */
    /**
     * Gets account proxy server port.
     *
     * @since 6.5.0 introduced
     * @access public
     * @return integer
     */
    public function getProxyPort()
    {
        return $this->mProxyPort;
    }
    /* }}} */
}
