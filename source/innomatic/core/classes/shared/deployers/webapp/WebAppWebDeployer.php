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
namespace Innomatic\Shared\Deployers\Webapp;

/**
 *
 * @since 6.3.0
 * @author Alex Pagnoni <alex.pagnoni@innoteam.it>
 */
class WebAppWebDeployer extends WebAppArchiveDeployer
{

    public function deploy(\Innomatic\WebApp\WebAppLocator $locator)
    {
        $context = \Innomatic\WebApp\WebAppContainer::instance('\Innomatic\WebApp\WebAppContainer');
        $tmp_file = $context->getHome() . 'temp/inst_' . rand();
        
        $from = $locator->getLocation();
        list ($protocol, $uri) = split('//', $from);
        // file_put_contents($tmp_file, file_get_contents($protocol . '//' . urlencode($uri)));
        file_put_contents($tmp_file, file_get_contents($protocol . '//' . $uri));
        
        $arc_locator = new \Innomatic\WebApp\WebAppLocator('archive', $tmp_file);
        try {
            parent::deploy($arc_locator);
            $this->saveLocator($this->getName(), $locator);
        } catch (\Innomatic\WebApp\Deploy\WebAppDeployerException $e) {
            unlink($tmp_file);
            throw new \Innomatic\WebApp\Deploy\WebAppDeployerException($e->getErrorCode());
        }
        unlink($tmp_file);
    }

    public function redeploy(\Innomatic\WebApp\WebAppLocator $locator)
    {
        $context = \Innomatic\WebApp\WebAppContainer::instance('\Innomatic\WebApp\WebAppContainer');
        $tmp_file = $context->getHome() . 'temp/inst_' . rand();
        
        $from = $locator->getLocation();
        list ($protocol, $uri) = split('//', $from);
        // file_put_contents($tmp_file, file_get_contents($protocol . '//' . urlencode($uri)));
        file_put_contents($tmp_file, file_get_contents($protocol . '//' . $uri));
        
        $arc_locator = new \Innomatic\WebApp\WebAppLocator('archive', $tmp_file);
        try {
            parent::redeploy($arc_locator);
            $this->saveLocator($this->getName(), $locator);
        } catch (\Innomatic\WebApp\Deploy\WebAppDeployerException $e) {
            unlink($tmp_file);
            throw new \Innomatic\WebApp\Deploy\WebAppDeployerException($e->getErrorCode());
        }
        unlink($tmp_file);
    }
}

?>