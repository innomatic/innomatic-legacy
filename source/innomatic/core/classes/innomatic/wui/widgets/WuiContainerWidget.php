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
namespace Innomatic\Wui\Widgets;

require_once('innomatic/wui/dispatch/WuiDispatcher.php');

/**
 * Base widget containers class.
 *
 * @package WUI
 */
abstract class WuiContainerWidget extends WuiWidget
{
    /*! @var mChilds array - array of child widgets. */
    public $mChilds = array();

    /*!
     @function WuiContainerWidget
     @abstract Class constructor.
     @discussion Class constructor.
     @param elemName string - Component unique name.
     @param elemArgs array - Array of element arguments and attributes.
     @param elemTheme string - Theme to be applied to the element.
    Currently unuseful.
     @param dispEvents array - Dispatcher events.
     */
    public function __construct(
        $elemName,
        $elemArgs = '',
        $elemTheme = '',
        $dispEvents = ''
    )
    {
        parent::__construct($elemName, $elemArgs, $elemTheme, $dispEvents);
    }

    /*!
     @function AddChild
     @abstract Adds a child to the container structure.
     @discussion Adds a child to the container structure.
     @param rchildWidget WuiWidgetClass - Child widget to be added to the
    structure.
     @result Always true if childWidget is a real object.
     */
    public function addChild(WuiWidget $rchildWidget)
    {
        $this->mChilds[] = $rchildWidget;
        return true;
    }

    /*!
     @function Build
     @abstract Builds the layout.
     @discussion Builds the layout calling the Build method for every child in
    the structure.
     @param rwuiDisp WuiDispatcher class - Wui internal dispatcher handler.
     @result True if the structure has been built by the member.
     */
    public function build(WuiDispatcher $rwuiDisp)
    {
        $result = false;
        $this->mrWuiDisp = $rwuiDisp;
        $this->mLayout .= $this->generateSourceBegin();
        $childrenCount = count($this->mChilds);

        if ($childrenCount) {
            for ($i = 0; $i < $childrenCount; $i ++) {
                if (is_object($this->mChilds[$i])) {
                    if ($this->mChilds[$i]->build($this->mrWuiDisp)) {
                        $this->mLayout.= $this->generateSourceBlockBegin();
                        $this->mLayout.= $this->mChilds[$i]->render();
                        $this->mLayout.= $this->generateSourceBlockEnd();

                        $this->mChilds[$i]->destroy();
                    }
                }
            }
            $this->mBuilt = true;
            $result = true;
        }

        $this->mLayout .= $this->generateSourceEnd();
        return $result;
    }

    public function destroy()
    {
        $this->mLayout = '';
        $this->mArgs = array();
    }

    /*!
     @function generateSourceBegin
     @abstract Wrapped function for layout block before the childs layout.
     @discussion Wrapped function for layout block before the childs layout.
     @result Empty string if not extended.
     */
    protected function generateSourceBegin()
    {
        return '';
    }

    /*!
     @function generateSourceBlockBegin
     @abstract Wrapped function for layout block before every child layout.
     @discussion Wrapped function for layout block before every child layout.
     @result Empty string if not extended.
     */
    protected function generateSourceBlockBegin()
    {
        return '';
    }

    /*!
     @function generateSourceBlockEnd
     @abstract Wrapped function for layout block after every child layout.
     @discussion Wrapped function for layout block after every child layout.
     @result Empty string if not extended.
     */
    protected function generateSourceBlockEnd()
    {
        return '';
    }

    /*!
     @function generateSourceEnd
     @abstract Wrapped function for layout block after the childs layout.
     @discussion Wrapped function for layout block after the childs layout.
     @result Empty string if not extended.
     */
    protected function generateSourceEnd()
    {
        return '';
    }
}
