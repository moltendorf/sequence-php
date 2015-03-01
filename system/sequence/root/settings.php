<?php

namespace sequence\root {

	use sequence as b;

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
		 * @param b\root $root
		 * @param string $binding
		 */
		public function __construct(b\root $root, $binding = '') {
			$this->bind($root, $binding);

			$application = $root->application;

			foreach ($application->settings as $key => $value) {
				$this->container[$key] = $value;
			}

			try {
				if ($root->database) {
					$database = $root->database;
					$prefix   = $database->prefix();

					$statement = $database->prepare("
						select setting_key, setting_value
						from {$prefix}settings
					");

					$statement->execute();

					foreach ($statement->fetchAll() as $row) {
						$this->container[$row[0]] = $row[1];
					}

					$statement->closeCursor();

					unset($row);

					$this->listen([$this, 'pushAll'], 'closing', 'root/database');
				}
			} catch (\Exception $exception) {
				$application->errors[] = $exception;
			}
		}

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
			$value  = (string) $value;

			if (isset($this->original[$offset])) {
				if ($this->original[$offset] == $value) {
					unset($this->original[$offset]);
				}
			} else {
				if (isset($this->container[$offset])) {
					if ($this->container[$offset] != $value) {
						$this->original[$offset] = $this->container[$offset];
					}
				} else {
					$this->original[$offset] = false;
				}
			}

			$this->container[$offset] = $value;
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
					unset($this->original[$offset]);
				}
			} else {
				$this->original[$offset] = $this->container[$offset];
			}

			if (isset($this->container[$offset])) {
				unset($this->container[$offset]);
			}
		}

		/*
		 * End implementation of \ArrayAccess.
		 */

		/**
		 *
		 * @param string $offset
		 */
		public function offsetPush($offset) {
			$offset = (string) $offset;

			if (isset($this->original[$offset])) {
				$root     = $this->root;
				$database = $root->database;
				$prefix   = $database->prefix();

				if ($this->original[$offset] !== false) {
					if (isset($this->container[$offset])) {
						$statement = $database->prepare("
							update {$prefix}settings
							set setting_value = :value
							where setting_key = :key
						");

						$statement->execute([
							':key'   => $offset,
							':value' => $this->container[$offset]
						]);
					} else {
						$statement = $database->prepare("
							delete from {$prefix}settings
							where setting_key = :key
						");

						$statement->execute([
							':key' => $offset
						]);
					}
				} else {
					$statement = $database->prepare("
							insert into {$prefix}settings
									(setting_key,	setting_value)
							values	(:value,		:key)
						");

					$statement->execute([
						':key'   => $offset,
						':value' => $this->container[$offset]
					]);
				}

				unset($this->original[$offset]);
			}
		}

		/**
		 *
		 * @todo Improve bulk update code.
		 */
		public function pushAll() {
			foreach (array_keys($this->original) as $offset) {
				$this->offsetPush($offset);
			}
		}
	}
}
