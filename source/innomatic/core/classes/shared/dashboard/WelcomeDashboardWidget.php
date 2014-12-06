<?php
namespace Shared\Dashboard;

use \Innomatic\Core\InnomaticContainer;

class WelcomeDashboardWidget extends \Innomatic\Desktop\Dashboard\DashboardWidget
{
    public function getWidgetXml()
    {
        $container = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');
        
        // Get the message of the day
        $message = $container->getCurrentDomain()->getMotd();

        // Check if the motd is empty. If it is empty, get the generic welcome message
        if (!strlen($message)) {
            $catalog = new \Innomatic\Locale\LocaleCatalog(
                    'innomatic::dashboard_welcome',
                    $container->getCurrentUser()->getLanguage()
            );

            $message = $catalog->getStr('welcome_message');
        }

        $xml = '<label><args><label>'.\Shared\Wui\WuiXml::cdata(nl2br($message)).'</label></args></label>';

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
