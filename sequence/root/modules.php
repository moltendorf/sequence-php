<?php

namespace sequence\root {

  use ArrayAccess;
  use Exception;
  use sequence as s;
  use sequence\root\module\Module;
  use sequence\SQL;

  class Modules implements ArrayAccess {

    use s\Broadcaster;
    use SQL;

    /**
     * List of messages that this class can send.
     *
     * @var array
     */
    const MESSAGES = [];

    const SQL_FETCH_ENABLED_MODULES = 0;

    /**
     * Loaded modules.
     *
     * @var array
     */
    private $container = [];

    /**
     * Basic constructor.
     *
     * @param Root   $root
     * @param string $binding
     */
    public function __construct(Root $root, $binding = '') {
      $this->bind($root, $binding);
      $this->buildSQL();
    }

    /**
     * Build all SQL statements.
     */
    private function buildSQL(): void {
      $root     = $this->root;
      $database = $root->database;
      $prefix   = $database->prefix;

      $this->sql = [
        self::SQL_FETCH_ENABLED_MODULES => "
          SELECT module_name
          FROM {$prefix}modules
          WHERE module_is_enabled = 1"
      ];
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
     * Load all enabled modules.
     */
    public function load() {
      $root = $this->root;

      foreach ($this->fetch(self::SQL_FETCH_ENABLED_MODULES) as $row) {
        $class = "sequence\\module\\$row[0]\\$row[0]";

        spl_autoload($class);
        $this->container[$row[0]] = new $class($root);
      }
    }

    /**
     * Get all successfully loaded module instances.
     *
     * @return array
     */
    public function getLoaded() {
      return $this->container;
    }

    /*
     * Implementation of \ArrayAccess.
     */

    /**
     * Check if the module was successfully loaded.
     *
     * @param string $offset
     *
     * @return boolean
     */
    public function offsetExists($offset) {
      return isset($this->container[(string)$offset]);
    }

    /**
     * Get the module instance.
     *
     * @param string $offset
     *
     * @return Module|null
     */
    public function offsetGet($offset) {
      $offset = (string)$offset;

      if (isset($this->container[$offset])) {
        return $this->container[$offset];
      } else {
        return null;
      }
    }

    /**
     * Overriding module instances at runtime is not supported.
     *
     * @param string $offset
     * @param string $value
     *
     * @throws Exception
     */
    public function offsetSet($offset, $value) {
      throw new Exception('METHOD_NOT_SUPPORTED');
    }

    /**
     * Overriding module instances at runtime is not supported.
     *
     * @param string $offset
     *
     * @throws Exception
     */
    public function offsetUnset($offset) {
      throw new Exception('METHOD_NOT_SUPPORTED');
    }

    /*
     * End implementation of \ArrayAccess.
     */
  }
}
