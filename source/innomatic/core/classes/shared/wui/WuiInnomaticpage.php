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
namespace Shared\Wui;

/**
 * @package WUI
 */
class WuiInnomaticpage extends \Innomatic\Wui\Widgets\WuiXml
{
    //public $mPageTitle;
    //public $mMenu;
    //public $mToolBars;
    //public $mMainContent;
    //public $mStatus;
    public $mIcon;
    public function __construct (
        $elemName,
        $elemArgs = '',
        $elemTheme = '',
        $dispEvents = ''
    )
    {
        parent::__construct($elemName, $elemArgs, $elemTheme, $dispEvents);
        if (isset($this->mArgs['icon']))
            $this->mIcon = $this->mArgs['icon'];
        $this->_fillDefinition();
    }
    protected function _fillDefinition()
    {
        $result = false;
        $this->mDefinition = '
<page>
  <name>page</name>
  <args><title type="encoded">' . urlencode($this->mArgs['pagetitle']) . '</title>';
        if (isset($this->mArgs['javascript']) and strlen($this->mArgs['javascript'])) {
            $this->mDefinition .= '<javascript type="encoded">' . $this->mArgs['javascript'] . '</javascript>';
        }
        $this->mDefinition .= '</args>
  <children>

    <vertgroup>
      <name>mainvertgroup</name>
      <children>

        <titlebar>
          <name>titlebar</name>
          <args>
            <title type="encoded">' . urlencode($this->mArgs['pagetitle']) . '</title>
            <icon type="encoded">' . urlencode($this->mIcon) . '</icon>
          </args>
        </titlebar>';
        if (strlen($this->mArgs['menu'])) {
            $this->mDefinition .= '<horizgroup><name>menuframe</name><children><menu><name>mainmenu</name><args><menu type="encoded">' . urlencode($this->mArgs['menu']) . '</menu></args></menu></children></horizgroup>';
        }
        if (is_array($this->mArgs['toolbars'])) {
            while (list (, $tbar) = each($this->mArgs['toolbars'])) {
                if (is_object($tbar)) {
                    $this->mDefinition .= '<wuiobject>' . urlencode(serialize($tbar)) . '</wuiobject>';
                }
            }
        }
        $this->mDefinition .= '        <horizframe>
          <name>mainhorizframe</name>
          <children>';
        if (isset($this->mArgs['alerttext']) and strlen($this->mArgs['alerttext'])) {
            $this->mDefinition .= '<alertframe>
                <name>alertframe</name>
                <args>
                    <text type="encoded">' . urlencode($this->mArgs['alerttext']) . '</text>
                </args>
                <children>';
        }
        if (InnomaticContainer::instance('innomaticcontainer')->getState() == InnomaticContainer::STATE_DEBUG)
            InnomaticContainer::instance('innomaticcontainer')->getLoadTimer()->Mark('start - WuiInnomaticPage::serialize');
        if (is_object($this->mArgs['maincontent']))
            $this->mDefinition .= '<wuiobject>' . urlencode(serialize($this->mArgs['maincontent'])) . '</wuiobject>';
        if (InnomaticContainer::instance('innomaticcontainer')->getState() == InnomaticContainer::STATE_DEBUG)
            InnomaticContainer::instance('innomaticcontainer')->getLoadTimer()->Mark('end - WuiInnomaticPage::serialize');
        if (isset($this->mArgs['alerttext']) and strlen($this->mArgs['alerttext'])) {
            $this->mDefinition .= '</children>
                </alertframe>';
        }
        $this->mDefinition .= '          </children>
        </horizframe>';
        $this->mDefinition .= '

        <statusbar>
          <name>pagestatus</name>
          <args><status type="encoded">' . urlencode($this->mArgs['status']) . '</status></args>
        </statusbar>

      </children>
    </vertgroup>

  </children>
</page>';
        return $result;
    }
}
