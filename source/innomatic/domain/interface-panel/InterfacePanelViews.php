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

use \Innomatic\Core\InnomaticContainer;
use \Innomatic\Wui\Widgets;
use \Innomatic\Wui\Dispatch;
use \Innomatic\Locale\LocaleCatalog;
use \Innomatic\Domain\User;
use \Shared\Wui;

class InterfacePanelViews extends \Innomatic\Desktop\Panel\PanelViews
{
    public $wuiPage;
    public $wuiMainvertgroup;
    public $wuiMainframe;
    public $wuiMainstatus;
    public $wuiTitlebar;
    protected $_localeCatalog;

    public function update($observable, $arg = '')
    {
        switch ($arg) {
            case 'status':
                $this->wuiMainstatus->mArgs['status'] =
                    $this->_controller->getAction()->status;
                break;
        }
    }

    public function beginHelper()
    {
        $this->_localeCatalog = new LocaleCatalog(
            'innomatic::domain_interface',
            InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getLanguage()
        );

        $this->_wuiContainer->loadWidget('button');
        $this->_wuiContainer->loadWidget('checkbox');
        $this->_wuiContainer->loadWidget('combobox');
        $this->_wuiContainer->loadWidget('date');
        $this->_wuiContainer->loadWidget('empty');
        $this->_wuiContainer->loadWidget('file');
        $this->_wuiContainer->loadWidget('formarg');
        $this->_wuiContainer->loadWidget('form');
        $this->_wuiContainer->loadWidget('grid');
        $this->_wuiContainer->loadWidget('helpnode');
        $this->_wuiContainer->loadWidget('horizbar');
        $this->_wuiContainer->loadWidget('horizframe');
        $this->_wuiContainer->loadWidget('horizgroup');
        $this->_wuiContainer->loadWidget('image');
        $this->_wuiContainer->loadWidget('label');
        $this->_wuiContainer->loadWidget('link');
        $this->_wuiContainer->loadWidget('listbox');
        $this->_wuiContainer->loadWidget('menu');
        $this->_wuiContainer->loadWidget('page');
        $this->_wuiContainer->loadWidget('progressbar');
        $this->_wuiContainer->loadWidget('radio');
        $this->_wuiContainer->loadWidget('sessionkey');
        $this->_wuiContainer->loadWidget('statusbar');
        $this->_wuiContainer->loadWidget('string');
        $this->_wuiContainer->loadWidget('submit');
        $this->_wuiContainer->loadWidget('tab');
        $this->_wuiContainer->loadWidget('table');
        $this->_wuiContainer->loadWidget('text');
        $this->_wuiContainer->loadWidget('titlebar');
        $this->_wuiContainer->loadWidget('toolbar');
        $this->_wuiContainer->loadWidget('treemenu');
        $this->_wuiContainer->loadWidget('vertframe');
        $this->_wuiContainer->loadWidget('vertgroup');
        $this->_wuiContainer->loadWidget('xml');

$this->wuiPage = new WuiPage('page', array('title' => $this->_localeCatalog->getStr('interface_pagetitle')));
$this->wuiMainvertgroup = new WuiVertgroup('mainvertgroup');
$this->wuiTitlebar = new WuiTitleBar(
                               'titlebar',
                               array('title' => $this->_localeCatalog->getStr('interface_title'), 'icon' => 'picture')
                              );
$this->wuiMainvertgroup->addChild($this->wuiTitlebar);

// Main tool bar
//
$wuiMainToolbar = new WuiToolBar('maintoolbar');

$defaultAction = new WuiEventsCall();
$defaultAction->addEvent(new WuiEvent('view', 'default', ''));
$wuiDefaultButton = new WuiButton(
                                   'defaultbutton',
                                   array(
                                         'label' => $this->_localeCatalog->getStr('default_button'),
                                         'themeimage' => 'mask',
                                         'horiz' => 'true',
                                         'action' => $defaultAction->getEventsCallString()
                                        )
                                  );
$wuiMainToolbar->addChild($wuiDefaultButton);

$countryAction = new WuiEventsCall();
$countryAction->addEvent(new WuiEvent('view', 'localization', ''));
$wuiCountryButton = new WuiButton(
                                   'countrybutton',
                                   array(
                                         'label' => $this->_localeCatalog->getStr('localization_button'),
                                         'themeimage' => 'globe2',
                                         'horiz' => 'true',
                                         'action' => $countryAction->getEventsCallString()
                                         )
                                   );
$wuiMainToolbar->addChild($wuiCountryButton);

// Help tool bar
//
$wuiHelpToolBar = new WuiToolBar('helpbar');

$viewDispatcher = new WuiDispatcher('view');
$eventName = $viewDispatcher->getEventName();

if (strcmp($eventName, 'help')) {
    $helpAction = new WuiEventsCall();
    $helpAction->addEvent(new WuiEvent('view', 'help', array('node' => $eventName)));
    $wuiHelpButton = new WuiButton(
                                   'helpbutton',
                                   array(
                                         'label' => $this->_localeCatalog->getStr('help_button'),
                                         'themeimage' => 'info',
                                         'horiz' => 'true',
                                         'action' => $helpAction->getEventsCallString()
                                         )
                                   );

    $wuiHelpToolBar->addChild($wuiHelpButton);
}

// Toolbar frame
//
$wuiToolBarFrame = new WuiHorizgroup('toolbarframe');

$wuiToolBarFrame->addChild($wuiMainToolbar);
$wuiToolBarFrame->addChild($wuiHelpToolBar);
$this->wuiMainvertgroup->addChild($wuiToolBarFrame);

$this->wuiMainframe = new WuiVertframe('mainframe');
$this->wuiMainstatus = new WuiStatusBar('mainstatusbar');
    }

