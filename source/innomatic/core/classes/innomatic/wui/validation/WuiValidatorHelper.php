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
 * @since      Class available since Release 5.0
*/
namespace Innomatic\Wui\Validation;

/**
 *
 * @package WUI
 */
class WuiValidatorHelper
{
    public static function validate()
    {
        static $validated = false;

        if (!$validated) {
            $innomatic = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');
            if ($innomatic->getState() != \Innomatic\Core\InnomaticContainer::STATE_SETUP) {
                $validators_query = $innomatic->getDataAccess()->execute('SELECT file FROM wui_validators');
                if ($validators_query) {
                    // TODO old
                    while (!$validators_query->eof) {
                        if (file_exists($innomatic->getHome().'core/classes/shared/wui/validators/'.$validators_query->getFields('file'))) {
                            include_once($innomatic->getHome().'core/classes/shared/wui/validators/'.$validators_query->getFields('file'));
                        }
                        $validators_query->moveNext();
                    }
                }
                $validators_query->free();
                $validated = true;
            }
        }
    }
}
