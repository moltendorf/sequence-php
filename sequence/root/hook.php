<?php

namespace sequence\root {

  use Exception;
  use sequence as s;

  class Hook {

    /**
     * The root of the application.
     * All major class instances are accessible from this class instance.
     *
     * @var s\Root
     */
    protected $root;

    /**
     * Class instances that are broadcasting to a binding.
     *
     * @var array
     */
    protected $bindings = [];

    /*
     * End re-implementation of b\bind.
     */

    /**
     * Class instances that are listening to specific bindings.
     *
     * @var array
     */
    protected $listeners = [];

    /**
     * Class instances that are listening to messages from any binding.
     *
     * @var array
     */
    protected $global = [];

    /**
     *
     * @param s\Root $root
     * @param string $binding
     */
    final public function __construct(s\Root $root, $binding = '') {
      $this->root = $root;
    }

    /**
     * Register a class instance to broadcast on a binding.
     *
     * @param s\Broadcaster $object
     * @param string        $binding
     *
     * @return &array
     */
    public function &register($object, $binding) {
      if (isset($this->bindings[$binding])) {
        $this->bindings[$binding][] = $object;
      } else {
        $this->bindings[$binding] = [$object];

        $this->listeners[$binding] = [];
      }

      return $this->listeners[$binding];
    }

    /**
     * Broadcast to all class instances listening to a message globally.
     *
     * @param string|null $base
     * @param string      $message
     * @param array       $arguments
     */
    public function broadcast($base, $message, $arguments) {
      if (isset($base)) {
        if (isset($this->global[$base])) {
          foreach ($this->global[$base] as $priority) {
            foreach ($priority as $method) {
              $method($message, ...$arguments);
            }
          }
        }
      }

      if (isset($this->global[$message])) {
        foreach ($this->global[$message] as $priority) {
          foreach ($priority as $method) {
            $method($message, ...$arguments);
          }
        }
      }
    }

    /**
     * Register a class to receive messages from a binding.
     *
     * @param callable    $method
     * @param string      $message
     * @param null|string $binding
     * @param integer     $priority
     *
     * @throws Exception
     */
    public function listen(callable $method, $message, $binding = null, $priority = 0) {
      if (isset($binding)) {
        if (isset($this->listeners[$binding])) {
          if (s\debug && count($this->bindings[$binding])) {
            $success = false;

            foreach ($this->bindings[$binding] as $object) {
              if (in_array($message, $object::messages)) {
                $success = true;

                break;
              }
            }

            if (!$success) {
              throw new Exception("UNDEFINED_MESSAGE: $binding::$message");
            }
          }

          if (isset($this->listeners[$binding][$message])) {
            if (isset($this->listeners[$binding][$message][$priority])) {
              $this->listeners[$binding][$message][$priority][] = $method;
            } else {
              $this->listeners[$binding][$message][$priority] = [$method];

              ksort($this->listeners[$binding][$message]);
            }
          } else {
            $this->listeners[$binding][$message] = [$priority => [$method]];
          }
        } else {
          $this->bindings[$binding] = [];

          $this->listeners[$binding] = [$message => [$priority => [$method]]];
        }
      } else {
        if (isset($this->global[$message])) {
          if (isset($this->global[$message][$priority])) {
            $this->global[$message][$priority][] = $method;
          } else {
            $this->global[$message][$priority] = [$method];

            ksort($this->global[$message]);
          }
        } else {
          $this->global[$message] = [$priority => [$method]];
        }
      }
    }
  }
}
