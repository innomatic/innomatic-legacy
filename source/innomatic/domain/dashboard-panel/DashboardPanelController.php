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
 * @license    http://www.innomaticplatform.com/license/   BSD License
 * @link       http://www.innomaticplatform.com
 * @since      Class available since Release 6.1
*/

class DashboardPanelController extends \Innomatic\Desktop\Panel\PanelController
{
    public function update($observable, $arg = '')
    {
    }

    public function getWidgetsList()
    {
        $domain_da = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess();

        $perm = new \Innomatic\Desktop\Auth\DesktopPanelAuthorizator($domain_da, \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getGroup());

        // Extract the list of all the widgets
        $widget_query = $domain_da->execute('SELECT * FROM domain_dashboards_widgets');

        while (!$widget_query->eof) {
            $panel = $widget_query->getFields('panel');

            // Do not show widgets tied to a panel when the panel is not accessible to the current user
            if (strlen($panel)) {
                $node_id = $perm->getNodeIdFromFileName($panel);
                if ( $perm->check( $node_id, \Innomatic\Desktop\Auth\DesktopPanelAuthorizator::NODETYPE_PAGE ) == \Innomatic\Desktop\Auth\DesktopPanelAuthorizator::NODE_NOTENABLED ) {
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
