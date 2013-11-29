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

require_once('innomatic/core/InnomaticContainer.php');

/*!
 @class Locale

 @abstract Language abstraction through strings catalogs.

 @discussion Locale class provides a language abstraction, through strings
 catalogs. A catalog is a collection of a certain language strings.

 Example:

 <b>code</b>:

 $myloc = new LocaleCatalog( "innomatic::innomatic", "en" );<br>
 echo $myloc->getStr( "teststring" );

 <b>innomatic_en.ini file</b>:

 teststring = Test

 This will open a catalog file named innomatic_en.ini. If the english translation
 for that catalog doesn't exists, the class fallbacks to other languages in this order:
 Innomatic default language; English language; default catalog (the one named innomatic.ini).

 Catalog files are ASCII files containing the locale strings in the key = value format.

 Naming convention: [catalogname]_[lowcase two letters format language].ini

 e.g.

 Catalog: test<br>
 Language: Engligh

 becomes: test_en.ini
 */
class LocaleCatalog
{
    /*! @var catalog string - Catalog name */
    private $catalog;
    /*! @var lang string - Language */
    private $lang;
    /*! @var locales array - Array of the catalog strings */
    private $locales;
    const SAFE_TIMESTAMP = 'Y-m-d, h:i:s A';

    /*!
     @function Locale

     @abstract Class constructor.

     @param catalog string - catalog name
     @param lang string - language id
     */
    public function __construct($catalog, $lang = '')
    {
        $this->catalog = null;
        $this->lang = null;
        if (empty($lang)) {
            $lang = InnomaticContainer::instance('innomaticcontainer')->getLanguage();
        }
        $this->setLocaleCT($catalog);
        $this->setLocaleLang($lang);
        $this->OpenCatalog();
    }

    /*!
     @function setLocaleCT

     @abstract Sets catalog file for the locale.
     @param catalog string - Catalog name.

     @result Always true.
     */
    public function setLocaleCT($catalog)
    {
        $this->catalog = $catalog;
        return true;
    }

    /*!
     @function getLocaleCT
     @abstract Gets catalog file for this locale.
     @result Locale catalog file name.
     */
    public function getLocaleCT()
    {
        return $this->catalog;
    }

    /*!
     @function setLocaleLang
     @abstract Sets language for this locale.
     @param lang string - Language id.
     @result True if the catalog file was specified.
     */
    public function setLocaleLang($lang)
    {
        $result = false;
        if ($this->catalog != null) {
            $this->lang = $lang;
            $result = true;
        }
        return $result;
    }

    /*
     @function getLocaleLang

     @abstract Gets locale language.

     @result int language id.
     */
    public function getLocaleLang()
    {
        $result = false;
        if ($this->catalog != null) {
            $result = $this->lang;
        }
        return $result;
    }

    /*!
     @function OpenCatalog
     @abstract Opens the catalog and read the locale strings.
     @discussion If it cannot find the given language locale catalog, it tries to
     fallback to Innomatic language one, then english one, and default one
     (that is the catalog with no language specification at all).
     @result True if it is able to open and read the catalog file.
     */
    public function openCatalog()
    {
        if (!(($this->catalog != null) and ($this->lang != null))) {
            return false;
        }

        list($base, $catalog) = explode('::', $this->catalog);
        $innomatic = InnomaticContainer::instance('innomaticcontainer');

        // Tries specified language catalog
        //
        if (file_exists($innomatic->getHome().'core/locale/catalogs/'.$base.'/'.$this->lang.'/'.$catalog.'.ini')) {
            $catfile = $innomatic->getHome().'core/locale/catalogs/'.$base.'/'.$this->lang.'/'.$catalog.'.ini';
        }
        // Tries Innomatic language catalog
        //
        else if (file_exists($innomatic->getHome().'core/locale/catalogs/'.$base.'/'.InnomaticContainer::instance('innomaticcontainer')->getLanguage().'/'.$catalog.'.ini')) {
            $catfile = $innomatic->getHome().'core/locale/catalogs/'.$base.'/'.InnomaticContainer::instance('innomaticcontainer')->getLanguage().'/'.$catalog.'.ini';
        }
        // Tries English catalog
        //
        else if (file_exists($innomatic->getHome().'core/locale/catalogs/'.$base.'/en/'.$catalog.'.ini')) {
            $catfile = $innomatic->getHome().'core/locale/catalogs/'.$base.'/en/'.$catalog.'.ini';
        } else {
            require_once('innomatic/logging/Logger.php');
            $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
            $log->logEvent('innomatic/locale/Locale/opencatalog', 'Unable to find a catalog file for the specified catalog ('.$this->catalog.') and language ('.$this->lang.') or fallback to another language', \Innomatic\Logging\Logger::ERROR);
        }

        if (!empty($catfile)) {
            // New way to read locale catalogs
            //
            $this->locales = @parse_ini_file($catfile, false, INI_SCANNER_RAW);

            $result = true;

            /*
                if ( sizeof( $this->locales ) == 0 ) {
                if ( $fh = @fopen( $catfile, 'r' ) ) {
                fclose( $fh );

                include( $catfile );
                $this->catversion = $catversion;
                $this->catdate    = $catdate;
                $this->locales    = $locale;
                }
                }
                */
        }
    }

    /*!
     @function getStr
     @abstract Returns locale string of a certain key.
     @param id string - Locale string key.
     @result The string if the key was found, nothing otherwise.
     */
    public function getStr($id)
    {
        return isset($this->locales[$id]) ? $this->locales[$id] : '';
    }

    /*!
     @function PrintStr
     @abstract Writes a string to the stdout and returns it.
     @discussion This function should be avoided in normal interfaces, since it would break OOPHTML.
     @param id string - Locale string key.
     @result The string if the key was found, nothing otherwise.
     */
    public function printStr($id)
    {
        echo $this->locales[$id];
        return $this->locales[$id];
    }
}
