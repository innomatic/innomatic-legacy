<?php

namespace Innomatic\Application;
use Innomatic\Core\InnomaticContainer;
use Innomatic\Core\RootContainer;

class ComposerManager
{
    public function getApplicationsWithComposer()
    {
        // Holds the list of the available application providing
        // composer.json file.
        $composerList = [];
        
        $container = InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer'); 
        $rootDA = $container->getDataAccess();
        
        // Get the list of the installed applications.
        $applications = $rootDA->execute(
            'SELECT appid FROM applications'
        );
        
        // Home of the applications directories.
        $applicationsHome = RootContainer::instance('\Innomatic\Core\RootContainer')
            ->getLegacyHome()
            . 'innomatic/core/applications/';

        // Scan the applications.
        while (!$applications->eof) {
            $composerFile = $applicationsHome . $applications->getFields('appid') . '/composer.json';
            if (file_exists($composerFile)) {
                echo "DIR OK", PHP_EOL;
                $composerList[] = ['name' => $applications->getFields('appid'), 'path' => $composerFile];
            }
            $applications->moveNext();
        }
        
        return $composerList;
    }
    
    public function fetchConfigurations()
    {
        $conf = [];
        $applications = $this->getApplicationsWithComposer();

        foreach ($applications as $application) {
            if (!$content = @file_get_contents($application['path'])) {
                throw new \RuntimeException('Unable to read Composer configuration file ' . $application['path']); 
            }
            
            if (!$configuration = json_decode($content, true)) {
                throw new \UnexpectedValueException('Composer configuration file ' . $application['path'] . ' is not a valid JSON file');
            }

            $conf[$application['name']] = $configuration; 
        }

        return $conf;
    }

    public function updateConfiguration()
    {
        $platformHome = RootContainer::instance('\Innomatic\Core\RootContainer')
            ->getPlatformHome();

        $configurations = $this->fetchConfigurations();
        
        $configuration = $this->buildConfiguration($configurations);
        // Write the new composer.json file.
        $composerFile = $platformHome . 'composer.json';
        $jsonOptions = JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES;
  
        if (!@file_put_contents($composerFile, json_encode($configuration, $jsonOptions))) {
            throw new \RuntimeException('Error writing composer.json file');
        }
    }
    
    public function updateDependencies()
    {
        $this->updateConfiguration();

        $platformHome = RootContainer::instance('\Innomatic\Core\RootContainer')
            ->getPlatformHome();
        
        if (file_exists($platformHome . 'composer.json')) {
            if (file_exists($platformHome . 'composer.lock')) {
                echo 'COMPOSER UPDATE', PHP_EOL;
            } else {
                echo "COMPOSER INSTALL", PHP_EOL;
            }
        } else {
            echo "NO COMPOSER", PHP_EOL;
        }
    }
    
    /**
     * Compares the passed minimum stability requirements.
     *
     * @return int
     *   Returns -1 if the first version is lower than the second, 0 if they are
     *   equal, and 1 if the second is lower.
     *
     * @throws \UnexpectedValueException
     */
    public function compareStability($a, $b)
    {
        $number = array(
            'dev' => 0,
            'alpha' => 1,
            'beta' => 2,
            'RC' => 3,
            'rc' => 3,
            'stable' => 4,
        );

        if (!isset($number[$a]) || !isset($number[$b])) {
            throw new \UnexpectedValueException('Unexpected value for "minimum-stability"');
        }

        if ($number[$a] == $number[$b]) {
            return 0;
        } else {
            return $number[$a] < $number[$b] ? -1 : 1;
        }
    }
    
