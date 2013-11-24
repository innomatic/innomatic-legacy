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
		$domain_da = InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess();

		require_once('innomatic/domain/user/Permissions.php');
		$perm = new Permissions($domain_da, InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getGroup());

		// Extract the list of all the widgets
    	$widget_query = $domain_da->execute('SELECT * FROM domain_dashboards_widgets');
    	
    	while (!$widget_query->eof) {
    		$panel = $widget_query->getFields('panel');
    		
    		// Do not show widgets tied to a panel when the panel is not accessible to the current user
    		if (strlen($panel)) {
    			$node_id = $perm->getNodeIdFromFileName($panel);
    			if ( $perm->check( $node_id, Permissions::NODETYPE_PAGE ) == Permissions::NODE_NOTENABLED ) {
    				continue;
    			}
    		}

    		// Add current widget
    		$widgets[] = array(
    			'name' => $widget_query->getFields('name'),
    			'file' => $widget_query->getFields('file'),
    			'class' => $widget_query->getFields('class'),
    			'panel' => $panel,
    			'catalog' => $widget_query->getFields('catalog'),
    			'title' => $widget_query->getFields('title')
    		);
    		
    		$widget_query->moveNext();
    	}
    	//$widgets['dashboard:motd'] = array('name' => 'motd', 'panel' => 'dashboard', 'catalog' => 'innomatic::domain_dashboard', 'title' => 'motd_widget');
    	
    	return $widgets;
    }
}
