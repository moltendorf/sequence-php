<?php

namespace blink {

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
		 */
		protected function bind(root $root, $binding = '') {
			$this->root    = $root;
			$this->binding = $binding . $this->getBinding();

			$this->listeners = & $this->root->hook->register($this, $this->binding);
		}

		/**
		 *
		 */
		protected function getBinding() {
			return str_replace('\\', '/', substr(get_class($this), strlen(__NAMESPACE__) + 1));
		}

		/**
		 *
		 * @todo Convert this to PHP 5.6+ syntax.
		 *
		 * @param string $message
		 */
		protected function broadcast($message) {
			$data = array_slice(func_get_args(), 1);

			$this->root->hook->broadcast($message, $data);

			if (isset($this->listeners[$message])) {
				foreach ($this->listeners[$message] as $method) {
					call_user_func_array($method, $data);
				}
			}
		}
	}
}
