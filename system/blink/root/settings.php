<?php

namespace blink\root {
	use blink as b;
	use blink\functions as f;

	class settings implements \ArrayAccess {

		use b\listener;

		/**
		 *
		 * @var array
		 */
		private $container = [];

		/**
		 *
		 * @var array
		 */
		private $original = [];

		/**
		 *
		 * @var b\root
		 */
		protected $root;

		/*
		 * Implementation of \ArrayAccess.
		 */

		/**
		 *
		 * @param string $offset
		 * @param string $value
		 */
		public function offsetSet($offset, $value) {
			$offset = (string) $offset;
			$value = (string) $value;

			if (isset($this->original[$offset])) {
				if ($this->original[$offset] == $value) {
					unset ($this->original[$offset]);
				}
			} else if (isset($this->container[$offset])){
				if ($this->container[$offset] != $value) {
					$this->original[$offset] = $this->container[$offset];
				}
			} else {
				$this->original[$offset] = false;
			}

			$this->container[$offset] = $value;
		}

		/**
		 *
		 * @param string $offset
		 * @return boolean
		 */
		public function offsetExists($offset) {
			return isset($this->container[(string) $offset]);
		}

		/**
		 *
		 * @param string $offset
		 * @return string
		 */
		public function offsetGet($offset) {
			$offset = (string) $offset;

			return isset($this->container[$offset]) ? $this->container[$offset] : null;
		}

		/**
		 *
		 * @param string $offset
		 */
		public function offsetUnset($offset) {
			$offset = (string) $offset;

			if (isset($this->original[$offset])) {
				if ($this->original[$offset] === false) {
					unset ($this->original[$offset]);
				}
			} else {
				$this->original[$offset] = $this->container[$offset];
			}

			if (isset($this->container[$offset])) {
				unset ($this->container[$offset]);
			}
		}

		/*
		 * End implementation of \ArrayAccess.
		 */

		/**
		 * @param b\root $root
		 */
		public function __construct(b\root $root) {
			$this->root = $root;

			$result = $root->database->select([
				'select' => ['key', 'value'],
				'from' => 'settings'
			]);

			$column = $result->column;

			while ($row = $result->fetch_row()) {
				$this->container[$row[$column['key']]] = $row[$column['value']];
			}

			unset($row);

			$this->listen([$this, 'pushAll'], 'sent', 'root/application');
		}

		/**
		 *
		 * @param string $offset
		 */
		public function offsetPush($offset) {
			$offset = (string) $offset;

			if (isset($this->original[$offset])) {
				$database = $this->root->database;

				if ($this->original[$offset] !== false) {
					if (isset($this->container[$offset])) {
						$database->update([
							'table' => 'settings',

							'set' => [
								'value' => $this->container[$offset]
							],

							'where' => [
								'key' => $offset
							]
						]);
					} else {
						$database->delete([
							'from' => 'settings',

							'where' => [
								'key' => $offset
							]
						]);
					}
				} else {
					$database->insert([
						'into' => 'settings',

						'columns' => ['key', 'value'],

						'values' => [
							[$offset, $this->container[$offset]]
						]
					]);
				}

				unset ($this->original[$offset]);
			}
		}

		/**
		 *
		 */
		public function pushAll() {
			$database = $this->root->database;

			$inserts = [];
			$deletes = [];

			foreach ($this->original as $offset => $original) {
				if ($original !== false) {
					if (isset($this->container[$offset])) {
						// I don't have much of an idea on how to do multiple updates in a single query, so we'll just
						// do multiple queries. @todo: Improve bulk update code.
						$database->update([
							'table' => 'settings',

							'set' => [
								'value' => $this->container[$offset]
							],

							'where' => [
								'key' => $offset
							]
						]);
					} else {
						$deletes[] = $offset;
					}
				} else {
					$inserts[] = [$offset, $this->container[$offset]];
				}
			}

			if (count($inserts)) {
				// Bulk insert.
				$database->insert([
					'into' => 'settings',

					'columns' => ['key', 'value'],

					'values' => $inserts
				]);
			}

			if (count($deletes)) {
				// Bulk delete.
				$database->delete([
					'from' => 'settings',

					'where' => [
						'key' => $deletes
					]
				]);
			}
		}
	}
}
