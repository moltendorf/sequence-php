<?php

namespace sequence\functions {

  function starts_with($haystack, $needle) {
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
  }
}
