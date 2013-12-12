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
namespace Innomatic\Domain\User;

class UserSettings
{
    public $mUserId;
    public $mrDomainDA;

    public function __construct(\Innomatic\Dataaccess\DataAccess $domainDA, $userId)
    {
        $this->mrDomainDA = $domainDA;
        $this->mUserId = (int) $userId;
    }

    public function getKey($key, $fallbackToDomainSetting = false)
    {
        if ($this->mrDomainDA) {
            $key_query = $this->mrDomainDA->execute('SELECT val FROM domain_users_settings WHERE userid='. (int) $this->mUserId.' AND keyname='.$this->mrDomainDA->formatText($key));

            if ($key_query->getNumberRows()) {
                return $key_query->getFields('val');
            } elseif ($fallbackToDomainSetting == true) {
                $sets = new \Innomatic\Domain\DomainSettings($this->mrDomainDA);
                return $sets->getKey($key);
            }
        }

        return '';
    }

    public function setKey($key, $value)
    {
        if ($this->mrDomainDA) {
            $key_query = $this->mrDomainDA->execute('SELECT val FROM domain_users_settings WHERE userid='. (int) $this->mUserId.' AND keyname='.$this->mrDomainDA->formatText($key));

            if ($key_query->getNumberRows()) {
                return $this->mrDomainDA->execute('UPDATE domain_users_settings SET val='.$this->mrDomainDA->formatText($value).' WHERE userid='. (int) $this->mUserId.' AND keyname='.$this->mrDomainDA->formatText($key));
            } else {
                return $this->mrDomainDA->execute('INSERT INTO domain_users_settings VALUES('. (int) $this->mUserId.','.$this->mrDomainDA->formatText($key).','.$this->mrDomainDA->formatText($value).')');
            }
        }
        return false;
    }

    public function editKey($key, $value)
    {
        return $this->setKey($key, $value);
    }

    public function checkKey($key)
    {
        if ($this->mrDomainDA) {
            $key_query = $this->mrDomainDA->execute('SELECT val FROM domain_users_settings WHERE userid='. (int) $this->mUserId.' AND keyname='.$this->mrDomainDA->formatText($key));

            if ($key_query->getNumberRows())
            return true;
        }
        return false;
    }

    public function deleteKey($key)
    {
        if ($this->mrDomainDA) {
            return $this->mrDomainDA->execute('DELETE FROM domain_users_settings WHERE userid='. (int) $this->mUserId.' AND keyname='.$this->mrDomainDA->formatText($key));
        }
        return false;
    }
}
