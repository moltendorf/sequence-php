<?php

namespace blink\root {

	use blink as b;

	class module implements \ArrayAccess, \Iterator {

		use b\listener;

		/**
		 *
		 * @var array
		 */
		private $container = [];

		/**
		 *
		 * @var int
		 */
		private $position = 0;

		/**
		 *
		 * @param b\root $root
		 * @param string $binding
		 */
		public function __construct(b\root $root, $binding = '') {
			$this->bind($root, $binding);
		}

		public function load() {
			$root     = $this->root;
			$database = $root->database;

			$statement = $database->prepare("
				select module_id, module_name
				from {$database->table('modules')}
				where module_is_enabled = 1
			");

			$statement->execute();

			foreach ($statement->fetchAll() as $row) {
				$class = 'blink\\module\\' . $row[1] . '\\load';

				spl_autoload($class);
				$this->container[$row[0]] = $this->container[$row[1]] = new $class($root);
			}
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

			if (isset($this->container[$offset])) {
				return $this->container[$offset];
			} else {
				if (b\debug) {
					return '{LANG: ' . $offset . '}';
				} else {
					return $offset;
				}
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

		/*
		 * Implementation of \Iterator.
		 */

		/**
		 *
		 * @return b\module
		 */
		function current() {
			return $this->container[$this->position];
		}

		/**
		 *
		 * @return int
		 */
		function key() {
			return $this->position;
		}

		/**
		 *
		 */
		function next() {
			++$this->position;
		}

		/**
		 *
		 */
		function rewind() {
			$this->position = 0;
		}

		/**
		 *
		 * @return bool
		 */
		function valid() {
			return isset($this->container[$this->position]);
		}
		/*
		 * End implementation of \Iterator.
		 */
	}
}
