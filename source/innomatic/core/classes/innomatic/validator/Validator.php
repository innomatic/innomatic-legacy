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
 * @license    http://www.innomaticplatform.com/license/   BSD License
 * @link       http://www.innomaticplatform.com
 * @since      Class available since Release 5.0
 */
namespace Innomatic\Validator;

/**
 * This is the abstract class for validation of generic data.
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 * @copyright Copyright 2012 Innoteam Srl
 * @since 5.0
 */
abstract class Validator
{
    /**
     * Array of errors.
     * @type array
     * @since 5.0
     */
    protected $errors = array();
    /**
     * Generic item to be validated.
     * @type mixed
     * @since 5.0
     */
    protected $item;
    /**
     * Array of validation parameters.
     * @type array
     * @since 5.0
     */
    protected $params = array();
    /**
     * Constructor.
     * @param mixed $item
     * @since 5.0
     */
    final public function __construct ($item, $params = array())
    {
        $this->item = $item;
        if (is_array($params) and count($params)) {
            foreach ($params as $param => $value) {
                $this->setParameter($param, $value);
            }
        }
    }
    /**
     * Starts the validation procedure.
     * @since 5.0
     */
    abstract public function validate ();
    /**
     * Adds an error to the errors list.
     * @param string $error
     * @since 5.0
     */
    protected function setError($error)
    {
        $this->errors[] = $error;
    }
    /**
     * Returns the current error from the errors list and advances
     * the internal counter.
     * @return string
     * @since 5.0
     */
    public function getError()
    {
        return array_pop($this->errors);
    }
    /**
     * Tells if there are no errors after calling Validator::validate().
     * @return boolean
     * @since 5.0
     */
    public function isValid()
    {
        return count($this->errors) ? false : true;
    }
    /**
     * Returns one of the internal parameters.
     * @param string $key
     * @return string
     * @since 5.0
     */
    public function getParameter($key)
    {
        return isset($this->params[$key]) ? $this->params[$key] : null;
    }
    /**
     * Sets a parameter.
     * Parameter are used by custom validators for passing extra
     * conditions.
     * @param string $key
     * @param string $value
     * @since 5.0
     */
    public function setParameter($key, $value)
    {
        $this->params[$key] = $value;
    }
}
