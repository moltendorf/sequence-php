<?php

namespace blink\root {

	use blink as b;

	class hook {

		/**
		 *
		 * @var b\root
		 */
		protected $root;

		/**
		 *
		 * @var array
		 */
		protected $bindings = [];

		/*
		 * End re-implementation of b\bind.
		 */
		/**
		 *
		 * @var array
		 */
		protected $listeners = [];

		/**
		 *
		 * @var array
		 */
		protected $global = [];

		/**
		 *
		 * @param b\root $root
		 * @param string $binding
		 */
		final public function __construct(b\root $root, $binding = '') {
			$this->root = $root;
		}

		/**
		 *
		 * @param b\broadcaster $object
		 * @param string        $binding
		 *
		 * @return &array
		 */
		public function &register($object, $binding) {
			if (isset($this->bindings[$binding])) {
				$this->bindings[$binding][] = $object;
			} else {
				$this->bindings[$binding] = [$object];

				$this->listeners[$binding] = [];
			}

			return $this->listeners[$binding];
		}

		/**
		 *
		 * @param string $message
		 * @param array  $data
		 */
		public function broadcast($message, $data) {
			if (isset($this->global[$message])) {
				foreach ($this->global[$message] as $method) {
					// @todo Convert this to PHP 5.6+ syntax.
					call_user_func_array($method, $data);
				}
			}
		}

		/**
		 *
		 * @param callable $method
		 * @param string   $message
		 * @param string   $binding
		 */
		public function listen(callable $method, $message, $binding = null) {
			if ($binding === null) {
				if (isset($this->global[$message])) {
					$this->global[$message][] = [$method];
				} else {
					$this->global[$message] = $method;
				}
			} else {
				if (isset($this->listeners[$binding])) {
					if (isset($this->listeners[$binding][$message])) {
						$this->listeners[$binding][$message][] = $method;
					} else {
						$this->listeners[$binding][$message] = [$method];
					}
				} else {
					$this->bindings[$binding] = [];

					$this->listeners[$binding] = [
						$message => $method
					];
				}
			}
		}
	}
}
