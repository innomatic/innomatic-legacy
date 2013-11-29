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

require_once('innomatic/core/InnomaticContainer.php');
require_once('innomatic/dataaccess/DataAccess.php');
require_once('innomatic/util/Singleton.php');

/**
 * Web User Interface.
 *
 * This is the WUI container.
 *
 * @package WUI
 */
class Wui extends Singleton
{
    /*! @var mrRootDb DataAccess class - Innomatic database handler. */
    private $mrRootDb;
    /*! @var mChilds array - Array of the structure main childs. */
    private $mChilds = array();
    /*! @var mDisp WuiDispatcher class - Wui internal dispatcher, called "wui". */
    private $mDisp;
    /*! @var mLayout string - Structure layout. Filled by Wui->Build member. */
    public $mLayout;
    /*! @var mBuilt bool - True if the structure has been built. */
    public $mBuilt;
    /*! @var mLoadedWidgets array - Array of the loaded widgets. */
    public $mLoadedWidgets = array();
    /*! @var mLastError integer - Last error id. */
    public $mLastError;
    /*! @var mForceSetup boolean - TRUE if the check for setup phase must be skipped. Useful only for Innomatic. */
    private $mForceSetup;
    public $parameters;
    private $mThemeName = '';
    private $mThemeHandler;
    private $registeredAjaxCalls = array();
    private $registeredAjaxSetupCalls = array();

    const DEFAULT_THEME = 'flattheme';

    /*!
     @param rrootDb DataAccess class - Innomatic database handler.
     @param forceSetup boolean - TRUE if the check for setup phase must be skipped. Useful only for Innomatic.
     */
    public function ___construct($forceSetup = false)
    {
        $this->mBuilt = false;
        // Parameters must be extracted before starting any dispatcher or validator
        //
        $this->parameters = $this->arrayMergeClobber(
            $this->arrayMergeClobber($_GET, $_POST), $_FILES
        );
        require_once('innomatic/wui/dispatch/WuiDispatcher.php');
        $this->mDisp = new WuiDispatcher('wui');
        $this->mForceSetup = $forceSetup;
        if (InnomaticContainer::instance('innomaticcontainer')->getState() != InnomaticContainer::STATE_SETUP
        or ($this->mForceSetup and InnomaticContainer::instance('innomaticcontainer')->getState() == InnomaticContainer::STATE_SETUP)) {
            $rootDA = InnomaticContainer::instance('innomaticcontainer')->getDataAccess();
            if (is_object($rootDA)) {
                $this->mrRootDb = $rootDA;
            }
        }
    }

    /*!
     @discussion Loads the handler for a widget class.
     @param widgetName string - widget class name to load.
     @result True if the widget has been loaded. May return false if the widget handler file doesn't exists.
     */
    public function loadWidget($widgetName)
    {
        if (class_exists('wui'.$widgetName)) {
            return true;
        }

        $widgetFile = InnomaticContainer::instance('innomaticcontainer')->getHome()
            . 'core/classes/shared/wui/Wui' . ucfirst($widgetName) . '.php';
        $result = include_once($widgetFile);

        if (!$result) {
            require_once('innomatic/logging/Logger.php');
            $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
            $log->logEvent(
                'innomatic.wui.wui.loadwidget',
                'Unable to load widget handler file '
                . InnomaticContainer::instance('innomaticcontainer')->getHome()
                . 'core/classes/shared/wui/Wui'
                . ucfirst($widgetName) . '.php', Logger::ERROR
            );
            require_once('innomatic/wui/WuiException.php');
            throw new WuiException(WuiException::MISSING_WIDGET_FILE);
        }

        $this->mLoadedWidgets[$widgetName] = $widgetName;
        return true;
    }

    /*!
     @abstract Loads all the widgets.
     @discussion Loads all the widgets in the wui_widgets table.
     Not functional during Innomatic setup phase.
     @result True if the widgets have been loaded.
     */
    public function loadAllWidgets()
    {
        $result = false;
        require_once('innomatic/core/InnomaticContainer.php');
        $innomatic = InnomaticContainer::instance('innomaticcontainer');

        if ($innomatic->getState() == InnomaticContainer::STATE_DEBUG) {
            $innomatic->getLoadTimer()->Mark('start - Wui::LoadAllWidgets()');
        }
        if ($innomatic->getState() != InnomaticContainer::STATE_SETUP
        or ($innomatic->getState() == InnomaticContainer::STATE_SETUP
        and $this->mForceSetup)) {
            if (is_object($this->mrRootDb)) {
                $query = $this->mrRootDb->execute('SELECT name FROM wui_widgets');

                if ($query) {
                    $result = true;

                    // Load every widget
                    //
                    while (!$query->eof) {
                        // Load the widget and check if the widget file exists
                        //
                        if (!$this->loadWidget($query->getFields('name'))) {
                            $result = false;
                            if ($this->mLastError == Wui::LOADWIDGET_FILE_NOT_EXISTS) {
                                require_once('innomatic/wui/WuiException.php');
                                throw new WuiException(WuiException::MISSING_WIDGET_FILE);
                            }
                        }
                        $query->moveNext();
                    }

                    if (!$result and strcmp($this->mLastError, Wui::LOADALLWIDGETS_A_WIDGET_FILE_NOT_EXISTS) == 0) {
                        require_once('innomatic/logging/Logger.php');
                        $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
                        $log->logEvent('innomatic.wui.wui.loadallwidgets', 'Unable to load at least one widget handler file', Logger::ERROR);
                    }
                } else {
                    require_once('innomatic/wui/WuiException.php');
                    throw new WuiException(WuiException::UNABLE_TO_RETRIEVE_WIDGETS_LIST);
                }
            } else {
                require_once('innomatic/wui/WuiException.php');
                throw new WuiException(WuiException::INVALID_INNOMATIC_DATAACCESS);
            }
        } else {
            require_once('innomatic/logging/Logger.php');
            $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
            $log->logEvent('innomatic.wui.wui.loadallwidgets', 'Function unavailable during Innomatic setup phase', Logger::WARNING);
            require_once('innomatic/wui/WuiException.php');
            throw new WuiException(WuiException::LOADALLWIDGETS_UNAVAILABLE);
        }
        if ($innomatic->getState() == InnomaticContainer::STATE_DEBUG) {
            $innomatic->getLoadTimer()->Mark('end - Wui::LoadAllWidgets()');
        }
        return $result;
    }

