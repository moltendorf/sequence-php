<?php

namespace sequence\root {

  use Exception;
  use PDO;
  use PDOStatement;
  use sequence as s;

  class Database extends PDO {

    use s\Listener;

    /**
     * Table prefix.
     * Used to prevent two or more websites from conflicting with each other.
     *
     * @var string
     */
    public $prefix = 'sequence__';

    /**
     * Database settings.
     *
     * @var array
     */
    private $settings = [];

    /**
     * Buffered statements.
     *
     * @var array
     */
    private $buffered = [];

    /**
     * Basic constructor.
     *
     * @param s\Root $root
     * @param string $binding
     */
    public function __construct(s\Root $root, $binding = '') {
      $this->bind($root, $binding);
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
     * Connect to the database and configure table prefix.
     *
     * @param array $settings
     *
     * @throws Exception
     */
    public function connect($settings) {
      if (!isset($settings['username'])) {
        throw new Exception('NO_DATABASE_USERNAME');
      }

      if (!isset($settings['password'])) {
        throw new Exception('NO_DATABASE_PASSWORD');
      }

      if (isset($settings['prefix'])) {
        $this->prefix = (string)$settings['prefix'];
      }

      $this->settings = $settings;

      $username = (string)$settings['username'];
      $password = (string)$settings['password'];

      parent::__construct($this->getPDODSN(), $username, $password, [self::ATTR_PERSISTENT => true]);

      $this->listen(function () {
        foreach ($this->buffered as $value) {
          /**
           * @var $statement PDOStatement
           */
          list($statement, $arguments) = $value;

          $statement->execute(...$arguments);
          $statement->closeCursor();
        }

        $this->buffered = [];
      }, 'close', 'application');
    }

    /**
     * Get the PDO DSN.
     *
     * @return string
     * @throws Exception
     */
    public function getPDODSN() {
      $settings = $this->settings;

      $dsn = [];

      $type = (string)$settings['type'];

      switch ($type) {
      case 'mysql':
        if (!isset($settings['use'])) {
          throw new Exception('NO_DATABASE_USE');
        }

        $dsn[] = "dbname=$settings[use]";

        if (isset($settings['socket'])) {
          $dsn[] = "unix_socket=$settings[socket]";
        } else {
          if (isset($settings['hostname'])) {
            $dsn[] = "host=$settings[hostname]";

            if (isset($settings['port'])) {
              $dsn[] = "port=$settings[port]";
            }
          }
        }

        break;

      default:
        throw new Exception('DATABASE_TYPE_INVALID');
      }

      return "$type:".implode(';', $dsn);
    }

    /**
     * Get the MDB2 DSN.
     *
     * @return string
     * @throws Exception
     */
    public function getMDBDSN() {
      $settings = $this->settings;

      $dsn = [];

      $type = (string)$settings['type'];

      $username = (string)$settings['username'];
      $password = (string)$settings['password'];

      switch ($type) {
      case 'mysql':
        if (!isset($settings['use'])) {
          throw new Exception('NO_DATABASE_USE');
        }

        $dsn[] = "$username:$password";

        if (isset($settings['socket'])) {
          $dsn[] = "@unix($settings[socket])";
        } else {
          if (isset($settings['hostname'])) {
            $dsn[] = "@$settings[hostname]";

            if (isset($settings['port'])) {
              $dsn[] = ":$settings[port]";
            }
          }
        }

        $dsn[] = "/$settings[use]";

        break;

      default:
        throw new Exception('DATABASE_TYPE_INVALID');
      }

      return "$type://".implode('', $dsn);
    }

    /**
     * Prepare a database query.
     *
     * @param string $statement
     * @param null   $options
     *
     * @return PDOStatement
     */
    public function prepare($statement, $options = null) {
      if ($options) {
        $result = parent::prepare($statement, $options);
      } else {
        $result = parent::prepare($statement);
      }

      $result->setFetchMode(self::FETCH_NUM);

      return $result;
    }

    /**
     * Execute a statement later.
     *
     * @param PDOStatement $statement
     */

    /**
     * Get the table prefix.
     *
     * @return string
     */
    public function getPrefix() {
      return $this->prefix;
    }
  }
}
