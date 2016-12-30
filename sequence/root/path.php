<?php

namespace sequence\root {

  use Exception;
  use sequence as s;

  class Path {

    use s\Broadcaster;

    /**
     * List of messages that this class can send.
     *
     * @var array
     */
    const MESSAGES = [];

    /**
     * A full path to the system directory.
     *
     * @var string
     */
    public $system;

    /**
     * A full path to the home directory.
     *
     * @var string
     */
    public $home;

    /**
     * A full path to the cache directory.
     *
     * @var string
     */
    public $cache;

    /**
     * A full path to the files directory.
     *
     * @var string
     */
    public $files;

    /**
     * A full path to the web directory.
     *
     * @var string
     */
    public $web;

    /**
     * A relative path to the module directory.
     *
     * @var string
     */
    public $module = 'sequence/module';

    /**
     * A relative path to the script directory.
     *
     * @var string
     */
    public $script = 'script';

    /**
     * A relative path to the style directory.
     *
     * @var string
     */
    public $style = 'style';

    /**
     * Bind all classes in root to application identity.
     *
     * @return string
     */
    protected function getBinding() {
      return 'application';
    }

    /**
     * Configure and define all paths.
     *
     * @param string $systemPath
     * @param string $homePath
     * @param array  $settings
     */
    public function settings($systemPath, $homePath, $settings) {
      $root        = $this->root;
      $application = $root->application;

      $this->system = $systemPath;
      $this->home   = $homePath;

      foreach (['cache', 'files', 'web'] as $key) {
        if (isset($settings[$key]) && is_string($settings[$key]) && strlen($settings[$key])) {
          $path = $settings[$key];

          if ($path[0] === '/') {
            $absolute = $path;
          } else {
            $absolute = "$this->home/$path";
          }
        } else {
          $absolute = "$this->home/$key";
        }

        if (is_dir($absolute)) {
          $this->$key = realpath($absolute);
        } elseif (file_exists($absolute)) {
          $this->$key = null;

          $application->errors[] = new Exception(strtoupper($key).'_PATH_IS_NOT_DIRECTORY');
        } else {
          try {
            mkdir($absolute, 0755, true);

            $this->$key = realpath($absolute);
          } catch (Exception $exception) {
            $this->$key = null;

            $application->errors[] = new Exception(strtoupper($key).'_PATH_NOT_EXIST');
          }
        }
      }
    }

    /**
     * Check if a file exists in the home directory and return its path.
     *
     * @param string $path
     *
     * @return string|false
     */
    public function home($path) {
      if (is_file($file = "$this->home/$path")) {
        return $file;
      }

      return false;
    }

    /**
     * Check if a file exists in the system directory and return its path.
     *
     * @param string $path
     *
     * @return string|false
     */
    public function system($path) {
      if (is_file($file = "$this->system/$path")) {
        return $file;
      }

      return false;
    }

    /**
     * Find and return a path to a file relative to home or system directories.
     *
     * @param string $path
     *
     * @return string|false
     */
    public function file($path) {
      if ($file = $this->home($path)) {
        return $file;
      }

      if ($file = $this->system($path)) {
        return $file;
      }

      return false;
    }

    /**
     * Find all files in passed relative path to home and system.
     *
     * @param string      $directory
     * @param string|null $extension
     *
     * @return array
     */
    public function glob($directory, $extension = null) {
      $root = $this->root;
      $path = $root->path;

      if (isset($extension)) {
        $extension = ".$extension";
      }

      $files = [];

      if (is_dir("$path->home/$directory")) {
        $files = glob("$path->home/$directory/*$extension");
      }

      if (is_dir("$path->system/$directory")) {
        $files = array_merge($files, glob("$path->system/$directory/*$extension"));
      }

      return $files;
    }

    /**
     * Generate an absolute path to a module file.
     *
     * @param string $path
     *
     * @return string
     */
    public function module($path) {
      return $this->file("$this->module/$path");
    }

    /**
     * Generate an absolute path to a script file.
     *
     * @param string $path
     *
     * @return string
     */
    public function script($path) {
      return $this->file("$this->script/$path");
    }

    /**
     * Generate an absolute path to a template file.
     *
     * @param string $path
     *
     * @return string
     */
    public function style($path) {
      return $this->file("$this->style/$path");
    }
  }
}
