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
 */

// Copy the phpunit distribution file
@copy($tmpdir.'/phpunit.xml', \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome().'phpunit.xml');

// Updates the front receiver file.
@copy(
    $tmpdir.'/../index.php',
    \Innomatic\Core\RootContainer::instance('\Innomatic\Core\RootContainer')->getHome()
    . 'index.php'
);
@chmod(\Innomatic\Core\RootContainer::instance('\Innomatic\Core\RootContainer')->getHome() . 'index.php', 0644);

/*
@copy($tmpdir . '/core/web.xml',
      \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome()
      . 'core/web.xml');

@chmod(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome()
       . 'core/web.xml', 0644);
*/

@copy(
    $tmpdir . '/index.php',
    \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome()
    . 'index.php'
);
@chmod(
    \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome()
    . 'index.php', 0644
);

@copy(
    $tmpdir . '/favicon.png',
    \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome()
    . 'favicon.png'
);
@chmod(
    \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome()
    . 'favicon.png', 0644
);

// Innomatic dependencies fix
//
$appQuery = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->execute(
    'SELECT id
    FROM
        applications
    WHERE
        appid='
    . \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->formatText('innomatic')
);

\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->execute(
    'DELETE FROM
        applications_dependencies
    WHERE
        appid='.$appQuery->getFields('id')
);

// Innomatic auto reupdate
//
/*
$app_query = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->execute(
    'SELECT appfile '.
    'FROM applications '.
    'WHERE appid='
    . \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess()->formatText('innomatic'));

@copy(
    \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome()
    . 'core/applications/'.$app_query->getFields( 'appfile' ),
    \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome()
    . 'core/temp/appinst/reupdate'
);
*/
