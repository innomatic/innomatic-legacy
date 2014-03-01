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
namespace Innomatic\Validator\Validators;

/**
 * This validator validates numbers.
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 * @copyright Copyright 2012 Innoteam Srl
 * @since 1.0
 */
class NumberValidator extends \Innomatic\Validator\Validator
{
    public function validate()
    {
        if (! is_numeric($this->_item)) {
            $this->setError('Not a number');
        }
        if (
            isset($this->_params['max'])
            and strlen($this->_item) > $this->_params['max']
        ) {
            $this->setError('Too long');
        }
        if (
            isset($this->_params['min'])
            and strlen($this->_item) < $this->_params['min']
        ) {
            $this->setError('Too short');
        }
    }
}
