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
namespace Innomatic\Application;

/**
 * This interface defines the basic methods that must be implemented by
 * component handlers.
 *
 * @since 5.0.0 introduced
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 */
interface ApplicationComponentBase
{
    /* public getType() {{{ */
    /**
     * Gets the component type identifier string.
     *
     * @static
     * @access public
     * @return string
     */
    public static function getType();
    /* }}} */

    /* public getPriority() {{{ */
    /**
     * Gets the priority applied when executing operations with
     * the component.
     *
     * @static
     * @access public
     * @return integer
     */
    public static function getPriority();
    /* }}} */

    /* public getIsDomain() {{{ */
    /**
     * Checks if the component can be enabled to/disabled from tenants.
     *
     * @static
     * @access public
     * @return boolean
     */
    public static function getIsDomain();
    /* }}} */

    /* public getIsOverridable() {{{ */
    /**
     * Checks if the component supports custom overrides.
     *
     * @static
     * @access public
     * @return boolean
     */
    public static function getIsOverridable();
    /* }}} */
}
