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
namespace Innomatic\Util;

require_once('innomatic/util/Observer.php');

/**
 * Questa classe rappresenta un oggetto osservabile.
 *
 * Un oggetto osservabile puo' avere uno o piu' oggetti osservatori,
 * che vengono notificati dall'oggetto osservabile non appena lo stesso
 * subisce una variazione, su comando dell'oggetto osservabile.
 * Attenzione: in un contesto multithread il meccanismo non e' utilizzabile
 * tra differenti processi. In tale ipotesi, utilizzare invece il meccanismo
 * degli hook.
 *
 */
abstract class Observable
{
    /**
     * Array degli oggetti che osservano.
     * @var array
     * @access private
     */
    protected $_observingObjects = array();
    /**
     * Indica se l'oggetto e' stato cambiato.
     * @protected bool
     * @access private
     */
    protected $_observableChanged = false;

    /**
     * Costruisce un oggetto Observable con nessun osservatore.
     * @return void
     */
    public function __construct()
    {
        $this->_observingObjects = array();
        $this->_observableChanged = false;
    }

    /**
     * Aggiunge un osservatore all'elenco degli oggetti
     * osservatori per questo oggetto.
     * @param Observer $observer oggetto di tipo Observer da notificare.
     * @return void
     */
    public function addObserver(Observer $observer)
    {
        $this->_observingObjects[] = $observer;
    }

    /**
     * Reimposta l'oggetto come non cambiato.
     * @access private
     * @return void
     */
    protected function clearChanged()
    {
        $this->_observableChanged = false;
    }

    /**
     * Indica quanti oggetti stanno osservando questo oggetto.
     * @access public
     * @return void
     */
    public function countObservers()
    {
        return count($this->_observingObjects);
    }

    /**
     * Elimina un oggetto dall'elenco degli oggetti osservatori.
     * L'oggetto viene individuato in base al valore di hash.
     * Removed because hashCode() is no more available.
     * @see Observable::deleteObservers()
     * @param Observer $observer oggetto di tipo IObserver da rimuovere.
     * @return void
     */
    /**
    public function deleteObserver(Observer $observer)
    {
        $hash = $observer->hashCode();

        foreach ($this->observingObjects as $id => $object) {
            if ($object->hashCode() == $hash) {
                unset ($this->observingObjects[$id]);
                break;
            }
        }

        reset($this->observingObjects);
    }
*/

    /**
     * Azzera l'elenco degli oggetti in osservazione.
     * @see Observable::deleteObserver()
     * @access public
     * @return void
     */
    public function deleteObservers()
    {
        $this->_observingObjects = array ();
    }

    /**
     * Indica se l'oggetto ha subito cambiamenti.
     * @see Observable::setChanged()
     * @see Observable::clearChanged()
     * @access public
     * @return bool
     */
    public function hasChanged()
    {
        return $this->_observableChanged;
    }

    /**
     * Notifica agli oggetti in osservazione che questo oggetto e' stato aggiornato.
     * @see Observable::setChanged()
     * @param mixed $arg argomento opzionale da passare agli oggetti osservatori.
     * @access public
     * @return void
     */
    public function notifyObservers($arg = '')
    {
        if ($this->hasChanged()) {
            foreach ($this->_observingObjects as $id => $objects) {
                $this->_observingObjects[$id]->update($this, $arg);
            }

            $this->clearChanged();
        }
    }

    /**
     * Imposta questo oggetto come cambiato.
     * @access private
     * @return void
     */
    protected function setChanged()
    {
        $this->_observableChanged = true;
    }
}
