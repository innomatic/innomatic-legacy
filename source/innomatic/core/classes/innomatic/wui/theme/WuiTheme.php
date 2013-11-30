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

require_once('innomatic/datatransfer/cache/CachedItem.php');
require_once('innomatic/dataaccess/DataAccess.php');
require_once('innomatic/wui/theme/WuiStyle.php');
require_once('innomatic/wui/theme/WuiIconsSet.php');
require_once('innomatic/wui/theme/WuiColorsSet.php');

/**
 * WUI themes handler.
 *
 * @package WUI
 */
class WuiTheme
{
    private $mrRootDb;
    /*! @var mTheme string - Theme name. */
    private $mTheme;
    /*! @var mThemeFile string - Theme file full path. */
    private $mThemeFile;

    private $mUserSettings;

    public $mIconsSetName;
    public $mIconsSetBase;
    public $mIconsSetDir;
    public $mIconsSet = array();
    public $mIconsBase;

    public $mColorsSetName;
    public $mColorsSet = array();

    public $mStyleBase;
    public $mStyleName;
    public $mStyleDir;
    public $mStyle = array();

    public function __construct($rrootDb, $themeName = 'default', $userSettings = '')
    {
        $this->mrRootDb = $rrootDb;
        if (strlen($themeName)) {
            $this->mTheme = $themeName;
            $this->InitTheme();
        }
        $this->mUserSettings = $userSettings;
    }

