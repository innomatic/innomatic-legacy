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
 * @license    http://www.innomaticplatform.com/license/   BSD License
 * @link       http://www.innomaticplatform.com
*/
namespace Innomatic\Desktop\Controller;

use \Innomatic\Core\InnomaticContainer;

/**
 * Front controller for the Innomatic desktop.
 *
 * This is the real front controller for the Innomatic desktop.
 *
 * @since      5.0.0 introduced
 * @package    Desktop
 */
class DesktopFrontController extends \Innomatic\Util\Singleton
{
    /**
     * Innomatic container.
     * 
     * @var \Innomatic\Core\InnomaticContainer
     */
    protected $container;
    /**
     * Innomatic mode.
     *
     * @var integer
     */
    protected $mode;
    /**
     * Desktop session object.
     *
     * @var DesktopSession
     */
    public $session;

    /**
     * Class constructor.
     */
    public function ___construct()
    {
        $this->session = new \Innomatic\Desktop\Session\DesktopSession();
        $this->session->start();
        $this->container = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');
    }

    public function execute($mode, $resource)
    {
        $this->mode = $mode;

        // Sets root theme.
        \Innomatic\Wui\Theme\WuiTheme::setRootTheme();

        // Authenticates the user.
        $auth = \Innomatic\Desktop\Auth\DesktopAuthenticatorHelperFactory::getAuthenticatorHelper($mode);
        if ($auth->authenticate()) {
            // Validates WUI widgets input.
            $validator = new \Innomatic\Wui\Validation\WuiValidatorHelper();
            $validator->validate();

            // TODO Put authorizer here

            // Sets domain theme, if the system is in domain mode.
            if ($mode == \Innomatic\Core\InnomaticContainer::MODE_DOMAIN) {
                WuiTheme::setDomainTheme();
            }

            switch ($mode) {
                case \Innomatic\Core\InnomaticContainer::MODE_BASE:
                    $this->executeBase($resource);
                    break;

                case \Innomatic\Core\InnomaticContainer::MODE_DOMAIN:
                    $this->executeDomain($resource);
                    break;

                case \Innomatic\Core\InnomaticContainer::MODE_ROOT:
                    $this->executeRoot($resource);
                    break;
            }

            // @todo Verify whose panel has been called

            // @todo Verificare se esiste e se è valida, altrimenti mandare 404 di WebApp
        }

        /**
         * To be applied when implementing a xml def file parser
         $wui->addChild(new \Shared\Wui\WuiXml('def',array('definition' => $this->response->getContent())));
         $wui->render();
         */
    }

    /**
     * Gets the Innomatic mode.
     *
     * @return integer
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Launches a panel in the base Innomatic desktop.
     *
     * @param string $resource Panel name.
     */
    public function executeBase($resource)
    {
        $path = 'base';
        // TODO verificare se e' ancora necessario dopo aver creato \Innomatic\Wui\Wui::setTheme()
        if (!($this->container->getState() == \Innomatic\Core\InnomaticContainer::STATE_SETUP)) {
            $appCfg = new \Innomatic\Application\ApplicationSettings(
                'innomatic'
            );
            if (strlen($appCfg->getKey('wui-root-theme'))) {
                \Innomatic\Wui\Wui::instance('\Innomatic\Wui\Wui')->setTheme(
                    $appCfg->getKey('wui-root-theme')
                );
            }
            unset($appCfg);
        } else {
            $path = 'setup';
        }

        if (substr($resource, -1, 1) != '/') {
            include(
                'innomatic/desktop/layout/'
                . $path . '/' . basename($resource) . '.php'
            );
        } else {
            \Innomatic\Webapp\WebAppContainer::instance('\Innomatic\Webapp\WebAppContainer')->getProcessor()->getResponse()->addHeader(
                'P3P', 'CP="CUR ADM OUR NOR STA NID"'
            );
            include('innomatic/desktop/layout/' . $path . '/index.php');
        }
    }

    /**
     * Launches a panel in the control panel (root desktop).
     *
     * If the panel name is "main" then
     * no real panel is launched, a root desktop layout file is included instead.
     *
     * Root desktop layout files are stored in the folder
     * core/classes/innomatic/desktop/layout/root.
     *
     * If the panel name is "unlock", a special routine for unlocking a blocked
     * Innomatic container (such as when an application update failed) is
     * launched, after a standard root authentication.
     *
     * @param string $resource Panel name.
     */
    public function executeRoot($resource)
    {
        if (substr($resource, -1, 1) != '/') {
            $desktopPanel = basename($resource);
            if (
                $this->container->getState()
                == \Innomatic\Core\InnomaticContainer::STATE_DEBUG
            ) {
                $dump = \Innomatic\Debug\InnomaticDump::instance('\Innomatic\Debug\InnomaticDump');
                $dump->desktopApplication = $desktopPanel;
            }

            if (is_dir($resource . '-panel')) {
                $panelHome = $resource . '-panel/';
                $panelName = basename($resource);
                $controllerClassName = ucfirst($panelName) . 'PanelController';

                // Checks if view file and definition exist
                if (!include_once($panelHome.$controllerClassName . '.php')) {
                    throw new \Innomatic\Wui\WuiException(
                        \Innomatic\Wui\WuiException::MISSING_CONTROLLER_FILE
                    );
                }
                if (!class_exists($controllerClassName, true)) {
                    throw new \Innomatic\Wui\WuiException(
                        \Innomatic\Wui\WuiException::MISSING_CONTROLLER_CLASS
                    );
                }
                $controller = new $controllerClassName(
                    \Innomatic\Core\InnomaticContainer::MODE_ROOT,
                    $panelName
                );
            } else {
                switch ($desktopPanel) {
                    case 'index':
                        include(
                            'innomatic/desktop/layout/root/'
                            . $desktopPanel
                            . '.php'
                        );
                        break;

                    case 'unlock':
                        // Handles system unlock.
                        $innomatic = $this->container;
                        $innomatic->setInterface(\Innomatic\Core\InnomaticContainer::INTERFACE_WEB);
                        $innomatic->unlock();
                        break;

                    default:
                        include($resource.'.php');
                }

            }
        } else {
            if (strlen($this->session->get('INNOMATIC_ROOT_AUTH_USER'))) {
                \Innomatic\Webapp\WebAppContainer::instance('\Innomatic\Webapp\WebAppContainer')->getProcessor()->getResponse()->addHeader('P3P', 'CP="CUR ADM OUR NOR STA NID"' );
                include('innomatic/desktop/layout/root/index.php');
            }
        }
    }

