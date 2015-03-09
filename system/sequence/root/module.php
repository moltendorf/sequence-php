<?php

namespace sequence\root {

	use sequence as b;

	class module implements \ArrayAccess, \Iterator {

		use b\broadcaster;

		/**
		 *
		 * @var array
		 */
		public static $messages = ['ready'];

		/**
		 *
		 * @var array
		 */
		private $container = [];

		/**
		 *
		 * @var array
		 */
		private $enabled = [];

		/**
		 *
		 * @var array
		 */
		private $lookup = [];

		/**
		 *
		 * @var int
		 */
		private $position = 0;

		/**
		 *
		 * @var bool
		 */
		private $ready = false;

		/**
		 *
		 * @param b\root $root
		 * @param string $binding
		 */
		public function __construct(b\root $root, $binding = '') {
			$this->bind($root, $binding);
		}

		/**
		 *
		 * @throws \Exception
		 */
		public function load() {
			$root     = $this->root;
			$database = $root->database;
			$prefix   = $database->prefix();

			$statement = $database->prepare("
				select module_id, module_name
				from {$prefix}modules
				where module_is_enabled = 1
			");

			$statement->execute();

			foreach ($statement->fetchAll() as $row) {
				$class = 'sequence\\module\\' . $row[1] . '\\load';

				spl_autoload($class);
				$this->container[$row[0]] = $this->lookup[$row[1]] = new $class($root);

				$this->enabled[] = $row[1];
			}

			$this->ready = true;
			$this->broadcast('ready');
		}

		/**
		 *
		 * @return array
		 */
		public function enabled() {
			return $this->enabled;
		}

		/**
		 *
		 * @return bool
		 */
		public function ready() {
			return $this->ready;
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
			return isset($this->lookup[(string) $offset]);
		}

		/**
		 *
		 * @param string $offset
		 *
		 * @return b\module|null
		 */
		public function offsetGet($offset) {
			$offset = (string) $offset;

			if (isset($this->lookup[$offset])) {
				return $this->lookup[$offset];
			} else {
				return null;
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
