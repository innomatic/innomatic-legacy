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
 * @license    http://www.innomatic.io/license/ New BSD License
 * @link       http://www.innomatic.io
*/
namespace Innomatic\Desktop\Auth;

use Shared\Wui;

/**
 *
 * @package Desktop
 * @since 5.0.0 introduced
 */
class DesktopRootAuthenticatorHelper implements \Innomatic\Desktop\Auth\DesktopAuthenticatorHelper
{
    public function authenticate()
    {
        $container = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');

        $login_disp = new \Innomatic\Wui\Dispatch\WuiDispatcher('login');
        $login_disp->addEvent('login', '\Innomatic\Desktop\Auth\login_login');
        $login_disp->addEvent('logout', '\Innomatic\Desktop\Auth\login_logout');
        $login_disp->Dispatch();

        if ($container->getConfig()->Value('SecurityOnlyHttpsRootAccessAllowed') == '1') {
            if (! isset($_SERVER['HTTPS']) or ($_SERVER['HTTPS'] != 'on')) {
                self::doAuth(true, 'only_https_allowed');
            }
        }

        $session = \Innomatic\Desktop\Controller\DesktopFrontController::instance('\Innomatic\Desktop\Controller\DesktopFrontController')->session;
        if (! $session->isValid('INNOMATIC_ROOT_AUTH_USER')) {
            self::doAuth();
        }

        if ($session->isValid('root_login_attempts')) {
            $session->remove('root_login_attempts');
        }

        $container->startRoot($session->get('INNOMATIC_ROOT_AUTH_USER'));

        return true;
    }

    public static function doAuth($wrong = false, $reason = '')
    {
        $container = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');
        $innomatic_locale = new \Innomatic\Locale\LocaleCatalog('innomatic::authentication', $container->getLanguage());

        $innomatic = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');

        $wui = \Innomatic\Wui\Wui::instance('\Innomatic\Wui\Wui');
        $wui->loadWidget('button');
        $wui->loadWidget('empty');
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

        $wui_page = new WuiPage('loginpage', array(
            'title' => $innomatic_locale->getStr('rootlogin'),
            'border' => 'false',
            'align' => 'center',
            'valign' => 'middle'
        ));
        $wui_topgroup = new WuiVertgroup('topgroup', array(
            'align' => 'center',
            'groupalign' => 'center',
            'groupvalign' => 'middle',
            'height' => '100%',
            'width' => '0%'
        ));
        $wui_maingroup = new WuiVertgroup('maingroup', array(
            'align' => 'center'
        ));
        $wui_titlebar = new WuiTitleBar('titlebar', array(
            'title' => $innomatic_locale->getStr('rootlogin')
        ));
        $wui_mainbframe = new WuiVertframe('vframe', array(
            'align' => 'center'
        ));
        $wui_mainframe = new WuiHorizgroup('horizframe');
        $wui_mainstatus = new WuiStatusBar('mainstatusbar');

        // Main frame
        //
        $wui_grid = new WuiGrid('grid', array(
            'rows' => '2',
            'cols' => '2'
        ));

        $wui_grid->addChild(new WuiLabel('usernamelabel', array(
            'label' => $innomatic_locale->getStr('username')
        )), 0, 0);
        $wui_grid->addChild(new WuiString('username', array(
            'disp' => 'login'
        )), 0, 1);

        $wui_grid->addChild(new WuiLabel('passwordlabel', array(
            'label' => $innomatic_locale->getStr('password')
        )), 1, 0);
        $wui_grid->addChild(new WuiString('password', array(
            'disp' => 'login',
            'password' => 'true'
        )), 1, 1);

        $wui_vgroup = new WuiVertgroup('vertgroup', array(
            'align' => 'center'
        ));
        // $wui_vgroup->addChild( new WuiLabel( 'titlelabel', array( 'label' => $innomatic_locale->getStr( 'rootlogin' ) ) ) );
        $wui_vgroup->addChild($wui_grid);
        $wui_vgroup->addChild(new WuiSubmit('submit', array(
            'caption' => $innomatic_locale->getStr('enter')
        )));

        $form_events_call = new \Innomatic\Wui\Dispatch\WuiEventsCall();
        $form_events_call->addEvent(new \Innomatic\Wui\Dispatch\WuiEvent('login', 'login', ''));
        $form_events_call->addEvent(new \Innomatic\Wui\Dispatch\WuiEvent('view', 'default', ''));

        $wui_form = new WuiForm('form', array(
            'action' => $form_events_call->getEventsCallString()
        ));

        $wui_hgroup = new WuiHorizgroup('horizgroup', array(
            'align' => 'middle'
        ));
        $wui_hgroup->addChild(new WuiButton('password', array(
            'themeimage' => 'keyhole',
            'themeimagetype' => 'big',
            'action' => $innomatic->getBaseUrl() . '/',
            'highlight' => false
        )));
        $wui_hgroup->addChild($wui_vgroup);

        $wui_form->addChild($wui_hgroup);
        $wui_mainframe->addChild($wui_form);

        // Wrong account check
        //
        $session = \Innomatic\Desktop\Controller\DesktopFrontController::instance('\Innomatic\Desktop\Controller\DesktopFrontController')->session;

        if ($wrong) {
            if ($innomatic->getConfig()->Value('SecurityAlertOnWrongLocalRootLogin') == '1') {
                $innomatic_security = new \Innomatic\Security\SecurityManager();
                $innomatic_security->sendAlert('Wrong root local login from remote address ' . $_SERVER['REMOTE_ADDR']);
                $innomatic_security->logFailedAccess('', true, $_SERVER['REMOTE_ADDR']);

                unset($innomatic_security);
            }

            $sleep_time = $innomatic->getConfig()->Value('WrongLoginDelay');
            if (! strlen($sleep_time))
                $sleep_time = 1;
            $max_attempts = $innomatic->getConfig()->Value('MaxWrongLogins');
            if (! strlen($max_attempts))
                $max_attempts = 3;

            sleep($sleep_time);

            if ($session->isValid('root_login_attempts')) {
                $session->put('root_login_attempts', $session->get('root_login_attempts') + 1);
                if ($session->get('root_login_attempts') >= $max_attempts) {
                    $innomatic->abort($innomatic_locale->getStr('wrongpwd'));
                }
            } else {
                $session->put('root_login_attempts', 1);
            }

            if ($reason) {
                $wui_mainstatus->mArgs['status'] = $innomatic_locale->getStr($reason);
            } else {
                $wui_mainstatus->mArgs['status'] = $innomatic_locale->getStr('wrongpwd');
            }
        } else {
            $session->put('domain_login_attempts', 0);
        }

        // Page render
        //
        $wui_maingroup->addChild($wui_titlebar);
        // $wui_maingroup->addChild( new WuiButton( 'innomaticlogo', array( 'image' => $innomatic->getBaseUrl(false).'/shared/styles/cleantheme/innomatic_big_asp.png', 'action' => $innomatic->getBaseUrl().'/' ) ) );
        $wui_mainbframe->addChild($wui_mainframe);
        $wui_mainbframe->addChild(new WuiHorizBar('hb'));
        $wui_mainbframe->addChild(new WuiLink('copyright', array(
            'label' => $innomatic_locale->getStr('auth_copyright.label'),
            'link' => 'http://www.innomatic.io/',
            'target' => '_blank'
        )));
        $wui_maingroup->addChild($wui_mainbframe);
        $wui_maingroup->addChild($wui_mainstatus);
        $wui_topgroup->addChild($wui_maingroup);
        $wui_page->addChild($wui_topgroup);
        $wui->addChild($wui_page);
        $wui->render();

        $innomatic->halt();
    }

