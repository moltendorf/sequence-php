<?php

namespace sequence {

	/**
	 *
	 * @property-read root\application     $application
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
