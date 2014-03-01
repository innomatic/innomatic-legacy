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
        
        // Users bar
        //
        $wuiUsersToolBar = new WuiToolBar('userstoolbar');
        
        $usersAction = new WuiEventsCall();
        $usersAction->addEvent(new WuiEvent('view', 'default', ''));
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
        
        $permissionsAction = new WuiEventsCall();
        $permissionsAction->addEvent(new WuiEvent('view', 'permissions', ''));
        $wuiPermissionsButton = new WuiButton(
            'usersbutton',
            array(
                'label' => $this->localeCatalog->getStr('permissions_button'),
                'themeimage' => 'user',
                'horiz' => 'true',
                'action' => $permissionsAction->getEventsCallString()
            )
        );
        $wuiRolesToolBar->addChild($wuiPermissionsButton);
        
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
                
                // Profiles bar
                //
                $wuiProfilesToolBar = new WuiToolBar('profilestoolbar');
                
        $homeAction = new WuiEventsCall();
                $homeAction->addEvent(new WuiEvent('view', 'profiles', ''));
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
        
        $wuiToolBarFrame->addChild($wuiUsersToolBar);
        $wuiToolBarFrame->addChild($wuiRolesToolBar);
        $wuiToolBarFrame->addChild($wuiProfilesToolBar);
        
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
        
        $this->wuiMainframe = new WuiHorizgroup('mainframe');
        $this->wuiMainstatus = new WuiStatusbar('mainstatusbar');
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
    
    public function viewProfiles($eventData)
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
        $formEventsCall->addEvent(new WuiEvent('view', 'profiles', ''));
    
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
                'profiles',
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
    
        $remove2Action = new WuiEventSCall();
        $remove2Action->addEvent(
            new WuiEvent(
                'view',
                'profiles',
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
    
        $dontRemoveAction = new WuiEventsCall();
        $dontRemoveAction->addEvent(
            new WuiEvent(
                'view',
                'profiles',
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
        $okAction->addEvent(new WuiEvent('view', 'profiles', ''));
    
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
        $formEventsCall->addEvent(new WuiEvent('view', 'profiles', ''));
    
        $wuiForm = new WuiForm('renameprofileform', array('action' => $formEventsCall->getEventsCallString()));
        $wuiForm->addChild($wuiVGroup);
    
        $this->wuiMainframe->addChild($wuiForm);
    
        $this->wuiTitlebar->mTitle.= ' - '.$profData['groupname'].' - '.$this->localeCatalog->getStr('renameprofile_title');
    }
    
    public function viewDefault($eventData)
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
    
                $chDataAction[$row] = new WuiEventsCall();
                $chDataAction[$row]->addEvent(new WuiEvent('view', 'edituser', array('userid' => $userData['id'])));
                    
                $wuiUsersTable->addChild(
                    new WuiLink(
                        'usernamelabel'.$row,
                        array('label' => $userData['username'], 'link' => $chDataAction[$row]->getEventsCallString())
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
                'default',
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
        $removeAction->addEvent(new WuiEvent('view', 'default', ''));
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
        $dontRemoveAction->addEvent(new WuiEvent('view', 'default', ''));
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
        $okAction->addEvent(new WuiEvent('view', 'profiles', ''));
    
        $wuiOkForm = new WuiForm('okform', array('action'));
    
        $wuiVGroup->addChild($wuiHGroup2);
    
        $this->wuiMainframe->addChild($wuiVGroup);
    
        $this->wuiTitlebar->mTitle.= ' - '.$userData['username'].' - '.$this->localeCatalog->getStr('removeuser_title');
    }
    
    public function viewEdituser($eventData)
    {
        $domainDa = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess();
        
        $userQuery = $domainDa->execute('SELECT * FROM domain_users WHERE id='.$eventData['userid']);
        $userData = $userQuery->getFields();
        
        $user = new \Innomatic\Domain\User\User(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->domainserial,
            $eventData['userid']
        );
        $userRolesQuery = $user->getAllRoles();
        $userRoles = array();
        
        while (!$userRolesQuery->eof) {
            $userRoles[$userRolesQuery->getFields('id')] = true;
            $userRolesQuery->moveNext();
        }
        
        $roles = \Innomatic\Domain\User\Role::getAllRoles();
    
        // Build profiles list
        $profQuery = $domainDa->execute('SELECT * FROM domain_users_groups');
        $profiles = array();
        $profiles[0] = $this->localeCatalog->getStr('noprofileid_label');
        while (!$profQuery->eof) {
            $profData = $profQuery->getFields();
            $profiles[$profData['id']] = $profData['groupname'];
            $profQuery->moveNext();
        }
    
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
        $formEventsCall->addEvent(new WuiEvent('view', 'default', ''));
    
        $xml = '<vertgroup><children>
            <form><name>userdata</name>
              <args>
                <action>'.WuiXml::cdata($formEventsCall->getEventsCallString()).'</action>
              </args>
              <children>
              <vertgroup><children>
                <vertgroup><children>
                    <label><args><bold>true</bold><label>'.WuiXml::cdata($this->localeCatalog->getStr('userdata_label')).'</label></args></label>
                <grid>
                  <children>
                    <label row="0" col="0"><args><label>'.WuiXml::cdata($this->localeCatalog->getStr('userfname_label')).'</label></args></label>
                    <string row="0" col="1"><name>fname</name><args><disp>action</disp><value>'.WuiXml::cdata($userData['fname']).'</value><size>20</size></args></string>
                    <label row="1" col="0"><args><label>'.WuiXml::cdata($this->localeCatalog->getStr('userlname_label')).'</label></args></label>
                    <string row="1" col="1"><name>lname</name><args><disp>action</disp><value>'.WuiXml::cdata($userData['lname']).'</value><size>20</size></args></string>
                    <label row="2" col="0"><args><label>'.WuiXml::cdata($this->localeCatalog->getStr('email_label')).'</label></args></label>
                    <string row="2" col="1"><name>email</name><args><disp>action</disp><value>'.WuiXml::cdata($userData['email']).'</value><size>30</size></args></string>
                    <label row="3" col="0"><args><label>'.WuiXml::cdata($this->localeCatalog->getStr('changeprofile_label')).'</label></args></label>
                    <combobox row="3" col="1"><name>profileid</name><args><disp>action</disp><default>'.WuiXml::cdata($userData['groupid']).'</default><elements type="array">'.WuiXml::encode($profiles).'</elements></args></combobox>
                    <label row="4" col="0"><args><label>'.WuiXml::cdata($this->localeCatalog->getStr('userother_label')).'</label></args></label>
                    <text row="4" col="1"><name>other</name><args><disp>action</disp><value>'.WuiXml::cdata($userData['otherdata']).'</value><rows>6</rows><cols>50</cols></args></text>
                        
                  </children>
                </grid>
                </children></vertgroup>

                <horizbar />

                <vertgroup><children>
                    <label><args><bold>true</bold><label>'.WuiXml::cdata($this->localeCatalog->getStr('userroles_label')).'</label></args></label>
                    <grid><children>';
        
        $row = 0;
        $col = 0;
        foreach ($roles as $roleId => $roleData) {
            $xml .= '<checkbox row="'.$row.'" col="'.$col++.'"><name>role_'.$roleId.'</name><args><disp>action</disp><checked>'.(isset($userRoles[$roleId]) ? 'true' : 'false').'</checked></args></checkbox>
                <label row="'.$row.'" col="'.$col++.'"><args><label>'.WuiXml::cdata($roleData['title']).'</label></args></label>';
            if ($col == 8) {
                $col = 0;
                $row++;
            }
        }
        
        
        $xml .= '</children></grid>
                 </children></vertgroup>
                        
                </children></vertgroup>
                <horizbar />
                        
                <horizgroup><children>
                  <button><name>save</name>
	                <args>
                      <themeimage>buttonok</themeimage>
                      <label>'.WuiXml::cdata($this->localeCatalog->getStr('edituser_submit')).'</label>
                      <action>'.WuiXml::cdata($formEventsCall->getEventsCallString()).'</action>
                      <formsubmit>userdata</formsubmit>
                      <horiz>true</horiz>
                      <frame>false</frame>
                    </args>
                  </button>
                </children></horizgroup>
              </children>
            </form>
            </children></vertgroup>';
        
        $this->wuiMainframe->addChild(new WuiXml('user', array('definition' => $xml)));
    
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
        $formEventsCall->addEvent(new WuiEvent('view', 'default', ''));
    
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
    
    public function viewPermissions($eventData)
    {
        $this->wuiMainframe->addChild(new WuiXml('rolespermissions', array('definition' => '<divframe><args><id>roleslist</id></args><children>'.$this->_controller->getRolesPermissionsXml().'</children></divframe>')));
    }
    
    public function viewRoles($eventData)
    {
        $roles = \Innomatic\Domain\User\Role::getAllRoles();
        $row = 0;
        
        $headers[0]['label'] = $this->localeCatalog->getStr('rolename_header');
        $headers[1]['label'] = $this->localeCatalog->getStr('roledescription_header');
        
        $xml = '<table><args><headers type="array">'.WuiXml::encode($headers).'</headers></args><children>';
        
        foreach ($roles as $roleId => $roleData) {
            $toolsArray = array();
            // Roles defined by applications are not editable
            if (!strlen($roleData['application'])) {
                $toolsArray['edit'] = array(
                    'label' => $this->localeCatalog->getStr('editrole_button'),
                    'themeimage' => 'pencil',
                    'horiz' => 'true',
                    'action' => \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('', array(array(
                        'view',
                        'editrole',
                        array('id' => $roleId))))
                    );
            }
            $toolsArray['trash'] = array(
                'label' => $this->localeCatalog->getStr('removerole_button'),
                'themeimage' => 'trash',
                'horiz' => 'true',
                'needconfirm' => 'true',
                'confirmmessage' => $this->localeCatalog->getStr('removerole_confirm'),
                'action' => \Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString('', array(
                    array('view', 'roles'),
                    array('action', 'removerole', array('id' => $roleId))))
                );
            
            $xml .= '<label row="'.$row.'" col="0"><args><label>'.WuiXml::cdata($roleData['title']).'</label></args></label>
                <label row="'.$row.'" col="1"><args><label>'.WuiXml::cdata($roleData['description']).'</label><nowrap>false</nowrap></args></label>
                    <innomatictoolbar row="'.$row.'" col="2"><name>tools</name>
  <args>
    <frame>false</frame>
    <toolbars type="array">'.WuiXml::encode(array(
                'view' => $toolsArray)).'</toolbars>
  </args>
</innomatictoolbar>';
            $row++;
        }
            
        $xml .= '</children></table>';
        $this->wuiMainframe->addChild(new WuiXml('newrole', array('definition' => $xml)));
    }
    
    public function viewNewrole($eventData)
    {
        $xml = '<vertgroup><children>
            <form><name>newrole</name>
              <args><action>'.WuiXml::cdata(
    	      		\Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString(
    	      				'',
    	      				array(
    	      					array('view', 'roles', array()),
    	      				    array('action', 'addrole', array())
    	      				)
    	      		)
    	      ).'</action></args>
              <children>
                <grid><children>
                  <label row="0" col="0"><args><label>'.WuiXml::cdata($this->localeCatalog->getStr('rolename_label')).'</label></args></label>
                  <string row="0" col="1"><name>name</name><args><disp>action</disp><size>25</size></args></string>
                  <label row="1" col="0"><args><label>'.WuiXml::cdata($this->localeCatalog->getStr('roledescription_label')).'</label></args></label>
                  <text row="1" col="1"><name>description</name><args><disp>action</disp><cols>40</cols><rows>5</rows></args></text>
                  </children></grid>
            </children></form>
            <horizbar/>
            <horizgroup><children>
              <button><name>save</name>
                <args>
                  <themeimage>buttonok</themeimage>
                  <label>'.WuiXml::cdata($this->localeCatalog->getStr('newrole_button')).'</label>
                  <action>'.WuiXml::cdata(
    	      		\Innomatic\Wui\Dispatch\WuiEventsCall::buildEventsCallString(
    	      				'',
    	      				array(
    	      					array('view', 'roles', array()),
    	      				    array('action', 'addrole', array())
    	      				)
    	      		)
    	      ).'</action>
                  <formsubmit>newrole</formsubmit>
                  <horiz>true</horiz>
                  <frame>false</frame>
                </args>
              </button>
            </children></horizgroup>
            </children></vertgroup>';
        $this->wuiMainframe->addChild(new WuiXml('newrole', array('definition' => $xml)));
    }
    
    public function viewEditrole($eventData)
    {
        $role = new \Innomatic\Domain\User\Role((int)$eventData['id']);

        $formEventsCall = new WuiEventsCall();
        $formEventsCall->addEvent(new WuiEvent('action', 'editrole', array('id' => $eventData['id'])));
        $formEventsCall->addEvent(new WuiEvent('view', 'roles', ''));
    
        $xml = '<vertgroup><children>
            <form><name>roledata</name>
              <args>
                <action>'.WuiXml::cdata($formEventsCall->getEventsCallString()).'</action>
              </args>
              <children>
              <vertgroup><children>
                <vertgroup><children>
                <grid>
                  <children>
                    <label row="0" col="0"><args><label>'.WuiXml::cdata($this->localeCatalog->getStr('rolename_label')).'</label></args></label>
                    <string row="0" col="1"><name>name</name><args><disp>action</disp><value>'.WuiXml::cdata($role->getName()).'</value><size>25</size></args></string>
                    <label row="1" col="0"><args><label>'.WuiXml::cdata($this->localeCatalog->getStr('roledescription_label')).'</label></args></label>
                    <text row="1" col="1"><name>description</name><args><disp>action</disp><value>'.WuiXml::cdata($role->getDescription()).'</value><rows>5</rows><cols>40</cols></args></text>
    
                  </children>
                </grid>
                </children></vertgroup>
    
                </children></vertgroup>
                <horizbar />
    
                <horizgroup><children>
                  <button><name>save</name>
	                <args>
                      <themeimage>buttonok</themeimage>
                      <label>'.WuiXml::cdata($this->localeCatalog->getStr('saverole_button')).'</label>
                      <action>'.WuiXml::cdata($formEventsCall->getEventsCallString()).'</action>
                      <formsubmit>roledata</formsubmit>
                      <horiz>true</horiz>
                      <frame>false</frame>
                    </args>
                  </button>
                </children></horizgroup>
              </children>
            </form>
            </children></vertgroup>';
    
        $this->wuiMainframe->addChild(new WuiXml('user', array('definition' => $xml)));
    
        $this->wuiTitlebar->mTitle.= ' - '.$userData['username'].' - '.$this->localeCatalog->getStr('edituser_title');
    }
}
