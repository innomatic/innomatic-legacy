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
 * @license    http://www.innomaticplatform.com/license/ New BSD License
 * @link       http://www.innomaticplatform.com
 * @since      Class available since Release 5.0
 */
namespace Innomatic\Validator\Validators;

/**
 * This validator validates strings with a regex.
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 * @copyright Copyright 2012 Innoteam Srl
 * @since 1.0
 */
class RegexValidator extends \Innomatic\Validator\Validator
{
    public function validate()
    {
        if (isset($this->_params['pattern'])) {
            if (! preg_match($this->_params['pattern'], $this->_item)) {
                $this->setError('Pattern does not match');
            }
        } else {
            $this->setError('Missing pattern');
        }
    }
}
