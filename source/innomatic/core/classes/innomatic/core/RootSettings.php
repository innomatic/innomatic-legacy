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
 */
namespace Innomatic\Core;

/**
 * @since 5.0.0 introduced
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 */
class RootSettings
{
    public $rootDA;
    
    public function __construct()
    {
        $this->rootDA = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess();
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
        if ($this->checkKey($key) === false) {
            return $this->rootDA->execute(
                'INSERT INTO root_settings VALUES ( '
                . $this->rootDA->formatText($key)
                . ',' . $this->rootDA->formatText($value) . ')'
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
        if ($this->checkKey($key) == true) {
            return $this->rootDA->execute(
                'UPDATE root_settings SET val = '
                . $this->rootDA->formatText($value)
                . ' WHERE keyname = '
                . $this->rootDA->formatText($key)
            );
        } else {
            $ins = 'INSERT INTO root_settings VALUES ('
                . $this->rootDA->formatText($key)
                . ',' . $this->rootDA->formatText($value) . ')';
            
            return $this->rootDA->execute($ins);
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
        if ($this->checkKey($key) == true) {
            return $this->rootDA->execute(
                'DELETE FROM root_settings WHERE keyname = '
                . $this->rootDA->formatText($key)
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
        $query = $this->checkKey($key);
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
            $keyquery = $this->rootDA->execute(
                'SELECT val FROM root_settings WHERE keyname = '
                . $this->rootDA->formatText($key)
            );
            
            if ($keyquery->getNumberRows() > 0) {
                return $keyquery;
            }
        }
        return false;
    }
}
