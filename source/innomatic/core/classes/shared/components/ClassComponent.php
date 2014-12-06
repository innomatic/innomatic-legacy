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
 * @since      Class available since Release 5.0
 */
namespace Shared\Components;

/**
 * Class component handler.
 *
 * A class component is a PHP file containing a PHP class, to be deployed
 * in core/classes directory.
 *
 * @copyright  1999-2014 Innomatic Company
 * @license    http://www.innomatic.io/license/ New BSD License
 * @link       http://www.innomatic.io
 * @since      Class available since Release 5.0
 */
class ClassComponent extends \Innomatic\Application\ApplicationComponent
{
    public function __construct($rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
        // Checks if the classes folder exists
        if (! is_dir($this->container->getHome() . 'core/classes/')) {
            \Innomatic\Io\Filesystem\DirectoryUtils::mktree($this->container->getHome() . 'core/classes/', 0755);
            clearstatcache();
        }
        // Checks if the classes global override folder exists
        if (! is_dir($this->container->getHome() . 'core/overrides/classes/')) {
            \Innomatic\Io\Filesystem\DirectoryUtils::mktree($this->container->getHome() . 'core/overrides/classes/', 0755);
            clearstatcache();
        }
    }

    public static function getType()
    {
        return 'class';
    }

    public static function getPriority()
    {
        return 110;
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
            if (! file_exists($this->basedir . '/core/overrides/classes/' . $params['name'])) {
                return false;
            }
            switch ($params['override']) {
                case self::OVERRIDE_GLOBAL:
                    if (! file_exists($this->container->getHome() . 'core/overrides/classes/' . dirname($params['name']))) {
                        \Innomatic\Io\Filesystem\DirectoryUtils::mktree($this->container->getHome() . 'core/overrides/classes/' . dirname($params['name']) . '/', 0755);
                    }
                    return copy($this->basedir . '/core/overrides/classes/' . $params['name'], $this->container->getHome() . 'core/overrides/classes/' . $params['name']);
                    break;
                case self::OVERRIDE_DOMAIN:
                    if (! is_dir($this->container->getHome() . 'core/applications/' . $this->appname . '/overrides/classes/')) {
                        \Innomatic\Io\Filesystem\DirectoryUtils::mktree($this->container->getHome() . 'core/applications/' . $this->appname . '/overrides/classes/', 0755);
                        clearstatcache();
                    }
                    if (! file_exists($this->container->getHome() . 'core/applications/' . $this->appname . '/overrides/classes/' . dirname($params['name']))) {
                        \Innomatic\Io\Filesystem\DirectoryUtils::mktree($this->container->getHome() . 'core/applications/' . $this->appname . '/overrides/classes/' . dirname($params['name']) . '/', 0755);
                    }
                    return copy($this->basedir . '/core/overrides/classes/' . $params['name'], $this->container->getHome() . 'core/applications/' . $this->appname . '/overrides/classes/' . $params['name']);
                    break;
                default:
                    return false;
            }

            \Innomatic\Core\RootContainer::clearClassesCache();
        } else {
            if (! file_exists($this->basedir . '/core/classes/' . $params['name'])) {
                return false;
            }
            if (! file_exists($this->container->getHome() . 'core/classes/' . dirname($params['name']))) {
                \Innomatic\Io\Filesystem\DirectoryUtils::mktree($this->container->getHome() . 'core/classes/' . dirname($params['name']) . '/', 0755);
            }

            \Innomatic\Core\RootContainer::clearClassesCache();
            return copy($this->basedir . '/core/classes/' . $params['name'], $this->container->getHome() . 'core/classes/' . $params['name']);
        }
    }

    public function doUninstallAction($params)
    {
        $result = false;
        if (isset($params['override']) and ($params['override'] == self::OVERRIDE_DOMAIN or $params['override'] == self::OVERRIDE_GLOBAL)) {
            if (file_exists($this->container->getHome() . 'core/overrides/classes/' . $params['name'])) {
                \Innomatic\Core\RootContainer::clearClassesCache();
                $result = unlink($this->container->getHome() . 'core/overrides/classes/' . $params['name']);
            }
        } else {
            if (strlen($params['name'])) {
                if (file_exists($this->container->getHome() . 'core/classes/' . $params['name'])) {
                    \Innomatic\Core\RootContainer::clearClassesCache();
                    $result = unlink($this->container->getHome() . 'core/classes/' . $params['name']);
                    // TODO add removal of empty class directory
                }
            }
        }
        return $result;
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
        if (! is_dir($this->container->getHome() . 'core/domains/' . $domain_id . '/overrides/classes/')) {
            \Innomatic\Io\Filesystem\DirectoryUtils::mktree($this->container->getHome() . 'core/domains/' . $domain_id . '/overrides/classes/', 0755);
            clearstatcache();
        }
        if (! file_exists($this->container->getHome() . 'core/domains/' . $domain_id . '/overrides/classes/' . dirname($params['name']))) {
            \Innomatic\Io\Filesystem\DirectoryUtils::mktree($this->container->getHome() . 'core/domains/' . $domain_id . '/overrides/classes/' . dirname($params['name']) . '/', 0755);
        }

        \Innomatic\Core\RootContainer::clearClassesCache();
        return copy($this->container->getHome() . 'core/applications/' . $this->appname . '/overrides/classes/' . $params['name'], $this->container->getHome() . 'core/domains/' . $domain_id . '/overrides/classes/' . $params['name']);
    }

    /**
     * Used only when the component is a domain override.
     *
     * @param unknown_type $domainid
     * @param unknown_type $params
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
        if (file_exists($this->container->getHome() . 'core/domains/' . $domain_id . '/overrides/classes/' . $params['name'])) {
            \Innomatic\Core\RootContainer::clearClassesCache();
            return unlink($this->container->getHome() . 'core/domains/' . $domain_id . '/overrides/classes/' . $params['name']);
            // TODO add removal of empty class directory
        }
        return false;
    }

    /**
     * Used only when the component is a domain override.
     *
     * @param unknown_type $domainid
     * @param unknown_type $params
     * @return bool
     */
    public function doUpdateDomainAction($domainid, $params)
    {
        return $this->doEnableDomainAction();
    }
}
