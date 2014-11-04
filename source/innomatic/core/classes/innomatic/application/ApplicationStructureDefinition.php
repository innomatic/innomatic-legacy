<?php
/**
 * Innomatic
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2014 Innomatic Company
 * @license    http://www.innomatic.io/license/ New BSD License
 * @link       http://www.innomatic.io
 */
namespace Innomatic\Application;

/**
 * Provides XML definition file handling.
 * 
 * This class reads a XML definition file and builds the application component
 * structure.
 * 
 * @since 5.0.0 introduced
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 */
class ApplicationStructureDefinition extends \Innomatic\Xml\XMLParser
{
    /*! @public mLog logger class - Log handler. */
    public $mLog;
    /*! @public deffile string - Definition file full path. */
    public $deffile;
    /*! @public data string - The whole content of the definition file. */
    public $data;
    /*! @public modstructure array - Array of the application components structure. */
    public $modstructure = array();
    /*! @public eltypes applicationcomponentfactory class - Application component types handler. */
    public $eltypes;
    /*! @public basedir string - Application base directory. */
    public $basedir;

    /*!
     @function ApplicationStructureDefinition

     @abstract Class constructor.

     @param basedir string - Application base directory.
     */
    public function __construct($basedir = '')
    {
        $container = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer');
        $this->mLog = $container->getLogger();
        $this->eltypes = new ApplicationComponentFactory($container->getDataAccess());
        $this->eltypes->fillTypes();
        parent::__construct();
        $this->basedir = $basedir;
        //$this->modstructure['generalpreinstall'] = '';
        //$this->modstructure['generalpreuninstall'] = '';
        //$this->modstructure['generalpostinstall'] = '';
        //$this->modstructure['generalpostuninstall'] = '';
        //$this->modstructure['domainpreinstall'] = '';
        //$this->modstructure['domainpreuninstall'] = '';
        //$this->modstructure['domainpostinstall'] = '';
        //$this->modstructure['domainpostuninstall'] = '';
        //$this->modstructure['generalpreupdate'] = '';
        //$this->modstructure['generalpostupdate'] = '';
        //$this->modstructure['domainpreupdate'] = '';
        //$this->modstructure['domainpostupdate'] = '';
    }

    /*!
     @function load_deffile
     @abstract Reads the structure file.
     @param deffile string - Full path of the definition file.
     */
    public function load_deffile($deffile)
    {
        $this->deffile = $deffile;
        $this->get_data(file_get_contents($this->deffile));
    }

    /*!
     @function get_data
     @abstract Returns the definition file content.
     @param data string - file path.
     */
    public function get_data($data)
    {
        $this->data = $data;
    }

    /*!
     @function _tagOpen
     @abstract Private member.
     @param tag sring - open tag.
     @param attrs array - attributes.
     */
    public function doTagOpen($tag, $attrs)
    {
        switch ($tag) {
            case 'APPLICATION' :
                break;

            case 'COMPONENTS' :
                break;

            case 'STRUCTURE' :
                break;

                // General cases
                //
            case 'GENERALPREINSTALL' : // Before installing the application
                $this->modstructure['generalpreinstall'] = $attrs['FILE'];
                break;

            case 'GENERALPREUNINSTALL' : // Before uninstalling the application
                $this->modstructure['generalpreuninstall'] = $attrs['FILE'];
                break;

            case 'GENERALPOSTINSTALL' : // After installing the application
                $this->modstructure['generalpostinstall'] = $attrs['FILE'];
                break;

            case 'GENERALPOSTUNINSTALL' : // After uninstalling the application
                $this->modstructure['generalpostuninstall'] = $attrs['FILE'];
                break;

                // Domain cases
                //
            case 'DOMAINPREINSTALL' : // Before enabling the application to a domain
                $this->modstructure['domainpreinstall'] = $attrs['FILE'];
                break;

            case 'DOMAINPREUNINSTALL' : // Before disabling the application to a domain
                $this->modstructure['domainpreuninstall'] = $attrs['FILE'];
                break;

            case 'DOMAINPOSTINSTALL' : // After enabling the application to a domain
                $this->modstructure['domainpostinstall'] = $attrs['FILE'];
                break;

            case 'DOMAINPOSTUNINSTALL' : // After disabling the application to a domain
                $this->modstructure['domainpostuninstall'] = $attrs['FILE'];
                break;

                // Update cases
                //
            case 'GENERALPREUPDATE' : // Before updating the application
                $this->modstructure['generalpreupdate'] = $attrs['FILE'];
                break;

            case 'GENERALPOSTUPDATE' : // After updating the application
                $this->modstructure['generalpostupdate'] = $attrs['FILE'];
                break;

            case 'DOMAINPREUPDATE' : // Before updating the application, for every enabled domain
                $this->modstructure['domainpreupdate'] = $attrs['FILE'];
                break;

            case 'DOMAINPOSTUPDATE' : // After updating the application, for every enabled domain
                $this->modstructure['domainpostupdate'] = $attrs['FILE'];
                break;

                // Component case
                //
            default :
                // Checks if it is a known component type
                //
                if (isset($this->eltypes->types[strtolower($tag)])) {
                    reset($this->eltypes->types[strtolower($tag)]);
                    $tmp = array();

                    // Fills the structure attributes for this component
                    //
                    while (list ($key, $val) = each($attrs)) {
                        $tmp[strtolower($key)] = $val;
                    }
                    $this->modstructure[strtolower($tag)][] = $tmp;
                } else {
                    if (
                        file_exists(
                            $this->basedir
                            . '/core/classes/shared/components/'
                            . ucfirst(strtolower($tag))
                            . 'Component.php'
                        )
                    ) {
                        $this->eltypes->types[strtolower($tag)] = array();

                        $tmp = array();

                        // Fills the structure attributes for this component
                        //
                        while (list ($key, $val) = each($attrs)) {
                            $tmp[strtolower($key)] = $val;
                        }
                        $this->modstructure[strtolower($tag)][] = $tmp;
                    }
                }

                break;
        }
    }

    /*!
     @function _tagClose
     @abstract Private member.
     @param tag string - close tag.
     */
    public function doTagClose($tag)
    {
    }

    public function doCdata($data)
    {
    }

    /*!
     @function get_structure
     @abstract Returns the application components structure array.
     @result array.
     */
    public function getStructure()
    {
        $this->parse($this->data);
        return $this->modstructure;
    }
}
