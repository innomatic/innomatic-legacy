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
    protected $localeCatalog;

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
        $this->localeCatalog = new LocaleCatalog(
            'innomatic::domain_interface',
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getLanguage()
        );

        $this->wuiContainer->loadWidget('button');
        $this->wuiContainer->loadWidget('checkbox');
        $this->wuiContainer->loadWidget('combobox');
        $this->wuiContainer->loadWidget('date');
        $this->wuiContainer->loadWidget('empty');
        $this->wuiContainer->loadWidget('file');
        $this->wuiContainer->loadWidget('formarg');
        $this->wuiContainer->loadWidget('form');
        $this->wuiContainer->loadWidget('grid');
        $this->wuiContainer->loadWidget('helpnode');
        $this->wuiContainer->loadWidget('horizbar');
        $this->wuiContainer->loadWidget('horizframe');
        $this->wuiContainer->loadWidget('horizgroup');
        $this->wuiContainer->loadWidget('image');
        $this->wuiContainer->loadWidget('label');
        $this->wuiContainer->loadWidget('link');
        $this->wuiContainer->loadWidget('listbox');
        $this->wuiContainer->loadWidget('menu');
        $this->wuiContainer->loadWidget('page');
        $this->wuiContainer->loadWidget('progressbar');
        $this->wuiContainer->loadWidget('radio');
        $this->wuiContainer->loadWidget('sessionkey');
        $this->wuiContainer->loadWidget('statusbar');
        $this->wuiContainer->loadWidget('string');
        $this->wuiContainer->loadWidget('submit');
        $this->wuiContainer->loadWidget('tab');
        $this->wuiContainer->loadWidget('table');
        $this->wuiContainer->loadWidget('text');
        $this->wuiContainer->loadWidget('titlebar');
        $this->wuiContainer->loadWidget('toolbar');
        $this->wuiContainer->loadWidget('treemenu');
        $this->wuiContainer->loadWidget('vertframe');
        $this->wuiContainer->loadWidget('vertgroup');
        $this->wuiContainer->loadWidget('xml');

