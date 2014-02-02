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
namespace Innomatic\Webapp;

/**
 * @since 1.0
 * @author Alex Pagnoni <alex.pagnoni@innoteam.it>
 * @copyright Copyright 2012 Innoteam Srl
 */
abstract class WebAppHandler
{
    protected $parameters;

    public function getInitParameter($param)
    {
        return isset($this->parameters[$param]) ? $this->parameters[$param] : null;
    }

    public function setInitParameters($params)
    {
        $this->parameters = $params;
    }

    abstract public function init();

    public function service(WebAppRequest $req, WebAppResponse $res)
    {
        switch ($req->getMethod()) {
            case 'GET':
                $this->doGet($req, $res);
                break;
            case 'POST':
                $this->doPost($req, $res);
                break;
            default:
                $res->sendError(WebAppResponse::SC_NOT_IMPLEMENTED);
                break;
        }
    }

    abstract public function doGet(WebAppRequest $req, WebAppResponse $res);

    abstract public function doPost(WebAppRequest $req, WebAppResponse $res);

    abstract public function destroy();
}
