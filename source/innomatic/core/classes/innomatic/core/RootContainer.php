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

// This require uses the absolute path because at this time the PHP include path
// is still the default one and doesn't include the Innomatic container classes
// directory.
require_once(dirname(__FILE__).'/../util/Singleton.php');

/**
 * The Root Container holds the base path of Innomatic and updates the PHP
 * include path adding the Innomatic container classes directory.
 * 
 * It also tells if the application started by the Innomatic container has
 * been exited in a clean way or if it crashed, letting the Innomatic container
 * call the RootContainer::stop() method. 
 * 
 * @copyright  2008-2012 Innoteam S.r.l.
 * @license    http://www.innomatic.org/license/   BSD License
 * @version    Release: @package_version@
 * @link       http://www.innomatic.org
 * @since      Class available since Release 5.0
 * @package    Core
 */
class RootContainer extends Singleton
{
    /**
     * Holds the root container base directory, where all the container
     * applications and the main index.php receiver file are stored.
     *
     * @var string
     */
    private $_home;
    /**
     * The clean state is false until explicitly changed to true calling the
     * RootContainer::stop() method.
     *
     * @var boolean
     */
    private $_clean = false;

    /**
     * RootContainer constructor.
     *
     */
    public function ___construct()
    {
        $this->_home = realpath(dirname(__FILE__).'/../../../../..').'/';
        @chdir($this->_home);
        
        // This is needed in order to prevent a successive chdir() to screw
        // including classes when relying on having Innomatic root directory
        // as current directory
        set_include_path(
            get_include_path() . PATH_SEPARATOR . $this->_home
            . 'innomatic/WEB-INF/classes/'
        );
    }

    /**
     * Returns the root container home directory.
     *
     * @return string
     */
    public function getHome()
    {
        return $this->_home;
    }
    
    /**
     * Stops the root container, setting the clean flag to true.
     * This is useful in conjunction with a PHP shutdown function registered
     * with register_shutdown_function, e.g. to catch fatal errors.
     *
     * This happens by default in the InnomaticContainer class.
     */
    public function stop()
    {
        $this->_clean = true;
    }
    
    /**
     * Tells if the root container is in a clean state. This can only be true
     * after a call to the RootContainer::stop() method.
     *
     * @return bool
     */
    public function isClean()
    {
        return $this->_clean;
    }
}
