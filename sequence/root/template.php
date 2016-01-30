<?php

namespace sequence\root {

	use sequence as s;
	use sequence\functions as f;

	use exception;

	class template {

		use s\broadcaster;

		/**
		 * List of messages that this class can send.
		 *
		 * @var array
		 */
		const messages = ['template'];

		/**
		 * Template data variables.
		 *
		 * @var array
		 */
		private $data = [];

		/**
		 * Current style.
		 *
		 * @var string
		 */
		private $style = 'default';

		/** Enabled styles.
		 *
		 * @var array
		 */
		private $styles = [];

		/**
		 * Configure current template settings.
		 *
		 * @param s\root $root
		 * @param string $binding
		 */
		public function __construct(s\root $root, $binding = '') {
			$this->bind($root, $binding);
			$this->clear();

			$application = $root->application;
			$settings    = $root->settings;

			try {
				if ($root->database) {
					$database = $root->database;
					$prefix   = $database->getPrefix();

					$statement = $database->prepare("
						select style_name, style_customizations
						from {$prefix}styles
						where style_is_enabled = 1
					");

					$statement->execute();

					foreach ($statement->fetchAll() as $row) {
						$this->styles[$row[0]] = [
							'customizations' => (boolean)$row[1],
							'loaded'         => false
						];
					}

					unset($row);

					$statement->closeCursor();
				}
			} catch (exception $exception) {
				$application->errors[] = $exception;
			}

			if ((!isset($settings['style']) || !$this->setStyle($settings['style'])) && !isset($this->styles['default'])) {
				// Only enable the default style if the preferred style is not enabled.
				$this->styles['default'] = [
					'customizations' => false,
					'loaded'         => false
				];

				$this->setStyle();
			}
		}

		/**
		 * Bind all classes in root to application identity.
		 *
		 * @return string
		 */
		protected function getBinding() {
			return 'application';
		}

		/**
		 * Set the current style.
		 *
		 * @param string $style
		 *
		 * @return bool
		 */
		public function setStyle($style = 'default') {
			if ($this->styleEnabled($style)) {
				/**
				 * Load style settings.
				 *
				 * @param string $style
				 * @param array  $styles
				 *
				 * @return bool
				 * @throws exception
				 */
				$load = function ($style, $styles = []) use (&$load) {
					$settings = &$this->styles[$style];

					// Short circuit if this style is already loaded.
					if ($settings['loaded']) {
						return true;
					}

					$settings['loaded'] = true;

					$styles[] = $style;

					$root = $this->root;
					$path = $root->path;

					if ($file = $path->style("$style/settings.php")) {
						$settings += include $file;
					}

					if (isset($settings['inherits'])) {
						foreach ($settings['inherits'] as $inherit) {
							if (!$this->styleEnabled($inherit)) {
								if (s\ship) {
									goto fail;
								} else {
									throw new exception('STYLE_NOT_ENABLED');
								}
							} elseif (in_array($inherit, $styles)) {
								if (s\ship) {
									goto fail;
								} else {
									throw new exception('STYLE_INHERITANCE_RECURSION');
								}
							}

							if (!$load($inherit, $styles)) {
								goto fail;
							}
						}
					}

					return true;

					fail:
					// Disable style.
					unset($this->styles[$style]);

					return false;
				};

				if ($load($style)) {
					$this->style = $style;

					return true;
				}
			}

			return false;
		}

		/**
		 * Get the current style.
		 *
		 * @return string
		 */
		public function getStyle() {
			return $this->style;
		}

		/**
		 * Check if the style is enabled.
		 *
		 * @param string $style
		 *
		 * @return boolean
		 */
		public function styleEnabled($style = 'default') {
			return isset($this->styles[$style]);
		}

		/**
		 * Get use customizations.
		 *
		 * @param string $style
		 *
		 * @return boolean
		 */
		public function styleCustomizations($style = 'default') {
			return $this->styleEnabled($style) && $this->styles[$style]['customizations'];
		}

		/**
		 * Add template variables.
		 *
		 * @param array $input
		 */
		public function add(array $input) {
			$this->data += $input;
		}

		/**
		 * Set template variables. This overrides variables defined using add()!
		 *
		 * @param array $input
		 */
		public function set(array $input) {
			$this->data = $input + $this->data;
		}

		/**
		 * Add a script to this page.
		 */
		public function script($script, $defer = true) {
			$key = $defer ? 'scripts_deferred' : 'scripts';

			if (is_array($script)) {
				$this->data[$key][] = $script;
			} else {
				$this->data[$key][] = ['src' => $script];
			}
		}

		/**
		 * Get a copy of template variables.
		 *
		 * @return array
		 */
		public function get() {
			return $this->data;
		}

		/**
		 * Clear all template variables.
		 */
		public function clear() {
			$this->data = [
				'scripts'          => [],
				'scripts_deferred' => []
			];
		}

		/**
		 * Load the template file.
		 *
		 * @param         $file
		 *
		 * @return string
		 */
		public function load($file) {
			$root    = $this->root;
			$handler = $root->handler;

			if ($type = $handler->getType()) {
				$this->broadcast('template:'.$type);
			} else {
				$this->broadcast('template');
			}

			$this->set(['style' => $this->style]);

			$l = $root->language;
			$s = $root->settings;
			$v = $this->data;

			/**
			 * Load style variables.
			 *
			 * @param string $style
			 *
			 * @return array
			 */
			$load = function ($style) use (&$load, $l, $s, $v) {
				$root = $this->root;
				$path = $root->path;

				/**
				 * Include file and return result.
				 *
				 * @param $file
				 *
				 * @return mixed
				 */
				$f = function ($file) use ($s, $l, $v) {
					return include $file;
				};

				$settings = &$this->styles[$style];

				$customizations = [];

				if ($settings['customizations']) {
					if ($file = $path->home("$path->style/$style/custom/variables.php")) {
						$customizations = $f($file);
					}

					if ($file = $path->system("$path->style/$style/custom/variables.php")) {
						$customizations += $f($file);
					}
				}

				if ($file = $path->home("$path->style/$style/variables.php")) {
					$variables = $f($file);
				} else {
					$variables = [];
				}

				if ($file = $path->system("$path->style/$style/variables.php")) {
					$variables += $f($file);
				}

				if (isset($settings['inherits'])) {
					foreach ($settings['inherits'] as $inherit) {
						list ($_variables, $_customizations) = $load($inherit);

						foreach (array_keys($_customizations) as $key) {
							if (isset($variables[$key])) {
								unset($_customizations[$key]);
							}
						}

						$variables += $_variables;
						$customizations += $_customizations;
					}
				}

				return [$variables, $customizations];
			};

			list($add, $set) = $load($this->style);

			$this->add($add);
			$this->set($set);

			$l = $root->language;
			$s = $root->settings;
			$v = $this->data;

			/**
			 * Load template file.
			 *
			 * @param string $file
			 *
			 * @throws exception
			 */
			$f = function ($file) use (&$config, $l, $s, $v) {
				/** @noinspection PhpUnusedLocalVariableInspection */
				/**
				 * Load template file.
				 *
				 * @param string $name
				 *
				 * @throws exception
				 */
				$f = function ($name) use (&$f, $l, $s, $v) {
					if ($file = $this->file($name)) {
						include $file;
					} else {
						throw new exception('TEMPLATE_FILE_NOT_FOUND');
					}
				};

				if ($file) {
					include $file;
				} else {
					throw new exception('TEMPLATE_FILE_NOT_FOUND');
				}
			};

			$f($file);
		}

		/**
		 * Load a set of template files. Primarily used by buildMainStyleSheet and buildMainScript methods.
		 *
		 * @param array   $set
		 * @param array   $files
		 * @param string  $prefix   Folder these files come from.
		 * @param boolean $comments Include CSS style comments at the beginning and end of files.
		 */
		public function loadSet(&$set, $files, $prefix = '', $comments = true) {
			$root = $this->root;

			$config = null;

			$l = $root->language;
			$s = $root->settings;
			$v = $this->data;

			/**
			 * Load template file.
			 *
			 * @param string $file
			 *
			 * @throws exception
			 */
			$f = function ($file) use (&$config, $l, $s, $v) {
				/** @noinspection PhpUnusedLocalVariableInspection */
				/**
				 * Load template file.
				 *
				 * @param string $name
				 *
				 * @throws exception
				 */
				$f = function ($name) use (&$f, $l, $s, $v) {
					if ($file = $this->file($name)) {
						include $file;
					} else {
						throw new exception('TEMPLATE_FILE_NOT_FOUND');
					}
				};

				if ($file) {
					include $file;
				} else {
					throw new exception('TEMPLATE_FILE_NOT_FOUND');
				}
			};

			/*
			 * Include each template file and stash its output into the sorting array by inclusion priority.
			 */

			foreach ($files as $file) {
				$short = basename($file, '.php');

				// Start of output buffering.
				ob_start();

				// Include the template file.
				$f($file);

				// Check if it requested a priority.
				if (isset($config['priority'])) {
					$priority = (integer)$config['priority'];
				} else {
					ob_end_clean();

					// End of output buffering.

					continue;
				}

				$config = null;

				// Include CSS style comments?
				if ($comments) {
					$header = '/* '.str_pad(" Beginning of file: $prefix/$short ", 100, '*', STR_PAD_BOTH)." *\n".
						' * '.str_pad(" Priority: $priority ", 100, '*', STR_PAD_BOTH).' */';
					$footer = '/* '.str_pad(" Ending of file: $prefix/$short ", 100, '*', STR_PAD_BOTH).' */';

					// Combine the header, output, and footer.
					$output = "\n$header\n\n".ob_get_clean()."\n$footer\n";
				} else {
					$output = "\n".ob_get_clean()."\n\n";
				}

				// End of output buffering.

				// Stash it.
				if (isset($set[$priority])) { // Check if there is already an array for this priority.
					$set[$priority][] = $output;
				} else {
					$set[$priority] = [$output];
				}
			}
		}

		/**
		 * Output a set of template files. Primarily used by buildMainStyleSheet and buildMainScript methods.
		 *
		 * @param array $set
		 */
		public function outputSet($set) {
			// Ensure priorities are in order.
			ksort($set);

			foreach ($set as $number) {
				// We sort each priority by filename to add some consistency.
				ksort($number);

				foreach ($number as $content) {
					echo $content;
				}
			}
		}

		/**
		 * Locate a template file.
		 *
		 * @param string $name
		 *
		 * @return string|false
		 */
		public function file($name) {
			$root = $this->root;
			$path = $root->path;

			$segments = explode(':', $name);

			if (count($segments) > 1) {
				list ($module, $name) = $segments;

				$name .= '.php';

				$segment = "module/$module/$name";
			} else {
				$name = $segment = "$name.php";
			}

			/**
			 * Find template file.
			 *
			 * @param string $style
			 *
			 * @return array
			 */
			$find = function ($style) use (&$find, $segment) {
				$root = $this->root;
				$path = $root->path;

				$settings = &$this->styles[$style];

				if ($settings['customizations'] && $file = $path->style("$style/template/custom/$segment")) {
					return $file;
				}

				if ($file = $path->style("$style/template/$segment")) {
					return $file;
				}

				if (isset($settings['inherits'])) {
					foreach ($settings['inherits'] as $inherit) {
						if ($file = $find($inherit)) {
							return $file;
						}
					}
				}

				return false;
			};

			/*
			 * Try finding the template in active style.
			 */
			if ($file = $find($this->style)) {
				return $file;
			}

			if (isset($module)) {
				/*
				 * Try finding template in module.
				 */

				if ($file = $path->module("$module/template/$name")) {
					return $file;
				}
			}

			return false;
		}

		/*
		 * Brainstorming:
		 *
		 * 1a.  Load all customization stylesheets for all loaded modules.
		 * 1b.  Load template stylesheets for all loaded modules that do not match the names of customization
		 *      stylesheets for all loaded modules.
		 * 1c.  If neither customization stylesheet or template stylesheet directories exist for a loaded module,
		 *      repeat step 2a and 2b for each module in the default style.
		 * 1d.  If neither customization stylesheet or template stylesheet directories exist for a loaded module in
		 *      the current style or the default style, load all stylesheets for each module from its template
		 *      directory.
		 *
		 * 2a.  Load all customization stylesheets.
		 * 2b.  Load template stylesheets that do not match names of customization stylesheets.
		 * 2c.  If neither customization stylesheet or template stylesheet directories exist, repeat step 1a and 1b
		 *      using default style.
		 */

		/**
		 * Build the main stylesheet and send it to main output buffer.
		 */
		public function buildMainStyleSheet() {
			$root    = $this->root;
			$handler = $root->handler;
			$module  = $root->module;
			$path    = $root->path;

			/**
			 * Find template file.
			 *
			 * @param string $style
			 * @param string $folder
			 *
			 * @return array
			 */
			$find = function ($style, $folder = '.') use (&$find, &$glob) {
				$root = $this->root;
				$path = $root->path;

				$settings = &$this->styles[$style];

				if ($settings['customizations']) {
					$files = $glob("$path->style/$style/template/custom/$folder");
				} else {
					$files = [];
				}

				$files += $glob("$path->style/$style/template/$folder");

				if (isset($settings['inherits'])) {
					foreach ($settings['inherits'] as $inherit) {
						$files += $find($inherit, $folder);
					}
				}

				return $files;
			};

			/**
			 * Find all stylesheets in passed relative path to home and system.
			 *
			 * @param string $base
			 * @param string $folder
			 *
			 * @return array
			 */
			$glob = function ($base, $folder = '/style/main') {
				$root = $this->root;
				$path = $root->path;

				return f\files_sort($path->glob($base.$folder, 'css.php'));
			};

			$set    = [];
			$folder = 'style/main';

			// No automatic type detection.
			$handler->setType('css');

			// Load the header file first.
			$this->load($this->file('style/header.css'));

			/*
			 * Load all module stylesheets.
			 */

			foreach ($module->getLoaded() as $name => $instance) {
				$this->loadSet($set, $find($this->style, "module/$name") + $glob("$path->module/$name/template"), "$name:$folder");
			}

			/*
			 * Load all template stylesheets.
			 */

			$this->loadSet($set, $find($this->style), $folder);

			$this->outputSet($set);
		}

		/*
		 * Brainstorming:
		 *
		 * 1.   Load all master scripts.
		 * 2.   Load all master scripts for all loaded modules.
		 * 3.   Load all current template scripts.
		 * 4.   Load all customization template scripts.
		 * 4.   Load all current template scripts for all loaded modules.
		 * 5.   Load all customization template scripts for all loaded modules.
		 */

		/**
		 * Build the main script and send it to main output buffer.
		 */
		public function buildMainScript() {
			$root    = $this->root;
			$handler = $root->handler;
			$module  = $root->module;
			$path    = $root->path;

			/**
			 * Find template file.
			 *
			 * @param string $style
			 * @param string $folder
			 *
			 * @return array
			 */
			$find = function ($style, $folder = '.') use (&$find, &$glob) {
				$root = $this->root;
				$path = $root->path;

				$settings = &$this->styles[$style];

				if ($settings['customizations']) {
					$files = $glob("$path->style/$style/template/custom/$folder");
				} else {
					$files = [];
				}

				$files += $glob("$path->style/$style/template/$folder");

				if (isset($settings['inherits'])) {
					foreach ($settings['inherits'] as $inherit) {
						$files += $find($inherit, $folder);
					}
				}

				return $files;
			};

			/**
			 * Find all scripts in passed relative path to home and system.
			 *
			 * @param string $base
			 * @param string $folder
			 *
			 * @return array
			 */
			$glob = function ($base, $folder = '/script/main') {
				$root = $this->root;
				$path = $root->path;

				return f\files_sort($path->glob($base.$folder, 'js.php'));
			};

			$set = [];

			// No automatic type detection.
			$handler->setType('js');

			// Load the header file first.
			$this->load($this->file('script/header.js'));

			/*
			 * Load all main scripts.
			 */

			$this->loadSet($set, $glob($folder = "$path->script/main", ''), "(root)/$folder");

			/*
			 * Load all main module scripts.
			 */

			foreach ($module->getLoaded() as $name => $instance) {
				$this->loadSet($set, $glob($folder = "$path->module/$name"), "(root)/$folder/script/main");
			}

			$folder = 'script/main';

			/*
			 * Load all current template scripts.
			 */

			$this->loadSet($set, $find($this->style), "($this->style style)/template/$folder");

			/*
			 * Load all current template module scripts.
			 */

			foreach ($module->getLoaded() as $name => $instance) {
				$this->loadSet($set, $find($this->style, "module/$name"), "($name module)/template/$folder");
			}

			$this->outputSet($set);
		}
	}
}
