<?php $f('header_base.html') ?>
<header id="site-header" class="fancy-box">
	<h1><a id="site-title" href="<?= $v['core_root'] ?>"><?= $v['core_display'] ?></a>
		<?php if (isset($v['core_tagline'])): ?><small id="site-tagline"><?= $v['core_tagline'] ?></small><?php endif; ?></h1>

	<?php if ($v['core_navigation']): ?>
		<nav id="site-nav">
			<?php foreach ($v['core_navigation'] as $m): ?>
				<a class="site-nav<?= $m['active'] ? ' active' : '' ?>" href="<?= $m['path'] ?>"><?= $m['display'] ?></a>
			<?php endforeach; ?>
		</nav>
	<?php endif; ?>
</header>