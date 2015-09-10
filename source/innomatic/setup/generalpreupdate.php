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
 * @license    http://www.innomatic.io/license/ New BSD License
 * @link       http://www.innomatic.io
 */

/*
if (file_exists(InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome()
                . 'core/db/innomatic_root.xml.old')) {
      @copy(InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome()
           . 'core/db/innomatic_root.xml.old',
           InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome()
           . 'core/db/innomatic_root.xml.old2' );
}
if (file_exists(InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome()
                . 'core/db/innomatic_root.xml')) {
    @copy(InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome()
          . 'core/db/innomatic_root.xml',
          InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome()
          . 'core/db/innomatic_root.xml.old' );
}
*/

chmod(
    InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome()
    . 'core/temp/pids', 0777
);
