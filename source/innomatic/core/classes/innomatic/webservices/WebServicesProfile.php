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

// TODO Alex Pagnoni 010711
// When a application is removed, all permission nodes
// related to the application must be removed.

require_once('innomatic/logging/Logger.php');
require_once('innomatic/process/Hook.php');

/*!
 @class WebServicesProfile

 @abstract Web services profile class.
 */
class WebServicesProfile
{
    var $mLog;
    var $mRootDb;
    var $mProfileId;
    const NODETYPE_APPLICATION = 0;
    const NODETYPE_METHOD = 1;
    const APPLICATIONNODE_FULLYENABLED = 1;
    const APPLICATIONNODE_PARTIALLYENABLED = 2;
    const APPLICATIONNODE_NOTENABLED = 3;
    const METHODNODE_ENABLED = 4;
    const METHODNODE_NOTENABLED = 5;
    
    /*!
     @function WebServicesProfile

     @abstract Class constructor.

     @param rootDb DataAccess class - Innomatic database handler.
     @param profileId integer - Profile serial.
     */
    function WebServicesProfile( &$innomaticDb, $profileId = '' )
    {
        $this->mLog = InnomaticContainer::instance('innomaticcontainer')->getLogger();

        if ( $innomaticDb ) $this->mRootDb = &$innomaticDb;
        else $this->mLog->LogDie( 'innomatic.webservicesprofile.webservicesprofile',
                                 'Invalid Innomatic database handler' );

        $this->mProfileId = $profileId;
    }

    /*!
     @function Add

     @abstract Adds a new profile.

     @param profileName string - Profile name.

     @result True if the profile has been added.
     */
    function Add( $profileName )
    {
        $result = false;

        $hook = new Hook( $this->mRootDb, 'innomatic', 'webservicesprofile.add' );
        if ( $hook->CallHooks( 'calltime', $this, array( 'name' => $profileName ) ) == Hook::RESULT_OK )
        {
            if ( $this->mRootDb )
            {
                if ( !$this->mProfileId )
                {
                    if ( strlen( $profileName ) )
                    {
                        $query = &$this->mRootDb->execute( 'SELECT profilename '.
                                                          'FROM webservices_profiles '.
                                                          'WHERE profilename='.$this->mRootDb->formatText( $profileName ) );
                        if ( !$query->getNumberRows() )
                        {
                            $this->mProfileId = $this->mRootDb->getNextSequenceValue( 'webservices_profiles_id_seq' );

                            $result = &$this->mRootDb->execute( 'INSERT INTO webservices_profiles '.
                                                               'VALUES ('.
                                                               $this->mProfileId.','.
                                                               $this->mRootDb->formatText( $profileName ).')' );

                            if ( $result )
                            {
                                $hook->CallHooks( 'profileadded', $this, array( 'name' => $profileName ) );

                                $this->mLog->logEvent( 'Innomatic',
                                                      'Created new web services profile', Logger::NOTICE );
                            }
                            else
                            {
                                $this->mLog->logEvent( 'innomatic.webservicesprofile.add',
                                                      'Unable to insert web services profile into webservices_profiles table', Logger::ERROR );
                            }
                        }
                    }
                    else $this->mLog->logEvent( 'innomatic.webservicesprofile.add',
                                                'Empty profile name', Logger::ERROR );
                }
                else $this->mLog->logEvent( 'innomatic.webservicesprofile.add',
                                            'Already assigned user for this object', Logger::ERROR );
            }
            else $this->mLog->logEvent( 'innomatic.webservicesprofile.add',
                                        'Invalid Innomatic database handler', Logger::ERROR );
        }

        return $result;
    }

    /*!
     @function Remove

     @abstract Removes a web services profile.

     @result True it the profile has been deleted.
     */
    function Remove()
    {
        $result = false;

        $hook = new Hook( $this->mRootDb, 'innomatic', 'webservicesprofile.remove' );
        if ( $hook->CallHooks( 'calltime', $this, array() ) == Hook::RESULT_OK )
        {
            if ( $this->mRootDb )
            {
                if ( $this->mProfileId )
                {
                    // Removes all permissions of the profile
                    //
                    $this->mRootDb->execute( 'DELETE FROM webservices_permissions '.
                                            'WHERE profileid='.(int)$this->mProfileId );

                    // Removes the profile from the users
                    //
                    $this->mRootDb->execute( 'UPDATE webservices_users '.
                                            'SET profileid=0 '.
                                            'WHERE profileid='.(int)$this->mProfileId );

                    // Removes the profile
                    //
                    $this->mRootDb->execute( 'DELETE FROM webservices_profiles '.
                                            'WHERE id='.(int)$this->mProfileId );

                    // Unset profile id
                    //
                    $this->mProfileId = '';

                    $hook->CallHooks( 'profileremoved', $this, array() );

                    $result = true;

                    $this->mLog->logEvent( 'Innomatic',
                                          'Removed web services profile', Logger::NOTICE );
                }
                else $this->mLog->logEvent( 'innomatic.webservicesprofile.remove',
                                            'Object not assigned to a profile', Logger::ERROR );
            }
            else $this->mLog->logEvent( 'innomatic.webservicesprofile.remove',
                                        'Invalid Innomatic database handler', Logger::ERROR );
        }

        return $result;
    }

