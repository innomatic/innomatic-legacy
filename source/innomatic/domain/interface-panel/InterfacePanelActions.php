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
 * @since      Class available since Release 5.0
*/

use \Innomatic\Core\InnomaticContainer;
use \Innomatic\Wui\Widgets;
use \Innomatic\Wui\Dispatch;
use \Innomatic\Locale\LocaleCatalog;
use \Innomatic\Domain\User;
use \Innomatic\Domain;
use \Shared\Wui;

class InterfacePanelActions extends \Innomatic\Desktop\Panel\PanelActions
{
    public $localeCatalog;
    public $status;
    public $javascript;

    public function __construct(\Innomatic\Desktop\Panel\PanelController $controller)
    {
        parent::__construct($controller);
    }

    public function beginHelper()
    {
        $this->localeCatalog = new LocaleCatalog(
            'innomatic::domain_interface',
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getLanguage()
        );
    }

    public function endHelper()
    {
    }

    public function executesettheme($eventData)
    {
        $userCfg = new UserSettings(
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(),
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId());
        $userCfg->setKey('wui-theme', $eventData['theme']);

        if (
                User::isAdminUser(
                        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserName(),
                        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDomainId()
                )
        ) {
            $domainCfg = new DomainSettings(
                    \Innomatic\Core\InnomaticContainer::instance(
                            '\Innomatic\Core\InnomaticContainer'
                    )->getCurrentDomain()->getDataAccess());
            $domainCfg->EditKey('wui-theme', $eventData['theme']);
        }

        $wui = \Innomatic\Wui\Wui::instance('\Innomatic\Wui\Wui');
        $wui->setTheme($eventData['theme']);

        \Innomatic\Webapp\WebAppContainer::instance(
        '\Innomatic\Webapp\WebAppContainer'
                )->getProcessor()->getResponse()->addHeader(
                'Location',
                WuiEventsCall::buildEventsCallString(
                '',
                array(
                array('view', 'default', ''),
                array('action', 'settheme2', '')
                )
                )
                );
    }

    public function executesettheme2($eventData)
    {
        $this->status = $this->localeCatalog->getStr('themeset_status');

        $this->setChanged();
        $this->notifyObservers('status');
    }

    public function executesetlanguage($eventData)
    {
        $userCfg = new UserSettings(
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(),
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId());
        $userCfg->setKey('desktop-language', $eventData['language']);

        $domainSets = new DomainSettings(
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()
        );

        if (
                User::isAdminUser(
                        \Innomatic\Core\InnomaticContainer::instance(
                                '\Innomatic\Core\InnomaticContainer'
                        )->getCurrentUser()->getUserName(),
                        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDomainId()
                )
        ) {
            $domainSets->EditKey('desktop-language', $eventData['language']);
        }

        $this->status = $this->localeCatalog->getStr('languageset_status');

        $this->setChanged();
        $this->notifyObservers('status');
        $this->notifyObservers('javascript');
    }

    public function executesetcountry($eventData)
    {
        $userCfg = new UserSettings(
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(),
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId());
        $userCfg->setKey('desktop-country', $eventData['country']);

        $domainSettings = new DomainSettings(
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()
        );

        if (
                User::isAdminUser(
                        \Innomatic\Core\InnomaticContainer::instance(
                                '\Innomatic\Core\InnomaticContainer'
                        )->getCurrentUser()->getUserName(),
                        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDomainId()
                )
        ) {
            $domainSettings->EditKey('desktop-country', $eventData['country']);
        }

        $this->status = $this->localeCatalog->getStr('countryset_status');

        $this->setChanged();
        $this->notifyObservers('status');
        $this->notifyObservers('javascript');
    }
}
