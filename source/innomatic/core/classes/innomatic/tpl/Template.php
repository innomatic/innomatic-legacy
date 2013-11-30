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
 * @since      Class available since Release 5.0
*/
//namespace Innomatic\Template;

/**
 * Generic template interface.
 *
 * Innomatic provides a generic template interface so that a contract for
 * templates system is available by default.
 *
 * @author Alex Pagnoni <alex.pagnoni@innoteam.it>
 * @copyright Copyright 2012 Innoteam Srl
 * @since 1.1
 */
interface Template
{
    /**
     * Initializes the template engine.
     *
     * @access public
     * @since 1.2
     * @param string $file Template file.
     */
    public function __construct($file);

    /**
     * Sets the value for a certain tag.
     *
     * @access public
     * @since 1.2
     * @param string $name Tag name.
     * @param string $value Tag value.
     */
    public function set($name, $value);

    /**
     * Parses the given template and returns the parsed result.
     *
     * @access public
     * @since 1.1
     * @return mixed
     */
    public function parse();

    /**
     * Returns a list of the set tag names.
     *
     * @access public
     * @since 6.1
     * @return array
     */
    public function getTags();
}
