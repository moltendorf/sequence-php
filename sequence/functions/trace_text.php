<?php

namespace sequence\functions {

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
