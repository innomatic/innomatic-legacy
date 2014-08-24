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
 */
namespace Shared\Components;

use \Innomatic\Core;
use \Innomatic\Io\Filesystem;

/**
 * Unit test component handler.
 *
 * A unit test component is a PHP file containing a PHPUnit test file, to be
 * deployed in core/classes directory (usually inside a tests subdirectory).
 *
 * @since 6.4.0 introduced
 */
class UnittestComponent extends \Innomatic\Application\ApplicationComponent
{

    public function __construct($rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
        // Checks if the classes folder exists
        if (! is_dir(InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/classes/')) {
            DirectoryUtils::mktree(InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/classes/', 0755);
            clearstatcache();
        }
        // Checks if the classes global override folder exists
        if (! is_dir(InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/overrides/classes/')) {
            DirectoryUtils::mktree(InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/overrides/classes/', 0755);
            clearstatcache();
        }
    }

    public static function getType()
    {
        return 'unittest';
    }

    public static function getPriority()
    {
        return 0;
    }

    public static function getIsDomain()
    {
        return false;
    }

    public static function getIsOverridable()
    {
        return true;
    }

    public function doInstallAction($params)
    {
        if (! strlen($params['name'])) {
            return false;
        }
        
        if (isset($params['override']) and ($params['override'] == self::OVERRIDE_DOMAIN or $params['override'] == self::OVERRIDE_GLOBAL)) {
            if (! file_exists("{$this->basedir}/core/overrides/classes/{$params['name']}")) {
                return false;
            }
            switch ($params['override']) {
                case self::OVERRIDE_GLOBAL:
                    if (! file_exists(InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/overrides/classes/' . dirname($params['name']))) {
                        DirectoryUtils::mktree(InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/overrides/classes/' . dirname($params['name']) . '/', 0755);
                    }
                    return copy($this->basedir . '/core/overrides/classes/' . $params['name'], InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/overrides/classes/' . $params['name']);
                    break;
                case self::OVERRIDE_DOMAIN:
                    if (! is_dir(InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/applications/' . $this->appname . '/overrides/classes/')) {
                        DirectoryUtils::mktree(InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/applications/' . $this->appname . '/overrides/classes/', 0755);
                        clearstatcache();
                    }
                    if (! file_exists(InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/applications/' . $this->appname . '/overrides/classes/' . dirname($params['name']))) {
                        DirectoryUtils::mktree(InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/applications/' . $this->appname . '/overrides/classes/' . dirname($params['name']) . '/', 0755);
                    }
                    return copy($this->basedir . '/core/overrides/classes/' . $params['name'], InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/applications/' . $this->appname . '/overrides/classes/' . $params['name']);
                    break;
                default:
                    return false;
            }
        } else {
            if (! file_exists($this->basedir . '/core/classes/' . $params['name'])) {
                return false;
            }
            if (! file_exists(InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/classes/' . dirname($params['name']))) {
                DirectoryUtils::mktree(InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/classes/' . dirname($params['name']) . '/', 0755);
            }
            return copy($this->basedir . '/core/classes/' . $params['name'], InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/classes/' . $params['name']);
        }
    }

    public function doUninstallAction($params)
    {
        if (isset($params['override']) and ($params['override'] == self::OVERRIDE_DOMAIN or $params['override'] == self::OVERRIDE_GLOBAL)) {
            if (file_exists(InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/overrides/classes/' . $params['name'])) {
                return unlink(InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/overrides/classes/' . $params['name']);
            }
        } else {
            if (strlen($params['name'])) {
                if (file_exists(InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/classes/' . $params['name'])) {
                    return unlink(InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/classes/' . $params['name']);
                    // TODO add removal of empty class directory
                }
            }
        }
        return false;
    }

    public function doUpdateAction($params)
    {
        $result = $this->doInstallAction($params);
    }

    /**
     * Used only when the component is a domain override.
     *
     * @param unknown_type $domainid            
     * @param unknown_type $params            
     * @return bool
     */
    public function doEnableDomainAction($domainid, $params)
    {
        if (! strlen($params['name'])) {
            return false;
        }
        if (! isset($params['override']) and $params['override'] = self::OVERRIDE_DOMAIN) {
            return true;
        }
        $domain_query = $this->rootda->execute('SELECT domainid FROM domains WHERE id=' . $domainid);
        if ($domain_query == false or $domain_query->getNumberRows() == 0) {
            return false;
        }
        $domain_id = $domain_query->getFields('domainid');
        // Checks if the classes override directory exists
        if (! is_dir(InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/domains/' . $domain_id . '/overrides/classes/')) {
            DirectoryUtils::mktree(InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/domains/' . $domain_id . '/overrides/classes/', 0755);
            clearstatcache();
        }
        if (! file_exists(InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/domains/' . $domain_id . '/overrides/classes/' . dirname($params['name']))) {
            DirectoryUtils::mktree(InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/domains/' . $domain_id . '/overrides/classes/' . dirname($params['name']) . '/', 0755);
        }
        return copy(InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/applications/' . $this->appname . '/overrides/classes/' . $params['name'], InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/domains/' . $domain_id . '/overrides/classes/' . $params['name']);
    }

    /**
     * Used only when the component is a domain override.
     *
     * @param string $domainid            
     * @param array $params            
     * @return bool
     */
    public function doDisableDomainAction($domainid, $params)
    {
        if (! strlen($params['name'])) {
            return false;
        }
        if (! isset($params['override']) and $params['override'] = self::OVERRIDE_DOMAIN) {
            return true;
        }
        $domain_query = $this->rootda->execute('SELECT domainid FROM domains WHERE id=' . $domainid);
        if ($domain_query == false or $domain_query->getNumberRows() == 0) {
            return false;
        }
        $domain_id = $domain_query->getFields('domainid');
        if (file_exists(InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/domains/' . $domain_id . '/overrides/classes/' . $params['name'])) {
            return unlink(InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/domains/' . $domain_id . '/overrides/classes/' . $params['name']);
            // TODO add removal of empty class directory
        }
        return false;
    }

    /**
     * Used only when the component is a domain override.
     *
     * @param string $domainid            
     * @param array $params            
     * @return bool
     */
    public function doUpdateDomainAction($domainid, $params)
    {
        return $this->doEnableDomainAction();
    }
}
