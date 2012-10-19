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

require_once('innomatic/util/Singleton.php');

/**
 * @package Desktop
 */
class DesktopLayout extends Singleton
{
    const DEFAULT_LAYOUT = 'horiz';
    protected $_layout;

    public function initRootLayout()
    {
        if (
            InnomaticContainer::instance('innomaticcontainer')->getState()
            != InnomaticContainer::STATE_SETUP
        ) {
            require_once('innomatic/application/ApplicationSettings.php');
            $appCfg = new ApplicationSettings(
                InnomaticContainer::instance(
                    'innomaticcontainer'
                )->getDataAccess(),
                'innomatic'
            );

            if (strlen($appCfg->getKey('desktop-root-layout'))) {
                $this->_layout = $appCfg->getKey('desktop-root-layout');
            } else {
                $this->_layout = DesktopLayout::DEFAULT_LAYOUT;
            }
        } else {
            $this->_layout = DesktopLayout::DEFAULT_LAYOUT;
        }
    }

    public function initDomainLayout()
    {
        // Wui theme
        //
        require_once('innomatic/domain/user/UserSettings.php');
        $userSettings = new UserSettings(
        InnomaticContainer::instance(
            'innomaticcontainer'
        )->getCurrentDomain()->getDataAccess(),
        InnomaticContainer::instance(
            'innomaticcontainer'
        )->getCurrentUser()->getUserId());
        $userLayout = $userSettings->getKey('desktop-layout', true);

        if (!strlen($userLayout)) {
            require_once('innomatic/application/ApplicationSettings.php');
            $appCfg = new ApplicationSettings(
                InnomaticContainer::instance(
                    'innomaticcontainer'
                )->getDataAccess(),
                'innomatic'
            );
            if (strlen($appCfg->getKey('desktop-root-layout'))) {
                $userLayout = $appCfg->getKey('desktop-root-layout');
                if (!strlen($userLayout)) {
                    $userLayout = DesktopLayout::DEFAULT_LAYOUT;
                }
            } else {
                $userLayout = DesktopLayout::DEFAULT_LAYOUT;
            }
            unset($appCfg);
        }
        $this->_layout = $userLayout;
    }

    public function getLayout()
    {
        switch ($this->_layout) {
            case 'horiz':
            case 'vert':
                return $this->_layout;
                break;
            default:
                return 'horiz';
                break;
        }
    }
}
