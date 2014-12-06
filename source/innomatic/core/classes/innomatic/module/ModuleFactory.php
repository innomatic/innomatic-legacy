<?php
namespace Innomatic\Module;

use \Innomatic\Module\Server;

/**
 * This is the Module objects factory.
 *
 * This is the central point for transparently obtaining a Module object, may it
 * be locally or remotely located.
 *
 * Modules must be requested to the factory and not instanced directly, except
 * when dealing with low level details is needed, or for particular performance
 * rmoduleons. Modules that are manually instantiated needs an already prepared
 * configuration.
 *
 * The factory automatically retrieves the object from the local context or
 * from a remote server by parsing the given Module locator.
 *
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 * @copyright Copyright 2004-2014 Innomatic Company
 * @since 5.1
 */
class ModuleFactory
{
    /**
     * Gets a Module object instance.
     *
     * This is the static method for obtaining a Module object in a transparent
     * way, without having to deal with the Module locator parsing.
     *
     * @since 5.1
     * @param ModuleLocator $locator Module locator object.
     * @return ModuleObject The Module object.
     */
    public static function getModule(ModuleLocator $locator)
    {
        return $locator->isRemote() ? self::getRemoteModule($locator) : self::getLocalModule($locator);
    }

    /**
     * Gets an instance of a local Module object.
     *
     * This static method retrieves a Module located in local context, without
     * using the Module server. Module authentication is needed anyway.
     *
     * The method also builds the configuration for the Module.
     *
     * @since 5.1
     * @param ModuleLocator $locator Module locator object.
     * @return ModuleObject The Module object.
     */
    public static function getLocalModule(ModuleLocator $locator)
    {
        $location = $locator->getLocation();

        // Authenticates
        $authenticator = ModuleServerAuthenticator::instance('ModuleServerAuthenticator');
        if (!$authenticator->authenticate($locator->getUsername(), $locator->getPassword())) {
            throw new ModuleException('Not authorized');
        }

        $context = ModuleServerContext::instance('\Innomatic\Module\Server\ModuleServerContext');

        // Checks if Module exists
        $classes_dir = $context->getHome().'core/modules/'.$location.'/classes/';
        if (!is_dir($classes_dir)) {
            throw new ModuleException('Module does not exists');
        }

        // Checks if configuration file exists
        $moduleXml = $context->getHome().'core/modules/'.$location.'/setup/module.xml';
        if (!file_exists($moduleXml)) {
            throw new ModuleException('Missing module.xml configuration file');
        }
        $cfg = \Innomatic\Module\Util\ModuleXmlConfig::getInstance($moduleXml);
        $fqcn = $cfg->getFQCN();

        // Builds Module Data Access Source Name
        $dasn_string = $authenticator->getDASN($locator->getUsername(), $locator->getLocation());
        if (strpos($dasn_string, 'context:')) {
            $module_context = new ModuleContext($location);
            $dasn_string = str_replace('context:', $module_context->getHome(), $dasn_string);
        }
        $cfg->setDASN(new DataAccessSourceName($dasn_string));

        // Adds Module classes directory to classpath
        // TODO: Should add include path only if not already available
        set_include_path($classes_dir.PATH_SEPARATOR.get_include_path());
        require_once $fqcn.'.php';

        // Retrieves class name from Fully Qualified Class Name
        $class = strpos($fqcn, '/') ? substr($fqcn, strrpos($fqcn, '/') + 1) : $fqcn;

        // Instantiates the new class and returns it
        return new $class ($cfg);
    }

    /**
     * Gets an instance of a remote Module object.
     *
     * This static method retrieves a Module located in a remote Module server.
     *
     * @since 5.1
     * @param ModuleLocator $locator Module locator object.
     * @return ModuleGenericRemoteObject The Module object.
     */
    public static function getRemoteModule(ModuleLocator $locator)
    {
        return new \Innomatic\Module\Util\ModuleGenericRemoteObject($locator);
    }

    /**
     * Gets an instance of a Module session object.
     *
     * This static method retrieves the session object for a Module.
     *
     * Normally there's no need to manually call this method, since Module sessions
     * are transparently handled by the Module server.
     *
     * @since 5.1
     * @param ModuleLocator $locator Module locator object.
     * @return ModuleObject The Module object.
     */
    public static function getSessionModule(ModuleLocator $locator, $sessionId)
    {
        $context = new ModuleContext($locator->getLocation());
        $session = new \Innomatic\Module\Session\ModuleSession($context, $sessionId);
        return $session->retrieve();
    }
}
