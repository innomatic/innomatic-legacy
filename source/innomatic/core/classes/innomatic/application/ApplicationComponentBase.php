<?php
namespace Innomatic\Application;

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