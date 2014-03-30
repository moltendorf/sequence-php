<?php

namespace blink\root\database\result {

	abstract class common {

		/**
		 *
		 * @var array|null
		 */
		public $column = null;

		/**
		 *
		 * @var string
		 */
		public $query;

		/**
		 *
		 * @var \mysqli_result
		 */
		protected $_instance;

		/**
		 *
		 * @param \mysqli_result $instance
		 * @param string $query
		 * @param array|null $columns
		 */
		public function __construct($instance, $query, $columns = null) {
			$this->_instance = $instance;

			$this->query = $query;

			if (is_array($columns)) {
				$this->column = array_flip($columns);
			}
		}

		/**
		 *
		 * @return array|null
		 */
		public function fetch_row() {
			return $this->_instance->fetch();
		}

		/**
		 *
		 */
		public function free() {
			$this->_instance->free();
		}

	}

}
