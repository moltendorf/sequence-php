<?php

namespace sequence\root {

	use sequence as b;
	use sequence\functions as f;

	class template {

		use b\broadcaster;

		/**
		 *
		 * @var array
		 */
		public static $messages = ['body'];

		/**
		 *
		 * @var array
		 */
		public $variable;

		/**
		 *
		 * @var string
		 */
		public $file;

		/**
		 *
		 * @var string
		 */
		private $current;

		/**
		 *
		 * @var boolean
		 */
		private $default = false;

		/**
		 *
		 * @var boolean
		 */
		private $customizations = false;

		/**
		 *
		 * @param b\root $root
		 * @param string $binding
		 */
		public function __construct(b\root $root, $binding = '') {
			$this->bind($root, $binding);

			$settings = $root->settings;

			if (isset($settings['template.default'])) {
				$this->current = $settings['template.default'];
			} else {
				$this->current = 'default';
				$this->default = true;
			}

			$this->customizations = (boolean) $settings['template.customizations'];

			$this->clear();
		}

		/**
		 *
		 */
		public function clear() {
			$this->variable = [
				'status' => 200
			];
		}

		/**
		 *
		 */
		public function body() {
			$this->broadcast('body');

			$f = null; // We pass a reference of $f to itself, so we need to define it here.
			$l = $this->root->language;
			$s = $this->root->settings;
			$v = $this->variable;

			$f = function ($template) use (& $f, $l, $s, $v) {
				if ($file = $this->file($template)) {
					include $file;
				} else {
					throw new \Exception('TEMPLATE_FILE_NOT_EXIST');
				}
			};

			http_response_code($v['status']);

			ob_start();

			try {
				$f($this->file);
			} catch (\Exception $exception) {
				ob_end_clean();

				throw $exception;
			}

			return ob_get_clean();
		}

		/**
		 *
		 * @param string $input
		 *
		 * @return string|boolean
		 */
		public function file($input) {
			$root = $this->root;
			$path = $root->path;

			$segments = explode(':', $input);

			if (count($segments) > 1) {
				list ($module, $template) = $segments;

				$template .= '.php';

				$segment = 'module/' . $module . '/' . $template;
			} else {
				$template = $segment = $input . '.php';
			}

			$check = function ($base, $template) use ($segment) {
				$path = $base . '/template/' . $template;

				if ($this->customizations) {
					$file = $path . '/custom/' . $segment;

					if (file_exists($file)) {
						return $file;
					}
				}

				$file = $path . '/' . $segment;

				if (file_exists($file)) {
					return $file;
				}

				return false;
			};

			if ($file = $check($path->home, $this->current)) {
				return $file;
			}

			if ($file = $check($path->system, $this->current)) {
				return $file;
			}

			if (!$this->default) {
				if ($file = $check($path->home, 'default')) {
					return $file;
				}

				if ($file = $check($path->system, 'default')) {
					return $file;
				}
			}

			if (isset($module)) {
				$check = function ($base) use ($module, $template) {
					$path = $base . '/sequence/module/' . $module . '/template';

					if ($this->customizations) {
						$file = $path . '/custom/' . $template;

						if (file_exists($file)) {
							return $file;
						}
					}

					$file = $path . '/' . $template;

					if (file_exists($file)) {
						return $file;
					}

					return false;
				};

				if ($file = $check($path->home)) {
					return $file;
				}

				if ($file = $check($path->system)) {
					return $file;
				}
			}

			return false;
		}

		/**
		 * @param string $location
		 * @param int    $status
		 */
		public function redirect($location, $status = 302) {
			$this->clear();

			$v = &$this->variable;

			$v['status']   = $status;
			$v['location'] = $location;

			if (($scheme = parse_url($location, PHP_URL_SCHEME)) != '') {
				$v['display'] = substr($location, strlen($scheme) + 3);
			} elseif (substr($location, 0, 2) == '//') {
				$v['display'] = substr($location, 2);
			} elseif (substr($location, 0, 1) == '/') {
				$host = $_SERVER['HTTP_HOST'];

				$v['display'] = $host . $location;
			} else {
				$host     = $_SERVER['HTTP_HOST'];
				$document = $_SERVER['DOCUMENT_URI'];

				$v['display'] = $host . substr($document, 0, strrpos($document, '/') + 1) . $location;
			}

			$this->file = 'redirect.html';

			if (b\ship) {
				header('Location: ' . $location);
			}
		}

		/**
		 * @param int|\Exception $status
		 */
		public function error($status = null) {
			$this->clear();

			$v = &$this->variable;

			if ($status instanceof \Exception) {
				$v['exception'] = $status;

				if ($status instanceof b\StatusException) {
					$v['status'] = $status->getStatus();
				} else {
					$v['status'] = 500;
				}

				$v['type']    = get_class($status);
				$v['message'] = $status->getMessage();
				$v['file']    = $status->getFile();
				$v['line']    = $status->getLine();
				$v['trace']   = f\text_format(f\text_normalize($status->getTraceAsString()));
			} else {
				if (is_int($status)) {
					$v['status'] = $status;
				} else {
					$v['status'] = 404;
				}
			}

			if (ob_get_length()) {
				$v['contents'] = f\text_format(f\text_normalize(ob_get_contents()));
			}

			if (b\ship) {
				$this->file = 'error.html';
			} else {
				$this->file = 'error_debug.html';
			}
		}
	}
}
