<?php  

require_once('innomatic/module/server/ModuleServerContext.php');
require_once('innomatic/util/Singleton.php');

/**
 * Authenticates and authorizes access to a Module object against an
 * username/password couple.
 *
 * @author Alex Pagnoni <alex.pagnoni@innoteam.it>
 * @copyright Copyright 2004-2013 Innoteam Srl
 * @since 5.1
 */
class ModuleServerAuthenticator extends Singleton {
    /**
     * Users structure.
     *
     * @var array
     * @access private
     * @since 5.1
     */
    private $structure;

    /**
     * Class constructor.
     *
     * @access public
     * @since 5.1
     */
    public function ___construct() {
        $this->parseConfig();
    }

    /**
     * Parses users configuration and stores it in a structured array.
     *
     * @access public
     * @since 5.1
     * @return void
     */
    public function parseConfig() {
        $context = ModuleServerContext::instance('ModuleServerContext');
        $xmldoc = simplexml_load_file($context->getHome().'core/conf/modules-users.xml');
        $this->structure = array ();
        foreach ($xmldoc->user as $user) {
            $userStr = "$user->username";
            $this->structure[$userStr]['password'] = "$user->password";
            // Give admin user access to all Modules.
            // Admin user has no DASN.
            if ($userStr == 'admin') {
                foreach ($context->getModuleList() as $module) {
                    $this->structure[$userStr]['modules'][$module] = '';
                }
            } else {
                foreach ($user->allowedmodules as $allowedmodules) {
                    foreach ($allowedmodules->allowedmodule as $allowedmodule) {
                        $this->structure[$userStr]['modules']["$allowedmodule->modulelocation"] = "$allowedmodule->moduledasn";
                    }
                }
            }
            foreach ($user->allowedactions as $allowedactions) {
                foreach ($allowedactions->allowedaction as $allowedaction) {
                    $this->structure[$userStr]['actions']["$allowedaction"] = 1;
                }
            }
        }
    }

    /**
     * Authenticates a login/password couple.
     *
     * @access public
     * @since 5.1
     * @param string $user Username.
     * @param string $password Password.
     * @return boolean
     */
    public function authenticate($user, $password) {
        if (isset ($this->structure[$user]) and $this->structure[$user]['password'] == $password) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Authorize an action for an authenticated user.
     *
     * @access public
     * @since 5.1
     * @param string $user Username.
     * @param string $action Action to be authorized.
     * @return boolean
     */
    public function authorizeAction($user, $action) {
        if (isset ($this->structure[$user]['actions'][$action])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Authorizes the execution of a Module for an user.
     *
     * @access public
     * @since 5.1
     * @param string $user Username.
     * @param string $location Name of the Module.
     * @return boolean
     */
    public function authorizeModule($user, $location) {
        if (isset ($this->structure[$user]['modules'][$location])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Retrieves password for an user.
     *
     * @access public
     * @since 5.1
     * @param $user Username.
     * @return boolean
     */
    public function getPassword($user) {
        return isset ($this->structure[$user]) ? $this->structure[$user]['password'] : false;
    }

    /**
     * Retrieves Data Access Source Name for an user.
     *
     * @access public
     * @since 5.1
     * @param $user Username.
     * @param $location Module name.
     * @return string Data Access Source Name.
     */
    public function getDASN($user, $location) {
        return isset ($this->structure[$user]['modules'][$location]) ? $this->structure[$user]['modules'][$location] : false;
    }

    /**
     * Returns an array of the authorized Modules for an user.
     *
     * @access public
     * @since 5.1
     * @param $user Username.
     * @return array
     */
    public function getAuthorizedModuleS($user) {
        return isset ($this->structure[$user]['modules']) ? $this->structure[$user]['modules'] : array ();
    }

    /**
     * Returns an array of the authorized actions for an user.
     *
     * @access public
     * @since 5.1
     * @param string $user Username.
     * @return array
     */
    public function getAuthorizedActions($user) {
        return isset ($this->structure[$user]['actions']) ? $this->structure[$user]['actions'] : array ();
    }
}

?>