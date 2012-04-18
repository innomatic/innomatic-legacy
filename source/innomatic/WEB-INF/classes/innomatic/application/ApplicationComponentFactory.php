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

/*!
 @class ApplicationComponentFactory
 @abstract Application component types handling.
 */
class ApplicationComponentFactory
{
    /*! @public types array - Array of the component types. */
    public $types = array();
    /*! @public rootda DataAccess class - Innomatic database handler. */
    public $rootda;

    /*!
     @function ApplicationComponentFactory

     @abstract Class constructor.
     */
    public function __construct($rootda)
    {
        $this->rootda = $rootda;
    }

    /*!
     @function FillTypes

     @abstract Fill the types property with the component types.

     @result True if no problem encountered.
     */
    public function fillTypes()
    {
        $result = TRUE;

        // Flushes current types
        //
        unset($this->types);
        $this->types = array();

        if ($this->rootda) {
            $query = $this->rootda->execute(
                'SELECT * FROM applications_components_types'
            );

            if ($query) {
                if ($query->getNumberRows()) {
                    // Fills types
                    //
                    while (!$query->eof) {
                        $data = $query->getFields();

                        unset($component);
                        if (
                            file_exists(
                                InnomaticContainer::instance('innomaticcontainer')->getHome()
                                .'WEB-INF/classes/shared/components/'.$data['file']
                            )
                        ) {
                            // TODO gestire con require_once una volta migliorata la gestione della variabile $component
                            require_once(
                                InnomaticContainer::instance('innomaticcontainer')->getHome()
                                .'WEB-INF/classes/shared/components/'.$data['file']
                            );
                        } else {
                            require_once('innomatic/logging/Logger.php');
                            $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
                            $log->logEvent(
                                'innomatic/application/ApplicationComponentFactory::fillTypes()',
                                'Component file '.$data['file']." doesn't exists in components directory",
                                Logger::WARNING
                            );
                            $result = FALSE;
                        }
                        $className = ucfirst($data['typename']).'Component';
                        if (class_exists($className)) {
                            $this->types[call_user_func(array($className, 'getType'))] = array(
                                'type' => call_user_func(array($className, 'getType')),
                                'classname' => $className,
                                'priority' => call_user_func(array($className, 'getPriority')),
                                'domain' => call_user_func(array($className, 'getIsDomain'))
                            );
                        }
                        $query->moveNext();
                    }
                }
                $query->free();
            } else {
                require_once('innomatic/logging/Logger.php');
                $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
                $log->logEvent(
                    'innomatic/application/ApplicationComponentFactory::fillTypes()',
                    'Unable to select component types from table',
                    Logger::ERROR
                );
                $result = FALSE;
            }
            $result = FALSE;
        }

        return $result;
    }

    /*!
     @abstract Installs a new component type handler.
     @discussion Component type handler must have Component.php suffix.
     @param filepath string - Complete path of the component type handler.
     @result True if successfully installed.
     */
    public function install($componentData)
    {
        $result = FALSE;
        $filepath = $componentData['filepath'];

        if ($this->rootda and file_exists($filepath)) {
            require_once($filepath);

            $className = substr(basename($filepath), 0, -4);
            if (class_exists($className)) {

                if (
                    call_user_func(array($className, 'getType'))
                    and $className
                ) {
                    /*
                     if (!isset($component['links'])) {
                     $component['links'] = array();
                     }
                     */

                    $result = $this->rootda->execute(
                        'INSERT INTO applications_components_types (id,typename,priority,domain,file) VALUES ('.
                        $this->rootda->getNextSequenceValue('applications_components_types_id_seq').','.
                        $this->rootda->formatText(call_user_func(array($className, 'getType'))).','.
                        call_user_func(array($className, 'getPriority')).','.
                        $this->rootda->formatText(
                            (
                                call_user_func(array($className, 'getIsDomain'))
                                ? $this->rootda->fmttrue
                                : $this->rootda->fmtfalse)
                        ).','.$this->rootda->formatText(basename($filepath)).')'
                    );
                }
            }
        } else {
            if (!file_exists($filepath)) {
                require_once('innomatic/logging/Logger.php');
                $log = InnomaticContainer::instance(
                    'innomaticcontainer'
                )->getLogger();
                $log->logEvent(
                    'innomatic/application/ApplicationComponentFactory::install()',
                    'Given file (' . $filepath . ') does not exists',
                    Logger::ERROR
                );
            }
        }
        return $result;
    }

