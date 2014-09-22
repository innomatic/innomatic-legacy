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
namespace Shared\Wui;

/**
 * @package WUI
 */
class WuiProgressbar extends \Innomatic\Wui\Widgets\WuiWidget
{
    //public $mTotalSteps = 100;
    //public $mProgress = 0;
    public function __construct (
        $elemName,
        $elemArgs = '',
        $elemTheme = '',
        $dispEvents = ''
    )
    {
        parent::__construct($elemName, $elemArgs, $elemTheme, $dispEvents);
    }
    protected function generateSource()
    {
        $progress = $this->mArgs['progress'] == 0 ? '0' : round((100 * $this->mArgs['progress']) / $this->mArgs['totalsteps']);
        $left = 100 - $progress;
        $this->mLayout = ($this->mComments ? '<!-- begin ' . $this->mName . ' progress -->' : '');
        $this->mLayout .= "<table border=\"0\" width=\"100%\" cellspacing=\"2\" cellpadding=\"1\"><tr><td bgcolor=\"" . $this->mThemeHandler->mColorsSet['frames']['border'] . "\">\n";
        $this->mLayout .= "<table border=\"0\" width=\"100%\" cellspacing=\"1\" cellpadding=\"0\" bgcolor=\"white\">\n";
        $this->mLayout .= "<tr>\n";
        $this->mLayout .= '<td width="' . $progress . '%" height="7" class="status" nowrap style="white-space: nowrap" align="center" bgcolor="' . $this->mThemeHandler->mColorsSet['buttons']['selected'] . '"><img src="' . \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getBaseUrl(false) . '/shared/clear.gif" alt="" height="7"></td>' . "\n";
        $this->mLayout .= '<td width="' . $left . '%" bgcolor="white"></td></tr>' . "\n</table>\n";
        $this->mLayout .= "</td></tr>\n</table>\n";
        $this->mLayout .= ($this->mComments ? '<!-- end ' . $this->mName . " progressbar -->\n" : '');
        return true;
    }
}
