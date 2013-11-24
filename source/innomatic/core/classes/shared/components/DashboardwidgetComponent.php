<?php
/**
 * Innomatic
 *
 * LICENSE 
 * 
 * This source file is subject to the new BSD license that is bundled 
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2013 Innoteam Srl
 * @license    http://www.innomatic.org/license/   BSD License
 * @link       http://www.innomatic.org
 * @since      Class available since Release 6.1
 */
require_once ('innomatic/application/ApplicationComponent.php');

/**
 * Dashboard widget component handler.
 */
class DashboardwidgetComponent extends ApplicationComponent
{
    public function DashboardwidgetComponent ($rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
    }
    
    public static function getType ()
    {
        return 'dashboardwidget';
    }
    
    public static function getPriority ()
    {
        return 0;
    }
    
    public static function getIsDomain ()
    {
        return true;
    }
    
    public static function getIsOverridable ()
    {
        return false;
    }
    
    public function doInstallAction ($args) {
        $result = false;
        $args['file'] = $this->basedir . '/core/classes/shared/dashboard/' . basename($args['file']);
        
        // Check if the shared dashboard widgets directory exists
        if (!is_dir(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/classes/shared/dashboard/')) {
        	require_once ('innomatic/io/filesystem/DirectoryUtils.php');
        	DirectoryUtils::mktree(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/classes/shared/dashboard/', 0755);
        }

        @copy($args['file'], InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/classes/shared/dashboard/' . basename($args['file']));
		@chmod(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/classes/shared/dashboard/' . basename($args['file']), 0644);
        return $result;
    }
    
    function doUninstallAction ($args)
    {
        $result = false;
        if (strlen($args['file'])) {
        	$args['file'] = $this->basedir . '/core/classes/shared/dashboard/' . basename($args['file']);
			@unlink(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/classes/shared/dashboard/' . $args['file']);
        }
        return true;
    }
    
    public function doUpdateAction ($args) {
        $result = false;
        if (strlen($args['file'])) {
	        $args['file'] = $this->basedir . '/core/classes/shared/dashboard/' . basename($args['file']);
	        if (file_exists($args['file'])) {
                @copy($args['file'], InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/classes/shared/dashboard/' . basename($args['file']));
                @chmod(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/classes/shared/dashboard/' . basename($args['file']), 0644);
    	    }
        }
        return $result;
    }
    
    public function doEnableDomainAction ($domainid, $params) {
    	$ins = 'INSERT INTO domain_dashboards_widgets VALUES (' . $this->domainda->getNextSequenceValue('domain_dashboards_widgets_id_seq') . ',';
    	$ins .= $this->domainda->formatText($params['name']) . ',';
    	$ins .= $this->domainda->formatText($grdata['panel']) . ',';
    	$ins .= $this->domainda->formatText($grdata['class']) . ',';
    	$ins .= $this->domainda->formatText($params['catalog']) . ',';
    	$ins .= $this->domainda->formatText($params['title']) . ',';
    	$ins .= $this->domainda->formatText($params['width']) . ')';
    	$result = $this->domainda->execute($ins);

    	return $result;
    }
    			
    public function doDisableDomainAction ($domainid, $params) {
		$this->domainda->execute(
'DELETE FROM domain_dashboards_widgets
WHERE name = ' . $this->domainda->formatText($params['name']).' LIMIT 1');
    	return true;
    }
    				
    public function doUpdateDomainAction ($domainid, $params) {
		$this->domainda->execute(
'UPDATE domain_dashboards_widgets SET
width=' . $this->domainda->formatText($params['width']) . ', panel=' . $this->domainda->formatText($params['panel']) . ', catalog=' . $this->domainda->formatText($params['catalog']) . ', class=' . $this->domainda->formatText($params['class']) . ', title=' . $this->domainda->formatText($params['title'])
.' WHERE name=' . $this->domainda->formatText($params['name']));

		return true;
    }
}
