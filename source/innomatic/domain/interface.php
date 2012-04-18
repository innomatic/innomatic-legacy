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

// NOTE: This is an old-style panel code with a single file
// acting as model, view and controller.

require_once('innomatic/locale/LocaleCatalog.php');
require_once('innomatic/domain/user/UserSettings.php');
require_once('innomatic/domain/DomainSettings.php');
require_once('innomatic/wui/Wui.php');
require_once('innomatic/wui/widgets/WuiWidget.php');
require_once('innomatic/wui/widgets/WuiContainerWidget.php');
require_once('innomatic/wui/dispatch/WuiEventsCall.php');
require_once('innomatic/wui/dispatch/WuiEvent.php');
require_once('innomatic/wui/dispatch/WuiEventRawData.php');
require_once('innomatic/wui/dispatch/WuiDispatcher.php');

global $wuiMainStatus, $wuiPage, $wuiMainFrame, $wuiTitleBar, $innomaticLocale, $actionDispatcher;

$innomaticLocale = new LocaleCatalog('innomatic::domain_interface',
                                     InnomaticContainer::instance(
                                         'innomaticcontainer'
                                     )->getCurrentUser()->getLanguage());
$innomaticLog = InnomaticContainer::instance('innomaticcontainer')->getLogger();
$wui = Wui::instance('wui');
$wui->loadWidget('button');
$wui->loadWidget('checkbox');
$wui->loadWidget('combobox');
$wui->loadWidget('date');
$wui->loadWidget('empty');
$wui->loadWidget('file');
$wui->loadWidget('formarg');
$wui->loadWidget('form');
$wui->loadWidget('grid');
$wui->loadWidget('helpnode');
$wui->loadWidget('horizbar');
$wui->loadWidget('horizframe');
$wui->loadWidget('horizgroup');
$wui->loadWidget('image');
$wui->loadWidget('label');
$wui->loadWidget('link');
$wui->loadWidget('listbox');
$wui->loadWidget('menu');
$wui->loadWidget('page');
$wui->loadWidget('progressbar');
$wui->loadWidget('radio');
$wui->loadWidget('sessionkey');
$wui->loadWidget('statusbar');
$wui->loadWidget('string');
$wui->loadWidget('submit');
$wui->loadWidget('tab');
$wui->loadWidget('table');
$wui->loadWidget('text');
$wui->loadWidget('titlebar');
$wui->loadWidget('toolbar');
$wui->loadWidget('treemenu');
$wui->loadWidget('vertframe');
$wui->loadWidget('vertgroup');
$wui->loadWidget('xml');

$wuiPage = new WuiPage('page', array('title' => $innomaticLocale->getStr('interface_pagetitle')));
$wuiMainVertGroup = new WuiVertGroup('mainvertgroup');
$wuiTitleBar = new WuiTitleBar(
                               'titlebar',
                               array('title' => $innomaticLocale->getStr('interface_title'), 'icon' => 'kcontrol')
                              );
$wuiMainVertGroup->addChild($wuiTitleBar);

// Main tool bar
//
$wuiMainToolbar = new WuiToolBar('maintoolbar');

$defaultAction = new WuiEventsCall();
$defaultAction->addEvent(new WuiEvent('view', 'default', ''));
$wuiDefaultButton = new WuiButton(
                                   'defaultbutton',
                                   array(
                                         'label' => $innomaticLocale->getStr('default_button'),
                                         'themeimage' => 'thumbnail',
                                         'horiz' => 'true',
                                         'action' => $defaultAction->getEventsCallString()
                                        )
                                  );
$wuiMainToolbar->addChild($wuiDefaultButton);

$desktopAction = new WuiEventsCall();
$desktopAction->addEvent(new WuiEvent('view', 'desktop', ''));
$wuiDesktopButton = new WuiButton(
                                   'desktopbutton',
                                   array(
                                         'label' => $innomaticLocale->getStr('desktop_button'),
                                         'themeimage' => 'desktop',
                                         'horiz' => 'true',
                                         'action' => $desktopAction->getEventsCallString()
                                        )
                                  );
$wuiMainToolbar->addChild($wuiDesktopButton);

