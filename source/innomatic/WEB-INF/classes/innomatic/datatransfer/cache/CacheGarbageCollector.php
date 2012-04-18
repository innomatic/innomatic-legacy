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

class CacheGarbageCollector
{
    public function removeDomainItems($domainId)
    {
        if (strlen($domainId)) {
            return InnomaticContainer::instance(
                'innomaticcontainer'
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
            return InnomaticContainer::instance(
                'innomaticcontainer'
            )->getDataAccess()->execute(
                'DELETE FROM cache_items WHERE userid=' . $userId
            );
        }
        return false;
    }

    public function removeApplicationItems($application)
    {
        if (strlen($application)) {
            return InnomaticContainer::instance(
                'innomaticcontainer'
            )->getDataAccess()->execute(
                'DELETE FROM cache_items WHERE application='
                . InnomaticContainer::instance(
                    'innomaticcontainer'
                )->getDataAccess()->formatText($application)
            );
        }
        return false;
    }

    public function emptyCache()
    {
        InnomaticContainer::instance(
            'innomaticcontainer'
        )->getDataAccess()->execute('DELETE FROM cache_items');
        
        $dirstream = opendir(
            InnomaticContainer::instance('innomaticcontainer')->getHome()
            . 'WEB-INF/temp/cache'
        );
        
        if ($dirstream) {
            while (false !== ($filename = readdir($dirstream))) {
                if ($filename != '.' && $filename != '..') {
                    if (
                        is_file(
                            InnomaticContainer::instance(
                                'innomaticcontainer'
                            )->getHome() . 'WEB-INF/temp/cache/' . $filename
                        )
                    ) {
                        unlink(
                            InnomaticContainer::instance(
                                'innomaticcontainer'
                            )->getHome() . 'WEB-INF/temp/cache/' . $filename
                        );
                    }
                }
            }
            closedir($dirstream);
        }
        return true;
    }
}
