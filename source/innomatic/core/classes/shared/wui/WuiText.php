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
namespace Shared\Wui;

/**
 * @package WUI
 */
class WuiText extends \Innomatic\Wui\Widgets\WuiWidget
{
    //public $mHint;
    /*! @public mValue string - Default value. */
    //public $mValue;
    //public $mDisp;
    //public $mRows;
    //public $mCols;
    //public $mReadOnly;
    /*! @public mRequired boolean - Set to 'true' if the value of the widget cannot be empty. */
    //public $mRequired;
    /*! @public mInteger boolean - Set to 'true' if the value of the widget must be an integer. */
    //public $mInteger;
    /*! @public mEmail boolean - Set to 'true' if the value of the widget must be an e-mail address. */
    //public $mEmail;
    /*! @public mCheckMessage string - Verbal description of the checks. */
    //public $mCheckMessage;
    //public $mBgColor;
    /*! @public mTabIndex integer - Position of the current element in the tabbing order. */
    //public $mTabIndex = 0;
    public function __construct (
        $elemName,
        $elemArgs = '',
        $elemTheme = '',
        $dispEvents = ''
    )
    {
        parent::__construct($elemName, $elemArgs, $elemTheme, $dispEvents);
        if (! isset($this->mArgs['tabindex']))
            $this->mArgs['tabindex'] = 0;
        if (! isset($this->mArgs['bgcolor']) or ! strlen($elemArgs['bgcolor']))
            $this->mArgs['bgcolor'] = '';
    }
    protected function generateSource()
    {
        $result = false;
        $event_data = new \Innomatic\Wui\Dispatch\WuiEventRawData(isset($this->mArgs['disp']) ? $this->mArgs['disp'] : '', $this->mName);
        $check_script = '';
        if ((isset($this->mArgs['required']) and $this->mArgs['required'] == 'true') || (isset($this->mArgs['integer']) and $this->mArgs['integer'] == 'true') || (isset($this->mArgs['email']) and $this->mArgs['email'] == 'true')) {
            $check_script = '
<script language="JavaScript" type="text/javascript">
<!--
requiredFields[requiredFields.length] = new Array( "' . $event_data->getDataString() . '", "' . $this->mArgs['checkmessage'] . '"' . ($this->mArgs['required'] == 'true' ? ', "required"' : '') . ($this->mArgs['integer'] == 'true' ? ', "integer"' : '') . ($this->mArgs['email'] == 'true' ? ', "email"' : '') . ' );
-->
</script>';
        }
        $this->mLayout = ($this->mComments ? '<!-- begin ' . $this->mName . ' textarea -->' : '') . '<textarea'.(isset($this->mArgs['id']) ? ' id="'.$this->mArgs['id'].'"' : '').' '.(isset($this->mArgs['maxlength']) ? ' maxlength="'.$this->mArgs['maxlength'].'"' : '') . ((isset($this->mArgs['hint']) and strlen($this->mArgs['hint'])) ? 'onMouseOver="wuiHint(\'' . str_replace("'", "\'", $this->mArgs['hint']) . '\');" onMouseOut="wuiUnHint();" ' : '') . 'name="' . $event_data->getDataString() . '"' . (strlen($this->mArgs['rows']) ? ' rows="' . $this->mArgs['rows'] . '"' : '') . (strlen($this->mArgs['bgcolor']) ? ' STYLE="background-color: ' . $this->mArgs['bgcolor'] . ';"' : '') . (strlen($this->mArgs['cols']) ? ' cols="' . $this->mArgs['cols'] . '"' : '') . ' tabindex="' . $this->mArgs['tabindex'] . '"' . ((isset($this->mArgs['readonly']) and strlen($this->mArgs['readonly'])) ? ' readonly' : '') . '>' . ((isset($this->mArgs['value']) and strlen($this->mArgs['value'])) ? \Innomatic\Wui\Wui::utf8_entities($this->mArgs['value']) : '') . '</textarea>' . $check_script . ($this->mComments ? '<!-- end ' . $this->mName . " textarea -->\n" : '');
        $result = true;
        return $result;
        
    }
}
