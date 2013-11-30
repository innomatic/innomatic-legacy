<?php
namespace Innomatic\Module;

require_once('innomatic/module/server/ModuleServerContext.php');

/**
 * The context in which a Module runs.
 *
 * ModuleContext class is useful for retrieving information about the Module context,
 * like its location and filesystem home directory.
 *
 * @author Alex Pagnoni <alex.pagnoni@innoteam.it>
 * @copyright Copyright 2004-2013 Innoteam Srl
 * @since 5.1
 */
class ModuleContext
{
    /**
     * Module name.
     *
     * @access protected
     * @var string
     * @since 5.1
     */
    protected $location;
    /**
     * Module home directory in filesystem.
     *
     * @access protected
     * @var string
     * @since 5.1
     */
    protected $home;

    /**
     * Object constructor.
     *
     * @access public
     * @param string $location Module name.
     * @since 5.1
     */
    public function __construct($location)
    {
        $serverContext = ModuleServerContext::instance('ModuleServerContext');
        $this->location = $location;
        $this->home = $serverContext->getHome().'modules'.DIRECTORY_SEPARATOR.$location.DIRECTORY_SEPARATOR;
    }

    /**
     * Retrieves Module home directory path in filesystem.
     *
     * @access public
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
     * @access public
     * @since 5.1
     * @return string Module name.
     */
    public function getLocation()
    {
        return $this->location;
    }
}
