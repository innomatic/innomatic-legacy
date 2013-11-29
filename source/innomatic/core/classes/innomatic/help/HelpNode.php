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

class HelpNode
{
    private $_base;
    private $_node;
    private $_language;

    /*!
     @param node string - Node name.
     */
    public function __construct($base, $node, $language)
    {
        $this->_base = $base;
        $this->_node = $node;
        $this->_language = $language;
    }

    /*!
     @abstract Gets help node content.
     @result string - Help node content.
     */
    public function getUrl()
    {
        $result = false;
        if (strlen($this->_node)) {
            $node_name = $this->_node;
            $anchor = '';
            if (strpos($this->_node, '#')) {
                $node_name = substr($this->_node, 0, strpos($this->_node, '#'));
                $anchor = substr($this->_node, strpos($this->_node, '#'));
            }

            $reg = \Innomatic\Util\Registry::instance();

            // Tries specified language catalog
            //
            if (file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'shared/help/'.$this->_base.'/'.$this->_language.'/'.$node_name)) {
                $help_node_file = $this->_base.'/'.$this->_language.'/'.$node_name;
            }
            // Tries default catalog
            //
            else
                if (file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'shared/help/'.$this->_base.'/'.$node_name)) {
                    $help_node_file = $this->_base.'/'.$node_name;
                }
            // Tries Innomatic language catalog
            //
            else
                if (file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'shared/help/'.$this->_base.'/'.InnomaticContainer::instance('innomaticcontainer')->getLanguage().'/'.$node_name)) {
                    $help_node_file = $this->_base.'/'.InnomaticContainer::instance('innomaticcontainer')->getLanguage().'/'.$node_name;
                }
            // Tries English catalog
            //
            else
                if (file_exists(InnomaticContainer::instance('innomaticcontainer')->getHome().'shared/help/'.$this->_base.'/en/'.$node_name)) {
                    $help_node_file = $this->_base.'/en/'.$node_name;
                } else {
                    
                    $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
                    $log->logEvent('innomatic.txt.txt.getcontent', 'Unable to find an help node file for the specified help node ('.$node_name.') and language ('.$this->_language.') or fallback to another language', \Innomatic\Logging\Logger::ERROR);
                }
            if (!empty($help_node_file)) {
                return InnomaticContainer::instance('innomaticcontainer')->getBaseUrl(false).'/shared/help/'.$help_node_file.$anchor;
            }
        }
        return $result;
    }
}
