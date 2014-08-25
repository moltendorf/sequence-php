<?php

namespace sequence\root {

	class path {

		/**
		 * The path under the site's configured root this application operates in.
		 *
		 * This path is forcefully prefixed to all application level path configuration. No pages can be generated for
		 * outside of this root. However, aliases can be used to map outside locations to paths within this root. Pages
		 * that are generated through an alias will be stored at the location within the root. Your webserver should be
		 * configured to serve the page from the aliased location in the event it exists to prevent this script from
		 * being run unnecessarily. e.g. a common alias is to map / to /root/ thus when / and /root/ are visited, a page
		 * at /root/ will be generated and served; if your web server does not look in /root/ when / is visited, then
		 * this script will be run unnecessarily to serve /root/.
		 *
		 * @var string
		 */
		public $root;

		/**
		 * A full path to the root system directory.
		 *
		 * @var string
		 */
		public $system;

		/**
		 * A full path to the cache system directory.
		 *
		 * @var string
		 */
		public $cache;

		/**
		 * A full path to the language system directory.
		 *
		 * @var string
		 */
		public $language;

		/**
		 * A full path to the template system directory.
		 *
		 * @var string
		 */
		public $template;

		/**
		 * A full path to the module system directory.
		 *
		 * @var string
		 */
		public $module;

		/**
		 * A full path to the page storage directory.
		 *
		 * @var string
		 */
		public $page;

		/**
		 * A full path to the static content storage directory.
		 *
		 * @var string
		 */
		public $content;

		/**
		 *
		 * @param string $system
		 * @param array  $settings
		 *
		 * @throws
		 */
		public function settings($system, $settings) {
			$paths = [
				'root'     => $settings['root'],
				'system'   => realpath($system),
				'cache'    => realpath($system . '/cache'),
				'language' => realpath($system . '/language'),
				'template' => realpath($system . '/template'),
				'module'   => realpath($system . '/sequence/module'),
				'page'     => realpath($system . '/' . $settings['page']),
				'content'  => realpath($system . '/' . $settings['content'])
			];

			foreach ($paths as $key => $path) {
				if ($path === false) {
					throw new \Exception(strtoupper($key) . '_PATH_NOT_FOUND');
				}

				$this->$key = $path;
			}
		}
	}
}
