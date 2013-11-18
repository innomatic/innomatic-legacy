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

require_once('innomatic/webapp/WebAppSession.php');

/**
 * @author Alex Pagnoni <alex.pagnoni@innoteam.it>
 * @copyright Copyright 2012 Innoteam Srl
 * @since 1.0
 */
class PHPSession implements WebAppSession
{
    protected $_id;

    public function start($id = '')
    {
        if (strlen($id)) {
            $this->_id = $id;
            session_id($id);
        } else {
            $this->_id = session_id();
        }
        if (!headers_sent()) {
            session_start();
        }
    }

    public function put($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    public function get($key)
    {
        return (isset($_SESSION[$key]) ? $_SESSION[$key] : '');
    }

    public function remove($key)
    {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    public function isValid($key)
    {
        return isset ($_SESSION[$key]);
    }

    public function getId()
    {
        return $this->_id;
    }

    public function setSavePath($path)
    {
        ini_set('session.save_path', $path);
    }

    public function setLifeTime($lifetime)
    {
        ini_set('session.gc_maxlifetime', $lifetime);
        ini_set('session.cookie_lifetime', $lifetime);
    }

    public function destroy()
    {
        // Unset all of the session variables.
        $_SESSION = array();

        // If it's desired to kill the session, also delete the session cookie.
        // Note: This will destroy the session, and not just the session data!
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 42000, '/');
        }

        // Finally, destroy the session.
        session_destroy();
    }
}
