<!doctype html>
<html>
	<head>
		<title><?= $lang['ERROR_' . $status] ?></title>

		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

		<style type="text/css">
			* {
				outline-style: none;

				margin: 0;
				padding: 0;
			}

			html {
				background-color: #eeffff;

				overflow-y: scroll;
			}

			body {
				font-family: sans-serif;
				font-size: 15px;
			}

			h1, h2 {
				color: #99bbdd;
			}

			h1 {
				font-size: 1.6em;

				margin-left: 32px;
				margin-right: 32px;
				margin-top: 24px;
			}

			.exception > h1 {
				font-size: 1.2em;

				margin-bottom: 8px;
				margin-left: 0;
				margin-right: 0;
				margin-top: 0;
			}

			h2 {
				font-size: 1.2em;

				margin-left: 40px;
				margin-right: 40px;
				margin-top: 16px;
			}

			.exception > h2 {
				margin-left: 8px;
				margin-right: 8px;
				margin-top: 8px;
			}

			.message > h2 {
				font-size: 1.2em;

				margin: 0;
			}

			h1 + h2 {
				margin-top: 8px;
			}

			div {
				background-color: #ffffff;

				border-color: #99bbdd;
				border-style: solid;
				border-width: 1px;

				margin-bottom: 24px;
				margin-left: 32px;
				margin-right: 32px;
				margin-top: 8px;

				padding: 16px;
			}

			.mono, .trace, .output {
				font-family: monospace;
				font-size: 0.8em;

				word-break: break-all;
			}

			.trace {
				margin-left: 0;
				margin-right: 0;
				margin-bottom: 0;
			}

			@media (min-resolution: 360dpcm) {
				body {
					font-size: 30px;
				}

				h1 {
					margin-left: 64px;
					margin-right: 64px;
					margin-top: 48px;
				}

				.exception > h1 {
					margin-bottom: 16px;
				}

				h2 {
					margin-left: 80px;
					margin-right: 80px;
					margin-top: 32px;
				}

				.exception > h2 {
					margin-left: 16px;
					margin-right: 16px;
					margin-top: 16px;
				}

				h1 + h2 {
					margin-top: 16px;
				}

				div {
					border-width: 2px;

					margin-bottom: 48px;
					margin-left: 64px;
					margin-right: 64px;
					margin-top: 16px;

					padding: 32px;
				}
			}
		</style>
	</head>

	<body>
		<h1><?= $lang['ERROR_' . $status] ?></h1>

		<div class="message">
			<h2><?= $lang['ERROR_' . $status . '_MESSAGE'] ?></h2>
		</div>

		<?php if ($exception): ?>
			<h2><?= $lang['EXCEPTION'] ?></h2>

			<div class="exception">
				<h1><?= $lang[$message] ?></h1>

				<p><?= $lang['TYPE'] ?>: <span class="mono"><?= $type ?></span></p>
				<p><?= $lang['MESSAGE'] ?>: <span class="mono"><?= $message ?></span></p>
				<p><?= $lang['FILE'] ?>: <span class="mono"><?= $file ?>(<?= $line ?>)</span></p>

				<h2><?= $lang['TRACE'] ?></h2>
				<div class="trace">
					<?= $trace ?>
				</div>
			</div>
		<?php endif ?>

		<?php if ($contents): ?>
			<h2><?= $lang['OUTPUT'] ?></h2>

			<div class="output">
				<?= $contents ?>
			</div>
		<?php endif ?>
	</body>
</html>
