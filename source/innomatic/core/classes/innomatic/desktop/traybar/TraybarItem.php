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
 * @license    http://www.innomaticplatform.com/license/ New BSD License
 * @link       http://www.innomaticplatform.com
 * @since      Class available since Release 6.4.0
 */
namespace Innomatic\Desktop\Traybar;

abstract class TraybarItem
{
    /**
     * Prepares the traybar item if needed.
     * It may contains initialization routines like AJAX handling, JQuery
     * libraries, etc.
     *
     * @since 6.4.0
     * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
     */
    public function prepare()
    {
    }

    /**
     * Returns the item HTML.
     *
     * @since 6.4.0
     * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
     */
    abstract public function getHtml();

}
