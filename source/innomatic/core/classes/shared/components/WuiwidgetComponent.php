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
 * Wuiwidget component handler.
 */
class WuiwidgetComponent extends ApplicationComponent
{
    function __construct ($rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
    }
    public static function getType ()
    {
        return 'wuiwidget';
    }
    public static function getPriority ()
    {
        return 50;
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
        $result = false;
        if (strlen($params['file'])) {
            $params['file'] = $this->basedir . '/core/classes/shared/wui/' . basename($params['file']);
            if (@copy($params['file'], InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/classes/shared/wui/' . basename($params['file']))) {
                @chmod(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/classes/shared/wui/' . basename($params['file']), 0644);
                if ($this->rootda->execute('INSERT INTO wui_widgets VALUES (' . $this->rootda->getNextSequenceValue('wui_widgets_id_seq') . ',' . $this->rootda->formatText($params['name']) . ',' . $this->rootda->formatText(basename($params['file'])) . ')')) {
                    $result = true;
                } else
                    $this->mLog->logEvent('innomatic.wuiwidget.wuiwidget.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to install component', Logger::ERROR);
            } else
                $this->mLog->logEvent('innomatic.wuiwidgetcomponent.wuiwidget.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to copy wui component file (' . $params['file'] . ') to its destination (' . InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/classes/shared/wui/' . basename($params['file']) . ')', Logger::ERROR);
        } else
            $this->mLog->logEvent('innomatic.wuiwidgetcomponent.wuiwidget.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty component file name', Logger::ERROR);
        return $result;
    }
    function DoUninstallAction ($params)
    {
        $result = false;
        if (strlen($params['file'])) {
            if ($this->rootda->execute('DELETE FROM wui_widgets WHERE name=' . $this->rootda->formatText($params['name']))) {
                if (@unlink(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/classes/shared/wui/' . basename($params['file']))) {
                    $result = true;
                }
            } else
                $this->mLog->logEvent('innomatic.wuiwidgetcomponent.wuiwidget.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to uninstall component', Logger::ERROR);
        } else
            $this->mLog->logEvent('innomatic.wuiwidgetcomponent.wuiwidget.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty component file name', Logger::ERROR);
        return $result;
    }
    function DoUpdateAction ($params)
    {
        $result = false;
        if (strlen($params['file'])) {
            $params['file'] = $this->basedir . '/core/classes/shared/wui/' . basename($params['file']);
            if (@copy($params['file'], InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/classes/shared/wui/' . basename($params['file']))) {
                @chmod(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/classes/shared/wui/' . basename($params['file']), 0644);
                $check_query = $this->rootda->execute('SELECT name FROM wui_widgets WHERE name=' . $this->rootda->formatText($params['name']));
                if ($check_query->getNumberRows()) {
                    $result = $this->rootda->execute('UPDATE wui_widgets SET file=' . $this->rootda->formatText(basename($params['file'])) . ' WHERE name=' . $this->rootda->formatText($params['name']));
                } else {
                    $result = $this->rootda->execute('INSERT INTO wui_widgets VALUES (' . $this->rootda->getNextSequenceValue('wui_widgets_id_seq') . ',' . $this->rootda->formatText($params['name']) . ',' . $this->rootda->formatText(basename($params['file'])) . ')');
                }
                if (! $result) {
                    $this->mLog->logEvent('innomatic.wuiwidgetcomponent.wuiwidget.doupdateaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to update component', Logger::ERROR);
                }
            } else
                $this->mLog->logEvent('innomatic.wuiwidgetcomponent.wuiwidget.doupdateaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to copy wui component file (' . $params['file'] . ') to its destination (' . InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/classes/shared/wui/' . basename($params['file']) . ')', Logger::ERROR);
        } else
            $this->mLog->logEvent('innomatic.wuiwidgetcomponent.wuiwidget.doupdateaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty component file name', Logger::ERROR);
        return $result;
    }
}
