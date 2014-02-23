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
 * @since      Class available since Release 6.4.0
*/

use \Innomatic\Core\InnomaticContainer;
use \Innomatic\Wui\Widgets;
use \Innomatic\Wui\Dispatch;
use \Innomatic\Locale\LocaleCatalog;
use \Innomatic\Domain\User;
use \Shared\Wui;

class ProfilesPanelViews extends \Innomatic\Desktop\Panel\PanelViews
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
            'innomatic::domain_profiles',
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getLanguage()
        );

        $this->wuiPage = new WuiPage('page', array('title' => $this->localeCatalog->getStr('profiles_title')));
        $this->wuiMainvertgroup = new WuiVertgroup('mainvertgroup');
        $this->wuiTitlebar = new WuiTitleBar(
            'titlebar',
            array('title' => $this->localeCatalog->getStr('profiles_title'),
                'icon' => 'user')
        );
        $this->wuiMainvertgroup->addChild($this->wuiTitlebar);
        
        // Profiles bar
        //
        $wuiProfilesToolBar = new WuiToolBar('profilestoolbar');
        
        $homeAction = new WuiEventsCall();
        $homeAction->addEvent(new WuiEvent('view', 'default', ''));
        $wuiHomeButton = new WuiButton(
            'homebutton',
            array('label' => $this->localeCatalog->getStr('profiles_button'),
                'themeimage' => 'user',
                'horiz' => 'true',
                'action' => $homeAction->getEventsCallString()
            )
        );
        $wuiProfilesToolBar->addChild($wuiHomeButton);
        
        $newProfileAction = new WuiEventsCall();
        $newProfileAction->addEvent(new WuiEvent('view', 'newprofile', ''));
        $wuiNewProfileButton = new WuiButton(
            'newprofilebutton',
            array(
                'label' => $this->localeCatalog->getStr('newprofile_button'),
                'themeimage' => 'useradd',
                'horiz' => 'true',
                'action' => $newProfileAction->getEventsCallString()
            )
        );
        $wuiProfilesToolBar->addChild($wuiNewProfileButton);
        
        // Users bar
        //
        $wuiUsersToolBar = new WuiToolBar('userstoolbar');
        
        $usersAction = new WuiEventsCall();
        $usersAction->addEvent(new WuiEvent('view', 'users', ''));
        $wuiUsersButton = new WuiButton(
            'usersbutton',
            array(
                'label' => $this->localeCatalog->getStr('users_button'),
                'themeimage' => 'user',
                'horiz' => 'true',
                'action' => $usersAction->getEventsCallString()
            )
        );
        $wuiUsersToolBar->addChild($wuiUsersButton);
        
        $newUserAction = new WuiEventsCall();
        $newUserAction->addEvent(new WuiEvent('view', 'newuser', ''));
        $wuiNewUserButton = new WuiButton(
            'newuserbutton',
            array(
                'label' => $this->localeCatalog->getStr('newuser_button'),
                'themeimage' => 'useradd',
                'horiz' => 'true',
                'action' => $newUserAction->getEventsCallString()
            )
        );
        $wuiUsersToolBar->addChild($wuiNewUserButton);
        
        // Roles bar
        //
        $wuiRolesToolBar = new WuiToolBar('rolestoolbar');
        
        $rolesAction = new WuiEventsCall();
                $rolesAction->addEvent(new WuiEvent('view', 'roles', ''));
                $wuiRolesButton = new WuiButton(
                    'usersbutton',
                    array(
                    'label' => $this->localeCatalog->getStr('roles_button'),
                    'themeimage' => 'user',
                    'horiz' => 'true',
                    'action' => $rolesAction->getEventsCallString()
                )
                );
                $wuiRolesToolBar->addChild($wuiRolesButton);
        
        $newRoleAction = new WuiEventsCall();
        $newRoleAction->addEvent(new WuiEvent('view', 'newrole', ''));
        $wuiNewRoleButton = new WuiButton(
            'newuserbutton',
            array(
            'label' => $this->localeCatalog->getStr('newrole_button'),
                'themeimage' => 'useradd',
                'horiz' => 'true',
                'action' => $newRoleAction->getEventsCallString()
            )
            );
                $wuiRolesToolBar->addChild($wuiNewRoleButton);
                
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
        
        $wuiToolBarFrame->addChild($wuiProfilesToolBar);
        $wuiToolBarFrame->addChild($wuiUsersToolBar);
        $wuiToolBarFrame->addChild($wuiRolesToolBar);
        
        if (
        User::isAdminUser(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserName(),
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDomainId()
        )
        ) {
            $motdTb = new WuiToolBar('motdtb');
            $motdAction = new WuiEventsCall();
            $motdAction->addEvent(New WuiEvent('view', 'motd', ''));
            $motdButton = new WuiButton(
                'motdbutton',
                array(
                    'label' => $this->localeCatalog->getStr('motd.button'),
                    'themeimage' => 'documenttext',
                    'horiz' => 'true',
                    'action' => $motdAction->getEventsCallString()
                )
            );
        
            $motdTb->addChild($motdButton);
            $wuiToolBarFrame->addChild($motdTb);
        }
        
        $wuiToolBarFrame->addChild($wuiHelpToolBar);
        
        $this->wuiMainvertgroup->addChild($wuiToolBarFrame);
        
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
        $this->_wuiContainer->addChild($this->wuiPage);
    }
    
    public function viewDefault($eventData)
    {
        $profQuery = \Innomatic\Core\InnomaticContainer::instance(
            '\Innomatic\Core\InnomaticContainer'
        )->getCurrentDomain()->getDataAccess()->execute(
            'SELECT * FROM domain_users_groups ORDER BY groupname'
        );
    
        $profiles = array();
        while (!$profQuery->eof) {
            $profData = $profQuery->getFields();
            $profiles[$profData['id']] = $profData['groupname'];
            $profQuery->moveNext();
        }
    
        if (count($profiles)) {
            $headers[0]['label'] = $this->localeCatalog->getStr('profilename_header');
    
            $row = 0;
    
            $wuiProfilesTable = new WuiTable('profilestable', array('headers' => $headers));
    
            while (list ($id, $profileName) = each($profiles)) {
                $wuiProfilesTable->addChild(new WuiLabel('profnamelabel'.$row, array('label' => $profileName)), $row, 0);
    
                $wuiProfileToolbar[$row] = new WuiHorizgroup('applicationtoolbar'.$row);
    
                $profileAction[$row] = new WuiEventsCall();
                $profileAction[$row]->addEvent(new WuiEvent('view', 'editprofile', array('profileid' => $id)));
                $wuiProfileButton[$row] = new WuiButton(
                    'profilebutton'.$row,
                    array(
                        'label' => $this->localeCatalog->getStr('editprofile_label'),
                        'themeimage' => 'listbulletleft',
                        'themeimagetype' => 'mini',
                        'horiz' => 'true',
                        'action' => $profileAction[$row]->getEventsCallString()
                    )
                );
                $wuiProfileToolbar[$row]->addChild($wuiProfileButton[$row]);
    
                $renameAction[$row] = new WuiEventsCall();
                $renameAction[$row]->addEvent(new WuiEvent('view', 'renameprofile', array('profileid' => $id)));
                $wuiRenameButton[$row] = new WuiButton(
                    'renamebutton'.$row,
                    array(
                        'label' => $this->localeCatalog->getStr('renameprofile_label'),
                        'themeimage' => 'documenttext',
                        'themeimagetype' => 'mini',
                        'horiz' => 'true',
                        'action' => $renameAction[$row]->getEventsCallString()
                    )
                );
                $wuiProfileToolbar[$row]->addChild($wuiRenameButton[$row]);
    
                $removeAction[$row] = new WuiEventsCall();
                $removeAction[$row]->addEvent(new WuiEvent('view', 'deleteprofile', array('profileid' => $id)));
                $wuiRemoveButton[$row] = new WuiButton(
                    'removebutton'.$row,
                    array(
                        'label' => $this->localeCatalog->getStr('removeprofile_label'),
                        'themeimage' => 'trash',
                        'themeimagetype' => 'mini',
                        'horiz' => 'true',
                        'action' => $removeAction[$row]->getEventsCallString()
                    )
                );
                $wuiProfileToolbar[$row]->addChild($wuiRemoveButton[$row]);
    
                $wuiProfilesTable->addChild($wuiProfileToolbar[$row], $row, 1);
    
                $row ++;
            }
    
            $this->wuiMainframe->addChild($wuiProfilesTable);
        }
    
        $this->wuiTitlebar->mTitle.= ' - '.$this->localeCatalog->getStr('default_title');
    }
    
    public function viewEditprofile($eventData)
    {
        $profQuery = \Innomatic\Core\InnomaticContainer::instance(
            '\Innomatic\Core\InnomaticContainer'
        )->getCurrentDomain()->getDataAccess()->execute(
            'SELECT * FROM domain_users_groups WHERE id='.$eventData['profileid']
        );
    
        $profData = $profQuery->getFields();
    
        $groupsQuery = \Innomatic\Core\InnomaticContainer::instance(
            '\Innomatic\Core\InnomaticContainer'
        )->getCurrentDomain()->getDataAccess()->execute('SELECT * FROM domain_panels_groups ORDER BY name');
    
        if ($groupsQuery->getNumberRows()) {
            $perm = new \Innomatic\Desktop\Auth\DesktopPanelAuthorizator(
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(),
                $eventData['profileid']
            );
            $row = 0;
    
            $headers[0]['label'] = '';
            $headers[1]['label'] = $this->localeCatalog->getStr('domaingroup_header');
            $headers[2]['label'] = '';
            $headers[3]['label'] = $this->localeCatalog->getStr('domainpanel_header');
    
            $wuiGroupsTable = new WuiTable('groupsstable', array('headers' => $headers));
    
            while (!$groupsQuery->eof) {
                $groupData = $groupsQuery->getFields();
                $tempLocale = new LocaleCatalog(
                    $groupData['catalog'],
                    \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getLanguage()
                );
                $nodeState = $perm->Check($groupData['id'], \Innomatic\Desktop\Auth\DesktopPanelAuthorizator::NODETYPE_GROUP);
    
                switch ($nodeState) {
                	case \Innomatic\Desktop\Auth\DesktopPanelAuthorizator::NODE_FULLYENABLED :
                	    $icon = $this->wuiMainframe->mThemeHandler->mStyle['greenball'];
                	    $enabled = true;
                	    break;
    
                	case \Innomatic\Desktop\Auth\DesktopPanelAuthorizator::NODE_PARTIALLYENABLED :
                	    $icon = $this->wuiMainframe->mThemeHandler->mStyle['goldball'];
                	    $enabled = true;
                	    break;
    
                	case \Innomatic\Desktop\Auth\DesktopPanelAuthorizator::NODE_NOTENABLED :
                	    $icon = $this->wuiMainframe->mThemeHandler->mStyle['redball'];
                	    $enabled = false;
                	    break;
                }
    
                $wuiGroupsTable->addChild(new WuiImage('statusimage'.$row, array('imageurl' => $icon)), $row, 0);
                $wuiGroupsTable->addChild(
                    new WuiLabel(
                        'grouplabel'.$row,
                        array(
                            'label' => $tempLocale->getStr($groupData['name'])
                        )
                    ),
                    $row, 1
                );
    
                $wuiGroupToolbar[$row] = new WuiHorizgroup('grouptoolbar'.$row);
    
                if ($enabled) {
                    $disableAction[$row] = new WuiEventsCall();
                    $disableAction[$row]->addEvent(
                        new WuiEvent(
                            'view',
                            'editprofile',
                            array(
                                'profileid' => $eventData['profileid']
                            )
                        )
                    );
                    $disableAction[$row]->addEvent(
                        new WuiEvent(
                            'action',
                            'disablenode',
                            array(
                                'ntype' => \Innomatic\Desktop\Auth\DesktopPanelAuthorizator::NODETYPE_GROUP,
                                'node' => $groupData['id'],
                                'gid' => $eventData['profileid']
                            )
                        )
                    );
                    $wuiDisableButton[$row] = new WuiButton(
                        'disablebutton'.$row,
                        array(
                            label => $this->localeCatalog->getStr('disablenode_label'),
                            'themeimage' => 'lock',
                            'themeimagetype' => 'mini',
                            'horiz' => 'true',
                            'action' => $disableAction[$row]->getEventsCallString()
                        )
                    );
                    $wuiGroupToolbar[$row]->addChild($wuiDisableButton[$row]);
                }
    
                if (!$enabled or $nodeState == \Innomatic\Desktop\Auth\DesktopPanelAuthorizator::NODE_PARTIALLYENABLED) {
                    $enableAction[$row] = new WuiEventsCall();
                    $enableAction[$row]->addEvent(
                        new WuiEvent(
                            'view',
                            'editprofile',
                            array('profileid' => $eventData['profileid']
                            )
                        )
                    );
                    $enableAction[$row]->addEvent(
                        new WuiEvent(
                            'action',
                            'enablenode',
                            array(
                                'ntype' => \Innomatic\Desktop\Auth\DesktopPanelAuthorizator::NODETYPE_GROUP,
                                'node' => $groupData['id'],
                                'gid' => $eventData['profileid']
                            )
                        )
                    );
                    $wuiEnableButton[$row] = new WuiButton(
                        'enablebutton'.$row,
                        array(
                            label => $this->localeCatalog->getStr('enablenode_label'),
                            'themeimage' => 'unlock',
                            'themeimagetype' => 'mini',
                            'horiz' => 'true',
                            'action' => $enableAction[$row]->getEventsCallString()
                        )
                    );
                    $wuiGroupToolbar[$row]->addChild($wuiEnableButton[$row]);
                }
    
                $wuiGroupsTable->addChild($wuiGroupToolbar[$row], $row, 4);
    
                $row ++;
    
                $pagesQuery = \Innomatic\Core\InnomaticContainer::instance(
                    '\Innomatic\Core\InnomaticContainer'
                )->getCurrentDomain()->getDataAccess()->execute(
                    'SELECT *
                FROM domain_panels
                WHERE groupid='.$groupData['id'].'
                AND (hidden != '.\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()
                    ->getDataAccess()->formatText(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()
                        ->getDataAccess()->fmttrue).'
                OR hidden IS NULL)
                ORDER BY name'
                );
    
                while (!$pagesQuery->eof) {
                    $pageData = $pagesQuery->getFields();
                    $tempLocale = new LocaleCatalog(
                        $pageData['catalog'],
                        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getLanguage()
                    );
                    $nodeState = $perm->Check($pageData['id'], 'page');
    
                    switch ($nodeState) {
                    	case \Innomatic\Desktop\Auth\DesktopPanelAuthorizator::NODE_FULLYENABLED :
                    	    $icon = $this->wuiMainframe->mThemeHandler->mStyle['greenball'];
                    	    $enabled = true;
                    	    break;
    
                    	case \Innomatic\Desktop\Auth\DesktopPanelAuthorizator::NODE_NOTENABLED :
                    	    $icon = $this->wuiMainframe->mThemeHandler->mStyle['redball'];
                    	    $enabled = false;
                    	    break;
                    }
    
                    $wuiGroupsTable->addChild(
                        new WuiImage(
                            'statusimage'.$row,
                            array(
                                'imageurl' => $icon
                            )
                        ),
                        $row, 2
                    );
                    $wuiGroupsTable->addChild(
                        new WuiLabel(
                            'methodlabel'.$row,
                            array(
                                'label' => $tempLocale->getStr($pageData['name'])
                            )
                        ),
                        $row, 3
                    );
    
                    $wuiPageToolbar[$row] = new WuiHorizgroup('pagetoolbar'.$row);
    
                    if ($enabled) {
                        $disableAction[$row] = new WuiEventsCall();
                        $disableAction[$row]->addEvent(
                            new WuiEvent(
                                'view',
                                'editprofile',
                                array(
                                    'profileid' => $eventData['profileid']
                                )
                            )
                        );
                        $disableAction[$row]->addEvent(
                            new WuiEvent(
                                'action',
                                'disablenode',
                                array(
                                    'ntype' => \Innomatic\Desktop\Auth\DesktopPanelAuthorizator::NODETYPE_PAGE,
                                    'node' => $pageData['id'],
                                    'gid' => $eventData['profileid']
                                )
                            )
                        );
                        $wuiDisableButton[$row] = new WuiButton(
                            'disablebutton'.$row,
                            array(
                                label => $this->localeCatalog->getStr('disablenode_label'),
                                'themeimage' => 'lock',
                                'themeimagetype' => 'mini',
                                'horiz' => 'true',
                                'action' => $disableAction[$row]->getEventsCallString()
                            )
                        );
                        $wuiPageToolbar[$row]->addChild($wuiDisableButton[$row]);
                    } else {
                        $enableAction[$row] = new WuiEventsCall();
                        $enableAction[$row]->addEvent(
                            new WuiEvent(
                                'view',
                                'editprofile',
                                array(
                                    'profileid' => $eventData['profileid']
                                )
                            )
                        );
                        $enableAction[$row]->addEvent(
                            new WuiEvent(
                                'action',
                                'enablenode',
                                array(
                                    'ntype' => \Innomatic\Desktop\Auth\DesktopPanelAuthorizator::NODETYPE_PAGE,
                                    'node' => $pageData['id'],
                                    'gid' => $eventData['profileid']
                                )
                            )
                        );
                        $wuiEnableButton[$row] = new WuiButton(
                            'enablebutton'.$row,
                            array(
                                label => $this->localeCatalog->getStr('enablenode_label'),
                                'themeimage' => 'unlock',
                                'themeimagetype' => 'mini',
                                'horiz' => 'true',
                                'action' => $enableAction[$row]->getEventsCallString()
                            )
                        );
                        $wuiPageToolbar[$row]->addChild($wuiEnableButton[$row]);
                    }
    
                    $wuiGroupsTable->addChild($wuiPageToolbar[$row], $row, 4);
    
                    $row ++;
                    $pagesQuery->moveNext();
                }
    
                $groupsQuery->moveNext();
            }
    
            $this->wuiMainframe->addChild($wuiGroupsTable);
        }
    
        $this->wuiTitlebar->mTitle.= ' - '.$profData['groupname'].' - '.$this->localeCatalog->getStr('editprofile_title');
    }
    
    public function viewNewprofile($eventData)
    {
        $wuiVGroup = new WuiVertgroup('vgroup');
    
        $wuiProfileGrid = new WuiGrid('newgroupgrid', array('rows' => '2', 'cols' => '2'));
    
        // Group fields
        //
        $wuiProfileGrid->addChild(
            new WuiLabel(
                'namelabel',
                array(
                    'label' => $this->localeCatalog->getStr('groupname_label').' (*)'
                )
            ),
            0, 0
        );
        $wuiProfileGrid->addChild(new WuiString('groupname', array('disp' => 'action')), 0, 1);
    
        $wuiVGroup->addChild($wuiProfileGrid);
        $wuiVGroup->addChild(
            new WuiSubmit(
                'submit1',
                array(
                    'caption' => $this->localeCatalog->getStr('newprofile_submit')
                )
            )
        );
    
        $wuiVGroup->addChild(
            new WuiHorizBar(
                'horizbar1'
            )
        );
        $wuiVGroup->addChild(
            new WuiLabel(
                'reqfieldslabel',
                array(
                    'label' => $this->localeCatalog->getStr('requiredfields_label')
                )
            )
        );
    
        $formEventsCall = new WuiEventsCall();
        $formEventsCall->addEvent(
            new WuiEvent(
                'action',
                'newgroup',
                ''
            )
        );
        $formEventsCall->addEvent(new WuiEvent('view', 'default', ''));
    
        $wuiForm = new WuiForm('newgroupform', array('action' => $formEventsCall->getEventsCallString()));
        $wuiForm->addChild($wuiVGroup);
    
        $this->wuiMainframe->addChild($wuiForm);
    
        $this->wuiTitlebar->mTitle.= ' - '.$this->localeCatalog->getStr('newgroup_title');
    }
    
    public function viewDeleteprofile($eventData)
    {
        $profQuery = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->execute(
            'SELECT * FROM domain_users_groups WHERE id='.$eventData['profileid']
        );
    
        $profData = $profQuery->getFields();
    
        $wuiVGroup = new WuiVertgroup('removereqvgroup', array('halign' => 'center', 'groupalign' => 'center'));
    
        $wuiHGroup1 = new WuiHorizgroup('removereqhgroup', array('align' => 'middle', 'width' => '0%'));
        $wuiHGroup1->addChild(
            new WuiLabel(
                'removereqlabel',
                array(
                    'label' => sprintf(
                        $this->localeCatalog->getStr('removeprofilequestion_label'),
                        $profData['groupname']
                    )
                )
            )
        );
    
        $wuiVGroup->addChild($wuiHGroup1);
    
        $wuiHGroup2 = new WuiHorizgroup(
            'removereqhgroup',
            array(
                'align' => 'middle',
                'groupalign' => 'center'
            )
        );
    
        $removeAction = new WuiEventSCall();
        $removeAction->addEvent(
            new WuiEvent(
                'view',
                'default',
                ''
            )
        );
        $removeAction->addEvent(
            new WuiEvent(
                'action',
                'removegroup',
                array(
                    'gid' => $eventData['profileid']
                )
            )
        );
        $removeButton = new WuiButton(
            'removebutton',
            array(
                'label' => $this->localeCatalog->getStr('okremoveprofile_button'),
                'horiz' => 'true',
                'themeimage' => 'buttonok',
                'action' => $removeAction->getEventsCallString()
            )
        );
        $wuiHGroup2->addChild($removeButton);
    
        $wuiHGroup2->addChild($removeFrame);
    
        $remove2Action = new WuiEventSCall();
        $remove2Action->addEvent(
            new WuiEvent(
                'view',
                'default',
                ''
            )
        );
        $remove2Action->addEvent(
            new WuiEvent(
                'action',
                'removegroup',
                array(
                    'gid' => $eventData['profileid'],
                    'userstoo' => '1'
                )
            )
        );
        $remove2Button = new WuiButton(
            'remove2button',
            array(
                'label' => $this->localeCatalog->getStr('okremoveprofileandusers_button'),
                'horiz' => 'true',
                'themeimage' => 'buttonok',
                'action' => $remove2Action->getEventsCallString()
            )
        );
        $wuiHGroup2->addChild($remove2Button);
    
        $wuiHGroup2->addChild($remove2Frame);
    
        $dontRemoveAction = new WuiEventsCall();
        $dontRemoveAction->addEvent(
            new WuiEvent(
                'view',
                'default',
                ''
            )
        );
        $dontRemoveButton = new WuiButton(
            'dontremovebutton',
            array(
                'label' => $this->localeCatalog->getStr('dontremoveprofile_button'),
                'horiz' => 'true',
                'themeimage' => 'stop',
                'action' => $dontRemoveAction->getEventsCallString()
            )
        );
        $wuiHGroup2->addChild($dontRemoveButton);
    
        $okAction = new WuiEventsCall();
        $okAction->addEvent(new WuiEvent('view', 'default', ''));
    
        $wuiOkForm = new WuiForm('okform', array('action'));
    
        $wuiVGroup->addChild($wuiHGroup2);
    
        $this->wuiMainframe->addChild($wuiVGroup);
    
        $this->wuiTitlebar->mTitle.= ' - '.$profData['profilename'].' - '.$this->localeCatalog->getStr('removeprofile_title');
    }
    
    public function viewRenameprofile($eventData)
    {
        $profQuery = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->execute(
            'SELECT * FROM domain_users_groups WHERE id='.$eventData['profileid']
        );
    
        $profData = $profQuery->getFields();
    
        $wuiVGroup = new WuiVertgroup('vgroup');
    
        $wuiProfileGrid = new WuiGrid('renprofilegrid', array('rows' => '2', 'cols' => '2'));
    
        // Profile fields
        //
        $wuiProfileGrid->addChild(
            new WuiLabel(
                'namelabel',
                array(
                    'label' => $this->localeCatalog->getStr('profilename_label').' (*)'
                )
            ),
            0, 0
        );
        $wuiProfileGrid->addChild(
            new WuiString(
                'groupname',
                array(
                    'disp' => 'action',
                    'value' => $profData['groupname']
                )
            ),
            0, 1
        );
    
        $wuiVGroup->addChild($wuiProfileGrid);
        $wuiVGroup->addChild(
            new WuiSubmit(
                'submit1',
                array(
                    'caption' => $this->localeCatalog->getStr('renameprofile_submit')
                )
            )
        );
    
        $wuiVGroup->addChild(new WuiHorizBar('horizbar1'));
        $wuiVGroup->addChild(
            new WuiLabel(
                'reqfieldslabel',
                array(
                    'label' => $this->localeCatalog->getStr('requiredfields_label')
                )
            )
        );
    
        $formEventsCall = new WuiEventsCall();
        $formEventsCall->addEvent(
            new WuiEvent(
                'action',
                'rengroup',
                array(
                    'gid' => $eventData['profileid']
                )
            )
        );
        $formEventsCall->addEvent(new WuiEvent('view', 'default', ''));
    
        $wuiForm = new WuiForm('renameprofileform', array('action' => $formEventsCall->getEventsCallString()));
        $wuiForm->addChild($wuiVGroup);
    
        $this->wuiMainframe->addChild($wuiForm);
    
        $this->wuiTitlebar->mTitle.= ' - '.$profData['groupname'].' - '.$this->localeCatalog->getStr('renameprofile_title');
    }
    
    public function viewUsers($eventData)
    {
        $usersQuery = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->execute(
            'SELECT id,username,fname,lname,email,groupid FROM domain_users ORDER BY username'
        );
        $profQuery = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->execute(
            'SELECT id,groupname FROM domain_users_groups ORDER BY groupname'
        );
    
        $profiles = array();
        while (!$profQuery->eof) {
            $profData = $profQuery->getFields();
            $profiles[$profData['id']] = $profData['groupname'];
            $profQuery->moveNext();
        }
    
        if ($usersQuery->getNumberRows()) {
            $headers[0]['label'] = $this->localeCatalog->getStr('username_header');
            $headers[1]['label'] = $this->localeCatalog->getStr('completename_header');
            $headers[2]['label'] = $this->localeCatalog->getStr('email_header');
            $headers[3]['label'] = $this->localeCatalog->getStr('userprofilename_header');
    
            $row = 0;
    
            $wuiUsersTable = new WuiTable('userstable', array('headers' => $headers));
    
            while (!$usersQuery->eof) {
                $userData = $usersQuery->getFields();
    
                $wuiUsersTable->addChild(
                    new WuiLabel(
                        'usernamelabel'.$row,
                        array('label' => $userData['username'])
                    ),
                    $row, 0
                );
                $wuiUsersTable->addChild(
                    new WuiLabel(
                        'completenamelabel'.$row,
                        array(
                            'label' => strcmp(
                                $userData['username'],
                                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDomainId()
                            )
                            != 0 ? $userData['lname'].' '.$userData['fname']
                            : $this->localeCatalog->getStr('superuser_label')
                        )
                    ),
                    $row, 1
                );
                $wuiUsersTable->addChild(new WuiLabel('emaillabel'.$row, array('label' => $userData['email'])), $row, 2);
                $wuiUsersTable->addChild(
                    new WuiLabel(
                        'userprofilelabel'.$row,
                        array(
                            'label' => ($userData['groupid'] != '0' and strlen($userData['groupid']))
                            ? $profiles[$userData['groupid']]
                            : $this->localeCatalog->getStr('noprofileid_label')
                        )
                    ),
                    $row, 3
                );
    
                if (
                !User::isAdminUser(
                    $userData['username'],
                    \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDomainId()
                )
                ) {
                    $wuiUserToolbar[$row] = new WuiHorizgroup('usertoolbar'.$row);
    
                    $profileAction[$row] = new WuiEventsCall();
                    $profileAction[$row]->addEvent(new WuiEvent('view', 'chprofile', array('userid' => $userData['id'])));
                    $wuiProfileButton[$row] = new WuiButton(
                        'profilebutton'.$row,
                        array(
                            'label' => $this->localeCatalog->getStr('chprofile_label'),
                            'themeimage' => 'listbulletleft',
                            'themeimagetype' => 'mini',
                            'horiz' => 'true',
                            'action' => $profileAction[$row]->getEventsCallString()
                        )
                    );
                    $wuiUserToolbar[$row]->addChild($wuiProfileButton[$row]);
    
                    $chPasswdAction[$row] = new WuiEventsCall();
                    $chPasswdAction[$row]->addEvent(
                        new WuiEvent(
                            'view',
                            'chpassword',
                            array(
                                'userid' => $userData['id']
                            )
                        )
                    );
                    $wuiChPasswdButton[$row] = new WuiButton(
                        'chpasswdbutton'.$row,
                        array(
                            'label' => $this->localeCatalog->getStr('chpasswd_label'),
                            'themeimage' => 'documenttext',
                            'themeimagetype' => 'mini',
                            'horiz' => 'true', 'action' => $chPasswdAction[$row]->getEventsCallString()
                        )
                    );
                    $wuiUserToolbar[$row]->addChild($wuiChPasswdButton[$row]);
    
                    $chDataAction[$row] = new WuiEventsCall();
                    $chDataAction[$row]->addEvent(
                        new WuiEvent(
                            'view',
                            'edituser',
                            array(
                                'userid' => $userData['id']
                            )
                        )
                    );
                    $wuiChDataButton[$row] = new WuiButton(
                        'chdatabutton'.$row,
                        array(
                            'label' => $this->localeCatalog->getStr('chdata_label'),
                            'themeimage' => 'documenttext',
                            'themeimagetype' => 'mini',
                            'horiz' => 'true',
                            'action' => $chDataAction[$row]->getEventsCallString()
                        )
                    );
                    $wuiUserToolbar[$row]->addChild($wuiChDataButton[$row]);
    
                    $removeAction[$row] = new WuiEventsCall();
                    $removeAction[$row]->addEvent(
                        new WuiEvent(
                            'view',
                            'deleteuser',
                            array(
                                'userid' => $userData['id']
                            )
                        )
                    );
                    $wuiRemoveButton[$row] = new WuiButton(
                        'removebutton'.$row,
                        array(
                            'label' => $this->localeCatalog->getStr('removeuser_label'),
                            'themeimage' => 'trash',
                            'themeimagetype' => 'mini',
                            'horiz' => 'true',
                            'action' => $removeAction[$row]->getEventsCallString()
                        )
                    );
                    $wuiUserToolbar[$row]->addChild($wuiRemoveButton[$row]);
    
                    $wuiUsersTable->addChild($wuiUserToolbar[$row], $row, 4);
                }
    
                $usersQuery->moveNext();
                $row ++;
            }
    
            $this->wuiMainframe->addChild($wuiUsersTable);
        }
    
        $this->wuiTitlebar->mTitle.= ' - '.$this->localeCatalog->getStr('users_title');
    }
    
    public function viewNewuser($eventData)
    {
        $profQuery = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->execute(
            'SELECT * FROM domain_users_groups'
        );
    
        $profiles = array();
        $profiles[0] = $this->localeCatalog->getStr('noprofileid_label');
        while (!$profQuery->eof) {
            $profData = $profQuery->getFields();
            $profiles[$profData['id']] = $profData['groupname'];
            $profQuery->moveNext();
        }
    
        $wuiVGroup = new WuiVertgroup('vgroup');
    
        $wuiUserGrid = new WuiGrid('newusergrid', array('rows' => '7', 'cols' => '2'));
    
        // User fields
        //
        $wuiUserGrid->addChild(
            new WuiLabel(
                'namelabel',
                array(
                    'label' => $this->localeCatalog->getStr('username_label').' (*)'
                )
            ),
            0, 0
        );
        $wuiUserGrid->addChild(
            new WuiString(
                'username',
                array(
                    'disp' => 'action'
                )
            ),
            0, 1
        );
    
        $wuiUserGrid->addChild(
            new WuiLabel(
                'passwordalabel',
                array(
                    'label' => $this->localeCatalog->getStr('userpassworda_label').' (*)'
                )
            ),
            1, 0
        );
        $wuiUserGrid->addChild(
            new WuiString(
                'passworda',
                array(
                    'disp' => 'action',
                    'password' => 'true'
                )
            ),
            1, 1
        );
    
        $wuiUserGrid->addChild(
            new WuiLabel(
                'passwordblabel',
                array(
                    'label' => $this->localeCatalog->getStr('userpasswordb_label').' (*)'
                )
            ),
            2, 0
        );
        $wuiUserGrid->addChild(
            new WuiString(
                'passwordb',
                array(
                    'disp' => 'action',
                    'password' => 'true'
                )
            ),
            2, 1
        );
    
        $wuiUserGrid->addChild(
            new WuiLabel(
                'profilelabel',
                array(
                    'label' => $this->localeCatalog->getStr('usergroup_label').' (*)'
                )
            ),
            3, 0
        );
        $wuiUserGrid->addChild(
            new WuiComboBox(
                'groupid',
                array(
                    'disp' => 'action',
                    'elements' => $profiles
                )
            ),
            3, 1
        );
    
        $wuiUserGrid->addChild(
            new WuiLabel(
                'fnamelabel',
                array(
                    'label' => $this->localeCatalog->getStr('userfname_label')
                )
            ),
            4, 0
        );
        $wuiUserGrid->addChild(
            new WuiString(
                'fname',
                array(
                    'disp' => 'action'
                )
            ),
            4, 1
        );
    
        $wuiUserGrid->addChild(
            new WuiLabel(
                'lnamelabel',
                array(
                    'label' => $this->localeCatalog->getStr('userlname_label')
                )
            ),
            5, 0
        );
        $wuiUserGrid->addChild(
            new WuiString(
                'lname',
                array(
                    'disp' => 'action'
                )
            ),
            5, 1
        );
    
        $wuiUserGrid->addChild(
            new WuiLabel(
                'emaillabel',
                array(
                    'label' => $this->localeCatalog->getStr('email_label')
                )
            ),
            6, 0
        );
        $wuiUserGrid->addChild(
            new WuiString(
                'email',
                array(
                    'disp' => 'action'
                )
            ),
            6, 1
        );
    
        $wuiUserGrid->addChild(
            new WuiLabel(
                'otherlabel',
                array(
                    'label' => $this->localeCatalog->getStr('userother_label')
                )
            ),
            7, 0
        );
        $wuiUserGrid->addChild(
            new WuiText(
                'other',
                array(
                    'disp' => 'action',
                    'rows' => '5',
                    'cols' => '80'
                )
            ),
            7, 1
        );
    
        $wuiVGroup->addChild($wuiUserGrid);
        $wuiVGroup->addChild(
            new WuiSubmit(
                'submit1',
                array(
                    'caption' => $this->localeCatalog->getStr('newuser_submit')
                )
            )
        );
    
        $wuiVGroup->addChild(new WuiHorizBar('horizbar1'));
        $wuiVGroup->addChild(
            new WuiLabel(
                'reqfieldslabel',
                array(
                    'label' => $this->localeCatalog->getStr('requiredfields_label')
                )
            )
        );
    
        $formEventsCall = new WuiEventsCall();
        $formEventsCall->addEvent(
            new WuiEvent(
                'action',
                'adduser',
                ''
            )
        );
        $formEventsCall->addEvent(
            new WuiEvent(
                'view',
                'users',
                ''
            )
        );
    
        $wuiForm = new WuiForm(
            'newuserform',
            array(
                'action' => $formEventsCall->getEventsCallString()
            )
        );
        $wuiForm->addChild($wuiVGroup);
    
        $this->wuiMainframe->addChild($wuiForm);
    
        $this->wuiTitlebar->mTitle.= ' - '.$this->localeCatalog->getStr('newuser_title');
    }
    
    public function viewDeleteuser($eventData)
    {
        $userQuery = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->execute(
            'SELECT * FROM domain_users WHERE id='.$eventData['userid']
        );
    
        $userData = $userQuery->getFields();
    
        $wuiVGroup = new WuiVertgroup(
            'removereqvgroup',
            array(
                'halign' => 'center',
                'groupalign' => 'center'
            )
        );
    
        $wuiHGroup1 = new WuiHorizgroup(
            'removereqhgroup',
            array(
                'align' => 'middle',
                'width' => '0%'
            )
        );
        $wuiHGroup1->addChild(
            new WuiLabel(
                'removereqlabel',
                array(
                    'label' => sprintf(
                        $this->localeCatalog->getStr('removeuserquestion_label'),
                        $userData['username']
                    )
                )
            )
        );
    
        $wuiVGroup->addChild($wuiHGroup1);
    
        $wuiHGroup2 = new WuiHorizgroup(
            'removereqhgroup',
            array(
                'align' => 'middle',
                'groupalign' => 'center'
            )
        );
    
        $removeAction = new WuiEventSCall();
        $removeAction->addEvent(new WuiEvent('view', 'users', ''));
        $removeAction->addEvent(new WuiEvent('action', 'removeuser', array('uid' => $eventData['userid'])));
        $removeButton = new WuiButton(
            'removebutton',
            array(
                'label' => $this->localeCatalog->getStr('okremoveuser_button'),
                'horiz' => 'true',
                'themeimage' => 'buttonok',
                'action' => $removeAction->getEventsCallString()
            )
        );
        $wuiHGroup2->addChild($removeButton);
    
        $dontRemoveAction = new WuiEventsCall();
        $dontRemoveAction->addEvent(new WuiEvent('view', 'users', ''));
        $dontRemoveButton = new WuiButton(
            'dontremovebutton',
            array(
                'label' => $this->localeCatalog->getStr('dontremoveuser_button'),
                'horiz' => 'true',
                'themeimage' => 'stop',
                'action' => $dontRemoveAction->getEventsCallString()
            )
        );
        $wuiHGroup2->addChild($dontRemoveButton);
    
        $okAction = new WuiEventsCall();
        $okAction->addEvent(new WuiEvent('view', 'default', ''));
    
        $wuiOkForm = new WuiForm('okform', array('action'));
    
        $wuiVGroup->addChild($wuiHGroup2);
    
        $this->wuiMainframe->addChild($wuiVGroup);
    
        $this->wuiTitlebar->mTitle.= ' - '.$userData['username'].' - '.$this->localeCatalog->getStr('removeuser_title');
    }
    
    public function viewEdituser($eventData)
    {
        $userQuery = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->execute(
            'SELECT * FROM domain_users WHERE id='.$eventData['userid']
        );
        $userData = $userQuery->getFields();
    
        $profQuery = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->execute(
            'SELECT * FROM domain_users_groups'
        );
    
        $profiles = array();
        $profiles[0] = $this->localeCatalog->getStr('noprofileid_label');
        while (!$profQuery->eof) {
            $profData = $profQuery->getFields();
            $profiles[$profData['id']] = $profData['groupname'];
            $profQuery->moveNext();
        }
    
        $wuiVGroup = new WuiVertgroup('vgroup');
    
        $wuiUserGrid = new WuiGrid('editusergrid', '');
    
        // User fields
        //
        $wuiUserGrid->addChild(
            new WuiLabel(
                'fnamelabel',
                array(
                    'label' => $this->localeCatalog->getStr('userfname_label')
                )
            ),
            0, 0
        );
        $wuiUserGrid->addChild(
            new WuiString(
                'fname',
                array(
                    'disp' => 'action',
                    'value' => $userData['fname']
                )
            ),
            0, 1
        );
    
        $wuiUserGrid->addChild(
            new WuiLabel(
                'lnamelabel',
                array(
                    'label' => $this->localeCatalog->getStr('userlname_label')
                )
            ),
            1, 0
        );
        $wuiUserGrid->addChild(
            new WuiString(
                'lname',
                array(
                    'disp' => 'action',
                    'value' => $userData['lname']
                )
            ),
            1, 1
        );
    
        $wuiUserGrid->addChild(
            new WuiLabel(
                'emaillabel',
                array(
                    'label' => $this->localeCatalog->getStr('email_label')
                )
            ),
            2, 0
        );
        $wuiUserGrid->addChild(
            new WuiString(
                'email',
                array(
                    'disp' => 'action',
                    'value' => $userData['email']
                )
            ),
            2, 1
        );
    
        $wuiUserGrid->addChild(
            new WuiLabel(
                'otherlabel',
                array(
                    'label' => $this->localeCatalog->getStr('userother_label')
                )
            ),
            3, 0
        );
        $wuiUserGrid->addChild(
            new WuiText(
                'other',
                array(
                    'disp' => 'action',
                    'rows' => '5',
                    'cols' => '80',
                    'value' => $userData['otherdata']
                )
            ),
            3, 1
        );
    
        $wuiVGroup->addChild($wuiUserGrid);
        $wuiVGroup->addChild(
            new WuiSubmit(
                'submit1',
                array(
                    'caption' => $this->localeCatalog->getStr('edituser_submit')
                )
            )
        );
    
        $wuiVGroup->addChild(new WuiHorizBar('horizbar1'));
        $wuiVGroup->addChild(
            new WuiLabel(
                'reqfieldslabel',
                array(
                    'label' => $this->localeCatalog->getStr('requiredfields_label')
                )
            )
        );
    
        $formEventsCall = new WuiEventsCall();
        $formEventsCall->addEvent(
            new WuiEvent(
                'action',
                'edituser',
                array(
                    'uid' => $eventData['userid'],
                    'groupid' => $userData['groupid'],
                    'username' => $userData['username']
                )
            )
        );
        $formEventsCall->addEvent(new WuiEvent('view', 'users', ''));
    
        $wuiForm = new WuiForm(
            'newuserform',
            array(
                'action' => $formEventsCall->getEventsCallString()
            )
        );
        $wuiForm->addChild($wuiVGroup);
    
        $this->wuiMainframe->addChild($wuiForm);
    
        $this->wuiTitlebar->mTitle.= ' - '.$userData['username'].' - '.$this->localeCatalog->getStr('edituser_title');
    }
    
    public function viewChpassword($eventData)
    {
        $userQuery = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->execute(
            'SELECT * FROM domain_users WHERE id='.$eventData['userid']
        );
        $userData = $userQuery->getFields();
    
        $wuiVGroup = new WuiVertgroup('vgroup');
    
        $wuiUserGrid = new WuiGrid('chpasswdgrid', array('rows' => '2', 'cols' => '2'));
    
        // User fields
        //
        $wuiUserGrid->addChild(
            new WuiLabel(
                'pwdlabel',
                array(
                    'label' => $this->localeCatalog->getStr('chpassword_label').' (*)'
                )
            ),
            0, 0
        );
        $wuiUserGrid->addChild(
            new WuiString(
                'password',
                array(
                    'disp' => 'action',
                    'password' => 'true'
                )
            ),
            0, 1
        );
    
        $wuiVGroup->addChild($wuiUserGrid);
        $wuiVGroup->addChild(
            new WuiSubmit(
                'submit1',
                array(
                    'caption' => $this->localeCatalog->getStr('chpasswd_submit')
                )
            )
        );
    
        $wuiVGroup->addChild(new WuiHorizBar('horizbar1'));
        $wuiVGroup->addChild(
            new WuiLabel(
                'reqfieldslabel',
                array(
                    'label' => $this->localeCatalog->getStr('requiredfields_label')
                )
            )
        );
    
        $formEventsCall = new WuiEventsCall();
        $formEventsCall->addEvent(
            new WuiEvent(
                'action',
                'chpasswd',
                array(
                    'uid' => $eventData['userid']
                )
            )
        );
        $formEventsCall->addEvent(new WuiEvent('view', 'users', ''));
    
        $wuiForm = new WuiForm(
            'chpasswdform',
            array(
                'action' => $formEventsCall->getEventsCallString()
            )
        );
        $wuiForm->addChild($wuiVGroup);
    
        $this->wuiMainframe->addChild($wuiForm);
    
        $this->wuiTitlebar->mTitle.= ' - '.$userData['username'].' - '.$this->localeCatalog->getStr('chpasswd_title');
    }
    
    public function viewChprofile($eventData)
    {
        $userQuery = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->execute(
            'SELECT * FROM domain_users WHERE id='.$eventData['userid'].' '
        );
        $userData = $userQuery->getFields();
    
        $profQuery = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->execute(
            'SELECT * FROM domain_users_groups ORDER BY groupname'
        );
    
        $profiles = array();
        $profiles[0] = $this->localeCatalog->getStr('noprofileid_label');
        while (!$profQuery->eof) {
            $profData = $profQuery->getFields();
            $profiles[$profData['id']] = $profData['groupname'];
            $profQuery->moveNext();
        }
    
        $wuiVGroup = new WuiVertgroup('vgroup');
    
        $wuiUserGrid = new WuiGrid('chprofilegrid', array('rows' => '2', 'cols' => '2'));
    
        // User fields
        //
        $wuiUserGrid->addChild(
            new WuiLabel(
                'profilelabel',
                array(
                    'label' => $this->localeCatalog->getStr('changeprofile_label').' (*)'
                )
            ),
            0, 0
        );
        $wuiUserGrid->addChild(
            new WuiComboBox(
                'profileid',
                array(
                    'disp' => 'action',
                    'elements' => $profiles,
                    'default' => $userData['groupid']
                )
            ),
            0, 1
        );
    
        $wuiVGroup->addChild($wuiUserGrid);
        $wuiVGroup->addChild(
            new WuiSubmit(
                'submit1',
                array(
                    'caption' => $this->localeCatalog->getStr('chprofile_submit')
                )
            )
        );
    
        $wuiVGroup->addChild(new WuiHorizBar('horizbar1'));
        $wuiVGroup->addChild(
            new WuiLabel(
                'reqfieldslabel',
                array(
                    'label' => $this->localeCatalog->getStr('requiredfields_label')
                )
            )
        );
    
        $formEventsCall = new WuiEventsCall();
        $formEventsCall->addEvent(new WuiEvent('action', 'chprofile', array('uid' => $eventData['userid'])));
        $formEventsCall->addEvent(new WuiEvent('view', 'users', ''));
    
        $wuiForm = new WuiForm('chprofileform', array('action' => $formEventsCall->getEventsCallString()));
        $wuiForm->addChild($wuiVGroup);
    
        $this->wuiMainframe->addChild($wuiForm);
    
        $this->wuiTitlebar->mTitle.= ' - '.$userData['username'].' - '.$this->localeCatalog->getStr('chprofile_title');
    }
    
        public function viewMotd($eventData)
        {
            if (
            User::isAdminUser(
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserName(),
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDomainId()
            )
            ) {
            
            $domain = new \Innomatic\Domain\Domain(
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDomainId(),
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()
            );
    
            $xmlDef = '<vertgroup><name>motd</name>
          <children>
    
            <form><name>motd</name>
              <args>
                <method>post</method>
                <action type="encoded">'
                .urlencode(
                    WuiEventsCall::buildEventsCallString(
                        '',
                        array(
                            array('view', 'motd', ''),
                            array('action', 'setmotd', '')
                        )
                    )
                )
                .'</action>
              </args>
              <children>
    
                <grid><name>motd</name>
    
                  <children>
    
                    <label row="0" col="0" halign="" valign="top"><name>label</name>
                      <args>
                        <label type="encoded">'.urlencode($this->localeCatalog->getStr('motd.label')).'</label>
                      </args>
                    </label>
    
                    <text row="0" col="1"><name>motd</name>
                      <args>
                        <rows>10</rows>
                        <cols>80</cols>
                        <disp>action</disp>
                        <value type="encoded">'.urlencode($domain->getMotd()).'</value>
                      </args>
                    </text>
    
                  </children>
    
                </grid>
    
              </children>
            </form>
    
            <horizbar><name>hb</name></horizbar>
    
            <button>
              <name>apply</name>
              <args>
                <horiz>true</horiz>
                <frame>false</frame>
                <themeimage>buttonok</themeimage>
                <label type="encoded">'.urlencode($this->localeCatalog->getStr('set_motd.submit')).'</label>
                <formsubmit>motd</formsubmit>
                <action type="encoded">'
                        .urlencode(
                            WuiEventsCall::buildEventsCallString(
                                '',
                                array(
                                    array('view', 'motd', ''),
                                    array('action', 'setmotd', '')
                                )
                            )
                        )
                        .'</action>
              </args>
            </button>
    
          </children>
        </vertgroup>';
            $this->wuiMainframe->addChild(new WuiXml('page', array('definition' => $xmlDef)));
    
            $this->wuiTitlebar->mTitle.= ' - '.$this->localeCatalog->getStr('motd.title');
        }
    }
    
    public function viewHelp($eventData)
    {
        $this->wuiTitlebar->mTitle.= ' - '.$this->localeCatalog->getStr('help_title');
        $this->wuiMainframe->addChild(
            new WuiHelpNode(
                'profiles_help',
                array(
                    'base' => 'innomatic',
                    'node' => 'innomatic.domain.profiles.'.$eventData['node'].'.html',
                    'language' => \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getLanguage()
                )
            )
        );
    }
    
    public function viewRoles($eventData)
    {
        $rolesList = \Innomatic\Domain\User\Role::getAllRoles();
        print_r($rolesList);
        
        $permissionsList = \Innomatic\Domain\User\Permission::getAllPermissions();
        print_r($permissionsList);
    }
}
