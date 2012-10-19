<?php
/**
 * Innomatic
 *
 * LICENSE 
 * 
 * This source file is subject to the new BSD license that is bundled 
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2012 Innoteam S.r.l.
 * @license    http://www.innomatic.org/license/   BSD License
 * @link       http://www.innomatic.org
 * @since      Class available since Release 5.0
 */
require_once ('innomatic/application/ApplicationComponent.php');

/**
 * Webappskeleton component handler.
 *
 * A webapp skeleton is a collection of directories and files providing at least
 * a minimal working webapp tree with a web.xml file.
 *  
 * @copyright  1999-2012 Innoteam S.r.l.
 * @license    http://www.innomatic.org/license/   BSD License
 * @link       http://www.innomatic.org
 * @since      Class available since Release 5.0
 */
class WebappskeletonComponent extends ApplicationComponent
{
    public function __construct ($rootda, $domainda, $appname, $name, $basedir)
    {
        parent::__construct($rootda, $domainda, $appname, $name, $basedir);
    }
    public static function getType ()
    {
        return 'webappskeleton';
    }
    public static function getPriority ()
    {
        return 0;
    }
    public static function getIsDomain ()
    {
        return false;
    }
    public static function getIsOverridable ()
    {
        return false;
    }
    public function doInstallAction ($params)
    {
        // Checks component name.
        if (! strlen($params['name'])) {
            $this->mLog->logEvent('WebappskeletonComponent::doInstallAction', 'Empty webapp skeleton name in application ' . $this->appname, Logger::ERROR);
            return false;
        }
        // Source folder.
        $skeleton_source = $this->basedir . '/WEB-INF/conf/skel/webapps/' . basename($params['name']) . '-skel/';
        // Checks if the skeleton directory exists in application archive.
        if (! is_dir($skeleton_source)) {
            $this->mLog->logEvent('WebappskeletonComponent::doInstallAction', 'Missing webapp skeleton folder (' . basename($params['name']) . '-skel) in application ' . $this->appname, Logger::ERROR);
            return false;
        }
        // Destination folder.
        $skeleton_destination = InnomaticContainer::instance('innomaticcontainer')->getHome() . 'WEB-INF/conf/skel/webapps/' . basename($params['name']) . '-skel/';
        // Copies the skeleton folder to the destination.
        require_once ('innomatic/io/filesystem/DirectoryUtils.php');
        $result = DirectoryUtils::dirCopy($skeleton_source, $skeleton_destination);
        if (! $result) {
            $this->mLog->logEvent('WebappskeletonComponent::doInstallAction', 'Unable to copy webapp skeleton source folder (' . $skeleton_source . ') to its destination (' . $skeleton_destination . ') in application ' . $this->appname, Logger::ERROR);
            return false;
        }
        // Adds the skeleton to the webapps_skeletons table.
        $result = $this->rootda->execute('INSERT INTO webapps_skeletons VALUES (' . $this->rootda->formatText($params['name']) . ',' . $this->rootda->formatText($params['catalog']) . ')');
        if (! $result) {
            $this->mLog->logEvent('WebappskeletonComponent::doInstallAction', 'Unable to insert webapp skeleton ' . basename($params['name']) . ' to webapps_skeletons table in application ' . $this->appname, Logger::ERROR);
            return false;
        }
        return true;
    }
    public function doUninstallAction ($params)
    {
        // Checks component name.
        if (! strlen($params['name'])) {
            $this->mLog->logEvent('WebappskeletonComponent::doUninstallAction', 'Empty webapp skeleton name in application ' . $this->appname, Logger::ERROR);
            return false;
        }
        // Skeleton folder.
        $skeleton_folder = InnomaticContainer::instance('innomaticcontainer')->getHome() . 'WEB-INF/conf/skel/webapps/' . basename($params['name']) . '-skel';
        // Removes skeleton directory.
        require_once ('innomatic/io/filesystem/DirectoryUtils.php');
        $result = DirectoryUtils::unlinkTree($skeleton_folder);
        if (! $result) {
            $this->mLog->logEvent('WebappskeletonComponent::doUninstallAction', 'Unable to remove webapp skeleton folder (' . $skeleton_folder . ') in application ' . $this->appname, Logger::ERROR);
            // The execution continues even if it is unable to remove webapps folder
        // in order to remove the row from the webapps_skeletons table.
        }
        // Removes the webapp skeleton row from the webapps_skeletons table
        $result = $this->rootda->execute('DELETE FROM webapps_skeletons WHERE name=' . $this->rootda->formatText(basename($params['name'])));
        if (! $result) {
            $this->mLog->logEvent('WebappskeletonComponent::doUninstallAction', 'Unable to remove webapp skeleton ' . basename($params['name']) . ' from webapps_skeletons table in application ' . $this->appname, Logger::ERROR);
            return false;
        }
        return true;
    }
    public function doUpdateAction ($params)
    {
        // Checks component name.
        if (! strlen($params['name'])) {
            $this->mLog->logEvent('WebappskeletonComponent::doUdpateAction', 'Empty webapp skeleton name in application ' . $this->appname, Logger::ERROR);
            return false;
        }
        // Source folder.
        $skeleton_source = $this->basedir . '/WEB-INF/conf/skel/webapps/' . basename($params['name']) . '-skel/';
        // Checks if the skeleton directory exists in application archive.
        if (! is_dir($skeleton_source)) {
            $this->mLog->logEvent('WebappskeletonComponent::doUdpateAction', 'Missing webapp skeleton folder (' . basename($params['name']) . '-skel) in application ' . $this->appname, Logger::ERROR);
            return false;
        }
        // Destination folder.
        $skeleton_destination = InnomaticContainer::instance('innomaticcontainer')->getHome() . 'WEB-INF/conf/skel/webapps/' . basename($params['name']) . '-skel/';
        // Removes previous skeleton directory.
        require_once ('innomatic/io/filesystem/DirectoryUtils.php');
        DirectoryUtils::unlinkTree($skeleton_folder);
        // Copies the skeleton folder to the destination.
        require_once ('innomatic/io/filesystem/DirectoryUtils.php');
        $result = DirectoryUtils::dirCopy($skeleton_source, $skeleton_destination);
        if (! $result) {
            $this->mLog->logEvent('WebappskeletonComponent::doUdpateAction', 'Unable to copy webapp skeleton source folder (' . $skeleton_source . ') to its destination (' . $skeleton_destination . ') in application ' . $this->appname, Logger::ERROR);
            return false;
        }
        // Checks if the webapp skeleton row exists in webapps_skeletons table.
        // If it doesn't exists it could means that Innomatic was unable to insert it
        // in a previous application installation. In this case, it tries to reinsert
        // a new row.
        $check_query = $this->rootda->execute('SELECT name FROM webapps_skeletons WHERE name=' . $this->rootda->formatText($params['name']));
        if ($check_query->getNumberRows()) {
            // Updates the skeleton row in the webapps_skeletons table.
            $result = $this->rootda->execute('UPDATE webapps_skeletons SET catalog=' . $this->rootda->formatText($params['catalog']) . ' ' . 'WHERE name=' . $this->rootda->formatText($params['name']));
            if (! $result) {
                $this->mLog->logEvent(
                    'WebappskeletonComponent::doUdpateAction',
                    'Unable to update webapp skeleton row ('
                    . basename($params['name'])
                    . ') in webapps_skeletons table in application '
                    . $this->appname,
                    Logger::ERROR
                );
                return false;
            }
        } else {
            // Adds the skeleton to the webapps_skeletons table.
            $result = $this->rootda->execute('INSERT INTO webapps_skeletons VALUES (' . $this->rootda->formatText($params['name']) . ',' . $this->rootda->formatText($params['catalog']) . ')');
            if (! $result) {
                $this->mLog->logEvent(
                    'WebappskeletonComponent::doUdpateAction',
                    'Unable to insert webapp skeleton '
                    . basename($params['name'])
                    . ' to webapps_skeletons table in application '
                    . $this->appname,
                    Logger::ERROR
                );
                return false;
            }
        }
        return true;
    }
}
