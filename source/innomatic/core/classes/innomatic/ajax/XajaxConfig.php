<?php
/**
 * Innomatic
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2012 Innoteam Srl
 * @license    http://www.innomatic.org/license/   BSD License
 * @link       http://www.innomatic.org
 * @since      Class available since Release 5.0
*/
namespace Innomatic\Ajax;

class XajaxConfig
{
    public $functions;

    public static function getInstance(\Innomatic\Webapp\WebApp $wa, $xmlconfig)
    {
        if (file_exists($wa->getVarDir().'cache/XajaxConfig.ser')) {
            return unserialize(file_get_contents($wa->getVarDir().'cache/XajaxConfig.ser'));
        } else {
            $result = new XajaxConfig();
            $result->parseConfig($xmlconfig);
            if (!is_dir($wa->getVarDir().'cache/')) {
                @mkdir($wa->getVarDir().'cache/');
            }
            if (is_dir($wa->getVarDir().'cache/')) {
                @file_put_contents($wa->getVarDir().'cache/XajaxConfig.ser', serialize($result));
            }
            return $result;
        }
    }

    public function parseConfig($xmlconfig)
    {
        $cfg = simplexml_load_file($xmlconfig);

        foreach ($cfg->function as $function) {
            $name = sprintf('%s', $function->name);
            $this->functions[$name]['classname'] = sprintf('%s', $function->classname);
            $this->functions[$name]['method'] = sprintf('%s', $function->method);
            $this->functions[$name]['classfile'] = sprintf('%s', $function->classfile);
        }
    }

    public function getFunction($name)
    {
        if (isset($this->functions[$name])) {
            return $this->functions[$name];
        } else {
            return false;
        }
    }

    public function flushCache(\Innomatic\Webapp\WebApp $wa)
    {
        if (file_exists($wa->getVarDir().'cache/XajaxConfig.ser')) {
            unlink($wa->getVarDir().'cache/XajaxConfig.ser');
        }
    }
}
