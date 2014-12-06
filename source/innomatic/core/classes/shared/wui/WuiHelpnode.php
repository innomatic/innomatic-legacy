<?php
/**
 * Innomatic
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2014 Innomatic Company
 * @license    http://www.innomatic.io/license/ New BSD License
 * @link       http://www.innomatic.io
 * @since      Class available since Release 5.0
 */
namespace Shared\Wui;

/**
 * @package WUI
 */
class WuiHelpnode extends \Innomatic\Wui\Widgets\WuiWidget
{
    public $mHint;
    public $mBase;
    public $mNode;
    public $mLanguage;
    public $mWidth = 400;
    public $mHeight = 500;
    public function __construct (
        $elemName,
        $elemArgs = '',
        $elemTheme = '',
        $dispEvents = ''
    )
    {
        parent::__construct($elemName, $elemArgs, $elemTheme, $dispEvents);
        if (isset($this->mArgs['hint']))
            $this->mHint = $this->mArgs['hint'];
        if (isset($this->mArgs['base']))
            $this->mBase = $this->mArgs['base'];
        if (isset($this->mArgs['node']))
            $this->mNode = $this->mArgs['node'];
        if (isset($this->mArgs['disp']))
            $this->mDisp = $this->mArgs['disp'];
        if (isset($this->mArgs['language']))
            $this->mLanguage = $this->mArgs['language'];
        if (isset($this->mArgs['width']) and strlen($this->mArgs['width']))
            $this->mWidth = $this->mArgs['width'];
        if (isset($this->mArgs['height']) and strlen($this->mArgs['height']))
            $this->mHeight = $this->mArgs['height'];
    }
    protected function generateSource()
    {
        $help_node = new \Innomatic\Help\HelpNode($this->mBase, $this->mNode, $this->mLanguage);
        $this->mLayout = ($this->mComments ? '<!-- begin ' . $this->mName . ' helpnode -->' : '') . '<iframe name="' . $this->mName . '"' . (strlen($this->mWidth) ? ' width="' . $this->mWidth . '"' : '') . (strlen($this->mHeight) ? ' height="' . $this->mHeight . '"' : '') . ' src="' . $help_node->getUrl() . '"' . ' frameborder="0">Your user agent does not support frames or is currently configured not to display frames.</iframe>' . ($this->mComments ? '<!-- end ' . $this->mName . " helpnode -->\n" : '');
        return true;
    }
}