    /**
     * Launches a panel in the domain desktop.
     *
     * If the panel name is one "main" then
     * no real panel is launched, a domain  desktop layout file is included.
     *
     * Domain desktop layout files are stored in the folder
     * core/classes/innomatic/desktop/layout/domain.
     *
     * @param string $resource Panel name.
     */

    public function executeDomain($resource)
    {
        // Check if this is the default page and if the user is allowed to access the dashboard
        if (substr($resource, -1, 1) == '/') {
            $perm = new \Innomatic\Desktop\Auth\DesktopPanelAuthorizator($this->container->getCurrentDomain()->getDataAccess(), $this->container->getCurrentUser()->getGroup());
            $node_id = $perm->getNodeIdFromFileName('dashboard');
            if ( $perm->check( $node_id, \Innomatic\Desktop\Auth\DesktopPanelAuthorizator::NODETYPE_PAGE ) != \Innomatic\Desktop\Auth\DesktopPanelAuthorizator::NODE_NOTENABLED ) {
                $resource = $resource.'dashboard';
            }
        }

        if (substr($resource, -1, 1) != '/') {
            // Must exit if the user called a page for which he isn't enabled
            //
            if (!isset($perm)) {
                $perm = new \Innomatic\Desktop\Auth\DesktopPanelAuthorizator($this->container->getCurrentDomain()->getDataAccess(), $this->container->getCurrentUser()->getGroup());
            }

            $desktopPanel = basename($resource);
            if ($this->container->getState() == \Innomatic\Core\InnomaticContainer::STATE_DEBUG) {
                $dump = \Innomatic\Debug\InnomaticDump::instance('\Innomatic\Debug\InnomaticDump');
                $dump->desktopApplication = $desktopPanel;
            }

            switch ($desktopPanel) {
                case 'index':
                    break;

                default:
                    $node_id = $perm->getNodeIdFromFileName($desktopPanel);

                    if ($node_id) {
                        if ($perm->check($node_id, \Innomatic\Desktop\Auth\DesktopPanelAuthorizator::NODETYPE_PAGE) == \Innomatic\Desktop\Auth\DesktopPanelAuthorizator::NODE_NOTENABLED) {
                            $adloc = new \Innomatic\Locale\LocaleCatalog('innomatic::authentication', $this->container->getCurrentUser()->getLanguage());
                            $this->container->abort($adloc->getStr('nopageauth'));
                        }
                    } else {
                        $adloc = new \Innomatic\Locale\LocaleCatalog('innomatic::authentication', $this->container->getCurrentUser()->getLanguage());
                        $this->container->abort($adloc->getStr('nopageauth'));
                    }
            }
            if (is_dir($resource . '-panel')) {
                $panelHome = $resource . '-panel/';
                $panelName = basename($resource);
                $controllerClassName = ucfirst($panelName) . 'PanelController';

                // Checks if view file and definition exist
                if (!include_once($panelHome . $controllerClassName . '.php')) {
                    throw new \Innomatic\Wui\WuiException(\Innomatic\Wui\WuiException::MISSING_CONTROLLER_FILE);
                }
                if (!class_exists($controllerClassName, true)) {
                    throw new \Innomatic\Wui\WuiException(\Innomatic\Wui\WuiException::MISSING_CONTROLLER_CLASS);
                }
                $controller = new $controllerClassName(\Innomatic\Core\InnomaticContainer::MODE_DOMAIN, $panelName);
                $this->container->setPanelController($controller);
            } else {
                switch ($desktopPanel) {
                    case 'menu':
                        include(
                            'innomatic/desktop/layout/domain/'
                            . $desktopPanel
                            . '.php'
                        );
                        break;

                    default:
                        include($resource . '.php');
                }
            }
        } else {
            if (strlen($this->session->get('INNOMATIC_AUTH_USER'))) {
                \Innomatic\Webapp\WebAppContainer::instance('\Innomatic\Webapp\WebAppContainer')->getProcessor()->getResponse()->addHeader('P3P', 'CP="CUR ADM OUR NOR STA NID"' );
                include('innomatic/desktop/layout/domain/index.php');
            }
        }
    }
}
