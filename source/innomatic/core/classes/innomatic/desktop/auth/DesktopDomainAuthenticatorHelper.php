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
namespace Innomatic\Desktop\Auth;

use \Innomatic\Core\InnomaticContainer;
use \Innomatic\Wui\Dispatch;

/**
 * @package Desktop
 */
class DesktopDomainAuthenticatorHelper implements \Innomatic\Desktop\Auth\DesktopAuthenticatorHelper
{
    public function authenticate()
    {
        $session = \Innomatic\Desktop\Controller\DesktopFrontController::instance('\Innomatic\Desktop\Controller\DesktopFrontController')->session;

        if (isset(\Innomatic\Wui\Wui::instance('\Innomatic\Wui\Wui')->parameters['wui']['login'])) {
            $loginDispatcher = new \Innomatic\Wui\Dispatch\WuiDispatcher('login');
            $loginDispatcher->addEvent('logout', '\Innomatic\Desktop\Auth\login_logout');
            $loginDispatcher->addEvent('login', '\Innomatic\Desktop\Auth\login_login');
            $loginDispatcher->Dispatch();
        }

        if (\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getConfig()->value('SecurityOnlyHttpsDomainAccessAllowed') == '1') {
            if (!isset($_SERVER['HTTPS']) or ($_SERVER['HTTPS'] != 'on')) {
                self::doAuth(true, 'only_https_allowed');
            }
        }

        if (!\Innomatic\Desktop\Controller\DesktopFrontController::instance('\Innomatic\Desktop\Controller\DesktopFrontController')->session->isValid('INNOMATIC_AUTH_USER')) {
            self::doAuth();
        }

        $domainsquery = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->execute('SELECT id FROM domains WHERE domainid='.\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->formatText(\Innomatic\Domain\User\User::extractDomainID($session->get('INNOMATIC_AUTH_USER'))));
        if ($domainsquery->getNumberRows() == 0) {
            self::doAuth();
        } else {
            $domainsquery->free();
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->startDomain(\Innomatic\Domain\User\User::extractDomainID($session->get('INNOMATIC_AUTH_USER')), $session->get('INNOMATIC_AUTH_USER'));
        }

        if ($session->isValid('domain_login_attempts')) {
            $session->remove('domain_login_attempts');
        }

        // Check if the domain is enabled
        //
        if (\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->domaindata['domainactive'] != \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->fmttrue) {
            self::doAuth(true, 'domaindisabled');
        }

        return true;
    }

