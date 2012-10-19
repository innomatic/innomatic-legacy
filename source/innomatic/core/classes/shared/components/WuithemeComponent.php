<?php
/**
 * Innomatic
 *
 * LICENSE 
 * 
 * This source file is subject to the new BSD license that is bundled 
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2012 Innoteam S.r.l.
 * @license    http://www.innomatic.org/license/   BSD License
 * @link       http://www.innomatic.org
 * @since      Class available since Release 5.0
 */
require_once ('innomatic/application/ApplicationComponent.php');
/**
 * Wuitheme component handler.
 */
class WuithemeComponent extends ApplicationComponent
{
    function WuithemeComponent ($rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
    }
    public static function getType ()
    {
        return 'wuitheme';
    }
    public static function getPriority ()
    {
        return 0;
    }
    public static function getIsDomain ()
    {
        return false;
    }
    public static function getIsOverridable ()
    {
        return false;
    }
    function DoInstallAction ($params)
    {
        $result = FALSE;
        if (strlen($params['file'])) {
            $params['file'] = $this->basedir . '/WEB-INF/conf/themes/' . basename($params['file']);
            // Creates themes configuration folder if it doesn't exists
            if (! is_dir(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'WEB-INF/conf/themes/')) {
                require_once ('innomatic/io/filesystem/DirectoryUtils.php');
                DirectoryUtils::mktree(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'WEB-INF/conf/themes/', 0755);
            }
            if (@copy($params['file'], InnomaticContainer::instance('innomaticcontainer')->getHome() . 'WEB-INF/conf/themes/' . basename($params['file']))) {
                @chmod(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'WEB-INF/conf/themes/' . basename($params['file']), 0644);
                $params['file'] = basename($params['file']);
                if ($this->rootda->execute('INSERT INTO wui_themes VALUES (' . $this->rootda->getNextSequenceValue('wui_themes_id_seq') . ',' . $this->rootda->formatText($params['name']) . ',' . $this->rootda->formatText($params['file']) . ',' . $this->rootda->formatText($params['catalog']) . ')')) {
                    $result = TRUE;
                } else
                    $this->mLog->logEvent('shared.components.wuithemecomponent.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to install component', Logger::ERROR);
            } else
                $this->mLog->logEvent('shared.components.wuithemecomponent.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to copy wui component file (' . $params['file'] . ') to its destination (' . InnomaticContainer::instance('innomaticcontainer')->getHome() . 'WEB-INF/conf/themes/' . basename($params['file']) . ')', Logger::ERROR);
        } else
            $this->mLog->logEvent('shared.components.wuithemecomponent.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty component file name', Logger::ERROR);
        return $result;
    }
    function DoUninstallAction ($params)
    {
        $result = FALSE;
        if (strlen($params['file'])) {
            if (@unlink(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'WEB-INF/conf/themes/' . basename($params['file']))) {
                $result = $this->rootda->execute('DELETE FROM wui_themes WHERE name=' . $this->rootda->formatText($params['name']));
            } else
                $this->mLog->logEvent('shared.components.wuithemecomponent.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to uninstall component', Logger::ERROR);
        } else
            $this->mLog->logEvent('shared.components.wuithemecomponent.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty component file name', Logger::ERROR);
        return $result;
    }
    function DoUpdateAction ($params)
    {
        $result = FALSE;
        if (strlen($params['file'])) {
            $params['file'] = $this->basedir . '/WEB-INF/conf/themes/' . basename($params['file']);
            // Creates themes configuration folder if it doesn't exists
            if (! is_dir(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'WEB-INF/conf/themes/')) {
                require_once ('innomatic/io/filesystem/DirectoryUtils.php');
                DirectoryUtils::mktree(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'WEB-INF/conf/themes/', 0755);
            }
            if (@copy($params['file'], InnomaticContainer::instance('innomaticcontainer')->getHome() . 'WEB-INF/conf/themes/' . basename($params['file']))) {
                @chmod(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'WEB-INF/conf/themes/' . basename($params['file']), 0644);
                $params['file'] = basename($params['file']);
                $check_query = $this->rootda->execute('SELECT name FROM wui_themes WHERE name=' . $this->rootda->formatText($params['name']));
                if ($check_query->getNumberRows()) {
                    $result = $this->rootda->execute('UPDATE wui_themes SET file=' . $this->rootda->formatText($params['file']) . ',catalog=' . $this->rootda->formatText($params['catalog']) . ' WHERE name=' . $this->rootda->formatText($params['name']));
                } else {
                    $result = $this->rootda->execute('INSERT INTO wui_themes VALUES (' . $this->rootda->getNextSequenceValue('wui_themes_id_seq') . ',' . $this->rootda->formatText($params['name']) . ',' . $this->rootda->formatText($params['file']) . ',' . $this->rootda->formatText($params['catalog']) . ')');
                }
                if (! $result)
                    $this->mLog->logEvent('shared.components.wuithemecomponent.doupdateaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to update component', Logger::ERROR);
            } else
                $this->mLog->logEvent('shared.components.wuithemecomponent.doupdateaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to copy wui component file (' . $params['file'] . ') to its destination (' . InnomaticContainer::instance('innomaticcontainer')->getHome() . 'WEB-INF/conf/themes/' . basename($params['file']) . ')', Logger::ERROR);
        } else
            $this->mLog->logEvent('shared.components.wuithemecomponent.doupdateaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty component file name', Logger::ERROR);
        return $result;
    }
}
