<?php
/**
 * Innomatic
 *
 * LICENSE 
 * 
 * This source file is subject to the new BSD license that is bundled 
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2012 Innoteam Srl
 * @license    http://www.innomatic.org/license/   BSD License
 * @link       http://www.innomatic.org
 * @since      Class available since Release 5.0
*/

require_once('innomatic/maintenance/MaintenanceTask.php');

class InnomaticRequirementsMaintenance extends MaintenanceTask {
    public function execute() {
        $result = true;

        // TODO Update version check
        // PHP version check
        if (!(ereg("[4-9]\.[1-9]\.[0-9].*", phpversion()) or ereg("[5-9]\.[0-9]\.[0-9].*", phpversion()))) $result = false;

        // File upload support
        if (!(ini_get('file_uploads') == '1')) $result = false;

        // XML support
        if (!function_exists('xml_set_object')) $result = false;

        // Zlib support
        if (!function_exists('gzinflate')) $result = false;

        // Database support
        if (!(function_exists('mysql_connect') or function_exists('pg_connect'))) $result = false;

        // Applications extensions
        $app_deps = InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->execute(
            'SELECT moddep FROM applications_dependencies WHERE moddep LIKE '
            . InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->formatText('%.extension'));

        while (!$app_deps->eof)
        {
            $dep = substr($app_deps->getFields('moddep'), 0, -10);
            if (!extension_loaded($dep)) $result = false;

            $app_deps->moveNext();
        }

        return $result;
    }

}
