<?php
/**
 * Innomatic
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2014 Innomatic Company
 * @license    http://www.innomatic.io/license/ New BSD License
 * @link       http://www.innomatic.io
 */
require_once 'scripts_container.php';
$script = \Innomatic\Scripts\ScriptContainer::instance('\Innomatic\Scripts\ScriptContainer');

if (!isset($argv[1])) {
    $argv[1] = '';
}

try {
    switch ($argv[1]) {
        case '-h':
            print('Usage: php innomatic/core/scripts/tenant.php command argument' . "\n");
            print("\n");
            print('Supported commands:' . "\n");
            print('    create tenantname description pwd  Creates a new tenant' . "\n");
            print('    remove tenantname                  Removes a tenant' . "\n");
            print('    enable tenantname                  Enables a disabled tenant' . "\n");
            print('    disable tenantname                 Disables a tenant' . "\n");
            print('    applist tenantname                 Retrieves a list of enabled applications' . "\n");
            print('    appenable tenantname application   Disables a tenant' . "\n");
            print('    appdisable tenantname application  Disables a tenant' . "\n");
            $script->cleanExit();
            break;
        
        case 'enable':
            $tenant = new \Innomatic\Domain\Domain(InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(), $argv[2], null);
            if ($tenant->enable()) {
                print("Tenant $argv[2] enabled\n");
                $script->cleanExit();
            } else {
                print("Tenant $argv[2] not enabled\n");
                $script->cleanExit(1);
            }
            break;
        
        case 'disable':
            $tenant = new \Innomatic\Domain\Domain(InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(), $argv[2], null);
            if ($tenant->disable()) {
                print("Tenant $argv[2] disabled\n");
                $script->cleanExit();
            } else {
                print("Tenant $argv[2] not disabled\n");
                $script->cleanExit(1);
            }
            break;
        
        case 'create':
            $tenant = new \Innomatic\Domain\Domain(InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(), 0, null);
            $data['domainid'] = $argv[2];
            $data['domainname'] = $argv[3];
            $data['domainpassword'] = $argv[4];
            
            if ($tenant->create($data)) {
                print("Tenant $argv[2] created\n");
                $script->cleanExit();
            } else {
                print("Tenant $argv[2] not created\n");
                $script->cleanExit(1);
            }
            break;
        
        case 'remove':
            $tenant = new \Innomatic\Domain\Domain(InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(), $argv[2], null);
            if ($tenant->remove()) {
                print("Tenant $argv[2] removed\n");
                $script->cleanExit();
            } else {
                print("Tenant $argv[2] not removed\n");
                $script->cleanExit(1);
            }
            break;
        
        case 'applist':
            $tenant = new \Innomatic\Domain\Domain(InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(), $argv[2], null);
            $list = $tenant->getEnabledApplications();
            foreach ($list as $app) {
                print($app . "\n");
            }
            $script->cleanExit();
            break;
        
        case 'appenable':
            $tenant = new \Innomatic\Domain\Domain(InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(), $argv[2], null);
            $appid = \Innomatic\Application\Application::getAppIdFromName($argv[3]);
            if ($tenant->enableApplication($appid)) {
                print("Application $argv[3] enabled to tenant $argv[2]\n");
                $script->cleanExit();
            } else {
                print("Application $argv[3] not enabled to tenant $argv[2]\n");
                $script->cleanExit(1);
            }
            break;
        
        case 'appdisable':
            $tenant = new \Innomatic\Domain\Domain(InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(), $argv[2], null);
            $appid = \Innomatic\Application\Application::getAppIdFromName($argv[3]);
            if ($tenant->disableApplication($appid)) {
                print("Application $argv[3] disabled from tenant $argv[2]\n");
                $script->cleanExit();
            } else {
                print("Application $argv[3] not disabled from tenant $argv[2]\n");
                $script->cleanExit(1);
            }
            break;
        
        default:
            print('Usage: php innomatic/core/scripts/tenant.php command' . "\n");
            print('Type tenant.php -h for a list of supported commands' . "\n");
    }
} catch (\Exception $e) {
    echo $e;
}

$script->cleanExit();
