<?php

namespace sequence\root {

	use sequence as b;

	class database extends \PDO {
		/**
		 *
		 * @var string
		 */
		protected $prefix = '';

		/**
		 *
		 * Do nothing.
		 */
		public function __construct() {

		}

		/**
		 *
		 * @param array $settings
		 *
		 * @throws
		 */
		public function connect($settings) {
			if (!isset($settings['username'])) {
				throw new \Exception('NO_DATABASE_USERNAME');
			}

			if (!isset($settings['password'])) {
				throw new \Exception('NO_DATABASE_PASSWORD');
			}

			if (isset($settings['prefix'])) {
				$this->prefix = (string) $settings['prefix'];
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
					} else {
						if (isset($settings['hostname'])) {
							$dsn[] = 'host=' . $settings['hostname'];

							if (isset($settings['port'])) {
								$dsn[] = 'port=' . $settings['port'];
							}
						}
					}

					break;
			}

			$dsn = $type . ':' . implode(';', $dsn);

			$username = (string) $settings['username'];
			$password = (string) $settings['password'];

			parent::__construct($dsn, $username, $password, [
				self::ATTR_PERSISTENT => true
			]);
		}

		public function prepare($statement, $options = null) {
			if ($options) {
				$result = parent::prepare($statement, $options);
			} else {
				$result = parent::prepare($statement);
			}

			$result->setFetchMode(self::FETCH_NUM);

			return $result;
		}

		public function table($table) {
			return $this->prefix . $table;
		}
	}
}
