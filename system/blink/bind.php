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
		 * @param root $root
		 * @param string $binding
		 */
		final public function __construct(root $root, $binding = '') {
			$this->root = $root;
			$this->binding = $binding.$this->getBinding();

			$this->construct();
		}

		/**
		 *
		 * @param string $message
		 * @param mixed $data,...
		 */
		final protected function broadcast($message) {
			$data = array_slice(func_get_args(), 1);

			$this->root->hook->_broadcast($this->binding, $message, $data);
		}

		/**
		 *
		 * @param callable $method
		 * @param string $message
		 * @param string $binding
		 */
		final protected function listen(callable $method, $message, $binding = null) {
			$this->root->hook->_listen($method, $message, $binding);
		}

		/**
		 *
		 * @param string $binding
		 * @param string $message
		 * @param array $data
		 */
		final private function _broadcast($binding, $message, $data) {

		}

		/**
		 *
		 * @param callable $method
		 * @param string $message
		 * @param string $binding
		 */
		final private function _listen(callable $method, $message, $binding) {

		}
	}
}
