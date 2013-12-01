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
namespace Shared\Components;

use \Innomatic\Core\InnomaticContainer;

/**
 * Ajaxcall component handler.
 *
 * @copyright  1999-2012 Innoteam Srl
 * @license    http://www.innomatic.org/license/   BSD License
 * @link       http://www.innomatic.org
 * @since      Class available since Release 5.0
 */
class AjaxcallComponent extends \Innomatic\Application\ApplicationComponent
{
    public function __construct($rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
    }
    public function __destruct()
    {
        // Flushes the xajax call function list cache.
        $ajax_cfg = new \Innomatic\Ajax\XajaxConfig();
        $ajax_cfg->flushCache(
            \Innomatic\Webapp\WebAppContainer::instance('webappcontainer')->getCurrentWebApp()
        );
    }
    public static function getType()
    {
        return 'ajaxcall';
    }
    public static function getPriority()
    {
        return 0;
    }
    public static function getIsDomain()
    {
        return false;
    }
    public static function getIsOverridable()
    {
        return false;
    }
    public function doInstallAction($params)
    {
        // Checks component name.
        if (! strlen($params['name']) or ! strlen($params['classname']) or ! strlen($params['method']) or ! strlen($params['classfile'])) {
            $this->mLog->logEvent('AjaxcallComponent::doInstallAction', 'Empty parameters in application ' . $this->appname, \Innomatic\Logging\Logger::ERROR);
            return false;
        }

        $ajax_xml_file = InnomaticContainer::instance(
            'innomaticcontainer'
        )->getHome()
        . 'core/conf/ajax.xml';

        $sx = simplexml_load_file($ajax_xml_file);
        // Function
        $ok = true;
        foreach ($sx->function as $ha) {
            if ("$ha->name" == $params['name']) {
                // An Ajax call function with the same name has been already
                // found.
                $ok = false;
                break;
            }
        }
        if ($ok) {
            $handler = $this->simplexml_addChild($sx, 'function');
            $this->simplexml_addChild($handler, 'name', $params['name']);
            $this->simplexml_addChild($handler, 'classname', $params['classname']);
            $this->simplexml_addChild($handler, 'method', $params['method']);
            $this->simplexml_addChild($handler, 'classfile', $params['classfile']);
            /*
 * This is supported only by PHP >= 5.1.3

            $handler = $sx->addChild('function');
            $handler->addChild('handlername', $params['name']);
            $handler->addChild('handlerclass', $params['class']);
 */
        }
        // Updates the ajax.xml file.
        return file_put_contents($ajax_xml_file, $sx->asXML());
    }
    public function doUninstallAction($params)
    {
        // Checks component name.
        if (! strlen($params['name']) or ! strlen($params['classname']) or ! strlen($params['method']) or ! strlen($params['classfile'])) {
            $this->mLog->logEvent('AjaxcallComponent::doUninstallAction', 'Empty parameters in application ' . $this->appname, \Innomatic\Logging\Logger::ERROR);
            return false;
        }
        $web_xml_file = InnomaticContainer::instance('innomaticcontainer')->getHome()
            . 'core/conf/ajax.xml';
        $sx = simplexml_load_file($web_xml_file);
        // Removes the Ajax call function.
        foreach ($sx->function as $hm) {
            if ("$hm->name" == $params['name']) {
                $dom = dom_import_simplexml($hm);
                $dom->parentNode->removeChild($dom);
            }
        }
        // Updates the ajax.xml file.
        return file_put_contents($web_xml_file, $sx->asXML());
    }
    public function doUpdateAction($params)
    {
        // Checks component name.
        if (! strlen($params['name']) or ! strlen($params['classname']) or ! strlen($params['method']) or ! strlen($params['classfile'])) {
            $this->mLog->logEvent(
                'AjaxcallComponent::doUpdateAction',
                'Empty parameters in application '
                . $this->appname,
                \Innomatic\Logging\Logger::ERROR
            );
            return false;
        }
        $web_xml_file = InnomaticContainer::instance('innomaticcontainer')->getHome()
            . 'core/conf/ajax.xml';
        $sx = simplexml_load_file($web_xml_file);
        // Keeps track if the Ajax call function is found in ajax.xml file.
        $found_handler = false;
        // Updates the Ajax call function.
        foreach ($sx->function as $ha) {
            if ("$ha->name" == $params['name']) {
                $ha->classname = $params['classname'];
                $ha->method = $params['method'];
                $ha->classfile = $params['classfile'];
                $found_handler = true;
            }
        }
        // If the function wasn't found, installs it.
        if (! $found_handler) {
            unset($sx);
            return $this->doInstallAction($params);
        }
        // Updates the ajax.xml file.
        return file_put_contents($web_xml_file, $sx->asXML());
    }
    private function simplexml_addChild($parent, $name, $value = '')
    {
        $new_child = new \SimpleXMLElement("<$name>$value</$name>");
        $node1 = dom_import_simplexml($parent);
        $dom_sxe = dom_import_simplexml($new_child);
        $node2 = $node1->ownerDocument->importNode($dom_sxe, true);
        $node1->appendChild($node2);
        return simplexml_import_dom($node2);
    }
    private function simplexml_addAttribute($parent, $name, $value = '')
    {
        $node1 = dom_import_simplexml($parent);
        $node1->setAttribute($name, $value);
        return simplexml_import_dom($node1);
    }
}
