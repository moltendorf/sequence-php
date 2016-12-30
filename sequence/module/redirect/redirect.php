<?php

namespace sequence\module\redirect {

  use sequence as s;

  class Redirect extends s\Module {

    use s\Listener;

    /**
     *
     * @param string $request
     * @param string $request_root
     *
     * @return array|null
     */
    public function request($request, $request_root): ?array {
      $root = $this->root;

      if (strlen($request) > 0) {
        $request = preg_replace('/[^-a-z]/', '', $request);

        $database = $root->database;
        $prefix   = $database->getPrefix();

        $statement = $database->prepare("
					select redirect_value
					from {$prefix}redirects
					where	redirect_key = :key
				");

        $statement->execute([
          'key' => $request
        ]);

        if ($row = $statement->fetch()) {
          header('Cache-Control: s-maxage=14400, max-age=14400');

          return [302, $row[0]];
        } else {
          return [404];
        }
      }
    }
  }
}
