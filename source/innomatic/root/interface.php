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
 * @license    http://www.innomatic.org/license/   BSD License
 * @link       http://www.innomatic.org
 * @since      Class available since Release 5.0
*/

// NOTE: This is an old-style panel code with a single file
// acting as model, view and controller.

global $innomaticLocale, $innomaticLog, $wuiMainframeGlobal, $wuiTitleBar, $wuiMainFrame;
global $wuiMainStatus, $wuiPage, $wuiComments, $compressedOb, $actionDispatcher;

$innomaticLocale = new \Innomatic\Locale\LocaleCatalog(
    'innomatic::root_interface',
    \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLanguage()
);
$innomaticLog = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();
$wui = \Innomatic\Wui\Wui::instance('\Innomatic\Wui\Wui');
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

$wuiComments = '';
$compressedOb = '';

$wuiPage = new WuiPage('page', array('title' => $innomaticLocale->getStr('interface_pagetitle')));
$wuiMainVertGroup = new WuiVertgroup('mainvertgroup');
$wuiTitleBar = new WuiTitleBar(
    'titlebar',
    array(
        'title' => $innomaticLocale->getStr('interface_title'),
        'icon' => 'picture'
    )
);
$wuiMainVertGroup->addChild($wuiTitleBar);

// Main tool bar
//
$wuiMainToolbar = new WuiToolBar('maintoolbar');

$defaultAction = new \Innomatic\Wui\Dispatch\WuiEventsCall();
$defaultAction->addEvent(new \Innomatic\Wui\Dispatch\WuiEvent('view', 'default', ''));
$wuiDefaultButton = new WuiButton(
    'defaultbutton',
    array(
        'label' => $innomaticLocale->getStr('default_button'),
        'themeimage' => 'glasses',
        'horiz' => 'true',
        'action' => $defaultAction->getEventsCallString()
    )
);
$wuiMainToolbar->addChild($wuiDefaultButton);

$countryAction= new \Innomatic\Wui\Dispatch\WuiEventsCall();
$countryAction->addEvent(new \Innomatic\Wui\Dispatch\WuiEvent('view', 'localization', ''));
$wuiCountryButton = new WuiButton(
    'countrybutton',
    array(
        'label' => $innomaticLocale->getStr('localization_button'),
        'themeimage' => 'globe2',
        'horiz' => 'true',
        'action' => $countryAction->getEventsCallString()
    )
);
$wuiMainToolbar->addChild($wuiCountryButton);

$nameAction = new \Innomatic\Wui\Dispatch\WuiEventsCall();
$nameAction->addEvent(new \Innomatic\Wui\Dispatch\WuiEvent('view', 'name', ''));
$wuiNameButton = new WuiButton(
    'namebutton',
    array(
        'label' => $innomaticLocale->getStr('name.button'),
        'themeimage' => 'home',
        'horiz' => 'true',
        'action' => $nameAction->getEventsCallString()
    )
);
$wuiMainToolbar->addChild($wuiNameButton);

// Help tool bar
//
$wuiHelpToolBar = new WuiToolBar('helpbar');

$viewDispatcher = new \Innomatic\Wui\Dispatch\WuiDispatcher('view');
$eventName = $viewDispatcher->getEventName();

