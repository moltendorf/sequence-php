<?php

namespace sequence\root {

	use sequence as s;

	use arrayaccess, exception;

	class language implements arrayaccess {

		use s\broadcaster;

		/**
		 * List of messages that this class can send.
		 *
		 * @var array
		 */
		const messages = [];

		/**
		 * Language string key to value pairs.
		 *
		 * @var array
		 */
		private $container = null;

		/**
		 * Language code.
		 * Used for loading language files.
		 *
		 * @var string
		 */
		private $tag = 'en-US';

		/**
		 * Basic constructor.
		 *
		 * @param s\root $root
		 * @param string $binding
		 */
		public function __construct(s\root $root, $binding = '') {
			$this->bind($root, $binding);
		}

		/**
		 * Bind all classes in root to application identity.
		 *
		 * @return string
		 */
		protected function getBinding() {
			return 'application';
		}

		/**
		 * Load language files and merge key to value pairs.
		 *
		 * @todo Add caching to this method.
		 */
		public function load() {
			$root = $this->root;

			$module   = $root->module;
			$settings = $root->settings;

			if (isset($settings['language'])) {
				$this->tag = $settings['language'];
			}

			$segment = "language/$this->tag";

			if (isset($settings['template_default'])) {
				$default  = false;
				$template = $settings['template_default'];
			} else {
				$default  = true;
				$template = 'default';
			}

			$customizations = (boolean)$settings['template_customizations'];
			$modules        = array_keys($module->getLoaded());

			$load = function ($base = '') use ($segment) {
				$root = $this->root;
				$path = $root->path;

				if (is_dir($directory = "$path->home$base/$segment")) {
					foreach (glob("$directory/*.php") as $file) {
						$this->container += require $file;
					}
				}

				if (is_dir($directory = "$path->system$base/$segment")) {
					foreach (glob("$directory/*.php") as $file) {
						$this->container += require $file;
					}
				}
			};

			$this->container = [];

			/*
			 * Load current template language files.
			 */

			$load("/template/$template");

			if (!$default) {
				$load('/template/default');
			}

			if ($customizations) {
				$container = $this->container;

				$load("/template/$template/custom");

				$this->container = $container + $this->container;
			}

			/*
			 * Load module language files.
			 */

			foreach ($modules as $name) {
				$load("/sequence/module/$name");
			}

			/*
			 * Load main language file.
			 */

			// Load main language file in home directory.
			$load();
		}

		/**
		 * Get all variables in json format.
		 *
		 * @return string
		 */
		public function json() {
			return json_encode($this->container);
		}

		/**
		 * Get the language string. Alias of offsetGet.
		 *
		 * @param string  $offset
		 * @param boolean $safety Use this when code must work with no language file.
		 *
		 * @return string
		 */
		public function __invoke($offset, $safety = false) {
			return $this->offsetGet($offset, $safety);
		}

		/*
		 * Implementation of \ArrayAccess.
		 */

		/**
		 * Check if the language string exists.
		 *
		 * @param string $offset
		 *
		 * @return boolean
		 */
		public function offsetExists($offset) {
			return isset($this->container[(string)$offset]);
		}

		/**
		 * Get the language string.
		 * Returns a language container that will convert to a string on demand if this language class instance is not
		 * ready. It is not recommended to rely on this functionality as it may change in the future. If you are
		 * attempting to store any language strings in the database it is highly recommended to store the key, not the
		 * value.
		 *
		 * @param string  $offset
		 * @param boolean $safety Use this when code must work with no language file.
		 *
		 * @return string
		 *
		 * @throws exception
		 */
		public function offsetGet($offset, $safety = false) {
			$offset = (string)$offset;

			if (s\ship) {
				if (isset($this->container[$offset])) {
					return $this->container[$offset];
				} else {
					if (isset($this->container)) {
						return $this->container[$offset] = $offset;
					} else {
						return $offset;
					}
				}
			} else {
				if (isset($this->container) || $safety) {
					if (isset($this->container[$offset])) {
						return $this->container[$offset];
					} else {
						if (s\debug\language && !$safety) {
							throw new exception('LANGUAGE_NOT_EXIST');
						} else {
							return $this->container[$offset] = "{LANG: $offset}";
						}
					}
				} else {
					throw new exception('LANGUAGE_NOT_LOADED');
				}
			}
		}

		/**
		 * Overriding language strings at runtime is not supported.
		 *
		 * @param string $offset
		 * @param string $value
		 *
		 * @throws exception
		 */
		public function offsetSet($offset, $value) {
			throw new exception('METHOD_NOT_SUPPORTED');
		}

		/**
		 * Overriding language strings at runtime is not supported.
		 *
		 * @param string $offset
		 *
		 * @throws exception
		 */
		public function offsetUnset($offset) {
			throw new exception('METHOD_NOT_SUPPORTED');
		}
		/*
		 * End implementation of \ArrayAccess.
		 */
	}
}
