<?php if (isset($view)): ?>
	<?php
	$extraStyle = '';
	if (!empty($cardStyle)) {
		if (preg_match('/^\s*style\s*=\s*(["\'])(.*)\1\s*$/is', $cardStyle, $m)) {
			$extraStyle = $m[2];
		} else {
			$extraStyle = $cardStyle;
		}
		$extraStyle = rtrim(trim($extraStyle), ';');
	}
	$combinedStyle = 'min-height: 100vh;' . ($extraStyle !== '' ? ' ' . $extraStyle . ';' : '');
	?>
	<div class="w-100 pb-5" style="<?php echo htmlspecialchars($combinedStyle, ENT_QUOTES, 'UTF-8') ?>">
		<?php $this->load->view($view) ?>
	</div>
<?php endif ?>