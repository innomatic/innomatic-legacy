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
 * @license    http://www.innomaticplatform.com/license/ New BSD License
 * @link       http://www.innomaticplatform.com
 * @since      Class available since Release 5.0
 */
namespace Shared\Wui;

/**
 * @package WUI
 *
 * This class is deprecated.
 */
class WuiInnomatictoolbar extends \Shared\Wui\WuiXml
{
    //public $mToolBars;
    //public $mFrame;
    public function __construct (
        $elemName,
        $elemArgs = '',
        $elemTheme = '',
        $dispEvents = ''
    )
    {
        parent::__construct($elemName, $elemArgs, $elemTheme, $dispEvents);
        /*
        if ( isset($this->mArgs['frame'] ) and
            (
             $this->mArgs['frame'] == 'true'
             or
             $this->mArgs['frame'] == 'false'
             )
           ) $this->mArgs['frame'] = $this->mArgs['frame'];
        else $this->mArgs['frame'] = 'true';
*/

        if ( isset($this->mArgs['toolbar'] ) and
                (
                        $this->mArgs['toolbar'] == 'true'
                        or
                        $this->mArgs['toolbar'] == 'false'
                )
        ) $this->mArgs['toolbar'] = $this->mArgs['toolbar'];
        else $this->mArgs['toolbar'] = 'false';


        $this->mArgs['frame'] = 'false';
        $this->_fillDefinition();
    }
    protected function _fillDefinition()
    {
        $result = false;
        $this->mDefinition = '';
        if ($this->mArgs['frame'] == 'true')
            $this->mDefinition .= '<horizframe><name>toolbarframe</name><children>';
        else
            $this->mDefinition .= '<horizgroup><children>';
        if (is_array($this->mArgs['toolbars'])) {
            while (list ($tbar_name, $tbar) = each($this->mArgs['toolbars'])) {
                if (is_array($tbar)) {
                    $this->mDefinition .= '<'.($this->mArgs['toolbar'] == 'true' ? 'toolbar' : 'horizgroup').'>
  <name>' . $tbar_name . '</name>
  <children>';
                    while (list ($button_name, $button) = each($tbar)) {
                        $this->mDefinition .= '<button>
  <name>' . $button_name . '</name>
                            <args><label type="encoded">' . urlencode($button['label']) . '</label><themeimage>' . (isset($button['themeimage']) ? $button['themeimage'] : '') . '</themeimage><themeimagetype>' . (isset($button['themeimagetype']) ? $button['themeimagetype'] : '') . '</themeimagetype><image>' . (isset($button['image']) ? \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getBaseUrl(false) . '/shared/' . $button['image'] : '') . '</image><action type="encoded">' . urlencode(isset($button['action']) ? $button['action'] : '') . '</action>';
                        if (isset($button['needconfirm']) and isset($button['confirmmessage']) and ($button['needconfirm'] == 'true') and strlen($button['confirmmessage']))
                            $this->mDefinition .= '<needconfirm>true</needconfirm><confirmmessage type="encoded">' . urlencode($button['confirmmessage']) . '</confirmmessage>';
                        if (isset($button['horiz']))
                            $this->mDefinition .= '<horiz>' . $button['horiz'] . '</horiz>';
                        if (isset($button['disabled']))
                            $this->mDefinition .= '<disabled>' . $button['disabled'] . '</disabled>';
                        if (isset($button['target']))
                            $this->mDefinition .= '<target>' . $button['target'] . '</target>';
                        if (isset($button['compact']))
                            $this->mDefinition .= '<compact>' . $button['compact'] . '</compact>';
                        if (isset($button['hint']))
                            $this->mDefinition .= '<hint type="encoded">' . urlencode($button['hint']) . '</hint>';
                        if (isset($button['formsubmit']))
                            $this->mDefinition .= '<formsubmit>' . $button['formsubmit'] . '</formsubmit>';
                        if (isset($button['nowrap']))
                            $this->mDefinition .= '<nowrap>' . $button['nowrap'] . '</nowrap>';
                        $this->mDefinition .= '</args>
</button>';
                    }
                    $this->mDefinition .= '</children>
</'.($this->mArgs['toolbar'] == 'true' ? 'toolbar' : 'horizgroup').'>';
                }
            }
        }
        if ($this->mArgs['frame'] == 'true')
            $this->mDefinition .= '</children></horizframe>';
        else
            $this->mDefinition .= '</children></horizgroup>';
        return $result;
    }
}
