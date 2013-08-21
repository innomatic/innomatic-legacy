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
require_once ('innomatic/wui/widgets/WuiWidget.php');
/**
 * @package WUI
 */
class WuiImage extends WuiWidget
{
    /*! @public mImageUrl string - Url of the image. */
    //public $mImageUrl;
    /*! @public mWidth integer - Image width, optional. */
    //public $mWidth;
    /*! @public mHeight integer - Image height, optional. */
    //public $mHeight;
    /*! @public mHint string - Optional hint message. */
    //public $mHint;
    /*!
     @function WuiImage

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
    }
    protected function generateSource ()
    {
        $result = false;
        if (strlen($this->mArgs['imageurl'])) {
            $this->mLayout = ($this->mComments ? '<!-- begin ' . $this->mName . ' image -->' : '') . '<img'.(isset($this->mArgs['id']) ? ' id="'.$this->mArgs['id'].'"' : ''). $this->getEventsCompleteString() . ' '.' ' . ((isset($this->mArgs['hint']) and strlen($this->mArgs['hint'])) ? 'onMouseOver="wuiHint(\'' . str_replace("'", "\'", $this->mArgs['hint']) . '\');" onMouseOut="wuiUnHint(); "' : '') . 'src="' . $this->mArgs['imageurl'] . '" border="0"' . (isset($this->mArgs['width']) ? ' width="' . $this->mArgs['width'] . '"' : '') . (isset($this->mArgs['height']) ? ' height="' . $this->mArgs['height'] . '"' : '') . '>' . ($this->mComments ? '<!-- end ' . $this->mName . " image -->\n" : '');
            $result = true;
        }
        return $result;
    }
}
