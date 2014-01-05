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
namespace Innomatic\Validator;

/**
 * This class runs a suite of validators collecting error results.
 * @author Alex Pagnoni <alex.pagnoni@innoteam.it>
 * @copyright Copyright 2012 Innoteam Srl
 * @since 1.0
 */
class ValidatorAggregate
{
    /**
     * Array of validators.
     * @access protected
     * @type array
     * @since 1.0
     */
    protected $_validators = array();
    /**
     * Array of the collected errors.
     * @access protected
     * @type array
     * @since 1.0
     */
    protected $_errors = array();
    /**
     * Adds a validator to the suite.
     * @param Validator $validator
     * @access public
     * @since 1.0
     */
    public function addValidator(Validator $validator)
    {
        $this->_validators[] = $validator;
    }
    /**
     * Runs the validators.
     * @access public
     * @since 1.0
     */
    public function validate()
    {
        foreach ($this->_validators as $validator) {
            $validator->validate();
            if (! $validator->isValid()) {
                while ($this->_errors[] = $validator->getError()) {
                }
            }
        }
    }
    /**
     * Gets the current error.
     * @access public
     * @since 1.0
     */
    public function getError()
    {
        return array_pop($this->_errors);
    }
    /**
     * Tells if the suite of validators is valid and there are no errors.
     * @access public
     * @since 1.0
     */
    public function isValid()
    {
        return count($this->_errors) ? false : true;
    }
}
