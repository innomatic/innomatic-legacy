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
namespace Innomatic\Desktop\Auth;

/**
 * Factory for DesktopAuthenticator classes.
 *
 * @copyright  2000-2012 Innoteam Srl
 * @license    http://www.innomaticplatform.com/license/   BSD License
 * @version    Release: @package_version@
 * @link       http://www.innomaticplatform.com
 * @since      5.0.0
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