    /*!
     @abstract Updates an component type handler.
     @discussion Component type handler must have Component.php suffix.
     @param filepath string - Complete path of the component type handler.
     @result True if successfully updated.
     */
    public function update($componentData)
    {
        $result = FALSE;
        $filepath = $componentData['filepath'];

        if ($this->rootda and file_exists($filepath)) {
            require_once($filepath);
            $className = substr(basename($filepath), 0, -4);
            if (class_exists($className)) {

                if (call_user_func(array($className, 'getType')) and $className) {
                    /*
                     if (!isset($component['links'])) {
                     $component['links'] = array();
                     }
                     */
                        
                    $checkQuery = $this->rootda->execute(
                        'SELECT typename FROM applications_components_types WHERE typename=' .
                        $this->rootda->formatText(call_user_func(array($className, 'getType')))
                    );

                    if ($checkQuery->getNumberRows()) {
                        $result = $this->rootda->execute(
                            'UPDATE applications_components_types SET priority='
                            .call_user_func(array($className, 'getPriority')).
                            ',domain='.$this->rootda->formatText(
                                (
                                    call_user_func(
                                        array(
                                            $className,
                                            'getIsDomain'
                                        )
                                    )
                                    ? $this->rootda->fmttrue : $this->rootda->fmtfalse
                                )
                            )
                            .',file='.$this->rootda->formatText(basename($filepath)).
                            ' WHERE typename='.$this->rootda->formatText(
                                call_user_func(
                                    array(
                                        $className,
                                        'getType'
                                    )
                                )
                            )
                        );
                    } else {
                        $result = $this->rootda->execute(
                            'INSERT INTO applications_components_types (id,typename,priority,domain,file) VALUES ('.
                            $this->rootda->getNextSequenceValue('applications_components_types_id_seq').','.
                            $this->rootda->formatText(call_user_func(array($className, 'getType'))).','.
                            call_user_func(array($className, 'getPriority')).','.
                            $this->rootda->formatText(
                                (
                                    call_user_func(
                                        array(
                                            $className,
                                            'getIsDomain'
                                        )
                                    )
                                    ? $this->rootda->fmttrue : $this->rootda->fmtfalse
                                )
                            ).','.$this->rootda->formatText(basename($filepath)).')'
                        );
                    }
                }
            }
        } else {
            if (!file_exists($filepath)) {
                require_once('innomatic/logging/Logger.php');
                $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
                $log->logEvent(
                    'innomatic/application/ApplicationComponentFactory::update()',
                    'Given file (' . $filepath . ') does not exists',
                    Logger::ERROR
                );
            }
        }
        return $result;
    }

    /*!
     @abstract Removes an component type handler.
     @discussion An component type handler must be removed only if there are no installed components of that type.
     @param filepath string - Complete path of the component type handler to be removed.
     @result True if successfully uninstalled.
     */
    public function uninstall($componentData)
    {
        $result = FALSE;
        $filepath = $componentData['filepath'];
        if ($this->rootda and file_exists($filepath)) {
            require_once($filepath);
            $className = substr(basename($filepath), 0, -4);
            if (class_exists($className)) {

                if (
                    call_user_func(array($className, 'getType'))
                    and $className
                ) {
                    $result = $this->rootda->execute(
                        'DELETE FROM applications_components_types WHERE typename=' .
                        $this->rootda->formatText(
                            call_user_func(array($className, 'getType'))
                        )
                    );
                }
            }
        } else {
            if (!file_exists($filepath)) {
                require_once('innomatic/logging/Logger.php');
                $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
                $log->logEvent(
                    'innomatic/application/ApplicationComponentFactory::uninstall()',
                    'Given file (' . $filepath . ') does not exists',
                    Logger::ERROR
                );
            }
        }
        return $result;
    }
}