    public function endHelper()
    {
        // Page render
        //
        $this->wuiMainvertgroup->addChild($this->wuiMainframe);
        $this->wuiMainvertgroup->addChild($this->wuiMainstatus);
        $this->wuiPage->addChild($this->wuiMainvertgroup);
        $this->_wuiContainer->addChild($this->wuiPage);
    }

    public function viewdefault($eventData)
    {
        //$app_cfg = new ApplicationSettings(
        //    InnomaticContainer::instance('innomaticcontainer')->getDataAccess(), 'innomatic' );

        $themesQuery = InnomaticContainer::instance(
                'innomaticcontainer'
        )->getDataAccess()->execute('SELECT name,catalog FROM wui_themes');

        while (!$themesQuery->eof) {
            $tmpLocale = new LocaleCatalog(
                    $themesQuery->getFields('catalog'),
                    InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getLanguage()
            );
            $elements[$themesQuery->getFields('name')] = $tmpLocale->getStr($themesQuery->getFields('name'));

            $themesQuery->moveNext();
        }

        asort($elements);

        $xmlDef = '<vertgroup><name>vgroup</name><args><halign>center</halign></args><children>
    <form><name>theme</name><args><action type="encoded">'
                .urlencode(
                        WuiEventsCall::buildEventsCallString(
                                '',
                                array(
                                        array('view', 'default', ''),
                                        array('action', 'settheme', '')
                                )
                        )
                )
                .'</action></args><children>
        <grid><name>themegrid</name><children>
          <label row="0" col="0"><name>themelabel</name><args><label type="encoded">'
                        .urlencode($this->_localeCatalog->getStr('themes_label'))
                        .'</label><bold>true</bold></args></label>
          <listbox row="1" col="0"><name>theme</name><args><elements type="array">'
                                .WuiXml::encode($elements)
                                .'</elements><default>'
                                        . (\Innomatic\Wui\Wui::instance('wui')->getThemeName()).
                                        '</default><disp>action</disp><size>10</size></args></listbox>
        </children></grid>
        <submit><name>submit</name><args><caption type="encoded">'
                                                .urlencode($this->_localeCatalog->getStr('settheme_submit'))
                                                .'</caption></args></submit>
      </children></form>
    </children></vertgroup>';

        $this->wuiMainframe->addChild(new WuiXml('page', array('definition' => $xmlDef)));

        $this->wuiTitlebar->mArgs['title'].= ' - '.$this->_localeCatalog->getStr('themes_title');
    }

