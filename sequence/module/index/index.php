<?php

namespace sequence\module\index {

  use sequence as s;
  use sequence\root\module\Module;

  class Index extends Module {

    use s\Listener;

    /**
     * Handle a request for a normal page.
     *
     * @param string $request
     * @param string $request_root
     *
     * @return array|null
     */
    public function request($request, $request_root): ?array {
      header('Cache-Control: s-maxage=14400, max-age=3600');

      return [200];
    }

  }
}
