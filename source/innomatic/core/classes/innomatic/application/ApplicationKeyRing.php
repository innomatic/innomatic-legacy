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
namespace Innomatic\Application;

class ApplicationKeyRing
{
    public function addKey(
        $keyFile
        )
    {
        $result = false;

        if ( file_exists($keyFile) ) {
            require( $keyFile );

            if ( isset($innocoderKey) ) {
                $keyName = $innocoderKey['applicationid']
                .(strlen($innocoderKey['domainid']) ? '-'.$innocoderKey['domainid'] : '').'.key';

                copy(
                    $keyFile,
                    InnomaticContainer::instance('innomaticcontainer')->getHome().'core/keyring/'.$keyName
                );

                $checkQuery = InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->execute(
                    'SELECT id '.
                    'FROM applications_keyring_keys '
                    .'WHERE application='.InnomaticContainer::instance(
                        'innomaticcontainer'
                    )->getDataAccess()->formatText($innocoderKey['applicationid'])
                    .' AND domain='
                    .InnomaticContainer::instance(
                        'innomaticcontainer'
                    )->getDataAccess()->formatText($innocoderKey['domainid'])
                );

                if ( $checkQuery->getNumberRows() ) {
                    InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->Execute(
                        'UPDATE applications_keyring_keys '.
                        'SET version='
                        .InnomaticContainer::instance(
                            'innomaticcontainer'
                        )->getDataAccess()->formatText($innocoderKey['version']).','
                        .'maxdomainusers='.(
                            strlen($innocoderKey['maxdomainusers'])
                            ? $innocoderKey['maxdomainusers']
                            : '0'
                        ).','.'validip='
                        .InnomaticContainer::instance(
                            'innomaticcontainer'
                        )->getDataAccess()->formatText($innocoderKey['validip'])
                        .','.'validrange='
                        .InnomaticContainer::instance(
                            'innomaticcontainer'
                        )->getDataAccess()->formatText($innocoderKey['validrange'])
                        .','.'expirydate='
                        .InnomaticContainer::instance(
                            'innomaticcontainer'
                        )->getDataAccess()->formatText($innocoderKey['expirydate'])
                        .' '.'WHERE application='
                        .InnomaticContainer::instance(
                            'innomaticcontainer'
                        )->getDataAccess()->formatText($innocoderKey['applicationid'])
                        .' '.'AND domain='
                        .InnomaticContainer::instance(
                            'innomaticcontainer'
                        )->getDataAccess()->formatText($innocoderKey['domainid'])
                    );
                } else {
                    $id = InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->getNextSequenceValue(
                        'applications_keyring_keys_id_seq'
                    );
                    InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->Execute(
                        'INSERT INTO applications_keyring_keys '.
                        'VALUES ('.$id.','
                        .InnomaticContainer::instance(
                            'innomaticcontainer'
                        )->getDataAccess()->formatText($innocoderKey['applicationid']).','
                        .InnomaticContainer::instance(
                            'innomaticcontainer'
                        )->getDataAccess()->formatText($innocoderKey['version']).','
                        .InnomaticContainer::instance(
                            'innomaticcontainer'
                        )->getDataAccess()->formatText($innocoderKey['domainid']).','
                        .(
                            strlen($innocoderKey['maxdomainusers'])
                            ? $innocoderKey['maxdomainusers']
                            : '0'
                        )
                        .','.InnomaticContainer::instance(
                            'innomaticcontainer'
                        )->getDataAccess()->formatText($innocoderKey['validip']).','
                        .InnomaticContainer::instance(
                            'innomaticcontainer'
                        )->getDataAccess()->formatText($innocoderKey['validrange']).','
                        .InnomaticContainer::instance(
                            'innomaticcontainer'
                        )->getDataAccess()->formatText($innocoderKey['expirydate']).','
                        .InnomaticContainer::instance(
                            'innomaticcontainer'
                        )->getDataAccess()->formatText($keyName).')'
                    );
                }

                $result = true;
            }
        }

        return $result;
    }

    public function removeKey(
        $id
        )
    {
        $result = false;

        $id = (int)$id;

        if ( $id ) {
            $checkQuery = InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->execute(
                'SELECT file '.
                'FROM applications_keyring_keys '.
                'WHERE id='.$id
            );

            if ( $checkQuery->getNumberRows() ) {
                InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->Execute(
                    'DELETE FROM applications_keyring_keys '.
                    'WHERE id='.$id
                );

                unlink(
                    InnomaticContainer::instance('innomaticcontainer')->getHome()
                    .'core/keyring/'.$checkQuery->getFields('file')
                );
            }
        }

        return $result;
    }

    public static function handleKey(
        $applicationName,
        $password = ''
        )
    {
        if (
            !ApplicationKeyRing::checkKey(
                $applicationName,
                InnomaticContainer::instance('innomaticcontainer')->isDomainStarted()
                ? InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDomainId()
                : '',
                $password
            )
        ) {
            ApplicationKeyRing::dieKey(
                $applicationName,
                InnomaticContainer::instance('innomaticcontainer')->isDomainStarted()
                ? InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->getDomainId()
                : ''
            );
        }

        return true;
    }

    public static function checkKey(
        $applicationName,
        $domainName = '',
        $password = ''
        )
    {
        $result = false;

        // If in upgrade state we must ensure Innomatic runs anyway
        if (
            InnomaticContainer::instance('innomaticcontainer')->getState() != InnomaticContainer::STATE_UPGRADE
        ) {
            $filename = InnomaticContainer::instance('innomaticcontainer')->getHome()
            .'core/keyring/'.$applicationName;
            if ( strlen($domainName) ) $filename .= '-'.$domainName;
            $filename .= '.key';

            if ( file_exists($filename) ) {
                require( $filename );

                if (
                    isset($innocoderKey )
                    and $innocoderKey['applicationid'] == $applicationName
                    and $innocoderKey['domainid'] == $domainName
                    and $innocoderKey['password'] == $password
                ) {
                    // Should handle max domain users here
                    $result = true;
                }
            }
        } else $result = true;

        return $result;
    }

    public static function dieKey(
        $applicationName,
        $domainName = ''
        )
    {
        InnomaticContainer::instance('innomaticcontainer')->abort(
            'Key for application '.$applicationName.(
                strlen($domainName) ? ', domain '.$domainName : ''
            )
            .' is missing or has expired',
            InnomaticContainer::INTERFACE_WEB
        );
    }
}
