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
namespace Innomatic\Config;

/*!
 @class ConfigMan

 @abstract Configuration files management.

 @discussion Configuration files are seen by this class as segments.
 */
class ConfigMan extends ConfigBase
{
    const TAGDELIMITER = '###';
    const BEGINTAG = '-BEGIN-';
    const ENDTAG = '-END-';
    const POSITION_TOP = 1;
    const POSITION_BOTTOM = 2;


    /*! @var mCommentPrefix string - Optional comment prefix, useful for non standard comments. */
    private $_commentPrefix;

    // string $appid:      application id name, used to mark the segments
    // string $configfile: path of the configuration file
    //
    public function __construct(
        $application,
        $configfile,
        $configmode = ConfigBase::MODE_ROOT,
        $autoCommit = false, $entry = ''
        )
    {
        $this->ConfigBase($configfile, $configmode, $autoCommit, $application, $entry);
        // Arguments check
        //
        if (!empty($application))
        $this->application = $application;
        else {
            
            $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
            $log->logdie('innomatic.configman.configman.configman', 'No application id name', LOGGER_FAULT);
        }
        $this->_commentPrefix = '';
    }

    // Public methods

    // Adds a new segment
    //
    public function AddSegment($segid, $segment, $position = ConfigMan::POSITION_BOTTOM)
    {
        $result = false;

        // Arguments check
        //
        if (!empty($segid) and !empty($segment)) {
            $src = $this->getsrcfile();

            $this->lockfile();

            // Reads the configuration file, if it exists
            //

            if (file_exists($src) and $sh = @fopen($src, 'r')) {
                $buffer = null;

                while (!feof($sh)) {
                    $buffer.= fread($sh, 4096);
                }

                fclose($sh);
            }

            // Writes the configuration file
            //
            if ($fh = @fopen($this->getdestfile(), 'w')) {
                // Executed if the segment must be positioned in the bottom of the file
                //
                if (!empty($buffer) and ($position == ConfigMan::POSITION_BOTTOM))
                @fwrite($fh, $buffer);

                // Writes segment block
                //
                @fputs(
                    $fh, $this->_commentPrefix.self::TAGDELIMITER
                    .$this->mApplication.self::BEGINTAG.$segid
                    .self::TAGDELIMITER."\n"
                );
                @fputs($fh, $segment); // !! it should check for EOL
                @fputs(
                    $fh,
                    $this->_commentPrefix.self::TAGDELIMITER.$this->mApplication
                    .self::ENDTAG.$segid.self::TAGDELIMITER."\n"
                );

                // Executed if the segment must be positioned in the top of the file
                //
                if (!empty($buffer) and ($position == ConfigMan::POSITION_TOP))
                @fwrite($fh, $buffer);

                @fclose($fh);

                $result = true;
            } else {
                
                $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
                $log->logEvent(
                    'innomatic.configman.configman.addsegment',
                    'Unable to open destination configuration file '.$this->getdestfile(),
                    \Innomatic\Logging\Logger::ERROR
                );
            }
            $this->UpdateLock();
            $this->unlockfile();
        } else {
            
            $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
            $log->logEvent(
                'innomatic.configman.configman.addsegment',
                'Missing segment id and/or segment',
                \Innomatic\Logging\Logger::ERROR
            );
        }
        return $result;
    }

    // Removes a segment
    //
    public function RemoveSegment($segid)
    {
        $result = false;

        if (!empty($segid)) {
            $src = $this->getsrcfile();

            $this->lockfile();

            if (file_exists($src) and $fh = @fopen($src, 'r')) {
                $result = true;

                $buffer = null;

                $state = 'ADD';

                while (!feof($fh)) {
                    $currline = fgets($fh);
                    if (
                        strcmp(
                            $currline,
                            $this->_commentPrefix.ConfigMan::TAGDELIMITER
                            .$this->mApplication.ConfigMan::BEGINTAG
                            .$segid.ConfigMan::TAGDELIMITER."\n"
                        ) == 0
                    )
                    $state = 'PASS';
                    if (strcmp($state, 'ADD') == 0)
                    $buffer.= $currline;
                    if (
                        strcmp(
                            $currline,
                            $this->_commentPrefix.ConfigMan::TAGDELIMITER
                            .$this->mApplication.ConfigMan::ENDTAG
                            .$segid.ConfigMan::TAGDELIMITER."\n"
                        ) == 0
                    )
                    $state = 'ADD';
                }
                @fclose($fh);

                if ($fhd = @fopen($this->getdestfile(), 'w')) {
                    @fwrite($fhd, $buffer);
                    @fclose($fhd);
                } else {
                    
                    $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
                    $log->logEvent(
                        'innomatic.configman.configman.removesegment',
                        'Unable to open destination configuration file '.$this->getdestfile(),
                        \Innomatic\Logging\Logger::ERROR
                    );
                }
            }

            $this->unlockfile();
            $this->UpdateLock();
        } else {
            
            $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
            $log->logEvent('innomatic.configman.configman.removesegment', 'Missing segment id', \Innomatic\Logging\Logger::ERROR);
        }
        return $result;
    }

    // Changes a segment
    //
    public function ChangeSegment($segid, $segment, $position = ConfigMan::POSITION_BOTTOM)
    {
        $result = false;

        if (!empty($segid) and !empty($segment)) {
            $this->removesegment($segid);
            $result = $this->addsegment($segid, $segment, $position);
        } else {
            
            $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
            $log->logEvent(
                'innomatic.configman.configman.changesegment',
                'Missing segment id and/or segment',
                \Innomatic\Logging\Logger::ERROR
            );
        }
        return $result;
    }
}
