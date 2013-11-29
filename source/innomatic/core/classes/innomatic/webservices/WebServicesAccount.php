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


require_once('innomatic/process/Hook.php');

/*!
 @class WebServicesAccount

 @abstract Handles web services accounts.
 */
class WebServicesAccount
{
    /*! @var mLog Logger class - Innomatic log handler. */
    public $mLog;
    /*! @var mLog Logger class - Web services procedures log handler. */
    public $mWebServicesLog;
    /*! @var mrRootdb DataAccess class - Innomatic database handler. */
    public $mrRootDb;
    /*! @var mId integer - Account id. */
    public $mId;
    /*! @var mName string - Account name. */
    public $mName;
    /*! @var mHost string - Account host. */
    public $mHost;
    /*! @var mPort string - Account port. */
    public $mPort;
    /*! @var mPath string - Account path. */
    public $mPath;
    /*! @var mUsername string - Account username. */
    public $mUsername;
    /*! @var mPassword string - Account password. */
    public $mPassword;
    /*! @var mProxy string - Optional proxy hostname. */
    public $mProxy;
    /*! @var mProxyPort string - Optional proxy port. */
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

    /*!
     @function WebServicesAccount

     @abstract Class constructor.

     @discussion Class constructor.

     @param rrootDb DataAccess class - Innomatic database handler.
     @param id integer - Account id.
     */
    public function WebServicesAccount(&$rrootDb, $id = '')
    {
        $this->mLog = InnomaticContainer::instance('innomaticcontainer')->getLogger();
        $this->mWebServicesLog = new \Innomatic\Logging\Logger( InnomaticContainer::instance('innomaticcontainer')->getHome().'core/log/webservices.log' );

        $this->mId = $id;

        if ( is_object( $rrootDb ) ) $this->mrRootDb = &$rrootDb;
        else $this->mLog->logEvent( 'innomatic.webservicesaccount',
                                   'Invalid Innomatic database handler', \Innomatic\Logging\Logger::ERROR );

        if ( $this->mId ) {
            $acc_query = &$this->mrRootDb->execute( 'SELECT * '.
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

    /*!
     @function Create

     @abstract Creates a new account.

     @discussion Creates a new account.

     @param name string - Account name.
     @param host string - Account host.
     @param port string - Account port.
     @param path string - Account path.
     @param username - Account username.
     @param password - Account password.

     @result True if the account has been created.
     */
    public function Create(
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

        $hook = new Hook( $this->mrRootDb, 'innomatic', 'webservicesaccount.create' );
        if ( $hook->CallHooks( 'calltime', $this, array( 'name' => $name, 'host' => $host, 'port' => $port, 'path' => $path, 'username' => $username, 'password' => $password ) ) == Hook::RESULT_OK ) {
            if ( strlen( $name ) ) {
                $acc_seq = $this->mrRootDb->getNextSequenceValue( 'webservices_accounts_id_seq' );

                $result = &$this->mrRootDb->execute( 'INSERT INTO webservices_accounts '.
                                                    'VALUES ('.
                                                    $acc_seq.','.
                                                    $this->mrRootDb->formatText( $name ).','.
                                                    $this->mrRootDb->formatText( $host ).','.
                                                    $this->mrRootDb->formatText( $path ).','.
                                                    $this->mrRootDb->formatText( $port ).','.
                                                    $this->mrRootDb->formatText( $username ).','.
                                                    $this->mrRootDb->formatText( $password ).','.
                                                    $this->mrRootDb->formatText( $proxy ).','.
                                                    $this->mrRootDb->formatText( $proxyPort ).')' );

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
                        $hook->CallHooks(
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
                            ) != Hook::RESULT_OK
                        ) $result = false;
                } else $result = WebServicesAccount::CREATE_UNABLE_TO_INSERT_ACCOUNT;
            } else {
                $result = WebServicesAccount::CREATE_EMPTY_ACCOUNT_NAME;
            }
        }

        return $result;
    }

    /*!
     @function Remove

     @abstract Removes the account.

     @discussion Removes the account.

     @result True if the account has been removed.
     */
    public function Remove()
    {
        $result = false;

        $hook = new Hook( $this->mrRootDb, 'innomatic', 'webservicesaccount.remove' );
        if ( $hook->CallHooks( 'calltime', $this, array( 'id' => $this->mId ) ) == Hook::RESULT_OK ) {
            if ( $this->mId ) {
                $result = &$this->mrRootDb->execute( 'DELETE FROM webservices_accounts WHERE id='.(int)$this->mId );

                if ( $result ) {
                    $this->mLog->logEvent( 'Innomatic',
                                           'Removed web services profile account', \Innomatic\Logging\Logger::NOTICE );

                    if ( $hook->CallHooks( 'accountremoved', $this, array( 'id' => $this->mId ) ) != Hook::RESULT_OK ) $result = false;
                    $this->mId = '';
                } else $result = WebServicesAccount::REMOVE_UNABLE_TO_REMOVE_ACCOUNT;
            } else $result = WebServicesAccount::REMOVE_EMPTY_ACCOUNT_ID;
        }

        return $result;
    }

    /*!
     @function Update

     @abstract Updates the account.
     */
    public function Update(
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

        $hook = new Hook( $this->mrRootDb, 'innomatic', 'webservicesaccount.update' );
        if ( $hook->CallHooks( 'calltime', $this, array( 'name' => $name, 'host' => $host, 'port' => $port, 'path' => $path, 'username' => $username, 'password' => $password ) ) == Hook::RESULT_OK ) {
            if ( $this->mId ) {
                if ( strlen( $name ) ) {
                    $result = &$this->mrRootDb->execute( 'UPDATE webservices_accounts '.
                                                        'SET '.
                                                        'name='.$this->mrRootDb->formatText( $name ).','.
                                                        'host='.$this->mrRootDb->formatText( $host ).','.
                                                        'path='.$this->mrRootDb->formatText( $path ).','.
                                                        'port='.$this->mrRootDb->formatText( $port ).','.
                                                        'username='.$this->mrRootDb->formatText( $username ).','.
                                                        'password='.$this->mrRootDb->formatText( $password ).','.
                                                        'proxy='.$this->mrRootDb->formatText( $proxy ).','.
                                                        'proxyport='.$this->mrRootDb->formatText( $proxyPort ).' '.
                                                        'WHERE id='.(int)$this->mId );

                    if ( $result ) {
                        if ( $hook->CallHooks( 'accountudpated', $this, array( 'name' => $name, 'host' => $host, 'port' => $port, 'path' => $path, 'username' => $username, 'password' => $password, 'id' => $this->mId ) ) != Hook::RESULT_OK ) $result = false;
                    } else $result = WebServicesAccount::UPDATE_UNABLE_TO_UPDATE_ACCOUNT;
                } else {
                    $result = WebServicesAccount::UPDATE_EMPTY_ACCOUNT_NAME;
                }
            } else $result = WebServicesAccount::REMOVE_EMPTY_ACCOUNT_ID;
        }

        return $result;
    }
}
