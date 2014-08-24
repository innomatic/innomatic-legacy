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
 * @license    http://www.innomaticplatform.com/license/   BSD License
 * @link       http://www.innomaticplatform.com
 * @since      Class available since Release 6.4.0
 */
namespace Shared\Components;

/**
 * Traybar item component handler.
 */
class TraybaritemComponent extends \Innomatic\Application\ApplicationComponent
{
    public function __construct($rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
    }

    public static function getType()
    {
        return 'traybaritem';
    }

    public static function getPriority()
    {
        return 0;
    }

    public static function getIsDomain()
    {
        return true;
    }

    public static function getIsOverridable()
    {
        return false;
    }

    public function doInstallAction($args)
    {
        $args['file'] = $this->basedir . '/core/classes/shared/traybar/' . basename($args['file']);

        // Check if the shared traybar items directory exists
        if (!is_dir(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/classes/shared/traybar/')) {
            \Innomatic\Io\Filesystem\DirectoryUtils::mktree(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/classes/shared/traybar/', 0755);
        }

        @copy($args['file'], \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/classes/shared/traybar/' . basename($args['file']));
        @chmod(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/classes/shared/traybar/' . basename($args['file']), 0644);
        return true;
    }

    public function doUninstallAction($args)
    {
        if (strlen($args['file'])) {
            $args['file'] = $this->basedir . '/core/classes/shared/traybar/' . basename($args['file']);
            @unlink(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/classes/shared/traybar/' . $args['file']);
        }
        return true;
    }

    public function doUpdateAction($args)
    {
        if (strlen($args['file'])) {
            $args['file'] = $this->basedir . '/core/classes/shared/traybar/' . basename($args['file']);
            if (file_exists($args['file'])) {
                @copy($args['file'], \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/classes/shared/traybar/' . basename($args['file']));
                @chmod(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/classes/shared/traybar/' . basename($args['file']), 0644);
            }
        }
        return true;
    }

    public function doEnableDomainAction($domainid, $params)
    {
        $ins = 'INSERT INTO domain_traybar_items VALUES (' . $this->domainda->getNextSequenceValue('domain_traybar_items_id_seq') . ',';
        $ins .= $this->domainda->formatText($params['name']) . ',';
        $ins .= $this->domainda->formatText($params['panel']) . ',';
        $ins .= $this->domainda->formatText($params['class']) . ',';
        $ins .= $this->domainda->formatText($params['file']) . ')';
        $result = $this->domainda->execute($ins);

        return true;
    }

    public function doDisableDomainAction($domainid, $params)
    {
        $this->domainda->execute(
'DELETE FROM domain_traybar_items
WHERE name = ' . $this->domainda->formatText($params['name']).' LIMIT 1');
        return true;
    }

    public function doUpdateDomainAction($domainid, $params)
    {
        $check_query = $this->domainda->execute('SELECT id FROM domain_traybar_items WHERE name=' . $this->domainda->formatText($params['name']));
        if ($check_query->getNumberRows() > 0) {
            $this->domainda->execute(
                    'UPDATE domain_traybar_items SET
file=' . $this->domainda->formatText($params['file']) . ', panel=' . $this->domainda->formatText($params['panel']) . ', class=' . $this->domainda->formatText($params['class'])
                    .' WHERE name=' . $this->domainda->formatText($params['name']));
            return true;
        } else {
            return $this->doEnableDomainAction($domainid, $params);
        }
    }
}
