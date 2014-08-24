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
namespace Innomatic\Locale;

use \Innomatic\Core\InnomaticContainer;

/*!
 @abstract Country locale settings.
 */
class LocaleCountry
{
    /*! @var mCountry string - Country name. */
    protected $country;
    /*! @var mCountryShort string - Country short name. */
    protected $countryShort;
    /*! @var mLanguage string - Default country language. */
    protected $language;
    /*! @var mDecimalSeparator string - Numbers decimal separator. */
    protected $decimalSeparator;
    /*! @var mThousandsSeparator string - Numbers thousands separator. */
    protected $thousandsSeparator;
    /*! @var mPositiveSign string - Positive numbers sign. */
    protected $positiveSign;
    /*! @var mNegativeSign string - Negative numbers sign. */
    protected $negativeSign;
    /*! @var mCurrencySymbol string - Currency symbol. */
    protected $currencySymbol;
    /*! @var mMoneyDecimalSeparator string - Money decimal separator. */
    protected $moneyDecimalSeparator;
    /*! @var mMoneyThousandsSeparator string - Money thousands separator symbol. */
    protected $moneyThousandsSeparator;
    /*! @var fractDigits int - Value decimals. */
    protected $fractDigits;
    /*! @var mPositivePrefixCurrency bool. */
    protected $positivePrefixCurrency;
    /*! @var mPositiveSignPosition int - How to represent positive sign, refer to LocaleCountry::SIGNPOSITION_* defines. */
    protected $positiveSignPosition;
    /*! @var mNegativePrefixCurrencty bool. */
    protected $negativePrefixCurrency;
    /*! @var mPositiveSignPosition int - How to represent negative sign, refer to LocaleCountry::SIGNPOSITION_* defines. */
    protected $negativeSignPosition;
    /*! @var mTimeFormat string - Time formatting string. */
    protected $timeFormat;
    /*! @var mDateFormat string - Date formatting string. */
    protected $dateFormat;
    /*! @var mShortDateFormat string - Short date formatting string. */
    protected $shortDateFormat;
    /*! @var mStartWeekOnMonday bool - True if week starts on Monday. */
    protected $startWeekOnMonday;
    protected $dateSeparator;
    protected $dateOrder;
    protected $charSet;
    const SIGNPOSITION_PARENS_AROUND = 0;
    const SIGNPOSITION_BEFORE_QUANTITY_MONEY = 1;
    const SIGNPOSITION_AFTER_QUANTITY_MONEY = 2;
    const SIGNPOSITION_BEFORE_MONEY = 3;
    const SIGNPOSITION_AFTER_MONEY = 4;


    /*!
     @function LocaleCountry

     @abstract Class constructor.

     @param countryName string - Country name in English language.
     */
    public function __construct($countryName)
    {
        if (strlen($countryName)) {
            $this->country = $countryName;
            $this->open();
        } else {
            
            $log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();
            $log->logEvent('innomatic/locale/LocaleCountry', 'Empty country name', \Innomatic\Logging\Logger::ERROR);
        }
    }

    /*!
     @abstract Opens the country file and reads the definitions.
     @result True if the country file exists and has been read
     */
    public function open()
    {
        $result = false;
        $innomatic = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');

        $country_file = @parse_ini_file($innomatic->getHome().'core/locale/countries/'.$this->country.'.ini', false, INI_SCANNER_RAW);
        if ($country_file !== false) {
            $this->countryShort = $country_file['COUNTRYSHORT'];
            $this->language = $country_file['LANGUAGE'];
            $this->decimalSeparator = $country_file['DECIMALSEPARATOR'];
            $this->thousandsSeparator = $country_file['THOUSANDSSEPARATOR'];
            $this->positiveSign = $country_file['POSITIVESIGN'];
            $this->negativeSign = $country_file['NEGATIVESIGN'];
            $this->currencySymbol = $country_file['CURRENCYSYMBOL'];
            $this->moneyDecimalSeparator = $country_file['MONEYDECIMALSEPARATOR'];
            $this->moneyThousandsSeparator = $country_file['MONEYTHOUSANDSSEPARATOR'];
            $this->fractDigits = $country_file['FRACTDIGITS'];
            $this->positivePrefixCurrency = $country_file['POSITIVEPREFIXCURRENCY'];
            $this->positiveSignPosition = $country_file['POSITIVESIGNPOSITION'];
            $this->negativePrefixCurrency = $country_file['NEGATIVEPREFIXCURRENCY'];
            $this->negativeSignPosition = $country_file['NEGATIVESIGNPOSITION'];
            $this->timeFormat = $country_file['TIMEFORMAT'];
            $this->dateFormat = $country_file['DATEFORMAT'];
            $this->shortDateFormat = $country_file['SHORTDATEFORMAT'];
            $this->startWeekOnMonday = $country_file['STARTWEEKONMONDAY'];
            $this->dateSeparator = $country_file['DATESEPARATOR'];
            $this->dateOrder = $country_file['DATEORDER'];
            $this->charSet = $country_file['CHARSET'];

            $result = true;
        } else {
            
            $log = $innomatic->getLogger();
            $log->logEvent('innomatic/locale/LocaleCountry', 'Unable to open country file '.$innomatic->getHome().'core/locale/countries/'.$this->country.'.ini', \Innomatic\Logging\Logger::ERROR);
        }
        
        return $result;
    }

