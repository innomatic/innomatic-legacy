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

/*!
 @class CachedItem

 @abstract Handles Innomatic items cache.

 @discussion Handles Innomatic items cache.
 */
class CachedItem
{
    /*! @var mrRootDb DataAccess class - Innomatic database handler. */
    protected $mrRootDb;
    /*! @var mApplication string - Application id name. */
    public $mApplication;
    /*! @var mItemId string - Item id. */
    public $mItemId;
    /*! @var mItemFile string - Cached item file name. */
    public $mItemFile;
    /*! @var mValidator string - Optional item validator. */
    public $mValidator;
    /*! @var mResult integer - Last action result; may be one of the CachedItem::ITEM_x defines. */
    public $mResult = 0;
    public $mDomainId = 0;
    public $mUserId = 0;
    private $cachePath;
    const ITEM_FOUND = -1;
    const ITEM_NOT_FOUND = -2;
    const ITEM_STORED = -5;
    const ITEM_NOT_STORED = -6;
    const ITEM_NOT_EQUAL = -3;
    const ITEM_EQUAL = -4;

    /*!
     @function CachedItem
     @abstract Class constructor.
     @discussion Class constructor.
     @param rrootDb DataAccess class - Innomatic database handler.
     @param application string - Application id name.
     @param itemId string - Item id.
     */
    public function CachedItem(DataAccess $rrootDb, $application, $itemId, $domainId = 0, $userId = 0)
    {
        $this->cachePath = InnomaticContainer::instance('innomaticcontainer')->getHome().'core/temp/cache/';
        $domainId = (int) $domainId;
        $userId = (int) $userId;
        $this->mrRootDb = $rrootDb;
        if (!$rrootDb->isConnected()) {
            $this->mResult = CachedItem::ITEM_NOT_FOUND;
        } else {
            if (strlen($itemId) and strlen($application)) {
                $this->mItemId = $itemId;
                $this->mApplication = $application;
                $this->mDomainId = $domainId;
                $this->mUserId = $userId;

                $item_query = $this->mrRootDb->execute('SELECT itemfile,validator,domainid,userid FROM cache_items WHERE application='.$this->mrRootDb->formatText($this->mApplication).' AND itemid='.$this->mrRootDb->formatText($this->mItemId). ($domainId ? ' AND domainid='.$domainId : ''). ($userId ? ' AND userid='.$userId : ''));

                if ($item_query->getNumberRows()) {
                    $this->mValidator = $item_query->getFields('validator');
                    $this->mItemFile = $this->cachePath.$item_query->getFields('itemfile');
                    $this->mDomainId = $item_query->getFields('domainid');
                    $this->mUserId = $item_query->getFields('userid');
                    $this->mResult = CachedItem::ITEM_FOUND;
                } else
                $this->mResult = CachedItem::ITEM_NOT_FOUND;
            }
        }
    }

    /*!
     @function Store
     @abstract Stores the item in the cache.
     @discussion Stores the item in the cache.
     @param $content string - Item content.
     @param $validator string - Optional validator.
     @result true if the item has been stored.
     */
    public function store($content, $validator = '')
    {
        $result = false;
        $this->mResult = CachedItem::ITEM_NOT_STORED;
        if (!$this->mrRootDb->isConnected()) {
            $this->mResult = CachedItem::ITEM_NOT_FOUND;
            return false;
        }

        $goon = false;
        require_once('innomatic/process/Semaphore.php');
        $sem = new Semaphore('cache', $this->mItemFile);
        $sem->WaitGreen();
        $sem->setRed();

        if (strlen($this->mItemFile) and file_exists($this->mItemFile)) {
            if ($fh = @fopen($this->mItemFile, 'w')) {
                if (@fwrite($fh, $content)) {
                    $name = $this->mItemFile;
                    $goon = true;
                }

                fclose($fh);
            }
        } else {
            $name = $this->cachePath.date('Ymd').'_cacheditem_'.rand();

            if (!file_exists($this->cachePath)) {
                require_once('innomatic/io/filesystem/DirectoryUtils.php');
                DirectoryUtils::mktree($this->cachePath, 0755);
            }
            if ($fh = @fopen($name, 'w')) {
                if (@fwrite($fh, $content)) {
                    $goon = true;
                }
                @fclose($fh);
            }
        }

        if ($goon) {
            $item_query = $this->mrRootDb->execute('SELECT itemid FROM cache_items WHERE itemid='.$this->mrRootDb->formatText($this->mItemId).' AND application='.$this->mrRootDb->formatText($this->mApplication). ($this->mDomainId ? ' AND domainid='.$this->mDomainId : ''). ($this->mUserId ? ' AND userid='.$this->mUserId : ''));
            if ($item_query->getNumberRows()) {
                if ($this->mrRootDb->execute('UPDATE cache_items SET validator='.$this->mrRootDb->formatText($validator).',itemfile='.$this->mrRootDb->formatText(basename($name)).',domainid='.$this->mDomainId.',userid='.$this->mUserId.' WHERE itemid='.$this->mrRootDb->formatText($this->mItemId).' AND application='.$this->mrRootDb->formatText($this->mApplication))) {
                    $result = true;
                }
            } else {
                if ($this->mrRootDb->execute('INSERT INTO cache_items VALUES ('.$this->mrRootDb->formatText($this->mApplication).','.$this->mrRootDb->formatText($this->mItemId).','.$this->mrRootDb->formatText(basename($name)).','.$this->mrRootDb->formatText($validator).','.$this->mDomainId.','.$this->mUserId.')')) {
                    $result = true;
                }
            }

            if ($result) {
                $this->mItemFile = $name;
                $this->mValidator = $validator;
                $this->mResult = CachedItem::ITEM_STORED;
            }
        }

        // The semaphore gets unlocked anyway, even if the operation has failed.
        $sem->setGreen();
        return $result;
    }