    public function initTheme()
    {
        $result = false;
        if (strlen($this->mTheme)) {

            require_once('innomatic/core/InnomaticContainer.php');
            $innomatic = InnomaticContainer::instance('innomaticcontainer');

            if ($this->mTheme == 'default')
            $this->mTheme = Wui::DEFAULT_THEME;
            if ($this->mTheme != 'userdefined') {
                if (file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/conf/themes/'.$this->mTheme.'_wuitheme.ini')) {
                    $this->mThemeFile = InnomaticContainer::instance('innomaticcontainer')->getHome().'core/conf/themes/'.$this->mTheme.'_wuitheme.ini';
                } else {
                    $this->mTheme = Wui::DEFAULT_THEME;
                    $this->mThemeFile = InnomaticContainer::instance('innomaticcontainer')->getHome().'core/conf/themes/'.Wui::DEFAULT_THEME.'_wuitheme.ini';
                }
                $cfg_file = @parse_ini_file($this->mThemeFile);

                if ($cfg_file !== false) {
                    $this->mIconsSetName = $cfg_file['THEME.ICONSSET'];
                    $this->mColorsSetName = $cfg_file['THEME.COLORSSET'];
                    $this->mStyleName = $cfg_file['THEME.STYLE'];
                } else {
                    
                    $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
                    $log-> LogEvent('innomatic.wuithemes.wuitheme.inittheme', 'Unable to open theme configuration file '.$this->mThemeFile, \Innomatic\Logging\Logger::ERROR);
                }
            } else {
                $this->mIconsSetName = $this->mUserSettings['iconsset'];
                $this->mColorsSetName = $this->mUserSettings['colorsset'];
                $this->mStyleName = $this->mUserSettings['stylename'];
            }

            $this->mIconsSetBase = InnomaticContainer::instance('innomaticcontainer')->getExternalBaseUrl().'/shared/icons/'.$this->mIconsSetName.'/';
            $this->mIconsBase = InnomaticContainer::instance('innomaticcontainer')->getExternalBaseUrl().'/shared/icons/';
            $this->mIconsSetDir = InnomaticContainer::instance('innomaticcontainer')->getHome().'shared/icons/'.$this->mIconsSetName.'/';
            $this->mStyleBase = InnomaticContainer::instance('innomaticcontainer')->getExternalBaseUrl().'/shared/styles/';
            $this->mStyleDir = InnomaticContainer::instance('innomaticcontainer')->getHome().'shared/styles/'.$this->mStyleName.'/';

            $wui_colors = new WuiColorsSet($this->mrRootDb, $this->mColorsSetName);
            $wui_icons = new WuiIconsSet($this->mrRootDb, $this->mIconsSetName);
            $wui_style = new WuiStyle($this->mrRootDb, $this->mStyleName);

            if ($innomatic->getState() != InnomaticContainer::STATE_SETUP) {
                $cached_iconsset = new CachedItem($this->mrRootDb, 'innomatic', 'wuiiconsset-'.$this->mIconsSetName);
                $cached_colorsset = new CachedItem($this->mrRootDb, 'innomatic', 'wuicolorsset-'.$this->mColorsSetName);
                $cached_style = new CachedItem($this->mrRootDb, 'innomatic', 'wuistyle-'.$this->mStyleName);

                $this->mIconsSet = unserialize($cached_iconsset->Retrieve());
                $this->mColorsSet = unserialize($cached_colorsset->Retrieve());
                $this->mStyle = unserialize($cached_style->Retrieve());
            }

            if (!$this->mIconsSet or !$this->mColorsSet or !$this->mStyle) {
                if (Wui::DEFAULT_THEME == $this->mTheme) {
                    $this->mColorsSet = $wui_colors->getColorsSet();
                    $this->mIconsSet = $wui_icons->getIconsSet();
                    $this->mStyle = $wui_style->getStyle();
                } else {
                    $def_cfg_file = @parse_ini_file(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/conf/themes/'.Wui::DEFAULT_THEME.'_wuitheme.ini');

                    if ($def_cfg_file !== false) {
                        $def_icons_set_name = $def_cfg_file['THEME.ICONSSET'];
                        $def_colors_set_name = $def_cfg_file['THEME.COLORSSET'];
                        $def_style_name = $def_cfg_file['THEME.STYLE'];
                    } else {
                        
                        $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
                        $log-> LogEvent('innomatic.wuithemes.wuitheme.inittheme', 'Unable to open default theme configuration file '.InnomaticContainer::instance('innomaticcontainer')->getHome().'core/conf/themes/'.Wui::DEFAULT_THEME.'_wuitheme.ini', \Innomatic\Logging\Logger::ERROR);
                    }

                    $wui_def_colors = new WuiColorsSet($this->mrRootDb, $def_colors_set_name);
                    $wui_def_icons = new WuiIconsSet($this->mrRootDb, $def_icons_set_name);
                    $wui_def_style = new WuiStyle($this->mrRootDb, $def_style_name);

                    $this->mColorsSet = $this->DefOpts($wui_def_colors->getColorsSet(), $wui_colors->getColorsSet());
                    $this->mIconsSet = $this->DefOpts($wui_def_icons->getIconsSet(), $wui_icons->getIconsSet());

                    $this->mStyle = $this->DefOpts($wui_def_style->getStyle(), $wui_style->getStyle());
                }

                while (list ($style_name, $style_item) = each($this->mStyle)) {
                    $this->mStyle[$style_name] = $this->mStyleBase.$style_item['base'].'/'.$style_item['value'];
                }
                if ($innomatic->getState() != InnomaticContainer::STATE_SETUP) {
                    $cached_iconsset->Store(serialize($this->mIconsSet));
                    $cached_colorsset->Store(serialize($this->mColorsSet));
                    $cached_style->Store(serialize($this->mStyle));
                }
            }
        }
        return $result;
    }

    public function defOpts($defaultSet, $givenSet)
    {
        $result = array();
        while (list ($key, $val) = each($defaultSet)) {
            if (is_array($val)) {
                $result[$key] = $this->DefOpts($defaultSet[$key], $givenSet[$key]);
            } else {
                if (isset($givenSet[$key])) {
                    $result[$key] = $givenSet[$key];
                    unset($givenSet[$key]);
                } else {
                    $result[$key] = $val;
                }
            }
        }
        if (is_array($givenSet)) {
            $result = array_merge($givenSet, $result);
        }
        return $result;
    }

    public static function setRootTheme()
    {
        require_once('innomatic/wui/Wui.php');
        if (InnomaticContainer::instance('innomaticcontainer')->getState() != InnomaticContainer::STATE_SETUP) {
            require_once('innomatic/application/ApplicationSettings.php');
            $app_cfg = new ApplicationSettings(InnomaticContainer::instance('innomaticcontainer')->getDataAccess(), 'innomatic');

            if (strlen($app_cfg->getKey('wui-root-theme'))) {
                Wui::instance('wui')->setTheme($app_cfg->getKey('wui-root-theme'));
            } else {
                Wui::instance('wui')->setTheme(Wui::DEFAULT_THEME);
            }
        } else {
            Wui::instance('wui')->setTheme(Wui::DEFAULT_THEME);
        }
    }

    public static function setDomainTheme()
    {
        // Wui theme
        //
        require_once('innomatic/domain/user/UserSettings.php');
        $user_settings = new UserSettings(
        InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess(),
        InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getUserId());
        $user_theme = $user_settings->getKey('wui-theme', true);

        require_once('innomatic/wui/Wui.php');

        if (!strlen($user_theme)) {
            require_once('innomatic/application/ApplicationSettings.php');
            $app_cfg = new ApplicationSettings(InnomaticContainer::instance('innomaticcontainer')->getDataAccess(), 'innomatic');
            if (strlen($app_cfg->getKey('wui-root-theme'))) {
                $user_theme = $app_cfg->getKey('wui-root-theme');
                if (!strlen($user_theme)) {
                    $user_theme = Wui::DEFAULT_THEME;
                }
            } else {
                $user_theme = Wui::DEFAULT_THEME;
            }
            unset($app_cfg);
        }
        Wui::instance('wui')->setTheme($user_theme);
    }
}
