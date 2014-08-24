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
 * @since      Class available since Release 5.0
*/
namespace Innomatic\Desktop\Auth;

/**
 *
 * @package Desktop
 */
class DesktopBaseAuthenticatorHelper implements \Innomatic\Desktop\Auth\DesktopAuthenticatorHelper
{

    public function authenticate()
    {
        return true;
    }

    public function authorize()
    {}
}
