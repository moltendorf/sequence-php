<?php

namespace sequence {

	/**
	 * Current version.
	 */
	const version = '0.1';

	use sequence\root\application;

	/**
	 *
	 * @property-read root\cache           $cache
	 * @property-read root\database        $database
	 * @property-read root\handler         $handler
	 * @property-read root\hook            $hook
	 * @property-read root\language        $language
	 * @property-read root\mail            $mail
	 * @property-read root\module          $module
	 * @property-read root\path            $path
	 * @property-read root\settings        $settings
	 * @property-read root\template        $template
	 */
	class root {

		/**
		 * @var root\application
		 */
		public $application;

		/**
		 * Instantiate the root class. This automatically instantiates the application class too.
		 *
		 * @param string $systemPath
		 * @param array  $homePath
		 */
		public function __construct($systemPath, $homePath) {
			$this->application = new application($this);
			$this->application->setup($systemPath, $homePath);

			if (!isset($this->database)) {
				// Define it so that the magic function does not define it for us.
				$this->database = null;
			}
		}

		/**
		 * Instantiate a main class and store the class instance.
		 *
		 * @param string $name
		 *
		 * @return mixed
		 */
		public function __get($name) {
			$class = "sequence\\root\\$name";

			spl_autoload($class);
			$this->$name = new $class($this);

			return $this->$name;
		}
	}
}
