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

require_once('innomatic/desktop/panel/PanelActions.php');

class DomainsPanelActions extends PanelActions
{
    private $_localeCatalog;
    public $status;

    public function __construct(PanelController $controller)
    {
        parent::__construct($controller);
    }

    public function beginHelper()
    {
        require_once('innomatic/logging/Logger.php');
        require_once('innomatic/locale/LocaleCatalog.php');
        require_once('innomatic/domain/Domain.php');
        require_once('innomatic/config/ConfigBase.php');
        require_once('innomatic/config/ConfigFile.php');
        require_once('innomatic/config/ConfigMan.php');
        require_once('innomatic/application/ApplicationDependencies.php');
        require_once('innomatic/application/Application.php');
        require_once('innomatic/domain/user/Group.php');
        require_once('innomatic/domain/user/Permissions.php');
        require_once('innomatic/locale/LocaleCatalog.php');
        $this->_localeCatalog = new LocaleCatalog(
            'innomatic::root_domains',
            InnomaticContainer::instance('innomaticcontainer')->getLanguage()
        );
    }

    public function endHelper()
    {
    }

    public function executeCreatedomain($eventData)
    {
        $domain = new Domain(InnomaticContainer::instance(
            'innomaticcontainer'
        )->getDataAccess(), 0, null);

        $domainData['domainid'] = $eventData['domainid'];
        $domainData['domainname'] = $eventData['domainname'];
        $domainData['domainpassword'] = $eventData['domainpassword'];
        $domainData['webappurl'] = $eventData['webappurl'];
        $domainData['domaindaname'] = $eventData['domaindaname'];
        $domainData['dataaccesshost'] = $eventData['dataaccesshost'];
        $domainData['dataaccessport'] = $eventData['dataaccessport'];
        $domainData['dataaccessuser'] = $eventData['dataaccessuser'];
        $domainData['dataaccesspassword'] = $eventData['dataaccesspassword'];
        $domainData['dataaccesstype'] = $eventData['dataaccesstype'];
        $domainData['webappskeleton'] = $eventData['webappskeleton'];
        $domainData['maxusers'] = $eventData['maxusers'];
        
        if ($domain->Create($domainData, $eventData['createdomainda'] == 'on' ? true : false)) {
            $this->status = $this->_localeCatalog->getStr('domaincreated_status');
        } else {
            $this->status = $this->_localeCatalog->getStr('domainnotcreated_status');
        }
        $this->setChanged();
        $this->notifyObservers('status');
    }

    public function executeUpdatedomain($eventData)
    {
        $domainQuery = InnomaticContainer::instance(
            'innomaticcontainer'
        )->getDataAccess()->execute(
            'SELECT domainid FROM domains WHERE id='
            . $eventData['domainserial']
        );

        $null = null;
        $domain = new Domain(
            InnomaticContainer::instance('innomaticcontainer')->getDataAccess(),
            $domainQuery->getFields('domainid'),
            $null
        );
        
        // Holds previous domain webapp skeleton information before updating.
        $currentWebappSkeleton = $domain->getWebappSkeleton();

        $domainData['domainserial'] = $eventData['domainserial'];
        $domainData['domainname'] = $eventData['domainname'];
        $domainData['domainpassword'] = $eventData['domainpassword'];
        $domainData['webappurl'] = $eventData['webappurl'];
        $domainData['dataaccesstype'] = $eventData['dataaccesstype'];
        $domainData['domaindaname'] = $eventData['domaindaname'];
        $domainData['dataaccesshost'] = $eventData['dataaccesshost'];
        $domainData['dataaccessport'] = $eventData['dataaccessport'];
        $domainData['dataaccessuser'] = $eventData['dataaccessuser'];
        $domainData['dataaccesspassword'] = $eventData['dataaccesspassword'];
        $domainData['dataaccessport'] = $eventData['dataaccessport'];
        $domainData['dataaccesstype'] = $eventData['dataaccesstype'];

        if ($domain->edit($domainData)) {
            // Changes max users limit.
            $domain->setMaxUsers($eventData['maxusers']);
            
            // Applies new webapp skeleton if changed.
            if ($eventData['webappskeleton'] != $currentWebappSkeleton) {
                $domain->setWebappSkeleton($eventData['webappskeleton']);
            }

            $this->status = $this->_localeCatalog->getStr(
                'domainupdated_status'
            );
        } else {
            $this->status = $this->_localeCatalog->getStr(
                'domainnotupdated_status'
            );
        }
        $this->setChanged();
        $this->notifyObservers('status');
    }

