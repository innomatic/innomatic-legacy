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

require_once('innomatic/desktop/panel/PanelActions.php');

class DashboardPanelActions extends PanelActions
{
    private $_localeCatalog;

    public function __construct(PanelController $controller)
    {
        parent::__construct($controller);
    }

    public function beginHelper()
    {
        require_once('innomatic/locale/LocaleCatalog.php');
        require_once('innomatic/wui/Wui.php');
        require_once('innomatic/wui/dispatch/WuiEventsCall.php');
        require_once('innomatic/wui/dispatch/WuiEvent.php');
        $this->_localeCatalog = new LocaleCatalog(
            'innomatic::domain_dashboard',
            InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getLanguage()
        );
    }

    public function endHelper()
    {
    }

    public function ajaxGetDashboardWidget($name)
    {
        $objResponse = new XajaxResponse();
        $xml = '<void/>';

        $domain_da = InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess();

        require_once('innomatic/domain/user/Permissions.php');
        $perm = new Permissions($domain_da, InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getGroup());

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
                // Check if the class file exists
                if (file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/classes/shared/dashboard/' . $widget_query->getFields('file'))) {
                    require_once(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/classes/shared/dashboard/' . $widget_query->getFields('file'));

                    $class = $widget_query->getFields('class');

                    // Check if the class exists
                    if (class_exists($class, false)) {
                        // Fetch the widget xml definition
                        $widget = new $class;
                        $xml = $widget->getWidgetXml();
                    }
                }
            }
        }

        // Create the widget html and send it to the dashboard
        $html = WuiXml::getContentFromXml('', $xml);
        $objResponse->addAssign('widget_'.$name, 'innerHTML', $html);

        return $objResponse;
    }
}
