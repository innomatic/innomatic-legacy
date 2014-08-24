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
 * @license    http://www.innomaticplatform.com/license/   BSD License
 * @link       http://www.innomaticplatform.com
 * @since      Class available since Release 5.0
*/
namespace Innomatic\Util;

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
     */
    protected $observingObjects = array();
    /**
     * Indica se l'oggetto e' stato cambiato.
     * @protected bool
     */
    protected $observableChanged = false;

    /**
     * Costruisce un oggetto Observable con nessun osservatore.
     * @return void
     */
    public function __construct()
    {
        $this->observingObjects = array();
        $this->observableChanged = false;
    }

    /**
     * Aggiunge un osservatore all'elenco degli oggetti
     * osservatori per questo oggetto.
     * @param Observer $observer oggetto di tipo Observer da notificare.
     * @return void
     */
    public function addObserver(\Innomatic\Util\Observer $observer)
    {
        $this->observingObjects[] = $observer;
    }

    /**
     * Reimposta l'oggetto come non cambiato.
     * @return void
     */
    protected function clearChanged()
    {
        $this->observableChanged = false;
    }

    /**
     * Indica quanti oggetti stanno osservando questo oggetto.
     * @return void
     */
    public function countObservers()
    {
        return count($this->observingObjects);
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
    public function deleteObserver(\Innomatic\Util\Observer $observer)
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
     * @return void
     */
    public function deleteObservers()
    {
        $this->observingObjects = array ();
    }

    /**
     * Indica se l'oggetto ha subito cambiamenti.
     * @see Observable::setChanged()
     * @see Observable::clearChanged()
     * @return bool
     */
    public function hasChanged()
    {
        return $this->observableChanged;
    }

    /**
     * Notifica agli oggetti in osservazione che questo oggetto e' stato aggiornato.
     * @see Observable::setChanged()
     * @param mixed $arg argomento opzionale da passare agli oggetti osservatori.
     * @return void
     */
    public function notifyObservers($arg = '')
    {
        if ($this->hasChanged()) {
            foreach ($this->observingObjects as $id => $objects) {
                $this->observingObjects[$id]->update($this, $arg);
            }

            $this->clearChanged();
        }
    }

    /**
     * Imposta questo oggetto come cambiato.
     * @return void
     */
    protected function setChanged()
    {
        $this->observableChanged = true;
    }
}
