<?php
namespace Shared\Dashboard;

use \Innomatic\Core\InnomaticContainer;

class WelcomeDashboardWidget extends \Innomatic\Desktop\Dashboard\DashboardWidget
{
    public function getWidgetXml()
    {
        // Get the message of the day
        $message = InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getMotd();

        // Check if the motd is empty. If it is empty, get the generic welcome message
        if (!strlen($message)) {
            $catalog = new \Innomatic\Locale\LocaleCatalog(
                    'innomatic::dashboard_welcome',
                    InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getLanguage()
            );

            $message = $catalog->getStr('welcome_message');
        }

        require_once 'shared/wui/WuiXml.php';
        $xml = '<label><args><label>'.WuiXml::cdata($message).'</label></args></label>';

        return $xml;
    }

    public function getWidth()
    {
        return 1;
    }

    public function getHeight()
    {
        return 60;
    }
}
