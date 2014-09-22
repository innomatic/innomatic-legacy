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
 * @license    http://www.innomatic.io/license/ New BSD License
 * @link       http://www.innomatic.io
 * @since      Class available since Release 5.0
 */
namespace Shared\Wui;

/**
 * @package WUI
 */
class WuiLink extends \Innomatic\Wui\Widgets\WuiWidget
{
    //public $mLink;
    //public $mLabel;
    /*! @public mNoWrap string - 'true' if the text may be automatically
    wrapped when necessary. Defaults to 'true'. */
    //public $mNoWrap;
    //public $mCompact;
    //public $mTarget;
    //public $mBold;
    //public $mTitle;
    /*!
     @function WuiLink

     @abstract Class constructor.
     */
    public function __construct (
        $elemName,
        $elemArgs = '',
        $elemTheme = '',
        $dispEvents = ''
    )
    {
        parent::__construct($elemName, $elemArgs, $elemTheme, $dispEvents);
        if (! isset($this->mArgs['label'])) {
            $this->mArgs['label'] = isset($this->mArgs['link']) ?
                $this->mArgs['label'] = $this->mArgs['link'] : '';
        }
        if (isset($this->mArgs['nowrap']) and ($this->mArgs['nowrap'] == 'true'
            or $this->mArgs['nowrap'] == 'false')) {
            $this->mArgs['nowrap'] = $this->mArgs['nowrap'];
        } else {
            $this->mArgs['nowrap'] = 'true';
        }
        if (isset($this->mArgs['compact'])) {
            $this->mArgs['compact'] = $this->mArgs['compact'] == 'true' ?
                'true' : 'false';
        } else {
            $this->mArgs['compact'] = 'false';
        }
        if (isset($this->mArgs['bold'])) {
            $this->mArgs['bold'] = $this->mArgs['bold'] == 'true' ? 'true'
                : 'false';
        } else {
            $this->mArgs['bold'] = 'false';
        }
    }
    protected function generateSource()
    {
        $this->mLayout = ($this->mComments ? '<!-- begin ' . $this->mName
            . ' link -->' : '') . '<table border="0" '
            . ($this->mArgs['nowrap'] == 'true' ? 'width="0%"' : '')
            . ' height="0%"' . ($this->mArgs['compact'] == 'true'
            ? ' cellpadding="1" cellspacing="0"' : '') . '>'
            . '<tr><td class="normal" ' . ($this->mArgs['nowrap'] == 'true'
            ? 'nowrap style="white-space: nowrap"' : '') . '>'
            . ((isset($this->mArgs['link']) and strlen($this->mArgs['link']))
            ? '<a'.(isset($this->mArgs['id']) ? ' id="'.$this->mArgs['id'].'"' : ''). $this->getEventsCompleteString() .' href="' . $this->mArgs['link'] . '"'
            . ((isset($this->mArgs['target'])
            and strlen($this->mArgs['target'])) ? ' target="'
            . $this->mArgs['target'] . '"' : '')
            . ((isset($this->mArgs['title']) and
            strlen($this->mArgs['title'])) ? ' title="' . $this->mArgs['title']
            . '"' : '') . '>' . ($this->mArgs['bold'] == 'true' ? '<strong>'
            : '') . $this->mArgs['label'] . ($this->mArgs['bold'] == 'true' ?
            '</strong>' : '') . '</a>' : $this->mArgs['label'])
            . '</td></tr></table>' . ($this->mComments ? '<!-- end '
            . $this->mName . " link -->\n" : '');
        return true;
    }
}
