<?php

namespace sequence\root {

  use Exception;
  use sequence as s;
  use sequence\functions as f;

  class Handler {

    use s\Broadcaster;

    /**
     * List of messages that this class can send.
     *
     * @var array
     */
    const messages = ['module', 'query'];

    /**
     * Array of content types.
     * Each value contains an array of parameters to call setTypeRaw() with.
     *
     * @var array
     */
    private $types;

    /**
     * Navigation configuration.
     *
     * @var array
     */
    private $navigation = [];

    /**
     * Tree of all configured paths.
     * Used to quickly traverse to the most unique path using the current request.
     *
     * @var array
     */
    private $tree = [];

    /**
     * Query path.
     *
     * @var string|null
     */
    private $query = null;

    /**
     * Module involved in handling this request.
     *
     * @var array
     */
    private $modules;

    /**
     * Request to be passed to the module.
     *
     * @var string
     */
    private $request;

    /**
     * Status code to return to the client.
     *
     * @var int
     */
    private $status = 200;

    /**
     * Method to call to generate output.
     *
     * @var callable|null
     */
    private $method = null;

    /**
     * Current type (file extension).
     */
    private $type = null;

    /**
     * Current content type.
     *
     * @var string|null
     */
    private $typeRaw = null;

    /**
     * @param Root   $root
     * @param string $binding
     */
    public function __construct(Root $root, $binding = '') {
      $this->bind($root, $binding);

      $path = $root->path;

      $types = require "$path->system/types.php";

      foreach ($types as $type) {
        foreach ($type[0] as $extension) {
          $this->types[$extension] = $type[1];
        }
      }

      $database = $root->database;

      if (isset($database)) {
        $prefix = $database->getPrefix();

        $statement = $database->prepare("
				select path_root, module_name, module_display, path_alias, path_is_prefix, path_order
				from {$prefix}paths
				join {$prefix}modules
					using (module_id)
				where	module_is_enabled = 1
					and	path_is_enabled = 1
				order by
					path_order asc
			");

        $statement->execute();

        foreach ($statement->fetchAll() as $row) {
          $path = substr($row[0], 1);

          if (strlen($path) > 0) {
            $segments = explode('/', $path);
          } else {
            $segments = [];
          }

          $branch = &$this->tree;

          foreach ($segments as $segment) {
            if (!isset($branch['branches'])) {
              $branch['branches'] = [];
            }

            if (!isset($branch['branches'][$segment])) {
              $branch['branches'][$segment] = [];
            }

            $branch = &$branch['branches'][$segment];
          }

          $branch['path']    = "$path/";
          $branch['module']  = $row[1];
          $branch['display'] = $row[2];
          $branch['alias']   = $row[3];
          $branch['prefix']  = (boolean)$row[4];

          unset($branch);

          // Check if this path is to be included in the navigation.
          if ($row[5]) {
            $this->navigation[] = [
              'path'    => $path,
              'module'  => $row[1],
              'display' => $row[2]
            ];
          }
        }
      }
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
     * Parse the request.
     * Figure out what module need to be messaged.
     *
     * @param string|null $request
     *
     * @return bool
     * @throws Exception
     */
    public function parse($request = null) {
      $root     = $this->root;
      $settings = $root->settings;
      $template = $root->template;

      if (!isset($request)) {
        $request = $_SERVER['REQUEST_URI'];
      }

      unset($find, $replace);

      if (isset($settings['root'])) {
        $path = $settings['root'];
      } else {
        $path = '/';
      }

      $length = strlen($path);

      // Check if the request is in the configured root.
      if (substr("$request/", 0, $length) !== $path) {
        // 500.
        throw new Exception('HANDLER_NOT_LOADED_IN_CONFIGURED_ROOT');
      }

      // Normalize the request to our path root.
      $normalized = f\path_normalize(substr($request, $length));

      if (isset($settings['query'])) {
        $query = $settings['query'];
      } else {
        $settings['query'] = $query = '/query/';
      }

      $length = strlen($query);

      // Check if the request is for the query interface.
      if (substr("/$normalized/", 0, $length) === $query) {
        return $this->parseQuery();
      }

      $length = strlen($normalized);

      if ($length > 0) {
        $segments = explode('/', $normalized);
      } else {
        $segments = [];
      }

      unset($length);

      $branch = $selected = $this->tree;
      $prefix = $branch['prefix'];

      foreach ($segments as $key => &$segment) {
        if ($prefix) {
          if (isset($selected)) {
            $previous = $selected;
          }
        } else {
          $prefix = true;

          if (isset($previous)) {
            $selected = $previous;
          } else {
            unset($selected);
          }
        }

        if (isset($branch['branches'])) {
          if (isset($branch['branches'][$segment])) {
            next:
            $branch = $branch['branches'][$segment];

            if (isset($branch['module'])) {
              $prefix   = $branch['prefix'];
              $selected = $branch;
            }

            continue;
          } else {
            $length = strrpos($segment, '.');

            // Try removing the file extension.
            if ($length !== false) {
              $test = substr($segment, 0, $length);

              if (isset($branch['branches'][$test])) {
                $segment = $test;

                goto next;
              }
            } else {
              $test = $segment;
            }

            goto index;
          }
        } else {
          $length = strrpos($segment, '.');

          // Try removing the file extension.
          if ($length !== false) {
            $test = substr($segment, 0, $length);
          } else {
            $test = $segment;
          }

          index:
          if (!isset($segments[$key + 1]) && isset($branch['module']) && !$branch['prefix'] && $test === 'index') {
            $selected = $branch;

            unset($segments[$key]);
          }

          break;
        }
      }

      unset($segment);

      // Segments may have been edited.
      $normalized = implode('/', $segments);

      if (isset($selected)) {
        $this->modules = [$selected['module']];
        $this->request = [
          $selected['alias'].substr($normalized, strlen($selected['path'])),
          $path.$selected['path']
        ];

        $template->add([
          'module_name'    => $selected['module'],
          'module_display' => $selected['display']
        ]);

        if ($_SERVER['REQUEST_URI'] != $path.$normalized) {
          $this->redirect($path.$normalized);

          return false;
        }

        return true;
      }

      if (!isset($this->method)) {
        $this->error();
      }

      return false;
    }

    /**
     * Parse a query.
     *
     * @return bool
     */
    public function parseQuery() {
      $root     = $this->root;
      $module   = $root->module;
      $settings = $root->settings;

      $limit     = $settings['query_limit'];
      $raw_limit = $settings['query_raw_limit'];

      if ($limit === null || $raw_limit === null) {
        $max = ini_get('memory_limit');

        $quantifier = substr($max, -1);
        $max        = (int)substr($max, 0, -1);

        switch ($quantifier) {
        case 'k':
        case 'K':
          // Hopefully this is more than 4MiB.
          $max *= 2**10;
          break;

        case 'M':
        case 'm':
          $max *= 2**20;
          break;

        case 'g':
        case 'G':
          $max *= 2**30;
          break;

        case 't':
        case 'T':
          // Wow.
          $max *= 2**40;
          break;

        case '-':
          $max = 128*2**20;
          break;

        default:
          $max = $max*10 + (int)$quantifier;
        }

        if ($limit === null) {
          if ($max > 2**20) {
            // Limit normal queries to 1/16 of memory limit (this should be around 8MiB on default installs).
            $limit = floor($max/16);
          } else {
            $limit = 64*2**10; // 64KiB minimum limit.
          }

          $settings->offsetStore('query_limit', $limit);
        }

        if ($raw_limit === null) {
          if ($max > 512*2**10) {
            // Limit raw queries to 1/4 of memory limit (this should be around 32MiB on default installs).
            $raw_limit = floor($max/4);
          } else {
            $raw_limit = 64*2**10; // 64KiB minimum limit.
          }

          $settings->offsetStore('query_raw_limit', $limit);
        }
      } else {
        $limit     = (int)$limit;
        $raw_limit = (int)$raw_limit;
      }

      if (isset($_GET['module'])) {
        $name = $_GET['module'];

        if (isset($module[$name])) {
          $this->modules[] = $name;

          if (isset($_GET['raw']) && $_GET['raw'] === '') {
            // Fetch input without processing it.
            $this->query = [
              $name => file_get_contents('php://input', false, null, 0, $raw_limit + 1)
            ];
          } else {
            $this->query = [
              $name => f\json_decode(file_get_contents('php://input', false, null, 0, $limit + 1))
            ];
          }
        } else {
          $this->query = null;
        }
      } else {
        $this->query = f\json_decode(file_get_contents('php://input', false, null, 0, $limit + 1));

        if (!is_array($this->query)) {
          $this->query = [];
        }

        ksort($this->query);

        $this->modules = [];

        // Check if this module is enabled.
        foreach (array_keys($this->query) as $name) {
          if (isset($module[$name])) {
            $this->modules[] = $name;
          } else {
            // Remove queries to disabled modules.
            unset($this->query[$name]);
          }
        }
      }

      return true;
    }

    /**
     * Get the currently active module(s).
     *
     * @return array
     */
    public function getModules() {
      return $this->modules;
    }

    /**
     * Perform request.
     */
    public function request() {
      $root   = $this->root;
      $module = $root->module;

      if (isset($this->query)) {
        $this->broadcast('query');

        $response = [];

        foreach ($this->query as $name => $input) {
          $response[$name] = $module[$name]->query($input);

          unset($this->query[$name]);
        }

        $this->setMethod(function () use ($response) {
          echo json_encode((object)$response);
        });

        $this->setStatus(200);
        $this->setType('json');
      } else {
        $name = $this->modules[0];
        $this->broadcast("module:$name");
        $info = $module[$name]->request(...$this->request);

        if (is_int($info)) {
          $status = $info;
        } elseif (is_array($info)) {
          list($status, $page) = $info;
        } else {
          $status = 200;
        }

        switch ($status) {
        case 301:
        case 302:
        case 303:
        case 307:
          if (isset($page) && is_string($page)) {
            $this->redirect($page, $status);
          } else {
            $this->setStatus($status);
          }

          break;

        case 404:
          $length = strrpos($this->request[0], '.');

          // Try removing the file extension.
          if ($length !== false) {
            $test = substr($this->request[0], 0, $length);
          } else {
            $test = $this->request[0];
          }

          if ($length !== false && $test === 'index') {
            $this->redirect(substr($this->request[1], 0, -1));

            break;
          }

          $this->error($status);

          break;

        case 401:
        case 403:
        case 410:
        case 444: // nginx "drop connection" status.
        case 500:
        case 503:
          $this->error($status);

          break;

        case 200:
        case 204:
        case 304:
        default:
          $this->setStatus($status);
        }
      }
    }

    /**
     * Get the navigation data fetched from the path table.
     *
     * @return array
     */
    public function getNavigation() {
      return $this->navigation;
    }

    /**
     * Set the method to generate the output.
     *
     * @param callable $method
     * @param array    $arguments
     */
    public function setMethod($method, $arguments = []) {
      $this->method = [$method, $arguments];
    }

    /**
     * Set the current template file.
     *
     * @param string|false|array $input
     * @param callable           $method
     *
     * @return string|false|null
     */
    public function setTemplate($input, callable $method = null) {
      $root     = $this->root;
      $template = $root->template;

      if (!is_array($input)) {
        $input = [$input];
      }

      list($name, $customizations, $style) = array_pad($input, 3, null);

      if ($name !== false) {
        $file = $template->file($name, $prefix);

        /**
         * Load the current template file.
         */
        $this->setMethod(function () use ($name, $prefix, $file, $customizations, $style, $method) {
          $root     = $this->root;
          $template = $root->template;

          if (!isset($this->typeRaw)) {
            $this->setType(substr($name, strrpos($name, '.') + 1));
          }

          if (is_callable($method)) {
            $method();
          }

          $template->load($file, $prefix);
        });

        return $file;
      } else {
        if (s\debug) {
          $this->info('BLANK_PAGE');
        } else {
          $this->setType('txt');

          /**
           * Do nothing.
           */
          $this->setMethod(function () {
            // Seriously, do nothing.
          });
        }

        return null;
      }
    }

    /**
     * Set the current template file directly.
     *
     * @param string|false|array $input
     * @param callable           $method
     *
     * @return string|false
     */
    public function setTemplateRaw($input, callable $method = null) {
      if (!is_array($input)) {
        $input = [$input];
      }

      list($file, $customizations, $style) = array_pad($input, 3, null);

      /**
       * Load the current template file.
       */
      $this->setMethod(function () use ($file, $customizations, $style, $method) {
        $root     = $this->root;
        $template = $root->template;

        if (is_callable($method)) {
          $method();
        }

        $template->load($file, $customizations, $style);
      });

      return $file;
    }

    /**
     * Get the current HTTP status.
     *
     * @return int
     */
    public function getStatus() {
      return $this->status;
    }

    /**
     * Set the current HTTP status.
     *
     * @param int $status
     */
    public function setStatus($status = 200) {
      $this->status = $status;
    }

    /**
     * Get the current content type (file extension).
     *
     * @return string
     */
    public function getType() {
      return $this->type;
    }

    /**
     * Get the current content type.
     *
     * @return string
     */
    public function getTypeRaw() {
      return $this->typeRaw;
    }

    /**
     * Set the current content type based on file extension.
     *
     * @param string      $type
     * @param string|null $charset
     *
     * @throws Exception
     */
    public function setType($type, $charset = null) {
      if (isset($this->types[$type])) {
        if (isset($charset)) {
          $this->setTypeRaw($this->types[0], $charset);
        } else {
          $this->setTypeRaw(...$this->types[$type]);
        }

        $this->type = $type;
      } else {
        throw new Exception('UNKNOWN_TYPE_SET');
      }
    }

    /**
     * Set the current content type directly.
     *
     * @param string $type
     * @param string $charset
     */
    public function setTypeRaw($type, $charset = 'utf-8') {
      $this->type = null;

      if (isset($charset)) {
        $this->typeRaw = "$type; charset=$charset";
      } else {
        $this->typeRaw = $type;
      }
    }

    /**
     * Clear the current content type.
     */
    public function clearType() {
      $this->type    = null;
      $this->typeRaw = null;
    }

    /**
     * Generate and return all output.
     *
     * @param boolean $default Use the default template if one is not specified.
     *
     * @return string
     * @throws Exception
     */
    public function output($default = true) {
      $root     = $this->root;
      $template = $root->template;

      if (isset($this->method)) {
        list ($method, $arguments) = $this->method;

        http_response_code($this->status);
        $template->set(['status' => $this->status]);

        ob_start();

        try {
          $method(...$arguments);

          if (!isset($this->typeRaw)) {
            throw new Exception('NO_TYPE_SET');
          }
        } catch (Exception $exception) {
          ob_end_flush();

          throw $exception;
        }

        return ob_get_clean();
      } else {
        if ($default) {
          try {
            $this->setTemplate("{$this->modules[0]}:{$this->modules[0]}.html");

            // Let's try this again...
            return $this->output(false); // False for no infinite loops.
          } catch (Exception $exception) {
            // Welp, that clearly didn't work.
          }
        }

        throw new Exception('TEMPLATE_FILE_NOT_SET');
      }
    }

    /**
     * Generate an info page.
     *
     * @param string $title
     * @param string $message
     */
    public function info($title, $message = null) {
      $root     = $this->root;
      $language = $root->language;
      $template = $root->template;

      $template->clear();

      $this->typeRaw = null;
      $this->setStatus(200);
      $this->setTemplate('info.html', function () use ($title, $message, $language, $template) {
        if (isset($language[$title])) {
          if (!isset($message)) {
            $message = $title.'_MESSAGE';
          }

          $title = $language[$title];
        }

        if (isset($language[$message])) {
          $message = $language[$message];
        } elseif (!isset($message)) {
          $message = $title;
          $title   = $language['INFO'];
        }

        $template->set([
          'title'             => $title,
          'message'           => $message,
          'redirect_location' => null
        ]);
      });
    }

    /**
     * Generate a redirect page.
     *
     * @param string      $location
     * @param int|null    $status
     * @param null|string $title
     * @param null|string $message
     * @param int         $delay
     */
    public function redirect($location, $status = 302, $title = null, $message = null, $delay = 15) {
      $root     = $this->root;
      $template = $root->template;

      $template->clear();

      $this->typeRaw = null;

      if (s\ship) {
        $this->setStatus($status);
      } else {
        $this->setStatus(302); // Override status.
      }

      if ($title === null && s\ship) {
        header("Location: $location");

        $this->setTemplate(false);
      } else {
        if ($delay && false) {
          header("Refresh: $delay; url=$location");
        }

        $root     = $this->root;
        $language = $root->language;
        $template = $root->template;

        $this->setTemplate('info.html', function () use ($location, $title, $message, $status, $language, $template) {
          // Create location.
          if (($scheme = parse_url($location, PHP_URL_SCHEME)) != '') {
            $display = substr($location, strlen($scheme) + 3);
          } elseif (substr($location, 0, 2) == '//') {
            $display = substr($location, 2);
          } elseif (substr($location, 0, 1) == '/') {
            $host = $_SERVER['HTTP_HOST'];

            $display = $host.$location;
          } else {
            $host     = $_SERVER['HTTP_HOST'];
            $document = $_SERVER['DOCUMENT_URI'];

            $display = $host.substr($document, 0, strrpos($document, '/') + 1).$location;
          }

          // Create title and message.
          if (!isset($title)) {
            $title = $language["INFO_$status"];
          } elseif (isset($language[$title])) {
            $title = $language[$title];
          }

          if (isset($language[$message])) {
            $message = $language[$message];
          }

          $template->set([
            'title'             => $title,
            'message'           => $message,
            'redirect_location' => $location,
            'redirect_display'  => preg_replace('/[\\/]$/', '', $display)
          ]);
        });
      }
    }

    /**
     * Generate an error page.
     *
     * @param int|Exception $status
     */
    public function error($status = null) {
      $root     = $this->root;
      $template = $root->template;

      $template->set([
        'title'             => null,
        'message'           => null,
        'redirect_location' => null
      ]);

      $this->typeRaw = null;

      if (s\ship) {
        $this->setTemplate('info.html');
      } else {
        $this->setTemplate('info_debug.html');

        if (ob_get_length()) {
          $template->set(['error_output' => f\text_format(f\text_normalize(ob_get_contents()))]);
        }
      }

      if ($status instanceof Exception) {
        $this->setStatus(500);

        if (s\debug) {
          do {
            $exception[] = [
              'instance' => $status,
              'class'    => get_class($status),
              'message'  => $status->getMessage(),
              'file'     => $status->getFile(),
              'line'     => $status->getLine(),
              'trace'    => f\text_format(f\text_normalize(f\trace_text($status->getTrace())))
            ];
          } while ($status = $status->getPrevious());

          $template->set(['error_exceptions' => $exception]);
        }
      } elseif (is_int($status)) {
        $this->setStatus($status);
      } else {
        $this->setStatus(404);
      }
    }
  }
}
