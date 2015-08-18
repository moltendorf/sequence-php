<?php $f('header.html') ?>
<article id="info" class="fancy-box">
	<h1><?= isset($v['title']) ? $v['title'] : $l["INFO_$v[status]"] ?></h1>

	<div class="message">
		<?php if (isset($v['message']) || !isset($v['redirect_location'])): ?>
			<p><?= isset($v['message']) ? $v['message'] : $l["INFO_$v[status]_MESSAGE"] ?></p>
		<?php endif; ?>
		<?php if (isset($v['redirect_location'])): ?>
			<p><?= $l["INFO_$v[status]_MESSAGE"] ?> <a href="<?= $v['redirect_location'] ?>"><?= $v['redirect_display'] ?></a>.</p>
		<?php endif; ?>
	</div>
</article>
<?php $f('footer.html') ?>
