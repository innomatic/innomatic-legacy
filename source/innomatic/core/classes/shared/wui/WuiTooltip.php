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
 * @since      Class available since Release 6.4.0
 */
namespace Shared\Wui;

class WuiTooltip extends \Innomatic\Wui\Widgets\WuiWidget
{
    public function __construct (
        $elemName,
        $elemArgs = '',
        $elemTheme = '',
        $dispEvents = ''
    )
    {
        parent::__construct($elemName, $elemArgs, $elemTheme, $dispEvents);
        if (isset($this->mArgs['content'])) {
            $this->mArgs['content'] = addslashes($this->mArgs['content']);
        }
    }

    protected function generateSource()
    {
        $image = $this->mThemeHandler->mIconsBase . $this->mThemeHandler->mIconsSet['icons']['info']['base'] . '/icons/' . $this->mThemeHandler->mIconsSet['icons']['info']['file'];

        $image_width = 20;
        $image_height = 20;

        $this->mLayout = ($this->mComments ? '<!-- begin ' . $this->mName . ' button -->' : '').'
    <a href="#" class="tooltip"><img src="'.$image.'" alt="" style="width: ' . $image_width . 'px; height: ' . $image_height . 'px;" />
  <span>'.$this->mArgs['content'].'</span>
</a>
    '. ($this->mComments ? '<!-- end ' . $this->mName . " button -->\n" : '');
        return true;
    }
}
