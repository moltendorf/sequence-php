<?php

namespace sequence\functions {

  /**
   * Automatically convert all tabs to spaces and make all line-feeds unix style.
   * Excellent for displaying pre-formatted monospace text.
   *
   * @param string $text
   * @param int    $tab
   *
   * @return string
   */
  function text_normalize($text, $tab = 4) {
    // Normalize all line breaks to line feeds, then split the text into an array at each line break.
    $text = explode("\n", str_replace(["\r\n", "\r"], "\n", $text));

    // Convert tabs to spaces.
    for ($i = 0, $j = count($text); $i < $j; ++$i) {
      $post = $text[$i];
      $line = '';

      // Used to calculate the size of each tab without having to recount the entire length of the string.
      $length = 0;

      while (($position = mb_strpos($post, "\t")) !== false) {
        // Split string into two, stripping the tab in the process.
        $pre  = $position ? mb_substr($post, 0, $position) : '';
        $post = mb_substr($post, $position + 1);

        $length += mb_strlen($pre);
        $size = $tab - ($length%$tab);
        $length += $size;

        $line .= $pre.str_repeat(' ', $size);
      }

      $text[$i] = $line.$post;
    }

    return implode("\n", $text);
  }
}
