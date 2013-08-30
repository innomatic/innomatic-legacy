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

class InterfacePanelActions extends PanelActions
{
    private $_localeCatalog;
    public $status;
    public $javascript;

    public function __construct(PanelController $controller)
    {
        parent::__construct($controller);
    }

    public function beginHelper()
    {
require_once('innomatic/locale/LocaleCatalog.php');
require_once('innomatic/domain/user/UserSettings.php');
require_once('innomatic/domain/DomainSettings.php');
require_once('innomatic/wui/Wui.php');
require_once('innomatic/wui/widgets/WuiWidget.php');
require_once('innomatic/wui/widgets/WuiContainerWidget.php');
require_once('innomatic/wui/dispatch/WuiEventsCall.php');
require_once('innomatic/wui/dispatch/WuiEvent.php');
require_once('innomatic/wui/dispatch/WuiEventRawData.php');
require_once('innomatic/wui/dispatch/WuiDispatcher.php');
        $this->_localeCatalog = new LocaleCatalog(
            'innomatic::domain_interface',
            InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getLanguage()
        );
    }

    public function endHelper()
    {
    }
    
    function executesettheme($eventData)
    {    
    	$userCfg = new UserSettings(
    			InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess(),
    			InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getUserId());
    	$userCfg->setKey('wui-theme', $eventData['theme']);
    
    	if (
    			User::isAdminUser(
    					InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getUserName(),
    					InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDomainId()
    			)
    	) {
    		$domainCfg = new DomainSettings(
    				InnomaticContainer::instance(
    						'innomaticcontainer'
    				)->getCurrentDomain()->getDataAccess());
    		$domainCfg->EditKey('wui-theme', $eventData['theme']);
    	}
    
    	$wui = Wui::instance('wui');
    	$wui->setTheme($eventData['theme']);
    
    	WebAppContainer::instance(
    	'webappcontainer'
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
    
    function executesettheme2($eventData)
    {    
    	$this->status = $this->_localeCatalog->getStr('themeset_status');
    	$this->javascript = "parent.frames.menu.location.reload();\nparent.frames.header.location.reload()";
    	
    	$this->setChanged();
    	$this->notifyObservers('status');
    	$this->notifyObservers('javascript');
    }
    
    function executesetdesktop($eventData)
    {    
    	$userCfg = new UserSettings(
    			InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess(),
    			InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getUserId());
    	$userCfg->setKey('desktop-layout', $eventData['layout']);
    
    	if (
    			User::isAdminUser(
    					InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getUserName(),
    					InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDomainId()
    			)
    	) {
    		$domainCfg = new DomainSettings(
    				InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess()
    		);
    		$domainCfg->EditKey('desktop-layout', $eventData['layout']);
    	}
    
    	WebAppContainer::instance(
    	'webappcontainer'
    			)->getProcessor()->getResponse()->addHeader(
    			'Location',
    			WuiEventsCall::buildEventsCallString(
    			'',
    			array(
    			array('view', 'desktop', ''),
    			array('action', 'setdesktop2', '')
    			)
    			)
    			);
    }
    
    function execute_setdesktop2($eventData)
    {
    	$this->status = $this->_localeCatalog->getStr('desktopset_status');
    	require_once('innomatic/webapp/WebAppContainer.php');
    	$uri = dirname(WebAppContainer::instance('webappcontainer')->getProcessor()->getRequest()->getRequestURI());
    	$this->javascript = "parent.location.href='".$uri."'";
    	
    	$this->setChanged();
    	$this->notifyObservers('status');
    	$this->notifyObservers('javascript');
    }
    
    function executesetlanguage($eventData)
    {
    	$userCfg = new UserSettings(
    			InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess(),
    			InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getUserId());
    	$userCfg->setKey('desktop-language', $eventData['language']);
    
    	$domainSets = new DomainSettings(
    			InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess()
    	);
    
    	if (
    			User::isAdminUser(
    					InnomaticContainer::instance(
    							'innomaticcontainer'
    					)->getCurrentUser()->getUserName(),
    					InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDomainId()
    			)
    	) {
    		$domainSets->EditKey('desktop-language', $eventData['language']);
    	}
    
    	$this->javascript = 'parent.frames.menu.location.reload()';
    
    	$this->status = $this->_localeCatalog->getStr('languageset_status');
    	
    	$this->setChanged();
    	$this->notifyObservers('status');
    	$this->notifyObservers('javascript');
    }
    
    function executesetcountry($eventData)
    {    
    	$userCfg = new UserSettings(
    			InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess(),
    			InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getUserId());
    	$userCfg->setKey('desktop-country', $eventData['country']);
    
    	$domainSettings = new DomainSettings(
    			InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess()
    	);
    
    	if (
    			User::isAdminUser(
    					InnomaticContainer::instance(
    							'innomaticcontainer'
    					)->getCurrentUser()->getUserName(),
    					InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDomainId()
    			)
    	) {
    		$domainSettings->EditKey('desktop-country', $eventData['country']);
    	}
    
    	$this->javascript = 'parent.frames.menu.location.reload()';
    
    	$this->status = $this->_localeCatalog->getStr('countryset_status');
    	
    	$this->setChanged();
    	$this->notifyObservers('status');
    	$this->notifyObservers('javascript');
    }
}
