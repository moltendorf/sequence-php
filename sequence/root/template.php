<?php
declare(strict_types=1);

namespace sequence\root {

  use Exception;
  use sequence as s;
  use sequence\functions as f;
  use sequence\SQL;

  class Template {

    use s\Broadcaster;
    use SQL;

    /**
     * List of messages that this class can send.
     *
     * @var array
     */
    const MESSAGES = ['template'];

    const SQL_FETCH_ENABLED_STYLES = 0;

    /**
     * Template data variables.
     *
     * @var array
     */
    private $data = [];

    /**
     * Current style.
     *
     * @var string
     */
    private $style = 'default';

    /** Enabled styles.
     *
     * @var array
     */
    private $styles = [];

    /**
     * Configure current template settings.
     *
     * @param Root   $root
     * @param string $binding
     */
    public function __construct(Root $root, string $binding = '') {
      $this->bind($root, $binding);
      $this->clear();

      $application = $root->application;
      $settings    = $root->settings;

      if (!$root->database) {
        goto set;
      }

      $this->buildSQL();

      try {
        foreach ($this->fetch(self::SQL_FETCH_ENABLED_STYLES) as $row) {
          $this->styles[$row[0]] = [
            'customizations' => (boolean)$row[1],
            'loaded'         => false
          ];
        }
      } catch (Exception $exception) {
        $application->errors[] = $exception;
      }

      set:
      if ((!isset($settings['style']) || !$this->setStyle($settings['style'])) && !isset($this->styles['default'])) {
        // Only enable the default style if the preferred style is not enabled.
        $this->styles['default'] = [
          'customizations' => false,
          'loaded'         => false
        ];

        $this->setStyle();
      }
    }

    /**
     * Build all SQL statements.
     */
    private function buildSQL(): void {
      $root     = $this->root;
      $database = $root->database;
      $prefix   = $database->prefix;

      $this->sql = [
        self::SQL_FETCH_ENABLED_STYLES => "
          SELECT style_name, style_customizations
          FROM {$prefix}styles
          WHERE style_is_enabled = 1"
      ];
    }

    /**
     * Bind all classes in root to application identity.
     *
     * @return string
     */
    protected function getBinding(): string {
      return 'application';
    }

    /**
     * Set the current style.
     *
     * @param string $style
     *
     * @return bool
     */
    public function setStyle(string $style = 'default'): bool {
      if ($this->styleEnabled($style)) {
        /**
         * Load style settings.
         *
         * @param string $style
         * @param array  $styles
         *
         * @return bool
         * @throws Exception
         */
        $load = function ($style, $styles = []) use (&$load): bool {
          $settings = &$this->styles[$style];

          // Short circuit if this style is already loaded.
          if ($settings['loaded']) {
            return true;
          }

          $settings['loaded'] = true;

          $styles[] = $style;

          $root = $this->root;
          $path = $root->path;

          if ($file = $path->style("$style/settings.php")) {
            $settings += include $file;
          }

          if (isset($settings['inherits'])) {
            foreach ($settings['inherits'] as $inherit) {
              if (!$this->styleEnabled($inherit)) {
                if (s\SHIP) {
                  goto fail;
                } else {
                  throw new Exception('STYLE_NOT_ENABLED');
                }
              } elseif (in_array($inherit, $styles)) {
                if (s\SHIP) {
                  goto fail;
                } else {
                  throw new Exception('STYLE_INHERITANCE_RECURSION');
                }
              }

              if (!$load($inherit, $styles)) {
                goto fail;
              }
            }
          }

          return true;

          fail:
          // Disable style.
          unset($this->styles[$style]);

          return false;
        };

        if ($load($style)) {
          $this->style = $style;

          return true;
        }
      }

      return false;
    }

    /**
     * Get the current style.
     *
     * @return string
     */
    public function getStyle(): string {
      return $this->style;
    }

    /**
     * Check if the style is enabled.
     *
     * @param string $style
     *
     * @return bool
     */
    public function styleEnabled(string $style = 'default'): bool {
      return isset($this->styles[$style]);
    }

    /**
     * Get use customizations.
     *
     * @param string $style
     *
     * @return bool
     */
    public function styleCustomizations(string $style = 'default'): bool {
      return $this->styleEnabled($style) && $this->styles[$style]['customizations'];
    }

    /**
     * Add template variables.
     *
     * @param array $input
     */
    public function add(array $input): void {
      $this->data += $input;
    }

    /**
     * Set template variables. This overrides variables defined using add()!
     *
     * @param array $input
     */
    public function set(array $input): void {
      $this->data = $input + $this->data;
    }

    /**
     * Add stylesheet to this page.
     *
     * @param string $href
     * @param array  $media
     */
    public function stylesheet(string $href, array $media = []): void {
      $this->data['stylesheets'][] = [
        'href'  => "/static/style/$this->style/$href.css",
        'media' => $media
      ];
    }

    /**
     * Add a script to this page.
     *
     * @param string $script
     * @param bool   $defer
     */
    public function script(string $script, bool $defer = true): void {
      $key = $defer ? 'scripts_deferred' : 'scripts';

      if (is_array($script)) {
        $this->data[$key][] = $script;
      } else {
        $this->data[$key][] = ['src' => $script];
      }
    }

    /**
     * Add a module to this page.
     *
     * @param string $module
     */
    public function addModule(string $module): void {
      // @todo fix hard coded path.
      $this->data['modules'][] = "/static/script/$module";
    }

