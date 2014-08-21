<!doctype html>
<html>
<head>
	<title><?= $l['ERROR_' . $v['status']] ?></title>

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

			margin: 24px 32px 0;
		}

		.exception > h1 {
			font-size: 1.2em;

			margin: 0 0 8px;
		}

		h2 {
			font-size: 1.2em;

			margin: 16px 40px 0;
		}

		.exception > h2 {
			margin: 8px 8px 0;
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

			border: 1px solid #99bbdd;
			margin: 8px 32px 24px;
			padding: 16px;
		}

		.mono, .trace, .output {
			font-family: monospace;
			font-size: 0.8em;

			word-break: break-all;
		}

		.trace {
			margin: 8px 0 0;
		}

		@media (min-resolution: 360dpcm) {
			body {
				font-size: 30px;
			}

			h1 {
				margin: 48px 64px 0;
			}

			.exception > h1 {
				margin-bottom: 16px;
			}

			h2 {
				margin: 32px 80px 0;
			}

			.exception > h2 {
				margin: 16px 16px 0;
			}

			h1 + h2 {
				margin-top: 16px;
			}

			div {
				border-width: 2px;

				margin: 16px 64px 48px;
				padding: 32px;
			}
		}
	</style>
</head>

<body>
<h1><?= $l['ERROR_' . $v['status']] ?></h1>

<div class="message">
	<h2><?= $l['ERROR_' . $v['status'] . '_MESSAGE'] ?></h2>
</div>

<?php if ($v['exception']): ?>
	<h2><?= $l['EXCEPTION'] ?></h2>

	<div class="exception">
		<h1><?= $l[$v['message']] ?></h1>

		<p><?= $l['TYPE'] ?>: <span class="mono"><?= $v['type'] ?></span></p>

		<p><?= $l['MESSAGE'] ?>: <span class="mono"><?= $v['message'] ?></span></p>

		<p><?= $l['FILE'] ?>: <span class="mono"><?= $v['file'] ?>(<?= $v['line'] ?>)</span></p>

		<h2><?= $l['TRACE'] ?></h2>

		<div class="trace">
			<?= $v['trace'] ?>
		</div>
	</div>
<?php endif ?>

<?php if ($v['contents']): ?>
	<h2><?= $l['OUTPUT'] ?></h2>

	<div class="output">
		<?= $v['contents'] ?>
	</div>
<?php endif ?>
</body>
</html>
