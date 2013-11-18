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

require_once(dirname(__FILE__).'/Registry.php');

/**
 * Classe per fornire oggetti singleton.
 *
 * Questa classe fornisce un metodo comune per creare un oggetto
 * singleton, facendo uso, in questa particolare implementazione,
 * del registro fornito dalla classe Registry. Inoltre non viene
 * fatto uso di variabili statiche.
 * Questa classe deve essere utilizzata estendendola nella classe
 * per la quale si intende ottenere un oggetto singleton.
 * Per instanziare un oggetto si dovr� usare
 * Oggetto::instance( 'nome classe' ).
 * Attenzione: poich� viene rilasciata una referenza, non sar�
 * possibile distruggere l'oggetto tramite unset() (almeno nel PHP 4).
 *
 * @since 1.0
 * @author Alex Pagnoni <alex.pagnoni@innoteam.it>
 * @copyright Copyright 2012 Innoteam Srl
 */
abstract class Singleton
{
    public function Singleton()
    {
        $registry = Registry::instance();
        if ($registry->isGlobalObject('singleton ' . get_class($this))) {
        }
    }

    static public final function instance($class)
    {
        $class = strtolower($class);
        $registry = Registry::instance();
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
    
    /*
     * A singleton cannot be cloned, so the __clone method is overriden and
     * declared final.
     * 
     * @since 1.2
     */
    protected final function __clone()
    {
    }
}
