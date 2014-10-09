<?php

namespace sequence {

	trait broadcaster {

		use listener;

		/**
		 *
		 * @var string
		 */
		private $binding;

		/**
		 *
		 * @var array
		 */
		private $listeners;

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
		 *
		 * @throws
		 */
		protected function bind(root $root, $binding = '') {
			$this->root    = $root;
			$this->binding = $binding . $this->getBinding();

			if (debug && !isset(self::$messages)) {
				throw new \Exception('NO_MESSAGES: ' . $this->binding);
			}

			$this->listeners = & $root->hook->register($this, $this->binding);
		}

		/**
		 *
		 */
		protected function getBinding() {
			return str_replace('\\', '/', substr(get_class($this), strlen(__NAMESPACE__) + 1));
		}

		/**
		 *
		 * @param string $message
		 * @param mixed  $arguments
		 *
		 * @throws \Exception
		 */
		protected function broadcast($message, ...$arguments) {
			if (debug && isset(self::$messages) && !in_array($message, self::$messages)) {
				throw new \Exception('UNDEFINED_MESSAGE: ' . $this->binding . '::' . $message);
			}

			$root = $this->root;
			$hook = $root->hook;

			$hook->broadcast($message, $arguments);

			if (isset($this->listeners[$message])) {
				foreach ($this->listeners[$message] as $method) {
					$method(...$arguments);
				}
			}
		}
	}
}