    /*!
     @discussion Adds a child widget to the structure.
     @param rchildWidget class WuiWidget - Adds a child widget to the structure.
     @result Always true.
     */
    public function addChild(WuiWidget $rchildWidget)
    {
        $this->mChilds[] = $rchildWidget;
        return true;
    }

    /*!
     @discussion Builds the structure.
     @result True if the structure has been built by the member.
     */
    public function build()
    {
        $result = false;

        if (!$this->mBuilt) {
            require_once('innomatic/core/InnomaticContainer.php');
            $innomatic = InnomaticContainer::instance('innomaticcontainer');
            if ($innomatic->getState() == InnomaticContainer::STATE_DEBUG) {
                $innomatic->getLoadTimer()->Mark('start - Wui::Build()');
            }

            $children_count = count($this->mChilds);
            if ($children_count) {
                // Builds the structure
                //
                for ($i = 0; $i < $children_count; $i ++) {
                    if ($this->mChilds[$i]->Build($this->mDisp))
                    $this->mLayout.= $this->mChilds[$i]->render();
                    $this->mChilds[$i]->Destroy();
                }
                $this->mBuilt = true;
                $result = true;
            }

            // Call the internal dispatcher, if not alread called
            //
            $this->mDisp->Dispatch();

            if ($innomatic->getState() == InnomaticContainer::STATE_DEBUG) {
                $innomatic->getLoadTimer()->Mark('stop - Wui::Build()');
            }
        }
        return $result;
    }

    /*!
     @abstract Renders the structure.
     @discussion If the structure has not been built, it will call the Wui->Build() member.
     @result True if the structure has been rendered
     */
    public function render()
    {
        if (!$this->mBuilt)
        $this->Build();

        if ($this->mBuilt) {
            WebAppContainer::instance('webappcontainer')->getProcessor()->getResponse()->addHeader('P3P', 'CP="CUR ADM OUR NOR STA NID"');
            echo $this->mLayout;
            return true;
        } else {
            require_once('innomatic/logging/Logger.php');
            $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
            $log->logEvent('innomatic.wui.wui.render', 'Unable to render wui', Logger::ERROR);
            require_once('innomatic/wui/WuiException.php');
            throw new WuiException(WuiException::UNABLE_TO_RENDER);
        }
        return false;
    }

    public function getDispatcher()
    {
        return $this->mDisp;
    }

    public static function showSourceComments()
    {
        static $show_comments;

        if (!isset($show_comments)) {
            if (InnomaticContainer::instance('innomaticcontainer')->getConfig()->Value('ShowWuiSourceComments') == '1'
            or InnomaticContainer::instance('innomaticcontainer')->getState() == InnomaticContainer::STATE_DEBUG) {
                $show_comments = true;
            } else {
                $show_comments = false;
            }
        }

        return $show_comments;
    }

    private function arrayMergeClobber($a1, $a2)
    {
        if (!is_array($a1) || !is_array($a2)) {
            return false;
        }
        $newarray = $a1;
        foreach ($a2 as $key => $val) {
            if (is_array($val) and isset($newarray[$key]) and is_array($newarray[$key])) {
                $newarray[$key] = $this->arrayMergeClobber($newarray[$key], $val);
            } else {
                $newarray[$key] = $val;
            }
        }

        return $newarray;
    }

    public function &getParameters()
    {
        return $this->parameters;
    }

    public function getThemeName()
    {
        return strlen($this->mThemeName) ? $this->mThemeName : Wui::DEFAULT_THEME;
    }

    public function getTheme()
    {
        if (!is_object($this->mThemeHandler)) {
            $this->setTheme($this->getThemeName());
        }
        return $this->mThemeHandler;
    }

    public function setTheme($name)
    {
        require_once('innomatic/wui/theme/WuiTheme.php');
        $this->mThemeHandler = new WuiTheme(
            InnomaticContainer::instance('innomaticcontainer')->getDataAccess(),
            $name
        );
        $this->mThemeName = $name;
    }

    public static function utf8_entities($string)
    {
        return htmlentities($string, ENT_QUOTES, 'UTF-8');
    }

    public function registerAjaxCall($callName)
    {
        $this->registeredAjaxCalls[$callName] = true;
    }

    public function getRegisteredAjaxCalls()
    {
        return $this->registeredAjaxCalls;
    }

    public function isRegisteredAjaxCall($callName)
    {
        return isset($this->registeredAjaxCalls[$callName]);
    }

    public function unregisterAjaxCall($callName)
    {
        if (isset($this->registeredAjaxCalls[$callName])) {
            unset($this->registeredAjaxCalls[$callName]);
        }
    }

    public function countRegisteredAjaxCalls()
    {
        return count($this->registeredAjaxCalls);
    }

    public function registerAjaxSetupCall($call)
    {
        $this->registeredAjaxSetupCalls[] = $call;
    }

    public function getRegisteredAjaxSetupCalls()
    {
        return $this->registeredAjaxSetupCalls;
    }

    public function countRegisteredAjaxSetupCalls()
    {
        return count($this->registeredAjaxSetupCalls);
    }
}
