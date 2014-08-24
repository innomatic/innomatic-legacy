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
namespace Shared\Wui;

/**
 * @package WUI
 */
class WuiDivframe extends \Innomatic\Wui\Widgets\WuiContainerWidget
{
    /*
     * align
     * width
     * bgcolor
     * display
     * position
     * border
     * zindex
     *
     * isdraggable
     * iscollapsable
     *
     * label
     * style
     *
     * top
     * left
     * bottom
     * right
     */
    public function __construct (
        $elemName,
        $elemArgs = '',
        $elemTheme = '',
        $dispEvents = ''
    )
    {
        parent::__construct($elemName, $elemArgs, $elemTheme, $dispEvents);
        if (isset($this->mArgs['align'])) {
            switch ($this->mArgs['align']) {
                case 'left':
                case 'center':
                case 'right':
                    break;
                default:
                    $this->mArgs['align'] = 'middle';
            }
        } else
            $this->mArgs['align'] = 'left';
        if (! isset($this->mArgs['bgcolor']))
            $this->mArgs['bgcolor'] = 'white';

        if (! isset($this->mArgs['zindex']))
            $this->mArgs['zindex'] = 0;

        if (isset($this->mArgs['isdraggable'])) {
	        if ( $this->mArgs['isdraggable'] == 'true' ) {
	        	$this->mArgs['isdraggable'] = true;
	        } else {
	        	$this->mArgs['isdraggable'] = false;
	        }
        } else {
        	$this->mArgs['isdraggable'] = false;
        }
        
        if (isset($this->mArgs['iscollapsable'])) {
        	if ( $this->mArgs['iscollapsable'] == 'true' ) {
        		$this->mArgs['iscollapsable'] = true;
        	} else {
        		$this->mArgs['iscollapsable'] = false;
        	}
        } else {
        	$this->mArgs['iscollapsable'] = false;
        }
    }

    protected function generateSourceBegin()
    {
         //$style = 'style="-moz-border-radius: 5px;';
        //$style .= '-webkit-border-radius:5px; ';

        $style = 'style="';

        if ((isset($this->mArgs['display'])) and ($this->mArgs['display'] == "false")) {
            $style .= 'display: none;';
        } else {
            $style .= 'display: block;';
        }
        
        if (strlen($this->mArgs['bgcolor'])) {
            $style .= ' background-color:'.$this->mArgs['bgcolor'].';';
        }

        if (isset($this->mArgs['position']) and strlen( $this->mArgs['position'])) {
            $style .= ' position:'.$this->mArgs['position'].';';
        }
        
        if (isset($this->mArgs['top']) and strlen($this->mArgs['top'])) {
            $style .= ' top:'.$this->mArgs['top'].';';
        }
        
        if (isset($this->mArgs['left']) and strlen( $this->mArgs['left'])) {
            $style .= ' left:'.$this->mArgs['left'].';';
        }
        
        if (isset($this->mArgs['bottom']) and strlen( $this->mArgs['bottom'])) {
            $style .= ' bottom:'.$this->mArgs['bottom'].';';
        }
        
        if (isset($this->mArgs['right']) and strlen($this->mArgs['right'])) {
            $style .= ' right:'.$this->mArgs['right'].';';
        }

        if (isset($this->mArgs['border']) and strlen( $this->mArgs['border'])) {
            $style .= ' border: '.$this->mArgs['border'].';';
        }
        
        $style .= 'z-index:'.$this->mArgs['zindex'].';';
        if ( $this->mArgs['isdraggable'] ) {
            $style .= 'cursor:move;';
            $style .= 'padding-top:4px; ';
            /*
            if ( $this->mArgs['bgcolor'] == 'white' ) {
                $style .= 'background-image:url(/innomatic/shared/styles/cleantheme/table_header_background.png);';
                $style .= 'background-repeat:repeat-x; background-position:0 -10px;';
            }
            */
        }

        // Generic style
        if (isset($this->mArgs['style']) and strlen( $this->mArgs['style'])) {
            $style .= ' '.$this->mStyle;
        }

        $style .= ( $this->mArgs['iscollapsable'] ? '; overflow:hidden;" ' : '" ' );

        if ($this->mArgs['iscollapsable']) {
            $session_args = $this->retrieveSession();
            if (\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->isDomainStarted()) {
                $language = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getLanguage();
            } else {
                $language = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLanguage();
            }
            $locale = new \Innomatic\Locale\LocaleCatalog('innomatic::wui', $language);
        }

        $myLayout = ( $this->mComments ? '<!-- begin '.$this->mName.' divframe -->' : '' ).
        '<div '.
        ( $this->mArgs['isdraggable'] ? 'class="drag" ' : '' ).
        $style.' id="'.$this->mArgs['id'].'" '.( strlen( $this->mArgs['width'] ) ? 'width="'.$this->mArgs['width'].'" ' : '' ).">\n".
        ( $this->mArgs['iscollapsable'] ? '&nbsp;<img title="'.$locale->getStr('fix_position').'" style="padding-left: 2px; padding-right: 2px; margin-bottom: 2px; float: left; cursor:hand; cursor:pointer;" id="pin_'.$this->mName.'" '.
            ( isset($session_args['top']) ? 'src="'.$this->mThemeHandler->mIconsBase . $this->mThemeHandler->mIconsSet['mini']['lock']['base'] . '/mini/' . $this->mThemeHandler->mIconsSet['mini']['lock']['file'].'" '
                    : 'src="'.$this->mThemeHandler->mIconsBase . $this->mThemeHandler->mIconsSet['mini']['flag']['base'] . '/mini/' . $this->mThemeHandler->mIconsSet['mini']['flag']['file'].'" ').
            'onclick="javascript:xajax_InnomaticStickFrame(new Array(\''.$this->mName.'\',this.parentNode.offsetTop, this.parentNode.offsetLeft));">' : '' ).
        ((isset($this->mArgs['label']) and strlen($this->mArgs['label']))
            ? '<a '.( $this->mArgs['iscollapsable']
                ? 'onclick="javascript:var myTargetDiv = document.getElementById(\''.$this->mArgs['id'].'\'); '.
                'if ( myTargetDiv.style.height == \'140px\' ) { myTargetDiv.style.height=\'44px\';} else { myTargetDiv.style.height=\'140px\';}" '
                : '').
                'style="padding-left:3px; color:#776666">'.
                ( $this->mArgs['iscollapsable'] ? '<img title="'.$locale->getStr('minimize').'" style="margin-bottom: 2px; float: left; cursor: pointer; cursor:hand;" src="'.$this->mThemeHandler->mIconsBase . $this->mThemeHandler->mIconsSet['mini']['folder_red_open']['base'] . '/mini/' . $this->mThemeHandler->mIconsSet['mini']['folder_red_open']['file'].'">' : '' ).
                '<b>'.$this->mArgs['label'].'</b></a>'
            : '' );

        return $myLayout;
    }

    protected function generateSourceEnd()
    {
        return "</div>\n".( $this->mComments ? "<!-- end ".$this->mName." divframe -->\n" : "" );
    }

    protected function generateSourceBlockBegin()
    {
        return '';
    }

    protected function generateSourceBlockEnd()
    {
        return "\n";
    }
}
