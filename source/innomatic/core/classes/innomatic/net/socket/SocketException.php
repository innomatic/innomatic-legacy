<?php
/**
 * Innomatic
 *
 * LICENSE 
 * 
 * This source file is subject to the new BSD license that is bundled 
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2012 Innoteam S.r.l.
 * @license    http://www.innomatic.org/license/   BSD License
 * @link       http://www.innomatic.org
 * @since      Class available since Release 5.0
*/

/**
 * @author Alex Pagnoni <alex.pagnoni@innoteam.it>
 * @copyright Copyright 2003-2012 Innoteam S.r.l.
 * @since 1.0
 */
class SocketException extends RuntimeException
{
    public function SocketException($exception)
    {
        parent::__construct($exception);
    }

    public function __toString()
    {
        return 'Socket exception: '.parent::toString();
    }
}
