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

require_once('innomatic/desktop/auth/DesktopAuthenticatorHelper.php');

/**
 * @package Desktop
 */
class DesktopBaseAuthenticatorHelper implements DesktopAuthenticatorHelper
{
    public function authenticate()
    {
        return true;
    }

    public function authorize()
    {
    }
}
