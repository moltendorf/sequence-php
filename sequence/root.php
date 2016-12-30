<?php

namespace sequence {

  /**
   * Current version.
   */
  const VERSION = '0.1';

  use Exception;
  use sequence\root\Application;

  /**
   *
   * @property-read root\Cache    $cache
   * @property-read root\Database $database
   * @property-read root\Handler  $handler
   * @property-read root\Hook     $hook
   * @property-read root\Language $language
   * @property-read root\Mail     $mail
   * @property-read root\Module   $module
   * @property-read root\Path     $path
   * @property-read root\Settings $settings
   * @property-read root\Template $template
   */
  class Root {

    /**
     * @var root\Application
     */
    public $application;

    /**
     * Instantiate the root class. This automatically instantiates the application class too.
     *
     * @param string $systemPath
     * @param array  $homePath
     */
    public function __construct($systemPath, $homePath) {
      $this->application = new Application($this);
      $this->application->setup($systemPath, $homePath);

      if (!isset($this->database)) {
        // Define it so that the magic function does not define it for us.
        $this->database = null;
      }
    }

    /**
     * Instantiate a main class and store the class instance.
     *
     * @param string $original
     *
     * @return mixed
     * @throws Exception
     */
    public function __get($original) {
      $name = strtolower($original);

      if ($original !== $name) {
        throw new Exception('INVALID_VARIABLE_REFERENCE');
      }

      $class = "sequence\\root\\$name";

      spl_autoload($class);
      $this->$name = new $class($this);

      return $this->$name;
    }
  }
}
