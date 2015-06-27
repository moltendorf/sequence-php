<?php

namespace sequence\root {

	use sequence as s;

	use exception;

	class hook {

		/**
		 * The root of the application.
		 * All major class instances are accessible from this class instance.
		 *
		 * @var s\root
		 */
		protected $root;

		/**
		 * Class instances that are broadcasting to a binding.
		 *
		 * @var array
		 */
		protected $bindings = [];

		/*
		 * End re-implementation of b\bind.
		 */

		/**
		 * Class instances that are listening to specific bindings.
		 *
		 * @var array
		 */
		protected $listeners = [];

		/**
		 * Class instances that are listening to messages from any binding.
		 *
		 * @var array
		 */
		protected $global = [];

		/**
		 *
		 * @param s\root $root
		 * @param string $binding
		 */
		final public function __construct(s\root $root, $binding = '') {
			$this->root = $root;
		}

		/**
		 * Register a class instance to broadcast on a binding.
		 *
		 * @param s\broadcaster $object
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
		 * Broadcast to all class instances listening to a message globally.
		 *
		 * @param string|null $base
		 * @param string      $message
		 * @param array       $arguments
		 */
		public function broadcast($base, $message, $arguments) {
			if (isset($base)) {
				if (isset($this->global[$base])) {
					foreach ($this->global[$base] as $method) {
						$method($message, ...$arguments);
					}
				}
			}

			if (isset($this->global[$message])) {
				foreach ($this->global[$message] as $method) {
					$method($message, ...$arguments);
				}
			}
		}

		/**
		 * Register a class to receive messages from a binding.
		 *
		 * @param callable $method
		 * @param string   $message
		 * @param string   $binding
		 *
		 * @throws exception
		 */
		public function listen(callable $method, $message, $binding = null) {
			if (isset($binding)) {
				if (isset($this->listeners[$binding])) {
					if (s\debug && count($this->bindings[$binding])) {
						$success = false;

						foreach ($this->bindings[$binding] as $object) {
							if (in_array($message, $object::messages)) {
								$success = true;

								break;
							}
						}

						if (!$success) {
							throw new exception("UNDEFINED_MESSAGE: $binding::$message");
						}
					}

					if (isset($this->listeners[$binding][$message])) {
						$this->listeners[$binding][$message][] = $method;
					} else {
						$this->listeners[$binding][$message] = [$method];
					}
				} else {
					$this->bindings[$binding] = [];

					$this->listeners[$binding] = [
						$message => [$method]
					];
				}
			} else {
				if (isset($this->global[$message])) {
					$this->global[$message][] = $method;
				} else {
					$this->global[$message] = [$method];
				}
			}
		}
	}
}