    public function authorize()
    {}
}

function login_login($eventData)
{
    $fh = @fopen(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/conf/rootpasswd.ini', 'r');
    if ($fh) {
        $cpassword = fgets($fh, 4096);
        if ($eventData['username'] == 'root' and md5($eventData['password']) == $cpassword) {
            \Innomatic\Desktop\Controller\DesktopFrontController::instance('\Innomatic\Desktop\Controller\DesktopFrontController')->session->put('INNOMATIC_ROOT_AUTH_USER', $eventData['username']);

            $innomatic_security = new \Innomatic\Security\SecurityManager();
            $innomatic_security->LogAccess('', false, true, $_SERVER['REMOTE_ADDR']);

            unset($innomatic_security);
        } else {
            DesktopRootAuthenticatorHelper::doAuth(true);
            \Innomatic\Desktop\Controller\DesktopFrontController::instance('\Innomatic\Desktop\Controller\DesktopFrontController')->session->remove('INNOMATIC_ROOT_AUTH_USER');
        }
    } else {
        DesktopRootAuthenticatorHelper::doAuth(true);
        unset($INNOMATIC_ROOT_AUTH_USER);
    }
}

function login_logout($eventData)
{
    $innomatic_security = new \Innomatic\Security\SecurityManager();
    $innomatic_security->LogAccess('', true, true, $_SERVER['REMOTE_ADDR']);

    \Innomatic\Desktop\Controller\DesktopFrontController::instance('\Innomatic\Desktop\Controller\DesktopFrontController')->session->remove('INNOMATIC_ROOT_AUTH_USER');
    unset($innomatic_security);
    DesktopRootAuthenticatorHelper::doAuth();
}
