<?php

namespace sequence {

  use sequence\root\Root;

  /**
   * Main function.
   * Created and called to keep all variables out of global scope.
   *
   * @return Root
   */
  function main(): Root {
    /*
     * Pre-configuration.
     */

    $systemPath = __DIR__;
    $homePath   = "$_SERVER[HOME]/system";

    // Attempt to auto-load files from $homePath first then $systemPath.
    set_include_path(implode(PATH_SEPARATOR, [$homePath, $systemPath]));

    // Classes are arranged by their namespace.
    spl_autoload_extensions('.php');
    spl_autoload_register();

    // Include vendor classes.
    require "$systemPath/vendor/autoload.php";

    // We use UTC for everything internally.
    date_default_timezone_set('UTC');

    return new Root($systemPath, $homePath);
  }
}
