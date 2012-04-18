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
require_once ('innomatic/wui/dispatch/WuiEventRawData.php');
require_once ('innomatic/locale/LocaleCatalog.php');
require_once ('innomatic/locale/LocaleCountry.php');
/**
 * @package WUI
 */
class WuiDate extends WuiWidget
{
    /*! @public mHint string - Help string for this element. */
    public $mHint;
    /*! @public mValue array - Default date. */
    public $mValue;
    /*! @public mDisp string - Dispatcher for this element. */
    public $mDisp;
    /*! @public mReadOnly boolean - Set to 'true' if this is a read only string. */
    public $mReadOnly;
    /*! @public mSize integer - Width in characters of the widget. */
    public $mSize = 10;
    /*! @public mMaxLength integer - Max string length. */
    public $mMaxLength = 10;
    /*! @public mCountry string - Country name, default to current user country. */
    public $mCountry;
    public $mLocaleCountryHandler;
    /*! @public mCountry string - Language name, default to current user language. */
    public $mLanguage;
    public $mLocaleHandler;
    /*! @public mTabIndex integer - Position of the current element in the tabbing order. */
    public $mTabIndex = 0;
    public $mType = 'date';
    
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
        if (isset($this->mArgs['value']))
            $this->mValue = $this->mArgs['value'];
        if (isset($this->mArgs['disp']))
            $this->mDisp = $this->mArgs['disp'];
        if (isset($this->mArgs['tabindex']))
            $this->mTabIndex = $this->mArgs['tabindex'];
        if (isset($this->mArgs['country']) and strlen($this->mArgs['country']))
            $this->mCountry = $this->mArgs['country'];
        else 
            if (InnomaticContainer::instance('innomaticcontainer')->isDomainStarted()) {
                $this->mCountry = InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getCountry();
            } else {
                $this->mCountry = InnomaticContainer::instance('innomaticcontainer')->getCountry();
            }
        if (isset($this->mArgs['language']) and strlen($this->mArgs['language']))
            $this->mLanguage = $this->mArgs['language'];
        else 
            if (InnomaticContainer::instance('innomaticcontainer')->isDomainStarted()) {
                $this->mLanguage = InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getLanguage();
            } else {
                $this->mLanguage = InnomaticContainer::instance('innomaticcontainer')->getLanguage();
            }
        $this->mLocaleCountryHandler = new LocaleCountry($this->mCountry);
        $this->mLocaleHandler = new LocaleCatalog('innomatic::wui', $this->mLanguage);
        if (isset($this->mArgs['readonly']))
            $this->mReadOnly = $this->mArgs['readonly'];
        if (isset($this->mArgs['type'])) {
            switch ($this->mArgs['type']) {
                case 'date':
                    $this->mSize = 10;
                    $this->mMaxLength = 10;
                    $this->mType = $this->mArgs['type'];
                    break;
                case 'time':
                    $this->mSize = 8;
                    $this->mMaxLength = 8;
                    $this->mType = $this->mArgs['type'];
                    break;
                case 'shorttime':
                    $this->mSize = 5;
                    $this->mMaxLength = 5;
                    $this->mType = $this->mArgs['type'];
                    break;
            }
        }
    }
    protected function generateSource ()
    {
        $result = false;
        $event_data = new WuiEventRawData($this->mDisp, $this->mName);
        $calendar_dateformat = str_replace('/', '\\/', $this->mLocaleCountryHandler->ShortDateFormat());
        $calendar_dateformat = str_replace('d', 'DD', $calendar_dateformat);
        $calendar_dateformat = str_replace('m', 'MM', $calendar_dateformat);
        $calendar_dateformat = str_replace('y', 'YY', $calendar_dateformat);
        $calendar_dateformat = str_replace('Y', 'YYYY', $calendar_dateformat);
        $this->mLayout = '';
        if ($this->mType == 'date') {
            $this->mLayout .= "<script language=\"JavaScript\">

Calendar.Title = '" . $this->mLocaleHandler->getStr('calendar') . "';
Calendar.TableGridColor = '" . $this->mThemeHandler->mColorsSet['tables']['gridcolor'] . "';
Calendar.TableBgColor = '" . $this->mThemeHandler->mColorsSet['tables']['bgcolor'] . "';
Calendar.TableHeaderBgColor = '" . $this->mThemeHandler->mColorsSet['tables']['headerbgcolor'] . "';

Calendar.WeekDays = new Array( '" . $this->mLocaleHandler->getStr('mon') . "',
    '" . $this->mLocaleHandler->getStr('tue') . "',
    '" . $this->mLocaleHandler->getStr('wed') . "',
    '" . $this->mLocaleHandler->getStr('thu') . "',
    '" . $this->mLocaleHandler->getStr('fri') . "',
    '" . $this->mLocaleHandler->getStr('sat') . "',
    '" . $this->mLocaleHandler->getStr('sun') . "');

Calendar.Months = new Array( '" . $this->mLocaleHandler->getStr('january') . "',
    '" . $this->mLocaleHandler->getStr('february') . "',
    '" . $this->mLocaleHandler->getStr('march') . "',
    '" . $this->mLocaleHandler->getStr('april') . "',
    '" . $this->mLocaleHandler->getStr('may') . "',
    '" . $this->mLocaleHandler->getStr('june') . "',
    '" . $this->mLocaleHandler->getStr('july') . "',
    '" . $this->mLocaleHandler->getStr('august') . "',
    '" . $this->mLocaleHandler->getStr('september') . "',
    '" . $this->mLocaleHandler->getStr('october') . "',
    '" . $this->mLocaleHandler->getStr('november') . "',
    '" . $this->mLocaleHandler->getStr('december') . "');
</script>";
        }
        $this->mLayout .= ($this->mComments ? '<!-- begin ' . $this->mName . ' date -->' : '') . '<span style="white-space: nowrap;"><input class="normal" ' . (strlen($this->mHint) ? 'onMouseOver="wuiHint(\'' . str_replace("'", "\'", $this->mHint) . '\');" onMouseOut="wuiUnHint();" ' : '') . 'type="text" name="' . $event_data->getDataString() . '"' . ' tabindex="' . $this->mTabIndex . '"' . (is_array($this->mValue) ? ' value="' . Wui::utf8_entities($this->mType == 'date' ? $this->mLocaleCountryHandler->FormatShortArrayDate($this->mValue) : $this->mLocaleCountryHandler->FormatArrayTime($this->mValue)) . '"' : '') . ($this->mHint ? ' alt="' . $this->mHint . '"' : '') . (strlen($this->mSize) ? ' size="' . $this->mSize . '"' : '') . (strlen($this->mMaxLength) ? ' maxlength="' . $this->mMaxLength . '"' : '') . (strlen($this->mReadOnly) ? ' readonly' : '') . '>' . ($this->mReadOnly != 'true' ? ($this->mType == 'date' ? "&nbsp;<a href=\"javascript:show_calendar( 'forms[' + GetFormNumber('" . $event_data->getDataString() . "') + '].elements[' +  GetElementNumber('" . $event_data->getDataString() . "') + ']'," . (is_array($this->mValue) ? "'" . sprintf('%u', $this->mValue['mon'] - 1) . "','" . $this->mValue['year'] . "'" : 'null,null') . ",'" . $calendar_dateformat . "');\">" . '<img src="' . $this->mThemeHandler->mIconsBase . $this->mThemeHandler->mIconsSet['mini']['kuser']['base'] . '/mini/' . $this->mThemeHandler->mIconsSet['mini']['1day']['file'] . '" alt="" border="0" style="width: 16px; height: 16px;"></a>' : '') : '') . '</span>' . ($this->mComments ? '<!-- end ' . $this->mName . " string -->\n" : '');
        $result = true;
        return $result;
    }
}
