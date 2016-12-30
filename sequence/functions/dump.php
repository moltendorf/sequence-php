<?php

namespace sequence\functions {

  /**
   * Archaic and simple debug function.
   * No longer used due to output automatically being monospaced and included in all error pages. Output is normally
   * considered an error except in certain conditions, so if you need to dump a variable while debugging, just echo
   * it out. No seriously, try it.
   *
   * @param mixed ...$variables
   */
  function dump(...$variables) {
    foreach ($variables as $variable) {
      echo '<pre>';
      var_dump($variable);
      echo '</pre>';
    }
  }
}
