<?php
/**
 * Innomatic
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2013 Innoteam Srl
 * @license    http://www.innomatic.org/license/   BSD License
 * @link       http://www.innomatic.org
 * @since      Class available since Release 5.0
*/
namespace Innomatic\Core;

// This require uses the absolute path because at this time the PHP include path
// is still the default one and doesn't include the Innomatic container classes
// directory.
require_once(dirname(__FILE__).'/../util/Singleton.php');

/**
 * The Root Container holds the base path of Innomatic and updates the PHP
 * include path adding the Innomatic container classes directory.
 *
 * It also tells if the application started by the Innomatic container has
 * been exited in a clean way or if it crashed, letting the Innomatic container
 * call the RootContainer::stop() method.
 *
 * @copyright  2008-2012 Innoteam Srl
 * @license    http://www.innomatic.org/license/   BSD License
 * @version    Release: @package_version@
 * @link       http://www.innomatic.org
 * @since      Class available since Release 5.0
 * @package    Core
 */
class RootContainer extends \Innomatic\Util\Singleton
{
    /**
     * Holds the root container base directory, where all the container
     * applications and the main index.php receiver file are stored.
     *
     * @var string
     */
    private $home;
    /**
     * The clean state is false until explicitly changed to true calling the
     * RootContainer::stop() method.
     *
     * @var boolean
     */
    private $clean = false;

    /**
     * RootContainer constructor.
     *
     */
    public function ___construct()
    {
        $this->home = realpath(dirname(__FILE__).'/../../../../..').'/';
        @chdir($this->home);

        // This is needed in order to prevent a successive chdir() to screw
        // including classes when relying on having Innomatic root directory
        // as current directory
        set_include_path(
            get_include_path() . PATH_SEPARATOR . $this->home
            . 'innomatic/core/classes/'
        );

        spl_autoload_register('\Innomatic\Core\RootContainer::autoload', true, true);
    }

    /**
     * Returns the root container home directory.
     *
     * @return string
     */
    public function getHome()
    {
        return $this->home;
    }

    /**
     * Stops the root container, setting the clean flag to true.
     * This is useful in conjunction with a PHP shutdown function registered
     * with register_shutdown_function, e.g. to catch fatal errors.
     *
     * This happens by default in the InnomaticContainer class.
     */
    public function stop()
    {
        $this->clean = true;
    }

    /**
     * Tells if the root container is in a clean state. This can only be true
     * after a call to the RootContainer::stop() method.
     *
     * @return bool
     */
    public function isClean()
    {
        return $this->clean;
    }
    
    /**
     * SPL autoload method.
     * 
     * @since 6.1
     * @param string $class_name
     */
    public static function autoload($class_name)
	{
	    if (strpos($class_name, '\\') !== false) {
	        $orig = $class_name;
	        $class_pop = explode('\\',$class_name);
	        $class_name = array_pop($class_pop);
	        $file = self::getClassFile($orig);
	    } else {
	    	$file = self::getClassFile($class_name);
	    }
	    // use some function to find the file that declares the class requested
	    
	    // remember the defined classes, include the $file and detect newly declared classes
	    $pre = array_merge(get_declared_classes(), get_declared_interfaces());
	    //require_once($file);
	    include_once($file);
	    $post = array_unique(array_diff(array_merge(get_declared_classes(), get_declared_interfaces()), $pre));
	    
	    // loop through the new class definitions and create weak aliases if they are given with qualified names
	    foreach ($post as $cd) {
	        $d = explode('\\',$cd);
	        if (count($d) > 1) {
	            // Aliasing full qualified classnames to their simple ones. Note: weak alias!
	            self::createClassAlias($cd,array_pop($d));
	        }
	    }
	    
	    // get the class definition. note: we assume that there's only one class/interface in each file!
	    $def = array_pop($post);
	    if (!isset($orig) && !$def)
	    // plain class requested AND file was already included, so search up the declared classes and alias
	    {
	        foreach( array_reverse($pre) as $c ) {
	            if (!(strtolower(substr($c,strlen($c)-strlen($class_name))) == strtolower($class_name))) {
	                continue;
	            }
	            // Aliasing previously included class
	            self::createClassAlias($c,$class_name,true);
	            break;
	        }
	    } elseif (isset($orig) && !$def) {
	    	self::createClassAlias($class_name,$orig,true);
	    } else {
	        $class_name = isset($orig)?$orig:$class_name;
	        if (strtolower($def) != strtolower($class_name) && strtolower(substr($def,strlen($def)-strlen($class_name))) == strtolower($class_name))
	        // no qualified classname requested but class was defined with namespace
	        {
	            // Aliasing class
	            self::createClassAlias($def,$class_name,true);
	        }
	    }
	}

	public static function getClassFile($className)
	{
		// Backwards compatibility system
		if (!isset($GLOBALS['system_classes'])) {
			if (file_exists('innomatic/core/applications/innomatic/application.xml')) {
			$xml = file_get_contents('innomatic/core/applications/innomatic/application.xml');
			$file = new \SimpleXMLElement($xml);
			$classes = array();
			
			foreach($file->components->class as $class) {
				$path = "{$class['name']}";
				$elements = explode('/', $path);
				$class = str_replace('.php', '', array_pop($elements));
				array_walk(
					$elements,
					function (&$match, $key) {
						$match = ucfirst($match);
					}
				);
				
				$fqcn = (count($elements) ? '\\'.implode('\\', $elements) : '').'\\'.$class;
				$GLOBALS['system_classes'][strtolower($class)] = array('path' => $path, 'fqcn' => $fqcn);
			}
			
			foreach($file->components->wuiwidget as $class) {
				$path = "shared/wui/{$class['file']}";
				$elements = explode('/', $path);
				$class = str_replace('.php', '', array_pop($elements));
				array_walk(
				$elements,
				function (&$match, $key) {
					$match = ucfirst($match);
				}
				);
			
				$fqcn = (count($elements) ? '\\'.implode('\\', $elements) : '').'\\'.$class;
				$GLOBALS['system_classes'][strtolower($class)] = array('path' => $path, 'fqcn' => $fqcn);
			}
			}
		}
		
		$className = ltrim($className, '\\');
		$fileName  = '';
		$namespace = '';
		if ($lastNsPos = strrpos($className, '\\')) {
			$namespace = substr($className, 0, $lastNsPos);
			$className = substr($className, $lastNsPos + 1);
			$fileName  = strtolower(str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR);
		}
		
		if (isset($GLOBALS['system_classes'][strtolower($className)]))
		{
			$fileName = $GLOBALS['system_classes'][strtolower($className)]['path'];
		}
		else
		{
			$fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
		}
		
		return $fileName;
	}
	
	public static function createClassAlias($original,$alias,$strong=false)
	{
		// if strong create a real alias known to PHP
		if ($strong)
		{
			if (!interface_exists($original)) {
				class_alias($original,$alias);
			}
		}
	
		// In any case store the alias in a global variable
		$alias = strtolower($alias);
		if( isset($GLOBALS['system_class_alias'][$alias]) )
		{
			if( $GLOBALS['system_class_alias'][$alias] == $original )
				return;
	
			if( !is_array($GLOBALS['system_class_alias'][$alias]) )
				$GLOBALS['system_class_alias'][$alias] = array($GLOBALS['system_class_alias'][$alias]);
			$GLOBALS['system_class_alias'][$alias][] = $original;
		}
		else
			$GLOBALS['system_class_alias'][$alias] = $original;
	}
}
