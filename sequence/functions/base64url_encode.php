<?php

namespace sequence\functions {

  /**
   * @param $data
   *
   * @return string
   */
  function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
  }
}