    public function buildConfiguration(array $data) {
  $combined = array();
  foreach ($data as $module => $json) {
    // @todo Detect duplicates, maybe add an "ignore" list. Figure out if this
    // encompases all keys that should be merged.
    $to_merge = array(
      'require',
      'require-dev',
      'conflict',
      'replace',
      'provide',
      'suggest',
      'repositories',
    );

    foreach ($to_merge as $key) {
      if (isset($json[$key])) {
        if (isset($combined[$key]) && is_array($combined[$key])) {
          $combined[$key] = array_merge($combined[$key], $json[$key]);
        }
        else {
          $combined[$key] = $json[$key];
        }
      }
    }

    $autoload_options = array('psr-0', 'psr-4');
    foreach ($autoload_options as $option) {
      if (isset($json['autoload'][$option])) {
        $namespaces = (array) $json['autoload'][$option];
        foreach ($json['autoload'][$option] as $namesapce => $dirs) {
          $dirs = (array) $dirs;
          ////array_walk($dirs, 'composer_manager_relative_autoload_path', $module);
          if (!isset($combined['autoload'][$option][$namesapce])) {
            $combined['autoload'][$option][$namesapce] = array();
          }
          $combined['autoload'][$option][$namesapce] = array_merge(
            $combined['autoload'][$option][$namesapce], $dirs
          );
        }
      }
    }

    // Merge in the "classmap" and "files" autoload options.
    $autoload_options = array('classmap', 'files');
    foreach ($autoload_options as $option) {
      if (isset($json['autoload'][$option])) {
        $dirs = (array) $json['autoload'][$option];
        ////array_walk($dirs, 'composer_manager_relative_autoload_path', $module);
        if (!isset($combined['autoload'][$option])) {
          $combined['autoload'][$option] = array();
        }
        $combined['autoload'][$option] = array_merge(
          $combined['autoload'][$option], $dirs
        );
      }
    }

    // Take the lowest stability.
    if (isset($json['minimum-stability'])) {
      if (!isset($combined['minimum-stability']) || -1 == $this->compareStability($json['minimum-stability'], $combined['minimum-stability'])) {
        $combined['minimum-stability'] = $json['minimum-stability'];
      }
    }
  }

  return $combined;
}

/**
 * Returns the realpath to the Composer file directory.
 *
 * @return string
 *
 * @throws \RuntimeException
 */
function composer_manager_file_dir() {
  $dir_uri = variable_get('composer_manager_file_dir', file_default_scheme() . '://composer');
  if (!$realpath = drupal_realpath($dir_uri)) {
    throw new \RuntimeException(t('Error resolving directory: @dir', array('@dir' => $dir_uri)));
  }
  return $realpath;
}

/**
 * Returns the path for the autoloaded directory or class relative to the
 * directory containing the composer.json file.
 */
function composer_manager_relative_autoload_path(&$path, $key, $module) {
  static $dir_from = NULL;
  if (NULL === $dir_from) {
    $dir_from = composer_manager_file_dir();
  }
  $dir_to = DRUPAL_ROOT . '/' . drupal_get_path('module', $module) . '/' . $path;
  $path = composer_manager_relative_dir($dir_to, $dir_from);
}

/**
 * Returns the vendor directory relative to the composer file directory.
 *
 * @return string
 *
 * @throws \RuntimeException
 */
function composer_manager_relative_vendor_dir() {
  return composer_manager_relative_dir(
    composer_manager_vendor_dir(),
    composer_manager_file_dir()
  );
}

/**
 * Gets the path of the "to" directory relative to the "from" directory.
 *
 * @param array $dir_to
 *   The absolute path of the directory that the relative path refers to.
 * @param array $dir_from
 *   The absolute path of the directory from which the relative path is being
 *   calculated.
 *
 * @return string
 */
function composer_manager_relative_dir($dir_to, $dir_from) {
  $dirs_to = explode('/', ltrim($dir_to, '/'));
  $dirs_from = explode('/', ltrim($dir_from, '/'));

  // Strip the matching directories so that both arrays are relative to a common
  // position. The count of the $dirs_from array tells us how many levels up we
  // need to traverse from the directory containing the composer.json file, and
  // $dirs_to is relative to the common position.
  foreach ($dirs_to as $pos => $dir) {
    if (!isset($dirs_from[$pos]) || $dirs_to[$pos] != $dirs_from[$pos]) {
      break;
    }
    unset($dirs_to[$pos], $dirs_from[$pos]);
  }

  $path = str_repeat('../', count($dirs_from)) . join('/', $dirs_to);
  if (PHP_OS == 'WINNT'){
    $path = preg_replace('%..\\/([a-zA-Z])%i', '${1}', $path, 1);
  }
  return $path;
}
}