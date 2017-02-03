<?php

namespace sequence\module\analytics {

  use sequence as s;
  use sequence\root\module\Module;
  use sequence\functions as f;

  class Analytics extends Module {

    use s\Listener;

    /**
     *
     * @param s\root\Root $root
     * @param string      $binding
     */
    public function __construct(s\root\Root $root, $binding = '') {
      $this->bind($root, $binding);

      $settings = $root->settings;

      if (isset($settings['analytics_tracking_id'])) {
        $this->listen([$this, 'template'], 'template', 'application', -10);
      }
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

    /**
     *
     */
    public function template() {
      $root     = $this->root;
      $template = $root->template;

      $template->script('/static/script/module/analytics/analytics.js');
    }
  }
}
