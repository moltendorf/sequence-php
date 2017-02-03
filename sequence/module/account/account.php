<?php
/**
 * Created by PhpStorm.
 * User: moltendorf
 * Date: 16/1/1
 * Time: 13:48
 */

namespace sequence\module\account {

  use sequence as s;
  use sequence\classes\Module;
  use sequence\functions as f;

  class Account extends Module {

    use s\Listener;

    /**
     *
     * @param s\root\Root $root
     * @param string      $binding
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
      $this->listen([$this, 'template'], 'template', 'application');

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

    public function template() {
      $this->root->template->addModule('module/account/account');
    }
  }
}
