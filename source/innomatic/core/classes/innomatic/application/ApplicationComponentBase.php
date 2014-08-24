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
 */
namespace Innomatic\Application;

/**
 * @since 5.0.0 introduced
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 */
interface ApplicationComponentBase
{
	/**
	 * Tells the component type identifier string.
	 *
	 */
	public static function getType();
	
	/**
	 * Tells the component priority over the other ones.
	 *
	*/
	public static function getPriority();
	
	/**
	 * Tells if the component supports the domain abilitation.
	 *
	*/
	public static function getIsDomain();
	
	/**
	 * Tells if the component supports the override feature.
	 *
	*/
	public static function getIsOverridable();
}