<?php
/**
 * Innomatic
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2014 Innoteam Srl
 * @license    http://www.innomaticplatform.com/license/ New BSD License
 * @link       http://www.innomaticplatform.com
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
        $container = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');
        $objResponse = new XajaxResponse();
        $xml = '<void/>';
        
        $domain_da = $container->getCurrentDomain()->getDataAccess();

        $perm = new \Innomatic\Desktop\Auth\DesktopPanelAuthorizator($domain_da, $container->getCurrentUser()->getGroup());

        // Check if the widget exists in the widgets list
        $widget_query = $domain_da->execute('SELECT * FROM domain_dashboards_widgets WHERE name='.$domain_da->formatText($name));

        if ($widget_query->getNumberRows() > 0) {
            $allowed = true;
            $panel = $widget_query->getFields('panel');

            // Do not show widgets tied to a panel when the panel is not accessible to the current user
            if (strlen($panel)) {
                $node_id = $perm->getNodeIdFromFileName($panel);
                if ( $perm->check( $node_id, \Innomatic\Desktop\Auth\DesktopPanelAuthorizator::NODETYPE_PAGE ) == \Innomatic\Desktop\Auth\DesktopPanelAuthorizator::NODE_NOTENABLED ) {
                    $allowed = false;
                }
            }

            if ($allowed) {
            	$class = $widget_query->getFields('class');

                // Check if the class exists
                if (class_exists($class, true)) {
                    // Fetch the widget xml definition
                    $widget = new $class;
                    $xml = $widget->getWidgetXml();
                }
            }
        }

        // Create the widget html and send it to the dashboard
        $html = WuiXml::getContentFromXml('', $xml);
        $objResponse->addAssign('widget_'.$name, 'innerHTML', $html);

        return $objResponse;
    }
}
