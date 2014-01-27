<?php
/**
 * Innomatic
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @copyright  2004-2014 Innoteam Srl
 * @license    http://www.innomatic.org/license/   BSD License
 * @link       http://www.innomatic.org
 * @since      Class available since Release 6.3.0
*/
namespace Innomatic\Webapp\Deploy;

/**
 *
 * @since 6.3.0
 * @author Alex Pagnoni <alex.pagnoni@innoteam.it>
 */
class WebAppLocator implements Serializable
{

    protected $method;

    protected $location;

    protected $adminMethod;

    public function __construct($method, $location, $adminMethod = '')
    {
        $this->method = $method;
        $this->location = $location;
        $this->adminMethod = $adminMethod;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getLocation()
    {
        return $this->location;
    }

    public function getAdminMethod()
    {
        return $this->adminMethod;
    }
}

?>