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
 * @since      Class available since Release 6.1
*/

require_once('innomatic/desktop/panel/PanelActions.php');

class DashboardPanelActions extends PanelActions
{
    private $_localeCatalog;

    public function __construct(PanelController $controller)
    {
        parent::__construct($controller);
    }

    public function beginHelper()
    {
		require_once('innomatic/locale/LocaleCatalog.php');
		require_once('innomatic/wui/Wui.php');
		require_once('innomatic/wui/dispatch/WuiEventsCall.php');
		require_once('innomatic/wui/dispatch/WuiEvent.php');
        $this->_localeCatalog = new LocaleCatalog(
            'innomatic::domain_dashboard',
            InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getLanguage()
        );
    }

    public function endHelper()
    {
    }
    
    public function ajaxGetDashboardWidget($panel, $name) {
    	$objResponse = new XajaxResponse();

    	$xml = '<label><args><label>Benvenuto su Innomatic</label></args></label>';
    	
    	$html = WuiXml::getContentFromXml('', $xml);
    	
    	//$widgets = $this->getController()->getWidgetsList();
    	
    	$objResponse->addAssign('widget_dashboard_motd', 'innerHTML', $html);
    	//$objResponse->addAssign('widget_'.$panel.'_'.$name, 'innerHTML', $html);
    	 
    	return $objResponse;
    }
}
