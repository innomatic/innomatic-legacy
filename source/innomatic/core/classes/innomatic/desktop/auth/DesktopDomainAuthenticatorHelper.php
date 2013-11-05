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

require_once('innomatic/desktop/auth/DesktopAuthenticatorHelper.php');

/**
 * @package Desktop
 */
class DesktopDomainAuthenticatorHelper implements DesktopAuthenticatorHelper
{
    public function authenticate()
    {
        require_once('innomatic/wui/Wui.php');
        require_once('innomatic/wui/dispatch/WuiDispatcher.php');
        require_once('innomatic/desktop/controller/DesktopFrontController.php');
        $session = DesktopFrontController::instance('desktopfrontcontroller')->session;

        if (isset(Wui::instance('wui')->parameters['wui']['login'])) {
            $loginDispatcher = new WuiDispatcher('login');
            $loginDispatcher->addEvent('logout', 'login_logout');
            $loginDispatcher->addEvent('login', 'login_login');
            $loginDispatcher->Dispatch();
        }

        if (InnomaticContainer::instance('innomaticcontainer')->getConfig()->value('SecurityOnlyHttpsDomainAccessAllowed') == '1') {
            if (!isset($_SERVER['HTTPS']) or ($_SERVER['HTTPS'] != 'on')) {
                self::doAuth(true, 'only_https_allowed');
            }
        }

        require_once('innomatic/desktop/controller/DesktopFrontController.php');
        if (!DesktopFrontController::instance('desktopfrontcontroller')->session->isValid('INNOMATIC_AUTH_USER')) {
            self::doAuth();
        }

        require_once('innomatic/domain/user/User.php');
        $domainsquery = InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->execute('SELECT id FROM domains WHERE domainid='.InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->formatText(User::extractDomainID($session->get('INNOMATIC_AUTH_USER'))));
        if ($domainsquery->getNumberRows() == 0) {
            self::doAuth();
        } else {
            $domainsquery->free();
            InnomaticContainer::instance('innomaticcontainer')->startDomain(User::extractDomainID($session->get('INNOMATIC_AUTH_USER')), $session->get('INNOMATIC_AUTH_USER'));
        }

        if ($session->isValid('domain_login_attempts')) {
            $session->remove('domain_login_attempts');
        }

        // Check if the domain is enabled
        //
        if (InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->domaindata['domainactive'] != InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->fmttrue) {
            self::doAuth(true, 'domaindisabled');
        }
        
        return true;
    }

