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

  /**
   * @param $data
   *
   * @return string
   */
  function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
  }

  /**
   * @param $data
   *
   * @return string
   */
  function base64url_decode($data) {
    return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data)%4, '=', STR_PAD_RIGHT));
  }

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

  function starts_with($haystack, $needle) {
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
  }

  function ends_with($haystack, $needle) {
    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
  }

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

  /**
   * Format all text for display in HTML.
   *
   * @param string $text
   *
   * @return string
   */
  function text_format($text) {
    return htmlspecialchars($text, ENT_COMPAT | ENT_DISALLOWED | ENT_HTML5);
  }

  /**
   * Convert a stack trace to a string.
   *
   * @param array $trace
   *
   * @return string
   */
  function trace_text($trace) {
    $objects = [];

    $argument = function ($variable) use (&$argument, &$objects) {
      switch (strtolower(gettype($variable))) {
      case 'boolean':
        $variable = $variable ? 'true' : 'false';
        break;

      case 'string':
        $variable = "'$variable'";
        break;

      case 'array':
        $values = [];

        foreach ($variable as $value) {
          $values[] = $argument($value);
        }

        $variable = '['.implode(', ', $values).']';
        break;

      case 'object':
        $objects[] = $variable;
        $variable  = 'Object #'.count($objects);
        break;

      case 'resource':
        $variable = (string)$variable;
        break;

      case 'null':
        $variable = 'null';
        break;

      case 'unknown type':
      default:
        $variable = 'unknown';
      }

      return $variable;
    };

    $lines = [];

    foreach ($trace as $number => $function) {
      if (isset($function['file'])) {
        $line = "#$number $function[file]($function[line]): ";
      } else {
        $line = "#$number: ";
      }

      if (isset($function['class'])) {
        $line .= "$function[class]$function[type]";
      }

      $line .= "$function[function](";

      $arguments = [];

      if (isset($function['args'])) {
        foreach ($function['args'] as $value) {
          $arguments[] = $argument($value);
        }
      }

      $line .= implode(', ', $arguments).')';

      $lines[] = $line;
    }

    $text = implode("\n", $lines);

    if (!empty($objects)) {
      ob_start();

      foreach ($objects as $number => $object) {
        echo "\n\nObject #".++$number.': ';

        var_dump($object);
      }

      return $text.ob_get_clean();
    } else {
    }

    return $text;
  }
}
