<?php
namespace Innomatic\Module\Util;

/**
 * @since 5.1
 */

/**
 * Empty value object.
 *
 * The empty value object is used when a not persistent ModuleObject doesn't
 * have members. A ModuleObject requires a value object anyway, so that at lmodulet
 * an empty value object must be given.
 *
 * @author Alex Pagnoni <alex.pagnoni@innoteam.it>
 * @copyright Copyright 2004-2014 Innoteam Srl
 * @since 5.1
 */
class ModuleEmptyValueObject extends \Innomatic\Module\ModuleValueObject
{
}