    public static function doAuth($wrong = false, $reason = '')
    {
        $innomaticLocale = new \Innomatic\Locale\LocaleCatalog('innomatic::authentication', \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLanguage());

        $innomatic = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');

        $wui = \Innomatic\Wui\Wui::instance('\Innomatic\Wui\Wui');
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
        $wuiTopGroup = new WuiVertgroup('topgroup', array('align' => 'center', 'groupalign' => 'center', 'groupvalign' => 'middle', 'height' => '100%', 'width' => '0%'));
        $wuiMainGroup = new WuiVertgroup('maingroup', array('align' => 'center'));
        $wuiTitleBar = new WuiTitleBar('titlebar', array('title' => $innomaticLocale->getStr('desktoplogin')));
        $wuiMainBFrame = new WuiVertframe('vframe', array('align' => 'center'));
        $wuiMainFrame = new WuiHorizgroup('horizframe');
        $wuiMainStatus = new WuiStatusBar('mainstatusbar');

        // Main frame
        //
        $wuiGrid = new WuiGrid('grid', array('rows' => '2', 'cols' => '2'));

        $wuiGrid->addChild(new WuiLabel('usernamelabel', array('label' => $innomaticLocale->getStr('username'))), 0, 0);
        $wuiGrid->addChild(new WuiString('username', array('disp' => 'login')), 0, 1);

        $wuiGrid->addChild(new WuiLabel('passwordlabel', array('label' => $innomaticLocale->getStr('password'))), 1, 0);
        $wuiGrid->addChild(new WuiString('password', array('disp' => 'login', 'password' => 'true')), 1, 1);

        $wuiVGroup = new WuiVertgroup('vertgroup', array('align' => 'center'));
        //$wui_vgroup->addChild( new WuiLabel( 'titlelabel', array( 'label' => $innomatic_locale->getStr( 'rootlogin' ) ) ) );
        $wuiVGroup->addChild($wuiGrid);
        $wuiVGroup->addChild(new WuiSubmit('submit', array('caption' => $innomaticLocale->getStr('enter'))));

        $formEventsCall = new WuiEventsCall();
        $formEventsCall->addEvent(new WuiEvent('login', 'login', ''));
        $formEventsCall->addEvent(new WuiEvent('view', 'default', ''));

        $wuiForm = new WuiForm('form', array('action' => $formEventsCall->getEventsCallString()));

        $wuiHGroup = new WuiHorizgroup('horizgroup', array('align' => 'middle'));
        $wuiHGroup->addChild(new WuiButton('password', array('themeimage' => 'keyhole', 'themeimagetype' => 'big', 'action' => \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getBaseUrl().'/', 'highlight' => false)));
        $wuiHGroup->addChild($wuiVGroup);

        $wuiForm->addChild($wuiHGroup);
        $wuiMainFrame->addChild($wuiForm);

        // Wrong account check
        //
        $session = \Innomatic\Desktop\Controller\DesktopFrontController::instance('\Innomatic\Desktop\Controller\DesktopFrontController')->session;

        if ($wrong) {
            if (\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getConfig()->value('SecurityAlertOnWrongLocalUserLogin') == '1') {
                $loginDispatcher = new \Innomatic\Wui\Dispatch\WuiDispatcher('login');
                $eventData = $loginDispatcher->getEventData();

                $innomaticSecurity = new \Innomatic\Security\SecurityManager();
                $innomaticSecurity->sendAlert('Wrong user local login for user '.$eventData['username'].' from remote address '.$_SERVER['REMOTE_ADDR']);
                $innomaticSecurity->logFailedAccess($eventData['username'], false, $_SERVER['REMOTE_ADDR']);

                unset($innomaticSecurity);
            }

            $sleepTime = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getConfig()->value('WrongLoginDelay');
            if (!strlen($sleepTime))
            $sleepTime = 1;
            $maxAttempts = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getConfig()->value('MaxWrongLogins');
            if (!strlen($maxAttempts))
            $maxAttempts = 3;

            sleep($sleepTime);

            if ($session->isValid('domain_login_attempts')) {
                $session->put('domain_login_attempts', $session->get('domain_login_attempts') + 1);
                if ($session->get('domain_login_attempts') >= $maxAttempts)
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->abort($innomaticLocale->getStr('wrongpwd'));
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
        //$wui_maingroup->addChild( new WuiButton( 'innomaticlogo', array( 'image' => \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getBaseUrl(false).'/shared/styles/cleantheme/innomatic_big_asp.png', 'action' => \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getBaseUrl().'/' ) ) );
        $wuiMainBFrame->addChild($wuiMainFrame);
        $wuiMainBFrame->addChild(new WuiHorizBar('hb'));
        $wuiMainBFrame->addChild(new WuiLink('copyright', array('label' => $innomaticLocale->getStr('auth_copyright.label'), 'link' => 'http://www.innoteam.it/', 'target' => '_blank')));
        $wuiMainGroup->addChild($wuiMainBFrame);
        $wuiMainGroup->addChild($wuiMainStatus);
        $wuiTopGroup->addChild($wuiMainGroup);
        $wuiPage->addChild($wuiTopGroup);
        $wui->addChild($wuiPage);
        $wui->render();

        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->halt();
    }

    public function authorize()
    {
    }
}


function login_login($eventData)
{
    $username = $eventData['username'];
    $domainId = \Innomatic\Domain\User\User::extractDomainID($username);

    // Checks it it can find the domain by hostname
    if (!strlen($domainId)) {
        $domainId = \Innomatic\Domain\Domain::getDomainByHostname();
        if (strlen($domainId)) {
            $username .= '@'.$domainId;
        }
    }

    // If no domain is found when in SAAS edition, it must be reauth without
    // checking database, since no Domain can be accessed.
    if (!strlen($domainId)) {
        DesktopDomainAuthenticatorHelper::doAuth(true);
    }
    $tmpDomain = new \Innomatic\Domain\Domain(
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
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
        \Innomatic\Desktop\Controller\DesktopFrontController::instance(
            '\Innomatic\Desktop\Controller\DesktopFrontController'
        )->session->put(
            'INNOMATIC_AUTH_USER',
            $username
        );

        $innomaticSecurity = new \Innomatic\Security\SecurityManager();
        $innomaticSecurity->logAccess(
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
    \Innomatic\Desktop\Controller\DesktopFrontController::instance(
        '\Innomatic\Desktop\Controller\DesktopFrontController'
    )->session->put(
        'INNOMATIC_AUTH_USER',
        $eventData['username']
    );

    $innomaticSecurity = new \Innomatic\Security\SecurityManager();
    $innomaticSecurity->logAccess(
        \Innomatic\Desktop\Controller\DesktopFrontController::instance(
            '\Innomatic\Desktop\Controller\DesktopFrontController'
        )->session->get(
            'INNOMATIC_AUTH_USER'
        ),
        true,
        false,
        $_SERVER['REMOTE_ADDR']
    );

    \Innomatic\Desktop\Controller\DesktopFrontController::instance('\Innomatic\Desktop\Controller\DesktopFrontController')->session->remove(
        'INNOMATIC_AUTH_USER'
    );
    unset($innomaticSecurity);

    DesktopDomainAuthenticatorHelper::doAuth();
}
