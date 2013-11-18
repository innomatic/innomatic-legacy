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

require_once('innomatic/dataaccess/DataAccess.php');

/**
 * Wui colors set handler.
 * 
 * A WUI colors set definition file should have _wuicolorsset.ini as suffix.
 * 
 * @package WUI
 */
class WuiColorsSet {
    /*! @var mrRootDb DataAccess class - Innomatic database handler. */
    private $mrRootDb;
    /*! @var mSetName string - Colors set name. */
    private $mSetName;

    /*!
     @function WuiColorsSet
     @abstract Class constructor.
     @discussion Class constructor.
     @param rrootDb DataAccess class - Innomatic database handler.
     @param setName string - Colors set name.
     */
    public function __construct($rrootDb, $setName) {
        if (!(InnomaticContainer::instance('innomaticcontainer')->getState() == InnomaticContainer::STATE_SETUP)) {
            if (is_object($rrootDb)) {
                $this->mrRootDb = $rrootDb;
            }
        }
        $this->mSetName = $setName;
    }

    /*!
     @function Install
     @abstract Installs a new Wui colors set.
     @discussion Installs a new Wui colors set.
     @param args array - Component arguments in the structure.
     @result True if the colors set has been installed.
     */
    public function install($args) {
        $result = false;
        if ($this->mrRootDb) {
            if (strlen($args['name']) and strlen($args['file'])) {
                $result = $this->mrRootDb->execute('INSERT INTO wui_colorssets VALUES ('.$this->mrRootDb->getNextSequenceValue('wui_colorssets_id_seq').','.$this->mrRootDb->formatText($args['name']).','.$this->mrRootDb->formatText($args['file']).','.$this->mrRootDb->formatText($args['catalog']).')');
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
    public function update($args) {
        $result = false;
        if ($this->mrRootDb) {
            if (strlen($this->mSetName)) {
                $check_query = $this->mrRootDb->execute('SELECT name FROM wui_colorssets WHERE name='.$this->mrRootDb->formatText($this->mSetName));

                if ($check_query->getNumberRows()) {
                    if (InnomaticContainer::instance('innomaticcontainer')->getState() != InnomaticContainer::STATE_SETUP) {
                        $cached_item = new CachedItem($this->mrRootDb, 'innomatic', 'wuicolorsset-'.$this->mSetName);
                        $cached_item->Destroy();
                    }
                    $result = $this->mrRootDb->execute('UPDATE wui_colorssets SET file='.$this->mrRootDb->formatText($args['file']).',catalog='.$this->mrRootDb->formatText($args['catalog']).' WHERE name='.$this->mrRootDb->formatText($this->mSetName));
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
    public function remove() {
        $result = false;
        if ($this->mrRootDb) {
            if (strlen($this->mSetName)) {
                
                if (InnomaticContainer::instance('innomaticcontainer')->getState() != InnomaticContainer::STATE_SETUP) {
                    $cached_item = new CachedItem($this->mrRootDb, 'innomatic', 'wuicolorsset-'.$this->mSetName);
                    $cached_item->Destroy();
                }
                $result = $this->mrRootDb->execute('DELETE FROM wui_colorssets WHERE name='.$this->mrRootDb->formatText($this->mSetName));
            }
        }
        return $result;
    }

    public function getColorsSet() {
        $result = array();
        $cfg_file = @parse_ini_file(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/conf/themes/'.$this->mSetName.'_wuicolorsset.ini');
        if ($cfg_file !== FALSE) {
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
            require_once('innomatic/logging/Logger.php');
            $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
            $log->logEvent('innomatic.wuithemes.wuicolorsset.getcolorsset', 'Unable to open colors set file '.InnomaticContainer::instance('innomaticcontainer')->getHome().'core/conf/themes/'.$this->mSetName.'_wuicolorsset.ini', Logger::ERROR);
        }
        return $result;
    }
}
