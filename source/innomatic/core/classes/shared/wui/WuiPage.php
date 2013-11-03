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
						require_once('innomatic/wui/dispatch/WuiEvent.php');
						require_once('innomatic/wui/dispatch/WuiEventsCall.php');
						require_once('innomatic/domain/user/Permissions.php');
						require_once('innomatic/locale/LocaleCatalog.php');

						if (!(InnomaticContainer::instance('innomaticcontainer')->getState() == InnomaticContainer::STATE_SETUP)) {
						if (!InnomaticContainer::instance('innomaticcontainer')->isDomainStarted()) {
							$root_db = InnomaticContainer::instance('innomaticcontainer')->getDataAccess();

							$groups_query = $root_db->execute('SELECT * FROM root_panels_groups ORDER BY name');
							$num_groups = $groups_query->getNumberRows();

							$tabs = array();
							$tab_pages = array();

							if ($num_groups > 0) {
							    $cont_a = 0;
							    unset($el);
							    while (!$groups_query->eof) {
							        $group_apps = false;
							        $group_data = $groups_query->getFields();

							        if (strlen($group_data['catalog'])) {
							            $tmp_locale = new LocaleCatalog($group_data['catalog'], InnomaticContainer::instance('innomaticcontainer')->getLanguage());
							            $el[$group_data['id']]['groupname'] = $tmp_locale->getStr($group_data['name']);
							        } else {
							            $el[$group_data['id']]['groupname'] = $group_data['name'];
							        }

							        $pagesquery = $root_db->execute('SELECT * FROM root_panels WHERE groupid='.$group_data['id'].' ORDER BY name');
							        if ($pagesquery) {
							            $pagesnum = $pagesquery->getNumberRows();

							            if ($pagesnum > 0) {
							                $group_apps = true;
							                $cont_b = 0;
							                while (!$pagesquery->eof) {
							                    $pagedata = $pagesquery->getFields();

							                    if (strlen($pagedata['catalog']) > 0) {
							                        $tmploc = new LocaleCatalog($pagedata['catalog'], InnomaticContainer::instance('innomaticcontainer')->getLanguage());
							                        $descstr = $tmploc->getStr($pagedata['name']);
							                    }

							                    $tmp_eventscall = new WuiEventsCall($pagedata['name']);
							                    $tmp_eventscall->addEvent(new WuiEvent('view', 'default', ''));

							                    if (strlen($pagedata['themeicontype']))
							                        $imageType = $pagedata['themeicontype'];
							                    else
							                        $imageType = 'apps';

							                    strlen($pagedata['themeicon']) ? $imageUrl = $this->mThemeHandler->mIconsBase.$this->mThemeHandler->mIconsSet[$imageType][$pagedata['themeicon']]['base'].'/'.$imageType.'/'.$this->mThemeHandler->mIconsSet[$imageType][$pagedata['themeicon']]['file'] : $imageUrl = $pagedata['iconfile'];

							                    $el[$group_data['id']]['groupelements'][$cont_b]['name'] = $descstr;
							                    $el[$group_data['id']]['groupelements'][$cont_b]['image'] = $imageUrl;
							                    $el[$group_data['id']]['groupelements'][$cont_b]['action'] = $tmp_eventscall->getEventsCallString();
							                    $el[$group_data['id']]['groupelements'][$cont_b]['themesized'] = 'true';

							                    unset($tmp_eventscall);
							                    $cont_b ++;
							                    $pagesquery->moveNext();
							                }
							            }
							        }

							        // TODO Check if this section is for compatibility only - and remove it
							        if ($group_data['name'] == 'innomatic') {
							            $pagesquery = $root_db->execute('SELECT * FROM root_panels WHERE groupid=0 OR groupid IS NULL ORDER BY name');
							            if ($pagesquery) {
							                $pagesnum = $pagesquery->getNumberRows();

							                if ($pagesnum > 0) {
							                    $group_apps = true;
							                    while (!$pagesquery->eof) {
							                        $pagedata = $pagesquery->getFields();

							                        if (strlen($pagedata['catalog']) > 0) {
							                            $tmploc = new LocaleCatalog($pagedata['catalog'], InnomaticContainer::instance('innomaticcontainer')->getLanguage());
							                            $descstr = $tmploc->getStr($pagedata['name']);
							                        }

							                        $tmp_eventscall = new WuiEventsCall($pagedata['name']);
							                        $tmp_eventscall->addEvent(new WuiEvent('view', 'default', ''));

							                        $el[$group_data['id']]['groupelements'][$cont_b]['name'] = $descstr;
							                        $el[$group_data['id']]['groupelements'][$cont_b]['image'] = $pagedata['iconfile'];
							                        $el[$group_data['id']]['groupelements'][$cont_b]['action'] = $tmp_eventscall->getEventsCallString();
							                        $el[$group_data['id']]['groupelements'][$cont_b]['themesized'] = 'true';

							                        unset($tmp_eventscall);
							                        $cont_b ++;
							                        $pagesquery->moveNext();
							                    }
							                }
							            }
							        }

							        $groups_query->moveNext();

							        if ($group_apps) {
							            $cont_a ++;
							        } else {
							            unset($el[$group_data['id']]);
							        }
							    }


							}
						} else {



						$tmpperm = new Permissions( InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess(), InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getGroup() );

						$tabs = array();
						$tab_pages = array();

						$groupsquery = InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess()->execute( 'select * from domain_panels_groups order by name' );
						$numgroups   = $groupsquery->getNumberRows();

						if ( $numgroups > 0 ) {
						    $prefs_id = 0;
						    $tools_id = 0;

						    $cont = 0;
						    unset( $el );

						    while ( !$groupsquery->eof )
						    {
						        $group_apps = false;
						        $groupdata = $groupsquery->getFields();

						        if ( $tmpperm->check( $groupdata['id'], 'group' ) != Permissions::NODE_NOTENABLED )
						        {
						            if ( $groupdata['name'] == 'tools' ) $tools_id = $groupdata['id'];
						            if ( $groupdata['name'] == 'preferences' ) $prefs_id = $groupdata['id'];

						            if ( strlen( $groupdata['catalog'] ) > 0 )
						            {
						                $tmploc = new LocaleCatalog( $groupdata['catalog'], InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getLanguage() );
						                $descstr = $tmploc->getStr( $groupdata['name'] );
						                $el[$groupdata['id']]['groupname'] = $descstr;
						        } else {
						            $el[$group_data['id']]['groupname'] = $groupdata['name'];
						        }

						            $pagesquery = InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess()->execute( 'select * from domain_panels where groupid = '.$groupdata['id'].' order by name' );
						            $pagesnum = $pagesquery->getNumberRows();

						            if ( $pagesnum > 0 )
						            {
						                $group_apps = true;
						                $contb = 0;

						                while ( !$pagesquery->eof )
						                {
						                    $pagedata = $pagesquery->getFields();

						                    if ( $tmpperm->check( $pagedata['id'], 'page' ) != Permissions::NODE_NOTENABLED )
						                    {
						                        if ( strlen( $pagedata['catalog'] ) > 0 )
						                        {
						                            $tmploc = new LocaleCatalog( $pagedata['catalog'], InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getLanguage() );
						                            $descstr = $tmploc->getStr( $pagedata['name'] );

						                            $tmp_eventscall = new WuiEventsCall($pagedata['name']);
						                            $tmp_eventscall->addEvent( new WuiEvent( 'view', 'default', '' ) );

						                            if ( strlen( $pagedata['themeicontype'] ) ) $imageType = $pagedata['themeicontype'];
						                            else $imageType = 'apps';

						                            strlen( $pagedata['themeicon'] ) ? $imageUrl = $this->mThemeHandler->mIconsBase.$this->mThemeHandler->mIconsSet[$imageType][$pagedata['themeicon']]['base'].'/'.$imageType.'/'.$this->mThemeHandler->mIconsSet[$imageType][$pagedata['themeicon']]['file'] : $imageUrl = $pagedata['iconfile'];

						                            $el[$groupdata['id']]['groupelements'][$contb]['name'] = $descstr;
						                            $el[$groupdata['id']]['groupelements'][$contb]['image'] = $imageUrl;
						                            $el[$groupdata['id']]['groupelements'][$contb]['action'] = $tmp_eventscall->getEventsCallString();
						                            $el[$groupdata['id']]['groupelements'][$contb]['themesized'] = 'true';

						                            unset( $tmp_eventscall );
						                        }
						                    }

						                    $pagesquery->movenext();
						                    $contb++;
						                }
						            }
						        }

						 //$cont++;
						 /**/
						        if ($group_apps) {
						            $cont++;
						        } else {
						            unset($el[$groupdata['id']]);
						        }
						        /**/
						        $groupsquery->movenext();
						    }

						    if ($prefs_id != 0) {
						    	
						    }
						}
					}

$menu = '';

			foreach ($el as $id => $group) {
				if ($id == $prefs_id) {
					continue;
				}
				$menu .= '.|' . $group['groupname'] . "\n";
		
				foreach ($group['groupelements'] as $panel) {					
					$menu .= '..|' . $panel['name'] . '|'
		            . $panel['action'] . "\n";
				}
			}
			
			if (isset($el[$prefs_id])) {
				$menu .= '.|' . $el[$prefs_id]['groupname'] . "\n";
				
				foreach ($el[$prefs_id]['groupelements'] as $panel) {
					$menu .= '..|' . $panel['name'] . '|'
							. $panel['action'] . "\n";
				}
			}

				        require_once ('innomatic/util/Registry.php');
				        $registry = Registry::instance();
				        if (! $registry->isGlobalObject('singleton xlayersmenu')) {
							require_once('innomatic/wui/widgets/layersmenu/XLayersMenu.php');
				            $mid = new XLayersMenu();
				            $registry->setGlobalObject('singleton xlayersmenu', $mid);
				        } else {
				            $mid = $registry->getGlobalObject('singleton xlayersmenu');
				        }
				
				        $mid->libdir = InnomaticContainer::instance(
				            'innomaticcontainer'
				        )->getHome() . 'core/lib/';
				        $mid->libwww = InnomaticContainer::instance(
				            'innomaticcontainer'
				        )->getBaseUrl(false) . '/shared/';
				        $mid->tpldir = InnomaticContainer::instance(
				            'innomaticcontainer'
				        )->getHome() . 'core/conf/layersmenu/';
				        $mid->imgdir = $this->mThemeHandler->mStyleDir;
				        $mid->imgwww = $this->mThemeHandler->mStyleBase
				            . $this->mThemeHandler->mStyleName . '/';
				        $mid->setMenuStructureString($menu);
				        $mid->setDownArrowImg(
				            basename($this->mThemeHandler->mStyle['arrowdownshadow'])
				        );
				        $mid->setForwardArrowImg(
				            basename($this->mThemeHandler->mStyle['arrowrightshadow'])
				        );
				        $mid->parseStructureForMenu($this->mName);
				        $mid->newHorizontalMenu($this->mName);
		}


		// User data
		if (InnomaticContainer::instance('innomaticcontainer')->isDomainStarted()) {
			$user_data = InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getUserData();
			$user_name = $user_data['fname'].' '.$user_data['lname'];

			$domain_name = InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->domaindata['domainname'];
			
			$logout_events_call = new WuiEventsCall(WebAppContainer::instance('webappcontainer')->getProcessor()->getRequest()->getUrlPath().'/domain');
			$innomatic_menu_locale = new LocaleCatalog('innomatic::root_menu', InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getLanguage());
		} else {
			$user_name = 'root';
			$domain_name = 'Innomatic';

			$logout_events_call = new WuiEventsCall(WebAppContainer::instance('webappcontainer')->getProcessor()->getRequest()->getUrlPath().'/root');
			$innomatic_menu_locale = new LocaleCatalog('innomatic::root_menu', InnomaticContainer::instance('innomaticcontainer')->getLanguage());
		}
		$logout_events_call->addEvent(new WuiEvent('login', 'logout', ''));
		
		// HTML
        $charset = 'UTF-8';
        //$block  = "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\" \"http://www.w3.org/TR/REC-html40/loose.dtd\">\n";
        $block = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">' . "\n";
        $block .= "<html>\n";
        $block .= "<head>\n";
        $block .= '<script language="JavaScript" type="text/javascript" src="' . InnomaticContainer::instance('innomaticcontainer')->getBaseUrl(false) . '/shared/wui.js"></script>' . "\n";
        $block .= "<script language=\"JavaScript\" type=\"text/javascript\" src=\"" . InnomaticContainer::instance('innomaticcontainer')->getBaseUrl(false) . '/shared/' . "layersmenu.js\"></script>\n";
        $block .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $this->mThemeHandler->mStyle['css'] . "\">\n";
        
        // JQuery
        $block .= '<link href="' . InnomaticContainer::instance('innomaticcontainer')->getBaseUrl(false) . '/shared/jquery/css/jquery-ui-1.10.3.custom.min.css" rel="stylesheet">' . "\n";
        $block .= '<script language="JavaScript" type="text/javascript" src="' . InnomaticContainer::instance('innomaticcontainer')->getBaseUrl(false) . '/shared/jquery/js/jquery-1.9.1.js"></script>' . "\n";
        $block .= '<script language="JavaScript" type="text/javascript" src="' . InnomaticContainer::instance('innomaticcontainer')->getBaseUrl(false) . '/shared/jquery/js/jquery-ui-1.10.3.custom.min.js"></script>' . "\n";
        $block .= '<link href="' . InnomaticContainer::instance('innomaticcontainer')->getBaseUrl(false) . '/shared/jquery/css/jquery_validation_errors.css" rel="stylesheet">' . "\n";
        $block .= '<script language="JavaScript" type="text/javascript" src="' . InnomaticContainer::instance('innomaticcontainer')->getBaseUrl(false) . '/shared/jquery/js/jquery.validate.js"></script>' . "\n";
		        
        $block .= '<link rel="shortcut icon" href="' . InnomaticContainer::instance('innomaticcontainer')->getBaseUrl(false) . '/favicon.png" type="image/png"/>' . "\n";
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
        $block .= ">\n";
        $block .= '<table width="100%" height="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