    public function viewlocalization($eventData)
    {
        $actionDispatcher = new WuiDispatcher('action');

        $eventData = $actionDispatcher->getEventData();

        $countryLocale = new LocaleCatalog(
                'innomatic::localization',
                InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getLanguage()
        );

        $selectedCountry = '';
        if (isset($eventData['country'])) {
            $selectedCountry = $eventData['country'];
        }

        $wuiVGroup = new WuiVertgroup('vgroup');

        $countryQuery = InnomaticContainer::instance(
                'innomaticcontainer'
        )->getDataAccess()->execute('SELECT * FROM locale_countries');

        while (!$countryQuery->eof) {
            $countries[$countryQuery->getFields('countryname')] = $countryLocale->getStr(
                    $countryQuery->getFields('countryname')
            );
            $countryQuery->moveNext();
        }

        $wuiLocaleGrid = new WuiGrid('localegrid', array('rows' => '1', 'cols' => '3'));

        $wuiLocaleGrid->addChild(
                new WuiLabel(
                        'countrylabel',
                        array('label' => $this->_localeCatalog->getStr('country_label'))
                ),
                0, 0
        );
        $wuiLocaleGrid->addChild(
                new WuiComboBox(
                        'country',
                        array(
                                'disp' => 'action',
                                'elements' => $countries,
                                'default' => (
                                        $selectedCountry ? $selectedCountry : InnomaticContainer::instance(
                                                'innomaticcontainer'
                                        )->getCurrentUser()->getCountry()
                                )
                        )
                ),
                0, 1
        );
        $wuiLocaleGrid->addChild(
                new WuiSubmit(
                        'submit1',
                        array('caption' => $this->_localeCatalog->getStr('country_submit'))
                ),
                0, 2
        );

        $wuiVGroup->addChild($wuiLocaleGrid);

        $formEventsCall = new WuiEventsCall();
        $formEventsCall->addEvent(new WuiEvent('action', 'setcountry', ''));
        $formEventsCall->addEvent(new WuiEvent('view', 'localization', ''));

        $wuiForm = new WuiForm('countryform', array('action' => $formEventsCall->getEventsCallString()));
        $wuiForm->addChild($wuiVGroup);

        $this->wuiMainframe->addChild($wuiForm);

        $locCountry = new LocaleCountry(
                InnomaticContainer::instance(
                        'innomaticcontainer'
                )->getCurrentUser()->getCountry()
        );
        $countryLanguage = $locCountry->Language();

        $languageLocale = new LocaleCatalog(
                'innomatic::localization',
                InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getLanguage()
        );

        $selectedLanguage = '';
        if (isset($eventData['language'])) {
            $selectedLanguage = $eventData['language'];
        }

        $wuiVGroup = new WuiVertgroup('vgroup');

        $languageQuery = InnomaticContainer::instance(
                'innomaticcontainer'
        )->getDataAccess()->execute('SELECT * FROM locale_languages');

        while (!$languageQuery->eof) {
            $languages[$languageQuery->getFields('langshort')] = $languageLocale->getStr(
                    $languageQuery->getFields('langname')
            );
            $languageQuery->moveNext();
        }

        $wuiLocaleGrid = new WuiGrid('localegrid', array('rows' => '1', 'cols' => '3'));

        $wuiLocaleGrid->addChild(
                new WuiLabel(
                        'languagelabel',
                        array('label' => $this->_localeCatalog->getStr('language_label'))
                ),
                0, 0
        );
        $wuiLocaleGrid->addChild(
                new WuiComboBox(
                        'language',
                        array(
                                'disp' => 'action',
                                'elements' => $languages,
                                'default' => (
                                        $selectedLanguage ? $selectedLanguage : InnomaticContainer::instance(
                                                'innomaticcontainer'
                                        )->getCurrentUser()->getLanguage())
                        )
                ),
                0, 1
        );
        $wuiLocaleGrid->addChild(
                new WuiSubmit(
                        'submit1',
                        array('caption' => $this->_localeCatalog->getStr('language_submit'))
                ),
                0, 2
        );

        $wuiVGroup->addChild($wuiLocaleGrid);
        $wuiVGroup->addChild(new WuiHorizBar('horizbar1'));
        $wuiVGroup->addChild(
                new WuiLabel(
                        'deflanglabel',
                        array(
                                'label' => sprintf(
                                        $this->_localeCatalog->getStr('countrylanguage_label'),
                                        $languages[$countryLanguage]
                                )
                        )
                )
        );

        $formEventsCall = new WuiEventsCall();
        $formEventsCall->addEvent(new WuiEvent('action', 'setlanguage', ''));
        $formEventsCall->addEvent(new WuiEvent('view', 'localization', ''));

        $wuiForm = new WuiForm('languageform', array('action' => $formEventsCall->getEventsCallString()));
        $wuiForm->addChild($wuiVGroup);

        $this->wuiMainframe->addChild($wuiForm);

        $this->wuiTitlebar->mArgs['title'].= ' - '.$this->_localeCatalog->getStr('country_title');
    }

