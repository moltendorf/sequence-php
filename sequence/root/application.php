<?php

namespace sequence\root {

  use ErrorException;
  use Exception;
  use sequence as s;

  /**
   *
   * @property-read array $debug
   */
  class Application {

    use s\Broadcaster;

    /**
     * List of messages that this class can send.
     *
     * @var array
     */
    const MESSAGES = ['ready', 'output', 'close'];

    /**
     * Errors that occurred during setup.
     *
     * @var array
     */
    public $errors = [];

    /**
     * Settings that take effect if the database is unresponsive.
     *
     * @var array
     */
    public $settings = [];

    /**
     * Contents ready for output.
     *
     * @var string
     */
    public $content;

    /**
     *
     * @var array
     */
    private $debug = false;

    /**
     * Basic constructor. Store reference of root class instance.
     *
     * @param Root $root
     */
    public function __construct(Root $root) {
      $this->root = $root;
    }

    /**
     *
     * @param string $systemPath
     * @param string  $homePath
     */
    public function setup($systemPath, $homePath) {
      $root = $this->root;

      /*
       * Set up output buffering.
       *
       * Output buffering is used to prevent accidental output.
       * If there is any output, an error is thrown with the output dumped to the page in debug mode.
       * If the output buffering level is different by the end of the script, an error is thrown.
       */

      // Cancel the default output buffer (we recommend having it on despite cancelling it).
      if (ini_get('output_buffering') && ob_get_level() === 1) {
        if (ob_get_length()) {
          $buffer = ob_get_clean();
        } else {
          ob_end_clean();
        }
      }

      ob_start();

      if (isset($buffer)) {
        echo $buffer;

        unset($buffer);
      }

      /*
       * Set up the error handler.
       *
       * We only deal with exceptions.
       */

      /**
       * @param $code
       * @param $message
       * @param $file
       * @param $line
       *
       * @throws ErrorException
       */
      $error_handler = function ($code, $message, $file, $line) {
        throw new ErrorException($message, 0, $code, $file, $line);
      };

      set_error_handler($error_handler);

      /*
       * Set up debugging code.
       *
       * Include any debug files, define the sequence\debug constant, and define the sequence\ship constant.
       */

      $debug = "$systemPath/debug";

      if (is_dir($debug)) {
        $this->debug = [];

        foreach (glob("$debug/*.php") as $file) {
          if (is_file($file)) {
            try {
              // Manually including each file as the namespace the classes are in would fool the autoloader.
              include $file;

              $class = 'sequence\\debug\\'.basename($file, '.php');

              if (class_exists($class, false)) {
                // Instantiate the debug class.
                $this->debug[] = new $class($root);
              }
            } catch (Exception $exception) {
              $this->errors[] = $exception;
            }
          }
        }

        unset($class);
      }

      // Allow debug files to set debugging constants.
      if (!defined($const = 'sequence\\debug')) {
        define($const, false);
      }

      if (!defined($const = 'sequence\\debug\\language')) {
        define($const, false);
      }

      unset($const);

      define('sequence\\ship', !s\DEBUG);

      unset($debug);

      /*
       * Bind this class for broadcasting and listening.
       *
       * This was performed late as it relies on the sequence\debug and sequence\ship constants to be defined.
       */

      $this->bind($root);

      /*
       * Include settings files.
       */

      $settingsFile = "$homePath/settings.php";

      if (is_file($settingsFile)) {
        try {
          $settings = include $settingsFile;
        } catch (Exception $exception) {
          $this->errors[] = $exception;

          $settings = [];
        }
      } else {
        $this->errors[] = new Exception('SETTINGS_FILE_NOT_FOUND');

        $settings = [];
      }

      if (isset($settings['application']) && is_array($settings['application'])) {
        $this->settings += $settings['application'];
      }

      $this->settings += require "$systemPath/settings.php";

      if (!isset($settings['path']) || !is_array($settings['path'])) {
        $settings['path'] = [];
      }

      $path = $root->path;

      // Set up our paths.
      $path->settings($systemPath, $homePath, $settings['path']);

      if (isset($settings['database']) && is_array($settings['database'])) {
        try {
          $database = $root->database;

          // Open database connection.
          $database->connect($settings['database']);
        } catch (Exception $exception) {
          $this->errors[] = $exception;
        }
      } else {
        $this->errors[] = new Exception('DATABASE_CONNECTION_FAILED');
      }

      unset($settings);

      $this->broadcast('ready');
    }

