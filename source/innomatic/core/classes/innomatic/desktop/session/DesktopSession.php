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
namespace Innomatic\Desktop\Session;

use \Innomatic\Core\InnomaticContainer;

/**
 * Session class for the Desktop.
 *
 * Desktop sessions supports the default $_SESSION PHP superglobals.
 *
 * The session lifetime is definable with the DesktopSessionLifetime parameter
 * in the core/conf/innomatic.ini configuration file.
 *
 * @copyright  2000-2012 Innoteam Srl
 * @license    http://www.innomatic.org/license/   BSD License
 * @link       http://www.innomatic.org
 * @since      Class available since Release 5.0
 * @package    Desktop
 */
class DesktopSession implements \Innomatic\Webapp\WebAppSession
{
    /**
     * Unique id of the session.
     *
     * @var string
     */
    protected $_id;

    public function start($id = '')
    {
        if (strlen($id)) {
            session_id($id);
            $this->_id = $id;
        } else {
            $this->_id = session_id();
        }

        // This must be set before session_start
        if (
            strlen(
                \Innomatic\Core\InnomaticContainer::instance(
                    '\Innomatic\Core\InnomaticContainer'
                )->getConfig()->value(
                    'DesktopSessionLifetime'
                )
            )
        ) {
            $lifetime = \Innomatic\Core\InnomaticContainer::instance(
                '\Innomatic\Core\InnomaticContainer'
            )->getConfig()->value(
                'DesktopSessionLifetime'
            ) * 60;
        } else {
            $lifetime = 1440 * 60 * 365; // A year
        }
        ini_set('session.gc_maxlifetime', $lifetime);
        ini_set('session.cookie_lifetime', $lifetime);

        if (
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getState()
            != \Innomatic\Core\InnomaticContainer::STATE_SETUP
        ) {
            ini_set(
                'session.save_path',
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome()
                . 'core/temp/phpsessions/'
            );
        }
        session_start();
    }

    public function put($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    public function get($key)
    {
        return(isset($_SESSION[$key]) ? $_SESSION[$key] : '');
    }

    public function remove($key)
    {
        if (isset($_SESSION[$key]))
        unset($_SESSION[$key]);
    }

    public function isValid($key)
    {
        return isset($_SESSION[$key]);
    }

    public function getId()
    {
        return $this->_id;
    }

    public function destroy()
    {

    }
}
