<?php
/**
 * Innomatic
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2014 Innomatic Company
 * @license    http://www.innomatic.io/license/ New BSD License
 * @link       http://www.innomatic.io
 * @since      Class available since Release 5.0
 */
namespace Innomatic\Validator;

/**
 * This class runs a suite of validators collecting error results.
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 * @copyright Copyright 2012 Innomatic Company
 * @since 1.0
 */
class ValidatorAggregate
{
    /**
     * Array of validators.
     * @type array
     * @since 1.0
     */
    protected $validators = array();
    /**
     * Array of the collected errors.
     * @type array
     * @since 1.0
     */
    protected $errors = array();
    /**
     * Adds a validator to the suite.
     * @param Validator $validator
     * @since 1.0
     */
    public function addValidator(Validator $validator)
    {
        $this->validators[] = $validator;
    }
    /**
     * Runs the validators.
     * @since 1.0
     */
    public function validate()
    {
        foreach ($this->validators as $validator) {
            $validator->validate();
            if (! $validator->isValid()) {
                while ($this->errors[] = $validator->getError()) {
                }
            }
        }
    }
    /**
     * Gets the current error.
     * @since 1.0
     */
    public function getError()
    {
        return array_pop($this->errors);
    }
    /**
     * Tells if the suite of validators is valid and there are no errors.
     * @since 1.0
     */
    public function isValid()
    {
        return count($this->errors) ? false : true;
    }
}
