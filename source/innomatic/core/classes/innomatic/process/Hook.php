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

require_once('innomatic/dataaccess/DataAccess.php');

/*!
 @class Hook

 @abstract Provides hook functionality.

 @discussion Provides hook functionality. An hook is a method to automatically call functions not defined
 at the moment of the code writing when a certain function is a called.
 */
class Hook {
    /*! @var mRootDb DataAccess class - Innomatic database handler. */
    private $mRootDb;
    /*! @var mApplication string - Application of the function containing the hook. */
    private $mApplication;
    /*! @var mFunction string - Name of the function containing the hook. */
    private $mFunction;
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
    public function __construct(DataAccess $innomaticDb, $application, $function) {
        $this->mRootDb = $innomaticDb;
        $this->mApplication = $application;
        $this->mFunction = $function;
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
    public function callHooks($event, $obj, $args = '') {
        $result = false;
        if ($this->mRootDb) {
            $query = $this->mRootDb->execute(
                'SELECT * FROM hooks WHERE functionapplication='.$this->mRootDb->formatText($this->mApplication).
                ' AND function='.$this->mRootDb->formatText($this->mFunction).
                ' AND event='.$this->mRootDb->formatText($event));
            if ($query) {

                $result = Hook::RESULT_OK;
                require_once('innomatic/core/InnomaticContainer.php');
                $innomatic = InnomaticContainer::instance('innomaticcontainer');
                while (!$query->eof) {
                    $data = $query->getFields();
                    if ($innomatic->getState() == InnomaticContainer::STATE_DEBUG) {
                        require_once('innomatic/debug/InnomaticDump.php');
                        $dump = InnomaticDump::instance('innomaticdump');
                        $dump->hooks[$this->mApplication.'::'.$this->mFunction.'::'.$event][] = $data['hookhandler'].' - '.$data['hookmethod'];
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
    public function add($event, $hookApplication, $hookHandler, $hookMethod) {
        $result = false;
        if ($event and $hookApplication and $hookHandler and $hookMethod) {
            // :TODO: Alex Pagnoni 020114: add check
            // The function should check if the method already exists.

            $result = $this->mRootDb->execute(
                'INSERT INTO hooks VALUES ('.$this->mRootDb->getNextSequenceValue('hooks_id_seq').
                ','.$this->mRootDb->formatText($this->mApplication).
                ','.$this->mRootDb->formatText($this->mFunction).
                ','.$this->mRootDb->formatText($event).
                ','.$this->mRootDb->formatText($hookApplication).
                ','.$this->mRootDb->formatText($hookHandler).
                ','.$this->mRootDb->formatText($hookMethod).' )');
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
    public function remove($event, $hookApplication) {
        if ($event) {
            return $this->mRootDb->execute(
                'DELETE FROM hooks WHERE functionapplication='.$this->mRootDb->formatText($this->mApplication).
                ' AND function='.$this->mRootDb->formatText($this->mFunction).
                ' AND event='.$this->mRootDb->formatText($event).
                ' AND hookapplication='.$this->mRootDb->formatText($hookApplication));
        }
        return false;
    }

    /*!
     @function Update
     @abstract Updates an existing hook.
     @param event string - Name of the event to be updated.
     @result True if the hook has been updated.
     */
    public function update($event, $hookApplication, $hookHandler, $hookMethod) {
        if ($hookMethod and $hookHandler) {
            return $this->mRootDb->execute(
                'UPDATE hooks SET hookhandler='.$this->mRootDb->formatText($hookHandler).
                ', hookmethod='.$this->mRootDb->formatText($hookMethod).
                ' WHERE functionapplication='.$this->mRootDb->formatText($this->mApplication).
                ' AND event='.$this->mRootDb->formatText($event).
                ' AND hookapplication='.$this->mRootDb->formatText($hookApplication).
                ' AND function='.$this->mRootDb->formatText($this->mFunction));
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
    public function addEvent($event) {
        $result = false;
        if ($event) {
            // :TODO: Alex Pagnoni 020114: add check
            // The function should check if the method already exists.
            $result = $this->mRootDb->execute(
                'INSERT INTO hooks_events VALUES ('.$this->mRootDb->getNextSequenceValue('hooks_events_id_seq').
                ','.$this->mRootDb->formatText($this->mApplication).
                ','.$this->mRootDb->formatText($this->mFunction).
                ','.$this->mRootDb->formatText($event).' )');
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
    public function removeEvent($event) {
        $result = false;
        if ($event) {
            $result = $this->mRootDb->execute(
                'DELETE FROM hooks_events WHERE functionapplication='.$this->mRootDb->formatText($this->mApplication).
                ' AND function='.$this->mRootDb->formatText($this->mFunction).
                ' AND event='.$this->mRootDb->formatText($event));
        }
        return $result;
    }
}
