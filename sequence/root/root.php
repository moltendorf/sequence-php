<?php

namespace sequence\root {

  /**
   * Current version.
   */
  const VERSION = '0.1';

  use Exception;

  /**
   *
   * @property-read Cache    $cache
   * @property-read Database $database
   * @property-read Handler  $handler
   * @property-read Hook     $hook
   * @property-read Language $language
   * @property-read Mail     $mail
   * @property-read Modules  $modules
   * @property-read Path     $path
   * @property-read Settings $settings
   * @property-read Template $template
   */
  class Root {

    /**
     * @var Application
     */
    public $application;

    /**
     * Instantiate the root class. This automatically instantiates the application class too.
     *
     * @param string $systemPath
     * @param string  $homePath
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
