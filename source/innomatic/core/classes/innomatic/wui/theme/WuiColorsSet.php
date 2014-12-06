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
 * @since      Class available since Release 5.0
*/
namespace Innomatic\Wui\Theme;

/**
 * Wui colors set handler.
 *
 * A WUI colors set definition file should have _wuicolorsset.ini as suffix.
 *
 * @package WUI
 */
class WuiColorsSet
{
    /*! @var rootdataaccess DataAccess class - Innomatic database handler. */
    private $rootdataaccess;
    /*! @var setname string - Colors set name. */
    private $setname;

    /*!
     @function WuiColorsSet
     @abstract Class constructor.
     @discussion Class constructor.
     @param rrootDb DataAccess class - Innomatic database handler.
     @param setName string - Colors set name.
     */
    public function __construct($rrootDb, $setName)
    {
        if (!(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getState() == \Innomatic\Core\InnomaticContainer::STATE_SETUP)) {
            if (is_object($rrootDb)) {
                $this->rootdataaccess = $rrootDb;
            }
        }
        $this->setname = $setName;
    }

    /*!
     @function Install
     @abstract Installs a new Wui colors set.
     @discussion Installs a new Wui colors set.
     @param args array - Component arguments in the structure.
     @result True if the colors set has been installed.
     */
    public function install($args)
    {
        $result = false;
        if ($this->rootdataaccess) {
            if (strlen($args['name']) and strlen($args['file'])) {
                $result = $this->rootdataaccess->execute('INSERT INTO wui_colorssets VALUES ('.$this->rootdataaccess->getNextSequenceValue('wui_colorssets_id_seq').','.$this->rootdataaccess->formatText($args['name']).','.$this->rootdataaccess->formatText($args['file']).','.$this->rootdataaccess->formatText($args['catalog']).')');
            }
        }
        return $result;
    }

    /*!
     @function Update
     @abstract Updates a Wui colors set.
     @discussion Updates a Wui colors set.
     @param args array - Component arguments in the structure.
     @result True if the colors set has been updated.
     */
    public function update($args)
    {
        $result = false;
        if ($this->rootdataaccess) {
            if (strlen($this->setname)) {
                $check_query = $this->rootdataaccess->execute('SELECT name FROM wui_colorssets WHERE name='.$this->rootdataaccess->formatText($this->setname));

                if ($check_query->getNumberRows()) {
                    if (\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getState() != \Innomatic\Core\InnomaticContainer::STATE_SETUP) {
                        $cached_item = new \Innomatic\Datatransfer\Cache\CachedItem($this->rootdataaccess, 'innomatic', 'wuicolorsset-'.$this->setname);
                        $cached_item->Destroy();
                    }
                    $result = $this->rootdataaccess->execute('UPDATE wui_colorssets SET file='.$this->rootdataaccess->formatText($args['file']).',catalog='.$this->rootdataaccess->formatText($args['catalog']).' WHERE name='.$this->rootdataaccess->formatText($this->setname));
                } else
                    $result = $this->Install($args);
            }
        }
        return $result;
    }

    /*!
     @function Remove
     @abstract Removes a Wui colors set.
     @discussion Removes a Wui colors set.
     @result True if the colors set has been removed.
     */
    public function remove()
    {
        $result = false;
        if ($this->rootdataaccess) {
            if (strlen($this->setname)) {

                if (\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getState() != \Innomatic\Core\InnomaticContainer::STATE_SETUP) {
                    $cached_item = new \Innomatic\Datatransfer\Cache\CachedItem($this->rootdataaccess, 'innomatic', 'wuicolorsset-'.$this->setname);
                    $cached_item->Destroy();
                }
                $result = $this->rootdataaccess->execute('DELETE FROM wui_colorssets WHERE name='.$this->rootdataaccess->formatText($this->setname));
            }
        }
        return $result;
    }

    public function getColorsSet()
    {
        $result = array();
        $cfg_file = @parse_ini_file(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome().'core/conf/themes/'.$this->setname.'_wuicolorsset.ini');
        if ($cfg_file !== false) {
            $result['pages']['bgcolor'] = $cfg_file['COLORSET.PAGES.BGCOLOR'];
            $result['pages']['border'] = $cfg_file['COLORSET.PAGES.BORDER'];
            $result['buttons']['text'] = $cfg_file['COLORSET.BUTTONS.TEXT'];
            $result['buttons']['disabledtext'] = $cfg_file['COLORSET.BUTTONS.DISABLEDTEXT'];
            $result['buttons']['selected'] = $cfg_file['COLORSET.BUTTONS.SELECTED'];
            $result['buttons']['notselected'] = $cfg_file['COLORSET.BUTTONS.NOTSELECTED'];
            $result['bars']['color'] = $cfg_file['COLORSET.BARS.COLOR'];
            $result['bars']['shadow'] = $cfg_file['COLORSET.BARS.SHADOW'];
            $result['alert']['border'] = $cfg_file['COLORSET.ALERT.BORDER'];
            $result['alert']['bgcolor'] = $cfg_file['COLORSET.ALERT.BGCOLOR'];
            $result['alert']['text'] = $cfg_file['COLORSET.ALERT.TEXT'];
            $result['frames']['border'] = $cfg_file['COLORSET.FRAMES.BORDER'];
            $result['statusbars']['bgcolor'] = $cfg_file['COLORSET.STATUSBARS.BGCOLOR'];
            $result['titlebars']['bgcolor'] = $cfg_file['COLORSET.TITLEBARS.BGCOLOR'];
            $result['titlebars']['textcolor'] = $cfg_file['COLORSET.TITLEBARS.TEXTCOLOR'];
            $result['toolbars']['separator'] = $cfg_file['COLORSET.TOOLBARS.SEPARATOR'];
            $result['tables']['bgcolor'] = $cfg_file['COLORSET.TABLES.BGCOLOR'];
            $result['tables']['headerbgcolor'] = $cfg_file['COLORSET.TABLES.HEADERBGCOLOR'];
            $result['tables']['gridcolor'] = $cfg_file['COLORSET.TABLES.GRIDCOLOR'];
        } else {
            
            $log = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getLogger();
            $log->logEvent('innomatic.wuithemes.wuicolorsset.getcolorsset', 'Unable to open colors set file '.\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getHome().'core/conf/themes/'.$this->setname.'_wuicolorsset.ini', \Innomatic\Logging\Logger::ERROR);
        }
        return $result;
    }
}
