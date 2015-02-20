<?php
/**
 * Innomatic
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2014 Innomatic Company
 * @license    http://www.innomatic.io/license/ New BSD License
 * @link       http://www.innomatic.io
 * @since      Class available since Release 6.1
*/

use \Innomatic\Core\InnomaticContainer;
use \Innomatic\Wui\Widgets;
use \Innomatic\Wui\Dispatch;
use \Innomatic\Locale\LocaleCatalog;
use \Innomatic\Domain\User;
use \Shared\Wui;

class DashboardPanelActions extends \Innomatic\Desktop\Panel\PanelActions
{
    public $localeCatalog;
    protected $container;

    public function __construct(\Innomatic\Desktop\Panel\PanelController $controller)
    {
        parent::__construct($controller);
    }

    public function beginHelper()
    {
        $this->container = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');

        $this->localeCatalog = new LocaleCatalog(
            'innomatic::domain_dashboard',
            $this->container->getCurrentUser()->getLanguage()
        );
    }

    public function endHelper()
    {
    }

    public static function ajaxGetDashboardWidget($name)
    {
        $objResponse = new XajaxResponse();

        $xml = \Innomatic\Desktop\Dashboard\WidgetHelper::getWidgetXml($name);

        // Create the widget html and send it to the dashboard
        $html = WuiXml::getContentFromXml('', $xml);
        $objResponse->addAssign('widget_'.$name, 'innerHTML', $html);

        return $objResponse;
    }
}
