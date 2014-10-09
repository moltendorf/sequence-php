<?php

namespace sequence\functions {

	/**
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

	/**
	 * @param string $text
	 * @param int    $tab
	 *
	 * @return string
	 */
	function text_normalize($text, $tab = 4) {
		echo $text;

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
				$size = $tab - ($length % $tab);
				$length += $size;

				$line .= $pre . str_repeat(' ', $size);
			}

			$text[$i] = $line . $post;
		}

		return implode("\n", $text);
	}

	/**
	 * @param string $text
	 *
	 * @return string
	 */
	function text_format($text) {
		return nl2br(str_replace(' ', '&nbsp;', htmlspecialchars($text, ENT_COMPAT | ENT_DISALLOWED | ENT_HTML5)));
	}
}
