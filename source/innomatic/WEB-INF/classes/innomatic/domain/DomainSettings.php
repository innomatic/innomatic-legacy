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

/*!
 @class DomainSettings

 @abstract Domain settings management
 */
class DomainSettings
{
    public $domainDA;

    /*!
     @function DomainSettings
    
     @abstract Class constructor
    
     @param domainDA DataAccess class - Domain database handler
     */
    public function __construct(DataAccess $domainDA)
    {
        $this->domainDA = $domainDA;
    }

    // Adds a key
    //
    // string $key:   key name
    // string $value: value of the key
    //
    // Returns: true if ok
    //
    public function addKey($key, $value)
    {
        if ($this->CheckKey($key) == false) {
            return $this->domainDA->execute(
                'INSERT INTO domain_settings VALUES ( '
                . $this->domainDA->formatText($key)
                . ',' . $this->domainDA->formatText($value) . ')'
            );
        }
        return false;
    }

    // Edits a key value. If the keys does not exists, it will
    // be created
    //
    // string $key:   key name
    // string $value: value of the key
    //
    // Returns: true if the key was changed
    //
    public function editKey($key, $value)
    {
        if ($this->CheckKey($key) == true) {
            return $this->domainDA->execute(
                'UPDATE domain_settings SET val = '
                . $this->domainDA->formatText($value)
                . ' WHERE keyname = '
                . $this->domainDA->formatText($key)
            );
        } else {
            $ins = 'INSERT INTO domain_settings VALUES ('
                . $this->domainDA->formatText($key)
                . ',' . $this->domainDA->formatText($value) . ')';
            return $this->domainDA->execute($ins);
        }
    }

    public function setKey($key, $value)
    {
        return $this->editKey($key, $value);
    }

    // Deletes a key
    //
    // string $key:   key name
    //
    // Returns: true if the key was deleted
    //
    public function deleteKey($key)
    {
        if ($this->CheckKey($key) == true) {
            return $this->domainDA->execute(
                'DELETE FROM domain_settings WHERE keyname = '
                . $this->domainDA->formatText($key)
            );
        }
        return false;
    }

    // Gets a key value
    //
    // string $key:   key name
    //
    // Returns: key value if the key exists
    //
    public function getKey($key)
    {
        $query = $this->CheckKey($key);
        if ($query == true) {
            return $query->getFields('val');
        }
        return '';
    }

    // Checks if a key exists
    //
    // string $key:   key name
    //
    // Returns: query index if the key exists
    //
    public function checkKey($key)
    {
        if (!empty($key)) {
            $keyquery = $this->domainDA->execute(
                'SELECT val FROM domain_settings WHERE keyname = '
                . $this->domainDA->formatText($key)
            );
            if ($keyquery->getNumberRows() > 0)
                return $keyquery;
        }
        return false;
    }
}
