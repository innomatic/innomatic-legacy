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

use \Innomatic\Domain\DomainSettings;

/**
 * Domainsettings component handler.
 */
class DomainsettingsComponent extends \Innomatic\Application\ApplicationComponent
{
    public function __construct($rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
    }
    public static function getType()
    {
        return 'domainsettings';
    }
    public static function getPriority()
    {
        return 10;
    }
    public static function getIsDomain()
    {
        return true;
    }
    public static function getIsOverridable()
    {
        return false;
    }
    public function doEnableDomainAction($domainid, $params)
    {
        $result = false;
        if (strlen($params['file']) and isset($params['key'])) {
            $domain_cfg = new DomainSettings($this->domainda);
            $domain_cfg->setKey($params['key'], isset($params['value']) ? $params['value'] : '');
        } else
            $this->mLog->logEvent('innomatic.domainsettingscomponent.domainsettingscomponent.doenabledomainaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty file argument', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
    public function doDisableDomainAction($domainid, $params)
    {
        $result = false;
        if (strlen($params['file']) and isset($params['application']) and isset($params['key'])) {
            if (! (isset($params['keep']) and $params['keep'] = 'true')) {
                $domain_cfg = new DomainSettings($this->domainda);
                $domain_cfg->DeleteKey($params['key']);
            }
        } else
            $this->mLog->logEvent('innomatic.domainsettingscomponent.domainsettingscomponent.dodisabledomainaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty file argument', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
    public function doUpdateDomainAction($domainid, $params)
    {
        $result = false;
        if (strlen($params['file']) and isset($params['application']) and isset($params['key'])) {
            $domain_cfg = new DomainSettings($this->domainda);
            if (! (isset($params['keep']) and $params['keep'] = 'true' and $domain_cfg->CheckKey($params['key']))) {
                $domain_cfg->setKey($params['key'], isset($params['value']) ? $params['value'] : '');
            }
        } else
            $this->mLog->logEvent('innomatic.domainsettingscomponent.domainsettingscomponent.doupdatedomainaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty file argument', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
}
