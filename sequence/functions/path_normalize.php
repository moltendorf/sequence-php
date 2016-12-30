<?php

namespace sequence\functions {

  /**
   * Automatically filter out any parent and current directory references in a path.
   *
   * @param string $path
   *
   * @return string
   */
  function path_normalize($path) {
    $path     = preg_replace(['/^\\/+|[!"&\'\\-.\\/:;=?@\\\\_]+$/', '/\\/\\/+/'], ['', '/'], $path);
    $segments = explode('/', $path);

    for ($i = 0, $length = count($segments); $i < $length; ++$i) {
      if ($segments[$i] === '.') {
        goto current_directory;
      }

      if ($segments[$i] === '..') {
        if ($i > 0) {
          array_splice($segments, $i - 1, 2);

          $i -= 2;
          $length -= 2;

          continue;
        }

        goto current_directory;
      }

      continue;

      current_directory:
      array_splice($segments, $i, 1);

      --$i;
      --$length;
    }

    return implode('/', $segments);
  }
}
