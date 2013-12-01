<?php
/**
 * Innomatic
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2013 Innoteam Srl
 * @license    http://www.innomatic.org/license/   BSD License
 * @link       http://www.innomatic.org
 * @since      Class available since Release 6.1
*/
use \Innomatic\Core\InnomaticContainer;
use \Innomatic\Wui\Wui;
use \Innomatic\Wui\Widgets;
use \Innomatic\Wui\Dispatch;
use \Innomatic\Locale\LocaleCatalog;
use \Innomatic\Domain\User;
use \Shared\Wui;

class DashboardPanelViews extends \Innomatic\Desktop\Panel\PanelViews
{
    public $wuiPage;
    public $wuiMainvertgroup;
    public $wuiMainframe;
    public $wuiMainstatus;
    public $wuiTitlebar;
    protected $localeCatalog;

    public function update($observable, $arg = '')
    {
    }

    public function beginHelper()
    {
        $this->localeCatalog = new LocaleCatalog(
            'innomatic::domain_dashboard',
            InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getLanguage()
        );

        $this->_wuiContainer->loadAllWidgets();

$this->wuiPage = new WuiPage('page', array('title' => $this->localeCatalog->getStr('dashboard_pagetitle')));
$this->wuiMainvertgroup = new WuiVertgroup('mainvertgroup');
$this->wuiTitlebar = new WuiTitleBar(
                               'titlebar',
                               array('title' => $this->localeCatalog->getStr('dashboard_title'), 'icon' => 'elements')
                              );
$this->wuiMainvertgroup->addChild($this->wuiTitlebar);

$this->wuiMainframe = new WuiVertframe('mainframe');
    }

    public function endHelper()
    {
        // Page render
        //
        $this->wuiMainvertgroup->addChild($this->wuiMainframe);
        $this->wuiPage->addChild($this->wuiMainvertgroup);
        $this->_wuiContainer->addChild($this->wuiPage);
    }

    public function viewDefault($eventData)
    {
        $def_width = 400;
        $def_height = 250;

        $widgets = $this->getController()->getWidgetsList();

        $widget_counter = 0;
        $columns = 3;

        $start_column = true;
        $end_column = false;
        $rows_per_column = floor(count($widgets) / $columns) + (count($widgets) % $columns > 0 ? 1 : 0);

        $wui_xml = '<horizgroup><children>';

        foreach ($widgets as $widget) {
            // If this is the start of a column, add the vertical group opener
            if ($start_column) {
                $wui_xml .= '<vertgroup><children>';
                $start_column = false;
            }
            // Add ajax setup call
            \Innomatic\Wui\Wui::instance('wui')->registerAjaxSetupCall('xajax_GetDashboardWidget(\''.$widget['name'].'\')');

            $width = 0;
            $height = 0;

            $class = $widget['class'];

            // Check if the class exists            
            if (class_exists($class, true)) {
                // Fetch the widget xml definition
                $widget_obj = new $class;
                $width = $widget_obj->getWidth() * $def_width;
                $height = $widget_obj->getHeight();
            }

            // Check width and height parameters
            if ($width == 0) $width = $def_width;
            if ($height == 0) $height = $def_height;

            // Widget title
            $widget_locale = new LocaleCatalog(
                    $widget['catalog'],
                    InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getLanguage()
            );
            $headers = array();
            $headers[0]['label'] = $widget_locale->getStr($widget['title']);

            // Draw the widget
            $wui_xml .= '<table halign="left" valign="top"><args><headers type="array">'.WuiXml::encode($headers).'</headers></args><children><vertgroup row="0" col="0" halign="left" valign="top"><args><width>'.$width.'</width><height>'.$height.'</height><groupvalign>top</groupvalign></args><children><divframe><args><id>widget_'.$widget['name'].'</id><width>300</width></args><children><void/></children></divframe></children></vertgroup></children></table>';

            $widget_counter++;

            // Check if this last widget for each column
            if ($widget_counter % $rows_per_column == 0) {
                $end_column = true;
            }

            // If this is the last widget, end the column anyway
            if ($widget_counter == count($widgets)) {
                $end_column = true;
            }

            // If this the end of a column, close the vertical group
            if ($end_column) {
                $wui_xml .= '</children></vertgroup>';
                $start_column = true;
                $end_column = false;
            }
        }

        $wui_xml .= '</children></horizgroup>';

        $this->wuiMainframe->addChild(new WuiXml('', array('definition' => $wui_xml)));
    }
}
