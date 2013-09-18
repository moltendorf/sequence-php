<?php

namespace blink {

	use blink\functions as f;

	trait broadcaster {

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
		 * @param root $root
		 * @param string $binding
		 */
		public function __construct(root $root, $binding = '') {
			$this->bind($root, $binding);
		}

		/**
		 *
		 */
		protected function getBinding() {
			return str_replace('\\', '/', substr(get_class($this), strlen(__NAMESPACE__) + 1));
		}

		/**
		 *
		 * @param root $root
		 * @param string $binding
		 */
		final protected function bind(root $root, $binding = '') {
			$this->root = $root;
			$this->binding = $binding . $this->getBinding();

			$this->listeners = &$this->root->hook->register($this, $this->binding);
		}

		/**
		 *
		 * @param string $message
		 * @param mixed $data,...
		 */
		final protected function broadcast($message) {
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