    /*!
     @abstract Formats a money amount.
     @result Formatted money amount.
     */
    public function formatMoney($amount)
    {
        // Formats with decimal and thousands separators and currency fract digits
        //
        $formatted_amount = number_format(abs($amount), $this->fractDigits, $this->moneyDecimalSeparator, $this->moneyThousandsSeparator);
        $formatted_currsymbol = $this->currencySymbol;

        if ($amount >= 0) {
            $sign_position = $this->positiveSignPosition;
            $prefix_currency = $this->positivePrefixCurrency;
            $sign = $this->positiveSign;
        } else {
            $sign_position = $this->negativeSignPosition;
            $prefix_currency = $this->negativePrefixCurrency;
            $sign = $this->negativeSign;
        }

        // Formats with the given sign position
        //
        switch ($sign_position) {
            case LocaleCountry::SIGNPOSITION_PARENS_AROUND :
                $formatted_amount = '('.$formatted_amount.')';
                break;

            case LocaleCountry::SIGNPOSITION_BEFORE_QUANTITY_MONEY :
                $formatted_amount = $sign.$formatted_amount;
                break;

            case LocaleCountry::SIGNPOSITION_AFTER_QUANTITY_MONEY :
                $formatted_amount = $formatted_amount.$sign;
                break;

            case LocaleCountry::SIGNPOSITION_BEFORE_MONEY :
                $formatted_currsymbol = $sign.$formatted_currsymbol;
                break;

            case LocaleCountry::SIGNPOSITION_AFTER_MONEY :
                $formatted_currsymbol = $formatted_currsymbol.$sign;
                break;
        }

        // Formats amount and currency symbol in the right order
        //
        if ($prefix_currency)
            $result = $formatted_currsymbol.' '.$formatted_amount;
        else
            $result = $formatted_amount.' '.$formatted_currsymbol;

        return $result;
    }

    /*!
     @function FormatNumber
     @abstract Formats a number.
     @result Formatted number.
     */
    public function formatNumber($number, $decimals = 0)
    {
            // Formats with decimal and thousands separators
        //
    $result = number_format($number, $decimals, $this->decimalSeparator, $this->thousandsSeparator);

        // Formats with sign symbol
        //
        return $number >= 0 ? $this->positiveSign.$result : $this->NegativeSign.$result;
    }

    /*** DATE AND TIME ***/

    /* Getters */

    /*!
    @function TimeFormat
    */
    public function timeFormat()
    {
        return $this->timeFormat;
    }

    /*!
    @function DateFormat
    */
    public function dateFormat()
    {
        return $this->dateFormat;
    }

    /*!
    @function ShortDateFormat
    */
    public function shortDateFormat()
    {
        return $this->shortDateFormat;
    }

    /*!
    @function StartWeekOnMonday
    */
    public function startWeekOnMonday()
    {
        return $this->startWeekOnMonday;
    }

    /*!
    @function DateSeparator
    */
    public function dateSeparator()
    {
        return $this->dateSeparator;
    }

    /*!
    @function DateOrder
    */
    public function dateOrder()
    {
        return $this->dateOrder;
    }

    /*!
     @function FormatDate
     @abstract Formats a date string.
     @param date int - UNIX timestamp.
     @result Formatted date.
     */
    public function formatDate($date)
    {
        return date($this->dateFormat, $date);
    }

    /*!
     @function FormatArrayDate
     @abstract Formats a date array.
     @param dateArray array - Array of date parameters.
     @result Formatted date.
     */
    public function formatArrayDate($dateArray)
    {
        return $this->formatDate(mktime($dateArray['hours'], $dateArray['minutes'], $dateArray['seconds'], $dateArray['mon'], $dateArray['mday'], $this->NormalizeYear($dateArray['year'])));
    }

