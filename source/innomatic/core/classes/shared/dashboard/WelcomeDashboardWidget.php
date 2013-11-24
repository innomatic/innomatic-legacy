<?php

require_once 'innomatic/desktop/dashboard/DashboardWidget.php';

class WelcomeDashboardWidget extends DashboardWidget {
	public function getWidgetXml() {
		// Get the message of the day
		$message = InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getMotd();
		
		// Check if the motd is empty. If it is empty, get the generic welcome message
		if (!strlen($message)) {
			require_once('innomatic/locale/LocaleCatalog.php');
			$catalog = new LocaleCatalog(
					'innomatic::dashboard_welcome',
					InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getLanguage()
			);
			
			$message = $catalog->getStr('welcome_title');
		}	
		
		require_once 'shared/wui/WuiXml.php';
		$xml = '<label><args><label>'.WuiXml::cdata($message).'</label></args></label>';
		
		return $xml;
	}
	
	public function getWidth() {
		return 1;
	}
	
	public function getHeight() {
		return 120;
	}
}