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
 * @license    http://www.innomatic.io/license/ New BSD License
 * @link       http://www.innomatic.io
 * @since      Class available since Release 5.0
*/
namespace Innomatic\Net\Socket;

/**
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 * @copyright Copyright 2003-2012 Innoteam Srl
 * @since 1.0
 */
class SocketException extends \RuntimeException
{
    public function __construct($exception)
    {
        parent::__construct($exception);
    }

    public function __toString()
    {
        return 'Socket exception: '.parent::__toString();
    }
}
