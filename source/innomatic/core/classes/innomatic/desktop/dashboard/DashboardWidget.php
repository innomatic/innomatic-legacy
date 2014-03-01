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
 * @license    http://www.innomatic.org/license/   BSD License
 * @link       http://www.innomatic.org
 * @since      Class available since Release 6.1
 */
namespace Innomatic\Desktop\Dashboard;

abstract class DashboardWidget
{
    /**
     * Returns the widget WUI xml definition.
     *
     * @since 6.1
     * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
     */
    abstract public function getWidgetXml();

    /**
     * Returns widget widget in units (not pixels).
     * Each unit is multiplied per the default unit width by the dashboard.
     *
     * @since 6.1
     * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
     */
    abstract public function getWidth();

    /**
     * Returns widget height in pixels.
     *
     * @since 6.1
     * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
     */
    abstract public function getHeight();

    public function getDefaultWidth()
    {
        return 400;
    }

    public function getDefaultHeight()
    {
        return 250;
    }
    
    /**
     * Tells if the widget should be visible.
     * 
     * This is useful when there is some sort of check in order to prevent
     * the widget to be shown, eg. when checking assigned roles.
     * 
     * By default this method returns true and should be extended by
     * widgets handling the above mentioned cases.
     *  
     * @since 6.4.0
     * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
     * @return boolean
     */
    public function isVisible()
    {
        return true;
    }

}
