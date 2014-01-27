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
class WebAppDeployerException extends RuntimeException
{

    /**
     * The specified location does not exist.
     */
    const ERROR_UNABLE_TO_FIND_WEBAPP = 1;

    /**
     * The specificed location is not a webapp.
     */
    const ERROR_NOT_A_WEBAPP = 2;

    /**
     * A webapp with that name already exists.
     */
    const ERROR_WEBAPP_ALREADY_EXISTS = 3;

    /**
     * The deployer is unable to deploy the specified webapp.
     */
    const ERROR_UNABLE_TO_DEPLOY_WEBAPP = 4;

    /**
     * The deployer is unable to undeploy the specified webapp.
     */
    const ERROR_UNABLE_TO_UNDEPLOY_WEBAPP = 5;

    /**
     * The deployer is unable to redeploy the specified webapp.
     */
    const ERROR_UNABLE_TO_REDEPLOY_WEBAPP = 6;

    /**
     * The specified webapp does not exists in webapps home.
     */
    const ERROR_WEBAPP_DOES_NOT_EXISTS = 7;

    public function WebAppDeployerException($code)
    {
        $this->errcode = $code;
        parent::__construct($this->getErrorMessage($code));
    }

    public function toString()
    {
        return $this->getMessage();
    }

    public function getErrorCode()
    {
        return $this->errcode;
    }

    public function getErrorMessage($code)
    {
        switch ($code) {
            case WebAppDeployerException::ERROR_UNABLE_TO_FIND_WEBAPP:
                return 'Unable to find webapp';
            case WebAppDeployerException::ERROR_NOT_A_WEBAPP:
                return 'Not a webapp';
            case WebAppDeployerException::ERROR_WEBAPP_ALREADY_EXISTS:
                return 'Webapp already exists';
            case WebAppDeployerException::ERROR_UNABLE_TO_DEPLOY_WEBAPP:
                return 'Unable to deploy webapp';
            case WebAppDeployerException::ERROR_UNABLE_TO_UNDEPLOY_WEBAPP:
                return 'Unable to undeploy webapp';
            case WebAppDeployerException::ERROR_UNABLE_TO_REDEPLOY_WEBAPP:
                return 'Unable to redeploy webapp';
            case WebAppDeployerException::ERROR_WEBAPP_DOES_NOT_EXISTS:
                return 'Webapp does not exists';
        }
    }
}

?>