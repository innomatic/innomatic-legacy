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
require_once ('innomatic/application/ApplicationComponent.php');
require_once ('innomatic/locale/LocaleCatalog.php');
/**
 * Language component handler.
 */
class LanguageComponent extends ApplicationComponent
{
    function LanguageComponent ($rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
    }
    public static function getType ()
    {
        return 'language';
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
        $result = false;
        if (strlen($params['name']) and strlen($params['short'])) {
            $result = &$this->rootda->execute('INSERT INTO locale_languages ' . 'VALUES (' . $this->rootda->formatText($params['short']) . ',' . $this->rootda->formatText($params['name']) . ')');
        } else
            $this->mLog->logEvent('innomatic.languagecomponent.languagecomponent.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty language name (' . $params['name'] . ') or short name (' . $params['short'] . ')', Logger::ERROR);
        return $result;
    }
    function DoUninstallAction ($params)
    {
        $result = false;
        if (strlen($params['name']) and strlen($params['short'])) {
            $result = &$this->rootda->execute('DELETE FROM locale_languages ' . 'WHERE langname=' . $this->rootda->formatText($params['name']) . ' ' . 'AND langshort=' . $this->rootda->formatText($params['short']));
        } else
            $this->mLog->logEvent('innomatic.languagecomponent.languagecomponent.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty language name (' . $params['name'] . ') or short name (' . $params['short'] . ')', Logger::ERROR);
        return $result;
    }
    function DoUpdateAction ($params)
    {
        $result = false;
        if (strlen($params['name']) and strlen($params['short'])) {
            $result = &$this->rootda->execute('UPDATE locale_languages ' . 'SET langshort=' . $this->rootda->formatText($params['short']) . ',' . 'langname = ' . $this->rootda->formatText($params['name']) . ' ' . 'WHERE langname=' . $this->rootda->formatText($params['name']));
        } else
            $this->mLog->logEvent('innomatic.languagecomponent.languagecomponent.doupdateaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty language name (' . $params['name'] . ') or short name (' . $params['short'] . ')', Logger::ERROR);
        return $result;
    }
}
