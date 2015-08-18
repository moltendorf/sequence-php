<?php $f('header.html') ?>
<article id="info" class="fancy-box">
	<h1><?= isset($v['title']) ? $v['title'] : $l("INFO_$v[status]", true) ?></h1>

	<div class="message">
		<?php if (isset($v['message']) || !isset($v['redirect_location'])): ?>
			<p><?= isset($v['message']) ? $v['message'] : $l("INFO_$v[status]_MESSAGE", true) ?></p>
		<?php endif; ?>
		<?php if (isset($v['redirect_location'])): ?>
			<p><?= $l("INFO_$v[status]_MESSAGE", true) ?> <a href="<?= $v['redirect_location'] ?>"><?= $v['redirect_display'] ?></a>.</p>
		<?php endif; ?>
	</div>

	<?php if (isset($v['error_exceptions'])): ?>
		<?php foreach ($v['error_exceptions'] as $n => $e): ?>
			<?php if ($n): ?>
				<h2><?= $l('EXCEPTION_CAUSED_BY', true) ?></h2>
			<?php else: ?>
				<h2><?= $l('EXCEPTION', true) ?></h2>
			<?php endif; ?>

			<div class="exception">
				<h4><?= $l("EXCEPTION_$e[message]", true) ?></h4>

				<p><?= $l('TYPE', true) ?>: <span class="mono"><?= $e['class'] ?></span></p>
				<p><?= $l('MESSAGE', true) ?>: <span class="mono"><?= $e['message'] ?></span></p>
				<p><?= $l('FILE', true) ?>: <span class="mono"><?= "$e[file]($e[line])" ?></span></p>
			</div>

			<h3><?= $l('TRACE', true) ?></h3>

			<div class="trace"><?= $e['trace'] ?></div>
		<?php endforeach; ?>
	<?php endif ?>

	<?php if (isset($v['error_output'])): ?>
		<h3><?= $l('OUTPUT', true) ?></h3>

		<div class="output"><?= $v['error_output'] ?></div>
	<?php endif ?>
</article>
<?php $f('footer.html') ?>
