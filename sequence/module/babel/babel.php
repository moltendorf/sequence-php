<?php
/**
 * Created by PhpStorm.
 * User: moltendorf
 * Date: 16/1/3
 * Time: 21:12:17
 */

namespace sequence\module\babel {

  use sequence as s;
  use sequence\root\module\Module;
  use sequence\functions as f;

  class Babel extends Module {

    use s\Listener;

    /**
     *
     * @param \sequence\root\Root $root
     * @param string              $binding
     */
    public function __construct(s\root\Root $root, $binding = '') {
      $this->bind($root, $binding);
    }

    /**
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
     *
     * @param array $query
     *
     * @return array
     */
    public function query($query): ?array {
      return null;
    }

    public function transform($type) {
      $root    = $this->root;
      $handler = $root->handler;

      if ($handler->getType() == 'js') {
        $application = $root->application;
        //$application->content;
      }
    }
  }
}
