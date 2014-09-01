<?php

namespace sequence {

	trait listener {

		/**
		 *
		 * @var root
		 */
		protected $root;

		/**
		 *
		 * @param root   $root
		 * @param string $binding
		 */
		public function __construct(root $root, $binding = '') {
			$this->bind($root, $binding);
		}

		/**
		 *
		 * @param root   $root
		 * @param string $binding
		 */
		protected function bind(root $root, $binding = '') {
			$this->root = $root;
		}

		/**
		 *
		 * @param callable $method
		 * @param string   $message
		 * @param string   $binding
		 */
		protected function listen(callable $method, $message, $binding = null) {
			$root = $this->root;
			$hook = $root->hook;

			$hook->listen($method, $message, $binding);
		}
	}
}
