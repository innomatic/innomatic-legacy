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
 * WUI icons set handler.
 *
 * A WUI icons set definition file should have _wuiiconsset.ini as suffix.
 *
 * @package WUI
 */
class WuiIconsSet
{
    /*! @var mrRootDb DataAccess class - Innomatic database handler. */
    private $mrRootDb;
    /*! @var mSetName string - Icons set name. */
    private $mSetName;

    /*!
     @function WuiIconsSet
     @abstract Class constructor.
     @discussion Class constructor.
     @param rrootDb DataAccess class - Innomatic database handler.
     @param setName string - Icons set name.
     */
    public function __construct($rrootDb, $setName)
    {
        if (!(InnomaticContainer::instance('innomaticcontainer')->getState() == InnomaticContainer::STATE_SETUP)) {
            if (is_object($rrootDb)) {
                $this->mrRootDb = $rrootDb;
            }
        }
        $this->mSetName = $setName;
    }

    /*!
     @function Install
     @abstract Installs a new Wui icons set.
     @discussion Installs a new Wui icons set.
     @param args array - Component arguments in the structure.
     @result True if the icons set has been installed.
     */
    public function install($args)
    {
        $result = FALSE;
        if ($this->mrRootDb) {
            if (strlen($args['name']) and strlen($args['file'])) {
                $result = $this->mrRootDb->execute('INSERT INTO wui_iconssets VALUES ('.$this->mrRootDb->getNextSequenceValue('wui_iconssets_id_seq').','.$this->mrRootDb->formatText($args['name']).','.$this->mrRootDb->formatText($args['file']).','.$this->mrRootDb->formatText($args['catalog']).')');
            }
        }
        return $result;
    }

    /*!
     @function Update
     @abstract Updates a Wui icons set.
     @discussion Updates a Wui icons set.
     @param args array - Component arguments in the structure.
     @result True if the icons set has been updated.
     */
    public function update($args)
    {
        $result = FALSE;
        if ($this->mrRootDb) {
            if (strlen($this->mSetName)) {
                $check_query = $this->mrRootDb->execute('SELECT name FROM wui_iconssets WHERE name='.$this->mrRootDb->formatText($this->mSetName));

                if ($check_query->getNumberRows()) {
                    if (InnomaticContainer::instance('innomaticcontainer')->getState() != InnomaticContainer::STATE_SETUP) {
                        $cached_item = new CachedItem($this->mrRootDb, 'innomatic', 'wuiiconsset-'.$this->mSetName);
                        $cached_item->Destroy();
                    }
                    $result = $this->mrRootDb->execute('UPDATE wui_iconssets SET file='.$this->mrRootDb->formatText($args['file']).',catalog='.$this->mrRootDb->formatText($args['catalog']).' WHERE name='.$this->mrRootDb->formatText($this->mSetName));
                } else
                    $result = $this->Install($args);
            }
        }
        return $result;
    }

    /*!
     @function Remove
     @abstract Removes a Wui icons set.
     @discussion Removes a Wui icons set.
     @result True if the icons set has been removed.
     */
    public function remove()
    {
        $result = FALSE;
        if ($this->mrRootDb) {
            if (strlen($this->mSetName)) {

                if (InnomaticContainer::instance('innomaticcontainer')->getState() != InnomaticContainer::STATE_SETUP) {
                    $cached_item = new CachedItem($this->mrRootDb, 'innomatic', 'wuiiconsset-'.$this->mSetName);
                    $cached_item->Destroy();
                }
                $result = $this->mrRootDb->execute('DELETE FROM wui_iconssets WHERE name='.$this->mrRootDb->formatText($this->mSetName));
            }
        }
        return $result;
    }

    public function getIconsSet()
    {
        $result = array();
        $values = @parse_ini_file(InnomaticContainer::instance('innomaticcontainer')->getHome().'core/conf/themes/'.$this->mSetName.'_wuiiconsset.ini');
        if ($values !== FALSE) {
            while (list ($key, $val) = each($values)) {
                $key = trim($key);
                $val = trim($val);

                if (substr_count($key, '.') == 2) {
                    $tmpkey = strtolower(substr($key, strpos($key, '.') + 1));
                    $type = substr($tmpkey, 0, strpos($tmpkey, '.'));
                    $realkey = substr($tmpkey, strpos($tmpkey, '.') + 1);

                    $result[$type][$realkey]['file'] = $val;
                    $result[$type][$realkey]['base'] = $this->mSetName;
                }
            }
        } else {
            require_once('innomatic/logging/Logger.php');
            $log = InnomaticContainer::instance('innomaticcontainer')->getLogger();
            $log->logEvent('innomatic.wuithemes.wuistyle.getstyle', 'Unable to open icons set file '.InnomaticContainer::instance('innomaticcontainer')->getHome().'core/conf/themes/'.$this->mSetName.'_wuiiconsset.ini', \Innomatic\Logging\Logger::ERROR);
        }

        return $result;
    }
}
