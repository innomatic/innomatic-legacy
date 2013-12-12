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

use \Innomatic\Core\InnomaticContainer;
use \Innomatic\Wui\Widgets;
use \Innomatic\Wui\Dispatch;
use \Innomatic\Locale\LocaleCatalog;
use \Innomatic\Domain\User;
use \Shared\Wui;

class DashboardPanelActions extends \Innomatic\Desktop\Panel\PanelActions
{
    private $_localeCatalog;

    public function __construct(\Innomatic\Desktop\Panel\PanelController $controller)
    {
        parent::__construct($controller);
    }

    public function beginHelper()
    {
        $this->_localeCatalog = new LocaleCatalog(
            'innomatic::domain_dashboard',
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getLanguage()
        );
    }

    public function endHelper()
    {
    }

    public function ajaxGetDashboardWidget($name)
    {
        $objResponse = new XajaxResponse();
        $xml = '<void/>';

        $domain_da = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess();

        $perm = new Permissions($domain_da, \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getGroup());

        // Check if the widget exists in the widgets list
        $widget_query = $domain_da->execute('SELECT * FROM domain_dashboards_widgets WHERE name='.$domain_da->formatText($name));

        if ($widget_query->getNumberRows() > 0) {
            $allowed = true;
            $panel = $widget_query->getFields('panel');

            // Do not show widgets tied to a panel when the panel is not accessible to the current user
            if (strlen($panel)) {
                $node_id = $perm->getNodeIdFromFileName($panel);
                if ( $perm->check( $node_id, Permissions::NODETYPE_PAGE ) == Permissions::NODE_NOTENABLED ) {
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