if (
    strcmp($eventName, 'help')
) {
    $helpAction = new \Innomatic\Wui\Dispatch\WuiEventsCall();
    $helpAction->addEvent(new \Innomatic\Wui\Dispatch\WuiEvent('view', 'help', array('node' => $eventName)));
    $wuiHelpButton = new WuiButton(
        'helpbutton',
        array(
            'label' => $innomaticLocale->getStr('help_button'),
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
$wuiMainVertGroup->addChild($wuiToolBarFrame);

$wuiMainFrame = new WuiVertframe('mainframe');
$wuiMainStatus = new WuiStatusBar('mainstatusbar');

// Pass dispatcher
//
$actionDispatcher = new \Innomatic\Wui\Dispatch\WuiDispatcher('action');

$actionDispatcher->addEvent('setserviceprovider', 'pass_setserviceprovider');
function pass_setserviceprovider($eventData)
{
    global $wuiMainStatus, $innomaticLocale, $wuiPage;

    $log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();

    $appCfg = new \Innomatic\Application\ApplicationSettings(
        'innomatic'
    );
    $appCfg->setKey('serviceprovider-name', $eventData['serviceprovidername']);
    $appCfg->setKey('serviceprovider-url', $eventData['serviceproviderurl']);

    if (strcmp($eventData['serviceproviderbiglogo']['tmp_name'], 'none') != 0) {
        if (is_uploaded_file($eventData['serviceproviderbiglogo']['tmp_name'])) {
            $extension = substr(
                $eventData['serviceproviderbiglogo']['name'],
                strrpos($eventData['serviceproviderbiglogo']['name'], '.')
            );
            move_uploaded_file(
                $eventData['serviceproviderbiglogo']['tmp_name'],
                \Innomatic\Core\InnomaticContainer::instance(
                    '\Innomatic\Core\InnomaticContainer'
                )->getHome().'shared/serviceproviderbiglogo'.$extension
            );
            $appCfg->setKey('serviceprovider-biglogo-filename', 'serviceproviderbiglogo'.$extension);
        }
    }

    if (strcmp($eventData['serviceproviderlinklogo']['tmp_name'], 'none') != 0) {
        if (is_uploaded_file($eventData['serviceproviderlinklogo']['tmp_name'])) {
            $extension = substr(
                $eventData['serviceproviderlinklogo']['name'],
                strrpos($eventData['serviceproviderlinklogo']['name'], '.')
            );
            move_uploaded_file(
                $eventData['serviceproviderlinklogo']['tmp_name'],
                \Innomatic\Core\InnomaticContainer::instance(
                    '\Innomatic\Core\InnomaticContainer'
                )->getHome().'shared/serviceproviderlinklogo'.$extension
            );
            $appCfg->setKey('serviceprovider-link-filename', 'serviceproviderlinklogo'.$extension);
        }
    }

    $log->logEvent('Innomatic', 'Changed Service Provider settings', \Innomatic\Logging\Logger::NOTICE);

    $wuiMainStatus->mArgs['status'] = $innomaticLocale->getStr('iconsset_status');
}

$actionDispatcher->addEvent('setenabledicons', 'pass_setenabledicons');
function pass_setenabledicons($eventData)
{
    global $wuiMainStatus, $innomaticLocale, $wuiPage;

    $log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();
    $appCfg = new \Innomatic\Application\ApplicationSettings('innomatic');
    $appCfg->setKey('innomatic-link-disabled', $eventData['innomaticicon'] == 'on' ? 0 : 1);
    $appCfg->setKey('serviceprovider-link-disabled', $eventData['serviceprovidericon'] == 'on' ? 0 : 1);
    $appCfg->setKey('innomatic-biglogo-disabled', $eventData['innomaticbigicon'] == 'on' ? 0 : 1);
    $appCfg->setKey('serviceprovider-biglogo-disabled', $eventData['serviceproviderbigicon'] == 'on' ? 0 : 1);

    $log->logEvent('Innomatic', 'Changed Innomatic interface settings', \Innomatic\Logging\Logger::NOTICE);

    $wuiMainStatus->mArgs['status'] = $innomaticLocale->getStr('iconsset_status');
}

$actionDispatcher->addEvent('settheme', 'pass_settheme');
function pass_settheme($eventData)
{
    global $wuiMainStatus, $innomaticLocale, $wuiPageGlobal;

    $log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();

    $appCfg = new \Innomatic\Application\ApplicationSettings('innomatic');
    $appCfg->setKey('wui-root-theme', $eventData['theme']);

    $wui = \Innomatic\Wui\Wui::instance('\Innomatic\Wui\Wui');
    $wui->setTheme($eventData['theme']);

    $log->logEvent('Innomatic', 'Changed Innomatic theme', \Innomatic\Logging\Logger::NOTICE);

    \Innomatic\Webapp\WebAppContainer::instance(
        '\Innomatic\Webapp\WebAppContainer'
    )->getProcessor()->getResponse()->addHeader(
        'Location',
        \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString(
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
}
$actionDispatcher->addEvent('setadvanced', 'pass_setadvanced');
function pass_setadvanced($eventData)
{
    global $wuiMainStatus, $innomaticLocale, $wuiPage, $wuiComments, $compressedOb;

    $log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();

    $innomaticCfg = new \Innomatic\Config\ConfigFile(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getConfigurationFile());
    $innomaticCfg->setValue('ShowWuiSourceComments', $eventData['wui-comments'] == 'on' ? '1' : '0');
    $innomaticCfg->setValue('CompressedOutputBuffering', $eventData['compressed-ob'] == 'on' ? '1' : '0');
    \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getConfig()->refresh();
    $wuiComments = 'false';
    $compressedOb = 'false';

    if ($eventData['wui-comments'] == 'on')
        $wuiComments = 'true';
    if ($eventData['compressed-ob'] == 'on')
        $compressedOb = 'true';

    $log->logEvent('Innomatic', 'Changed Innomatic advanced interface settings', \Innomatic\Logging\Logger::NOTICE);

    $wuiMainStatus->mArgs['status'] = $innomaticLocale->getStr('advancedset_status');
}

$actionDispatcher->addEvent('setlanguage', 'pass_setlanguage');
function pass_setlanguage($eventData)
{
    global $wuiMainStatus, $innomaticLocale, $wuiPage;

    $log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();

    $innomaticConfig = new \Innomatic\Config\ConfigFile(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getConfigurationFile());
    $innomaticConfig->setValue('RootLanguage', $eventData['language']);
    \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getConfig()->refresh();

    $log->logEvent('Innomatic', 'Changed Innomatic root language', \Innomatic\Logging\Logger::NOTICE);

    $wuiMainStatus->mArgs['status'] = $innomaticLocale->getStr('languageset_status');
}

$actionDispatcher->addEvent('setcountry', 'pass_setcountry');
function pass_setcountry($eventData)
{
    global $wuiMainStatus, $innomaticLocale, $wuiPage;

    $log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();

    $innomaticConfig = new \Innomatic\Config\ConfigFile(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getConfigurationFile());
    $innomaticConfig->setValue('RootCountry', $eventData['country']);
    \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getConfig()->refresh();
    $log->logEvent('Innomatic', 'Changed Innomatic root country', \Innomatic\Logging\Logger::NOTICE);

    $wuiMainStatus->mArgs['status'] = $innomaticLocale->getStr('countryset_status');
}

$actionDispatcher->addEvent('editname', 'pass_editname');
function pass_editname($eventData)
{
    global $wuiPage, $wuiMainStatus, $innomaticLocale;

    $log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();

    $innomaticcfg = new \Innomatic\Config\ConfigFile(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getConfigurationFile());
    $innomaticcfg->setValue('PlatformName', $eventData['name']);
    $innomaticcfg->setValue('PlatformGroup', $eventData['domain']);

    $log->logEvent('Innomatic', 'Changed Innomatic network settings', \Innomatic\Logging\Logger::NOTICE);
    $wuiMainStatus->mArgs['status'] = $innomaticLocale->getStr('datachanged');
}
$actionDispatcher->Dispatch();

// Main dispatcher
//
$viewDispatcher = new \Innomatic\Wui\Dispatch\WuiDispatcher('view');

function interface_tab_action_builder($tab)
{
    return \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('', array(array('view', 'default', array('activetab' => $tab))));
}

$viewDispatcher->addEvent('default', 'main_default');
function main_default($eventData)
{
    global $wuiMainFrame, $wuiTitleBar, $innomaticLocale, $actionDispatcher,
           $wuiMainStatus, $wuiComments, $compressedOb;
    $appCfg = new \Innomatic\Application\ApplicationSettings('innomatic');

    $themesQuery = \Innomatic\Core\InnomaticContainer::instance(
        '\Innomatic\Core\InnomaticContainer'
    )->getDataAccess()->execute('SELECT name,catalog FROM wui_themes ');
    while (!$themesQuery->eof) {
        $tmpLocale = new \Innomatic\Locale\LocaleCatalog(
            $themesQuery->getFields('catalog'),
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLanguage()
        );
        $elements[$themesQuery->getFields('name')] = $tmpLocale->getStr($themesQuery->getFields('name'));
        $themesQuery->moveNext();
    }
    asort($elements);

    $themesXmlDef = '<vertgroup><name>vgroup</name><args><halign>center</halign></args><children>
        <form><name>theme</name><args><action type="encoded">'
        .urlencode(
            \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString(
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
            .urlencode($innomaticLocale->getStr('themes_label')).'</label><bold>true</bold></args></label>
            <listbox row="1" col="0"><name>theme</name><args><elements type="array">'
            .WuiXml::encode($elements).'</elements><default>'
            . (\Innomatic\Wui\Wui::instance('\Innomatic\Wui\Wui')->getThemeName())
            .'</default><disp>action</disp><size>10</size></args></listbox>
        </children></grid>
        <submit><name>submit</name><args><caption type="encoded">'
        .urlencode($innomaticLocale->getStr('settheme_submit'))
        .'</caption></args></submit>
      </children></form>
    </children></vertgroup>';

    // Service provider settings

    // Service Provider
    //
    $serviceProviderFrame = new WuiVertframe('serviceproviderframe');

    $serviceProviderVGroup = new WuiVertgroup('serviceprovidervgroup', array('width' => '100%'));

    $serviceProviderVGroup->addChild(
        new WuiLabel(
            'serviceproviderlabel',
            array(
                'label' => $innomaticLocale->getStr('serviceproviderframe_label'),
                'bold' => 'true'
            )
        )
    );

    $serviceProviderGrid = new WuiGrid('serviceprovidergrid', array('rows' => '4', 'cols' => '2'));

    // Service Provider name
    //
    $serviceProviderGrid->addChild(
        new WuiLabel(
            'serviceprovidername_label',
            array(
                'label' => $innomaticLocale->getStr('serviceprovidername_label')
            )
        ),
        0, 0
    );

    $serviceProviderGrid->addChild(
        new WuiString(
            'serviceprovidername',
            array(
                'disp' => 'action',
                'size' => '30',
                'value' => $appCfg->getKey('serviceprovider-name')
            )
        ),
        0, 1
    );

    // Service Provider url
    //
    $serviceProviderGrid->addChild(
        new WuiLabel(
            'serviceproviderurl_label',
            array(
                'label' => $innomaticLocale->getStr('serviceproviderurl_label')
            )
        ),
        1, 0
    );

    $serviceProviderGrid->addChild(
        new WuiString(
            'serviceproviderurl',
            array(
                'disp' => 'action',
                'size' => '30',
                'value' => $appCfg->getKey('serviceprovider-url')
            )
        ),
        1, 1
    );

    // Service Provider big logo
    //
    $serviceProviderGrid->addChild(
        new WuiLabel(
            'serviceproviderbiglogo_label',
            array(
                'label' => $innomaticLocale->getStr('serviceproviderbiglogo_label')
            )
        ),
        2, 0
    );

    $serviceProviderGrid->addChild(new WuiFile('serviceproviderbiglogo', array('disp' => 'action')), 2, 1);

    // Service Provider link logo
    //
    $serviceProviderGrid->addChild(
        new WuiLabel(
            'serviceproviderlogo_label',
            array(
                'label' => $innomaticLocale->getStr('serviceproviderlogo_label')
            )
        ),
        3, 0
    );

    $serviceProviderGrid->addChild(new WuiFile('serviceproviderlinklogo', array('disp' => 'action')), 3, 1);

    $serviceProviderVGroup->addChild($serviceProviderGrid);

    $serviceProviderVGroup->addChild(
        new WuiSubmit(
            'serviceprovidersubmit',
            array(
                'caption' => $innomaticLocale->getStr('serviceprovider_submit')
            )
        )
    );

    $formEventsCall = new \Innomatic\Wui\Dispatch\WuiEventsCall();
    $formEventsCall->addEvent(new \Innomatic\Wui\Dispatch\WuiEvent('action', 'setserviceprovider', ''));
    $formEventsCall->addEvent(new \Innomatic\Wui\Dispatch\WuiEvent('view', 'default', ''));

    $serviceProviderForm = new WuiForm(
        'setserviceproviderform',
        array(
            'action' => $formEventsCall->getEventsCallString()
        )
    );
    $serviceProviderForm->addChild($serviceProviderVGroup);

    // Enabled icons
    //
    $enableVGroup = new WuiVertgroup('enablevgroup', array('width' => '100%'));

    $enableVGroup->addChild(
        new WuiLabel(
            'enablelabel',
            array(
                'label' => $innomaticLocale->getStr('enabled_icons_label'),
                'bold' => 'true'
            )
        )
    );

    $enableGrid = new WuiGrid('enablegrid', array('rows' => '5', 'cols' => '2'));

    // Innomatic site link
    //
    $enableGrid->addChild(
        new WuiLabel(
            'innomaticlabel',
            array(
                'label' => $innomaticLocale->getStr('innomatic_link_enabled_label')
            )
        ),
        0, 1
    );

    $enableGrid->addChild(
        new WuiCheckBox(
            'innomaticicon',
            array(
                'disp' => 'action',
                'checked' => $appCfg->getKey('innomatic-link-disabled') ? 'false' : 'true'
            )
        ),
        0, 0
    );

    // Service Provider link
    //
    $enableGrid->addChild(
        new WuiLabel(
            'serviceprovidericonlabel',
            array(
                'label' => $innomaticLocale->getStr('serviceprovider_link_enabled_label')
            )
        ),
        1, 1
    );
    $enableGrid->addChild(
        new WuiCheckBox(
            'serviceprovidericon',
            array(
                'disp' => 'action',
                'checked' => $appCfg->getKey('serviceprovider-link-disabled') ? 'false' : 'true'
            )
        ),
        1, 0
    );

    // Innomatic big logo
    //
    $enableGrid->addChild(
        new WuiLabel(
            'innomaticbigiconlabel',
            array(
                'label' => $innomaticLocale->getStr('innomatic_biglogo_enabled_label')
            )
        ),
        2, 1
    );
    $enableGrid->addChild(
        new WuiCheckBox(
            'innomaticbigicon',
            array(
                'disp' => 'action',
                'checked' => $appCfg->getKey('innomatic-biglogo-disabled') ? 'false' : 'true'
            )
        ),
        2, 0
    );

    // Service Provider logo
    //
    $enableGrid->addChild(
        new WuiLabel(
            'serviceprovidericonlabel',
            array(
                'label' => $innomaticLocale->getStr('serviceprovider_biglogo_enabled_label')
            )
        ),
        3, 1
    );
    $enableGrid->addChild(
        new WuiCheckBox(
            'serviceproviderbigicon',
            array(
                'disp' => 'action',
                'checked' => $appCfg->getKey('serviceprovider-biglogo-disabled') ? 'false' : 'true'
            )
        ),
        3, 0
    );

    $enableVGroup->addChild($enableGrid);

    $enableVGroup->addChild(
        new WuiSubmit(
            'enablesubmit',
            array(
                'caption' => $innomaticLocale->getStr('enable_submit')
            )
        )
    );

    $formEventsCall = new \Innomatic\Wui\Dispatch\WuiEventsCall();
    $formEventsCall->addEvent(new \Innomatic\Wui\Dispatch\WuiEvent('action', 'setenabledicons', ''));
    $formEventsCall->addEvent(new \Innomatic\Wui\Dispatch\WuiEvent('view', 'default', ''));

    $enableForm = new WuiForm('setenableform', array('action' => $formEventsCall->getEventsCallString()));
    $enableForm->addChild($enableVGroup);

    // Advanced settings
    //
    $advancedVGroup = new WuiVertgroup('enablevgroup', array('width' => '100%'));

    $advancedVGroup->addChild(
        new WuiLabel(
            'enablelabel',
            array(
                'label' => $innomaticLocale->getStr('advancedsettings_label'),
                'bold' => 'true'
            )
        )
    );

    $advancedGrid = new WuiGrid('enablegrid', array('rows' => '2', 'cols' => '2'));

    // Compressed output buffering
    //
    $advancedGrid->addChild(
        new WuiLabel(
            'compressed-ob-label',
            array(
                'label' => $innomaticLocale->getStr('compressed-ob_label')
            )
        ),
        0, 1
    );

    if (!strlen($compressedOb)) {
        if (
            \Innomatic\Core\InnomaticContainer::instance(
                '\Innomatic\Core\InnomaticContainer'
            )->getConfig()->value('CompressedOutputBuffering') == '1'
        ) {
            $compressedOb = 'true';
        } else {
            $compressedOb = 'false';
        }
    }

    $advancedGrid->addChild(
        new WuiCheckBox(
            'compressed-ob',
            array(
                'disp' => 'action',
                'checked' => $compressedOb
            )
        ),
        0, 0
    );

    // WUI code comments
    //
    $advancedGrid->addChild(
        new WuiLabel(
            'wui-comments-label',
            array(
                'label' => $innomaticLocale->getStr('wui-comments_label')
            )
        ),
        1, 1
    );

    if (!strlen($wuiComments)) {
        if (\Innomatic\Wui\Wui::showSourceComments())
            $wuiComments = 'true';
        else
            $wuiComments = 'false';
    }

    $advancedGrid->addChild(
        new WuiCheckBox(
            'wui-comments',
            array(
                'disp' => 'action',
                'checked' => $wuiComments
            )
        ),
        1, 0
    );

    $advancedVGroup->addChild($advancedGrid);

    $advancedVGroup->addChild(
        new WuiSubmit(
            'enablesubmit',
            array(
                'caption' => $innomaticLocale->getStr('advanced_submit')
            )
        )
    );

    $formEventsCall = new \Innomatic\Wui\Dispatch\WuiEventsCall();
    $formEventsCall->addEvent(new \Innomatic\Wui\Dispatch\WuiEvent('action', 'setadvanced', ''));
    $formEventsCall->addEvent(new \Innomatic\Wui\Dispatch\WuiEvent('view', 'default', ''));

    $advancedForm = new WuiForm('setenableform', array('action' => $formEventsCall->getEventsCallString()));
    $advancedForm->addChild($advancedVGroup);

    $tabHeaders[0]['label'] = $innomaticLocale->getStr('themes_title');
    $tabHeaders[1]['label'] = $innomaticLocale->getStr('serviceproviderframe_label');
    $tabHeaders[2]['label'] = $innomaticLocale->getStr('enabled_icons_label');
    $tabHeaders[3]['label'] = $innomaticLocale->getStr('advancedsettings_label');

    $tab = new WuiTab(
        'interface',
        array(
            'tabactionfunction' => 'interface_tab_action_builder',
            'activetab' => (isset($eventData['activetab']) ? $eventData['activetab'] : ''),
            'tabs' => $tabHeaders
        )
    );

    $tab->addChild(new WuiXml('page', array('definition' => $themesXmlDef)));
    $tab->addChild($serviceProviderForm);
    $tab->addChild($enableForm);
    $tab->addChild($advancedForm);

    $wuiMainFrame->addChild($tab);

    $wuiTitleBar->mTitle.= ' - '.$innomaticLocale->getStr('default_title');
}

$viewDispatcher->addEvent('localization', 'main_localization');
function main_localization($eventData)
{
    global $wuiMainFrame, $wuiTitleBar, $innomaticLocale, $actionDispatcher, $wuiMainStatus;

    $eventData = $actionDispatcher->getEventData();

    $countryLocale = new \Innomatic\Locale\LocaleCatalog(
        'innomatic::localization',
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLanguage()
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
            array(
                'label' => $innomaticLocale->getStr('country_label')
            )
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
                    )->getCountry()
                )
            )
        ),
        0, 1
    );
    $wuiLocaleGrid->addChild(
        new WuiSubmit(
            'submit1',
            array(
                'caption' => $innomaticLocale->getStr('country_submit')
            )
        ),
        0, 2
    );

    $wuiVGroup->addChild($wuiLocaleGrid);

    $formEventsCall = new \Innomatic\Wui\Dispatch\WuiEventsCall();
    $formEventsCall->addEvent(new \Innomatic\Wui\Dispatch\WuiEvent('action', 'setcountry', ''));
    $formEventsCall->addEvent(new \Innomatic\Wui\Dispatch\WuiEvent('view', 'localization', ''));

    $wuiForm = new WuiForm('countryform', array('action' => $formEventsCall->getEventsCallString()));
    $wuiForm->addChild($wuiVGroup);

    $wuiMainFrame->addChild($wuiForm);

    $locCountry = new \Innomatic\Locale\LocaleCountry(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCountry());
    $countryLanguage = $locCountry->Language();

    $languageLocale = new \Innomatic\Locale\LocaleCatalog(
        'innomatic::localization',
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLanguage()
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
                'default' => (
                    $selectedLanguage ? $selectedLanguage : \Innomatic\Core\InnomaticContainer::instance(
                        '\Innomatic\Core\InnomaticContainer'
                    )->getLanguage()
                )
            )
        ),
        0, 1
    );
    $wuiLocaleGrid->addChild(
        new WuiSubmit(
            'submit1',
            array(
                'caption' => $innomaticLocale->getStr('language_submit')
            )
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

    $formEventsCall = new \Innomatic\Wui\Dispatch\WuiEventsCall();
    $formEventsCall->addEvent(new \Innomatic\Wui\Dispatch\WuiEvent('action', 'setlanguage', ''));
    $formEventsCall->addEvent(new \Innomatic\Wui\Dispatch\WuiEvent('view', 'localization', ''));

    $wuiForm = new WuiForm('languageform', array('action' => $formEventsCall->getEventsCallString()));
    $wuiForm->addChild($wuiVGroup);

    $wuiMainFrame->addChild($wuiForm);

    $wuiTitleBar->mTitle.= ' - '.$innomaticLocale->getStr('country_title');
}

$viewDispatcher->addEvent('name', 'main_name');
function main_name($eventData)
{
    global $wuiMainFrame, $innomaticLocale, $wuiTitleBar, $actionDispatcher;

    if ($actionDispatcher->getEventName() == 'editname') {
        $pdData = $actionDispatcher->getEventData();

        $name = $pdData['name'];
        $domain = $pdData['domain'];
//    $innomaticcfg = new \Innomatic\Config\ConfigFile(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getConfigurationFile());
//    $name = $innomaticcfg->setValue('PlatformName', $eventData['name']);
//    $domain = $innomaticcfg->setValue('PlatformGroup', $eventData['domain']);
    } else {
        $name = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getPlatformName();
        $domain = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getPlatformGroup();
    }


    $wuiGrid = new WuiGrid('grid', array('rows' => '3', 'cols' => '2'));

    $wuiGrid->addChild(new WuiLabel('namelabel', array('label' => $innomaticLocale->getStr('namedesc'))), 0, 0);
    $wuiGrid->addChild(new WuiString('name', array('value' => $name, 'disp' => 'action')), 0, 1);

    $wuiGrid->addChild(new WuiLabel('domainlabel', array('label' => $innomaticLocale->getStr('domaindesc'))), 1, 0);
    $wuiGrid->addChild(new WuiString('domain', array('value' => $domain, 'disp' => 'action')), 1, 1);

    $wuiVGroup = new WuiVertgroup('vertgroup', array('align' => 'center'));
    $wuiVGroup->addChild($wuiGrid);
    $wuiVGroup->addChild(new WuiSubmit('submit', array('caption' => $innomaticLocale->getStr('submit'))));

    $formEventsCall = new \Innomatic\Wui\Dispatch\WuiEventsCall();
    $formEventsCall->addEvent(new \Innomatic\Wui\Dispatch\WuiEvent('view', 'name', ''));
    $formEventsCall->addEvent(new \Innomatic\Wui\Dispatch\WuiEvent('action', 'editname', ''));

    $form = new WuiForm('form', array('action' => $formEventsCall->getEventsCallString()));
    $form->addChild($wuiVGroup);
    $wuiMainFrame->addChild($form);

    $wuiTitleBar->mTitle .= ' - Edit';
    //$wui_mainframe->addChild( new WuiLabel( 'mainlabel', array( 'label' => 'Main page' ) ) );
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
                'node' => 'innomatic.root.interface.html#'.$eventData['node'],
                'language' => \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLanguage()
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
