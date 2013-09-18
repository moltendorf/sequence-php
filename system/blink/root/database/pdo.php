<?php

namespace blink\root\database {

	use blink\functions as f;

	class pdo extends common {

		/*
		 * Implementation of common.
		 */

		/**
		 *
		 * @param array $settings
		 */
		public function connect($settings) {
			if (!isset($settings['username'])) {
				throw new \Exception('NO_DATABASE_USERNAME');
			}

			if (!isset($settings['password'])) {
				throw new \Exception('NO_DATABASE_PASSWORD');
			}

			$type = (string) $settings['type'];

			$dsn = [];

			switch ($type) {
				case 'mysql':
					if (!isset($settings['use'])) {
						throw new \Exception('NO_DATABASE_USE');
					}

					$dsn[] = 'dbname=' . $settings['use'];

					if (isset($settings['socket'])) {
						$dsn[] = 'unix_socket=' . $settings['socket'];
					} else if (isset($settings['hostname'])) {
						$dsn[] = 'host=' . $settings['hostname'];

						if (isset($settings['port'])) {
							$dsn[] = 'port=' . $settings['port'];
						}
					}

					break;
			}

			$dsn = $type . ':' . implode(';', $dsn);

			$username = (string) $settings['username'];
			$password = (string) $settings['password'];


			$this->_instance = new \PDO($dsn, $username, $password);
		}

		/**
		 *
		 */
		public function close() {
			$this->_instance = null;
		}

		/**
		 *
		 * @param string $query
		 * @param array|null $columns
		 * @return result\mysqli|boolean
		 */
		public function query($query, $columns = null) {
			$result = $this->_instance->query($query);

			if ($result instanceof \PDOStatement) {
				return new result\pdo($result, $query, $columns);
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
			return '\'' . $this->_instance->quote($value) . '\'';
		}

		/*
		 * End implementation of common.
		 */
	}
}
