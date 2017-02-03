<?php

namespace sequence\classes {

  use sequence\root\Root;

  abstract class Module {

    /**
     * Handle a query.
     *
     * @param array $query
     *
     * @return array|null
     */
    public function query($query): ?array {
      return null;
    }

    /**
     * Handle a request for a normal page.
     *
     * @param string $request
     * @param string $request_root
     *
     * @return array
     */
    public function request($request, $request_root): ?array {
      return null;
    }

    /**
     * Basic class instance setup.
     *
     * @param Root   $root
     * @param string $binding
     */
    abstract protected function bind(Root $root, $binding = '');
  }
}
