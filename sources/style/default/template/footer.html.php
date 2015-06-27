<footer id="footer-wrap">
	<?php if ($v['core_copyright']): ?>
		<div id="footer-copyright"><?= $l['COPYRIGHT'] ?> <?= $v['core_copyright_date'] ?> <?= $v['core_copyright_display'] ?></div>
	<?php endif; ?>
	<div id="footer-powered-by"><?= $l['POWERED_BY'] ?></div>
</footer>
<?php $f('footer_base.html') ?>
