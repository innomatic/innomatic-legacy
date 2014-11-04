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
namespace Innomatic\Desktop\Auth;

/**
 * Helper interface for desktop authentication and authorization.
 *
 * @copyright  2000-2012 Innomatic Company
 * @license    http://www.innomatic.io/license/ New BSD License
 * @link       http://www.innomatic.io
 * @since      5.0.0
 * @package    Desktop
 */
interface DesktopAuthenticatorHelper
{
    public function authenticate();

    public function authorize();
}
