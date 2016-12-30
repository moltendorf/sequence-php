<?php

namespace sequence\module\redirect {

  use sequence as s;
  use sequence\root\Root;
  use sequence\SQL;

  class Redirect extends s\Module {

    use s\Listener;
    use SQL;

    const SQL_FETCH_REDIRECT_BY_KEY = 0;

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
        self::SQL_FETCH_REDIRECT_BY_KEY => "
          SELECT redirect_value
          FROM {$prefix}redirects
          WHERE	redirect_key = :key"
      ];
    }

    /**
     *
     * @param string $request
     * @param string $request_root
     *
     * @return array|null
     */
    public function request($request, $request_root): ?array {
      if (strlen($request) > 0) {
        $request = preg_replace('/[^-a-z]/', '', $request);

        $rows = $this->fetch(self::SQL_FETCH_REDIRECT_BY_KEY, [
          'key' => $request
        ]);

        if (count($rows)) {
          [$row] = $rows;

          header('Cache-Control: s-maxage=14400, max-age=14400');

          return [302, $row[0]];
        } else {
          return [404];
        }
      }
    }
  }
}
