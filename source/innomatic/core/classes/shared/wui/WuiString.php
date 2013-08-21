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
class WuiString extends WuiWidget
{
    /*! @public mHint string - Help string for this element. */
    //public $mHint;
    /*! @public mValue string - Default content. */
    //public $mValue;
    /*! @public mDisp string - Dispatcher for this element. */
    public $mDisp;
    /*! @public mPassword boolean - Set to 'true' if this is a password string. */
    //public $mPassword;
    /*! @public mReadOnly boolean - Set to 'true' if this is a read only string. */
    //public $mReadOnly;
    /*! @public mRequired boolean - Set to 'true' if the value of the widget cannot be empty. */
    //public $mRequired;
    /*! @public mInteger boolean - Set to 'true' if the value of the widget must be an integer. */
    //public $mInteger;
    /*! @public mEmail boolean - Set to 'true' if the value of the widget must be an e-mail address. */
    //public $mEmail;
    /*! @public mCheckMessage string - Verbal description of the checks. */
    //public $mCheckMessage;
    /*! @public mSize integer - Width in characters of the widget. */
    //public $mSize;
    /*! @public mMaxLength integer - Max string length. */
    //public $mMaxLength;
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
        if (! isset($this->mArgs['bgcolor']) or ! strlen($this->mArgs['bgcolor']))
            $this->mArgs['bgcolor'] = 'white';
    }
    protected function generateSource ()
    {
        require_once ('innomatic/wui/dispatch/WuiEventRawData.php');
        $event_data = new WuiEventRawData(isset($this->mArgs['disp']) ? $this->mArgs['disp'] : '', $this->mName);
        if ((isset($this->mArgs['required']) and $this->mArgs['required'] == 'true') || (isset($this->mArgs['integer']) and $this->mArgs['integer'] == 'true') || (isset($this->mArgs['email']) and $this->mArgs['email'] == 'true')) {
            $check_script = '
<script language="JavaScript" type="text/javascript">
<!--
requiredFields[requiredFields.length] = new Array( "' . $event_data->getDataString() . '", "' . $this->mArgs['checkmessage'] . '"';
            $check_script .= (isset($this->mArgs['required']) and $this->mArgs['required'] == 'true') ? ', "required"' : '';
            $check_script .= (isset($this->mArgs['integer']) and $this->mArgs['integer'] == 'true') ? ', "integer"' : '';
            $check_script .= (isset($this->mArgs['email']) and $this->mArgs['email'] == 'true') ? ', "email"' : '';
            $check_script .= ' );
-->
</script>';
        }
        $this->mLayout = $this->mComments ? '<!-- begin ' . $this->mName . ' string -->' : '';
        $this->mLayout .= '<input'.(isset($this->mArgs['id']) ? ' id="'.$this->mArgs['id'].'"' : ''). $this->getEventsCompleteString().' class="normal" ' . ((isset($this->mArgs['hint']) and strlen($this->mArgs['hint'])) ? 'onMouseOver="wuiHint(\'' . str_replace("'", "\'", $this->mArgs['hint']) . '\');" onMouseOut="wuiUnHint();" ' : '') . 'type="' . ((isset($this->mArgs['password']) and $this->mArgs['password'] == 'true') ? 'password' : 'text') . '" name="' . $event_data->getDataString() . '"';
        $this->mLayout .= ' tabindex="' . $this->mArgs['tabindex'] . '"';
        $this->mLayout .= (isset($this->mArgs['value']) and strlen($this->mArgs['value'])) ? ' value="' . Wui::utf8_entities($this->mArgs['value']) . '"' : '';
        $this->mLayout .= (isset($this->mArgs['hint']) and $this->mArgs['hint']) ? ' alt="' . $this->mArgs['hint'] . '"' : '';
        $this->mLayout .= (isset($this->mArgs['bgcolor']) and strlen($this->mArgs['bgcolor'])) ? ' style="background-color: ' . $this->mArgs['bgcolor'] . ';"' : '';
        $this->mLayout .= (isset($this->mArgs['size']) and strlen($this->mArgs['size'])) ? ' size="' . $this->mArgs['size'] . '"' : '';
        $this->mLayout .= (isset($this->mArgs['maxlength']) and strlen($this->mArgs['maxlength'])) ? ' maxlength="' . $this->mArgs['maxlength'] . '"' : '';
        $this->mLayout .= (isset($this->mArgs['readonly']) and strlen($this->mArgs['readonly'])) ? ' readonly' : '';
        $this->mLayout .= '>' . ((isset($check_script) and strlen($check_script)) ? $check_script : '');
        $this->mLayout .= $this->mComments ? '<!-- end ' . $this->mName . " string -->\n" : '';
        return true;
    }
}
