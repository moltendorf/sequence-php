<?php

namespace sequence {

  use PDOStatement;

  trait SQL {

    use Listener;

    /**
     * Array of SQL queries.
     *
     * @var array
     */
    private $sql = [];

    private function prepare(int $key): PDOStatement {
      $root     = $this->root;
      $database = $root->database;

      $pdo = $database->pdo;

      return $pdo->prepare($this->sql[$key]);
    }

    private function execute(int $key, ?array $input_parameters = null): bool {
      $query = $this->prepare($key);

      $result = $query->execute($input_parameters);
      $query->closeCursor();

      return $result;
    }

    private function insertForId(int $key, ?array $input_parameters = null): ?int {
      $root     = $this->root;
      $database = $root->database;
      $pdo      = $database->pdo;

      if ($this->execute($key, $input_parameters)) {
        return $pdo->lastInsertId();
      } else {
        return null;
      }
    }

    private function fetch(int $key, ?array $input_parameters = null): array {
      $query = $this->prepare($key);

      $query->execute($input_parameters);

      $rows = $query->fetchAll();

      $query->closeCursor();

      return $rows;
    }
  }
}
