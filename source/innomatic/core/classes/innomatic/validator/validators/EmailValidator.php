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
require_once ('innomatic/validator/Validator.php');
/**
 * This validator validates email addresses.
 * @author Alex Pagnoni <alex.pagnoni@innoteam.it>
 * @copyright Copyright 2012 Innoteam Srl
 * @since 1.0
 */
class EmailValidator extends Validator
{
    public function validate()
    {
        $pattern = "/^([a-zA-Z0-9])+([.a-zA-Z0-9_-])*@([a-zA-Z0-9_-])+(.[a-zA-Z0-9_-]+)+/";
        if (! preg_match($pattern, $this->_item)) {
            $this->setError('Invalid email address');
        }
    }
}