$countryAction = new WuiEventsCall();
$countryAction->addEvent(new WuiEvent('view', 'localization', ''));
$wuiCountryButton = new WuiButton(
                                   'countrybutton',
                                   array(
                                         'label' => $innomaticLocale->getStr('localization_button'),
                                         'themeimage' => 'locale',
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
                                         'label' => $innomaticLocale->getStr('help_button'),
                                         'themeimage' => 'help',
                                         'horiz' => 'true',
                                         'action' => $helpAction->getEventsCallString()
                                         )
                                   );

    $wuiHelpToolBar->addChild($wuiHelpButton);
}

// Toolbar frame
//
$wuiToolBarFrame = new WuiHorizGroup('toolbarframe');

$wuiToolBarFrame->addChild($wuiMainToolbar);
$wuiToolBarFrame->addChild($wuiHelpToolBar);
$wuiMainVertGroup->addChild($wuiToolBarFrame);

$wuiMainFrame = new WuiVertFrame('mainframe');
$wuiMainStatus = new WuiStatusBar('mainstatusbar');

// Pass dispatcher
//
$actionDispatcher = new WuiDispatcher('action');

$actionDispatcher->addEvent('settheme', 'pass_settheme');
function pass_settheme($eventData)
{
    global $wuiMainStatus, $innomaticLocale, $wuiPage;

    $userCfg = new UserSettings(
    InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess(),
    InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getUserId());
    $userCfg->setKey('wui-theme', $eventData['theme']);

    if (
        User::isAdminUser(
            InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getUserName(),
            InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDomainId()
        )
    ) {
        $domainCfg = new DomainSettings(
                                         InnomaticContainer::instance(
                                             'innomaticcontainer'
                                         )->getCurrentDomain()->getDataAccess());
        $domainCfg->EditKey('wui-theme', $eventData['theme']);
    }

    $wui = Wui::instance('wui');
    $wui->setTheme($eventData['theme']);

    WebAppContainer::instance(
        'webappcontainer'
    )->getProcessor()->getResponse()->addHeader(
        'Location',
        WuiEventsCall::buildEventsCallString(
            '',
            array(
                                                   array('view', 'default', ''),
                                                   array('action', 'settheme2', '')
                                                  )
        )
    );
}

$actionDispatcher->addEvent('settheme2', 'pass_settheme2');
function pass_settheme2($eventData)
{
    global $wuiMainStatus, $innomaticLocale, $wuiPage;

    $wuiMainStatus->mArgs['status'] = $innomaticLocale->getStr('themeset_status');
    $wuiPage->mArgs['javascript'] = "parent.frames.menu.location.reload();\nparent.frames.header.location.reload()";
}

$actionDispatcher->addEvent('setdesktop', 'pass_setdesktop');
function pass_setdesktop($eventData)
{
    global $wuiMainStatus, $innomaticLocale;

    $userCfg = new UserSettings(
    InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess(),
    InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getUserId());
    $userCfg->setKey('desktop-layout', $eventData['layout']);

    if (
        User::isAdminUser(
            InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getUserName(),
            InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDomainId()
        )
    ) {
        $domainCfg = new DomainSettings(
            InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess()
        );
        $domainCfg->EditKey('desktop-layout', $eventData['layout']);
    }

    WebAppContainer::instance(
        'webappcontainer'
    )->getProcessor()->getResponse()->addHeader(
        'Location',
        WuiEventsCall::buildEventsCallString(
            '',
            array(
                array('view', 'desktop', ''),
                array('action', 'setdesktop2', '')
            )
        )
    );
}

$actionDispatcher->addEvent('setdesktop2', 'pass_setdesktop2');
function pass_setdesktop2($eventData)
{
    global $wuiMainStatus, $innomaticLocale, $wuiPage;

    $wuiMainStatus->mArgs['status'] = $innomaticLocale->getStr('desktopset_status');
    require_once('innomatic/webapp/WebAppContainer.php');
    $uri = dirname(WebAppContainer::instance('webappcontainer')->getProcessor()->getRequest()->getRequestURI());
    $wuiPage->mArgs['javascript'] = "parent.location.href='".$uri."'";
}

