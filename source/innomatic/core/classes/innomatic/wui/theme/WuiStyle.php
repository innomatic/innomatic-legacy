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
 * WUI style handler.
 *
 * A WUI style definition file should have .wuistyle as suffix.
 *
 * @package WUI
 */
class WuiStyle
{
    /*! @var mrRootDb DataAccess class - Innomatic database handler. */
    private $mrRootDb;
    /*! @var mStyleName string - Icons set name. */
    private $mStyleName;

    /*!
     @function WuiStyle
     @abstract Class constructor.
     @discussion Class constructor.
     @param rrootDb DataAccess class - Innomatic database handler.
     @param styleName string - Icons set name.
     */
    public function __construct($rrootDb, $styleName)
    {
        if (!(InnomaticContainer::instance('innomaticcontainer')->getState() == InnomaticContainer::STATE_SETUP)) {
            $this->mrRootDb = $rrootDb;
        }
        $this->mStyleName = $styleName;
    }

    /*!
     @function Install
     @abstract Installs a new Wui style.
     @discussion Installs a new Wui style.
     @param args array - Component arguments in the structure.
     @result True if the style has been installed.
     */
    public function install($args)
    {
        $result = FALSE;
        if ($this->mrRootDb) {
            if (strlen($args['name']) and strlen($args['file'])) {
                $result = $this->mrRootDb->execute('INSERT INTO wui_styles VALUES ('.$this->mrRootDb->getNextSequenceValue('wui_styles_id_seq').','.$this->mrRootDb->formatText($args['name']).','.$this->mrRootDb->formatText($args['file']).','.$this->mrRootDb->formatText($args['catalog']).')');
            }
        }
        return $result;
    }

    /*!
     @function Update
     @abstract Updates a Wui style.
     @discussion Updates a Wui style.
     @param args array - Component arguments in the structure.
     @result True if the style has been updated.
     */
    public function update($args)
    {
        $result = FALSE;

        if ($this->mrRootDb) {
            if (strlen($this->mStyleName)) {
                $check_query = $this->mrRootDb->execute('SELECT name FROM wui_styles WHERE name='.$this->mrRootDb->formatText($this->mStyleName));

                if ($check_query->getNumberRows()) {
                    if (InnomaticContainer::instance('innomaticcontainer')->getState() != InnomaticContainer::STATE_SETUP) {
                        $cached_item = new CachedItem($this->mrRootDb, 'innomatic', 'wuistyle-'.$this->mStyleName);

                        $cached_item->Destroy();
                    }
                    $result = $this->mrRootDb->execute('UPDATE wui_styles SET file='.$this->mrRootDb->formatText($args['file']).',catalog='.$this->mrRootDb->formatText($args['catalog']).' WHERE name='.$this->mrRootDb->formatText($this->mStyleName));
                } else
                    $result = $this->Install($args);
            }
        }
        return $result;
    }

    /*!
     @function Remove
     @abstract Removes a Wui style.
     @discussion Removes a Wui style.
     @result True if the style has been removed.
     */
    public function remove()
    {
        $result = FALSE;

        if ($this->mrRootDb) {
            if (strlen($this->mStyleName)) {

                if (InnomaticContainer::instance('innomaticcontainer')->getState() != InnomaticContainer::STATE_SETUP) {
                    $cached_item = new CachedItem($this->mrRootDb, 'innomatic', 'wuistyle-'.$this->mStyleName);
                    $cached_item->Destroy();
                }
                $result = $this->mrRootDb->execute('DELETE FROM wui_styles WHERE name='.$this->mrRootDb->formatText($this->mStyleName));
            }
        }
        return $result;
    }

    public function getStyle()
    {
        $result = array();
        $values = @parse_ini_file(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/conf/themes/'.$this->mStyleName.'_wuistyle.ini');
        if ($values !== FALSE) {
            while (list ($key, $val) = each($values)) {
                $key = trim($key);
                $val = trim($val);

                $realkey = strtolower(substr($key, strpos($key, '.') + 1));
                if ($realkey != 'name') {
                    $result[$realkey]['value'] = $val;
                    $result[$realkey]['base'] = $this->mStyleName;
                }
            }
        } else {
            require_once('innomatic/logging/Logger.php');
            $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
            $log->logEvent('innomatic.wuithemes.wuistyle.getstyle', 'Unable to open style file '.InnomaticContainer::instance('innomaticcontainer')->getHome().'core/conf/themes/'.$this->mStyleName.'_wuistyle.ini', \Innomatic\Logging\Logger::ERROR);
        }
        return $result;
    }
}
