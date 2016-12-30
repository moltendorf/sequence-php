<?php

namespace sequence\functions {

  /**
   * Wrapper for json_decode. It makes associative arrays by default.
   *
   * @param string  $json
   * @param boolean $assoc
   *
   * @return mixed
   */
  function json_decode($json, $assoc = true) {
    return \json_decode($json, $assoc);
  }
}
