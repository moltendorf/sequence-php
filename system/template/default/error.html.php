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

		h2 {
			font-size: 1.2em;

			margin: 16px 40px 0;
		}

		.message > h2 {
			font-size: 1.2em;

			margin: 0;
		}

		div {
			background-color: #ffffff;

			border: 1px solid #99bbdd;
			margin: 8px 32px 24px;
			padding: 16px;
		}

		@media (min-resolution: 360dpcm) {
			body {
				font-size: 30px;
			}

			h1 {
				margin: 48px 64px 0;
			}

			h2 {
				margin: 32px 80px 0;
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
</body>
</html>