    /*!
     @function FormatShortDate
     @abstract Formats a date string in short format.
     @param date int - UNIX timestamp.
     @result Formatted date.
     */
    public function formatShortDate($date)
    {
        return date($this->shortDateFormat, $date);
    }

    /*!
     @function FormatShortArrayDate
     @abstract Formats a date array in short format.
     @param dateArray array - Array of date parameters.
     @result Formatted date.
     */
    public function formatShortArrayDate($dateArray, $format = '')
    {
        if (!strlen($format))
            $date = $this->shortDateFormat;
        else
            $date = $format;

        if (!isset($dateArray['hours']))
            $dateArray['hours'] = '';
        if (!isset($dateArray['minutes']))
            $dateArray['minutes'] = '';
        if (!isset($dateArray['seconds']))
            $dateArray['seconds'] = '';
        if (!isset($dateArray['year']))
            $dateArray['year'] = '';
        if (!isset($dateArray['mon']))
            $dateArray['mon'] = '';
        if (!isset($dateArray['mday']))
            $dateArray['mday'] = '';

        $dateArray['year'] = $this->NormalizeYear($dateArray['year']);

        $date = str_replace('a', $dateArray['hours'] > 12 ? 'pm' : 'am', $date);
        $date = str_replace('A', $dateArray['hours'] > 12 ? 'PM' : 'AM', $date);
        $date = str_replace('d', str_pad($dateArray['mday'], 2, '0', STR_PAD_LEFT), $date);
        $date = str_replace('g', $dateArray['hours'] > 12 ? $dateArray['hours'] - 12 : $dateArray['hours'], $date);
        $date = str_replace('G', $dateArray['hours'], $date);
        $date = str_replace('h', str_pad($dateArray['hours'] > 12 ? $dateArray['hours'] - 12 : $dateArray['hours'], 2, '0', STR_PAD_LEFT), $date);
        $date = str_replace('H', str_pad($dateArray['hours'], 2, '0', STR_PAD_LEFT), $date);
        $date = str_replace('i', str_pad($dateArray['minutes'], 2, '0', STR_PAD_LEFT), $date);
        $date = str_replace('j', $dateArray['mday'], $date);
        $date = str_replace('m', str_pad($dateArray['mon'], 2, '0', STR_PAD_LEFT), $date);
        $date = str_replace('n', $dateArray['mon'], $date);
        $date = str_replace('s', str_pad($dateArray['seconds'], 2, '0', STR_PAD_LEFT), $date);
        $date = str_replace('Y', $dateArray['year'], $date);
        $date = str_replace('y', substr($dateArray['year'], -2), $date);

        return $date;
    }

    /*!
     @function SafeFormatTimestamp
     @abstract Safely formats an Unix timestamp to a localized datestamp.
     @param unixTimestamp integer - Unix timestamp. Defaults to current timestamp.
     @result A safe datestamp.
     */
    public function safeFormatTimestamp($unixTimestamp = '')
    {
        return date(LocaleCatalog::SAFE_TIMESTAMP, strlen($unixTimestamp) ? $unixTimestamp : time());
    }

    /*!
     @function FormatTime
     @abstract Formats a time string.
     @param time int - UNIX timestamp.
     @result Formatted time.
     */
    public function formatTime($time)
    {
        return date($this->timeFormat, $time);
    }

    /*!
     @function FormatArrayTime
     @abstract Formats a time array in short format.
     @param dateArray array - Array of date parameters.
     @result Formatted time.
     */
    public function formatArrayTime($dateArray)
    {
        return $this->FormatShortArrayDate($dateArray, $this->timeFormat);

        /*$timestamp = mktime(
            $dateArray['hours'],
            $dateArray['minutes'],
            $dateArray['seconds'],
            $dateArray['mon'],
            $dateArray['mday'],
            $dateArray['year'] );
        return $this->FormatTime( $timestamp );*/
    }

