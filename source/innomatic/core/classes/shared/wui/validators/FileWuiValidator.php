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
namespace Shared\Wui\Validators;

// Handling of uploaded files wui eventdata
//
if (
    isset($_FILES['wuifiles'])
    and is_array($_FILES['wuifiles']) and count($_FILES['wuifiles'])
) {
    reset($_FILES);
    $disps = array();

    while (list ($key, $val) = each($_FILES['wuifiles']['name'])) {
        $disps[] = $key;
    }

    $args[0] = 'name';
    $args[1] = 'type';
    $args[2] = 'tmp_name';
    $args[3] = 'size';

    for ($i = 0; $i < 4; $i ++) {
        reset($disps);
        reset($_FILES);
        while (list (, $disp) = each($disps)) {
            while (
                list ($eventdataname, $eventdatacontent)
                = each($_FILES['wuifiles'][$args[$i]][$disp]['evd'])
            ) {
                \Innomatic\Wui\Wui::instance(
                    '\Innomatic\Wui\Wui'
                )->parameters['wui'][$disp]['evd'][$eventdataname][$args[$i]]
                = $eventdatacontent;
            }
        }
    }
}
