	<?php foreach ($v['scripts_deferred'] as $script): ?>
		<?php if (!isset($script['src'])): ?>
			<script><?= $script['body'] ?></script>
		<?php endif; ?>
	<?php endforeach; ?>
</body>
</html>
