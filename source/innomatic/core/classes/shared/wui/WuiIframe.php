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
 * @license    http://www.innomaticplatform.com/license/   BSD License
 * @link       http://www.innomaticplatform.com
 * @since      Class available since Release 5.0
 */
namespace Shared\Wui;

/**
 * @package WUI
 */
class WuiIframe extends \Innomatic\Wui\Widgets\WuiWidget
{
    public $mSource;
    public $mWidth;
    public $mHeight;
    public $mScrolling;
    public $mMarginWidth = '0';
    public $mMarginHeight = '0';
    /*!
     @function WuiIFrame

     @abstract Class constructor.
     */
    public function __construct (
        $elemName,
        $elemArgs = '',
        $elemTheme = '',
        $dispEvents = ''
    )
    {
        parent::__construct($elemName, $elemArgs, $elemTheme, $dispEvents);
        if (isset($this->mArgs['source']))
            $this->mSource = $this->mArgs['source'];
        if (isset($this->mArgs['scrolling']) and ($elemArgs['scrolling'] == 'true' or $elemArgs['scrolling'] == 'false' or $elemArgs['scrolling'] == 'auto'))
            $this->mScrolling = $this->mArgs['scrolling'];
        else
            $this->mScrolling = 'auto';
        if (isset($this->mArgs['marginwidth']))
            $this->mMarginWidth = $this->mArgs['marginwidth'];
        if (isset($this->mArgs['marginheight']))
            $this->mMarginHeight = $this->mArgs['marginheight'];
        if (isset($this->mArgs['width']))
            $this->mWidth = $this->mArgs['width'];
        if (isset($this->mArgs['height']))
            $this->mHeight = $this->mArgs['height'];
    }
    protected function generateSource()
    {
        $this->mLayout = ($this->mComments ? '<!-- begin ' . $this->mName . ' iframe -->' : '') . '<iframe'.(isset($this->mArgs['id']) ? ' id="'.$this->mArgs['id'].'"' : '').' name="' . $this->mName . '"' . ' src="' . $this->mSource . '"' . ' scrolling="' . $this->mScrolling . '"' . (strlen($this->mWidth) ? ' width="' . $this->mWidth . '"' : '') . (strlen($this->mHeight) ? ' height="' . $this->mHeight . '"' : '') . (strlen($this->mMarginWidth) ? ' width="' . $this->mMarginWidth . '"' : '') . (strlen($this->mMarginHeight) ? ' height="' . $this->mMarginHeight . '"' : '') . ' frameborder="0">Your user agent does not support frames or is currently configured not to display frames.</iframe>' . ($this->mComments ? '<!-- end ' . $this->mName . " iframe -->\n" : '');
        return true;
    }
}
