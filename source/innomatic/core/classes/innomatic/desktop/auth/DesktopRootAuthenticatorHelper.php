<?php
/**
 * Innomatic
 *
 * LICENSE 
 * 
 * This source file is subject to the new BSD license that is bundled 
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2012 Innoteam S.r.l.
 * @license    http://www.innomatic.org/license/   BSD License
 * @link       http://www.innomatic.org
 * @since      Class available since Release 5.0
*/

require_once('innomatic/desktop/auth/DesktopAuthenticatorHelper.php');

/**
 * @package Desktop
 */
class DesktopRootAuthenticatorHelper implements DesktopAuthenticatorHelper
{
    public function authenticate()
    {
        require_once('innomatic/wui/dispatch/WuiDispatcher.php');
        require_once('innomatic/desktop/controller/DesktopFrontController.php');

        $login_disp = new WuiDispatcher('login');
        $login_disp->addEvent('login', 'login_login');
        $login_disp->addEvent('logout', 'login_logout');
        $login_disp->Dispatch();

        if (InnomaticContainer::instance('innomaticcontainer')->getConfig()->Value('SecurityOnlyHttpsRootAccessAllowed') == '1') {
            if (!isset($_SERVER['HTTPS']) or ($_SERVER['HTTPS'] != 'on')) {
                self::doAuth(true, 'only_https_allowed');
            }
        }

        $session = DesktopFrontController::instance('desktopfrontcontroller')->session;
        if (!$session->isValid('INNOMATIC_ROOT_AUTH_USER')) {
            self::doAuth();
        }

        if ($session->isValid('root_login_attempts')) {
            $session->remove('root_login_attempts');
        }

        InnomaticContainer::instance('innomaticcontainer')->startRoot($session->get('INNOMATIC_ROOT_AUTH_USER'));
        
        return true;
    }