    /*!
     @function Rename

     @abstract Renames a web services profile.

     @param profileName string - New profile name.

     @result True it the profile has been renamed.
     */
    function Rename( $profileName )
    {
        $result = false;
        
        $hook = new Hook( $this->mRootDb, 'innomatic', 'webservicesprofile.rename' );
        if ( $hook->CallHooks( 'calltime', $this, array( 'name' => $profileName ) ) == Hook::RESULT_OK )
        {
            if ( $this->mRootDb )
            {
                if ( $this->mProfileId )
                {
                    if ( strlen( $profileName ) )
                    {
                        // Removes the profile
                        //
                        $result = $this->mRootDb->execute( 'UPDATE webservices_profiles '.
                            'SET profilename='.$this->mRootDb->formatText( $profileName ).' '.
                            'WHERE id='.(int)$this->mProfileId );

                        $hook->CallHooks( 'profilerenamed', $this, array( 'name' => $profileName ) );

                    }
                    else $this->mLog->logEvent( 'innomatic.webservicesprofile.rename',
                                                'Empty new profile name', Logger::ERROR );
                }
                else $this->mLog->logEvent( 'innomatic.webservicesprofile.rename',
                                            'Object not assigned to a profile', Logger::ERROR );
            }
            else $this->mLog->logEvent( 'innomatic.webservicesprofile.rename',
                                        'Invalid Innomatic database handler', Logger::ERROR );
        }

        return $result;
    }

    /*!
     @function EnableNode

     @abstract Enables a node.

     @discussion A node is a method or a whole set of application metods.

     @param nodeType integer - Node type, as in the WebServicesProfile::NODETYPE defines.
     @param applicationName string - Application name of the method.
     @param methodName string - Method name, may be empty if nodeType is WebServicesProfile::NODETYPE_APPLICATION.

     @result True if the node has been enabled.
     */
    function EnableNode( $nodeType, $applicationName, $methodName = '' )
    {
        $result = false;

        if ( $this->mRootDb )
        {
            if ( $this->mProfileId )
            {
                if ( strlen( $nodeType )
                     and
                     strlen( $applicationName )
                     and
                     (
                      $nodeType == WebServicesProfile::NODETYPE_APPLICATION
                      or
                      (
                       $nodeType == WebServicesProfile::NODETYPE_METHOD
                       and
                       strlen( $methodName )
                      )
                     )
                   )
                {
                    // :TODO: Alex Pagnoni 010710
                    // It should check if the node already exists

                    // If nodeType is application, then remove all nodes that are methods of the application
                    //
                    if ( $nodeType == WebServicesProfile::NODETYPE_APPLICATION ) $this->mRootDb->execute( 'DELETE FROM webservices_permissions '.
                                                                                              'WHERE application='.$this->mRootDb->formatText( $applicationName ).' '.
                                                                                              'AND profileid='.(int)$this->mProfileId );

                    // Checks if all other method nodes in the application were enabled,
                    // in that case disables all them and enables the application node
                    //
                    if ( $nodeType == WebServicesProfile::NODETYPE_METHOD )
                    {
                        $tmpquery = &$this->mRootDb->execute( 'SELECT count(*) AS count '.
                                                             'FROM webservices_permissions '.
                                                             'WHERE application='.$this->mRootDb->formatText( $applicationName ).' '.
                                                             'AND profileid='.(int)$this->mProfileId );

                        $tmpqueryb = &$this->mRootDb->execute( 'SELECT count(*) AS count '.
                                                              'FROM webservices_methods '.
                                                              'WHERE application='.$this->mRootDb->formatText( $applicationName ) );

                        if ( $tmpquery->getFields( 'count' ) == ( $tmpqueryb->getFields( 'count' ) - 1 ) )
                        {
                            $this->mRootDb->execute( 'DELETE FROM webservices_permissions '.
                                                    'WHERE application='.$this->mRootDb->formatText( $applicationName ).' '.
                                                    'AND profileid='.(int)$this->mProfileId );
                            $skip_method = true;
                        }
                    }

                    $result = &$this->mRootDb->execute( 'INSERT INTO webservices_permissions '.
                                                       'VALUES ('.
                                                       $this->mProfileId.','.
                                                       $this->mRootDb->formatText( $applicationName ).','.
                                                       $this->mRootDb->formatText( ( ( ( $nodeType == WebServicesProfile::NODETYPE_METHOD ) and ( $skip_method != true ) )
                                                                                     ?
                                                                                     $methodName
                                                                                     :
                                                                                     '' ) ).')' );

                    // :TODO: Alex Pagnoni 010711
                    // If nodetype is method, it should check if all methods of the application were enabled.
                    // In that case it should remove every node relative to the application and enable a new node of application type.

                    if ( !$result ) $this->mLog->logEvent( 'innomatic.webservicesprofile.enablenode',
                                                           'Unable to insert web services profile node into webservices_permissions table', Logger::ERROR );
                }
                else $this->mLog->logEvent( 'innomatic.webservicesprofile.enablenode',
                                            'Wrong parameters', Logger::ERROR );
            }
            else $this->mLog->logEvent( 'innomatic.webservicesprofile.enablenode',
                                        'Object not assigned to a profile', Logger::ERROR );
        }
        else $this->mLog->logEvent( 'innomatic.webservicesprofile.enablenode',
                                    'Invalid Innomatic database handler', Logger::ERROR );

        return $result;
    }

