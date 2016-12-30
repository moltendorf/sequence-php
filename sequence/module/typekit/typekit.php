<?php

namespace sequence\module\typekit {

  use sequence as s;
  use sequence\functions as f;

  class TypeKit extends s\Module {

    use s\Listener;

    /**
     *
     * @param s\Root $root
     * @param string $binding
     */
    public function __construct(s\Root $root, $binding = '') {
      $this->bind($root, $binding);

      $settings = $root->settings;

      if (isset($settings['typekit_kit_id'])) {
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
      $settings = $root->settings;
      $template = $root->template;

      $template->script("//use.typekit.net/$settings[typekit_kit_id].js", false);
      $template->script('/static/script/module/typekit/typekit.js', false);
    }
  }
}
