<?php
/**
 * Innomatic
 *
 * LICENSE 
 * 
 * This source file is subject to the new BSD license that is bundled 
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2013 Innoteam Srl
 * @license    http://www.innomatic.org/license/   BSD License
 * @link       http://www.innomatic.org
 * @since      Class available since Release 6.1
*/

require_once('innomatic/desktop/panel/PanelViews.php');

class DashboardPanelViews extends PanelViews
{
    public $wuiPage;
    public $wuiMainvertgroup;
    public $wuiMainframe;
    public $wuiMainstatus;
    public $wuiTitlebar;
    protected $_localeCatalog;
    
    public function update($observable, $arg = '')
    {
    }

    public function beginHelper()
    {
		require_once('innomatic/locale/LocaleCatalog.php');
		require_once('innomatic/wui/Wui.php');
		require_once('innomatic/wui/widgets/WuiWidget.php');
		require_once('innomatic/wui/widgets/WuiContainerWidget.php');
		require_once('innomatic/wui/dispatch/WuiEventsCall.php');
		require_once('innomatic/wui/dispatch/WuiEvent.php');
		require_once('innomatic/wui/dispatch/WuiEventRawData.php');
		require_once('innomatic/wui/dispatch/WuiDispatcher.php');
        $this->_localeCatalog = new LocaleCatalog(
            'innomatic::domain_dashboard',
            InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getLanguage()
        );

        $this->_wuiContainer->loadAllWidgets();

$this->wuiPage = new WuiPage('page', array('title' => $this->_localeCatalog->getStr('dashboard_pagetitle')));
$this->wuiMainvertgroup = new WuiVertGroup('mainvertgroup');
$this->wuiTitlebar = new WuiTitleBar(
                               'titlebar',
                               array('title' => $this->_localeCatalog->getStr('dashboard_title'), 'icon' => 'elements')
                              );
$this->wuiMainvertgroup->addChild($this->wuiTitlebar);

$this->wuiMainframe = new WuiVertFrame('mainframe');
    }

    public function endHelper()
    {
        // Page render
        //
        $this->wuiMainvertgroup->addChild($this->wuiMainframe);
        $this->wuiPage->addChild($this->wuiMainvertgroup);
        $this->_wuiContainer->addChild($this->wuiPage);
    }

    public function viewDefault($eventData)
    {
    	$widgets = $this->getController()->getWidgetsList();
    	
    	$widget_counter = 0;
    	$row_counter = 0;
    	$columns = 2;
    	 
    	$wui_xml = '<grid><children>';
    	foreach ($widgets as $widget) {
    		// Add ajax setup call
    		Wui::instance('wui')->registerAjaxSetupCall('xajax_GetDashboardWidget(\''.$widget['panel'].'\', \''.$widget['name'].'\')');
    		
    		$col = $widget_counter % $columns;
    		
    		$wui_xml .= '<table row="'.$row_counter.'" col="'.$col.'"><args></args><children><divframe row="0" col="0"><args><id>widget_'.$widget['panel'].'_'.$widget['name'].'</id><width>300</width></args><children><void/></children></divframe></children></table>';
    		
    		if (($col == $columns - 1)) {
    			$row_counter++;
    		}
    		$widget_counter++;
    	}
    	$wui_xml .= '</children></grid>';
    	
    	
    	$this->wuiMainframe->addChild(new WuiXml('', array('definition' => $wui_xml)));
    }
}