    public static function doAuth($wrong = false, $reason = '')
    {
        require_once('innomatic/locale/LocaleCatalog.php');
        require_once('innomatic/wui/Wui.php');
        $innomatic_locale = new LocaleCatalog('innomatic::authentication', InnomaticContainer::instance('innomaticcontainer')->getLanguage());

        $innomatic = InnomaticContainer::instance('innomaticcontainer');

        $wui = Wui::instance('wui');
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

        $wui_page = new WuiPage('loginpage', array('title' => $innomatic_locale->getStr('rootlogin'), 'border' => 'false', 'align' => 'center', 'valign' => 'middle'));
        $wui_topgroup = new WuiVertGroup('topgroup', array('align' => 'center', 'groupalign' => 'center', 'groupvalign' => 'middle', 'height' => '100%', 'width' => '0%'));
        $wui_maingroup = new WuiVertGroup('maingroup', array('align' => 'center'));
        $wui_titlebar = new WuiTitleBar('titlebar', array('title' => $innomatic_locale->getStr('rootlogin')));
        $wui_mainbframe = new WuiVertFrame('vframe', array('align' => 'center'));
        $wui_mainframe = new WuiHorizGroup('horizframe');
        $wui_mainstatus = new WuiStatusBar('mainstatusbar');

        // Main frame
        //
        $wui_grid = new WuiGrid('grid', array('rows' => '2', 'cols' => '2'));

        $wui_grid->addChild(new WuiLabel('usernamelabel', array('label' => $innomatic_locale->getStr('username'))), 0, 0);
        $wui_grid->addChild(new WuiString('username', array('disp' => 'login')), 0, 1);

        $wui_grid->addChild(new WuiLabel('passwordlabel', array('label' => $innomatic_locale->getStr('password'))), 1, 0);
        $wui_grid->addChild(new WuiString('password', array('disp' => 'login', 'password' => 'true')), 1, 1);

        $wui_vgroup = new WuiVertGroup('vertgroup', array('align' => 'center'));
        //$wui_vgroup->addChild( new WuiLabel( 'titlelabel', array( 'label' => $innomatic_locale->getStr( 'rootlogin' ) ) ) );
        $wui_vgroup->addChild($wui_grid);
        $wui_vgroup->addChild(new WuiSubmit('submit', array('caption' => $innomatic_locale->getStr('enter'))));

        require_once('innomatic/wui/dispatch/WuiEvent.php');
        require_once('innomatic/wui/dispatch/WuiEventsCall.php');
        $form_events_call = new WuiEventsCall();
        $form_events_call->addEvent(new WuiEvent('login', 'login', ''));
        $form_events_call->addEvent(new WuiEvent('view', 'default', ''));

        $wui_form = new WuiForm('form', array('action' => $form_events_call->getEventsCallString()));

        $wui_hgroup = new WuiHorizGroup('horizgroup', array('align' => 'middle'));
        $wui_hgroup->addChild(new WuiButton('password', array('themeimage' => 'keyhole', 'themeimagetype' => 'big', 'action' => InnomaticContainer::instance('innomaticcontainer')->getBaseUrl().'/', 'highlight' => false)));
        $wui_hgroup->addChild($wui_vgroup);

        $wui_form->addChild($wui_hgroup);
        $wui_mainframe->addChild($wui_form);

        // Wrong account check
        //
        require_once('innomatic/desktop/controller/DesktopFrontController.php');
        $session = DesktopFrontController::instance('desktopfrontcontroller')->session;

        if ($wrong) {
            if (InnomaticContainer::instance('innomaticcontainer')->getConfig()->Value('SecurityAlertOnWrongLocalRootLogin') == '1') {
                require_once('innomatic/security/SecurityManager.php');

                $innomatic_security = new SecurityManager();
                $innomatic_security->SendAlert('Wrong root local login from remote address '.$_SERVER['REMOTE_ADDR']);
                $innomatic_security->LogFailedAccess('', true, $_SERVER['REMOTE_ADDR']);

                unset($innomatic_security);
            }

            $sleep_time = InnomaticContainer::instance('innomaticcontainer')->getConfig()->Value('WrongLoginDelay');
            if (!strlen($sleep_time))
            $sleep_time = 1;
            $max_attempts = InnomaticContainer::instance('innomaticcontainer')->getConfig()->Value('MaxWrongLogins');
            if (!strlen($max_attempts))
            $max_attempts = 3;

            sleep($sleep_time);

            if ($session->isValid('root_login_attempts')) {
                $session->put('root_login_attempts', $session->get('root_login_attempts') + 1);
                if ($session->get('root_login_attempts') >= $max_attempts) {
                    InnomaticContainer::instance('innomaticcontainer')->abort($innomatic_locale->getStr('wrongpwd'));
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
        //$wui_maingroup->addChild( new WuiButton( 'innomaticlogo', array( 'image' => InnomaticContainer::instance('innomaticcontainer')->getBaseUrl(false).'/shared/styles/cleantheme/innomatic_big_asp.png', 'action' => InnomaticContainer::instance('innomaticcontainer')->getBaseUrl().'/' ) ) );
        $wui_mainbframe->addChild($wui_mainframe);
        $wui_mainbframe->addChild(new WuiHorizBar('hb'));
        $wui_mainbframe->addChild(new WuiLink('copyright', array('label' => $innomatic_locale->getStr('auth_copyright.label'), 'link' => 'http://www.innoteam.it/', 'target' => '_blank')));
        $wui_maingroup->addChild($wui_mainbframe);
        $wui_maingroup->addChild($wui_mainstatus);
        $wui_topgroup->addChild($wui_maingroup);
        $wui_page->addChild($wui_topgroup);
        $wui->addChild($wui_page);
        $wui->render();

        InnomaticContainer::instance('innomaticcontainer')->halt();
    }

    public function authorize()
    {
    }
}


function login_login($eventData)
{     
    $fh = @fopen(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/conf/rootpasswd.ini', 'r');
    if ($fh) {
        $cpassword = fgets($fh, 4096);
        require_once('innomatic/desktop/controller/DesktopFrontController.php');
        if ($eventData['username'] == 'root' and md5($eventData['password']) == $cpassword) {
            DesktopFrontController::instance('desktopfrontcontroller')->session->put(
                'INNOMATIC_ROOT_AUTH_USER',
                $eventData['username']);

            require_once('innomatic/security/SecurityManager.php');

            $innomatic_security = new SecurityManager();
            $innomatic_security->LogAccess('', false, true, $_SERVER['REMOTE_ADDR']);

            unset($innomatic_security);
        } else {
            DesktopRootAuthenticatorHelper::doAuth(true);
            DesktopFrontController::instance('desktopfrontcontroller')->session->remove('INNOMATIC_ROOT_AUTH_USER');
        }
    } else {
        DesktopRootAuthenticatorHelper::doAuth(true);
        unset($INNOMATIC_ROOT_AUTH_USER);
    }
}

function login_logout($eventData)
{
    require_once('innomatic/security/SecurityManager.php');

    $innomatic_security = new SecurityManager();
    $innomatic_security->LogAccess('', true, true, $_SERVER['REMOTE_ADDR']);

    DesktopFrontController::instance('desktopfrontcontroller')->session->remove('INNOMATIC_ROOT_AUTH_USER');
    unset($innomatic_security);
    DesktopRootAuthenticatorHelper::doAuth();
}
