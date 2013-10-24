<?php    

require_once('innomatic/module/server/ModuleServerContext.php');

/**
 * Collects garbage sessions file and removes them.
 *
 * @author Alex Pagnoni <alex.pagnoni@innoteam.it>
 * @copyright Copyright 2004-2013 Innoteam S.r.l.
 * @since 5.1
 */
class ModuleSessionGarbageCollector {
    /**
     * Executes garbage collection.
     *
     * @access public
     * @since 5.1
     * @return void
     */
	public static function clean() {
        // Obtains Modules list.
		$context = ModuleServerContext::instance('ModuleServerContext');
		$module_list = $context->getModuleList();

        // Cleans session files for each Module context.
		foreach ($module_list as $module) {
			if (!file_exists($context->getHome().'modules/'.$module.'/sessions')) {
				continue;
			}

			if (!$dh = opendir($context->getHome().'modules/'.$module.'/sessions/')) {
				continue;
			}

			while (($file = readdir($dh)) !== false) {
				if ($file != '.' and $file != '..' and is_file($context->getHome().'modules/'.$module.'/sessions/'.$file)) {
					unlink($context->getHome().'modules/'.$module.'/sessions/'.$file);
				}
			}
			closedir($dh);
		}
	}
}

?>