    /*!
     @function DisableNode

     @abstract Disables a node.

     @discussion A node is a method or a whole set of application metods.

     @param nodeType integer - Node type, as in the WebServicesProfile::NODETYPE defines.
     @param applicationName string - Application name of the method.
     @param methodName string - Method name, may be empty if nodeType is WebServicesProfile::NODETYPE_APPLICATION.

     @result True if the node has been disabled.
     */
    function DisableNode( $nodeType, $applicationName, $methodName = '' )
    {
        $result = false;

        if ( $this->mRootDb )
        {
            if ( $this->mProfileId )
            {
                if (
                    strlen( $nodeType )
                    and
                    strlen( $applicationName )
                    and
                    (
                     $nodeType == WebServicesProfile::NODETYPE_APPLICATION
                     or
                     (
                      $nodeType == WebServicesProfile::NODETYPE_METHOD
                      and
                      strlen( $methodName )
                     )
                    )
                   )
                {
                    if ( $nodeType == WebServicesProfile::NODETYPE_METHOD )
                    {
                        // Checks if the application node is enabled
                        //
                        $tmpquery = &$this->mRootDb->execute( 'SELECT application, method '.
                                                             'FROM webservices_permissions '.
                                                             'WHERE profileid='.(int)$this->mProfileId.' '.
                                                             'AND application='.$this->mRootDb->formatText( $applicationName ).' '.
                                                             "AND method=''" );

                        if ( $tmpquery->getNumberRows() == 1 )
                        {
                            // Delete all nodes relative to the application
                            //
                            $this->mRootDb->execute( 'DELETE FROM webservices_permissions '.
                                                    'WHERE profileid='.(int)$this->mProfileId.' '.
                                                    'AND application='.$this->mRootDb->formatText( $applicationName ) );

                            // Enable all application methods nodes expect the method node to disable
                            //
                            $tmpqueryb = &$this->mRootDb->execute( 'SELECT name '.
                                                                  'FROM webservices_methods '.
                                                                  'WHERE application='.$this->mRootDb->formatText( $applicationName ) );

                            while ( !$tmpqueryb->eof )
                            {
                                if (
                                    strlen( $tmpqueryb->getFields( 'name' ) )
                                    and
                                    (
                                     $tmpqueryb->getFields( 'name' ) != $methodName
                                    )
                                   )
                                {
                                    $this->EnableNode( WebServicesProfile::NODETYPE_METHOD, $applicationName, $tmpqueryb->getFields( 'name' ) );
                                }
                                $tmpqueryb->moveNext();
                            }

                            $this->mRootDb->execute( 'INSERT INTO webservices_permissions VALUES ('.
                                                    $this->mProfileId.','.
                                                    $this->mRootDb->formatText( $applicationName ).','.
                                                    $this->mRootDb->formatText( $methodName ).')' );
                        }
                    }

                    $result = &$this->mRootDb->execute( 'DELETE FROM webservices_permissions '.
                                                       'WHERE profileid='.(int)$this->mProfileId.' '.
                                                       'AND application='.$this->mRootDb->formatText( $applicationName ).' '.
                                                       ( $nodeType == WebServicesProfile::NODETYPE_METHOD ? 'AND method='.$this->mRootDb->formatText( $methodName ) : '' ) );
                }
                else $this->mLog->logEvent( 'innomatic.webservicesprofile.disablenode',
                                            'Wrong parameters', Logger::ERROR );
            }
            else $this->mLog->logEvent( 'innomatic.webservicesprofile.disablenode',
                                        'Object not assigned to a profile', Logger::ERROR );
        }
        else $this->mLog->logEvent( 'innomatic.webservicesprofile.disablenode',
                                    'Invalid Innomatic database handler', Logger::ERROR );

        return $result;
    }

