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
require_once ('innomatic/application/ApplicationComponent.php');
/**
 * Component component handler.
 */
class ComponentComponent extends ApplicationComponent
{
    public $eltype;
    public function ComponentComponent($rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
        require_once ('innomatic/application/ApplicationComponentFactory.php');
        $this->eltype = new ApplicationComponentFactory($rootda);
    }
    public static function getType()
    {
        return 'component';
    }
    public static function getPriority()
    {
        return 100;
    }
    public static function getIsDomain()
    {
        return false;
    }
    public static function getIsOverridable()
    {
        return false;
    }
    public function DoInstallAction($params)
    {
        $result = false;
        /*
        if (strlen($params['class'])) {
            require_once('innomatic/application.components.ClassComponent');
            $class_elem = new ClassComponent($this->rootda,$this->domainda,$this->appname,$params['class'],$params['class'],$this->basedir);
            $class_params['name'] = $class_params['file'] = $params['class'];
            $class_elem->Install($class_params);
            //$this->rootda->execute('INSERT INTO applications_components_types (id,typename,priority,domain,file) VALUES ('.$this->rootda->getNextSequenceValue('applications_components_types_id_seq').','.$this->rootda->formatText($params['type']).','.$component['priority'].','.$this->rootda->formatText(($component['domain'] ? $this->rootda->fmttrue : $this->rootda->fmtfalse)).','.$this->rootda->formatText(basename($filepath)).')');
        }
        */
        if (strlen($params['file'])) {
            $params['file'] = $this->basedir . '/core/classes/shared/components/' . basename($params['file']);
            if (@copy($params['file'], InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/classes/shared/components/' . basename($params['file']))) {
                @chmod(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/classes/shared/components/' . basename($params['file']), 0644);
                $params['filepath'] = InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/classes/shared/components/' . basename($params['file']);
                if ($this->eltype->Install($params)) {
                    $result = true;
                }
            } else
                $this->mLog->logEvent('innomatic.componentcomponent.componentcomponent.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to copy component file (' . $params['file'] . ') to its destination (' . InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/classes/shared/components/' . basename($params['file']) . ')', \Innomatic\Logging\Logger::ERROR);
        } else
            $this->mLog->logEvent('innomatic.componentcomponent.componentcomponent.doinstallaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty component file name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
    public function DoUninstallAction($params)
    {
        $result = false;
        $params['filepath'] = InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/classes/shared/components/' . basename($params['file']);
        if ($this->eltype->Uninstall($params)) {
            if (@unlink(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/classes/shared/components/' . basename($params['file']))) {
                $result = true;
            }
        }
        return $result;
    }
    public function DoUpdateAction($params)
    {
        $result = false;
        if (strlen($params['file'])) {
            $params['file'] = $this->basedir . '/core/classes/shared/components/' . basename($params['file']);
            if (@copy($params['file'], InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/classes/shared/components/' . basename($params['file']))) {
                @chmod(InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/classes/shared/components/' . basename($params['file']), 0644);
                $params['filepath'] = InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/classes/shared/components/' . basename($params['file']);
                if ($this->eltype->Update($params)) {
                    $result = true;
                }
            } else
                $this->mLog->logEvent('innomatic.componentcomponent.componentcomponent.doupdateaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Unable to copy component file (' . $params['file'] . ') to its destination (' . InnomaticContainer::instance('innomaticcontainer')->getHome() . 'core/classes/shared/components/' . basename($params['file']) . ')', \Innomatic\Logging\Logger::ERROR);
        } else
            $this->mLog->logEvent('innomatic.componentcomponent.componentcomponent.doupdateaction', 'In application ' . $this->appname . ', component ' . $params['name'] . ': Empty component file name', \Innomatic\Logging\Logger::ERROR);
        return $result;
    }
}
