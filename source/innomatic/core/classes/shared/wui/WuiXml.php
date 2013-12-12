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
namespace Shared\Wui;

use \Innomatic\Xml\XMLParser;
/**
 * @package WUI
 */
class WuiXml extends \Innomatic\Wui\Widgets\WuiWidget
{
    /*! @public mDefinition string - XML definition of the widget. */
    public $mDefinition;
    /*! @public mDefinitionFile string - Optional file containing the XML definition.
        Overrides the "definition" argument if given. */
    public $mDefinitionFile;
    public function __construct (
        $elemName,
        $elemArgs = '',
        $elemTheme = '',
        $dispEvents = ''
    )
    {
        parent::__construct($elemName, $elemArgs, $elemTheme, $dispEvents);
        if (isset($this->mArgs['definitionfile']) and file_exists($this->mArgs['definitionfile'])) {
            $this->mDefinitionFile = $this->mArgs['definitionfile'];
            if (file_exists($this->mDefinitionFile)) {
                $this->mDefinition = file_get_contents($this->mDefinitionFile);
            }
        } else {
            if (isset($this->mArgs['definition'])) {
                $this->mDefinition = &$this->mArgs['definition'];
            }
        }
        // Adds default UTF8 encoding if no encoding has been defined in the xml text.
        if (strlen($this->mDefinition) and substr($this->mDefinition, 0, 5) != '<?xml') {
            $this->mDefinition = '<?xml version="1.0" encoding="utf-8" ?>' . $this->mDefinition;
        }
    }
    public function build(\Innomatic\Wui\Dispatch\WuiDispatcher $rwuiDisp)
    {
        $this->mrWuiDisp = $rwuiDisp;
        if ($this->mDefinition == null) {
            return false;
        }
        $root_element = &$this->getElementStructure(array_shift(XMLParser::getXmlTree($this->mDefinition)));
        $this->mLayout = ($this->mComments ? '<!-- begin ' . $this->mName . ' xml -->' : '') . ($root_element->build($this->mrWuiDisp) ? $root_element->render() : '') . ($this->mComments ? '<!-- end ' . $this->mName . " xml -->\n" : '');
        $this->mBuilt = true;
        return true;
    }
    /*!
     @abstract Returns an object corresponding to a given node.
     @param $element xml node - Element node.
     @result An Wui element object.
     */
    protected function &getElementStructure (&$element)
    {
        $result = false;
        $elementType = 'Wui' . strtolower($element['tag']);
        $elementName = '';
        $elementArgs = array();
        $elementEvents = array();
        $elementChildren = array();
        // Parse the element definition
        //
        if (isset($element['children']) and is_array($element['children']))
            while (list (, $node) = each($element['children'])) {
                switch ($node['tag']) {
                    case 'NAME':
                        $elementName = $node['value'];
                        break;
                    case 'ARGS':
                        if (isset($node['children']) and is_array($node['children'])) {
                            while (list (, $arg) = each($node['children'])) {
                                $attrs = isset($arg['attributes']) ? $arg['attributes'] : '';
                                $type = 'text';
                                if (is_object($attrs) or is_array($attrs)) {
                                    while (list ($attr, $value) = each($attrs)) {
                                        if ($attr == 'TYPE' and $value == 'array')
                                            $type = 'array';
                                        if ($attr == 'TYPE' and $value == 'encoded')
                                            $type = 'encoded';
                                    }
                                }
                                if ($type == 'array')
                                    $value = WuiXml::decode($arg['value']);
                                else
                                    if ($type == 'encoded') {
                                        $value = urldecode($arg['value']);
                                    } else {
                                        $value = $arg['value'];
                                    }
                                $elementArgs[strtolower($arg['tag'])] = $value;
                            }
                        }
                        break;
                    case 'EVENTS':
                        // Parses javascript events to be added to the widget.
                        if (isset($node['children']) and is_array($node['children'])) {
                            while (list (, $arg) = each($node['children'])) {
                                $attrs = isset($arg['attributes']) ? $arg['attributes'] : '';
                                $type = 'text';
                                if (is_object($attrs) or is_array($attrs)) {
                                    while (list ($attr, $value) = each($attrs)) {
                                        if ($attr == 'TYPE' and $value == 'array')
                                            $type = 'array';
                                        if ($attr == 'TYPE' and $value == 'encoded')
                                            $type = 'encoded';
                                    }
                                }
                                if ($type == 'array')
                                    $value = WuiXml::decode($arg['value']);
                                else
                                    if ($type == 'encoded') {
                                        $value = urldecode($arg['value']);
                                    } else {
                                        $value = $arg['value'];
                                    }
                                $elementEvents[strtolower($arg['tag'])] = $value;
                            }
                        }
                        break;
                    case 'CHILDREN':
                        if (isset($node['children']) and is_array($node['children'])) {
                            while (list (, $child_node) = each($node['children'])) {
                                $relem = &$elementChildren[];
                                $relem['args'] = array();
                                if (strtolower($child_node['tag']) == 'wuiobject') {
                                    $relem['element'] = unserialize(urldecode($child_node['value']));
                                } else {
                                    $relem['element'] = &$this->getElementStructure($child_node);
                                }
                                // Add not standard parameters
                                //
                                if (isset($child_node['attributes']) and is_array($child_node['attributes'])) {
                                    while (list ($attr, $value) = each($child_node['attributes'])) {
                                        $relem['args'][strtolower($attr)] = $value;
                                    }
                                }
                            }
                        }
                        break;
                }
            }
        if (! strlen($elementName)) {
            $elementName = $elementType . rand();
        }
        // Build element arguments array
        //
        while (list ($key, $val) = each($elementArgs)) {
            $elementArgs[$key] = $val;
        }
        // Tries to load the widget if it wasn't loaded.
        //
        if (! class_exists($elementType, true)) {
            $widget_name = strtolower($element['tag']);
            if (! defined(strtoupper($widget_name . '_WUI')) and file_exists(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/classes/shared/wui/Wui' . ucfirst($widget_name) . '.php')) {
                include_once (\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/classes/shared/wui/Wui' . ucfirst($widget_name) . '.php');
            }
        }
        // Create the element and add children if any
        //
        if (class_exists($elementType, true)) {
            $result = new $elementType($elementName, $elementArgs);
            // Adds the Javascript events to the widget.
            foreach ($elementEvents as $eventName => $eventCall) {
                $result->addEvent($eventName, $eventCall);
            }
            while (list (, $child_element) = each($elementChildren)) {
                if (isset($child_element['element']) and is_object($child_element['element'])) {
                    unset($tmp_array);
                    $tmp_array[] = $child_element['element'];
                    $args = array_merge($tmp_array, $child_element['args']);
                    call_user_func_array(array(&$result , 'AddChild'), $args);
                }
            }
        } else {
            $log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();
            $log->logEvent('innomatic.xml_wui.wuixml.getelementstructure', 'Element of type ' . $elementType . ' is not defined', \Innomatic\Logging\Logger::WARNING);
        }
        return $result;
    }
    public static function encode($var)
    {
        return urlencode(serialize($var));
    }
    public static function decode($string)
    {
        return unserialize(urldecode($string));
    }
    public static function cdata($data)
    {
        return '<![CDATA[' . $data . ']]>';
    }

    public static function getContentFromXml($name, $xmlText)
    {
        $wui_widget = new WuiXml($name, array('definition' => $xmlText));
        $wui_widget->build();
        $content = $wui_widget->render();
        return $content;
    }
}
