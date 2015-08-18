<?php

namespace sequence {

	trait listener {

		/**
		 * The root of the application.
		 * All major class instances are accessible from this class instance.
		 *
		 * @var root
		 */
		protected $root;

		/**
		 * Stock constructor used when another one isn't implemented.
		 *
		 * @param root   $root
		 * @param string $binding
		 */
		public function __construct(root $root, $binding = '') {
			$this->bind($root, $binding);
		}

		/**
		 * Basic class instance setup.
		 *
		 * @param root   $root
		 * @param string $binding
		 */
		protected function bind(root $root, $binding = '') {
			$this->root = $root;
		}

		/**
		 * Register a class to receive messages from a binding or globally.
		 *
		 * @param callable    $method
		 * @param             $message
		 * @param null|string $binding
		 */
		protected function listen(callable $method, $message, $binding = null) {
			$root = $this->root;
			$hook = $root->hook;

			$hook->listen($method, $message, $binding);
		}
	}
}
