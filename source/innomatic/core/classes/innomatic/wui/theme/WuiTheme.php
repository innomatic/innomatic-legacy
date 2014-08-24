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
namespace Innomatic\Wui\Theme;

use Innomatic\Wui;

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
            $innomatic = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');

            if ($this->mTheme == 'default')
            $this->mTheme = \Innomatic\Wui\Wui::DEFAULT_THEME;
            if ($this->mTheme != 'userdefined') {
                if (file_exists($innomatic->getHome().'core/conf/themes/'.$this->mTheme.'_wuitheme.ini')) {
                    $this->mThemeFile = $innomatic->getHome().'core/conf/themes/'.$this->mTheme.'_wuitheme.ini';
                } else {
                    $this->mTheme = \Innomatic\Wui\Wui::DEFAULT_THEME;
                    $this->mThemeFile = $innomatic->getHome().'core/conf/themes/'.\Innomatic\Wui\Wui::DEFAULT_THEME.'_wuitheme.ini';
                }
                $cfg_file = @parse_ini_file($this->mThemeFile);

                if ($cfg_file !== false) {
                    $this->mIconsSetName = $cfg_file['THEME.ICONSSET'];
                    $this->mColorsSetName = $cfg_file['THEME.COLORSSET'];
                    $this->mStyleName = $cfg_file['THEME.STYLE'];
                } else {
                    
                    $log = $innomatic->getLogger();
                    $log-> LogEvent('innomatic.wuithemes.wuitheme.inittheme', 'Unable to open theme configuration file '.$this->mThemeFile, \Innomatic\Logging\Logger::ERROR);
                }
            } else {
                $this->mIconsSetName = $this->mUserSettings['iconsset'];
                $this->mColorsSetName = $this->mUserSettings['colorsset'];
                $this->mStyleName = $this->mUserSettings['stylename'];
            }

            $this->mIconsSetBase = $innomatic->getExternalBaseUrl().'/shared/icons/'.$this->mIconsSetName.'/';
            $this->mIconsBase = $innomatic->getExternalBaseUrl().'/shared/icons/';
            $this->mIconsSetDir = $innomatic->getHome().'shared/icons/'.$this->mIconsSetName.'/';
            $this->mStyleBase = $innomatic->getExternalBaseUrl().'/shared/styles/';
            $this->mStyleDir = $innomatic->getHome().'shared/styles/'.$this->mStyleName.'/';

            $wui_colors = new WuiColorsSet($this->mrRootDb, $this->mColorsSetName);
            $wui_icons = new WuiIconsSet($this->mrRootDb, $this->mIconsSetName);
            $wui_style = new WuiStyle($this->mrRootDb, $this->mStyleName);

            if ($innomatic->getState() != \Innomatic\Core\InnomaticContainer::STATE_SETUP) {
                $cached_iconsset = new \Innomatic\Datatransfer\Cache\CachedItem($this->mrRootDb, 'innomatic', 'wuiiconsset-'.$this->mIconsSetName);
                $cached_colorsset = new \Innomatic\Datatransfer\Cache\CachedItem($this->mrRootDb, 'innomatic', 'wuicolorsset-'.$this->mColorsSetName);
                $cached_style = new \Innomatic\Datatransfer\Cache\CachedItem($this->mrRootDb, 'innomatic', 'wuistyle-'.$this->mStyleName);

                $this->mIconsSet = unserialize($cached_iconsset->Retrieve());
                $this->mColorsSet = unserialize($cached_colorsset->Retrieve());
                $this->mStyle = unserialize($cached_style->Retrieve());
            }

            if (!$this->mIconsSet or !$this->mColorsSet or !$this->mStyle) {
                if (\Innomatic\Wui\Wui::DEFAULT_THEME == $this->mTheme) {
                    $this->mColorsSet = $wui_colors->getColorsSet();
                    $this->mIconsSet = $wui_icons->getIconsSet();
                    $this->mStyle = $wui_style->getStyle();
                } else {
                    $def_cfg_file = @parse_ini_file($innomatic->getHome().'core/conf/themes/'.\Innomatic\Wui\Wui::DEFAULT_THEME.'_wuitheme.ini');

                    if ($def_cfg_file !== false) {
                        $def_icons_set_name = $def_cfg_file['THEME.ICONSSET'];
                        $def_colors_set_name = $def_cfg_file['THEME.COLORSSET'];
                        $def_style_name = $def_cfg_file['THEME.STYLE'];
                    } else {
                        
                        $log = $innomatic->getLogger();
                        $log-> LogEvent('innomatic.wuithemes.wuitheme.inittheme', 'Unable to open default theme configuration file '.$innomatic->getHome().'core/conf/themes/'.\Innomatic\Wui\Wui::DEFAULT_THEME.'_wuitheme.ini', \Innomatic\Logging\Logger::ERROR);
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
                if ($innomatic->getState() != \Innomatic\Core\InnomaticContainer::STATE_SETUP) {
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
        if (\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getState() != \Innomatic\Core\InnomaticContainer::STATE_SETUP) {
            $app_cfg = new \Innomatic\Application\ApplicationSettings('innomatic');

            if (strlen($app_cfg->getKey('wui-root-theme'))) {
                \Innomatic\Wui\Wui::instance('\Innomatic\Wui\Wui')->setTheme($app_cfg->getKey('wui-root-theme'));
            } else {
                \Innomatic\Wui\Wui::instance('\Innomatic\Wui\Wui')->setTheme(\Innomatic\Wui\Wui::DEFAULT_THEME);
            }
        } else {
            \Innomatic\Wui\Wui::instance('\Innomatic\Wui\Wui')->setTheme(\Innomatic\Wui\Wui::DEFAULT_THEME);
        }
    }

    public static function setDomainTheme()
    {
        $container = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');

        // Wui theme
        //
        $user_settings = new \Innomatic\Domain\User\UserSettings(
        $container->getCurrentDomain()->getDataAccess(),
        $container->getCurrentUser()->getUserId());
        $user_theme = $user_settings->getKey('wui-theme', true);

        if (!strlen($user_theme)) {
            $app_cfg = new \Innomatic\Application\ApplicationSettings('innomatic');
            if (strlen($app_cfg->getKey('wui-root-theme'))) {
                $user_theme = $app_cfg->getKey('wui-root-theme');
                if (!strlen($user_theme)) {
                    $user_theme = \Innomatic\Wui\Wui::DEFAULT_THEME;
                }
            } else {
                $user_theme = \Innomatic\Wui\Wui::DEFAULT_THEME;
            }
            unset($app_cfg);
        }
        \Innomatic\Wui\Wui::instance('\Innomatic\Wui\Wui')->setTheme($user_theme);
    }
}
