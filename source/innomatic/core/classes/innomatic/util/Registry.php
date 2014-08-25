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
 * @license    http://www.innomaticplatform.com/license/ New BSD License
 * @link       http://www.innomaticplatform.com
 * @since      Class available since Release 5.0
*/
namespace Innomatic\Util;

/**
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 * @copyright Copyright 2012 Innoteam Srl
 * @since 1.0
 */
final class Registry
{
    private $globalObjects = array();

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
     * @return void
     */
    public function setGlobalObject($key, $item)
    {
        $this->globalObjects[$key] = $item;
    }

    /**
     * Restituisce un oggetto contenuto nel registro.
     * @param string $key chiave di identificazione dell'oggetto.
     * @return object
     */
    public function getGlobalObject($key)
    {
        if (isset($this->globalObjects[$key])) {
            return $this->globalObjects[$key];
        } else {
            return null;
        }
    }

    /**
     * Verifica che la chiave indicata sia presente nel registro.
     * @param string $key chiave da verificare.
     * @return bool
     */
    public function isGlobalObject($key)
    {
        return (isset($this->globalObjects[$key]));
    }
}
