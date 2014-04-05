<?php

namespace blink\functions {

	/**
	 *
	 * @param mixed $variable
	 */
	function dump($variable) {
		$arguments = func_get_args();

		foreach ($arguments as $argument) {
			echo '<pre>';
			var_dump($argument);
			echo '</pre>';
		}
	}

	/**
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
	 *
	 * @param string $filename
	 *
	 * @return mixed
	 */
	function file_get_json($filename) {
		$json = \file_get_contents($filename);

		if ($json) {
			return json_decode($json);
		}

		return false;
	}
}
