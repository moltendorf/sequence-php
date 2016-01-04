<?php

namespace sequence {

	use exception;

	trait broadcaster {

		use listener;

		/**
		 * The binding this class instance will send messages as.
		 *
		 * @var string
		 */
		private $binding;

		/**
		 * Class instances that are listening to messages from this binding.
		 *
		 * @var array
		 */
		private $listeners;

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
		 * Automatically registers this class instance to broadcast to this binding.
		 *
		 * @param root   $root
		 * @param string $binding
		 */
		protected function bind(root $root, $binding = '') {
			$this->root      = $root;
			$this->binding   = $binding.$this->getBinding();
			$this->listeners = &$root->hook->register($this, $this->binding);
		}

		/**
		 * Generate a binding name for this class instance.
		 * Bindings should not overlap unless there is no overlapping messages.
		 *
		 * @return string
		 */
		protected function getBinding() {
			return str_replace('\\', '/', substr(get_class($this), strlen(__NAMESPACE__) + 1));
		}

		/**
		 * Broadcast a message to this binding.
		 * Additional arguments will be passed to the listeners in order.
		 *
		 * @param string $message
		 * @param mixed  $arguments
		 *
		 * @throws exception
		 */
		protected function broadcast($message, ...$arguments) {
			$root = $this->root;
			$hook = $root->hook;

			if ($length = strpos($message, ':')) {
				$base = substr($message, 0, $length);

				/** @noinspection PhpUndefinedClassConstantInspection */
				if (debug && !in_array($base, self::messages)) {
					throw new exception("UNDEFINED_MESSAGE: $this->binding::$message");
				}

				$hook->broadcast($base, $message, $arguments);

				if (isset($this->listeners[$base])) {
					foreach ($this->listeners[$base] as $priority) {
						foreach ($priority as $method) {
							$method($message, ...$arguments);
						}
					}
				}
			} else {
				/** @noinspection PhpUndefinedClassConstantInspection */
				if (debug && !in_array($message, self::messages)) {
					throw new exception("UNDEFINED_MESSAGE: $this->binding::$message");
				}

				$hook->broadcast(null, $message, $arguments);
			}

			if (isset($this->listeners[$message])) {
				foreach ($this->listeners[$message] as $priority) {
					foreach ($priority as $method) {
						$method($message, ...$arguments);
					}
				}
			}
		}
	}
}
