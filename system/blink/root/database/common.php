<?php

namespace blink\root\database {

	use blink as b;

	abstract class common {

		use b\broadcaster;

		/**
		 *
		 * @var string
		 */
		protected $prefix = '';

		/**
		 *
		 * @var \PDO
		 */
		protected $_instance;

		/**
		 *
		 * @var b\root
		 */
		protected $root;

		/**
		 *
		 * @param b\root $root
		 * @param array  $settings
		 * @param string $binding
		 */
		final public function __construct(b\root $root, array $settings, $binding = '') {
			$this->bind($root, $binding);

			$this->broadcast('connecting');

			if (isset($settings['prefix'])) {
				$this->prefix = $settings['prefix'];
			}

			$this->connect($settings);

			$this->broadcast('connected');
		}

		/**
		 *
		 * @param array $settings
		 */
		abstract protected function connect($settings);

		/**
		 *
		 */
		final public function close() {
			$this->broadcast('closing');

			$this->destroy();

			$this->broadcast('closed');
		}

		/**
		 *
		 */
		abstract protected function destroy();

		/**
		 *
		 * @param array $input
		 *
		 * @return result\common|boolean
		 */
		public function select($input) {
			$columns = null;

			$query = ['SELECT'];

			if (isset($input['select'])) {
				$columns = [];
				$select  = [];

				foreach ($input['select'] as $column) {
					if (is_string($column)) {
						$columns[] = $column;
						$select[]  = $this->escape_column($column);
					} else {
						if (count($column) == 1) {
							$columns[] = $column[0];
							$select[]  = $this->escape_column($column[0]);
						} else {
							$columns[] = $column[0];
							$select[]  = $this->escape_column($column[0]) . ' AS ' . $this->escape_column($column[1]);
						}
					}
				}

				$query[] = implode(', ', $select);
			} else {
				$query[] = '*';
			}

			$query[] = 'FROM ' . $this->escape_table($this->prefix . $input['from']);

			return $this->query(implode(' ', $query) . ';', $columns);
		}

		/**
		 *
		 * @param string $column
		 *
		 * @return string
		 */
		abstract public function escape_column($column);

		/*
		 * Implementation of b\bind.
		 */

		/**
		 *
		 * @param string $table
		 *
		 * @return string
		 */
		abstract public function escape_table($table);

		/**
		 *
		 * @param string     $query
		 * @param array|null $columns
		 *
		 * @return result\common|boolean
		 */
		abstract public function query($query, $columns = null);

		/*
		 * End implementation of b\bind.
		 */

		/**
		 *
		 * @param array $input
		 *
		 * @return result\common|boolean
		 */
		public function insert($input) {
			$query = ['INSERT INTO ' . $this->escape_table($this->prefix . $input['into'])];

			if (isset($input['columns'])) {
				$columns = [];

				foreach ($input['columns'] as $column) {
					$columns[] = $this->escape_column($column);
				}

				$query[] = '(' . implode(', ', $columns) . ')';
			}

			$query[] = 'VALUES';

			$rows = [];

			foreach ($input['values'] as $row) {
				$values = [];

				foreach ($row as $value) {
					$values[] = $this->escape_value($value);
				}

				$rows[] = '(' . implode(', ', $values) . ')';
			}

			$query[] = implode(', ', $rows);

			return $this->query(implode(' ', $query) . ';');
		}

		/**
		 *
		 * @param string $value
		 *
		 * @return string
		 */
		abstract public function escape_value($value);

		/**
		 *
		 * @param array $input
		 *
		 * @return result\common|boolean
		 */
		public function update($input) {
			$query = ['UPDATE ' . $this->escape_table($this->prefix . $input['table']) . ' SET'];

			$set = [];

			foreach ($input['set'] as $column => $value) {
				$set[] = $this->escape_column($column) . ' = ' . $this->escape_value($value);
			}

			$query[] = implode(', ', $set) . ' WHERE';

			$where = [];

			foreach ($input['where'] as $column => $value) {
				if (is_array($value)) {
					$list = [];

					foreach ($value as $item) {
						$list[] = $this->escape_value($item);
					}

					$where[] = $this->escape_column($column) . ' IN (' . implode(', ', $list) . ')';
				} else {
					$where[] = $this->escape_column($column) . ' = ' . $this->escape_value($value);
				}
			}

			$query[] = implode(' AND ', $where);

			return $this->query(implode(' ', $query) . ';');
		}

		/**
		 *
		 * @param array $input
		 *
		 * @return result\common|boolean
		 */
		public function delete($input) {
			$query = ['DELETE FROM ' . $this->escape_table($this->prefix . $input['from']) . ' WHERE'];

			$where = [];

			foreach ($input['where'] as $column => $value) {
				if (is_array($value)) {
					$list = [];

					foreach ($value as $item) {
						$list[] = $this->escape_value($item);
					}

					$where[] = $this->escape_column($column) . ' IN (' . implode(', ', $list) . ')';
				} else {
					$where[] = $this->escape_column($column) . ' = ' . $this->escape_value($value);
				}
			}

			$query[] = implode(' AND ', $where);

			return $this->query(implode(' ', $query) . ';');
		}

		/**
		 * Override automatic binding.
		 */
		final protected function getBinding() {
			return 'root/database';
		}
	}
}
