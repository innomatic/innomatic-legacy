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

// Updates the front receiver file.
@copy(
    $tmpdir.'/../index.php',
    RootContainer::instance('rootcontainer')->getHome()
    . 'index.php'
);
@chmod(RootContainer::instance('rootcontainer')->getHome() . 'index.php', 0644);

/*
@copy($tmpdir . '/core/web.xml',
      InnomaticContainer::instance('innomaticcontainer')->getHome()
      . 'core/web.xml');

@chmod(InnomaticContainer::instance('innomaticcontainer')->getHome()
       . 'core/web.xml', 0644);
*/

@copy(
    $tmpdir . '/index.php',
    InnomaticContainer::instance('innomaticcontainer')->getHome()
    . 'index.php'
);
@chmod(
    InnomaticContainer::instance('innomaticcontainer')->getHome()
    . 'index.php', 0644
);

@copy(
    $tmpdir . '/favicon.png',
    InnomaticContainer::instance('innomaticcontainer')->getHome()
    . 'favicon.png'
);
@chmod(
    InnomaticContainer::instance('innomaticcontainer')->getHome()
    . 'favicon.png', 0644
);

// Innomatic dependencies fix
//
$appQuery = InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->execute(
    'SELECT id
    FROM
        applications
    WHERE
        appid='
    . InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->formatText('innomatic')
);

InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->execute(
    'DELETE FROM
        applications_dependencies
    WHERE
        appid='.$appQuery->getFields('id')
);

// Innomatic auto reupdate
//
/*
$app_query = InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->execute(
    'SELECT appfile '.
    'FROM applications '.
    'WHERE appid='
    . InnomaticContainer::instance('innomaticcontainer')->getDataAccess()->formatText('innomatic'));

@copy(
    InnomaticContainer::instance('innomaticcontainer')->getHome()
    . 'core/applications/'.$app_query->getFields( 'appfile' ),
    InnomaticContainer::instance('innomaticcontainer')->getHome()
    . 'core/temp/appinst/reupdate'
);
*/
