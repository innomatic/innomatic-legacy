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

abstract class DashboardWidget {
	/**
	 * Returns the widget WUI xml definition.
	 * 
	 * @since 6.1
	 * @author Alex Pagnoni <alex.pagnoni@innoteam.it>
	 */
	public abstract function getWidgetXml();
	
	/**
	 * Returns widget widget in units (not pixels).
	 * Each unit is multiplied per the default unit width by the dashboard.
	 * 
	 * @since 6.1
	 * @author Alex Pagnoni <alex.pagnoni@innoteam.it>
	 */
	public abstract function getWidth();
	
	/**
	 * Returns widget height in pixels.
	 * 
	 * @since 6.1
	 * @author Alex Pagnoni <alex.pagnoni@innoteam.it>
	 */
	public abstract function getHeight();
	
	public function getDefaultWidth() {
		return 400;
	}
	
	public function getDefaultHeight() {
		return 250;
	}
	
}