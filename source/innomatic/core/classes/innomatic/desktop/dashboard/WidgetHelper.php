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
 */
namespace Innomatic\Desktop\Dashboard;

use \Innomatic\Desktop\Auth\DesktopPanelAuthorizator;

/**
 * Helper methods for desktop dashboard widgets.
 *
 * @since 7.0.0
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 */
class WidgetHelper
{
    /**
     * Builds the WUI xml string for the given dashboard widget.
     *
     * @since 7.0.0
     * @param string $name Widget name.
     * @return string
     */
    public static function getWidgetXml($name)
    {
        // Default WUI XML definition
        $xml       = '<void/>';
        $container = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');
        $domain_da = $container->getCurrentDomain()->getDataAccess();
        $perm      = new DesktopPanelAuthorizator($domain_da, $container->getCurrentUser()->getGroup());

        // Check if the widget exists in the widgets list
        $widget_query = $domain_da->execute(
'SELECT *
FROM domain_dashboards_widgets
WHERE name='.$domain_da->formatText($name)
            );

        if ($widget_query->getNumberRows() > 0) {
            $allowed = true;
            $panel   = $widget_query->getFields('panel');

            // Do not show widgets tied to a panel when the panel is not accessible to the current user
            if (strlen($panel)) {
                // Check permission
                $node_id = $perm->getNodeIdFromFileName($panel);
                if (
                    $perm->check($node_id, DesktopPanelAuthorizator::NODETYPE_PAGE )
                    == DesktopPanelAuthorizator::NODE_NOTENABLED
                ) {
                    $allowed = false;
                }
            }

            // Check if the current widget is allowed
            if ($allowed) {
                // Get class field
                $class = $widget_query->getFields('class');

                // Check if the class exists
                // @todo must be updated to new class definition with namespace
                if (class_exists($class, true)) {
                    // Fetch the widget xml definition
                    $widget = new $class;
                    $xml    = $widget->getWidgetXml();
                }
            }
        }

        return $xml;
    }

    /**
     * Returns the list of the available dashboard widgets for the current
     * tenant user.
     *
     * @return array Array of the available widgets.
     */
    public function getWidgetsList()
    {
        $container = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');
        $domain_da = $container->getCurrentDomain()->getDataAccess();
        $perm      = new DesktopPanelAuthorizator($domain_da, $container->getCurrentUser()->getGroup());
        $widgets   = array();

        // Extract the list of all the widgets
        $widget_query = $domain_da->execute('SELECT * FROM domain_dashboards_widgets');

        while (!$widget_query->eof) {
            $panel = $widget_query->getFields('panel');

            // Do not show widgets tied to a panel when the panel is not accessible to the current user
            if (strlen($panel)) {
                $node_id = $perm->getNodeIdFromFileName($panel);
                if (
                    $perm->check($node_id, DesktopPanelAuthorizator::NODETYPE_PAGE )
                    == DesktopPanelAuthorizator::NODE_NOTENABLED
                ) {
                	$widget_query->moveNext();
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

        return $widgets;
    }
}
