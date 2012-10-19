<?php
/**
 * Innomatic
 *
 * LICENSE 
 * 
 * This source file is subject to the new BSD license that is bundled 
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2012 Innoteam S.r.l.
 * @license    http://www.innomatic.org/license/   BSD License
 * @link       http://www.innomatic.org
 * @since      Class available since Release 5.0
*/

/**
 * Wui Exception.
 *
 * @package WUI
 */
class WuiException extends Exception
{
    const INVALID_APPLICATION = 1;
    const NO_VIEW_DEFINED = 2;
    const MISSING_VIEWS_FILE = 3;
    const MISSING_VIEWS_CLASS = 4;
    const MISSING_ACTIONS_FILE = 5;
    const MISSING_ACTIONS_CLASS = 6;
    const MISSING_CONTROLLER_FILE = 5;
    const MISSING_CONTROLLER_CLASS = 6;
    const UNABLE_TO_RENDER = 7;
    const LOADALLWIDGETS_UNAVAILABLE = 8;
    const MISSING_WIDGET_FILE = 9;
    const INVALID_INNOMATIC_DATAACCESS = 10;
    const UNABLE_TO_RETRIEVE_WIDGETS_LIST = 11;
}
