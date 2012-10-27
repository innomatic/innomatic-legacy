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

/**
 * @author Alex Pagnoni <alex.pagnoni@innoteam.it>
 * @copyright Copyright 2012 Innoteam S.r.l.
 * @since 1.0
 */
final class Registry
{
    private $_globalObjects = array();

    public static function instance()
    {
        static $instance;
        if (!isset ($instance)) {
            $instance = new Registry();
        }
        return $instance;
    }

    /**
     * Imposta nel registro un oggetto
     * tramite una chiave di identificazione.
     * @param string $key chiave di identificazione dell'oggetto.
     * @param object $item oggetto da mantenere nel registro.
     * @access public
     * @return void
     */
    public function setGlobalObject($key, $item)
    {
        $this->_globalObjects[$key] = $item;
    }

    /**
     * Restituisce un oggetto contenuto nel registro.
     * @param string $key chiave di identificazione dell'oggetto.
     * @access public
     * @return object
     */
    public function getGlobalObject($key)
    {
        if (isset($this->_globalObjects[$key])) {
            return $this->_globalObjects[$key];
        } else {
            return NULL;
        }
    }

    /**
     * Verifica che la chiave indicata sia presente nel registro.
     * @param string $key chiave da verificare.
     * @access public
     * @return bool
     */
    public function isGlobalObject($key)
    {
        return (isset($this->_globalObjects[$key]));
    }
}