<td valign="' . $this->mArgs['valign'] . '" align="' . $this->mArgs['align'] . '" style="height: 100%;">' . "\n";
        if ($this->mArgs['border'] == 'true') {
        	if (!(InnomaticContainer::instance('innomaticcontainer')->getState() == InnomaticContainer::STATE_SETUP)) {
	        	$menu_header = ((isset($GLOBALS['gEnv']['runtime']['wui_menu']['header'])) ? '' : $mid->MakeHeader()) . $mid->getMenu($this->mName);
	        	$menu_footer = $mid->MakeFooter();
        	}
        	
			$block .= "<table class=\"page\" border=\"0\" style=\"border-bottom: 0px solid ".$this->mThemeHandler->mColorsSet['pages']['border'].";\" width=\"100%\" height=\"100%\" cellspacing=\"0\" cellpadding=\"10\">\n"
			. '<thead class="page"><tr class="headerbar">'."\n" 
			. "<td style=\"width: 100%; height: 45px; align: center; padding-left: 16px;\" align=\"left\"><img src=\"".$this->mThemeHandler->mStyle['titlelogo'] ."\" align=\"left\" width=\"25\" height=\"25\" style=\"margin-right: 15px;\" alt=\"Innomatic\"><span nowrap class=\"headerbar\" style=\"white-space: nowrap;\">".$domain_name.'</span></td>'
							. '<td align="right" valign="middle" nowrap style="white-space: nowrap; padding-right: 10px;"><table border="0" style="margin: 0px; padding: 0px;" cellpadding="0" cellspacing="0"><tr><td><span class="headerbar" style="white-space: nowrap;">' . $user_name . "</span></td>"
			. '<td><a href="'.$logout_events_call->getEventsCallString().'" alt="'.$innomatic_menu_locale->getStr('logout').'"><img width="25" height="25" align="right" style="margin-left: 15px;" src="'.$this->mThemeHandler->mStyle['logout'].'" alt="'.$innomatic_menu_locale->getStr('logout').'" /></a></td></tr></table>'
			. "</td></tr>"
			. "<tr><td colspan=\"2\" style=\"border-bottom: 1px solid #cccccc; margin: 0px; padding: 0px; width: 100%; height: 45px; background-color: "
			. $this->mThemeHandler->mColorsSet['titlebars']['bgcolor'] . ";\" align=\"left\" valign=\"middle\" nowrap style=\"white-space: nowrap\">"
			. $menu_header
			. '</td></tr>'
			. "<tr><td colspan=\"2\" style=\"border-bottom: 1px solid #cccccc; margin: 0px; padding: 0px; height: 45px; background-color: white;\" align=\"left\" valign=\"middle\" nowrap style=\"white-space: nowrap\"><table cellspacing=\"0\" cellpadding=\"0\" style=\"margin: 0px; padding: 4px;\"><tr><td>{[wui-titlebar-title]}{[wui-toolbars]}"
			. '</tr></table></td></tr><tr>'
			. '</thead><tbody class="page">'
			. "<td valign=\"top\" colspan=\"2\" style=\"\">\n";

			$GLOBALS['gEnv']['runtime']['wui_menu']['header'] = true;
			$GLOBALS['gEnv']['runtime']['wui_menu']['footer'] = $menu_footer;

        }
        return $block;
    }
    
    protected function generateSourceEnd ()
    {
		// Add titlebar
		$this->mLayout = str_replace('{[wui-titlebar-title]}', $GLOBALS['wui']['titlebar-title'], $this->mLayout);
		
		// Extract toolbars
		$string = '';
		preg_match_all("/<!\[WUITOOLBAR\[(.*?)\]\]>/s", $this->mLayout, $string);
		$toolbars = implode($string[0]);
		$toolbars = str_replace('<![WUITOOLBAR[', '', $toolbars);
		$toolbars = str_replace(']]>', '', $toolbars);

		// Strip toolbars template
		$this->mLayout = preg_replace("/<!\[WUITOOLBAR\[(.*?)\]\]>/s", '', $this->mLayout); 

		// Add toolbars
		$this->mLayout = str_replace('{[wui-toolbars]}', $toolbars, $this->mLayout);
		
        $block = '';
        $block .= "</td></tr>\n</table>\n";

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
            if (InnomaticContainer::instance('innomaticcontainer')->getState() == InnomaticContainer::STATE_DEBUG) {
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