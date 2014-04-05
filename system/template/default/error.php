<!doctype html>
<html>
<head>
	<title><?= $lang['ERROR_' . $status] ?></title>

	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

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

		h2 {
			font-size: 1.2em;

			margin-left: 40px;
			margin-right: 40px;
			margin-top: 16px;
		}

		.message > h2 {
			font-size: 1.2em;

			margin: 0;
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

		@media (min-resolution: 360dpcm) {
			body {
				font-size: 30px;
			}

			h1 {
				margin-left: 64px;
				margin-right: 64px;
				margin-top: 48px;
			}

			h2 {
				margin-left: 80px;
				margin-right: 80px;
				margin-top: 32px;
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
</body>
</html>