    /**
     * Get a private value.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name) {
      switch ($name) {
      case 'debug':
        return $this->debug;
      }

      return null;
    }

    /**
     * Bind all classes in root to application identity.
     *
     * @return string
     */
    protected function getBinding() {
      return 'application';
    }

    /**
     * Start main routine.
     * Run setup method. Load language strings. Parse the request address. Run the module(s). Configure the status
     * and content-type. Generate, compress and send the output. Perform database updates and maintenance.
     *
     * @param boolean $finish
     */
    public function routine($finish = true) {
      $root = $this->root;

      try {
        $level = ob_get_level();

        $language = $root->language;

        if ($this->errors) {
          $language->load();

          throw $this->errors[0];
        }

        /*
         * Load all major core components.
         * ...something breaks when it shouldn't when we load these on demand...something to do with messaging?
         */

        $handler  = $root->handler;
        $module   = $root->modules;
        $template = $root->template;

        /*
         * Load all modules and language files.
         */

        $module->load();
        $language->load();

        // Parse the request.
        if ($handler->parse()) {
          $start = microtime(true)*1e6;

          $handler->request();

          // Calculate the time it took to run the module.
          $template->add(['runtime' => $runtime = microtime(true)*1e6 - $start]);
        }

        if (s\SHIP) {
          $this->generate();

          ob_end_clean();
          echo $this->content;

          if ($finish && function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
          }

          $this->broadcast('close');
        } else {
          if (ob_get_level() != $level) {
            throw new Exception('OUTPUT_BUFFER_LEVEL_DIFFERS');
          }

          if (ob_get_length()) {
            throw new Exception('OUTPUT_BUFFER_NOT_EMPTY');
          }

          $this->generate();
          $this->broadcast('close');

          if (ob_get_length()) {
            throw new Exception('OUTPUT_BUFFER_NOT_EMPTY');
          }

          if (isset($runtime)) {
            if ($runtime > 1e3) {
              $runtime = number_format($runtime/1e3).'ms';
            } else {
              $runtime = number_format($runtime).utf8_decode('µs');
            }

            header("X-Debug-Module-Runtime: $runtime");
          }

          ob_end_clean();

          if (isset($_SERVER['REQUEST_TIME_FLOAT'])) {
            $runtime = microtime(true)*1e6 - $_SERVER['REQUEST_TIME_FLOAT']*1e6;

            // Create an extra header warning when runtime is greater than 100ms.
            if ($runtime > 1e5) {
              header('X-Debug-Warning-Runtime: Runtime is over 100ms!');
            }

            if ($runtime > 1e3) {
              $runtime = number_format($runtime/1e3).'ms';
            } else {
              $runtime = number_format($runtime).utf8_decode('µs');
            }

            header("X-Debug-Total-Runtime: $runtime");
          }

          echo $this->content;
          // We do not call fastcgi_finish_request() to ensure every bit of detail makes its way out.
        }
      } catch (Exception $exception) {
        $handler = $root->handler;

        $handler->error($exception);

        $this->generate();

        ob_end_clean();
        echo $this->content;
      }
    }

    /**
     * Generate output for request.
     */
    public function generate() {
      $root    = $this->root;
      $handler = $root->handler;

      $this->content = $handler->output();

      $this->broadcast('output', $this);

      // Prevent issues if we're debugging.
      if (s\SHIP) {
        $digest        = base64_encode(pack('H*', md5($this->content)));
        $this->content = gzencode($this->content, 9);

        header('Content-Encoding: gzip');
        header('Content-Length: '.mb_strlen($this->content, '8bit'));
        header("Content-MD5: $digest");
      }

      header('Content-Type: '.$handler->getTypeRaw());
      header('Last-Modified: '.(new \DateTime('now', new \DateTimeZone('GMT')))->format('D, d M Y H:i:s T'));
    }
  }
}
