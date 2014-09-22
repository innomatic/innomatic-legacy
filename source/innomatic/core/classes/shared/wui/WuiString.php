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
class WuiString extends \Innomatic\Wui\Widgets\WuiWidget
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

    /*
     * id string - HTML element id
     * autocomplete boolean - JQuery autocompletion
     * autocompleteminlength integer - Minimum length to activate the autocompletion, default is 3
     * autocompletesearchurl string - Backend url to use for searching autocompletion results
     * autocompletevalueid integer - Optional default value id when setting value argument
     */
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
            $this->mArgs['bgcolor'] = '';

        if (isset($this->mArgs['autocomplete']) and $this->mArgs['autocomplete'] == 'true') {
            $this->mArgs['autocomplete'] = true;
        } else {
            $this->mArgs['autocomplete'] = false;
        }

        if (!isset($this->mArgs['autocompleteminlength'])) {
            $this->mArgs['autocompleteminlength'] = 3;
        }
    }
    protected function generateSource()
    {
        $event_data = new \Innomatic\Wui\Dispatch\WuiEventRawData(isset($this->mArgs['disp']) ? $this->mArgs['disp'] : '', $this->mName);
        $event_data_id = new \Innomatic\Wui\Dispatch\WuiEventRawData(isset($this->mArgs['disp']) ? $this->mArgs['disp'] : '', $this->mName.'_id');

        $this->mLayout = $this->mComments ? '<!-- begin ' . $this->mName . ' string -->' : '';

        // JQuery autocomplete
        if ($this->mArgs['autocomplete'] == true) {
            //$jquery_id = 'jqautocomplete_'.$this->mName;

            $this->mLayout .= '<style>
.ui-autocomplete-loading { background: white url(\''.$this->mThemeHandler->mStyle['ajax_mini'].'\') right center no-repeat; background-size: 16px 16px;}
.ui-autocomplete {
max-height: 250px;
overflow-y: auto;
}
</style>';

            $this->mLayout .= "<script type=\"text/javascript\">
$(document).ready(function () {
$(\"#".$this->mArgs['id']."\").autocomplete({
source: \"".$this->mArgs['autocompletesearchurl']."\",
select: function (event, ui) {
$( \"#".$this->mArgs['id']."_value\" ).attr( \"value\", ui.item.id );
},
minLength: ".$this->mArgs['autocompleteminlength']."
});
});
</script>\n";

            $def_value = '';
            if (isset($this->mArgs['value']) and strlen($this->mArgs['value']) and isset($this->mArgs['autocompletevalueid']) and $this->mArgs['autocompletevalueid'] != '') {
                $def_value = ' value=\''.$this->mArgs['autocompletevalueid'].'\'';
            }
            $this->mLayout .= "<input type='hidden' name='".$event_data_id->getDataString()."' id='".$this->mArgs['id'].'_value\''.$def_value.'>';
        }

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
        $this->mLayout .= '<input'.(isset($this->mArgs['id']) ? ' id="'.$this->mArgs['id'].'"' : ''). $this->getEventsCompleteString().' ' . ((isset($this->mArgs['hint']) and strlen($this->mArgs['hint'])) ? 'onMouseOver="wuiHint(\'' . str_replace("'", "\'", $this->mArgs['hint']) . '\');" onMouseOut="wuiUnHint();" ' : '') . 'type="' . ((isset($this->mArgs['password']) and $this->mArgs['password'] == 'true') ? 'password' : 'text') . '" name="' . $event_data->getDataString() . '"';
        $this->mLayout .= ' tabindex="' . $this->mArgs['tabindex'] . '"';
        $this->mLayout .= (isset($this->mArgs['value']) and strlen($this->mArgs['value'])) ? ' value="' . \Innomatic\Wui\Wui::utf8_entities($this->mArgs['value']) . '"' : '';
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