$actionDispatcher->addEvent('setlanguage', 'pass_setlanguage');
function pass_setlanguage($eventData)
{
    global $wuiMainStatus, $innomaticLocale, $wuiPage;

    $userCfg = new UserSettings(
    InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess(),
    InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getUserId());
    $userCfg->setKey('desktop-language', $eventData['language']);

    $domainSets = new DomainSettings(
        InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess()
    );

    if (
        User::isAdminUser(
            InnomaticContainer::instance(
                'innomaticcontainer'
            )->getCurrentUser()->getUserName(),
            InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDomainId()
        )
    ) {
        $domainSets->EditKey('desktop-language', $eventData['language']);
    }

    $wuiPage->mArgs['javascript'] = 'parent.frames.menu.location.reload()';

    $wuiMainStatus->mArgs['status'] = $innomaticLocale->getStr('languageset_status');
}

$actionDispatcher->addEvent('setcountry', 'pass_setcountry');
function pass_setcountry($eventData)
{
    global $wuiMainStatus, $innomaticLocale, $wuiPage;

    $userCfg = new UserSettings(
    InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess(),
    InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getUserId());
    $userCfg->setKey('desktop-country', $eventData['country']);

    $domainSettings = new DomainSettings(
        InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDataAccess()
    );

    if (
        User::isAdminUser(
            InnomaticContainer::instance(
                'innomaticcontainer'
            )->getCurrentUser()->getUserName(),
            InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDomainId()
        )
    ) {
        $domainSettings->EditKey('desktop-country', $eventData['country']);
    }

    $wuiPage->mArgs['javascript'] = 'parent.frames.menu.location.reload()';

    $wuiMainStatus->mArgs['status'] = $innomaticLocale->getStr('countryset_status');
}

$actionDispatcher->Dispatch();

// Main dispatcher
//
$viewDispatcher = new WuiDispatcher('view');

$viewDispatcher->addEvent('default', 'main_default');
function main_default($eventData)
{
    global $wuiMainFrame, $wuiTitleBar, $innomaticLocale;

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
            .urlencode($innomaticLocale->getStr('themes_label'))
            .'</label><bold>true</bold></args></label>
          <listbox row="1" col="0"><name>theme</name><args><elements type="array">'
            .WuiXml::encode($elements)
            .'</elements><default>'
            . (Wui::instance('wui')->getThemeName()).
            '</default><disp>action</disp><size>10</size></args></listbox>
        </children></grid>
        <submit><name>submit</name><args><caption type="encoded">'
        .urlencode($innomaticLocale->getStr('settheme_submit'))
        .'</caption></args></submit>
      </children></form>
    </children></vertgroup>';

    $wuiMainFrame->addChild(new WuiXml('page', array('definition' => $xmlDef)));

    $wuiTitleBar->mTitle.= ' - '.$innomaticLocale->getStr('themes_title');
}

$viewDispatcher->addEvent('desktop', 'main_desktop');
function main_desktop($eventData)
{
    global $wuiMainFrame, $wuiTitleBar, $innomaticLocale;

    $layout = DesktopLayout::instance('desktoplayout')->getLayout();
    switch ($layout)
    {
        case 'horiz':
        case 'vert':
            break;
        default:
            $layout = 'horiz';
    }

    $xmlDef = '<vertgroup><name>vgroup</name><args><halign>center</halign></args><children>
      <form><name>desktop</name><args><action type="encoded">'
        .urlencode(
            WuiEventsCall::buildEventsCallString(
                '', array(
                        array('view', 'desktop', ''),
                        array('action', 'setdesktop', '')
                )
            )
        )
        .'</action></args><children>
        <grid><name>desktopgrid</name><children>
          <label row="0" col="0"><name>desktoplabel</name><args><label type="encoded">'
            .urlencode($innomaticLocale->getStr('desktop_label'))
            .'</label><bold>true</bold></args></label>
            <radio row="1" col="0" halign="center"><name>layout</name>
              <args>
                <disp>action</disp>
                <checked>'. ($layout == 'horiz' ? 'true' : 'false').'</checked>
                <value>horiz</value>
              </args>
            </radio>
    
            <radio row="2" col="0" halign="center"><name>layout</name>
              <args>
                <disp>action</disp>
                <checked>'. ($layout == 'vert' ? 'true' : 'false').'</checked>
                <value>vert</value>
              </args>
            </radio>
            
            <label row="1" col="1">
              <args>
                <label type="encoded">'.urlencode($innomaticLocale->getStr('layout_horiz.label')).'</label>
              </args>
            </label>
    
            <label row="2" col="1">
              <args>
                <label type="encoded">'.urlencode($innomaticLocale->getStr('layout_vert.label')).'</label>
              </args>
            </label>
    
                  </children></grid>
        <submit><name>submit</name><args><caption type="encoded">'
        .urlencode($innomaticLocale->getStr('setdesktop_submit'))
        .'</caption></args></submit>
      </children></form>
    </children></vertgroup>';

    $wuiMainFrame->addChild(new WuiXml('page', array('definition' => $xmlDef)));

    $wuiTitleBar->mTitle.= ' - '.$innomaticLocale->getStr('desktop_title');
}

