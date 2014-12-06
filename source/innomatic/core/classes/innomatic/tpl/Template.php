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
 * @since      Class available since Release 5.0
*/

namespace Innomatic\Tpl;

/**
 * Generic template interface.
 *
 * Innomatic provides a generic template interface so that a contract for
 * templates system is available by default.
 *
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 * @copyright Copyright 2012 Innomatic Company
 * @since 1.1
 */
interface Template
{
    /**
     * Initializes the template engine.
     *
     * @since 1.2
     * @param string $file Template file.
     */
    public function __construct($file);

    /**
     * Sets the value for a certain tag.
     *
     * @since 1.2
     * @param string $name Tag name.
     * @param string $value Tag value.
     */
    public function set($name, $value);

    /**
     * Parses the given template and returns the parsed result.
     *
     * @since 1.1
     * @return mixed
     */
    public function parse();

    /**
     * Returns a list of the set tag names.
     *
     * @since 6.1
     * @return array
     */
    public function getTags();
}