    /**
     * Add a module to this page.
     *
     * @param string $code
     */
    public function addModuleInline(string $code): void {
      $this->data['modules_inline'][] = $code;
    }

    /**
     * Get a copy of template variables.
     *
     * @return array
     */
    public function get(): array {
      return $this->data;
    }

    /**
     * Clear all template variables.
     */
    public function clear(): void {
      $this->data = [
        'scripts'          => [],
        'scripts_deferred' => [],
        'stylesheets'      => [],
        'modules'          => [],
        'modules_inline'   => []
      ];
    }

    /**
     * Load the template file.
     *
     * @param string $file
     * @param string $prefix
     */
    public function load(string $file, string &$prefix = ""): void {
      $root    = $this->root;
      $handler = $root->handler;

      if ($type = $handler->getType()) {
        $this->broadcast('template:'.$type);
      } else {
        $this->broadcast('template');
      }

      $this->set(['style' => $this->style]);

      $l = $root->language;
      $s = $root->settings;
      $v = $this->data;

      /**
       * Load style variables.
       *
       * @param string $style
       *
       * @return array
       */
      $load = function (string $style) use (&$load, $l, $s, $v): array {
        $root = $this->root;
        $path = $root->path;

        /**
         * Include file and return result.
         *
         * @param string $file
         *
         * @return mixed
         */
        $f = function (string $file) use ($s, $l, $v) {
          return include $file;
        };

        $settings = &$this->styles[$style];

        $customizations = [];

        if ($settings['customizations']) {
          if ($file = $path->home("$path->style/$style/custom/variables.php")) {
            $customizations = $f($file);
          }

          if ($file = $path->system("$path->style/$style/custom/variables.php")) {
            $customizations += $f($file);
          }
        }

        if ($file = $path->home("$path->style/$style/variables.php")) {
          $variables = $f($file);
        } else {
          $variables = [];
        }

        if ($file = $path->system("$path->style/$style/variables.php")) {
          $variables += $f($file);
        }

        if (isset($settings['inherits'])) {
          foreach ($settings['inherits'] as $inherit) {
            list ($_variables, $_customizations) = $load($inherit);

            foreach (array_keys($_customizations) as $key) {
              if (isset($variables[$key])) {
                unset($_customizations[$key]);
              }
            }

            $variables += $_variables;
            $customizations += $_customizations;
          }
        }

        return [$variables, $customizations];
      };

      list($add, $set) = $load($this->style);

      $this->add($add);
      $this->set($set);

      $l = $root->language;
      $s = $root->settings;
      $v = $this->data;

      /**
       * Load template file.
       *
       * @param string $file
       *
       * @throws Exception
       */
      $f = function (string $file) use (&$config, $l, $s, $v, $prefix): void {
        $stack = [$prefix];

        /** @noinspection PhpUnusedLocalVariableInspection */
        /**
         * Load template file.
         *
         * @param string $name
         *
         * @throws Exception
         */
        $f = function (string $name) use (&$f, $l, $s, $v, &$stack): void {
          $prefix = $stack[count($stack) - 1];
          if ($file = $this->file($name, $prefix)) {
            array_push($stack, $prefix);

            include $file;

            array_pop($stack);
          } else {
            throw new Exception('TEMPLATE_FILE_NOT_FOUND');
          }
        };

        if ($file) {
          include $file;
        } else {
          throw new Exception('TEMPLATE_FILE_NOT_FOUND');
        }
      };

      $f($file);
    }

    /**
     * Locate a template file.
     *
     * @param string $name
     * @param string $prefix (optional) if set, this will default to a location if one is not specified.
     *
     * @return string|null
     */
    public function file(string $name, string &$prefix = ""): ?string {
      $root = $this->root;
      $path = $root->path;

      if (strpos($name, ':') === false) {
        $name = $prefix.$name;
      }

      if (($index = strrpos($name, '/')) !== false || ($index = strrpos($name, ':')) !== false) {
        $prefix = substr($name, 0, $index + 1);
      } else {
        $prefix = "";
      }

      $segments = explode(':', $name);

      if (count($segments) > 1) {
        list ($module, $name) = $segments;

        if ($module !== '') {
          $name .= '.php';
          $segment = "module/$module/$name";
        } else {
          $prefix = "";
          $name   = $segment = "$name.php";
        }
      } else {
        $prefix = "";
        $name   = $segment = "$name.php";
      }

      /**
       * Find template file.
       *
       * @param string $style
       *
       * @return string|null
       */
      $find = function (string $style) use (&$find, $segment): ?string {
        $root = $this->root;
        $path = $root->path;

        $settings = &$this->styles[$style];

        if ($settings['customizations'] && $file = $path->style("$style/template/custom/$segment")) {
          return $file;
        }

        if ($file = $path->style("$style/template/$segment")) {
          return $file;
        }

        if (isset($settings['inherits'])) {
          foreach ($settings['inherits'] as $inherit) {
            if ($file = $find($inherit)) {
              return $file;
            }
          }
        }

        return null;
      };

      /*
       * Try finding the template in active style.
       */
      if ($file = $find($this->style)) {
        return $file;
      }

      if (isset($module)) {
        /*
         * Try finding template in module.
         */

        if ($file = $path->module("$module/template/$name")) {
          return $file;
        }
      }

      return null;
    }
  }
}
