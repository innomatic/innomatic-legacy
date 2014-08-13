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
namespace Shared\Components;

/**
 * Component component handler.
 */
class ComponentComponent extends \Innomatic\Application\ApplicationComponent
{
    public $eltype;

    /* public __construct($rootda, $domainda, $appname, $name, $basedir) {{{ */
    /**
     * Component constructor.
     *
     * @param \Innomatic\Dataaccess\DataAccess $rootda Root data access
     * @param \Innomatic\Dataaccess\DataAccess $domainda Domain data access
     * @param string $appname Application identifier
     * @param string $name Component name
     * @param string $basedir Base directory
     * @access public
     * @return void
     */
    public function __construct($rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
        $this->eltype = new \Innomatic\Application\ApplicationComponentFactory($rootda);
    }
    /* }}} */

    /* public getType() {{{ */
    /**
     * Returns the component type identifier.
     *
     * @static
     * @access public
     * @return string The component type
     */
    public static function getType()
    {
        return 'component';
    }
    /* }}} */

    /* public getPriority() {{{ */
    /**
     * Returns the component type priority over other types.
     *
     * @static
     * @access public
     * @return integer The component priority
     */
    public static function getPriority()
    {
        return 100;
    }
    /* }}} */

    /* public getIsDomain() {{{ */
    /**
     * Checks if the component type is domain based.
     *
     * Is true when the component type executes some actions when enabled or
     * disabled to a domain.
     *
     * @static
     * @access public
     * @return boolean Whether the component is domain based or not
     */
    public static function getIsDomain()
    {
        return false;
    }
    /* }}} */

    /* public getIsOverridable() {{{ */
    /**
     * Checks if the component type is overridable by other components.
     *
     * @static
     * @access public
     * @return boolean Whether the component is overridable or not
     */
    public static function getIsOverridable()
    {
        return false;
    }
    /* }}} */

    public function doInstallAction($params)
    {
        $result = false;
        /*
        if (strlen($params['class'])) {
            $class_elem = new ClassComponent($this->rootda,$this->domainda,$this->appname,$params['class'],$params['class'],$this->basedir);
            $class_params['name'] = $class_params['file'] = $params['class'];
            $class_elem->Install($class_params);
            //$this->rootda->execute('INSERT INTO applications_components_types (id,typename,priority,domain,file) VALUES ('.$this->rootda->getNextSequenceValue('applications_components_types_id_seq').','.$this->rootda->formatText($params['type']).','.$component['priority'].','.$this->rootda->formatText(($component['domain'] ? $this->rootda->fmttrue : $this->rootda->fmtfalse)).','.$this->rootda->formatText(basename($filepath)).')');
        }
        */
        if (strlen($params['file'])) {
            $params['file'] = $this->basedir . '/core/classes/shared/components/' . basename($params['file']);
            if (@copy($params['file'], \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/classes/shared/components/' . basename($params['file']))) {
                @chmod(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/classes/shared/components/' . basename($params['file']), 0644);
                $params['filepath'] = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/classes/shared/components/' . basename($params['file']);
                if ($this->eltype->Install($params)) {
                    $result = true;
                }
            } else {
                $this->mLog->logEvent('innomatic.componentcomponent.componentcomponent.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to copy component file (' . $params['file'] . ') to its destination (' . \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/classes/shared/components/' . basename($params['file']) . ')', \Innomatic\Logging\Logger::ERROR);
            }
        } else {
            $this->mLog->logEvent('innomatic.componentcomponent.componentcomponent.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty component file name', \Innomatic\Logging\Logger::ERROR);
        }
        return $result;
    }

    public function doUninstallAction($params)
    {
        $result = false;
        $params['filepath'] = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/classes/shared/components/' . basename($params['file']);
        if ($this->eltype->Uninstall($params)) {
            if (@unlink(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/classes/shared/components/' . basename($params['file']))) {
                $result = true;
            }
        }
        return $result;
    }

    public function doUpdateAction($params)
    {
        $result = false;
        if (strlen($params['file'])) {
            $params['file'] = $this->basedir . '/core/classes/shared/components/' . basename($params['file']);
            if (@copy($params['file'], \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/classes/shared/components/' . basename($params['file']))) {
                @chmod(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/classes/shared/components/' . basename($params['file']), 0644);
                $params['filepath'] = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/classes/shared/components/' . basename($params['file']);
                if ($this->eltype->Update($params)) {
                    $result = true;
                }
            } else
                $this->mLog->logEvent('innomatic.componentcomponent.componentcomponent.doupdateaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to copy component file (' . $params['file'] . ') to its destination (' . \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome() . 'core/classes/shared/components/' . basename($params['file']) . ')', \Innomatic\Logging\Logger::ERROR);
        } else
            $this->mLog->logEvent('innomatic.componentcomponent.componentcomponent.doupdateaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty component file name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
}
