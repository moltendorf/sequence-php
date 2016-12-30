<?php

namespace sequence\functions {

  /**
   * Automatically read and parse a JSON file into associative arrays.
   *
   * @param string $filename
   *
   * @return mixed
   */
  function file_get_json($filename) {
    $json = \file_get_contents($filename);

    if ($json !== false) {
      return json_decode($json);
    }

    return false;
  }
}
