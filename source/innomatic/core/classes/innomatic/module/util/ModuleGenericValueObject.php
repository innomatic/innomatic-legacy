<?php 

require_once('innomatic/module/ModuleValueObject.php');

/**
 * Generic value object whose members are defined at runtime.
 *
 * There are occasions where a Module value object structure is not hard coded
 * in a dedicated value object class. This is a common occurrence when Module
 * value object fields are declared in its module.xml configuration file.
 *
 * In such cases, using the EmptyValueObject would be incoherent, so this
 * generic value object class has been implementated.
 *
 * @author Alex Pagnoni <alex.pagnoni@innoteam.it>
 * @copyright Copyright 2004-2013 Innoteam S.r.l.
 * @since 5.1
 */
class ModuleGenericValueObject extends ModuleValueObject {
}

?>