    public function executeEditdomainnotes($eventData)
    {
        $null = null;
        $domain = new Domain(
            InnomaticContainer::instance('innomaticcontainer')->getDataAccess(),
            $eventData['domainid'],
            $null
        );

        $domain->setNotes($eventData['notes']);

        $this->status = $this->_localeCatalog->getStr('notes_set.status');
        $this->setChanged();
        $this->notifyObservers('status');
    }

    public function executeRemovedomain($eventData)
    {
        $null = null;
        $domain = new Domain(
            InnomaticContainer::instance('innomaticcontainer')->getDataAccess(),
            $eventData['domainid'],
            $null
        );

        if ($domain->Remove()) {
            $this->status = $this->_localeCatalog->getStr(
                'domainremoved_status'
            );
        } else {
            $this->status = $this->_localeCatalog->getStr(
                'domainnotremoved_status'
            );
        }
        $this->setChanged();
        $this->notifyObservers('status');
    }

    public function executeEnabledomain($eventData)
    {
        $null = null;
        $domain = new Domain(
            InnomaticContainer::instance('innomaticcontainer')->getDataAccess(),
            $eventData['domainid'],
            $null
        );

        if ($domain->enable()) {
            $this->status = $this->_localeCatalog->getStr('domainenabled_status');
        } else {
            $this->status = $this->_localeCatalog->getStr(
                'domainnotenabled_status'
            );
        }
        $this->setChanged();
        $this->notifyObservers('status');
    }

    public function executeDisabledomain($eventData)
    {
        $null = null;
        $domain = new Domain(
            InnomaticContainer::instance('innomaticcontainer')->getDataAccess(),
            $eventData['domainid'],
            $null
        );

        if ($domain->disable()) {
            $this->status = $this->_localeCatalog->getStr(
                'domaindisabled_status'
            );
        } else {
            $this->status = $this->_localeCatalog->getStr(
                'domainnotdisabled_status'
            );
        }
        $this->setChanged();
        $this->notifyObservers('status');
    }

    public function executeActivateapplication($eventData)
    {
        $domainQuery = InnomaticContainer::instance(
            'innomaticcontainer'
        )->getDataAccess()->execute(
            'SELECT domainid FROM domains WHERE id = '
            . $eventData['domainid']
        );

        if ($domainQuery) {
            $domainData = $domainQuery->getFields();

            $null = null;
            $domain = new Domain(
                InnomaticContainer::instance(
                    'innomaticcontainer'
                )->getDataAccess(),
                $domainData['domainid'],
                $null
            );
            if (!$domain->enableApplication($eventData['appid'])) {
                $unmetDeps = $domain->getLastActionUnmetDeps();

                if (count($unmetDeps)) {
                    while (list (, $dep) = each($unmetDeps))
                    $unmetDepsStr.= ' '.$dep;

                    $this->status .= $this->_localeCatalog->getStr('modnotenabled_status').' ';
                    $this->status .= $this->_localeCatalog->getStr('unmetdeps_status').$unmetDepsStr.'.';
                }

                $unmetSuggestions = $domain->getLastActionUnmetSuggs();

                if (count($unmetSuggestions)) {
                    while (list (, $sugg) = each($unmetSuggestions))
                    $unmetSuggestionsString.= ' '.$sugg.$this->status .= $this->_localeCatalog->getStr(
                        'unmetsuggs_status'
                    ).$unmetSuggestionsString.'.';
                }
            } else
            $this->status .= $this->_localeCatalog->getStr('modenabled_status');
        }
        $this->setChanged();
        $this->notifyObservers('status');
    }

