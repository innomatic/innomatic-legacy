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
                    \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome().'core/keyring/'.$keyName
                );

                $checkQuery = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->execute(
                    'SELECT id '.
                    'FROM applications_keyring_keys '
                    .'WHERE application='.\Innomatic\Core\InnomaticContainer::instance(
                        '\Innomatic\Core\InnomaticContainer'
                    )->getDataAccess()->formatText($innocoderKey['applicationid'])
                    .' AND domain='
                    .\Innomatic\Core\InnomaticContainer::instance(
                        '\Innomatic\Core\InnomaticContainer'
                    )->getDataAccess()->formatText($innocoderKey['domainid'])
                );

                if ( $checkQuery->getNumberRows() ) {
                    \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->Execute(
                        'UPDATE applications_keyring_keys '.
                        'SET version='
                        .\Innomatic\Core\InnomaticContainer::instance(
                            '\Innomatic\Core\InnomaticContainer'
                        )->getDataAccess()->formatText($innocoderKey['version']).','
                        .'maxdomainusers='.(
                            strlen($innocoderKey['maxdomainusers'])
                            ? $innocoderKey['maxdomainusers']
                            : '0'
                        ).','.'validip='
                        .\Innomatic\Core\InnomaticContainer::instance(
                            '\Innomatic\Core\InnomaticContainer'
                        )->getDataAccess()->formatText($innocoderKey['validip'])
                        .','.'validrange='
                        .\Innomatic\Core\InnomaticContainer::instance(
                            '\Innomatic\Core\InnomaticContainer'
                        )->getDataAccess()->formatText($innocoderKey['validrange'])
                        .','.'expirydate='
                        .\Innomatic\Core\InnomaticContainer::instance(
                            '\Innomatic\Core\InnomaticContainer'
                        )->getDataAccess()->formatText($innocoderKey['expirydate'])
                        .' '.'WHERE application='
                        .\Innomatic\Core\InnomaticContainer::instance(
                            '\Innomatic\Core\InnomaticContainer'
                        )->getDataAccess()->formatText($innocoderKey['applicationid'])
                        .' '.'AND domain='
                        .\Innomatic\Core\InnomaticContainer::instance(
                            '\Innomatic\Core\InnomaticContainer'
                        )->getDataAccess()->formatText($innocoderKey['domainid'])
                    );
                } else {
                    $id = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->getNextSequenceValue(
                        'applications_keyring_keys_id_seq'
                    );
                    \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->Execute(
                        'INSERT INTO applications_keyring_keys '.
                        'VALUES ('.$id.','
                        .\Innomatic\Core\InnomaticContainer::instance(
                            '\Innomatic\Core\InnomaticContainer'
                        )->getDataAccess()->formatText($innocoderKey['applicationid']).','
                        .\Innomatic\Core\InnomaticContainer::instance(
                            '\Innomatic\Core\InnomaticContainer'
                        )->getDataAccess()->formatText($innocoderKey['version']).','
                        .\Innomatic\Core\InnomaticContainer::instance(
                            '\Innomatic\Core\InnomaticContainer'
                        )->getDataAccess()->formatText($innocoderKey['domainid']).','
                        .(
                            strlen($innocoderKey['maxdomainusers'])
                            ? $innocoderKey['maxdomainusers']
                            : '0'
                        )
                        .','.\Innomatic\Core\InnomaticContainer::instance(
                            '\Innomatic\Core\InnomaticContainer'
                        )->getDataAccess()->formatText($innocoderKey['validip']).','
                        .\Innomatic\Core\InnomaticContainer::instance(
                            '\Innomatic\Core\InnomaticContainer'
                        )->getDataAccess()->formatText($innocoderKey['validrange']).','
                        .\Innomatic\Core\InnomaticContainer::instance(
                            '\Innomatic\Core\InnomaticContainer'
                        )->getDataAccess()->formatText($innocoderKey['expirydate']).','
                        .\Innomatic\Core\InnomaticContainer::instance(
                            '\Innomatic\Core\InnomaticContainer'
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
            $checkQuery = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->execute(
                'SELECT file '.
                'FROM applications_keyring_keys '.
                'WHERE id='.$id
            );

            if ( $checkQuery->getNumberRows() ) {
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->Execute(
                    'DELETE FROM applications_keyring_keys '.
                    'WHERE id='.$id
                );

                unlink(
                    \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome()
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
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->isDomainStarted()
                ? \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDomainId()
                : '',
                $password
            )
        ) {
            ApplicationKeyRing::dieKey(
                $applicationName,
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->isDomainStarted()
                ? \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDomainId()
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
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getState() != \Innomatic\Core\InnomaticContainer::STATE_UPGRADE
        ) {
            $filename = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome()
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
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->abort(
            'Key for application '.$applicationName.(
                strlen($domainName) ? ', domain '.$domainName : ''
            )
            .' is missing or has expired',
            \Innomatic\Core\InnomaticContainer::INTERFACE_WEB
        );
    }
}
