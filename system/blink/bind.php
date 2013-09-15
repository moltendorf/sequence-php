<?php

namespace blink {
	abstract class bind {

		/**
		 *
		 * @var root
		 */
		protected $root;

		/**
		 *
		 */
		abstract protected function construct();

		/**
		 *
		 */
		protected function getBinding() {
			return substr(get_class($this), strlen(__NAMESPACE__)+1);
		}

		/**
		 *
		 * @var string
		 */
		private $binding;

		/**
		 *
		 * @var array
		 */
		private $listeners = [];

		/**
		 *
		 * @param root $root
		 * @param string $binding
		 */
		final public function __construct(root $root, $binding = '') {
			$this->root		 = $root;
			$this->binding	 = $binding.$this->getBinding();

			$this->listeners = &$this->root->hook->register($this, $binding);

			$this->construct();
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

		/**
		 *
		 * @param callable $method
		 * @param string $message
		 * @param string $binding
		 */
		final protected function listen(callable $method, $message, $binding = null) {
			$this->root->hook->listen($method, $message, $binding);
		}
	}
}
