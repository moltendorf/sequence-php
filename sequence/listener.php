<?php

namespace sequence {

  trait Listener {

    /**
     * The root of the application.
     * All major class instances are accessible from this class instance.
     *
     * @var Root
     */
    protected $root;

    /**
     * Stock constructor used when another one isn't implemented.
     *
     * @param Root   $root
     * @param string $binding
     */
    public function __construct(Root $root, $binding = '') {
      $this->bind($root, $binding);
    }

    /**
     * Basic class instance setup.
     *
     * @param Root   $root
     * @param string $binding
     */
    protected function bind(Root $root, $binding = '') {
      $this->root = $root;
    }

    /**
     * Register a class to receive messages from a binding or globally.
     *
     * @param callable    $method
     * @param string      $message
     * @param null|string $binding
     * @param integer     $priority
     */
    protected function listen(callable $method, $message, $binding = null, $priority = 0) {
      $root = $this->root;
      $hook = $root->hook;

      $hook->listen($method, $message, $binding, $priority);
    }
  }
}
