<?php

namespace Innomatic\Desktop\Panel;

class PanelTemplate implements \Innomatic\Tpl\Template
{
    protected $tplEngine;
    protected $vars;
    
    /**
     * Constructor.
     *
     * @since 1.1
     * @param string $file full path of the template.
     */
    public function __construct($file)
    {
        $this->file = $file;
        $this->tplEngine = new \Innomatic\Php\PHPTemplate($file);
    }

    /**
     * Sets a variable and its value.
     *
     * The variable will be extracted to the local scope of the template with
     * the same name given as parameter to the method. So setting a variable
     * named "title" will result in a $title variable available to the template.
     *
     * The value can be a string, a number or a PhpTemplate instance. In the
     * latter case, the variable content will be the result of the whole parsed
     * template of the PhpTemplate instance.
     *
     * The proper method for setting arrays is setArray().
     *
     * @since 1.1
     * @param string $name Name of the variable.
     * @param string|number|PhpTemplate $value variable content.
     * @see setArray()
     */
    public function set($name, $value)
    {
        $this->vars[$name] = $value instanceof \Innomatic\Tpl\Template ?
            $value->parse()
            : \Shared\Wui\WuiXml::cdata($value);

        $this->tplEngine->set(
            $name,
            $this->vars[$name]
        );
    }

    /**
     * Returns the current value of a variable.
     *
     * @since 1.1
     * @param string $name Name of the variable.
     * @return string Variable value.
     * @see getArray()
     */
    public function get($name)
    {
        if (isset($this->vars[$name])) {
            return $this->vars[$name];
        }
        return false;
    }

    /**
     * Sets an array by reference as variable.
     *
     * This method is similar to the set() one, with the difference that it
     * takes arrays by reference and that it doesn't support passing a
     * PhpTemplate as value.
     *
     * @since 1.1
     * @param string $name Array name.
     * @param array $value Array.
     * @see get()
     */
    public function setArray($name, &$value)
    {
        $this->vars[$name] = &$value;
        $this->tplEngine->setArray($name, \Shared\Wui\WuiXml::encode($value));
    }

    /**
     * Returns the current value of a variable stored as array.
     *
     * @since 1.1
     * @param string $name Name of the array.
     * @return string Array.
     * @see get()
     */
    public function &getArray($name)
    {
        if (isset ($this->vars[$name])) {
            return $this->vars[$name];
        }
        return false;
    }

    /**
     * Parses the template.
     *
     * This method parses the template and returns it parsed.
     *
     * @since 1.1
     * @return string
     */
    public function parse()
    {
        return $this->tplEngine->parse();
    }

    /**
     * Returns a list of the set tag names.
     *
     * @since 6.1
     * @return array
     */
    public function getTags()
    {
        return array_keys($this->vars);
    }
}