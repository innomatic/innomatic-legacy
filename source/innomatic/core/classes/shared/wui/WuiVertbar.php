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
namespace Shared\Wui;

require_once ('innomatic/wui/widgets/WuiWidget.php');
/**
 * @package WUI
 */
class WuiVertbar extends WuiWidget
{
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
        $this->mLayout = ($this->mComments ? '<!-- begin ' . $this->mName . ' horizbar -->' . "\n" : '') . '<table border="0" cellspacing="1" cellpadding="1" bgcolor="white" width="0%" height="100%" style="height: 100%"><tr><td>';
        $this->mLayout .= '<table border="0" cellspacing="0" cellpadding="0" style="height: 100%" width="0%" height="100%">' . "\n";
        $this->mLayout .= '<tr><td bgcolor="' . $this->mThemeHandler->mColorsSet['bars']['color'] . '" width="1" height="100%"><img src="' . InnomaticContainer::instance('innomaticcontainer')->getBaseUrl(false) . '/shared/clear.gif" border="0" alt=""></td>' . "\n";
        $this->mLayout .= '<td bgcolor="' . $this->mThemeHandler->mColorsSet['bars']['shadow'] . '" width="1" height="100%"><img src="' . InnomaticContainer::instance('innomaticcontainer')->getBaseUrl(false) . '/shared/clear.gif" border="0" alt=""></td></tr>' . "\n";
        $this->mLayout .= "</table>\n";
        $this->mLayout .= "</td></tr></table>\n" . ($this->mComments ? '<!-- end ' . $this->mName . ' horizbar -->' . "\n" : '');
        return true;
    }
}