    /*!
     @function Retrieve
     @abstract Retrieves the item from the cache.
     @discussion Retrieves the item from the cache.
     @param md5 string - Optional md5 hash to be checked with the cached item one.
     @result The item content.
     */
    public function retrieve($md5 = '')
    {
        $result = false;
        require_once('innomatic/process/Semaphore.php');
        $sem = new Semaphore('cache', $this->mItemFile);
        $sem->WaitGreen();

        if (strlen($this->mItemFile) and file_exists($this->mItemFile)) {
            $sem->setRed();
            $goon = true;
            if (strlen($md5)) {
                if ($this->getItemMd5() == $md5)
                $goon = true;
                else
                $goon = false;
            }

            if ($goon) {
                if (file_exists($this->mItemFile)) {
                    $result = file_get_contents($this->mItemFile);
                }
            } else
            $this->mResult = CachedItem::ITEM_NOT_EQUAL;
            $sem->setGreen();
        } else
        $this->mResult = CachedItem::ITEM_NOT_FOUND;

        return $result;
    }

    /*!
     @function CheckValidator
     @abstract Checks if the optional validator is equal to a given one.
     @discussion Checks if the optional validator is equal to a given one.
     @param validator string - Validator to be checked.
     @result true if the validators are equals.
     */
    public function checkValidator($validator)
    {
        $result = false;
        if (strlen($this->mItemFile) and file_exists($this->mItemFile)) {
            if ($validator == $this->mValidator)
            $result = true;
        }
        return $result;
    }

    /*!
     @function Destroy
     @abstract Destroys the item from the cache.
     @discussion Destroys the item from the cache.
     @result true if the item has been destroyed.
     */
    public function destroy()
    {
        $result = false;
        require_once('innomatic/process/Semaphore.php');
        $sem = new Semaphore('cache', $this->mItemFile);
        $sem->WaitGreen();
        $sem->setRed();
        if (strlen($this->mItemFile) and file_exists($this->mItemFile)) {
            $result = @unlink($this->mItemFile);
        } else
        $this->mResult = CachedItem::ITEM_NOT_FOUND;
        if ($result)
        $result = $this->mrRootDb->execute('DELETE FROM cache_items WHERE application='.$this->mrRootDb->formatText($this->mApplication).' AND itemid='.$this->mrRootDb->formatText($this->mItemId));
        $sem->setGreen();
        return $result;
    }

    /*!
     @function CompareMd5
     @abstract Checks if the md5 of the cached item is equal to the md5 of a given item.
     @discussion Checks if the md5 of the cached item is equal to the md5 of a given item.
     @param itemContent string - Content of the item to be checked.
     @result true if the md5 of the items are equals.
     */
    public function compareMd5($itemContent)
    {
        $result = false;
        if (strlen($this->mItemFile) and file_exists($this->mItemFile)) {
            if (md5($itemContent) == $this->getItemMd5()) {
                $this->mResult = CachedItem::ITEM_EQUAL;
                $result = true;
            } else
            $this->mResult = CachedItem::ITEM_NOT_EQUAL;
        } else
        $this->mResult = CachedItem::ITEM_NOT_FOUND;
        return $result;
    }

    /*!
     @function getItemMd5
     @abstract Gets the md5 hash of the file, in order to compare it with the original item.
     @discussion Gets the md5 hash of the file, in order to compare it with the original item.
     @result The md5 hash.
     */
    public function getItemMd5()
    {
        $result = false;
        if (strlen($this->mItemFile) and file_exists($this->mItemFile)) {
            if (function_exists('md5_file'))
            $result = md5_file($this->mItemFile);
            else {
                $result = md5(file_get_contents($this->mItemFile));
            }
            $this->mResult = CachedItem::ITEM_FOUND;
        } else
        $this->mResult = CachedItem::ITEM_NOT_FOUND;
        return $result;
    }

    public function getCachePath()
    {
        return $this->cachePath;
    }
}
