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
 * @since      Class available since Release 5.0
 */
namespace Shared\Components;

/**
 * Country component handler.
 */
class CountryComponent extends \Innomatic\Application\ApplicationComponent
{
    public function __construct($rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
    }
    public static function getType()
    {
        return 'country';
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
        if (strlen($params['file']) and strlen($params['name']) and strlen($params['short'])) {
            $params['file'] = $this->basedir . '/core/locale/countries/' . $params['file'];
            if (@copy($params['file'], \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/locale/countries/' . basename($params['file']))) {
                @chmod(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/locale/countries/' . basename($params['file']), 0644);
                $result = $this->rootda->execute('INSERT INTO locale_countries ' . 'VALUES (' . $this->rootda->formatText($params['short']) . ',' . $this->rootda->formatText($params['name']) . ',' . $this->rootda->formatText(basename($params['file'])) . ')');
            }
        } else
            $this->mLog->logEvent('innomatic.countrycomponent.countrycomponent.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty country file name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
    public function doUninstallAction($params)
    {
        $result = false;
        if (strlen($params['file']) and strlen($params['name']) and strlen($params['short'])) {
            if (@unlink(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/locale/countries/' . basename($params['file']))) {
                $result = $this->rootda->execute('DELETE FROM locale_countries ' . 'WHERE countryname=' . $this->rootda->formatText($params['name']) . ' ' . 'AND countryshort=' . $this->rootda->formatText($params['short']));
            }
        } else
            $this->mLog->logEvent('innomatic.countrycomponent.countrycomponent.douninstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty country file name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
    public function doUpdateAction($params)
    {
        $result = false;
        if (strlen($params['name']) and strlen($params['short']) and strlen($params['file'])) {
            $params['file'] = $this->basedir . '/core/locale/countries/' . $params['file'];
            if (@copy($params['file'], \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/locale/countries/' . basename($params['file']))) {
                @chmod(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/locale/countries/' . basename($params['file']), 0644);
                $result = $this->rootda->execute('UPDATE locale_countries ' . 'SET countryshort=' . $this->rootda->formatText($params['short']) . ',' . 'countryname = ' . $this->rootda->formatText($params['name']) . ' ' . 'WHERE countryfile=' . $this->rootda->formatText(basename($params['file'])));
            }
        } else
            $this->mLog->logEvent('innomatic.countrycomponent.countrycomponent.doupdateaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty country name (' . $params['name'] . ') or short name (' . $params['short'] . ')', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
}
