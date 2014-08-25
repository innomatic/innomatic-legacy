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
 * @since      Class available since Release 6.4.0
*/

class ProfilesPanelController extends \Innomatic\Desktop\Panel\PanelController
{
    public function update($observable, $arg = '')
    {
    }
    
    public static function getRolesPermissionsXml()
    {
        $localeCatalog = new \Innomatic\Locale\LocaleCatalog(
            'innomatic::domain_profiles',
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getLanguage()
        );
        
        $rolesList = \Innomatic\Domain\User\Role::getAllRoles();
        $rolesCount = count($rolesList);
        
        // Build roles/permissions matrix
        $rolesPermissions = array();
        foreach ($rolesList as $roleId => $roleData) {
            $role = new \Innomatic\Domain\User\Role($roleId);
            $rolesPermissions[$roleId] = $role->getPermissions();
        }
        
        $permissionsList = \Innomatic\Domain\User\Permission::getAllPermissions();
        
        // Build table headers
        $headers = array();
        $headerCounter = 1;
        foreach ($rolesList as $roleId => $roleData) {
            $headers[$headerCounter++]['label'] = $roleData['title'];
        }
        
        $xml = '<vertgroup><children>
              <table>
                <args>
                  <headers type="array">'.WuiXml::encode($headers).'</headers>
                </args>
                <children>';
        
        $row = 0;
        $prevApplication = '';
        
        foreach ($permissionsList as $permId => $permData) {
            if ($permData['application'] != $prevApplication) {
                $xml .= '<label row="'.$row++.'" col="0" halign="left" valign="middle" nowrap="false" width="" colspan="'.($rolesCount+1).'"><args><label>'.WuiXml::cdata($permData['application']).'</label><bold>true</bold></args></label>';
            }
            
            $xml .= '<label row="'.$row.'" col="0" halign="left" valign="middle"><args><label>'.WuiXml::cdata($permData['title']).'</label><nowrap>false</nowrap></args></label>';
            $col = 1;
            
            foreach ($rolesList as $roleId => $roleData) {
                if (isset($rolesPermissions[$roleId][$permId])) {
                    $checked = 'true';
                } else {
                    $checked = 'false';
                }
                
                $xml .= '<checkbox row="'.$row.'" col="'.$col++.'" halign="center" valign="middle"><name>rolepermissioncb</name><args><id>cbrole_'.$roleId.'-'.$permId.'</id><checked>'.$checked.'</checked></args></checkbox>';
            }
            
            $prevApplication = $permData['application'];
            $row++;
        }
        
        $xml .= '</children>
              </table>
              <horizbar/>
              <button><name>save</name>
	<args><themeimage>buttonok</themeimage><label>'.WuiXml::cdata($localeCatalog->getStr('save_roles_permissions_button')).'</label><action>javascript:void(0)</action><horiz>true</horiz><frame>false</frame></args>
  	<events>
    	<click>'.WuiXml::cdata("
var checkboxes = document.getElementsByName('wui[][evd][rolepermissioncb]');
var vals = '';
for (var i=0, n=checkboxes.length;i<n;i++) {
  if (checkboxes[i].checked) vals += ','+checkboxes[i].id;
}
if (vals) vals = vals.substring(1);
    			
xajax_SaveRolesPermissions(vals);").'</click>
  	</events></button>
            </children></vertgroup>';
        
        return $xml;
    }
}