$this->wuiPage = new WuiPage('page', array('title' => $this->localeCatalog->getStr('interface_pagetitle')));
$this->wuiMainvertgroup = new WuiVertgroup('mainvertgroup');
$this->wuiTitlebar = new WuiTitleBar(
                               'titlebar',
                               array('title' => $this->localeCatalog->getStr('interface_title'), 'icon' => 'picture')
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
                                         'label' => $this->localeCatalog->getStr('default_button'),
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
                                         'label' => $this->localeCatalog->getStr('localization_button'),
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
                                         'label' => $this->localeCatalog->getStr('help_button'),
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
        $this->wuiContainer->addChild($this->wuiPage);
    }

    public function viewdefault($eventData)
    {
        //$app_cfg = new ApplicationSettings(
        //    'innomatic' );

        $themesQuery = \Innomatic\Core\InnomaticContainer::instance(
                '\Innomatic\Core\InnomaticContainer'
        )->getDataAccess()->execute('SELECT name,catalog FROM wui_themes');

        while (!$themesQuery->eof) {
            $tmpLocale = new LocaleCatalog(
                    $themesQuery->getFields('catalog'),
                    \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getLanguage()
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
                        .urlencode($this->localeCatalog->getStr('themes_label'))
                        .'</label><bold>true</bold></args></label>
          <listbox row="1" col="0"><name>theme</name><args><elements type="array">'
                                .WuiXml::encode($elements)
                                .'</elements><default>'
                                        . (\Innomatic\Wui\Wui::instance('\Innomatic\Wui\Wui')->getThemeName()).
                                        '</default><disp>action</disp><size>10</size></args></listbox>
        </children></grid>
        <submit><name>submit</name><args><caption type="encoded">'
                                                .urlencode($this->localeCatalog->getStr('settheme_submit'))
                                                .'</caption></args></submit>
      </children></form>
    </children></vertgroup>';

        $this->wuiMainframe->addChild(new WuiXml('page', array('definition' => $xmlDef)));

        $this->wuiTitlebar->mArgs['title'].= ' - '.$this->localeCatalog->getStr('themes_title');
    }

    public function viewlocalization($eventData)
    {
        $actionDispatcher = new WuiDispatcher('action');

        $eventData = $actionDispatcher->getEventData();

        $countryLocale = new LocaleCatalog(
                'innomatic::localization',
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getLanguage()
        );

        $selectedCountry = '';
        if (isset($eventData['country'])) {
            $selectedCountry = $eventData['country'];
        }

        $wuiVGroup = new WuiVertgroup('vgroup');

        $countryQuery = \Innomatic\Core\InnomaticContainer::instance(
                '\Innomatic\Core\InnomaticContainer'
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
                        array('label' => $this->localeCatalog->getStr('country_label'))
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
                                        $selectedCountry ? $selectedCountry : \Innomatic\Core\InnomaticContainer::instance(
                                                '\Innomatic\Core\InnomaticContainer'
                                        )->getCurrentUser()->getCountry()
                                )
                        )
                ),
                0, 1
        );
        $wuiLocaleGrid->addChild(
                new WuiSubmit(
                        'submit1',
                        array('caption' => $this->localeCatalog->getStr('country_submit'))
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
                \Innomatic\Core\InnomaticContainer::instance(
                        '\Innomatic\Core\InnomaticContainer'
                )->getCurrentUser()->getCountry()
        );
        $countryLanguage = $locCountry->Language();

        $languageLocale = new LocaleCatalog(
                'innomatic::localization',
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getLanguage()
        );

        $selectedLanguage = '';
        if (isset($eventData['language'])) {
            $selectedLanguage = $eventData['language'];
        }

        $wuiVGroup = new WuiVertgroup('vgroup');

        $languageQuery = \Innomatic\Core\InnomaticContainer::instance(
                '\Innomatic\Core\InnomaticContainer'
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
                        array('label' => $this->localeCatalog->getStr('language_label'))
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
                                        $selectedLanguage ? $selectedLanguage : \Innomatic\Core\InnomaticContainer::instance(
                                                '\Innomatic\Core\InnomaticContainer'
                                        )->getCurrentUser()->getLanguage())
                        )
                ),
                0, 1
        );
        $wuiLocaleGrid->addChild(
                new WuiSubmit(
                        'submit1',
                        array('caption' => $this->localeCatalog->getStr('language_submit'))
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
                                        $this->localeCatalog->getStr('countrylanguage_label'),
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

        $this->wuiTitlebar->mArgs['title'].= ' - '.$this->localeCatalog->getStr('country_title');
    }

    public function viewlanguage($eventData)
    {
        $actionDispatcher = new WuiDispatcher('action');

        $locCountry = new LocaleCountry(
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getCountry()
        );
        $countryLanguage = $locCountry->Language();

        $languageLocale = new LocaleCatalog(
                'innomatic::localization',
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getLanguage()
        );

        $selectedLanguage = $actionDispatcher->getEventData();
        if (isset($selectedLanguage['language'])) {
            $selectedLanguage = $selectedLanguage['language'];
        }

        $wuiVGroup = new WuiVertgroup('vgroup');

        $languageQuery = \Innomatic\Core\InnomaticContainer::instance(
                '\Innomatic\Core\InnomaticContainer'
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
                                'label' => $this->localeCatalog->getStr('language_label')
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
                                'default' => ($selectedLanguage ? $selectedLanguage : \Innomatic\Core\InnomaticContainer::instance(
                                        '\Innomatic\Core\InnomaticContainer'
                                )->getCurrentUser()->getLanguage())
                        )
                ),
                0, 1
        );
        $wuiLocaleGrid->addChild(
                new WuiSubmit(
                        'submit1',
                        array('caption' => $this->localeCatalog->getStr('language_submit'))
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
                                        $this->localeCatalog->getStr('countrylanguage_label'),
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

        $this->wuiTitlebar->mArgs['title'].= ' - '.$this->localeCatalog->getStr('language_title');
    }

    public function viewhelp($eventData)
    {
        $this->wuiTitlebar->mArgs['title'].= ' - '.$this->localeCatalog->getStr('help_title');
        $this->wuiMainframe->addChild(
                new WuiHelpNode(
                        'locale_help',
                        array(
                                'base' => 'innomatic',
                                'node' => 'innomatic.domain.interface.'.$eventData['node'].'.html',
                                'language' => \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getLanguage()
                        )
                )
        );
    }
}
