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
class WuiButton extends \Innomatic\Wui\Widgets\WuiWidget
{
    //public $mAction;
    //public $mLabel;
    //public $mImage;
    //public $mThemeImage;
    //public $mThemeImageType;
    //public $mHint;
    //public $mTarget;
    //public $mHoriz;
    //public $mNoWrap;
    //public $mDisabled;
    //public $mFormSubmit;
    //public $mNeedConfirm;
    //public $mConfirmMessage;
    //public $mFormCheckMessage;
    //public $mHighlight;
    //public $mCompact;
    public function __construct (
        $elemName,
        $elemArgs = '',
        $elemTheme = '',
        $dispEvents = ''
    )
    {
        parent::__construct($elemName, $elemArgs, $elemTheme, $dispEvents);

        $iconsBase = 'icons';

        if (isset($this->mArgs['mainaction'])) {
            $this->mArgs['mainaction'] = $this->mArgs['mainaction'] == 'true' ? 'true' : 'false';
        } else {
            $this->mArgs['mainaction'] = 'false';
        }

        if (isset($this->mArgs['dangeraction'])) {
            $this->mArgs['dangeraction'] = $this->mArgs['dangeraction'] == 'true' ? 'true' : 'false';
        } else {
            $this->mArgs['dangeraction'] = 'false';
        }

        if ($this->mArgs['mainaction'] == 'true' or $this->mArgs['dangeraction'] == 'true') {
            $iconsBase = 'light';
        }

        if (isset($this->mArgs['compact']))
            $this->mArgs['compact'] = $this->mArgs['compact'] == 'true' ? 'true' : 'false';
        else
            $this->mArgs['compact'] = 'false';

        if (! isset($this->mArgs['themeimagetype']) or ! strlen($this->mArgs['themeimagetype']))
            $this->mArgs['themeimagetype'] = 'actions';

        if (isset($this->mArgs['themeimage']) and strlen($this->mArgs['themeimage'])) {
            if (
            	isset($this->mThemeHandler->mIconsSet[$iconsBase][$this->mArgs['themeimage']]['base']) and
            	file_exists(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome().'shared/icons/'.$this->mThemeHandler->mIconsSet[$iconsBase][$this->mArgs['themeimage']]['base'] . '/'.$iconsBase.'/' . $this->mThemeHandler->mIconsSet[$iconsBase][$this->mArgs['themeimage']]['file'])
			) {
                $this->mArgs['image'] = $this->mThemeHandler->mIconsBase . $this->mThemeHandler->mIconsSet[$iconsBase][$this->mArgs['themeimage']]['base'] . '/'.$iconsBase.'/' . $this->mThemeHandler->mIconsSet[$iconsBase][$this->mArgs['themeimage']]['file'];
            } else {
                // Fallback to old icon set style
                 $this->mArgs['image'] = $this->mThemeHandler->mIconsBase . $this->mThemeHandler->mIconsSet[$this->mArgs['themeimagetype']][$this->mArgs['themeimage']]['base'] . '/' . $this->mArgs['themeimagetype'] . '/' . $this->mThemeHandler->mIconsSet[$this->mArgs['themeimagetype']][$this->mArgs['themeimage']]['file'];
            }
        }
        if (isset($this->mArgs['confirmmessage']))
            $this->mArgs['confirmmessage'] = addslashes($this->mArgs['confirmmessage']);
        if (isset($this->mArgs['disabled']) and ($this->mArgs['disabled'] == 'true' or $this->mArgs['disabled'] == 'false'))
            $this->mArgs['disabled'] = $this->mArgs['disabled'];
        else
            $this->mArgs['disabled'] = 'false';
        if (isset($this->mArgs['nowrap']) and ($this->mArgs['nowrap'] == 'true' or $this->mArgs['nowrap'] == 'false'))
            $this->mArgs['nowrap'] = $this->mArgs['nowrap'];
        else
            $this->mArgs['nowrap'] = 'true';
        if (! isset($this->mArgs['action']) or ! strlen($this->mArgs['action']))
            $this->mArgs['disabled'] = 'true';
        if (isset($this->mArgs['target'])) {
            if ($this->mArgs['target'] == 'main' or $this->mArgs['target'] == 'header' or $this->mArgs['target'] == 'menu')
                $this->mArgs['target'] = 'parent.' . $this->mArgs['target'];
            if ($this->mArgs['target'] == '_top')
                $this->mArgs['target'] = 'top';
        }
        if (! isset($this->mArgs['highlight']))
            $this->mArgs['highlight'] = 'true';
        if (isset($this->mArgs['width']) and ! is_numeric($this->mArgs['width'])) {
            unset($this->mArgs['width']);
        }
        if (isset($this->mArgs['height']) and ! is_numeric($this->mArgs['height'])) {
            unset($this->mArgs['height']);
        }
    }
    protected function generateSource()
    {
        $buttonTextColorClass = 'buttontext';
        $mouseOutColor        = $this->mThemeHandler->mColorsSet['pages']['bgcolor'];
        $mouseOverColor       = $this->mThemeHandler->mColorsSet['buttons']['notselected'];

        if ($this->mArgs['mainaction'] == 'true') {
            $buttonTextColorClass = 'buttontextmainaction';
            $mouseOutColor = '#24c128';
            $mouseOverColor = '#00ff06';
        } elseif ($this->mArgs['dangeraction'] == 'true') {
            $buttonTextColorClass = 'buttontextdangeraction';
            $mouseOutColor = '#e93f33';
            $mouseOverColor = '#ff1100';
        }

$this->mArgs['mainaction'] = 'false';
        if ($this->mArgs['themeimagetype'] == 'actions') {
            $image_width = 20;
            $image_height = 20;
        } else
            if ($this->mArgs['themeimagetype'] == 'mini') {
                $image_width = 15;
                $image_height = 15;
            } else
                if ($this->mArgs['themeimagetype'] == 'big') {
                    $image_width = 60;
                    $image_height = 60;
                } else
                    if (isset($this->mArgs['themeimage']) and strlen($this->mArgs['themeimage'])) {
                        $image_width = 30;
                        $image_height = 30;
                    }
        $sizes = '';
        if (isset($this->mArgs['themeimage']) and strlen($this->mArgs['themeimage'])) {
            $sizes = ' style="width: ' . $image_width . 'px; height: ' . $image_height . 'px;"';
        } else
            if (isset($this->mArgs['width']) or isset($this->mArgs['height'])) {
                $sizes = ' style="';
                isset($this->mArgs['width']) ? $sizes .= 'width: ' . $this->mArgs['width'] . 'px;' : '';
                isset($this->mArgs['height']) ? $sizes .= 'height: ' . $this->mArgs['height'] . 'px;' : '';
                $sizes .= '"';
            }
        $this->mLayout = ($this->mComments ? '<!-- begin ' . $this->mName . ' button -->' : '') . '<table'.(isset($this->mArgs['id']) ? ' id="'.$this->mArgs['id'].'"' : '')
             . $this->getEventsCompleteString() . ($this->mArgs['compact'] == 'true' ? ' cellpadding="1" cellspacing="0"' : ' cellpadding="4"')
            . ' style="background-color: '.$mouseOutColor.';' . ($this->mArgs['disabled'] == 'true' ? 'cursor: default;' : 'cursor: pointer;') . '"'
            . ($this->mArgs['disabled'] == 'true' ? '' : ($this->mArgs['highlight'] == 'true' ? ' onMouseOver="this.style.backgroundColor=\'' . $mouseOverColor . '\';'
            . ((isset($this->mArgs['label']) and strlen($this->mArgs['label'])) ? 'wuiHint(\'' . str_replace("'", "\'", $this->mArgs['label']) . '\');' : '')
            . '" onMouseOut="this.style.backgroundColor=\'' . $mouseOutColor . '\';wuiUnHint();"' : '')
            . ' onClick="' . ((isset($this->mArgs['needconfirm']) and $this->mArgs['needconfirm'] == 'true') ? 'javascript:if ( confirm(\'' . $this->mArgs['confirmmessage'] . '\') ) {' : '')
            . ($this->mArgs['highlight'] == 'true' ? 'this.style.backgroundColor=\'' . $this->mThemeHandler->mColorsSet['buttons']['selected'] . '\';' : '')
            . ((isset($this->mArgs['formsubmit']) and strlen($this->mArgs['formsubmit'])) ? 'void(submitForm(\'' . $this->mArgs['formsubmit'] . '\',\'' . $this->mArgs['action'] . '\',\''
            . (isset($this->mArgs['formcheckmessage']) ? $this->mArgs['formcheckmessage'] : '') . '\',\''
            . ((isset($this->mArgs['target']) and strlen($this->mArgs['target'])) ? $this->mArgs['target'] : '') . '\'));' : (((isset($this->mArgs['target']) and strlen($this->mArgs['target']) and ($this->mArgs['target'] != '_blank')) ? $this->mArgs['target'] . '.' : '')
            . ((isset($this->mArgs['target']) and $this->mArgs['target'] == '_blank') ? 'window.open(\'' : 'location.href=\'') . $this->mArgs['action'] . ((isset($this->mArgs['target']) and $this->mArgs['target'] == '_blank') ? '\')' : '\''))) . ((isset($this->mArgs['needconfirm']) and $this->mArgs['needconfirm'] == 'true') ? '}' : '') . '"')
            . '><tr valign="middle"><td class="normalbig" align="center" ' . ($this->mArgs['nowrap'] == 'true' ? 'nowrap style="white-space: nowrap" ' : '') . ' valign="middle"><center>' . ((isset($this->mArgs['image']) and strlen($this->mArgs['image'])) ? '<img src="' . $this->mArgs['image']
            . '" align="middle" border="0"' . $sizes . ((isset($this->mArgs['hint']) and strlen($this->mArgs['hint'])) ? ' alt="' . $this->mArgs['hint'] . '"' : 'alt=""') . '>' . ((isset($this->mArgs['horiz']) and $this->mArgs['horiz']) == 'true' ? '</center></td><td class="normalbig" align="center" ' . ($this->mArgs['nowrap'] == 'true' ? 'nowrap style="white-space: nowrap"' : '')
            . ' valign="middle"><center>' : '<br>') : '') . ($this->mArgs['disabled'] == 'true' ? '<font color="' . $this->mThemeHandler->mColorsSet['buttons']['disabledtext'] . '">'
            . ($this->mArgs['mainaction'] == 'true' ? '<strong>' : '').((isset($this->mArgs['label']) and strlen($this->mArgs['label'])) ? \Innomatic\Wui\Wui::utf8_entities($this->mArgs['label']) : "")
            . ($this->mArgs['mainaction'] == 'true' ? '</strong>' : '')
            . '</font>' : '<font color="' . $this->mThemeHandler->mColorsSet['buttons']['text'] . '"><span class="'.$buttonTextColorClass.'">'
            . ($this->mArgs['mainaction'] == 'true' ? '<strong>' : '').((isset($this->mArgs['label']) and strlen($this->mArgs['label'])) ? \Innomatic\Wui\Wui::utf8_entities($this->mArgs['label']) : ""). ($this->mArgs['mainaction'] == 'true' ? '</strong>' : '')) . '</span></font></center></td></tr></table>' . ($this->mComments ? '<!-- end ' . $this->mName . " button -->\n" : '');
        return true;
    }
}
