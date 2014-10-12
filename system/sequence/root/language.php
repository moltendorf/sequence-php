<?php

namespace sequence\root {

	use sequence as b;

	class language implements \ArrayAccess {

		use b\listener;

		/**
		 *
		 * @var array
		 */
		private $container = null;

		/**
		 *
		 * @var array
		 */
		private $instance = [];

		/**
		 *
		 * @var string
		 */
		private $tag = 'en-US';

		/**
		 *
		 * @param b\root $root
		 * @param string $binding
		 */
		public function __construct(b\root $root, $binding = '') {
			$this->bind($root, $binding);
			$this->load();
		}

		public function load() {
			$root = $this->root;

			$path     = $root->path;
			$settings = $root->settings;

			if (isset($settings['language'])) {
				$this->tag = $settings['language'];
			}

			$lang = [];

			$load = function ($file) use (& $lang) {
				$file .=  '.php';

				if (file_exists($file)) {
					$include = require $file;

					if (is_array($include)) {
						$lang[] = $include;
					}

					unset($file, $include);
				}
			};

			// Base language file.
			$load($path->language . '/' . $this->tag);

			// Template language files.
			if (isset($settings['template'])) {
				$load($path->template . '/' . $settings['template'] . '/language/' . $this->tag);
			}

			if ($settings['template_custom']) {
				$load($path->template . '/' . $settings['template'] . '/custom/language/' . $this->tag);
			}

			$this->container = array_merge(...$lang);
		}

		/*
		 * Implementation of \ArrayAccess.
		 */

		/**
		 *
		 * @param string $offset
		 *
		 * @return boolean
		 */
		public function offsetExists($offset) {
			return isset($this->container[(string) $offset]);
		}

		/**
		 *
		 * @param string $offset
		 *
		 * @return string
		 */
		public function offsetGet($offset) {
			$offset = (string) $offset;

			if (isset($this->instance[$offset])) {
				return $this->instance[$offset];
			} else {
				return $this->instance[$offset] = new languageString($this->container, $offset);
			}
		}

		/**
		 *
		 * @param string $offset
		 * @param string $value
		 *
		 * @throws
		 */
		public function offsetSet($offset, $value) {
			throw new \Exception('METHOD_NOT_SUPPORTED');
		}

		/**
		 *
		 * @param string $offset
		 *
		 * @throws
		 */
		public function offsetUnset($offset) {
			throw new \Exception('METHOD_NOT_SUPPORTED');
		}
		/*
		 * End implementation of \ArrayAccess.
		 */
	}

	class languageString {

		/**
		 *
		 * @var array
		 */
		private $container;

		/**
		 *
		 * @var string
		 */
		private $offset;

		/**
		 *
		 * @var string
		 */
		private $output;

		/**
		 *
		 * @param array  $container
		 * @param string $offset
		 */
		public function __construct(& $container, $offset) {
			$this->container = &$container;
			$this->offset    = $offset;
		}

		/**
		 *
		 */
		public function __toString() {
			if ($this->output === null) {
				if (isset($this->container[$this->offset])) {
					$this->output = $this->container[$this->offset];
				} else {
					if (b\debug) {
						$this->output = '{LANG: ' . $this->offset . '}';
					} else {
						$this->output = $this->offset;
					}
				}
			}

			return $this->output;
		}
	}
}
