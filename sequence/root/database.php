<?php

namespace sequence\root {

  use Exception;
  use PDO;
  use PDOStatement;
  use sequence as s;

  class Database {

    use s\Listener;

    /**
     * Table prefix.
     * Used to prevent two or more websites from conflicting with each other.
     *
     * @var string
     */
    public $prefix = 'sequence__';

    /**
     * Database connection.
     *
     * @var PDO
     */
    public $pdo;

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
     * @param Root   $root
     * @param string $binding
     */
    public function __construct(Root $root, $binding = '') {
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

      $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_NUM,
        PDO::ATTR_PERSISTENT         => true
      ];

      $this->pdo = new PDO($this->buildDSN(), $username, $password, $options);

      $this->listen([$this, 'close'], 'close', 'application');
    }

    /**
     * Get the PDO DSN.
     *
     * @return string
     * @throws Exception
     */
    public function buildDSN() {
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

        if (!isset($settings['charset'])) {
          $settings['charset'] = 'utf8';
        }

        $dsn[] = "charset=$settings[charset]";

        break;

      default:
        throw new Exception('DATABASE_TYPE_INVALID');
      }

      $dsn = implode(';', $dsn);

      return "$type:$dsn";
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
     * Push all buffered statements to the database.
     */
    public function close(): void {
      foreach ($this->buffered as $value) {
        /**
         * @var $statement PDOStatement
         */
        [$statement, $arguments] = $value;

        $statement->execute(...$arguments);
        $statement->closeCursor();
      }

      $this->buffered = [];
    }
  }
}
