<?php
namespace Innomatic\Module\Session;

use \Innomatic\Module;

/**
 * Object for storing a ModuleObject's object value in a session.
 *
 * Sessions are required for maintaining object value contents between
 * requests for a same Module remote instance.
 *
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 * @copyright Copyright 2004-2014 Innoteam Srl
 * @since 5.1
 */
class ModuleSession
{
    /**
     * Module context.
     *
     * @var ModuleContext
     * @since 5.1
     */
    protected $context;
    /**
     * Session id
     * @var string
     * @since 5.1
     */
    protected $id;

    /**
     * Constructs the object.
     *
     * @param ModuleContext $context Context.
     * @param strind $id Optional session identifier.
     * @since 5.1
     */
    public function __construct(\Innomatic\Module\ModuleContext $context, $id = false)
    {
        $this->context = $context;
        $this->id = $id;
    }

    /**
     * Starts a new session.
     *
     * Assigns a session identifier.
     *
     * @since 5.1
     * @return void
     */
    public function start()
    {
        // !!! rand too short
        $this->id = rand();
    }

    /**
     * Saves a Module object instance in the session.
     *
     * @param ModuleObject $module Module object to be stored.
     * @since 5.1
     * @return void
     */
    public function save(ModuleObject $module)
    {
        if (!$this->id) {
            $this->start();
        }
        $sess_dir = $this->context->getHome().'sessions';
        if (!file_exists($sess_dir)) {
            mkdir($sess_dir);
        }
        file_put_contents($sess_dir.'/sess_'.$this->id.'.ser', $module->serialize());
    }

    /**
     * Retrieves a Module object from its session file.
     *
     * @since 5.1
     * @return ModuleObject
     */
    public function retrieve()
    {
        $sess_dir = $this->context->getHome().'sessions';
        if (!$this->id or !file_exists($sess_dir.'/sess_'.$this->id.'.ser')) {
            return null;
        }
        return unserialize(file_get_contents($sess_dir.'/sess_'.$this->id.'.ser'));
    }

    /**
     * Destroys a session and its session file.
     *
     * @since 5.1
     * @return void
     */
    public function destroy()
    {
        if (!$this->id) {
            return;
        }
        $sess_dir = $this->context->getHome().'sessions';
        if (file_exists($sess_dir.'/sess_'.$this->id.'.ser')) {
            unlink($sess_dir.'/sess_'.$this->id.'.ser');
        }
        $this->id = false;
    }

    /**
     * Retrieves session identifier.
     *
     * @since 5.1
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
}
