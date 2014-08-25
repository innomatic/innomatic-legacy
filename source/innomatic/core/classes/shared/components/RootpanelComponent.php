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
 * @since      Class available since Release 5.0
 */
namespace Shared\Components;

/**
 * Rootpanel component handler.
 */
class RootpanelComponent extends \Innomatic\Application\ApplicationComponent
{
    public function __construct($rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
    }
    public static function getType()
    {
        return 'rootpanel';
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
        return false;
    }
    public function doInstallAction($params)
    {
        $result = false;
        $name = $params['name'];
        if (! isset($params['icon']))
            $params['icon'] = '';
        if (! isset($params['catalog']))
            $params['catalog'] = '';
        if (! isset($params['themeicon']))
            $params['themeicon'] = '';
        if (! isset($params['themeicontype']))
            $params['themeicontype'] = '';
        if (strlen($params['name'])) {
            if (strlen($params['icon'])) {
                $params['icon'] = $this->basedir . '/root/' . $params['icon'];
                if (@copy($params['icon'], \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'root/' . basename($params['icon']))) {
                    @chmod(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'root/' . basename($params['icon']), 0644);
                    $result = true;
                } else
                    $this->mLog->logEvent('innomatic.rootpanelcomponent.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to copy root application icon ' . $params['icon'] . ' to destination ' . \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'root/' . basename($params['icon']), \Innomatic\Logging\Logger::ERROR);
            }
            $params['name'] = $this->basedir . '/root/' . $params['name'];
            $result = false;
            if (is_dir($params['name'] . '-panel')) {
                if (\Innomatic\Io\Filesystem\DirectoryUtils::dirCopy($params['name'] . '-panel/', \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'root/' . basename($params['name']) . '-panel/')) {
                    $result = true;
                }
            } else
                if (file_exists($params['name'] . '.php')) {
                    $result = @copy($params['name'] . '.php', \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'root/' . basename($params['name']) . '.php');
                    if ($result) {
                        @chmod(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'root/' . basename($params['name']) . '.php', 0644);
                    }
                }
            if ($result) {
                if (! isset($params['show']) or $params['show'] != 'no') {
                    $group_query = $this->rootda->execute('SELECT * FROM root_panels_groups WHERE name = ' . $this->rootda->formatText($params['category']));
                    if ($group_query->getNumberRows())
                        $group_id = $group_query->getFields('id');
                    else
                        $group_id = '';
                    $ins = 'INSERT INTO root_panels VALUES (' . $this->rootda->getNextSequenceValue('root_panels_id_seq') . ',' . $this->rootda->formatText($name) . ',' . $this->rootda->formatText(basename($params['icon'])) . ',' . $this->rootda->formatText($params['catalog']) . ',' . $this->rootda->formatText($group_id) . ',' . $this->rootda->formatText($params['themeicon']) . ',' . $this->rootda->formatText($params['themeicontype']) . ')';
                    $result = $this->rootda->execute($ins);
                    if (! $result)
                        $this->mLog->logEvent('innomatic.rootpanelcomponent.doinstallaction', 'In application ' . $this->appname . ', component ' . $name . ': Unable to insert root application in root_panels table', \Innomatic\Logging\Logger::ERROR);
                } else
                    $result = true;
            } else
                $this->mLog->logEvent('innomatic.rootpanelcomponent.doinstallaction', 'In application ' . $this->appname . ', component ' . $name . ': Unable to copy root application ' . $name . ' to destination ' . \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'root/' . basename($params['name']), \Innomatic\Logging\Logger::ERROR);
        } else
            $this->mLog->logEvent('innomatic.rootpanelcomponent.doinstallaction', 'In application ' . $this->appname . ', component ' . $name . ': Empty application file name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
    public function doUninstallAction($params)
    {
        $result = false;
        if (! isset($params['icon']))
            $params['icon'] = '';
        if (! isset($params['catalog']))
            $params['catalog'] = '';
        if (! isset($params['themeicon']))
            $params['themeicon'] = '';
        if (! isset($params['themeicontype']))
            $params['themeicontype'] = '';
        if (strlen($params['name'])) {
            if (strlen($params['icon'])) {
                if (@unlink(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'root/' . basename($params['icon']))) {
                    $result = true;
                } else
                    $this->mLog->logEvent('innomatic.rootpanelcomponent.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to remove icon file ' . \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'root/' . basename($params['icon']), \Innomatic\Logging\Logger::ERROR);
            }
            if (file_exists(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'root/' . basename($params['name']) . '.php')) {
                $result = @unlink(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'root/' . basename($params['name']) . '.php');
            }
            if (is_dir(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'root/' . basename($params['name']) . '-panel')) {
                $result = \Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'root/' . basename($params['name']) . '-panel');
            }
            if (! $result) {
                $this->mLog->logEvent('innomatic.rootpanelcomponent.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to remove root application file ' . \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'root/' . basename($params['name']), \Innomatic\Logging\Logger::ERROR);
            }
            if ($params['show'] != 'no') {
                $result = $this->rootda->execute('DELETE FROM root_panels WHERE name = ' . $this->rootda->formatText($params['name']));
                if (! $result)
                    $this->mLog->logEvent('innomatic.rootpanelcomponen.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to remove root application from root_panels table', \Innomatic\Logging\Logger::ERROR);
            } else
                $result = true;
        } else
            $this->mLog->logEvent('innomatic.rootpanelcomponent.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty application file name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
    public function doUpdateAction($params)
    {
        $result = false;
        $name = $params['name'];
        if (! isset($params['icon']))
            $params['icon'] = '';
        if (! isset($params['catalog']))
            $params['catalog'] = '';
        if (! isset($params['themeicon']))
            $params['themeicon'] = '';
        if (! isset($params['themeicontype']))
            $params['themeicontype'] = '';
        if (strlen($params['name'])) {
            if (isset($params['icon']) and strlen($params['icon'])) {
                $params['icon'] = $this->basedir . '/root/' . $params['icon'];
                if (@copy($params['icon'], \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'root/' . basename($params['icon']))) {
                    @chmod(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'root/' . basename($params['icon']), 0644);
                    $result = true;
                } else
                    $this->mLog->logEvent('innomatic.rootpanelcomponent.doupdateaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to copy root application icon ' . $params['icon'] . ' to destination ' . \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'root/' . basename($params['icon']), \Innomatic\Logging\Logger::ERROR);
            }
            $params['name'] = $this->basedir . '/root/' . $params['name'];
            $result = false;
            if (file_exists(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'root/' . basename($params['name']) . '.php')) {
                @unlink(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'root/' . basename($params['name']) . '.php');
            }
            if (is_dir(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'root/' . basename($params['name']) . '-panel')) {
                \Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'root/' . basename($params['name']) . '-panel');
            }
            if (is_dir($params['name'] . '-panel')) {
                if (\Innomatic\Io\Filesystem\DirectoryUtils::dirCopy($params['name'] . '-panel/', \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'root/' . basename($params['name']) . '-panel/')) {
                    $result = true;
                }
            } else
                if (file_exists($params['name']) . '.php') {
                    $result = @copy($params['name'] . '.php', \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'root/' . basename($params['name']) . '.php');
                    if ($result) {
                        @chmod(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'root/' . basename($params['name']) . '.php', 0644);
                    }
                }
            if ($result) {
                if (! isset($params['category']))
                    $params['category'] = '';
                $group_query = $this->rootda->execute('SELECT * ' . 'FROM root_panels_groups ' . 'WHERE name = ' . $this->rootda->formatText($params['category']));
                if ($group_query->getNumberRows())
                    $group_id = $group_query->getFields('id');
                else
                    $group_id = '';
                $result = $this->rootda->execute('UPDATE root_panels SET iconfile=' . $this->rootda->formatText(basename($params['icon'])) . ',' . 'catalog=' . $this->rootda->formatText($params['catalog']) . ',' . 'themeicon=' . $this->rootda->formatText($params['themeicon']) . ',' . 'themeicontype=' . $this->rootda->formatText($params['themeicontype']) . ($group_id ? ',groupid=' . $group_id : '') . ' WHERE name=' . $this->rootda->formatText($name));
                if (! $result)
                    $this->mLog->logEvent('innomatic.rootpanelcomponent.doupdateaction', 'In application ' . $this->appname . ', component ' . $name . ': Unable to update root application in root_panels table', \Innomatic\Logging\Logger::ERROR);
            } else
                $this->mLog->logEvent('innomatic.rootpanelcomponent.doupdateaction', 'In application ' . $this->appname . ', component ' . $name . ': Unable to copy root application file ' . $name . ' to destination ' . \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'root/' . basename($name), \Innomatic\Logging\Logger::ERROR);
        } else
            $this->mLog->logEvent('innomatic.rootpanelcomponent.doupdateaction', 'In application ' . $this->appname . ', component ' . $name . ': Empty application file name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
}
