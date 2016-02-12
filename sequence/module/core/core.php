<?php

namespace sequence\module\core {

	use sequence as s;
	use sequence\functions as f;

	class core extends s\module {

		use s\listener;

		/**
		 *
		 * @param s\root $root
		 * @param string $binding
		 */
		public function __construct(s\root $root, $binding = '') {
			$this->bind($root, $binding);

			$this->listen([$this, 'template'], 'template', 'application');
		}

		/**
		 *
		 * @param string $request
		 * @param string $request_root
		 *
		 * @return array
		 */
		public function request($request, $request_root) {
			$root     = $this->root;
			$handler  = $root->handler;
			$path     = $root->path;
			$template = $root->template;

			if (strlen($request) > 0) {
				$segments = explode('/', $request);
			} else {
				$segments = [];
			}

			$count = count($segments);

			if ($count > 1) {
				$file = end($segments);
				$type = substr(strrchr($file, '.'), 1);

				switch ($segments[0]) {
				case 'script':
					switch ($type) {
					case 'js':
					case 'json':
						if ($count > 3 && $segments[1] === 'module') {
							$module = preg_replace('/(?:^[^a-z0-9]|[^a-z0-9_]|[^a-z0-9]$)/', '', $segments[2]);
							$file   = $path->module("$module/script/".implode('/', array_slice($segments, 3)).'.php');
						} else {
							$file = $path->script(implode('/', array_slice($segments, 1)).'.php');
						}

						if ($file) {
							$handler->setTemplateRaw($file);
							$handler->setType($type);

							header('Cache-Control: s-maxage=14400, max-age=14400');

							return 200;
						}
					}
					break;

				case 'style':
					if ($count == 2) {
						$style = preg_replace('/(?:^[^a-z0-9]|[^a-z0-9_]|[^a-z0-9]$)/', '', basename($file, ".$type"));

						if ($template->styleEnabled($style)) {
							$template->setStyle($style);

							switch ($type) {
							case 'css':
								$handler->setMethod([$template, 'buildMainStyleSheet']);

								header('Cache-Control: s-maxage=14400, max-age=14400');

								return 200;

							case 'js':
								$handler->setMethod([$template, 'buildMainScript']);

								header('Cache-Control: s-maxage=14400, max-age=14400');

								return 200;
							}
						}
					} elseif ($count > 2) {
						$style = preg_replace('/(?:^[^a-z0-9]|[^a-z0-9_]|[^a-z0-9]$)/', '', $segments[1]);

						if ($template->setStyle($style)) {
							if ($count > 4 && $segments[2] === 'module') {
								$prefix = preg_replace('/(?:^[^a-z0-9]|[^a-z0-9_]|[^a-z0-9]$)/', '', $segments[3]).':';
								$file   = implode('/', array_slice($segments, 4));
							} else {
								$prefix = '';
								$file   = implode('/', array_slice($segments, 2));
							}

							switch ($type) {
							case 'css':
								if ($handler->setTemplate("{$prefix}style/$file")) {
									header('Cache-Control: s-maxage=14400, max-age=14400');

									return 200;
								}
								break;

							case 'js':
							case 'json':
								if ($handler->setTemplate("{$prefix}script/$file")) {
									header('Cache-Control: s-maxage=14400, max-age=14400');

									return 200;
								}
								break;
							}
						}
					}
					break;
				}
			}

			return 404;
		}

		/**
		 * Define core template variables.
		 */
		public function template() {
			$root     = $this->root;
			$handler  = $root->handler;
			$s        = $root->settings;
			$template = $root->template;

			$v = $template->get();

			// Site display.

			if (isset($s['site_display'])) {
				$display = $s['site_display'];
			} else {
				$display = $_SERVER['HTTP_HOST'];
			}

			// Site tagline.

			if (isset($s['site_tagline'])) {
				$tagline = $s['site_tagline'];
			} else {
				$tagline = false;
			}

			// Site title.

			$title = '';

			if (isset($v['page_title'])) {
				$title = $v['page_title'];
			} elseif (isset($v['module_display'])) {
				$title = $v['module_display'];
			} elseif (isset($v['module_name'])) {
				$title = $v['module_name'];
			} else {
				goto title_suffix;
			}

			$title .= ' // ';

			title_suffix:
			$title .= $display;

			// Copyright display.

			if ($s['site_copyright'] === '1') {
				$copyright = true;

				if (isset($s['site_copyright_display'])) {
					$copyright_display = $s['site_copyright_display'];
				} else {
					$copyright_display = $display;
				}

				if (isset($s['site_copyright_date'])) {
					$year = date('Y');

					if ($s['site_copyright_date'] < $year) {
						$copyright_date = "{$s['site_copyright_date']}-$year";
					} else {
						$copyright_date = $s['site_copyright_date'];
					}
				} else {
					$copyright_date = '';
				}
			} else {
				$copyright         = false;
				$copyright_display = false;
				$copyright_date    = false;
			}

			// Navigation data.

			$navigation = $handler->getNavigation();

			if ($navigation) {
				$active = $handler->getModules();

				if (!is_array($active)) {
					$active = [];
				}

				$modules = [];

				foreach ($navigation as $module) {
					$module['active'] = in_array($module['module'], $active);

					$modules[] = $module;
				}
			} else {
				$modules = false;
			}

			// Style.

			if (isset($s['style'])) {
				$style = $s['style'];
			} else {
				$style = 'default';
			}

			// Script.
			$script = "/static/style/$style.js";

			$script_data = [
				'query'   => $s['query'],
				'version' => s\version
			];

			// Stylesheet.

			$stylesheet = "/static/style/$style.css";

			$template->add([
				'core_display'           => $display,
				'core_tagline'           => $tagline,
				'core_title'             => $title,
				'core_copyright'         => $copyright,
				'core_copyright_display' => $copyright_display,
				'core_copyright_date'    => $copyright_date,
				'core_root'              => $s['root'],
				'core_navigation'        => $modules,
				'core_script'            => $script,
				'core_script_data'       => json_encode($script_data),
				'core_stylesheet'        => $stylesheet,
				'core_version'           => s\version,
			]);
		}
	}
}
