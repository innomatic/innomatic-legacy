<?php

require_once 'innomatic/desktop/dashboard/DashboardWidget.php';

class WelcomeDashboardWidget extends DashboardWidget {
	public function getWidget() {
		$xml = '<label><args><label>Welcome to Innomatic</label></args></label>';
		
		return $xml;
	}
}