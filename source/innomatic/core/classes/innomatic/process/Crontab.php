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

/*!
 @class Crontab

 @abstract Handles cron jobs in a simple manner.

 @discussion Handles cron jobs in a simple manner.
 */
class Crontab
{
    /*! @var mAppId string - Application id name. */
    var $mAppId;
    /*! @var mRegCron ConfigMan class - Regular cron tab handler. */
    var $mRegCron;
    /*! @var mTempCron ConfigMan class - Temporary cron tab handler. */
    var $mTempCron;
    
    const TYPE_REGULAR = 0;
    const TYPE_TEMPORARY = 1;
    
    /*!
     @function Crontab

     @abstract Class constructor.

     @discussion Class constructor.

     @param appId string - Application id name.
     */
    function Crontab( $appId )
    {
        // Arguments check
        //
        if ( !empty( $appId ) ) $this->mAppId = $appId;
        else {
            require_once('innomatic/logging/Logger.php');
$log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
            $log->LogDie( 'innomatic.cron.simplecron.simplecron',
                                 'No application id name' );
        }
        /*
        if ( !empty( $ ) ) $this-> = $;
        else $this->mLog->logdie( 'innomatic.configman.configman.configman', '' );
        */

        require_once('innomatic/config/ConfigMan.php');
        $this->mRegCron  = new ConfigMan( $this->mAppId, InnomaticContainer::instance('innomaticcontainer')->getHome().'core/conf/crontab_regular', ConfigBase::MODE_DIRECT );
        $this->mTempCron = new ConfigMan( $this->mAppId, InnomaticContainer::instance('innomaticcontainer')->getHome().'core/conf/crontab_temporary', ConfigBase::MODE_DIRECT );
    }

    /*!
     @function AddEntry

     @abstract Adds an entry to the cron tab.

     @discussion Adds an entry to the cron tab.

     @param identifier string - Entry identifier.
     @param entry string - Entry content, the command to be executed.
     @param entryType integer - Crontab::TYPE_REGULAR if a regular cron tab entry, Crontab::TYPE_TEMPORARY if a temporary cron tab entry.

     @result TRUE if the entry has been added.
     */
    function AddEntry( $identifier, $entry, $entryType )
    {
        $result = FALSE;

        if (
            strlen( $identifier )
            and
            strlen( $entry )
            and
            strlen( $entryType )
           )
        {
            switch ( $entryType )
            {
            case Crontab::TYPE_REGULAR:
                $result = $this->mRegCron->changesegment( $this->mAppId.'-'.$identifier, $entry );
                break;

            case Crontab::TYPE_TEMPORARY:
                $result = $this->mTempCron->changesegment( $this->mAppId.'-'.$identifier, $entry );
                $this->mTempCron->changesegment( 'innomatic-cronremover', 'rm '.InnomaticContainer::instance('innomaticcontainer')->getHome().'core/conf/crontab_temporary'."\n" );
                break;

            default:
            require_once('innomatic/logging/Logger.php');
$log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
                $log->logEvent( 'innomatic.cron.simplecron.addentry',
                                      'Invalid entry type', Logger::ERROR );
                break;
            }
        }
        else {
            require_once('innomatic/logging/Logger.php');
$log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
            $log->logEvent( 'innomatic.cron.simplecron.addentry',
                                   'Empty identifier ('.$identifier.'), entry ('.$entry.') or entry type ('.$entryType.')', Logger::ERROR );
        }
        return $result;
    }

    /*!
     @function RemoveEntry

     @abstract Removes an entry from the cron tab.

     @discussion Removes an entry from the cron tab.

     @param identifier string - Entry identifier.
     @param entryType integer - Crontab::TYPE_REGULAR if a regular cron tab entry, Crontab::TYPE_TEMPORARY if a temporary cron tab entry.

     @result TRUE if the entry has been removed.
     */
    function RemoveEntry( $identifier, $entryType )
    {
        $result = FALSE;

        if (
            strlen( $identifier )
            and
            strlen( $entryType )
           )
        {
            switch ( $entryType )
            {
            case Crontab::TYPE_REGULAR:
                $result = $this->mRegCron->removesegment( $this->mAppId.'-'.$identifier );
                break;

            case Crontab::TYPE_TEMPORARY:
                $result = $this->mTempCron->removesegment( $this->mAppId.'-'.$identifier );
                $this->mTempCron->changesegment( 'innomatic-cronremover', 'rm '.InnomaticContainer::instance('innomaticcontainer')->getHome().'core/conf/crontab_temporary'."\n" );
                break;

            default:
            require_once('innomatic/logging/Logger.php');
$log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
                $log->logEvent( 'innomatic.cron.simplecron.removeentry',
                                      'Invalid entry type', Logger::ERROR );
                break;
            }
        }
        else {
            require_once('innomatic/logging/Logger.php');
$log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
            $log->logEvent( 'innomatic.cron.simplecron.removeentry',
                                   'Empty identifier ('.$identifier.') or entry type ('.$entryType.')', Logger::ERROR );
        }
        return $result;
    }
}