    /*!
     @function AvailableMethods

     @abstract Returns the list of the methods available for this profile.

     @result Associative array of the available methods and their attributes.
     */
    function AvailableMethods()
    {
        $result = false;

        if ( $this->mRootDb )
        {
            if ( $this->mProfileId )
            {
                $unsecure_lock = InnomaticContainer::instance('innomaticcontainer')->getConfig()->Value( 'SecurityLockUnsecureWebservices' );

                $query = &$this->mRootDb->execute(
                                                 'SELECT webservices_methods.name AS name, '.
                                                 'webservices_methods.handler AS handler, '.
                                                 'webservices_methods.application AS application, '.
                                                 'webservices_methods.function AS function, '.
                                                 'webservices_methods.unsecure AS unsecure, '.
                                                 'webservices_methods.docstring AS docstring '.
                                                 'FROM webservices_methods,webservices_permissions '.
                                                 'WHERE webservices_permissions.profileid='.(int)$this->mProfileId.' '.
                                                 'AND ( ( webservices_methods.application=webservices_permissions.application '.
                                                 "AND webservices_permissions.method='' ) ".
                                                 'OR webservices_methods.name=webservices_permissions.method )' );

                $result = array();

                while ( !$query->eof )
                {
                    if ( !( $query->getFields( 'unsecure' ) == $this->mRootDb->fmttrue and $unsecure_lock == '1' ) )
                    {
                        $result[] = $query->getFields();
                    }

                    $query->moveNext();
                }
            }
            else $this->mLog->logEvent( 'innomatic.webservicesprofile.availablemethods',
                                        'Object not assigned to a profile', Logger::ERROR );
        }
        else $this->mLog->logEvent( 'innomatic.webservicesprofile.availablemethods',
                                    'Invalid Innomatic database handler', Logger::ERROR );

        return $result;
    }

    /*!
     @function NodeCheck

     @abstract Checks if a node is enabled

     @param applicationName string - Node to check.
     @param nodeType integer - Node type.
     @param methodName string - Method name, may be empty.

     @result One of the WebServicesProfile::xNODE defines accordingly.
     */
    function NodeCheck( $nodeType, $applicationName, $methodName = '' )
    {
        $result = false;

        if ( $this->mRootDb )
        {
            if ( $this->mProfileId )
            {
                if (
                    strlen( $nodeType )
                    and
                    strlen( $applicationName )
                    and
                    (
                     $nodeType == WebServicesProfile::NODETYPE_APPLICATION
                     or
                     (
                      $nodeType == WebServicesProfile::NODETYPE_METHOD
                      and
                      strlen( $methodName )
                     )
                    )
                   )
                {
                    $query = &$this->mRootDb->execute( 'SELECT application,method '.
                                                      'FROM webservices_permissions '.
                                                      'WHERE application='.$this->mRootDb->formatText( $applicationName ).' '.
                                                      'AND profileid='.(int)$this->mProfileId.' '.
                                                      ( $nodeType == WebServicesProfile::NODETYPE_METHOD
                                                        ?
                                                        'AND ( method='.$this->mRootDb->formatText( $methodName ).' '.
                                                        "OR method='' )"
                                                        :
                                                        ''
                                                      )
                                                    );

                    if ( $query->getNumberRows() )
                    {
                        if ( $nodeType == WebServicesProfile::NODETYPE_APPLICATION )
                        {
                            if ( $query->getFields( 'method' ) ) $result = WebServicesProfile::APPLICATIONNODE_PARTIALLYENABLED;
                            else $result = WebServicesProfile::APPLICATIONNODE_FULLYENABLED;
                        }
                        else $result = WebServicesProfile::METHODNODE_ENABLED;
                    }
                    else
                    {
                        if ( $nodeType == WebServicesProfile::NODETYPE_APPLICATION ) $result = WebServicesProfile::APPLICATIONNODE_NOTENABLED;
                        else $result = WebServicesProfile::METHODNODE_NOTENABLED;
                    }
                }
                else $this->mLog->logEvent( 'innomatic.webservicesprofile.applicationnodecheck',
                                            'Wrong parameters', Logger::ERROR );
            }
            else $this->mLog->logEvent( 'innomatic.webservicesprofile.applicationnodecheck',
                                        'Object not assigned to a profile', Logger::ERROR );
        }
        else $this->mLog->logEvent( 'innomatic.webservicesprofile.applicationnodecheck',
                                    'Invalid Innomatic database handler', Logger::ERROR );

        return $result;
    }
}
