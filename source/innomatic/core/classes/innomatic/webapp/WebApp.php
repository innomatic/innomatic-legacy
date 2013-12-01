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
namespace Innomatic\Webapp;

/**
 * @since 5.0
 * @author Alex Pagnoni <alex.pagnoni@innoteam.it>
 * @copyright Copyright 2012 Innoteam Srl
 */
class WebApp
{
    // Base info
    protected $name;
    protected $home;
    protected $varDir;

    // Parameters
    protected $displayName;
    protected $description;
    protected $parameters;
    protected $mimeMappings;
    protected $handlerMappings;
    protected $welcomeFiles;
    protected $handlers;

    public function __construct($home)
    {
        $this->home = $home;
        $this->name = basename($home);

        // If the webapp contains classes, adds them to the include path.
        // The webapp classes take precedence over Innomatic ones.
        if (file_exists($this->home.'core/classes/')) {
            set_include_path($this->home.'core/classes/'.PATH_SEPARATOR.get_include_path());
        }

        // Sets var directory
        $container = WebAppContainer::instance('webappcontainer');
        if ($container->useDefaults() or !$container->isKey('webapps.var_dir') or $container->getKey('webapps.var_dir') == 'shared') {
            $this->varDir = RootContainer::instance('rootcontainer')->getHome().'innomatic/core/domains/'.$this->getName().'/var/';
        } else {
            $this->varDir = $this->getHome().'core/var/';
        }

        // Make var directories
        if (!is_dir($this->varDir)) {
            @mkdir($this->varDir, 0777, true);
        }

        // Init configuration
        // Must be called only after the cache directory has been set
        $this->parseConfig($home.'core/web.xml');
    }

    /**
     * Parses webapp configuration.
     */
    protected function parseConfig($xmlconfig)
    {
        $container = WebAppContainer::instance('webappcontainer');
        if ($container->useDefaults() or !$container->isKey('webapps.cache_config') or $container->getKey('webapps.cache_config')) {
            $cache_dir = $this->getVarDir().'cache/';
            if (file_exists($cache_dir.'WebAppConfig.ser')) {
                $cfg = unserialize(file_get_contents($cache_dir.'WebAppConfig.ser'));
            } else {
                $cfg = simplexml_load_file($xmlconfig);
                if (!is_dir($cache_dir)) {
                    @mkdir($cache_dir);
                }
                // TODO: Sistemare
                //@file_put_contents($cache_dir.'WebAppConfig.ser', serialize($cfg));
            }
        } else {
            $cfg = simplexml_load_file($xmlconfig);
        }

        $this->displayName = sprintf('%s', $cfg->displayname);
        $this->description = sprintf('%s', $cfg->description);
        foreach ($cfg->contextparam as $param) $this->parameters[sprintf('%s', $param->paramname)] = sprintf('%s', $param->paramvalue);
        foreach ($cfg->mimemapping as $mimemapping) $this->mimeMappings[sprintf('%s', $mimemapping->extension)] = sprintf('%s', $mimemapping->mimetype);
        foreach ($cfg->handlermapping as $handlermapping) $this->handlerMappings[sprintf('%s', $handlermapping->urlpattern)] = sprintf('%s', $handlermapping->handlername);
        foreach ($cfg->welcomefilelist as $welcomefile) $this->welcomeFiles[] = sprintf('%s', $welcomefile->welcomefile);
        foreach ($cfg->handler as $handler) {
            $name = sprintf('%s', $handler->handlername);
            $this->handlers[$name]['class'] = sprintf('%s', $handler->handlerclass);
            foreach ($handler->initparam as $params) {
                $this->handlers[$name]['params'][sprintf('%s', $params->paramname)] = sprintf('%s', $params->paramvalue);
            }
        }
    }

    /**
     * Checks if the given directory contains a valid webapp.
     */
    public static function isValid($home = '')
    {
        // TODO: it should check if the web.xml is valid too
        return file_exists((strlen($home) ? $home : $this->home).'core/web.xml');
    }

    /**
     * Removes webapp cache directory.
     */
    public function refresh()
    {
        if (is_dir($this->getVarDir().'cache/')) {
            \Innomatic\Io\Filesystem\DirectoryUtils::unlinkTree($this->getVarDir());
        }
    }

    /**
     * Returns webapp name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns webapp home directory.
     */
    public function getHome()
    {
        return $this->home;
    }

    /**
     * Returns webapp var directory.
     * Depending on the configuration, it can be resident inside webapp core/var directory
     * or inside general var directory.
     */
    public function getVarDir()
    {
        return $this->varDir;
    }

    /**
     * Returns public webapp name.
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * Returns webapp descriptio.
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns the value of an init parameter.
     */
    public function getInitParameter($param)
    {
        return isset($this->parameters[$param]) ? $this->parameters[$param] : false;
    }

    /**
     * Returns a list of the valid welcome files to be searched for when requesting a directory.
     */
    public function getWelcomeFiles()
    {
        return $this->welcomeFiles;
    }

    /**
     * Gets the handler corresponding to the given pattern.
     */
    public function getHandlerMapping($pattern)
    {
        return isset($this->handlerMappings[$pattern]) ? $this->handlerMappings[$pattern] : null;
    }

    /**
     * Gets the complete list of handler mappings.
     */
    public function getHandlerMappings()
    {
        return $this->handlerMappings;
    }

    /**
     * Removes an handler mapping.
     */
    public function removeHandlerMapping($pattern)
    {
        unset($this->handlerMappings[$pattern]);
    }

    /**
     * Returns the fully qualified class name for the given handler.
     */
    public function getHandler($name)
    {
        return isset($this->handlers[$name]['class']) ? $this->handlers[$name]['class'] : null;
    }

    /**
     * Returns the parameters array for the given handler.
     */
    public function getHandlerParameters($name)
    {
        $result = array();
        return isset($this->handlers[$name]['params']) ? $this->handlers[$name]['params'] : $result;
    }

    /**
     * Returns the mime type defined for the given file.
     */
    public function getMimeType($file)
    {
        $info = pathinfo($file);
        if (!isset($info['extension']))
            return null;
        return isset($this->mimeMappings[$pathinfo['extension']]) ? $this->mimeMappings[$pathinfo['extension']] : null;
    }
}
