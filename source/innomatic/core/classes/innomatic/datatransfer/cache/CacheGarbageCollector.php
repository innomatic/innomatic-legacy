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
 * @license    http://www.innomatic.org/license/   BSD License
 * @link       http://www.innomatic.org
 * @since      Class available since Release 5.0
*/
namespace Innomatic\Datatransfer\Cache;

class CacheGarbageCollector
{
    public function removeDomainItems($domainId)
    {
        if (strlen($domainId)) {
            return \Innomatic\Core\InnomaticContainer::instance(
                '\Innomatic\Core\InnomaticContainer'
            )->getDataAccess()->execute(
                'DELETE FROM cache_items WHERE domainid=' . $domainId
            );
        }
        return false;
    }

    public function removeUserItems($userId)
    {
        $userId = (int) $userId;
        if (strlen($userId)) {
            return \Innomatic\Core\InnomaticContainer::instance(
                '\Innomatic\Core\InnomaticContainer'
            )->getDataAccess()->execute(
                'DELETE FROM cache_items WHERE userid=' . $userId
            );
        }
        return false;
    }

    public function removeApplicationItems($application)
    {
        if (strlen($application)) {
            return \Innomatic\Core\InnomaticContainer::instance(
                '\Innomatic\Core\InnomaticContainer'
            )->getDataAccess()->execute(
                'DELETE FROM cache_items WHERE application='
                . \Innomatic\Core\InnomaticContainer::instance(
                    '\Innomatic\Core\InnomaticContainer'
                )->getDataAccess()->formatText($application)
            );
        }
        return false;
    }

    public function emptyCache()
    {
        \Innomatic\Core\InnomaticContainer::instance(
            '\Innomatic\Core\InnomaticContainer'
        )->getDataAccess()->execute('DELETE FROM cache_items');

        $dirstream = opendir(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome()
            . 'core/temp/cache'
        );

        if ($dirstream) {
            while (false !== ($filename = readdir($dirstream))) {
                if ($filename != '.' && $filename != '..') {
                    if (
                        is_file(
                            \Innomatic\Core\InnomaticContainer::instance(
                                '\Innomatic\Core\InnomaticContainer'
                            )->getHome() . 'core/temp/cache/' . $filename
                        )
                    ) {
                        unlink(
                            \Innomatic\Core\InnomaticContainer::instance(
                                '\Innomatic\Core\InnomaticContainer'
                            )->getHome() . 'core/temp/cache/' . $filename
                        );
                    }
                }
            }
            closedir($dirstream);
        }
        return true;
    }
}
