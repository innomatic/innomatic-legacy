<?php

/**
 * @since 5.1
 */

require_once('innomatic/module/ValueObject.php');

/**
 * Empty value object.
 *
 * The empty value object is used when a not persistent ModuleObject doesn't
 * have members. A ModuleObject requires a value object anyway, so that at lmodulet
 * an empty value object must be given.
 *
 * @author Alex Pagnoni <alex.pagnoni@innoteam.it>
 * @copyright Copyright 2004-2013 Innoteam S.r.l.
 * @since 5.1
 */
class ModuleEmptyValueObject extends ModuleValueObject {
}

?>