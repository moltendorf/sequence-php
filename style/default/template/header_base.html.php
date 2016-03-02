<!doctype html>
<html>
<head lang="en">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1"/>

	<title><?= $v['core_title'] ?? $_SERVER['HTTP_HOST'] ?></title>

	<link rel="shortcut icon" href="/favicon.ico" type="image/vnd.microsoft.icon"/>
	<link rel="shortcut icon" href="/favicon.png" type="image/png"/>
	<link rel="shortcut icon" href="/favicon.gif" type="image/gif"/>

	<?php if (isset($v['core_stylesheet'])): ?>
		<link rel="stylesheet" href="<?= $v['core_stylesheet'] ?>"/>
	<?php endif ?>

	<?php if (isset($v['core_stylesheet_print'])): ?>
		<link rel="stylesheet"<?php if (empty($v['print'])): ?> media="print"<?php endif ?>
					href="<?= $v['core_stylesheet_print'] ?>"/>
	<?php endif ?>

	<?php foreach ($v['scripts'] as $script): ?>
		<?php if (isset($script['src'])): ?>
			<script src="<?= $script['src'] ?>"></script>
		<?php else: ?>
			<script><?= $script['body'] ?></script>
		<?php endif; ?>
	<?php endforeach; ?>

	<?php if (sequence\ship): ?>
		<script src="//code.jquery.com/jquery-2.1.4.min.js" defer></script>
	<?php else: ?>
		<script src="//code.jquery.com/jquery-2.1.4.js" defer></script>
	<?php endif; ?>

	<?php if (isset($v['core_script'])): ?>
		<script src="<?= $v['core_script'] ?>" defer></script>
	<?php endif ?>

	<?php foreach ($v['scripts_deferred'] as $script): ?>
		<?php if (isset($script['src'])): ?>
			<script src="<?= $script['src'] ?>" defer></script>
		<?php endif; ?>
	<?php endforeach; ?>
</head>

<body>
