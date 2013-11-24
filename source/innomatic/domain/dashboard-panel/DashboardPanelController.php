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

require_once('innomatic/desktop/panel/PanelController.php');

class DashboardPanelController extends PanelController
{
    public function update($observable, $arg = '')
    {
    }
    
    public function getWidgetsList() {
    	$widgets['dashboard:motd'] = array('name' => 'motd', 'panel' => 'dashboard', 'ajaxcall' => 'xajax_getMotdWidget()', 'catalog' => 'innomatic::domain_dashboard', 'title' => 'motd_widget');
    	
    	return $widgets;
    }
}
