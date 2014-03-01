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
namespace Innomatic\Help;

class HelpNode
{

    protected $base;

    protected $node;

    protected $language;
    
    /*
     * ! @param node string - Node name.
     */
    public function __construct($base, $node, $language)
    {
        $this->base = $base;
        $this->node = $node;
        $this->language = $language;
    }
    
    /*
     * ! @abstract Gets help node content. @result string - Help node content.
     */
    public function getUrl()
    {
        $result = false;
        if (strlen($this->node)) {
            $node_name = $this->node;
            $anchor = '';
            if (strpos($this->node, '#')) {
                $node_name = substr($this->node, 0, strpos($this->node, '#'));
                $anchor = substr($this->node, strpos($this->node, '#'));
            }
            
            $reg = \Innomatic\Util\Registry::instance();
            
            // Tries specified language catalog
            //
            if (file_exists(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'shared/help/' . $this->base . '/' . $this->language . '/' . $node_name)) {
                $help_node_file = $this->base . '/' . $this->language . '/' . $node_name;
            }             // Tries default catalog
            //
            else 
                if (file_exists(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'shared/help/' . $this->base . '/' . $node_name)) {
                    $help_node_file = $this->base . '/' . $node_name;
                }                 // Tries Innomatic language catalog
                //
                else 
                    if (file_exists(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'shared/help/' . $this->base . '/' . \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLanguage() . '/' . $node_name)) {
                        $help_node_file = $this->base . '/' . \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLanguage() . '/' . $node_name;
                    }                     // Tries English catalog
                    //
                    else 
                        if (file_exists(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'shared/help/' . $this->base . '/en/' . $node_name)) {
                            $help_node_file = $this->base . '/en/' . $node_name;
                        } else {
                            $log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();
                            $log->logEvent('innomatic.txt.txt.getcontent', 'Unable to find an help node file for the specified help node (' . $node_name . ') and language (' . $this->language . ') or fallback to another language', \Innomatic\Logging\Logger::ERROR);
                        }
            if (! empty($help_node_file)) {
                return \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getBaseUrl(false) . '/shared/help/' . $help_node_file . $anchor;
            }
        }
        return $result;
    }
}