    public function viewlanguage($eventData)
    {
        $actionDispatcher = new WuiDispatcher('action');

        $locCountry = new LocaleCountry(
                InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getCountry()
        );
        $countryLanguage = $locCountry->Language();

        $languageLocale = new LocaleCatalog(
                'innomatic::localization',
                InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getLanguage()
        );

        $selectedLanguage = $actionDispatcher->getEventData();
        if (isset($selectedLanguage['language'])) {
            $selectedLanguage = $selectedLanguage['language'];
        }

        $wuiVGroup = new WuiVertgroup('vgroup');

        $languageQuery = InnomaticContainer::instance(
                'innomaticcontainer'
        )->getDataAccess()->execute('SELECT * FROM locale_languages');

        while (!$languageQuery->eof) {
            $languages[$languageQuery->getFields('langshort')] = $languageLocale->getStr(
                    $languageQuery->getFields('langname')
            );
            $languageQuery->moveNext();
        }

        $wuiLocaleGrid = new WuiGrid('localegrid', array('rows' => '1', 'cols' => '3'));

        $wuiLocaleGrid->addChild(
                new WuiLabel(
                        'languagelabel',
                        array(
                                'label' => $this->_localeCatalog->getStr('language_label')
                        )
                ),
                0, 0
        );
        $wuiLocaleGrid->addChild(
                new WuiComboBox(
                        'language',
                        array(
                                'disp' => 'action',
                                'elements' => $languages,
                                'default' => ($selectedLanguage ? $selectedLanguage : InnomaticContainer::instance(
                                        'innomaticcontainer'
                                )->getCurrentUser()->getLanguage())
                        )
                ),
                0, 1
        );
        $wuiLocaleGrid->addChild(
                new WuiSubmit(
                        'submit1',
                        array('caption' => $this->_localeCatalog->getStr('language_submit'))
                ),
                0, 2
        );

        $wuiVGroup->addChild($wuiLocaleGrid);
        $wuiVGroup->addChild(new WuiHorizBar('horizbar1'));
        $wuiVGroup->addChild(
                new WuiLabel(
                        'deflanglabel',
                        array(
                                'label' => sprintf(
                                        $this->_localeCatalog->getStr('countrylanguage_label'),
                                        $languages[$countryLanguage]
                                )
                        )
                )
        );

        $formEventsCall = new WuiEventsCall();
        $formEventsCall->addEvent(new WuiEvent('action', 'setlanguage', ''));
        $formEventsCall->addEvent(new WuiEvent('view', 'language', ''));

        $wuiForm = new WuiForm('languageform', array('action' => $formEventsCall->getEventsCallString()));
        $wuiForm->addChild($wuiVGroup);

        $this->wuiMainframe->addChild($wuiForm);

        $this->wuiTitlebar->mArgs['title'].= ' - '.$this->_localeCatalog->getStr('language_title');
    }

    public function viewhelp($eventData)
    {
        $this->wuiTitlebar->mArgs['title'].= ' - '.$this->_localeCatalog->getStr('help_title');
        $this->wuiMainframe->addChild(
                new WuiHelpNode(
                        'locale_help',
                        array(
                                'base' => 'innomatic',
                                'node' => 'innomatic.domain.interface.'.$eventData['node'].'.html',
                                'language' => InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getLanguage()
                        )
                )
        );
    }
}
