<?php

namespace blink\root\database {
	use blink\functions as f;

	class mysqli extends common {

		/*
		 * Implementation of common.
		 */

		/**
		 *
		 * @param array $settings
		 */
		public function connect($settings) {
			if (!isset($settings['username'])) {
				throw new \Exception('NO_CONNECTION_USERNAME');
			}

			if (!isset($settings['password'])) {
				throw new \Exception('NO_CONNECTION_PASSWORD');
			}

			if (!isset($settings['database'])) {
				throw new \Exception('NO_CONNECTION_DATABASE');
			}

			$hostname = isset($settings['hostname']) ? $settings['hostname'] : null;

			if ($hostname !== null) {
				$hostname = (string) $hostname;
			}

			$username = (string) $settings['username'];
			$password = (string) $settings['password'];

			$database = (string) $settings['database'];

			if (!isset($settings['port'])) {
				$this->_instance = new \mysqli($hostname, $username, $password, $database);
			} else {
				// The port parameter must be an integer so we have a special case for when it's defined.
				$this->_instance = new \mysqli($hostname, $username, $password, $database, (integer) $settings['port']);
			}
		}

		/**
		 *
		 */
		public function close() {
			$this->_instance->close();
		}

		/**
		 *
		 * @param string $query
		 * @param array|null $columns
		 * @return result\mysqli|boolean
		 */
		public function query($query, $columns = null) {
			$result = $this->_instance->query($query);

			if ($result instanceof \mysqli_result) {
				return new result\mysqli($result, $query, $columns);
			}

			return $result;
		}

		/**
		 *
		 * @param string $table
		 * @return string
		 */
		public function escape_table($table) {
			return '`'.str_replace('`', '``', $table).'`';
		}

		/**
		 *
		 * @param string $column
		 * @return string
		 */
		public function escape_column($column) {
			return '`'.str_replace('`', '``', $column).'`';
		}

		/**
		 *
		 * @param string $value
		 * @return string
		 */
		public function escape_value($value) {
			return '\''.$this->_instance->real_escape_string($value).'\'';
		}

		/*
		 * End implementation of common.
		 */
	}
}
