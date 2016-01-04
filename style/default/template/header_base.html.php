<!doctype html>
<html>
<head lang="en">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1"/>

<link rel="shortcut icon" href="/favicon.ico" type="image/vnd.microsoft.icon"/>
<link rel="shortcut icon" href="/favicon.png" type="image/png"/>
<link rel="shortcut icon" href="/favicon.gif" type="image/gif"/>

<link rel="stylesheet" href="<?= $v['core_stylesheet'] ?>"/>

<?php foreach($v['scripts'] as $script): ?>
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

<script src="<?= $v['core_script'] ?>" defer></script>

<?php foreach($v['scripts_deferred'] as $script): ?>
    <?php if (isset($script['src'])): ?>
        <script src="<?= $script['src'] ?>" defer></script>
    <?php else: ?>
        <script defer><?= $script['body'] ?></script>
    <?php endif; ?>
<?php endforeach; ?>

<title><?= $v['core_title'] ?></title>
</head>

<body>
