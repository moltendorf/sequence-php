<?php

namespace sequence\root {

	use Exception;
	use sequence\listener;

	class path {

		use listener;

		/**
		 * A full path to the root system directory.
		 *
		 * @var string
		 */
		public $system;

		/**
		 * A full path to the language system directory.
		 *
		 * @var string
		 */
		public $language;

		/**
		 * A full path to the module system directory.
		 *
		 * @var string
		 */
		public $module;

		/**
		 * A full path to the template system directory.
		 *
		 * @var string
		 */
		public $template;

		/**
		 * A full path to the cache system directory.
		 *
		 * @var string
		 */
		public $cache;

		/**
		 *
		 * @param string $systemPath
		 * @param string $homePath
		 * @param string $webPath
		 * @param array  $settings
		 *
		 * @throws Exception
		 */
		public function settings($systemPath, $homePath, $webPath, $settings) {
			$root        = $this->root;
			$application = $root->application;

			/**
			 * @param string $path   Path to the directory.
			 * @param bool   $create Create directory if it does not exist.
			 *
			 * @return string
			 */
			$directory = function ($path, $create = false) {
				if (!is_string($path)) {
					return false;
				}

				$full = realpath($path);

				if ($full !== false) {
					return $full;
				}

				if ($create) {
					try {
						mkdir($path, 0755, true);
					} catch (Exception $exception) {
						// Discard.
					}

					return realpath($path);
				}

				return false;
			};

			/*
			 * Configure and test system paths.
			 */

			$systemPath = $directory($systemPath);

			$paths = [
				'web'      => $webPath,
				'home'     => $homePath,
				'system'   => $systemPath,
				'language' => $directory($systemPath . '/language'),
				'template' => $directory($systemPath . '/template'),
				'module'   => $directory($systemPath . '/sequence/module')
			];

			foreach ($paths as $key => $path) {
				if ($systemPath === false || $path === false) {
					$application->errors[] = new Exception(strtoupper($key) . '_PATH_NOT_FOUND');

					$this->$key = false;
				} else {
					$this->$key = $path;
				}
			}

			/*
			 * Configure and test application paths.
			 */

			$settingsPath = $directory($homePath);

			$paths = [
				'cache'
			];

			foreach ($paths as $key) {
				if ($settingsPath !== false && isset($settings[$key])) {
					$path = $directory($settingsPath . '/' . $settings[$key], true);
				} else {
					$path = false;
				}

				if ($settingsPath === false || $path === false) {
					$application->errors[] = new Exception(strtoupper($key) . '_PATH_NOT_FOUND');

					$this->$key = false;
				} else {
					$this->$key = $path;
				}
			}
		}
	}
}
