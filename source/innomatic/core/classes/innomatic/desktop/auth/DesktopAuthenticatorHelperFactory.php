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
namespace Innomatic\Desktop\Auth;

/**
 * Factory for DesktopAuthenticator classes.
 *
 * @copyright  2000-2012 Innoteam Srl
 * @license    http://www.innomatic.org/license/   BSD License
 * @version    Release: @package_version@
 * @link       http://www.innomatic.org
 * @since      Class available since Release 5.0
 * @package    Desktop
 */
class DesktopAuthenticatorHelperFactory
{
    public static function getAuthenticatorHelper($mode)
    {
        switch ($mode) {
            case \Innomatic\Core\InnomaticContainer::MODE_BASE:
                return new DesktopBaseAuthenticatorHelper();
                break;

            case \Innomatic\Core\InnomaticContainer::MODE_DOMAIN:
                return new DesktopDomainAuthenticatorHelper();
                break;

            case \Innomatic\Core\InnomaticContainer::MODE_ROOT:
                // break was intentionally omitted

            default:
                return new DesktopRootAuthenticatorHelper();
                break;
        }
    }
}
