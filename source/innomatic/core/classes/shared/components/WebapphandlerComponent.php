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
require_once ('innomatic/application/ApplicationComponent.php');

/**
 * Webapphandler component handler.
 *
 * A Webapp Handler is the front controller for a certain mapping in a webapp
 * as defined in the web.xml file for that webapp.
 *
 * @copyright  1999-2012 Innoteam Srl
 * @license    http://www.innomatic.org/license/   BSD License
 * @link       http://www.innomatic.org
 * @since      Class available since Release 5.0
 */
class WebapphandlerComponent extends ApplicationComponent
{
    public function __construct($rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
    }
    public static function getType()
    {
        return 'webapphandler';
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
        if (! strlen($params['name']) or ! strlen($params['class']) or ! strlen($params['urlpattern'])) {
            $this->mLog->logEvent('WebapphandlerComponent::doInstallAction', 'Empty webapp handler parameters in application ' . $this->appname, \Innomatic\Logging\Logger::ERROR);
            return false;
        }
        $web_xml_file = InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/web.xml';
        $sx = simplexml_load_file($web_xml_file);
        // Handler
        $ok = true;
        foreach ($sx->handler as $ha) {
            if ("$ha->handlername" == $params['name']) {
                // A webapp handler with the same name has been already found.
                $ok = false;
                break;
            }
        }
        if ($ok) {
            $handler = $this->simplexml_addChild($sx, 'handler');
            $this->simplexml_addChild($handler, 'handlername', $params['name']);
            $this->simplexml_addChild($handler, 'handlerclass', $params['class']);
            /*
 * This is supported only by PHP >= 5.1.3

            $handler = $sx->addChild('handler');
            $handler->addChild('handlername', $params['name']);
            $handler->addChild('handlerclass', $params['class']);
 */
        }
        // Handler mapping
        $ok = true;
        foreach ($sx->handlermapping as $hm) {
            if ("$hm->handlername" == $params['name']) {
                // A mapping to a webapp handler with the same name has been already found.
                $ok = false;
                break;
            }
        }
        if ($ok) {
            $handler = $this->simplexml_addChild($sx, 'handlermapping');
            $this->simplexml_addChild($handler, 'handlername', $params['name']);
            $this->simplexml_addChild($handler, 'urlpattern', $params['urlpattern']);
            /*
 * This is supported only by PHP >= 5.1.3

            $handler = $sx->addChild('handlermapping');
            $handler->addChild('handlername', $params['name']);
            $handler->addChild('urlpattern', $params['urlpattern']);
 */
        }
        // Updates the web.xml file.
        return file_put_contents($web_xml_file, $sx->asXML());
    }
    public function doUninstallAction($params)
    {
        // Checks component name.
        if (! strlen($params['name']) or ! strlen($params['class']) or ! strlen($params['urlpattern'])) {
            $this->mLog->logEvent('WebapphandlerComponent::doUninstallAction', 'Empty webapp handler parameters in application ' . $this->appname, \Innomatic\Logging\Logger::ERROR);
            return false;
        }
        $web_xml_file = InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/web.xml';
        $sx = simplexml_load_file($web_xml_file);
        // Removes any mapping to the webapp handler.
        foreach ($sx->handlermapping as $hm) {
            if ("$hm->handlername" == $params['name']) {
                $dom = dom_import_simplexml($hm);
                $dom->parentNode->removeChild($dom);
            }
        }
        // Removes the webapp handler.
        foreach ($sx->handler as $ha) {
            if ("$ha->handlername" == $params['name']) {
                $dom = dom_import_simplexml($ha);
                $dom->parentNode->removeChild($dom);
            }
        }
        // Updates the web.xml file.
        return file_put_contents($web_xml_file, $sx->asXML());
    }
    public function doUpdateAction($params)
    {
        // Checks component name.
        if (! strlen($params['name']) or ! strlen($params['class']) or ! strlen($params['urlpattern'])) {
            $this->mLog->logEvent('WebapphandlerComponent::doUpdateAction', 'Empty webapp handler parameters in application ' . $this->appname, \Innomatic\Logging\Logger::ERROR);
            return false;
        }
        $web_xml_file = InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/web.xml';
        $sx = simplexml_load_file($web_xml_file);
        // Keeps track if the webapp handler is found in web.xml file.
        $found_handler = false;
        $found_mapping = false;
        // Updates the webapp handler.
        foreach ($sx->handler as $ha) {
            if ("$ha->handlername" == $params['name']) {
                $ha->handlerclass = $params['class'];
                $found_handler = true;
            }
        }
        // Updates the mapping to the webapp handler.
        foreach ($sx->handlermapping as $hm) {
            if ("$hm->handlername" == $params['name']) {
                $hm->urlpattern = $params['urlpattern'];
                $found_mapping = true;
            }
        }
        // If the webapp handler or the mapping weren't found, installs them.
        if (! $found_handler or ! $found_mapping) {
            unset($sx);
            return $this->doInstallAction($params);
        }
        // Updates the web.xml file.
        return file_put_contents($web_xml_file, $sx->asXML());
    }
    private function simplexml_addChild($parent, $name, $value = '')
    {
        $new_child = new SimpleXMLElement("<$name>$value</$name>");
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
