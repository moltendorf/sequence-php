<?php

namespace sequence\functions {

  /**
   * Format all text for display in HTML.
   *
   * @param string $text
   *
   * @return string
   */
  function text_format($text) {
    return htmlspecialchars($text, ENT_COMPAT | ENT_DISALLOWED | ENT_HTML5);
  }
}
