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
namespace Innomatic\Process;

use \Innomatic\Core\InnomaticContainer;

/*!
 @class Crontab

 @abstract Handles cron jobs in a simple manner.

 @discussion Handles cron jobs in a simple manner.
 */
class Crontab
{
    /*! @var mAppId string - Application id name. */
    public $mAppId;
    /*! @var mRegCron ConfigMan class - Regular cron tab handler. */
    public $mRegCron;
    /*! @var mTempCron ConfigMan class - Temporary cron tab handler. */
    public $mTempCron;

    const TYPE_REGULAR = 0;
    const TYPE_TEMPORARY = 1;

    /*!
     @function Crontab

     @abstract Class constructor.

     @discussion Class constructor.

     @param appId string - Application id name.
     */
    public function __construct($appId)
    {
        // Arguments check
        //
        if ( !empty( $appId ) ) $this->mAppId = $appId;
        else {
            
$log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();
            $log->LogDie( 'innomatic.cron.simplecron.simplecron',
                                 'No application id name' );
        }
        /*
        if ( !empty( $ ) ) $this-> = $;
        else $this->mLog->logdie( 'innomatic.configman.configman.configman', '' );
        */

        $this->mRegCron  = new \Innomatic\Config\ConfigMan( $this->mAppId, \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome().'core/conf/crontab_regular', \Innomatic\Config\ConfigBase::MODE_DIRECT );
        $this->mTempCron = new \Innomatic\Config\ConfigMan( $this->mAppId, \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome().'core/conf/crontab_temporary', \Innomatic\Config\ConfigBase::MODE_DIRECT );
    }

    /*!
     @function AddEntry

     @abstract Adds an entry to the cron tab.

     @discussion Adds an entry to the cron tab.

     @param identifier string - Entry identifier.
     @param entry string - Entry content, the command to be executed.
     @param entryType integer - Crontab::TYPE_REGULAR if a regular cron tab entry, Crontab::TYPE_TEMPORARY if a temporary cron tab entry.

     @result true if the entry has been added.
     */
    public function AddEntry($identifier, $entry, $entryType)
    {
        $result = false;

        if (
            strlen( $identifier )
            and
            strlen( $entry )
            and
            strlen( $entryType )
           )
        {
            switch ( $entryType ) {
            case Crontab::TYPE_REGULAR:
                $result = $this->mRegCron->changesegment( $this->mAppId.'-'.$identifier, $entry );
                break;

            case Crontab::TYPE_TEMPORARY:
                $result = $this->mTempCron->changesegment( $this->mAppId.'-'.$identifier, $entry );
                $this->mTempCron->changesegment( 'innomatic-cronremover', 'rm '.\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome().'core/conf/crontab_temporary'."\n" );
                break;

            default:
            
$log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();
                $log->logEvent( 'innomatic.cron.simplecron.addentry',
                                      'Invalid entry type', \Innomatic\Logging\Logger::ERROR );
                break;
            }
        } else {
            
$log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();
            $log->logEvent( 'innomatic.cron.simplecron.addentry',
                                   'Empty identifier ('.$identifier.'), entry ('.$entry.') or entry type ('.$entryType.')', \Innomatic\Logging\Logger::ERROR );
        }
        return $result;
    }

    /*!
     @function RemoveEntry

     @abstract Removes an entry from the cron tab.

     @discussion Removes an entry from the cron tab.

     @param identifier string - Entry identifier.
     @param entryType integer - Crontab::TYPE_REGULAR if a regular cron tab entry, Crontab::TYPE_TEMPORARY if a temporary cron tab entry.

     @result true if the entry has been removed.
     */
    public function RemoveEntry($identifier, $entryType)
    {
        $result = false;

        if (
            strlen( $identifier )
            and
            strlen( $entryType )
           )
        {
            switch ( $entryType ) {
            case Crontab::TYPE_REGULAR:
                $result = $this->mRegCron->removesegment( $this->mAppId.'-'.$identifier );
                break;

            case Crontab::TYPE_TEMPORARY:
                $result = $this->mTempCron->removesegment( $this->mAppId.'-'.$identifier );
                $this->mTempCron->changesegment( 'innomatic-cronremover', 'rm '.\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome().'core/conf/crontab_temporary'."\n" );
                break;

            default:
            
$log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();
                $log->logEvent( 'innomatic.cron.simplecron.removeentry',
                                      'Invalid entry type', \Innomatic\Logging\Logger::ERROR );
                break;
            }
        } else {
            
$log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();
            $log->logEvent( 'innomatic.cron.simplecron.removeentry',
                                   'Empty identifier ('.$identifier.') or entry type ('.$entryType.')', \Innomatic\Logging\Logger::ERROR );
        }
        return $result;
    }
}
