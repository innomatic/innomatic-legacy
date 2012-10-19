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
 * Country component handler.
 */
class CountryComponent extends ApplicationComponent
{
    function CountryComponent ($rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
    }
    public static function getType ()
    {
        return 'country';
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
        if (strlen($params['file']) and strlen($params['name']) and strlen($params['short'])) {
            $params['file'] = $this->basedir . '/core/locale/countries/' . $params['file'];
            if (@copy($params['file'], InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/locale/countries/' . basename($params['file']))) {
                @chmod(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/locale/countries/' . basename($params['file']), 0644);
                $result = &$this->rootda->execute('INSERT INTO locale_countries ' . 'VALUES (' . $this->rootda->formatText($params['short']) . ',' . $this->rootda->formatText($params['name']) . ',' . $this->rootda->formatText(basename($params['file'])) . ')');
            }
        } else
            $this->mLog->logEvent('innomatic.countrycomponent.countrycomponent.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty country file name', Logger::ERROR);
        return $result;
    }
    function DoUninstallAction ($params)
    {
        $result = false;
        if (strlen($params['file']) and strlen($params['name']) and strlen($params['short'])) {
            if (@unlink(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/locale/countries/' . basename($params['file']))) {
                $result = &$this->rootda->execute('DELETE FROM locale_countries ' . 'WHERE countryname=' . $this->rootda->formatText($params['name']) . ' ' . 'AND countryshort=' . $this->rootda->formatText($params['short']));
            }
        } else
            $this->mLog->logEvent('innomatic.countrycomponent.countrycomponent.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty country file name', Logger::ERROR);
        return $result;
    }
    function DoUpdateAction ($params)
    {
        $result = false;
        if (strlen($params['name']) and strlen($params['short']) and strlen($params['file'])) {
            $params['file'] = $this->basedir . '/core/locale/countries/' . $params['file'];
            if (@copy($params['file'], InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/locale/countries/' . basename($params['file']))) {
                @chmod(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/locale/countries/' . basename($params['file']), 0644);
                $result = &$this->rootda->execute('UPDATE locale_countries ' . 'SET countryshort=' . $this->rootda->formatText($params['short']) . ',' . 'countryname = ' . $this->rootda->formatText($params['name']) . ' ' . 'WHERE countryfile=' . $this->rootda->formatText(basename($params['file'])));
            }
        } else
            $this->mLog->logEvent('innomatic.countrycomponent.countrycomponent.doupdateaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty country name (' . $params['name'] . ') or short name (' . $params['short'] . ')', Logger::ERROR);
        return $result;
    }
}
