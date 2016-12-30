<?php

namespace sequence\functions {

  /**
   * @param $data
   *
   * @return string
   */
  function base64url_decode($data) {
    return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data)%4, '=', STR_PAD_RIGHT));
  }
}
