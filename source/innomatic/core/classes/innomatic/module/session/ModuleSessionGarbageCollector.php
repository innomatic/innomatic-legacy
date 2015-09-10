<?php
namespace Innomatic\Module\Session;

use \Innomatic\Module\Server;

/**
 * Collects garbage sessions file and removes them.
 *
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 * @copyright Copyright 2004-2014 Innoteam Srl
 * @since 5.1
 */
class ModuleSessionGarbageCollector
{
    /**
     * Executes garbage collection.
     *
     * @since 5.1
     * @return void
     */
    public static function clean()
    {
        // Obtains Modules list.
        $context = ModuleServerContext::instance('\Innomatic\Module\Server\ModuleServerContext');
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