    /*!
     @function getDateArrayFromShortDateStamp
     @abstract Gets a date array from a localized datestamp in short format, like "12/31/2002".
     @param datestamp string - Date stamp.
     @result Array of date parameters.
     */
    public function getDateArrayFromShortDateStamp($datestamp)
    {
        $order = $this->DateOrder();
        $date_elements = explode($this->DateSeparator(), $datestamp);
        $fmt = "%". (strpos($order, 'y') + 1)."\$s-%". (strpos($order, 'm') + 1)."\$s-%". (strpos($order, 'd') + 1)."\$s";

        list ($date['year'], $date['mon'], $date['mday']) = explode('-', sprintf($fmt, $date_elements[0], $date_elements[1], $date_elements[2]));
        $date['year'] = $this->NormalizeYear($date['year']);

        return $date;
    }

    /*!
     @function getDateArrayFromSafeTimestamp
     @abstract Gets a date array from a safe time stamp, like the one returned by SafeFormatTimestamp().
     @param timestamp string - Safe timestamp.
     @result A safe date array.
     */
    public function getDateArrayFromSafeTimestamp($timestamp)
    {
        $timestamp = str_replace(',', '', $timestamp);
        $date_elements = explode(' ', $timestamp);
        list ($date['year'], $date['mon'], $date['mday']) = explode('-', $date_elements[0]);
        list ($date['hours'], $date['minutes'], $date['seconds']) = explode(':', $date_elements[1]);

        if (isset($date_elements[2]) and $date_elements[2] == 'PM')
            $date['hours'] += 12;

        return $date;
    }

    public function getDateArrayFromUnixTimestamp($timestamp)
    {
        if (!strlen($timestamp)) {
            return false;
        }
        $result['year'] = date('Y', $timestamp);
        $result['mon'] = date('m', $timestamp);
        $result['mday'] = date('d', $timestamp);
        $result['hours'] = date('H', $timestamp);
        $result['minutes'] = date('i', $timestamp);
        $result['seconds'] = date('s', $timestamp);
        return $result;
    }

    public function normalizeYear($year)
    {
        $result = $year;

        switch (strlen($year)) {
            case '0' :
                $result = '2000';
                break;
            case '1' :
                $result = '200'.$year;
                break;
            case '2' :
                $result = '20'.$year;
                break;
            case '3' :
                $result = '2'.$year;
                break;
        }

        return $result;
    }

    /*!
     @function Language
     @abstract Returns default country language.
     */
    public function language()
    {
        return $this->language;
    }

    /*!
     @function Country
     @abstract Returns country name.
     @result Country name.
     */
    public function country()
    {
        return $this->country;
    }

    /*!
     @function CountryShort
     @abstract Returns country short name.
     @result Country short name.
     */
    public function countryShort()
    {
        return $this->countryShort;
    }

    /*!
     @function DecimalSeparator
     @abstract Returns decimal separator.
     @result Decimal separator.
     */
    public function decimalSeparator()
    {
        return $this->decimalSeparator;
    }

    /*!
     @function ThousandsSeparator
     @abstract Returns thousands separator.
     @result Thousands separator.
     */
    public function thousandsSeparator()
    {
        return $this->thousandsSeparator;
    }

    /*!
     @function PositiveSign
     @abstract Returns positive sign.
     @result Positive sign.
     */
    public function positiveSign()
    {
        return $this->positiveSign;
    }

    /*!
     @function NegativeSign
     @abstract Returns negative sign.
     @result Negative sign.
     */
    public function negativeSign()
    {
        return $this->negativeSign;
    }

    /*!
     @function CurrencySymbol
     @abstract Returns currency symbol.
     @result Currency symbol.
     */
    public function currencySymbol()
    {
        return $this->currencySymbol;
    }

    /*!
     @function MoneyDecimalSeparator
     @abstract Returns money decimal separator.
     @result Money decimal separator.
     */
    public function moneyDecimalSeparator()
    {
        return $this->moneyDecimalSeparator;
    }

    /*!
     @function MoneyThousandsSeparator
     @abstract Returns money thousands separator.
     @result Money thousands separator.
     */
    public function moneyThousandsSeparator()
    {
        return $this->moneyThousandsSeparator;
    }

    /*!
     @function FractDigits
     @abstract Returns money fract digits.
     @result Money fract digits.
     */
    public function fractDigits()
    {
        return $this->fractDigits;
    }

    public function positivePrefixCurrency()
    {
        return $this->positivePrefixCurrency;
    }

    public function positiveSignPosition()
    {
        return $this->positiveSignPosition;
    }

    public function negativePrefixCurrency()
    {
        return $this->negativePrefixCurrency;
    }

    public function negativeSignPosition()
    {
        return $this->negativeSignPosition;
    }

    public function getCharSet()
    {
        return $this->charSet;
    }
}