    public function executeActivateallapplications($eventData)
    {
        $domainQuery = InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->execute(
            'SELECT domainid FROM domains WHERE id = '.$eventData['domainid']
        );

        if ($domainQuery) {
            $domainData = $domainQuery->getFields();

            $domain = new Domain(
                InnomaticContainer::instance('innomaticcontainer')->getDataAccess(),
                $domainData['domainid'],
                ''
            );
            if ($domain->EnableAllApplications()) {
                $this->status = $this->_localeCatalog->getStr('applications_enabled.status');
            }
        } else {
            $this->status = $this->_localeCatalog->getStr('applications_not_enabled.status');
        }
        $this->setChanged();
        $this->notifyObservers('status');
    }

    public function executeDeactivateallapplications($eventData)
    {
        $domainQuery = InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->execute(
            'SELECT domainid FROM domains WHERE id = '.$eventData['domainid']
        );

        if ($domainQuery) {
            $domainData = $domainQuery->getFields();

            $domain = new Domain(
                InnomaticContainer::instance('innomaticcontainer')->getDataAccess(),
                $domainData['domainid'],
                ''
            );
            if ($domain->disableAllApplications(false))
            $this->status = $this->_localeCatalog->getStr('applications_disabled.status');
        } else
        $this->status = $this->_localeCatalog->getStr('applications_not_disabled.status');
        $this->setChanged();
        $this->notifyObservers('status');
    }

    public function executeEnableoption($eventData)
    {
        require_once('innomatic/application/Application.php');

        $application = new Application(
            InnomaticContainer::instance('innomaticcontainer')->getDataAccess(),
            $eventData['applicationid']
        );

        $application->enableOption($eventData['option'], $eventData['domainid']);

        $this->status = $this->_localeCatalog->getStr('option_enabled.status');
        $this->setChanged();
        $this->notifyObservers('status');
    }

    public function executeDisableoption($eventData)
    {
        require_once('innomatic/application/Application.php');

        $application = new Application(
            InnomaticContainer::instance('innomaticcontainer')->getDataAccess(),
            $eventData['applicationid']
        );
        $application->disableOption($eventData['option'], $eventData['domainid']);

        $this->status = $this->_localeCatalog->getStr('option_disabled.status');
        $this->setChanged();
        $this->notifyObservers('status');
    }

    public function executeDeactivateapplication($eventData)
    {
        $domainQuery = InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->execute(
            'SELECT domainid FROM domains WHERE id = '.$eventData['domainid']
        );

        if ($domainQuery) {
            $domainData = $domainQuery->getFields();

            $null = null;
            $domain = new Domain(
                InnomaticContainer::instance('innomaticcontainer')->getDataAccess(),
                $domainData['domainid'],
                $null
            );
            if (!$domain->DisableApplication($eventData['appid'])) {
                $unmetDeps = $domain->getLastActionUnmetDeps();

                if (count($unmetDeps)) {
                    while (list (, $dep) = each($unmetDeps))
                    $unmetDepsStr.= ' '.$dep;

                    $this->status.= $this->_localeCatalog->getStr('modnotdisabled_status').' ';
                    $this->status.= $this->_localeCatalog->getStr('disunmetdeps_status').$unmetDepsStr.'.';
                }
            } else
            $this->status.= $this->_localeCatalog->getStr('moddisabled_status');
        }
        $this->setChanged();
        $this->notifyObservers('status');
    }

    public function executeCleandomainlog($eventData)
    {
        $tempLog = new Logger(
            InnomaticContainer::instance('innomaticcontainer')->getHome()
            .'core/domains/'.$eventData['domainid'].'/log/domain.log'
        );

        if ($tempLog->cleanLog()) {
            $this->status = $this->_localeCatalog->getStr('logcleaned_status');
        } else {
            $this->status = $this->_localeCatalog->getStr('lognotcleaned_status');
        }
        $this->setChanged();
        $this->notifyObservers('status');
    }

    public function executeCleandataaccesslog($eventData)
    {
        $query = InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->execute(
            'SELECT domainid FROM domains WHERE id='.$eventData['domainid']
        );

        $tempLog = new Logger(
            InnomaticContainer::instance('innomaticcontainer')->getHome()
            .'core/domains/'.$query->getFields('domainid').'/log/dataaccess.log'
        );

        if ($tempLog->cleanLog()) {
            $this->status = $this->_localeCatalog->getStr('logcleaned_status');
        } else {
            $this->status = $this->_localeCatalog->getStr('lognotcleaned_status');
        }
        $this->setChanged();
        $this->notifyObservers('status');
    }
}
