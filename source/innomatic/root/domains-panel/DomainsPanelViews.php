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

class DomainsPanelViews extends \Innomatic\Desktop\Panel\PanelViews
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
        $this->_localeCatalog = new \Innomatic\Locale\LocaleCatalog(
            'innomatic::root_domains',
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLanguage()
        );

        $this->wuiContainer->loadWidget('innomatictoolbar');
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

        $this->wuiPage = new WuiPage('page', array('title' => $this->_localeCatalog->getStr('domains_title')));
        $this->wuiMainvertgroup = new WuiVertgroup('mainvertgroup');
        $this->wuiTitlebar = new WuiTitleBar(
            'titlebar',
            array(
                'title' => $this->_localeCatalog->getStr('domains_title'),
                'icon' => 'stack1')
            );
        $this->wuiMainvertgroup->addChild($this->wuiTitlebar);

        // Main tool bar
        //
        $wuiMainToolBar = new WuiToolBar('maintoolbar');

        $homeAction = new \Innomatic\Wui\Dispatch\WuiEventsCall();
        $homeAction->addEvent(new \Innomatic\Wui\Dispatch\WuiEvent('view', 'default', ''));
        $wuiHomeButton = new WuiButton(
            'homebutton',
            array(
                'label' => $this->_localeCatalog->getStr('domains_button'),
                'themeimage' => 'home',
                'horiz' => 'true', 'action' => $homeAction->getEventsCallString()
                ));
        $wuiMainToolBar->addChild($wuiHomeButton);

        if (
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getEdition() == \Innomatic\Core\InnomaticContainer::EDITION_ENTERPRISE
        ) {
            $domainQuery = \Innomatic\Core\InnomaticContainer::instance(
                '\Innomatic\Core\InnomaticContainer'
            )->getDataAccess()->execute('SELECT count(*) AS domains FROM domains');
        }

        if (\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')
            ->getEdition() == \Innomatic\Core\InnomaticContainer::EDITION_SAAS or !$domainQuery->getFields('domains') > 0) {
            $newAction = new \Innomatic\Wui\Dispatch\WuiEventsCall();
            $newAction->addEvent(new \Innomatic\Wui\Dispatch\WuiEvent('view', 'newdomain', ''));
            $wuiNewButton = new WuiButton(
                    'newbutton',
                    array(
                        'label' => $this->_localeCatalog->getStr('newdomain_button'),
                        'themeimage' => 'mathadd', 'horiz' => 'true',
                        'action' => $newAction->getEventsCallString()
                        ));
            $wuiMainToolBar->addChild($wuiNewButton);
        }

        // Situation tool bar
        //
        $wuiSitToolbar = new WuiToolBar('situation');
        $wuiSitButton = new WuiButton(
            'sitbutton',
            array(
                'label' => $this->_localeCatalog->getStr('situation.button'),
                'themeimage' => 'listdetailed',
                'horiz' => 'true',
                'action' => \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString(
                    '',
                    array(array('view', 'situation'))
                )
            )
        );
        $wuiSitToolbar->addChild($wuiSitButton);

        // Help tool bar
        //
        $wuiHelpToolbar = new WuiToolBar('helpbar');

        $mainDisp = new WuiDispatcher('view');
        $eventName = $mainDisp->getEventName();

        if (strcmp($eventName, 'help')) {
            $helpAction = new \Innomatic\Wui\Dispatch\WuiEventsCall();
            $helpAction->addEvent(new \Innomatic\Wui\Dispatch\WuiEvent('view', 'help', array('node' => $eventName)));
            $wuiHelpButton = new WuiButton(
                'helpbutton',
                array('label' => $this->_localeCatalog->getStr('help_button'),
                    'themeimage' => 'info',
                    'horiz' => 'true',
                    'action' => $helpAction->getEventsCallString()
                )
            );

            $wuiHelpToolbar->addChild($wuiHelpButton);
        }

        // Toolbar frame
        //
        $wuiToolbarFrame = new WuiHorizgroup('toolbarframe');

        $wuiToolbarFrame->addChild($wuiMainToolBar);
        $wuiToolbarFrame->addChild($wuiSitToolbar);
        $wuiToolbarFrame->addChild($wuiHelpToolbar);
        $this->wuiMainvertgroup->addChild($wuiToolbarFrame);

        $this->wuiMainframe = new WuiHorizframe('mainframe');
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

    public function viewDefault($eventData)
    {
        $query = \Innomatic\Core\InnomaticContainer::instance(
            '\Innomatic\Core\InnomaticContainer'
        )->getDataAccess()->execute('SELECT * FROM domains ORDER BY domainname');

        $applicationsQuery = \Innomatic\Core\InnomaticContainer::instance(
            '\Innomatic\Core\InnomaticContainer'
        )->getDataAccess()->execute(
            'SELECT id FROM applications WHERE onlyextension <> '
            .\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')
            ->getDataAccess()->formatText(
                \Innomatic\Core\InnomaticContainer::instance(
                    '\Innomatic\Core\InnomaticContainer'
                )->getDataAccess()->fmttrue
            )
        );

        if ($query->getNumberRows() > 0) {
            $headers[0]['label'] = $this->_localeCatalog->getStr('status_header');
            $headers[1]['label'] = $this->_localeCatalog->getStr('domainid_header');
            $headers[2]['label'] = $this->_localeCatalog->getStr('domainname_header');
            $headers[3]['label'] = $this->_localeCatalog->getStr('domaincreationdate_header');

            $row = 0;

            $wuiDomainsTable = new WuiTable(
                'domainstable',
                array(
                    'headers' => $headers,
                    'rowsperpage' => '10',
                    'pagesactionfunction' => 'domains_list_action_builder',
                    'pagenumber' => (is_array($eventData) and
                                     isset($eventData['domainspage'])) ? $eventData['domainspage'] : '')
                );

            while (!$query->eof) {
                $data = $query->getFields();
                if (
                    $data['domainactive'] == \Innomatic\Core\InnomaticContainer::instance(
                        '\Innomatic\Core\InnomaticContainer'
                    )->getDataAccess()->fmttrue
                )
                $wuiDomainsTable->addChild(
                    new WuiImage(
                        'status'.$row, array('imageurl' => $this->wuiMainframe->mThemeHandler->mStyle['greenball'])),
                    $row, 0
                );
                else
                $wuiDomainsTable->addChild(
                    new WuiImage(
                        'status'.$row,
                        array(
                            'imageurl' => $this->wuiMainframe->mThemeHandler->mStyle['redball'])
                        ),
                    $row, 0
                );

                $wuiDomainsTable->addChild(
                    new WuiLabel(
                        'domainidlabel'.$row,
                        array(
                            'label' => $data['domainid']
                            )
                        ),
                    $row, 1
                );
                $wuiDomainsTable->addChild(
                    new WuiLabel(
                        'domainnamelabel'.$row,
                        array(
                            'label' => $data['domainname']
                            )
                        ),
                    $row, 2
                );
                $wuiDomainsTable->addChild(
                    new WuiLabel(
                        'domaincreationdate'.$row,
                        array(
                            'label' => $data['domaincreationdate']
                            )
                        ),
                    $row, 3
                );
                //$wui_domains_table->addChild(
                //  new WuiLabel( 'domainlabel'.$row, array( 'label' => $data['domainid'] ) ), $row, 4 );

                //$wui_buttons = new WuiHorizgroup( 'buttons'.$row );

                $wuiDomainToolBar[$row] = new WuiHorizgroup('domaintoolbar'.$row);

                $showAction[$row] = new \Innomatic\Wui\Dispatch\WuiEventsCall();
                $showAction[$row]->addEvent(
                    new \Innomatic\Wui\Dispatch\WuiEvent(
                        'view',
                        'showdomainconfig',
                        array(
                            'domainid' => $data['id']
                        )
                    )
                );
                $wuiShowButton[$row] = new WuiButton(
                    'showbutton'.$row,
                    array(
                        'label' => $this->_localeCatalog->getStr('showconfig_label'),
                        'themeimage' => 'zoom',
                        'action' => $showAction[$row]->getEventsCallString()
                    )
                );
                $wuiDomainToolBar[$row]->addChild($wuiShowButton[$row]);

                $editAction[$row] = new \Innomatic\Wui\Dispatch\WuiEventsCall();
                $editAction[$row]->addEvent(
                    new \Innomatic\Wui\Dispatch\WuiEvent(
                        'view',
                        'editdomainconfig',
                        array(
                            'domainid' => $data['id']
                        )
                    )
                );
                $wuiEditButton[$row] = new WuiButton(
                    'editbutton'.$row,
                    array(
                        'label' => $this->_localeCatalog->getStr('editconfig_label'),
                        'themeimage' => 'documenttext',
                        'action' => $editAction[$row]->getEventsCallString()
                    )
                );
                $wuiDomainToolBar[$row]->addChild($wuiEditButton[$row]);

                $notesAction[$row] = new \Innomatic\Wui\Dispatch\WuiEventsCall();
                $notesAction[$row]->addEvent(
                    new \Innomatic\Wui\Dispatch\WuiEvent(
                        'view',
                        'editdomainnotes',
                        array(
                            'domainid' => $data['id']
                        )
                    )
                );
                $wuiNotesButton[$row] = new WuiButton(
                    'notesbutton'.$row,
                    array(
                        'label' => $this->_localeCatalog->getStr('domainnotes_label'),
                        'themeimage' => 'attach',
                        'action' => $notesAction[$row]->getEventsCallString()
                    )
                );
                $wuiDomainToolBar[$row]->addChild($wuiNotesButton[$row]);

                if (
                    $data['domainactive'] == \Innomatic\Core\InnomaticContainer::instance(
                        '\Innomatic\Core\InnomaticContainer'
                    )->getDataAccess()->fmttrue
                ) {
                    $accessAction[$row] = new \Innomatic\Wui\Dispatch\WuiEventsCall();
                    $accessAction[$row]->addEvent(
                        new \Innomatic\Wui\Dispatch\WuiEvent(
                            'view',
                            'accessdomain',
                            array(
                                'domainid' => $data['id']
                            )
                        )
                    );
                    $wuiAccessButton[$row] = new WuiButton(
                        'accessbutton'.$row,
                        array(
                            'label' => $this->_localeCatalog->getStr('accessdomain_label'),
                            'themeimage' => 'home',
                            'action' => $accessAction[$row]->getEventsCallString()
                        )
                    );
                    $wuiDomainToolBar[$row]->addChild($wuiAccessButton[$row]);

                    if (strlen($data['webappurl'])) {
                        $domainWebappUrl = $data['webappurl'];
                    } else {
                        $base = dirname(
                            dirname(
                                dirname(
                                    \Innomatic\Webapp\WebAppContainer::instance(
                                        '\Innomatic\Webapp\WebAppContainer'
                                    )->getProcessor()->getRequest()->getRequestUri()
                                )
                            )
                        );
                        $domainWebappUrl = $base.'/'.$data['domainid'];
                    }
                    $wuiWebappButton[$row] = new WuiButton(
                        'webappbutton'.$row,
                        array(
                            'label' => $this->_localeCatalog->getStr('webapp_label'),
                            'themeimage' => 'globe2',
                            'action' => $domainWebappUrl,
                            'target' => '_blank'
                        )
                    );
                    $wuiDomainToolBar[$row]->addChild($wuiWebappButton[$row]);
                }

                if (
                    $data['domainactive'] == \Innomatic\Core\InnomaticContainer::instance(
                        '\Innomatic\Core\InnomaticContainer'
                    )->getDataAccess()->fmttrue
                ) {
                    $disableAction[$row] = new \Innomatic\Wui\Dispatch\WuiEventsCall();
                    $disableAction[$row]->addEvent(
                        new \Innomatic\Wui\Dispatch\WuiEvent(
                            'view',
                            'default', ''
                        )
                    );
                    $disableAction[$row]->addEvent(
                        new \Innomatic\Wui\Dispatch\WuiEvent(
                            'action',
                            'disabledomain',
                            array(
                                'domainid' => $data['domainid']
                            )
                        )
                    );
                    $wuiSisableButton[$row] = new WuiButton(
                        'disablebutton'.$row,
                        array(
                            'label' => $this->_localeCatalog->getStr('disabledomain_label'),
                            'themeimage' => 'lock',
                            'action' => $disableAction[$row]->getEventsCallString()
                        )
                    );
                    $wuiDomainToolBar[$row]->addChild($wuiSisableButton[$row]);
                } else {
                    $enableAction[$row] = new \Innomatic\Wui\Dispatch\WuiEventsCall();
                    $enableAction[$row]->addEvent(
                        new \Innomatic\Wui\Dispatch\WuiEvent(
                            'view',
                            'default',
                            ''
                        )
                    );
                    $enableAction[$row]->addEvent(
                        new \Innomatic\Wui\Dispatch\WuiEvent(
                            'action',
                            'enabledomain',
                            array(
                                'domainid' => $data['domainid']
                            )
                        )
                    );
                    $wuiEnableButton[$row] = new WuiButton(
                        'enablebutton'.$row,
                        array(
                            'label' => $this->_localeCatalog->getStr('enabledomain_label'),
                            'themeimage' => 'unlock',
                            'action' => $enableAction[$row]->getEventsCallString()
                        )
                    );
                    $wuiDomainToolBar[$row]->addChild($wuiEnableButton[$row]);
                }

                $removeAction[$row] = new \Innomatic\Wui\Dispatch\WuiEventsCall();
                $removeAction[$row]->addEvent(
                    new \Innomatic\Wui\Dispatch\WuiEvent(
                        'view',
                        'default',
                        ''
                    )
                );
                $removeAction[$row]->addEvent(
                    new \Innomatic\Wui\Dispatch\WuiEvent(
                        'action',
                        'removedomain',
                        array(
                            'domainid' => $data['domainid']
                        )
                    )
                );
                $wuiRemoveButton[$row] = new WuiButton(
                    'removebutton'.$row,
                    array(
                        'label' => $this->_localeCatalog->getStr('removedomain_label'),
                        'themeimage' => 'trash',
                        'action' => $removeAction[$row]->getEventsCallString(),
                        'needconfirm' => 'true',
                        'confirmmessage' => sprintf(
                            $this->_localeCatalog->getStr('removedomainquestion_label'),
                            $data['domainid'].' ('.$data['domainname'].')'
                        )
                    )
                );
                $wuiDomainToolBar[$row]->addChild($wuiRemoveButton[$row]);

                if ($applicationsQuery->getNumberRows()) {
                    $applicationsAction[$row] = new \Innomatic\Wui\Dispatch\WuiEventsCall();
                    $applicationsAction[$row]->addEvent(
                        new \Innomatic\Wui\Dispatch\WuiEvent(
                            'view',
                            'domainapplications',
                            array(
                                'domainid' => $data['id']
                            )
                        )
                    );
                    $wuiApplicationsButton[$row] = new WuiButton(
                        'applicationsbutton'.$row,
                        array(
                            'label' => $this->_localeCatalog->getStr('domainapplications_label'),
                            'themeimage' => 'listicons',
                            'action' => $applicationsAction[$row]->getEventsCallString()
                        )
                    );
                    $wuiDomainToolBar[$row]->addChild($wuiApplicationsButton[$row]);
                }

                if (
                    file_exists(
                        \Innomatic\Core\InnomaticContainer::instance(
                            '\Innomatic\Core\InnomaticContainer'
                        )->getHome().'core/domains/'.$data['domainid'].'/log/domain.log'
                    )
                ) {
                    $logAction[$row] = new \Innomatic\Wui\Dispatch\WuiEventsCall();
                    $logAction[$row]->addEvent(
                        new \Innomatic\Wui\Dispatch\WuiEvent(
                            'view',
                            'showdomainlog',
                            array(
                                'domainid' => $data['id']
                            )
                        )
                    );
                    $wuiLogButton[$row] = new WuiButton(
                        'logbutton'.$row,
                        array(
                            'label' => $this->_localeCatalog->getStr('domainlog_label'),
                            'themeimage' => 'alignjustify',
                            'action' => $logAction[$row]->getEventsCallString()
                        )
                    );
                    $wuiDomainToolBar[$row]->addChild($wuiLogButton[$row]);
                }

                if (
                    file_exists(
                        \Innomatic\Core\InnomaticContainer::instance(
                            '\Innomatic\Core\InnomaticContainer'
                        )->getHome().'core/domains/'.$data['domainid'].'/log/dataaccess.log'
                    )
                ) {
                    $dblogAction[$row] = new \Innomatic\Wui\Dispatch\WuiEventsCall();
                    $dblogAction[$row]->addEvent(
                        new \Innomatic\Wui\Dispatch\WuiEvent(
                            'view',
                            'showdataaccesslog',
                            array(
                                'domainid' => $data['id']
                            )
                        )
                    );
                    $wuiDblogButton[$row] = new WuiButton(
                        'dblogbutton'.$row,
                        array(
                            'label' => $this->_localeCatalog->getStr('dataaccesslog_label'),
                            'themeimage' => 'alignjustify',
                            'action' => $dblogAction[$row]->getEventsCallString()
                        )
                    );
                    $wuiDomainToolBar[$row]->addChild($wuiDblogButton[$row]);
                }

                $wuiDomainsTable->addChild($wuiDomainToolBar[$row], $row, 4);

                $row ++;
                $query->moveNext();
            }

            $this->wuiMainframe->addChild($wuiDomainsTable);
        } else
        $this->wuiMainstatus->mArgs['status'] = $this->_localeCatalog->getStr('no_available_domains_status');
    }

    public function viewNewDomain($eventData)
    {
        $dbtypes = \Innomatic\Dataaccess\DataAccessFactory::getDrivers();

        // Retrieves the list of available webapp skeletons.
        $skeletonsQuery = \Innomatic\Core\InnomaticContainer::instance(
            '\Innomatic\Core\InnomaticContainer'
        )->getDataAccess()->execute('SELECT name,catalog FROM webapps_skeletons');

        $skeletons = array();

        while (!$skeletonsQuery->eof) {
            $tmpLocale = new \Innomatic\Locale\LocaleCatalog(
            $skeletonsQuery->getFields('catalog'),
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLanguage());
            $skeletons[$skeletonsQuery->getFields('name')] = $tmpLocale->getStr($skeletonsQuery->getFields('name'));
            $skeletonsQuery->moveNext();
        }
        asort($skeletons);

        $wuiVgroup = new WuiVertgroup('vgroup');

        $wuiDomainGrid = new WuiGrid('newdomaingrid');

        $tabIndex = 1;

        // Domain fields
        //
        $wuiDomainGrid->addChild(
            new WuiLabel(
                'basedatalabel',
                array(
                    'label' => $this->_localeCatalog->getStr('domain_base_data'),
                    'bold' => 'true'
                     )
                ),
            0, 0
        );

        $wuiDomainGrid->addChild(
            new WuiLabel(
                'namelabel',
                array(
                    'label' => $this->_localeCatalog->getStr('domainname_label').' (*)'
                    )
                ),
            1, 0
        );
        $wuiDomainGrid->addChild(
            new WuiString(
                'domainname',
                array(
                    'disp' => 'action',
                    'checkmessage' => $this->_localeCatalog->getStr('domainname_label'),
                    'required' => 'true',
                    'tabindex' => $tabIndex ++
                    )
                ),
            1, 1
        );

        $wuiDomainGrid->addChild(
            new WuiLabel(
                'idlabel',
                array(
                    'label' => $this->_localeCatalog->getStr('domainid_label').' (*)'
                    )
                ),
            2, 0
        );
        $wuiDomainGrid->addChild(
            new WuiString(
                'domainid',
                array(
                    'disp' => 'action',
                    'checkmessage' => $this->_localeCatalog->getStr('domainid_label'),
                    'required' => 'true',
                    'tabindex' => $tabIndex ++
                    )
                ),
            2, 1
        );

        $wuiDomainGrid->addChild(
            new WuiLabel(
                'passwordlabel',
                array(
                    'label' => $this->_localeCatalog->getStr('domainpassword_label').' (*)'
                    )
                ),
            3, 0
        );
        $wuiDomainGrid->addChild(
            new WuiString(
                'domainpassword',
                array(
                    'disp' => 'action',
                    'checkmessage' => $this->_localeCatalog->getStr('domainpassword_label'),
                    'required' => 'true',
                    'tabindex' => $tabIndex ++, 'password' => 'true'
                    )
                ),
            3, 1
        );

        $wuiDomainGrid->addChild(
            new WuiLabel(
                'maxuserslabel',
                array(
                    'label' => $this->_localeCatalog->getStr('maxusers_label')
                    )
                ),
            4, 0
        );
        $wuiDomainGrid->addChild(
            new WuiString(
                'maxusers',
                array(
                    'disp' => 'action',
                    'tabindex' => $tabIndex ++
                    )
                ),
            4, 1
        );
        $wuiDomainGrid->addChild(
            new WuiLabel(
                'webappdatalabel',
                array(
                    'label' => $this->_localeCatalog->getStr('webapp_data'),
                    'bold' => 'true'
                    )
                ),
            5, 0
        );
        $wuiDomainGrid->addChild(
            new WuiLabel(
                'webappskeletonlabel',
                array(
                    'label' => $this->_localeCatalog->getStr('webappskeleton_label')
                    )
                ),
            6, 0
        );
        $wuiDomainGrid->addChild(
            new WuiComboBox(
                'webappskeleton',
                array(
                    'disp' => 'action',
                    'tabindex' => $tabIndex++,
                    'elements' => $skeletons,
                    'default' => 'default'
                    )
                ),
            6, 1
        );
        $wuiDomainGrid->addChild(
            new WuiLabel(
                'urllabel',
                array(
                    'label' => $this->_localeCatalog->getStr('webappurl_label')
                    )
                ),
            7, 0
        );
        $wuiDomainGrid->addChild(
            new WuiString(
                'webappurl',
                array(
                    'disp' => 'action',
                    'tabindex' => $tabIndex ++
                    )
                ),
            7, 1
        );

        if (\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getEdition() == \Innomatic\Core\InnomaticContainer::EDITION_SAAS) {
            // Database fields
            //
            $wuiDomainGrid->addChild(
                new WuiLabel(
                    'databasedatalabel',
                    array(
                        'label' => $this->_localeCatalog->getStr('database_data'),
                        'bold' => 'true'
                        )
                    ),
                0, 2
            );

            $wuiDomainGrid->addChild(
                new WuiLabel(
                    'dbtypelabel',
                    array(
                        'label' => $this->_localeCatalog->getStr('dataaccesstype_label')
                        )
                    ),
                1, 2
            );
            $wuiDomainGrid->addChild(
                new WuiComboBox(
                    'dataaccesstype',
                    array(
                        'disp' => 'action',
                        'tabindex' => $tabIndex ++,
                        'elements' => $dbtypes,
                        'default' => \Innomatic\Core\InnomaticContainer::instance(
                            '\Innomatic\Core\InnomaticContainer'
                        )->getConfig()->value('RootDatabaseType')
                        )
                    ),
                1, 3
            );

            $wuiDomainGrid->addChild(
                new WuiLabel(
                    'dbnamelabel',
                    array(
                        'label' => $this->_localeCatalog->getStr('domaindaname_label').' (**)'
                        )
                    ),
                2, 2
            );
            $wuiDomainGrid->addChild(
                new WuiString(
                    'domaindaname',
                    array(
                        'disp' => 'action',
                        'tabindex' => $tabIndex ++
                        )
                    ),
                2, 3
            );

            $wuiDomainGrid->addChild(
                new WuiLabel(
                    'dbhostlabel',
                    array(
                        'label' => $this->_localeCatalog->getStr('dataaccesshost_label').' (**)'
                        )
                    ),
                3, 2
            );
            $wuiDomainGrid->addChild(
                new WuiString(
                    'dataaccesshost',
                    array(
                        'disp' => 'action',
                        'tabindex' => $tabIndex ++
                        )
                    ),
                3, 3
            );

            $wuiDomainGrid->addChild(
                new WuiLabel(
                    'dbportlabel',
                    array(
                        'label' => $this->_localeCatalog->getStr('dataaccessport_label').' (**)'
                        )
                    ),
                4, 2
            );
            $wuiDomainGrid->addChild(
                new WuiString(
                    'dataaccessport',
                    array(
                        'disp' => 'action',
                        'tabindex' => $tabIndex ++
                        )
                    ),
                4, 3
            );

            $wuiDomainGrid->addChild(
                new WuiLabel(
                    'dbuserlabel',
                    array(
                        'label' => $this->_localeCatalog->getStr('dataaccessuser_label').' (**)'
                        )
                    ),
                5, 2
            );
            $wuiDomainGrid->addChild(
                new WuiString(
                    'dataaccessuser',
                    array(
                        'disp' => 'action',
                        'tabindex' => $tabIndex ++
                        )
                    ),
                5, 3
            );

            $wuiDomainGrid->addChild(
                new WuiLabel(
                    'dbpasswordlabel',
                    array(
                        'label' => $this->_localeCatalog->getStr('dataaccesspassword_label').' (**)'
                        )
                    ),
                6, 2
            );
            $wuiDomainGrid->addChild(
                new WuiString(
                    'dataaccesspassword',
                    array(
                        'disp' => 'action',
                        'tabindex' => $tabIndex ++
                        )
                    ),
                6, 3
            );

            $wuiDomainGrid->addChild(
                new WuiLabel(
                    'createdblabel',
                    array(
                        'label' => $this->_localeCatalog->getStr('createdb_label').' (***)'
                        )
                    ),
                7, 2
            );
            $wuiDomainGrid->addChild(
                new WuiCheckBox(
                    'createdomainda',
                    array(
                        'disp' => 'action',
                        'tabindex' => $tabIndex ++,
                        'checked' => 'true'
                        )
                    ),
                7, 3
            );
        }

        $wuiVgroup->addChild($wuiDomainGrid);

        $formEventsCall = new \Innomatic\Wui\Dispatch\WuiEventsCall();
        $formEventsCall->addEvent(new \Innomatic\Wui\Dispatch\WuiEvent('action', 'createdomain', ''));
        $formEventsCall->addEvent(new \Innomatic\Wui\Dispatch\WuiEvent('view', 'default', ''));

        //$wuiVgroup->addChild(
        //  new WuiSubmit('submit1', array( 'caption' => $this->localeCatalog
        //  ->getStr( 'createdomain_submit' ), 'tabindex' => $tabIndex++ ) ) );
        $wuiVgroup->addChild(
            new WuiButton(
                'submit1',
                array(
                    'label' => $this->_localeCatalog->getStr('createdomain_submit'),
                    'themeimage' => 'buttonok',
                    'horiz' => 'true',
                    'formsubmit' => 'newdomainform',
                    'formcheckmessage' => $this->_localeCatalog->getStr('newdomain_formcheck.message'),
                    'action' => $formEventsCall->getEventsCallString()
                )
            )
        );

        $wuiVgroup->addChild(new WuiHorizBar('horizbar1'));
        $wuiVgroup->addChild(
            new WuiLabel(
                'reqfieldslabel',
                array(
                    'label' => $this->_localeCatalog->getStr('requiredfields_label')
                )
            )
        );
        $wuiVgroup->addChild(
            new WuiLabel(
                'dbparamsnotelabel',
                array(
                    'label' => $this->_localeCatalog->getStr('dbparamsnote_label')
                )
            )
        );
        $wuiVgroup->addChild(
            new WuiLabel(
                'dbcreatenotelabel',
                array(
                    'label' => $this->_localeCatalog->getStr('createdbnote_label')
                )
            )
        );

        $wuiForm = new WuiForm('newdomainform', array('action' => $formEventsCall->getEventsCallString()));
        $wuiForm->addChild($wuiVgroup);

        $this->wuiMainframe->addChild($wuiForm);

        $this->wuiTitlebar->mArgs['title'].= ' - '.$this->_localeCatalog->getStr('newdomain_title');
    }

    public function viewEditDomainConfig($eventData)
    {
        $dbtypes = \Innomatic\Dataaccess\DataAccessFactory::getDrivers();

        $query = \Innomatic\Core\InnomaticContainer::instance(
            '\Innomatic\Core\InnomaticContainer'
        )->getDataAccess()->execute('SELECT * FROM domains WHERE id='.$eventData['domainid'].' ORDER BY domainname');

        if ($query->getNumberRows()) {
            $domainData = $query->getFields();

            // Retrieves the list of available webapp skeletons.
            $skeletonsQuery = \Innomatic\Core\InnomaticContainer::instance(
                '\Innomatic\Core\InnomaticContainer'
            )->getDataAccess()->execute('SELECT name,catalog FROM webapps_skeletons');

            $skeletons = array();

            while (!$skeletonsQuery->eof) {
                $tmpLocale = new \Innomatic\Locale\LocaleCatalog(
                $skeletonsQuery->getFields('catalog'),
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLanguage());
                $skeletons[$skeletonsQuery->getFields('name')] = $tmpLocale->getStr(
                    $skeletonsQuery->getFields('name')
                );
                $skeletonsQuery->moveNext();
            }
            asort($skeletons);

            $wuiVgroup = new WuiVertgroup('vgroup');
            $wuiDomainGrid = new WuiGrid('newdomaingrid', array('rows' => '6', 'cols' => '4'));
            $tabIndex = 1;

            // Domain fields
            //
            $wuiDomainGrid->addChild(
                new WuiLabel(
                    'basedatalabel',
                    array(
                        'label' => $this->_localeCatalog->getStr('domain_base_data'),
                        'bold' => 'true'
                    )
                ),
                0, 0
            );

            $wuiDomainGrid->addChild(
                new WuiLabel(
                    'namelabel',
                    array(
                        'label' => $this->_localeCatalog->getStr('domainname_label').' (*)'
                    )
                ),
                1, 0
            );
            $wuiDomainGrid->addChild(
                new WuiString(
                    'domainname',
                    array(
                        'disp' => 'action',
                        'checkmessage' => $this->_localeCatalog->getStr('domainname_label'),
                        'required' => 'true',
                        'tabindex' => $tabIndex ++,
                        'value' => $domainData['domainname']
                    )
                ),
                1, 1
            );

            $wuiDomainGrid->addChild(
                new WuiLabel(
                    'idlabel',
                    array(
                        'label' => $this->_localeCatalog->getStr('domainid_label')
                    )
                ),
                2, 0
            );
            $wuiDomainGrid->addChild(
                new WuiString(
                    'domainid',
                    array(
                        'disp' => 'action',
                        'tabindex' => $tabIndex ++,
                        'readonly' => 'true',
                        'value' => $domainData['domainid']
                    )
                ),
                2, 1
            );

            $wuiDomainGrid->addChild(
                new WuiLabel(
                    'passwordlabel',
                    array(
                        'label' => $this->_localeCatalog->getStr('domainpassword_label').' (*)'
                    )
                ),
                3, 0
            );
            $wuiDomainGrid->addChild(
                new WuiString(
                    'domainpassword',
                    array(
                        'disp' => 'action',
                        'tabindex' => $tabIndex ++,
                        'password' => 'true'
                    )
                ),
                3, 1
            );

            $wuiDomainGrid->addChild(
                new WuiLabel(
                    'maxuserslabel',
                    array(
                        'label' => $this->_localeCatalog->getStr('maxusers_label')
                    )
                ),
                4, 0
            );
            $wuiDomainGrid->addChild(
                new WuiString(
                    'maxusers',
                    array(
                        'disp' => 'action',
                        'tabindex' => $tabIndex ++,
                        'value' => $domainData['maxusers']
                    )
                ),
                4, 1
            );

            $wuiDomainGrid->addChild(
                new WuiLabel(
                    'basedatalabel',
                    array(
                        'label' => $this->_localeCatalog->getStr('webapp_data'),
                        'bold' => 'true'
                    )
                ),
                5, 0
            );

            $wuiDomainGrid->addChild(
                new WuiLabel(
                    'webappskeletonlabel',
                    array(
                        'label' => $this->_localeCatalog->getStr('webappskeleton_label')
                    )
                ),
                6, 0
            );
            $wuiDomainGrid->addChild(
                new WuiComboBox(
                    'webappskeleton',
                    array(
                        'disp' => 'action',
                        'tabindex' => $tabIndex ++,
                        'elements' => $skeletons,
                        'default' => $domainData['webappskeleton']
                    )
                ),
                6, 1
            );

            $wuiDomainGrid->addChild(
                new WuiLabel(
                    'urllabel',
                    array(
                        'label' => $this->_localeCatalog->getStr('webappurl_label')
                    )
                ),
                7, 0
            );
            $wuiDomainGrid->addChild(
                new WuiString(
                    'webappurl',
                    array(
                        'disp' => 'action',
                        'tabindex' => $tabIndex ++,
                        'value' => $domainData['webappurl']
                    )
                ),
                7, 1
            );

            if (\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getEdition() == \Innomatic\Core\InnomaticContainer::EDITION_SAAS) {
                // Database fields
                //
                $wuiDomainGrid->addChild(
                    new WuiLabel(
                        'basedatalabel',
                        array(
                            'label' => $this->_localeCatalog->getStr('database_data'),
                            'bold' => 'true'
                        )
                    ),
                    0, 2
                );

                $wuiDomainGrid->addChild(
                    new WuiLabel(
                        'dbtypelabel',
                        array(
                            'label' => $this->_localeCatalog->getStr('dataaccesstype_label')
                        )
                    ),
                    1, 2
                );
                $wuiDomainGrid->addChild(
                    new WuiComboBox(
                        'dataaccesstype',
                        array(
                            'disp' => 'action',
                            'tabindex' => $tabIndex ++,
                            'elements' => $dbtypes,
                            'default' => $dbtypes[$domainData['dataaccesstype']]
                        )
                    ),
                    1, 3
                );

                $wuiDomainGrid->addChild(
                    new WuiLabel(
                        'dbnamelabel',
                        array(
                            'label' => $this->_localeCatalog->getStr('domaindaname_label')
                        )
                    ),
                    2, 2
                );
                $wuiDomainGrid->addChild(
                    new WuiString(
                        'domaindaname',
                        array(
                            'disp' => 'action',
                            'tabindex' => $tabIndex ++,
                            'value' => $domainData['domaindaname']
                        )
                    ),
                    2, 3
                );

                $wuiDomainGrid->addChild(
                    new WuiLabel(
                        'dbhostlabel',
                        array(
                            'label' => $this->_localeCatalog->getStr('dataaccesshost_label')
                        )
                    ),
                    3, 2
                );
                $wuiDomainGrid->addChild(
                    new WuiString(
                        'dataaccesshost',
                        array(
                            'disp' => 'action',
                            'tabindex' => $tabIndex ++,
                            'value' => $domainData['dataaccesshost']
                        )
                    ),
                    3, 3
                );

                $wuiDomainGrid->addChild(
                    new WuiLabel(
                        'dbportlabel',
                        array(
                            'label' => $this->_localeCatalog->getStr('dataaccessport_label')
                        )
                    ),
                    4, 2
                );
                $wuiDomainGrid->addChild(
                    new WuiString(
                        'dataaccessport',
                        array(
                            'disp' => 'action',
                            'tabindex' => $tabIndex ++,
                            'value' => $domainData['dataaccessport']
                        )
                    ),
                    4, 3
                );

                $wuiDomainGrid->addChild(
                    new WuiLabel(
                        'dbuserlabel',
                        array(
                            'label' => $this->_localeCatalog->getStr('dataaccessuser_label')
                        )
                    ),
                    5, 2
                );
                $wuiDomainGrid->addChild(
                    new WuiString(
                        'dataaccessuser',
                        array(
                            'disp' => 'action',
                            'tabindex' => $tabIndex ++,
                            'value' => $domainData['dataaccessuser']
                        )
                    ),
                    5, 3
                );

                $wuiDomainGrid->addChild(
                    new WuiLabel(
                        'dbpasswordlabel',
                        array(
                            'label' => $this->_localeCatalog->getStr('dataaccesspassword_label')
                        )
                    ),
                    6, 2
                );
                $wuiDomainGrid->addChild(
                    new WuiString(
                        'dataaccesspassword',
                        array(
                            'disp' => 'action',
                            'tabindex' => $tabIndex ++,
                            'value' => $domainData['dataaccesspassword']
                        )
                    ),
                    6, 3
                );
            }

            $wuiVgroup->addChild($wuiDomainGrid);

            $formEventsCall = new \Innomatic\Wui\Dispatch\WuiEventsCall();
            $formEventsCall->addEvent(
                new \Innomatic\Wui\Dispatch\WuiEvent(
                    'action',
                    'updatedomain',
                    array(
                        'domainserial' => $domainData['id']
                    )
                )
            );
            $formEventsCall->addEvent(new \Innomatic\Wui\Dispatch\WuiEvent('view', 'default', ''));

            $wuiVgroup->addChild(
                new WuiButton(
                    'submit1',
                    array(
                        'label' => $this->_localeCatalog->getStr('editdomain_submit'),
                        'themeimage' => 'buttonok',
                        'horiz' => 'true',
                        'formsubmit' => 'editdomainform',
                        'formcheckmessage' => $this->_localeCatalog->getStr('editdomain_formcheck.message'),
                        'action' => $formEventsCall->getEventsCallString()
                    )
                )
            );

            $wuiVgroup->addChild(new WuiHorizBar('horizbar1'));
            $wuiVgroup->addChild(
                new WuiLabel(
                    'reqfieldslabel',
                    array(
                        'label' => $this->_localeCatalog->getStr('requiredfields_label')
                    )
                )
            );

            $wuiForm = new WuiForm('editdomainform', array('action' => $formEventsCall->getEventsCallString()));
            $wuiForm->addChild($wuiVgroup);

            $this->wuiMainframe->addChild($wuiForm);
        }

        $this->wuiTitlebar->mArgs['title'].= ' - '.$this->_localeCatalog->getStr('editdomainconfig_title');
    }

    public function viewEditDomainNotes($eventData)
    {
        $domainQuery = \Innomatic\Core\InnomaticContainer::instance(
            '\Innomatic\Core\InnomaticContainer'
        )->getDataAccess()->execute(
            'SELECT domainid,domainname,notes FROM domains WHERE id='.$eventData['domainid']
        );
        $domainData = $domainQuery->getFields();

        $xmlDef = '<vertgroup>
      <name>notes</name>
      <children>
        <label>
          <name>notes</name>
          <args>
            <bold>true</bold>
            <label type="encoded">'.urlencode($this->_localeCatalog->getStr('domainnotes_text.label')).'</label>
          </args>
        </label>
        <form>
          <name>notes</name>
          <args>
            <method>post</method>
            <action type="encoded">'
            .urlencode(
                \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString(
                    '',
                    array(
                        array('view', 'default', ''),
                        array(
                            'action',
                            'editdomainnotes',
                            array('domainid' => $domainData['domainid'])
                        )
                    )
                )
            )
            .'</action>
          </args>
          <children>
            <text>
              <name>notes</name>
              <args>
                <disp>action</disp>
                <cols>80</cols>
                <rows>10</rows>
                <value type="encoded">'.urlencode($domainData['notes']).'</value>
              </args>
            </text>
          </children>
        </form>
        <horizbar>
          <name>hb</name>
        </horizbar>
        <button>
          <name>apply</name>
          <args>
            <horiz>true</horiz>
            <frame>false</frame>
            <formsubmit>notes</formsubmit>
            <action type="encoded">'
            .urlencode(
                \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString(
                    '',
                    array(
                        array('view', 'default', ''),
                        array(
                            'action',
                            'editdomainnotes',
                            array('domainid' => $domainData['domainid'])
                        )
                    )
                )
            )
            .'</action>
            <label type="encoded">'.urlencode($this->_localeCatalog->getStr('notes_apply.submit')).'</label>
            <themeimage>buttonok</themeimage>
          </args>
        </button>
      </children>
    </vertgroup>';

        $this->wuiMainframe->addChild(new WuiXml('page', array('definition' => $xmlDef)));

        $this->wuiTitlebar->mArgs['title'].= ' - '
        .$domainData['domainid'].' ('.$domainData['domainname'].') - '
        .$this->_localeCatalog->getStr('domainnotes.title');
    }

    public function viewShowDomainConfig($eventData)
    {
        $dbtypes = \Innomatic\Dataaccess\DataAccessFactory::getDrivers();

        $query = \Innomatic\Core\InnomaticContainer::instance(
            '\Innomatic\Core\InnomaticContainer'
        )->getDataAccess()->execute('SELECT * FROM domains WHERE id='.$eventData['domainid']);

        if ($query->getNumberRows()) {
            $domainData = $query->getFields();

            // Retrieves the webapp skeleton catalog and localized name.
            $skeletonsQuery = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->execute(
                'SELECT catalog FROM webapps_skeletons '.
                'WHERE name='.\Innomatic\Core\InnomaticContainer::instance(
                    '\Innomatic\Core\InnomaticContainer'
                )->getDataAccess()->formatText($domainData['webappskeleton'])
            );
            $tmpLocale = new \Innomatic\Locale\LocaleCatalog(
            $skeletonsQuery->getFields('catalog'),
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLanguage());
            $skeletonName = $tmpLocale->getStr($domainData['webappskeleton']);

            $wuiVGroup = new WuiVertgroup('vgroup');
            $wuiDomainGrid = new WuiGrid('showdomaingrid', array('rows' => '6', 'cols' => '4'));

            // Domain fields
            //
            $wuiDomainGrid->addChild(
                new WuiLabel(
                    'basedatalabel',
                    array(
                        'label' => $this->_localeCatalog->getStr('domain_base_data'),
                        'bold' => 'true'
                    )
                ),
                0, 0
            );

            $wuiDomainGrid->addChild(
                new WuiLabel(
                    'namelabel',
                    array(
                        'label' => $this->_localeCatalog->getStr('domainname_label')
                    )
                ),
                1, 0
            );
            $wuiDomainGrid->addChild(
                new WuiString(
                    'domainname',
                    array(
                        'disp' => 'action',
                        'readonly' => 'true',
                        'value' => $domainData['domainname']
                    )
                ),
                1, 1
            );

            $wuiDomainGrid->addChild(
                new WuiLabel(
                    'idlabel',
                    array(
                        'label' => $this->_localeCatalog->getStr('domainid_label')
                    )
                ),
                2, 0
            );
            $wuiDomainGrid->addChild(
                new WuiString(
                    'domainid',
                    array(
                        'disp' => 'action',
                        'readonly' => 'true',
                        'value' => $domainData['domainid']
                    )
                ),
                2, 1
            );

            $wuiDomainGrid->addChild(
                new WuiLabel(
                    'maxuserslabel',
                    array(
                        'label' => $this->_localeCatalog->getStr('maxusers_label')
                    )
                ),
                3, 0
            );
            $wuiDomainGrid->addChild(
                new WuiString(
                    'maxusers',
                    array(
                        'disp' => 'action',
                        'readonly' => 'true',
                        'value' => $domainData['maxusers']
                    )
                ),
                3, 1
            );

            $wuiDomainGrid->addChild(
                new WuiLabel(
                    'basedatalabel',
                    array(
                        'label' => $this->_localeCatalog->getStr('webapp_data'),
                        'bold' => 'true'
                    )
                ),
                4, 0
            );

            $wuiDomainGrid->addChild(
                new WuiLabel(
                    'webappskeletonlabel',
                    array(
                        'label' => $this->_localeCatalog->getStr('webappskeleton_label')
                    )
                ),
                5, 0
            );
            $wuiDomainGrid->addChild(
                new WuiString(
                    'webappskeleton',
                    array(
                        'disp' => 'action',
                        'readonly' => 'true',
                        'value' => $skeletonName
                    )
                ),
                5, 1
            );

            $wuiDomainGrid->addChild(
                new WuiLabel(
                    'urllabel',
                    array(
                        'label' => $this->_localeCatalog->getStr('webappurl_label')
                    )
                ),
                6, 0
            );
            $wuiDomainGrid->addChild(
                new WuiString(
                    'webappurl',
                    array(
                        'disp' => 'action',
                        'readonly' => 'true',
                        'value' => $domainData['webappurl']
                    )
                ),
                6, 1
            );

            if (\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getEdition() == \Innomatic\Core\InnomaticContainer::EDITION_SAAS) {
                // Database fields
                //
                $wuiDomainGrid->addChild(
                    new WuiLabel(
                        'basedatalabel',
                        array(
                            'label' => $this->_localeCatalog->getStr('database_data'),
                            'bold' => 'true'
                        )
                    ),
                    0, 2
                );

                $wuiDomainGrid->addChild(
                    new WuiLabel(
                        'dbtypelabel',
                        array(
                            'label' => $this->_localeCatalog->getStr('dataaccesstype_label')
                        )
                    ),
                    1, 2
                );
                $wuiDomainGrid->addChild(
                    new WuiString(
                        'dataaccesstype',
                        array(
                            'disp' => 'action',
                            'readonly' => 'true',
                            'value' => $dbtypes[$domainData['dataaccesstype']]
                        )
                    ),
                    1, 3
                );

                $wuiDomainGrid->addChild(
                    new WuiLabel(
                        'dbnamelabel',
                        array(
                            'label' => $this->_localeCatalog->getStr('domaindaname_label')
                        )
                    ),
                    2, 2
                );
                $wuiDomainGrid->addChild(
                    new WuiString(
                        'domaindaname',
                        array(
                            'disp' => 'action',
                            'readonly' => 'true',
                            'value' => $domainData['domaindaname']
                        )
                    ),
                    2, 3
                );

                $wuiDomainGrid->addChild(
                    new WuiLabel(
                        'dbhostlabel',
                        array(
                            'label' => $this->_localeCatalog->getStr('dataaccesshost_label')
                        )
                    ),
                    3, 2
                );
                $wuiDomainGrid->addChild(
                    new WuiString(
                        'dataaccesshost',
                        array(
                            'disp' => 'action',
                            'readonly' => 'true',
                            'value' => $domainData['dataaccesshost']
                        )
                    ),
                    3, 3
                );

                $wuiDomainGrid->addChild(
                    new WuiLabel(
                        'dbportlabel',
                        array(
                            'label' => $this->_localeCatalog->getStr('dataaccessport_label')
                        )
                    ),
                    4, 2
                );
                $wuiDomainGrid->addChild(
                    new WuiString(
                        'dataaccessport',
                        array(
                            'disp' => 'action',
                            'readonly' => 'true',
                            'value' => $domainData['dataaccessport']
                        )
                    ),
                    4, 3
                );

                $wuiDomainGrid->addChild(
                    new WuiLabel(
                        'dbuserlabel',
                        array(
                            'label' => $this->_localeCatalog->getStr('dataaccessuser_label')
                        )
                    ),
                    5, 2
                );
                $wuiDomainGrid->addChild(
                    new WuiString(
                        'dataaccessuser',
                        array(
                            'disp' => 'action',
                            'readonly' => 'true',
                            'value' => $domainData['dataaccessuser']
                        )
                    ),
                    5, 3
                );

                $wuiDomainGrid->addChild(
                    new WuiLabel(
                        'dbpasswordlabel',
                        array(
                            'label' => $this->_localeCatalog->getStr('dataaccesspassword_label')
                        )
                    ),
                    6, 2
                );
                $wuiDomainGrid->addChild(
                    new WuiString(
                        'dataaccesspassword',
                        array(
                            'disp' => 'action',
                            'readonly' => 'true',
                            'value' => $domainData['dataaccesspassword']
                        )
                    ),
                    6, 3
                );
            }

            $wuiVGroup->addChild($wuiDomainGrid);

            $this->wuiMainframe->addChild($wuiVGroup);
        }

        $this->wuiTitlebar->mArgs['title'].= ' - '.$this->_localeCatalog->getStr('showdomainconfig_title');
    }

    public function viewshowdomainlog($eventData)
    {
        $query = \Innomatic\Core\InnomaticContainer::instance(
            '\Innomatic\Core\InnomaticContainer'
        )->getDataAccess()->execute('SELECT domainid,domainname FROM domains WHERE id='.$eventData['domainid']);

        if ($query->getNumberRows()) {
            $domainData = $query->getFields();

            $wuiVgroup = new WuiVertgroup('vgroup');

            $domainLogContent = '';

            if (
                file_exists(
                    \Innomatic\Core\InnomaticContainer::instance(
                        '\Innomatic\Core\InnomaticContainer'
                    )->getHome().'core/domains/'.$domainData['domainid'].'/log/domain.log'
                )
            ) {
                $logToolbar = new WuiToolBar('logbar');

                $cleanlogAction = new \Innomatic\Wui\Dispatch\WuiEventsCall();
                $cleanlogAction->addEvent(
                    new \Innomatic\Wui\Dispatch\WuiEvent(
                        'view',
                        'showdomainlog',
                        array(
                            'domainid' => $eventData['domainid']
                        )
                    )
                );
                $cleanlogAction->addEvent(
                    new \Innomatic\Wui\Dispatch\WuiEvent(
                        'action',
                        'cleandomainlog',
                        array(
                            'domainid' => $domainData['domainid']
                        )
                    )
                );
                $cleanlogButton = new WuiButton(
                    'cleanlogbutton',
                    array(
                        'label' => $this->_localeCatalog->getStr('cleanlog_button'),
                        'themeimage' => 'documentdelete',
                        'horiz' => 'true',
                        'action' => $cleanlogAction->getEventsCallString()
                    )
                );

                $logToolbar->addChild($cleanlogButton);
                $this->wuiMainvertgroup->addChild($logToolbar);

                $domainLogContent = file_get_contents(
                    \Innomatic\Core\InnomaticContainer::instance(
                        '\Innomatic\Core\InnomaticContainer'
                    )->getHome().'core/domains/'.$domainData['domainid'].'/log/domain.log'
                );
            }

            $wuiVgroup->addChild(
                new WuiText(
                    'domainlog',
                    array(
                        'disp' => 'action',
                        'readonly' => 'true',
                        'value' => \Innomatic\Wui\Wui::utf8_entities($domainLogContent),
                        'rows' => '20',
                        'cols' => '120'
                    )
                ),
                0, 1
            );
            $this->wuiMainframe->addChild($wuiVgroup);
        }

        $this->wuiTitlebar->mArgs['title'].= ' - '
            .$domainData['domainid'].' ('.$domainData['domainname'].') - '
            .$this->_localeCatalog->getStr('showdomainlog_title');
    }

    public function viewshowdataaccesslog($eventData)
    {
        $query = \Innomatic\Core\InnomaticContainer::instance(
            '\Innomatic\Core\InnomaticContainer'
        )->getDataAccess()->execute('SELECT * FROM domains WHERE id='.$eventData['domainid']);

        if ($query->getNumberRows()) {
            $domainData = $query->getFields();

            $wuiVgroup = new WuiVertgroup('vgroup');

            $dbLogContent = '';

            if (
                file_exists(
                    \Innomatic\Core\InnomaticContainer::instance(
                        '\Innomatic\Core\InnomaticContainer'
                    )->getHome().'core/domains/'.$domainData['domainid'].'/log/dataaccess.log'
                )
            ) {
                $logToolbar = new WuiToolBar('logbar');

                $cleanlogAction = new \Innomatic\Wui\Dispatch\WuiEventsCall();
                $cleanlogAction->addEvent(
                    new \Innomatic\Wui\Dispatch\WuiEvent(
                        'view',
                        'showdataaccesslog',
                        array(
                            'domainid' => $eventData['domainid']
                        )
                    )
                );
                $cleanlogAction->addEvent(
                    new \Innomatic\Wui\Dispatch\WuiEvent(
                        'action',
                        'cleandataaccesslog',
                        array(
                            'domainid' => $eventData['domainid']
                        )
                    )
                );
                $cleanlogButton = new WuiButton(
                    'cleanlogbutton',
                    array(
                        'label' => $this->_localeCatalog->getStr('cleanlog_button'),
                        'themeimage' => 'documentdelete',
                        'horiz' => 'true',
                        'action' => $cleanlogAction->getEventsCallString()
                    )
                );

                $logToolbar->addChild($cleanlogButton);
                $this->wuiMainvertgroup->addChild($logToolbar);

                $dbLogContent = file_get_contents(
                    \Innomatic\Core\InnomaticContainer::instance(
                        '\Innomatic\Core\InnomaticContainer'
                    )->getHome().'core/domains/'.$domainData['domainid'].'/log/dataaccess.log'
                );
            }

            $wuiVgroup->addChild(
                new WuiText(
                    'dataaccesslog',
                    array(
                        'disp' => 'action',
                        'readonly' => 'true',
                        'value' => \Innomatic\Wui\Wui::utf8_entities($dbLogContent),
                        'rows' => '20',
                        'cols' => '120'
                    )
                ),
                0, 1
            );

            $this->wuiMainframe->addChild($wuiVgroup);
        }

        $this->wuiTitlebar->mArgs['title'].= ' - '
            .$domainData['domainid'].' ('.$domainData['domainname'].') - '
            .$this->_localeCatalog->getStr('showdataaccesslog_title');
    }

    public function viewAccessDomain($eventData)
    {
        $domainquery = \Innomatic\Core\InnomaticContainer::instance(
            '\Innomatic\Core\InnomaticContainer'
        )->getDataAccess()->execute('SELECT domainid FROM domains WHERE id='.$eventData['domainid']);
        DesktopFrontController::instance(
            '\Innomatic\Desktop\Controller\DesktopFrontController'
        )->session->put('INNOMATIC_AUTH_USER', \Innomatic\Domain\User\User::getAdminUsername($domainquery->getFields('domainid')));
        \Innomatic\Webapp\WebAppContainer::instance(
            '\Innomatic\Webapp\WebAppContainer'
        )->getProcessor()->getResponse()->addHeader(
            'Location', \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getBaseUrl().'/domain/'
        );
    }

    public function viewdomainapplications($eventData)
    {
        $domainQuery = \Innomatic\Core\InnomaticContainer::instance(
            '\Innomatic\Core\InnomaticContainer'
        )->getDataAccess()->execute('SELECT * FROM domains WHERE id='.$eventData['domainid']);
        $domainData = $domainQuery->getFields();

        $applicationsQuery = \Innomatic\Core\InnomaticContainer::instance(
            '\Innomatic\Core\InnomaticContainer'
        )->getDataAccess()->execute(
            'SELECT * FROM applications WHERE onlyextension <> '
            .\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')
            ->getDataAccess()
            ->formatText(
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->fmttrue
            )
            .' ORDER BY appid'
        );

        if ($applicationsQuery) {
            if ($applicationsQuery->getNumberRows()) {
                $headers[0]['label'] = $this->_localeCatalog->getStr('status_header');
                $headers[1]['label'] = $this->_localeCatalog->getStr('applicationid_header');
                $headers[2]['label'] = $this->_localeCatalog->getStr('modactivationdate_header');
                $headers[4]['label'] = $this->_localeCatalog->getStr('appdeps_header');

                $row = 0;

                $wuiDomainApplicationsTable = new WuiTable('domainapplicationstable', array('headers' => $headers));

                while (!$applicationsQuery->eof) {
                    $applicationsData = $applicationsQuery->getFields();

                    if ($applicationsData['appid'] != 'innomatic') {
                        $actQuery = \Innomatic\Core\InnomaticContainer::instance(
                            '\Innomatic\Core\InnomaticContainer'
                        )->getDataAccess()->execute(
                            'SELECT * FROM applications_enabled WHERE domainid = '
                            .$eventData['domainid'].' AND applicationid = '.$applicationsData['id']
                        );

                        $wuiEnGroup[$row] = new WuiVertgroup('enable');
                        $wuiDomainApplicationsToolbar[$row] = new WuiHorizgroup('domainapplicationstoolbar'.$row);
                        $appDep = new \Innomatic\Application\ApplicationDependencies();

                        if ($actQuery->getNumberRows()) {
                            // Application is enabled
                            //
                            $actData = $actQuery->getFields();
                            $wuiDomainApplicationsTable->addChild(
                                new WuiImage(
                                    'status'.$row,
                                    array(
                                        'imageurl' => $this->wuiMainframe->mThemeHandler->mStyle['greenball']
                                    )
                                ),
                                $row, 0
                            );
                            $wuiDomainApplicationsTable->addChild(
                                new WuiLabel(
                                    'appid'.$row,
                                    array(
                                        'label' => $applicationsData['appid'],
                                        'compact' => 'true'
                                    )
                                ),
                                $row, 1
                            );
                            $wuiDomainApplicationsTable->addChild(
                                new WuiLabel(
                                    'actdate'.$row,
                                    array(
                                        'label' => $actData['activationdate'],
                                        'compact' => 'true'
                                    )
                                ),
                                $row, 2
                            );

                            $domainDependingApplications = $appDep->checkDomainDependingApplications(
                                $applicationsData['appid'], $domainData['domainid']
                            );

                            $application = new \Innomatic\Application\Application(
                                \Innomatic\Core\InnomaticContainer::instance(
                                    '\Innomatic\Core\InnomaticContainer'
                                )->getDataAccess(), $applicationsData['id']
                            );

                            $options = $application->getOptions();

                            if (!$domainDependingApplications) {
                                // No applications depends on this one
                                //
                                $disableAction[$row] = new \Innomatic\Wui\Dispatch\WuiEventsCall();
                                $disableAction[$row]->addEvent(
                                    new \Innomatic\Wui\Dispatch\WuiEvent(
                                        'view',
                                        'domainapplications',
                                        array(
                                            'domainid' => $eventData['domainid']
                                        )
                                    )
                                );
                                $disableAction[$row]->addEvent(
                                    new \Innomatic\Wui\Dispatch\WuiEvent(
                                        'action',
                                        'deactivateapplication',
                                        array(
                                            'domainid' => $eventData['domainid'],
                                            'appid' => $applicationsData['id']
                                        )
                                    )
                                );
                                $wuiSisableButton[$row] = new WuiButton(
                                    'disablebutton'.$row,
                                    array(
                                        'label' => $this->_localeCatalog->getStr('deactivateapplication_label'),
                                        'horiz' => 'true',
                                        'themeimage' => 'buttoncancel',
                                        'action' => $disableAction[$row]->getEventsCallString()
                                    )
                                );

                                $wuiDomainApplicationsToolbar[$row]->addChild($wuiSisableButton[$row]);

                                $wuiEnGroup[$row]->addChild($wuiDomainApplicationsToolbar[$row]);
                            } else {
                                // At least one application depends on this one
                                //
                                $appDepListStr = '';
                                while (list (, $dep) = each($domainDependingApplications))
                                $appDepListStr.= $dep.'<br>';
                                $wuiDomainApplicationsTable->addChild(
                                    new WuiLabel(
                                        'appdeps'.$row,
                                        array(
                                            'label' => $appDepListStr
                                        )
                                    ),
                                    $row, 4
                                );
                            }

                            if (count($options)) {
                                $toolbar = array();

                                while (list (, $name) = each($options)) {
                                    $enabled = $application->checkIfOptionEnabled($name, $eventData['domainid']);

                                    $toolbar['view']['enable'] = array(
                                        'label' => sprintf(
                                            $this->_localeCatalog->getStr(
                                                ($enabled ? 'disable' : 'enable').'_option.button'
                                            ),
                                            ucfirst($name)
                                        ),
                                        'themeimage' => $enabled ? 'buttoncancel' : 'buttonok',
                                        'compact' => 'true',
                                        'themeimagetype' => 'mini',
                                        'horiz' => 'true',
                                        'action' => \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString(
                                            '',
                                            array(
                                                array(
                                                    'view',
                                                    'domainapplications',
                                                    array(
                                                        'domainid' => $eventData['domainid']
                                                    )
                                                ),
                                                array(
                                                    'action',
                                                    $enabled ? 'disableoption' : 'enableoption',
                                                    array(
                                                        'applicationid' => $applicationsData['id'],
                                                        'domainid' => $eventData['domainid'],
                                                        'option' => $name
                                                    )
                                                )
                                            )
                                        )
                                    );

                                    $wuiEnGroup[$row]->addChild(
                                        new WuiInnomaticToolBar(
                                            'view',
                                            array(
                                                'frame' => 'false',
                                                'toolbars' => $toolbar
                                            )
                                        )
                                    );
                                }
                            }

                            $wuiDomainApplicationsTable->addChild($wuiEnGroup[$row], $row, 3);
                        } else {
                            // Application is not enabled
                            //
                            $wuiDomainApplicationsTable->addChild(
                                new WuiImage(
                                    'status'.$row,
                                    array(
                                        'imageurl' => $this->wuiMainframe->mThemeHandler->mStyle['redball']
                                    )
                                ),
                                $row, 0
                            );
                            $wuiDomainApplicationsTable->addChild(
                                new WuiLabel(
                                    'appid'.$row,
                                    array(
                                        'label' => $applicationsData['appid']
                                    )
                                ),
                                $row, 1
                            );

                            $domainApplicationDeps = $appDep->checkDomainApplicationDependencies(
                                $applicationsData['appid'],
                                $domainData['domainid'],
                                \Innomatic\Application\ApplicationDependencies::TYPE_DEPENDENCY
                            );

                            if (!is_array($domainApplicationDeps)) {
                                // All application dependecies are met
                                //
                                $enableAction[$row] = new \Innomatic\Wui\Dispatch\WuiEventsCall();
                                $enableAction[$row]->addEvent(
                                    new \Innomatic\Wui\Dispatch\WuiEvent(
                                        'view',
                                        'domainapplications',
                                        array(
                                            'domainid' => $eventData['domainid']
                                        )
                                    )
                                );
                                $enableAction[$row]->addEvent(
                                    new \Innomatic\Wui\Dispatch\WuiEvent(
                                        'action',
                                        'activateapplication',
                                        array(
                                            'domainid' => $eventData['domainid'],
                                            'appid' => $applicationsData['id']
                                        )
                                    )
                                );
                                $wuiEnableButton[$row] = new WuiButton(
                                    'enablebutton'.$row,
                                    array(
                                        'label' => $this->_localeCatalog->getStr('activateapplication_label'),
                                        'horiz' => 'true',
                                        'themeimage' => 'buttonok',
                                        'action' => $enableAction[$row]->getEventsCallString()
                                    )
                                );
                                $wuiDomainApplicationsToolbar[$row]->addChild($wuiEnableButton[$row]);
                            } else {
                                // At least one application dependency is not met
                                //
                                $appDepListStr = '';
                                while (list (, $dep) = each($domainApplicationDeps))
                                $appDepListStr.= $dep.'<br>';
                                $wuiDomainApplicationsTable->addChild(
                                    new WuiLabel(
                                        'appdeps'.$row,
                                        array(
                                            'label' => $appDepListStr
                                        )
                                    ),
                                    $row, 4
                                );
                            }
                            $wuiDomainApplicationsTable->addChild($wuiDomainApplicationsToolbar[$row], $row, 3);
                        }
                        $row ++;
                    }

                    $applicationsQuery->moveNext();
                }

                $xmlDef = '<horizgroup>
              <children>

                <button>
                  <args>
                    <themeimage>buttonok</themeimage>
                    <label type="encoded">'
                    .urlencode($this->_localeCatalog->getStr('enable_all_applications.button'))
                    .'</label>
                    <horiz>true</horiz>
                    <action type="encoded">'
                    .urlencode(
                        \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString(
                            '',
                            array(
                                array(
                                    'view',
                                    'domainapplications',
                                    array(
                                        'domainid' => $eventData['domainid']
                                    )
                                ),
                                array(
                                    'action',
                                    'activateallapplications',
                                    array(
                                        'domainid' => $eventData['domainid']
                                    )
                                )
                            )
                        )
                    )
                    .'</action>
                  </args>
                </button>

                <button>
                  <args>
                    <themeimage>buttoncancel</themeimage>
                    <label type="encoded">'
                    .urlencode($this->_localeCatalog->getStr('disable_all_applications.button'))
                    .'</label>
                    <horiz>true</horiz>
                    <needconfirm>true</needconfirm>
                    <confirmmessage type="encoded">'
                    .urlencode($this->_localeCatalog->getStr('disable_all_applications.confirm'))
                    .'</confirmmessage>
                    <action type="encoded">'
                    .urlencode(
                        \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString(
                            '',
                            array(
                                array(
                                    'view',
                                    'domainapplications',
                                    array(
                                        'domainid' => $eventData['domainid']
                                    )
                                ),
                                array(
                                    'action',
                                    'deactivateallapplications',
                                    array(
                                        'domainid' => $eventData['domainid']
                                    )
                                )
                            )
                        )
                    )
                    .'</action>
                  </args>
                </button>

              </children>
            </horizgroup>';

                $wuiMainVGroup = new WuiVertgroup('');

                $wuiMainVGroup->addChild($wuiDomainApplicationsTable);
                $wuiMainVGroup->addChild(new WuiHorizBar(''));
                $wuiMainVGroup->addChild(new WuiXml('', array('definition' => $xmlDef)));

                $this->wuiMainframe->addChild($wuiMainVGroup);
            }
        }

        $this->wuiTitlebar->mArgs['title'].= ' - '
            .$domainData['domainid'].' ('.$domainData['domainname'].') - '
            .$this->_localeCatalog->getStr('domainapplications_title');
    }

    public function viewsituation($eventData)
    {
        $domainsQuery = \Innomatic\Core\InnomaticContainer::instance(
            '\Innomatic\Core\InnomaticContainer'
        )->getDataAccess()->execute('SELECT domainid FROM domains ORDER BY domainid');

        $applicationsQuery = \Innomatic\Core\InnomaticContainer::instance(
            '\Innomatic\Core\InnomaticContainer'
        )->getDataAccess()->execute(
            'SELECT appid FROM applications WHERE onlyextension='
            .\Innomatic\Core\InnomaticContainer::instance(
                '\Innomatic\Core\InnomaticContainer'
            )->getDataAccess()->formatText(
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->fmtfalse
            ).' ORDER BY appid'
        );

        $headers = array();
        $cont = 1;
        while (!$applicationsQuery->eof) {
            $origLabel = $applicationsQuery->getFields('appid');
            $label = '';

            for ($i = 0; $i < strlen($origLabel); $i ++) {
                if ($i)
                $label.= '<br>';
                $label.= $origLabel {
                    $i};
            }

            $headers[$cont ++]['label'] = $label;
            $applicationsQuery->moveNext();
        }

        $xmlDef = '<table><name>situation</name>
      <args>
        <headers type="array">'.WuiXml::encode($headers).'</headers>
      </args>
      <children>';

        $row = 0;

        $appDeps = new \Innomatic\Application\ApplicationDependencies();

        while (!$domainsQuery->eof) {
            $xmlDef.= '<label row="'.$row.'" col="0">
          <args>
            <label type="encoded">'.urlencode($domainsQuery->getFields('domainid')).'</label>
            <compact>true</compact>
          </args>
        </label>';

            $col = 1;

            $applicationsQuery->MoveFirst();

            while (!$applicationsQuery->eof) {
                $enabled = $appDeps->IsEnabled(
                    $applicationsQuery->getFields('appid'),
                    $domainsQuery->getFields('domainid')
                );

                $xmlDef.= '<image row="'.$row.'" col="'.$col.'" halign="center" valign="middle">
              <args>
                <imageurl>'
                . ($enabled ? $this->wuiMainframe->mThemeHandler->mStyle['greenball']
                   : $this->wuiMainframe->mThemeHandler->mStyle['redball'])
                .'</imageurl>
              </args>
            </image>';
                $col ++;

                $applicationsQuery->moveNext();
            }

            $row ++;

            $domainsQuery->moveNext();
        }

        $xmlDef.= '  </children>
    </table>';

        $this->wuiMainframe->addChild(new WuiXml('', array('definition' => $xmlDef)));
    }

    public function viewhelp($eventData)
    {
        $this->wuiTitlebar->mArgs['title'].= ' - '.$this->_localeCatalog->getStr('help_title');
        $this->wuiMainframe->addChild(
            new WuiHelpNode(
                'domains_help',
                array(
                    'base' => 'innomatic',
                    'node' => 'innomatic.root.domains.'.$eventData['node'].'.html',
                    'language' => \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLanguage()
                )
            )
        );
    }
}


function domains_list_action_builder($pageNumber)
{
    return \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString(
        '',
        array(
            array(
                'view',
                'default',
                array('domainspage' => $pageNumber)
            )
        )
    );
}