    public static function doAuth($wrong = false, $reason = '')
    {
        require_once('innomatic/wui/Wui.php');
        require_once('innomatic/locale/LocaleCatalog.php');
        $innomaticLocale = new LocaleCatalog('innomatic::authentication', InnomaticContainer::instance('innomaticcontainer')->getLanguage());

        $innomatic = InnomaticContainer::instance('innomaticcontainer');

        $wui = Wui::instance('wui');
        $wui->loadWidget('button');
        $wui->loadWidget('formarg');
        $wui->loadWidget('form');
        $wui->loadWidget('grid');
        $wui->loadWidget('horizbar');
        $wui->loadWidget('horizframe');
        $wui->loadWidget('horizgroup');
        $wui->loadWidget('image');
        $wui->loadWidget('label');
        $wui->loadWidget('link');
        $wui->loadWidget('page');
        $wui->loadWidget('sessionkey');
        $wui->loadWidget('statusbar');
        $wui->loadWidget('string');
        $wui->loadWidget('submit');
        $wui->loadWidget('titlebar');
        $wui->loadWidget('vertframe');
        $wui->loadWidget('vertgroup');

        $wuiPage = new WuiPage('loginpage', array('title' => $innomaticLocale->getStr('desktoplogin'), 'border' => 'false', 'align' => 'center', 'valign' => 'middle'));
        $wuiTopGroup = new WuiVertGroup('topgroup', array('align' => 'center', 'groupalign' => 'center', 'groupvalign' => 'middle', 'height' => '100%', 'width' => '0%'));
        $wuiMainGroup = new WuiVertGroup('maingroup', array('align' => 'center'));
        $wuiTitleBar = new WuiTitleBar('titlebar', array('title' => $innomaticLocale->getStr('desktoplogin')));
        $wuiMainBFrame = new WuiVertFrame('vframe', array('align' => 'center'));
        $wuiMainFrame = new WuiHorizGroup('horizframe');
        $wuiMainStatus = new WuiStatusBar('mainstatusbar');

        // Main frame
        //
        $wuiGrid = new WuiGrid('grid', array('rows' => '2', 'cols' => '2'));

        $wuiGrid->addChild(new WuiLabel('usernamelabel', array('label' => $innomaticLocale->getStr('username'))), 0, 0);
        $wuiGrid->addChild(new WuiString('username', array('disp' => 'login')), 0, 1);

        $wuiGrid->addChild(new WuiLabel('passwordlabel', array('label' => $innomaticLocale->getStr('password'))), 1, 0);
        $wuiGrid->addChild(new WuiString('password', array('disp' => 'login', 'password' => 'true')), 1, 1);

        $wuiVGroup = new WuiVertGroup('vertgroup', array('align' => 'center'));
        //$wui_vgroup->addChild( new WuiLabel( 'titlelabel', array( 'label' => $innomatic_locale->getStr( 'rootlogin' ) ) ) );
        $wuiVGroup->addChild($wuiGrid);
        $wuiVGroup->addChild(new WuiSubmit('submit', array('caption' => $innomaticLocale->getStr('enter'))));

        require_once('innomatic/wui/dispatch/WuiEvent.php');
        require_once('innomatic/wui/dispatch/WuiEventsCall.php');
        $formEventsCall = new WuiEventsCall();
        $formEventsCall->addEvent(new WuiEvent('login', 'login', ''));
        $formEventsCall->addEvent(new WuiEvent('view', 'default', ''));

        $wuiForm = new WuiForm('form', array('action' => $formEventsCall->getEventsCallString()));

        $wuiHGroup = new WuiHorizGroup('horizgroup', array('align' => 'middle'));
        $wuiHGroup->addChild(new WuiButton('password', array('themeimage' => 'keyhole', 'themeimagetype' => 'big', 'action' => InnomaticContainer::instance('innomaticcontainer')->getBaseUrl().'/', 'highlight' => false)));
        $wuiHGroup->addChild($wuiVGroup);

        $wuiForm->addChild($wuiHGroup);
        $wuiMainFrame->addChild($wuiForm);

        // Wrong account check
        //
        require_once('innomatic/desktop/controller/DesktopFrontController.php');
        $session = DesktopFrontController::instance('desktopfrontcontroller')->session;
        
        if ($wrong) {
            if (InnomaticContainer::instance('innomaticcontainer')->getConfig()->value('SecurityAlertOnWrongLocalUserLogin') == '1') {
                require_once('innomatic/security/SecurityManager.php');
                $loginDispatcher = new WuiDispatcher('login');
                $eventData = $loginDispatcher->getEventData();

                $innomaticSecurity = new SecurityManager();
                $innomaticSecurity->SendAlert('Wrong user local login for user '.$eventData['username'].' from remote address '.$_SERVER['REMOTE_ADDR']);
                $innomaticSecurity->LogFailedAccess($eventData['username'], false, $_SERVER['REMOTE_ADDR']);

                unset($innomaticSecurity);
            }

            $sleepTime = InnomaticContainer::instance('innomaticcontainer')->getConfig()->value('WrongLoginDelay');
            if (!strlen($sleepTime))
            $sleepTime = 1;
            $maxAttempts = InnomaticContainer::instance('innomaticcontainer')->getConfig()->value('MaxWrongLogins');
            if (!strlen($maxAttempts))
            $maxAttempts = 3;

            sleep($sleepTime);

            if ($session->isValid('domain_login_attempts')) {
                $session->put('domain_login_attempts', $session->get('domain_login_attempts') + 1);
                if ($session->get('domain_login_attempts') >= $maxAttempts)
                InnomaticContainer::instance('innomaticcontainer')->abort($innomaticLocale->getStr('wrongpwd'));
            } else {
                $session->put('domain_login_attempts', 1);
            }

            if ($reason)
            $wuiMainStatus->mArgs['status'] = $innomaticLocale->getStr($reason);
            else
            $wuiMainStatus->mArgs['status'] = $innomaticLocale->getStr('wrongpwd');
        } else {
            $session->put('domain_login_attempts', 0);
        }

        // Page render
        //
        $wuiMainGroup->addChild($wuiTitleBar);
        //$wui_maingroup->addChild( new WuiButton( 'innomaticlogo', array( 'image' => InnomaticContainer::instance('innomaticcontainer')->getBaseUrl(false).'/shared/styles/cleantheme/innomatic_big_asp.png', 'action' => InnomaticContainer::instance('innomaticcontainer')->getBaseUrl().'/' ) ) );
        $wuiMainBFrame->addChild($wuiMainFrame);
        $wuiMainBFrame->addChild(new WuiHorizBar('hb'));
        $wuiMainBFrame->addChild(new WuiLink('copyright', array('label' => $innomaticLocale->getStr('auth_copyright.label'), 'link' => 'http://www.innoteam.it/', 'target' => '_blank')));
        $wuiMainGroup->addChild($wuiMainBFrame);
        $wuiMainGroup->addChild($wuiMainStatus);
        $wuiTopGroup->addChild($wuiMainGroup);
        $wuiPage->addChild($wuiTopGroup);
        $wui->addChild($wuiPage);
        $wui->render();

        InnomaticContainer::instance('innomaticcontainer')->halt();
    }
    
