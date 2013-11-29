<?php
/**
 * Object containing the indicator for locating a Module.
 *
 * Modules are located through a Module locator string, contained and parsed by
 * this class.
 *
 * Locator format is the following:
 * <code>
 * module://username:password@host:port/modulelocation
 * </code>
 *
 * Most variations are allowed:
 *
 * Local Module:
 * <code>
 * module:///modulelocation
 * module://username:password@/modulelocation
 * </code>
 *
 * Remote Module:
 * <code>
 * module://username:password@host:port/modulelocation
 * module://host:port/modulelocation
 * module://host/modulelocation
 * </code>
 *
 * Username and password are generally required for accessing a Module.
 *
 * @author Alex Pagnoni <alex.pagnoni@innoteam.it>
 * @copyright Copyright 2004-2013 Innoteam Srl
 * @since 5.1
 */
class ModuleLocator
{
    /**
     * Module server hostname.
     *
     * @access private
     * @var string
     * @since 5.1
     */
    private $host;
    /**
     * Module server port.
     *
     * @access private
     * @var string
     * @since 5.1
     */
    private $port = 80;
    /**
     * Module username.
     *
     * @access private
     * @var string
     * @since 5.1
     */
    private $username;
    /**
     * Module password.
     *
     * @access private
     * @var string
     * @since 5.1
     */
    private $password;
    /**
     * Module name and location.
     *
     * @access private
     * @var string
     * @since 5.1
     */
    private $location;

    /**
     * Class constructor.
     *
     * @access public
     * @param string $locator Locator string.
     * @since 5.1
     */
    public function __construct($locator)
    {
        if (($pos = strpos($locator, '://')) === false) {
            return;
        }

        $protocol = substr($locator, 0, $pos);
        $location = substr($locator, $pos +3);

        if ($protocol != 'module') {
            return;
        }

        // Get (if found): username and password.
        if (($at = strrpos($location, '@')) !== false) {
            $str = substr($location, 0, $at);
            $location = substr($location, $at +1);
            if (($pos = strpos($str, ':')) !== false) {
                $this->username = rawurldecode(substr($str, 0, $pos));
                $this->password = rawurldecode(substr($str, $pos +1));
            } else {
                $this->username = rawurldecode($str);
            }
        }

        // Find protocol and hostspec.
        if (strpos($location, '/') !== false) {
            list ($proto_opts, $this->location) = explode('/', $location, 2);
        } else {
            $proto_opts = $location;
            $this->location = null;
        }

        // Process the different protocol options.
        $proto_opts = rawurldecode($proto_opts);
        if (strpos($proto_opts, ':') !== false) {
            list ($this->host, $this->port) = explode(':', $proto_opts);
        } else {
            $this->host = $proto_opts;
        }
    }

    /**
     * Tells if the location refers to a remote server.
     *
     * @access public
     * @since 5.1
     * @return boolean
     */
    public function isRemote()
    {
        return strlen($this->host) ? true : false;
    }

    /**
     * Returns Module server hostname.
     *
     * @access public
     * @since 5.1
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Returns Module server port.
     *
     * @access public
     * @since 5.1
     * @return integer
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Returns Module username.
     *
     * @access public
     * @since 5.1
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Returns Module password.
     *
     * @access public
     * @since 5.1
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Returns Module location.
     *
     * @access public
     * @since 5.1
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }
}
