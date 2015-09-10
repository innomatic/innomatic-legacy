<?php
namespace Innomatic\Module;

/**
 * The context in which a Module runs.
 *
 * ModuleContext class is useful for retrieving information about the Module context,
 * like its location and filesystem home directory.
 *
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 * @copyright Copyright 2004-2014 Innoteam Srl
 * @since 5.1
 */
class ModuleContext
{
    /**
     * Module name.
     *
     * @var string
     * @since 5.1
     */
    protected $location;
    /**
     * Module home directory in filesystem.
     *
     * @var string
     * @since 5.1
     */
    protected $home;

    /**
     * Object constructor.
     *
     * @param string $location Module name.
     * @since 5.1
     */
    public function __construct($location)
    {
        $serverContext = \Innomatic\Module\Server\ModuleServerContext::instance('moduleservercontext');
        $this->location = $location;
        $this->home = $serverContext->getHome().'modules'.DIRECTORY_SEPARATOR.$location.DIRECTORY_SEPARATOR;
    }

    /**
     * Retrieves Module home directory path in filesystem.
     *
     * @since 5.1
     * @return string Module home directory path.
     */
    public function getHome()
    {
        return $this->home;
    }

    /**
     * Retrieves Module name.
     *
     * @since 5.1
     * @return string Module name.
     */
    public function getLocation()
    {
        return $this->location;
    }
}
