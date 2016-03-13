<?php

namespace sequence\module\robots {

	use sequence as s;

	class robots extends s\module {

		use s\listener;

		private $rules = ['*' => ['allow' => ['/']]];

		/**
		 *
		 * @param string $request
		 * @param string $request_root
		 *
		 * @return int|null
		 */
		public function request($request, $request_root) {
			$root     = $this->root;
			$handler  = $root->handler;
			$settings = $root->settings;

			$handler->setMethod([$this, 'output']);
			$handler->setType('txt');

			$domain = $_SERVER['HTTP_HOST'];

			if (isset($settings['robots_domains']) && !in_array($domain, explode(',', $settings['robots_domains']))) {
				$this->rules = ['*' => ['disallow' => ['/'], 'noindex' => ['/']]];
			}

			header('Cache-Control: s-maxage=14400, max-age=14400');

			return 200;
		}

		/**
		 * Output robots info.
		 */
		public function output() {
			foreach ($this->rules as $agent => $rules) {
				echo 'User-agent: ', $agent, "\n";

				ksort($rules);

				foreach ($rules as $type => $paths) {
					$type = ucfirst($type);

					foreach ($paths as $path) {
						echo $type, ': ', $path, "\n";
					}
				}

				echo "\n";
			}
		}
	}
}
