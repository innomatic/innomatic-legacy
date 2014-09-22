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
 * @license    http://www.innomatic.io/license/ New BSD License
 * @link       http://www.innomatic.io
 * @since      Class available since Release 5.0
*/
namespace Innomatic\Process;

/*!
 @class Hook

 @abstract Provides hook functionality.

 @discussion Provides hook functionality. An hook is a method to automatically call functions not defined
 at the moment of the code writing when a certain function is a called.
 */
class Hook
{
    /*! @var rootdataaccess DataAccess class - Innomatic database handler. */
    private $rootdataaccess;
    /*! @var application string - Application of the function containing the hook. */
    private $application;
    /*! @var function string - Name of the function containing the hook. */
    private $function;
    const RESULT_OK = 1;
    const RESULT_CANCEL = 2;
    const RESULT_ABORT = 3;

    /*!
     @function Hook
     @abstract Class constructor.
     @discussion Class constructor.
     @param &rootDb DataAccess class - Innomatic database handler.
     @param application string - Application of the function containing the hook.
     @param function string - Name of the function containing the hook.
     */
    public function __construct(\Innomatic\Dataaccess\DataAccess $innomaticDb, $application, $function)
    {
        $this->rootdataaccess = $innomaticDb;
        $this->application = $application;
        $this->function = $function;
    }

    /*!
     @function CallHooks
     @abstract Call the functions associated to a certain hook event.
     @discussion Call the functions associated to a certain hook event.
     @param event string -.Name of the hook event that occured.
     @param &obj object - If the hook is contained in an object, a reference to the object should be passed.
     @param args array - Array of the arguments of the function containing the hook.
     @result True if the function associated to the hook event have been called.
     */
    public function callHooks($event, $obj, $args = '')
    {
        $result = false;
        if ($this->rootdataaccess) {
            $query = $this->rootdataaccess->execute(
                'SELECT * FROM hooks WHERE functionapplication='.$this->rootdataaccess->formatText($this->application).
                ' AND function='.$this->rootdataaccess->formatText($this->function).
                ' AND event='.$this->rootdataaccess->formatText($event));
            if ($query) {

                $result = Hook::RESULT_OK;
                $innomatic = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');
                while (!$query->eof) {
                    $data = $query->getFields();
                    if ($innomatic->getState() == \Innomatic\Core\InnomaticContainer::STATE_DEBUG) {
                        $dump = \Innomatic\Debug\InnomaticDump::instance('\Innomatic\Debug\InnomaticDump');
                        $dump->hooks[$this->application.'::'.$this->function.'::'.$event][] = $data['hookhandler'].' - '.$data['hookmethod'];
                    }
                    require_once('shared/hooks/'.$data['hookhandler']);

                    // let the 'class::function' syntax be accepted in dispatch maps
                    if (is_string($data['hookmethod']) && strpos($data['hookmethod'], '::')) {
                        $data['hookmethod'] = explode('::', $data['hookmethod']);
                    }
                    // verify that function to be invoked is in fact callable
                    if (!is_callable($data['hookmethod'])) {
                        continue;
                    }

                    $func_result = call_user_func($data['hookmethod'], $obj, $args);
                    if ($func_result == Hook::RESULT_ABORT) {
                        $result = Hook::RESULT_ABORT;
                    }
                    $query->moveNext();
                }
            }
        }

        return $result;
    }

    /*!
     @function Add
     @abstract Adds an event to the hook.
     @discussion Adds an event to the hook.
     @param event string - Name of the event to be added.
     @param hookApplication string - Name of the application containing the function with the hook.
     @param hookHandler string - Name of the file containing the handler for the hook event.
     @param hookMethod string - Name of the function that handles the hook event.
     @result True if the hook event has been added.
     */
    public function add($event, $hookApplication, $hookHandler, $hookMethod)
    {
        $result = false;
        if ($event and $hookApplication and $hookHandler and $hookMethod) {
            // :TODO: Alex Pagnoni 020114: add check
            // The function should check if the method already exists.

            $result = $this->rootdataaccess->execute(
                'INSERT INTO hooks VALUES ('.$this->rootdataaccess->getNextSequenceValue('hooks_id_seq').
                ','.$this->rootdataaccess->formatText($this->application).
                ','.$this->rootdataaccess->formatText($this->function).
                ','.$this->rootdataaccess->formatText($event).
                ','.$this->rootdataaccess->formatText($hookApplication).
                ','.$this->rootdataaccess->formatText($hookHandler).
                ','.$this->rootdataaccess->formatText($hookMethod).' )');
        }
        return $result;
    }

    /*!
     @function Remove
     @abstract Removes a hook.
     @discussion Removed a hook.
     @param event string - Name of the event to be removed.
     @param hookApplication string - Name of the application containing the function with the hook.
     @result True if the hook has been removed.
     */
    public function remove($event, $hookApplication)
    {
        if ($event) {
            return $this->rootdataaccess->execute(
                'DELETE FROM hooks WHERE functionapplication='.$this->rootdataaccess->formatText($this->application).
                ' AND function='.$this->rootdataaccess->formatText($this->function).
                ' AND event='.$this->rootdataaccess->formatText($event).
                ' AND hookapplication='.$this->rootdataaccess->formatText($hookApplication));
        }
        return false;
    }

    /*!
     @function Update
     @abstract Updates an existing hook.
     @param event string - Name of the event to be updated.
     @result True if the hook has been updated.
     */
    public function update($event, $hookApplication, $hookHandler, $hookMethod)
    {
        if ($hookMethod and $hookHandler) {
            return $this->rootdataaccess->execute(
                'UPDATE hooks SET hookhandler='.$this->rootdataaccess->formatText($hookHandler).
                ', hookmethod='.$this->rootdataaccess->formatText($hookMethod).
                ' WHERE functionapplication='.$this->rootdataaccess->formatText($this->application).
                ' AND event='.$this->rootdataaccess->formatText($event).
                ' AND hookapplication='.$this->rootdataaccess->formatText($hookApplication).
                ' AND function='.$this->rootdataaccess->formatText($this->function));
        }
        return false;
    }

    /*!
     @function AddEvent
     @abstract Adds an event to the hook events list.
     @discussion Adds an event to the hook events list.
     @param event string - Event name.
     @result True if the event has been added into the list.
     */
    public function addEvent($event)
    {
        $result = false;
        if ($event) {
            // :TODO: Alex Pagnoni 020114: add check
            // The function should check if the method already exists.
            $result = $this->rootdataaccess->execute(
                'INSERT INTO hooks_events VALUES ('.$this->rootdataaccess->getNextSequenceValue('hooks_events_id_seq').
                ','.$this->rootdataaccess->formatText($this->application).
                ','.$this->rootdataaccess->formatText($this->function).
                ','.$this->rootdataaccess->formatText($event).' )');
        }
        return $result;
    }

    /*!
     @function Remove event
     @abstract Removes a hook event from the list.
     @discussion Removes a hook event from the list.
     @param event string - Event name.
     @result True if the hook event has been removed.
     */
    public function removeEvent($event)
    {
        $result = false;
        if ($event) {
            $result = $this->rootdataaccess->execute(
                'DELETE FROM hooks_events WHERE functionapplication='.$this->rootdataaccess->formatText($this->application).
                ' AND function='.$this->rootdataaccess->formatText($this->function).
                ' AND event='.$this->rootdataaccess->formatText($event));
        }
        return $result;
    }
}
