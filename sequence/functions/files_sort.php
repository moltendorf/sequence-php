<?php

namespace sequence\functions {

  /**
   * Sort an indexed array into an associative array with keys being the last path component.
   *
   * @param array $files
   *
   * @return array
   */
  function files_sort($files) {
    $index = [];

    foreach ($files as $file) {
      $index[basename($file)] = $file;
    }

    return $index;
  }
}
