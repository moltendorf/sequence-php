<?php

namespace blink\root {

	use blink as b;

	class language implements \ArrayAccess {

		use b\listener;

		/**
		 *
		 * @var b\root
		 */
		protected $root;

		/**
		 *
		 * @var array
		 */
		private $container = [];

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

		/*
		 * Implementation of \ArrayAccess.
		 */

		/**
		 * @param b\root $root
		 */
		public function __construct(b\root $root) {
			$this->root = $root;

			if (isset($root->settings['language'])) {
				$this->tag = $root->settings['language'];
			}

			$lang = [];

			$file = $root->path->language . '/' . $this->tag . '.php';

			if (file_exists($file)) {
				$include = require $file;

				if (is_array($include)) {
					$lang[] = $include;
				}

				unset($include);
			}

			if (isset($root->settings['template'])) {
				$file = $root->path->template . '/' . $root->settings['template'] . '/language/' . $this->tag . '.php';

				if (file_exists($file)) {
					$include = require $file;

					if (is_array($include)) {
						$lang[] = $include;
					}

					unset($include);
				}
			}

			if ($root->settings['template_custom']) {
				$file = $root->path->template . '/' . $root->settings['template'] . '/custom/language/' . $this->tag . '.php';

				if (file_exists($file)) {
					$include = require $file;

					if (is_array($include)) {
						$lang[] = $include;
					}

					unset($include);
				}
			}

			/**
			 *
			 * @todo Convert this to PHP 5.6+ syntax.
			 */
			$this->container = call_user_func_array('array_merge', $lang);
		}

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
		 */
		public function offsetSet($offset, $value) {
			throw new Exception('METHOD_NOT_SUPPORTED');
		}

		/*
		 * End implementation of \ArrayAccess.
		 */

		/**
		 *
		 * @param string $offset
		 */
		public function offsetUnset($offset) {
			throw new Exception('METHOD_NOT_SUPPORTED');
		}
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
		 * @param language $container
		 * @param string   $offset
		 */
		public function __construct(&$container, $offset) {
			$this->container = & $container;
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
