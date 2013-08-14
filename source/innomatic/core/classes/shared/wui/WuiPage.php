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
require_once ('innomatic/wui/widgets/WuiContainerWidget.php');
/**
 * @package WUI
 */
class WuiPage extends WuiContainerWidget
{
    /*! @public mTitle string - Page title. */
    //public $mTitle;
    /*! @public mBackground string - Background image url. */
    //public $mBackground;
    /*! @public mJavascript string - Javascript. */
    //public $mJavascript;
    /*! @public mBorder string - Set to 'true' if the page should have a border. Defaults to 'true'. */
    //public $mBorder;
    /*! @public mRefresh integer - Optional page refresh time in seconds. */
    //public $mRefresh = 0;
    public function __construct (
        $elemName,
        $elemArgs = '',
        $elemTheme = '',
        $dispEvents = ''
    )
    {
        $this->WuiContainerWidget($elemName, $elemArgs, $elemTheme, $dispEvents);
        if (isset($this->mArgs['border']) and ($elemArgs['border'] == 'true' or $elemArgs['border'] == 'false'))
            $this->mArgs['border'] = $elemArgs['border'];
        else
            $this->mArgs['border'] = 'true';
        if (isset($this->mArgs['refresh']))
            $this->mArgs['refresh'] = (int) $this->mArgs['refresh'];
        else
            $this->mArgs['refresh'] = 0;
        if (isset($this->mArgs['align'])) {
            switch ($this->mArgs['align']) {
                case 'left':
                case 'center':
                case 'right':
                    break;
                default:
                    $this->mArgs['align'] = 'left';
            }
        } else {
            $this->mArgs['align'] = 'left';
        }
        if (isset($this->mArgs['valign'])) {
            switch ($this->mArgs['valign']) {
                case 'top':
                case 'middle':
                case 'bottom':
                    break;
                default:
                    $this->mArgs['valign'] = 'top';
            }
        } else {
            $this->mArgs['valign'] = 'top';
        }
        if (isset($this->mArgs['ajaxloader']) and ($elemArgs['ajaxloader'] == 'true' or $elemArgs['ajaxloader'] == 'false'))
        	$this->mArgs['ajaxloader'] = $elemArgs['ajaxloader'];
        else
        	$this->mArgs['ajaxloader'] = 'true';
    }
    protected function generateSourceBegin ()
    {
        /*
            if (InnomaticContainer::instance('innomaticcontainer')->isDomainStarted()) {
                $country_string = InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getCountry();
            } else {
                 $country_string = InnomaticContainer::instance('innomaticcontainer')->getCountry();
            }
            require_once('innomatic/locale/LocaleCountry.php');
            $country = new LocaleCountry(strlen($country_string) ? $country_string : 'unitedstates');
            $charset = $country->getCharSet();
            unset($country);
            if (!strlen($charset)) {
                $charset = 'UTF-8';
                //$charset = 'iso-8859-1';
            }
            */
        $charset = 'UTF-8';
        //$block  = "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\" \"http://www.w3.org/TR/REC-html40/loose.dtd\">\n";
        $block = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">' . "\n";
        $block .= "<html>\n";
        $block .= "<head>\n";
        $block .= '<script language="JavaScript" type="text/javascript" src="' . InnomaticContainer::instance('innomaticcontainer')->getBaseUrl(false) . '/shared/wui.js"></script>' . "\n";
        $block .= "<script language=\"JavaScript\" type=\"text/javascript\">\n<!--\nfunction WuiWindowClose()\n{\nif ( window.name == \"detached-" . $_SERVER['PHP_SELF'] . "\" )\n{\nwindow.close();\n}\nelse\n{\nwindow.location = \"main\";\n}\n}\n";
        $block .= "function WuiWindowOpen(location,name,params)\n{\nif ( window.name != \"detached-" . $_SERVER['PHP_SELF'] . "\" )\n{\nvar myWin = window.open(location,name,params);\nwindow.location = \"main\";\n}\n}\n";
        $block .= "-->\n</script>\n";
        $block .= "<script language=\"JavaScript\" type=\"text/javascript\" src=\"" . InnomaticContainer::instance('innomaticcontainer')->getBaseUrl(false) . '/shared/' . "layersmenu.js\"></script>\n";
        $block .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $this->mThemeHandler->mStyle['css'] . "\">\n";
        $block .= '<link rel="shortcut icon" href="' . InnomaticContainer::instance('innomaticcontainer')->getBaseUrl(false) . '/favicon.ico" type="image/x-icon"/>' . "\n";
        $block .= "<style type=\"text/css\">\nimg {\nbehavior:    url(\"" . InnomaticContainer::instance('innomaticcontainer')->getBaseUrl(false) . '/shared/' . "pngbehavior.htc\");\n}\n</style>\n";
        $block .= "<title>" . Wui::utf8_entities($this->mArgs['title']) . "</title>\n";
        $block .= ((isset($this->mArgs['javascript']) and strlen($this->mArgs['javascript'])) ? "<script language=\"JavaScript\">\n<!--\n" . $this->mArgs['javascript'] . "\n//-->\n</script>\n" : '');
        $block .= '<meta http-equiv="Content-Type" content="text/html; charset=' . $charset . '">' . "\n";
        $block .= '<meta name="MSSmartTagsPreventParsing" content="TRUE">' . "\n";
        if ($this->mArgs['refresh'])
            $block .= '<meta http-equiv="refresh" content="' . $this->mArgs['refresh'] . '">' . "\n";
        $block .= "</head>\n";
        $block .= '<body bgcolor="' . $this->mThemeHandler->mColorsSet['pages']['bgcolor'] . '"';
        if (isset($this->mArgs['background']) and strlen($this->mArgs['background'])) {
            $block .= ' style="background-image: url(\'' . $this->mArgs['background'] . '\');';
            if (isset($this->mArgs['horizbackground']) and $this->mArgs['horizbackground'] == 'true') {
                $block .= ' background-repeat: repeat-x;';
            }
            $block .= '"';
        }
        //$block.= ((isset($this->mArgs['background']) and strlen($this->mArgs['background'])) ? ' background="'.$this->mArgs['background'].'"' : '');
        $block .= ">\n";
        $block .= '<table width="100%" height="100%">
<tr>
<td valign="' . $this->mArgs['valign'] . '" align="' . $this->mArgs['align'] . '">' . "\n";
        if ($this->mArgs['border'] == 'true') {
            $block .= '<table border="0" height="0%" cellspacing="0" cellpadding="1"><tr><td bgcolor="' . $this->mThemeHandler->mColorsSet['pages']['border'] . "\">\n";
            $block .= '<table border="0" height="0%" cellspacing="0" cellpadding="0" bgcolor="white">' . "\n";
            $block .= '<tr><td>';
        }
        return $block;
    }
    protected function generateSourceEnd ()
    {
        $block = '';
        if ($this->mArgs['border'] == 'true') {
            $block .= '</td></tr>' . "\n";
            $block .= '<tr><td height="0%" bgcolor="white"></td></tr>' . "\n</table>\n";
            $block .= "</td></tr>\n</table>\n";
        }
        if (isset($GLOBALS['gEnv']['runtime']['wui_menu']['footer'])) {
            $block .= $GLOBALS['gEnv']['runtime']['wui_menu']['footer'];
        }
        $block .= "</td></tr></table>\n";
        // Ajax support.
        require_once ('innomatic/wui/Wui.php');
        if (Wui::instance('wui')->countRegisteredAjaxCalls() > 0) {
            require_once ('innomatic/ajax/Xajax.php');
            $xajax = Xajax::instance('Xajax');
            // Show the ajax loader?
            $xajax->ajaxLoader = $this->mArgs['ajaxloader'] == 'true' ?  true : false;
            
            // Set debug mode
            if (InnomaticContainer::instance('innomaticcontainer') == InnomaticContainer::STATE_DEBUG) {
            	$xajax->debugOn();
            }
            
            $block .= $xajax->getJavascript(InnomaticContainer::instance('innomaticcontainer')->getBaseUrl() . '/shared', 'xajax.js');
            // Setup calls.
            if (Wui::instance('wui')->countRegisteredAjaxSetupCalls() > 0) {
                $setup_calls = Wui::instance('wui')->getRegisteredAjaxSetupCalls();
                $block .= '<script type="text/javascript">' . "\n";
                foreach ($setup_calls as $call) {
                    $block .= $call . ";\n";
                }
                $block .= '</script>' . "\n";
            }
        }
        $block .= "</body>\n</html>\n";
        return $block;
    }
}