$viewDispatcher->addEvent('localization', 'main_localization');
function main_localization($eventData)
{
    global $wuiMainFrame, $wuiTitleBar, $innomaticLocale, $actionDispatcher, $wuiMainStatus;

    $eventData = $actionDispatcher->getEventData();
    
    $countryLocale = new LocaleCatalog(
        'innomatic::localization',
        InnomaticContainer::instance('innomaticcontainer')->getCurrentUser()->getLanguage()
    );

    $selectedCountry = '';
    if (isset($eventData['country'])) {
        $selectedCountry = $eventData['country'];
    }

    $wuiVGroup = new WuiVertGroup('vgroup');

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
            array('label' => $innomaticLocale->getStr('country_label'))
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
            array('caption' => $innomaticLocale->getStr('country_submit'))
            ),
        0, 2
    );

    $wuiVGroup->addChild($wuiLocaleGrid);

    $formEventsCall = new WuiEventsCall();
    $formEventsCall->addEvent(new WuiEvent('action', 'setcountry', ''));
    $formEventsCall->addEvent(new WuiEvent('view', 'localization', ''));

    $wuiForm = new WuiForm('countryform', array('action' => $formEventsCall->getEventsCallString()));
    $wuiForm->addChild($wuiVGroup);

    $wuiMainFrame->addChild($wuiForm);

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

    $wuiVGroup = new WuiVertGroup('vgroup');

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
            array('label' => $innomaticLocale->getStr('language_label'))
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
            array('caption' => $innomaticLocale->getStr('language_submit'))
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
                    $innomaticLocale->getStr('countrylanguage_label'),
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

    $wuiMainFrame->addChild($wuiForm);
    
    $wuiTitleBar->mTitle.= ' - '.$innomaticLocale->getStr('country_title');
}

$viewDispatcher->addEvent('language', 'main_language');
function main_language($eventData)
{
    global $wuiMainFrame, $wuiTitleBar, $innomaticLocale, $actionDispatcher, $wuiMainStatus;

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

    $wuiVGroup = new WuiVertGroup('vgroup');

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
                'label' => $innomaticLocale->getStr('language_label')
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
            array('caption' => $innomaticLocale->getStr('language_submit'))
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
                    $innomaticLocale->getStr('countrylanguage_label'),
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

    $wuiMainFrame->addChild($wuiForm);

    $wuiTitleBar->mTitle.= ' - '.$innomaticLocale->getStr('language_title');
}

$viewDispatcher->addEvent('help', 'main_help');
function main_help($eventData)
{
    global $wuiTitleBar, $wuiMainFrame, $innomaticLocale;
    $wuiTitleBar->mTitle.= ' - '.$innomaticLocale->getStr('help_title');
    $wuiMainFrame->addChild(
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

$viewDispatcher->Dispatch();

// Page render
//
$wuiMainVertGroup->addChild($wuiMainFrame);
$wuiMainVertGroup->addChild($wuiMainStatus);
$wuiPage->addChild($wuiMainVertGroup);
$wui->addChild($wuiPage);
$wui->render();