    public function authorize()
    {
    }
}


function login_login($eventData)
{
	$username = $eventData['username'];
    require_once('innomatic/domain/Domain.php');
    require_once('innomatic/domain/user/User.php');
    $domainId = User::extractDomainID($username);
    
    // Checks it it can find the domain by hostname 
    if (!strlen($domainId)) {
    	$domainId = Domain::getDomainByHostname();
    	if (strlen($domainId)) {
    		$username .= '@'.$domainId;
    	}
    }
    
    // If no domain is found when in SAAS edition, it must be reauth without
    // checking database, since no Domain can be accessed.
    if (!strlen($domainId)) {
        DesktopDomainAuthenticatorHelper::doAuth(true);
    }
    $tmpDomain = new Domain(
        InnomaticContainer::instance('innomaticcontainer')->getDataAccess(),
        $domainId,
        null
    );
    $domainDA = $tmpDomain->getDataAccess();
    $userQuery = $domainDA->execute(
        'SELECT * FROM domain_users WHERE username='
        . $domainDA->formatText($username)
        . ' AND password='
        . $domainDA->formatText(md5($eventData['password']))
    );

    if ($userQuery->getNumberRows()) {
        require_once('innomatic/desktop/controller/DesktopFrontController.php');
        DesktopFrontController::instance(
            'desktopfrontcontroller'
        )->session->put(
            'INNOMATIC_AUTH_USER',
            $username
        );

        require_once('innomatic/security/SecurityManager.php');

        $innomaticSecurity = new SecurityManager();
        $innomaticSecurity->LogAccess(
            $username,
            false,
            false,
            $_SERVER['REMOTE_ADDR']
        );

        unset($innomaticSecurity);
    } else {
        DesktopDomainAuthenticatorHelper::doAuth(true);
    }

    //        unset( $INNOMATIC_ROOT_AUTH_USER );
}

function login_logout($eventData)
{
    require_once('innomatic/security/SecurityManager.php');
    require_once('innomatic/desktop/controller/DesktopFrontController.php');
    
    DesktopFrontController::instance(
        'desktopfrontcontroller'
    )->session->put(
        'INNOMATIC_AUTH_USER',
        $eventData['username']
    );

    $innomaticSecurity = new SecurityManager();
    $innomaticSecurity->LogAccess(
        DesktopFrontController::instance(
            'desktopfrontcontroller'
        )->session->get(
            'INNOMATIC_AUTH_USER'
        ),
        true,
        false,
        $_SERVER['REMOTE_ADDR']
    );

    DesktopFrontController::instance('desktopfrontcontroller')->session->remove(
        'INNOMATIC_AUTH_USER'
    );
    unset($innomaticSecurity);

    DesktopDomainAuthenticatorHelper::doAuth();
}
