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
 * @since      Class available since Release 5.0
*/
namespace Innomatic\Util;

require_once(dirname(__FILE__).'/Registry.php');


/**
 * Class that handles singletons.
 *
 * This class delivers a common method to create singleton objects using
 * a registry.
 *
 * Since the object returned by the instance method is a reference to the
 * real object stored inside the Registry, it cannot be destroyed with
 * unset().
 *
 * @since 5.0
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 * @copyright Copyright 2012 Innomatic Company
 */
abstract class Singleton
{
    /* public Singleton() {{{ */
    /**
     * Class constructor.
     *
     * @access public
     * @return void
     */
    public function Singleton()
    {
        $registry = \Innomatic\Util\Registry::instance();
        if ($registry->isGlobalObject('singleton ' . get_class($this))) {
        }
    }
    /* }}} */

    /* public instance($class) {{{ */
    /**
     * Method to be called when instancing a singleton.
     *
     * This method must be called when creating a new instance of a singleton;
     * singletons must not be created with the new keyword.
     *
     * This method calls the class ___construct method if available (please note
     * the three underscores), the first time the singleton is instanced.
     *
     * @param string $class Fully qualified class name to be instanced.
     * @static
     * @final
     * @access public
     * @return object
     */
    final public static function instance($class)
    {
        $registry = \Innomatic\Util\Registry::instance();

        // @todo compatibility mode

        if ($registry->isGlobalObject('system_classes')) {
            $system_classes = $registry->getGlobalObject('system_classes');
            if (isset($system_classes[$class])) {
                $class = $system_classes[$class]['fqcn'];
            }
        }

        if (!$registry->isGlobalObject('singleton ' . $class)) {
            $singleton = new $class();
            $registry->setGlobalObject('singleton ' . $class, $singleton);

            // Checks if the class has a ___construct method, that is the
            // real constructor for the object in place of the __construct one.
            if (method_exists($singleton, '___construct')) {
                // Checks if there are any parameter to pass to the constructor.
                if (func_num_args() > 1) {
                    // Gets this method parameters and strips away the first
                    // one, that is the name of the singleton class.
                    $args = func_get_args();
                    unset($args[0]);

                    // Calls the real class constructor.
                    call_user_func_array(
                        array($singleton, '___construct'),
                        $args
                    );
                } else {
                    // Calls the real class constructor without parameters.
                    $singleton->___construct();
                }
            }
        }
        return $registry->getGlobalObject('singleton '.$class);
    }
    /* }}} */

    /*
     * A singleton cannot be cloned, so the __clone method is overriden and
     * declared final.
     *
     * @since 1.2
     */
    final protected function __clone()
    {
    }
}
