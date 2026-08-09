<?php
$fieldName    = isset($field['name'])         ? $field['name']         : 'password';
$fieldId      = isset($field['id'])           ? $field['id']           : $fieldName;
$placeholder  = isset($field['placeholder'])  ? $field['placeholder']  : '';
$required     = isset($field['required']) && $field['required'] ? ' required' : '';
$autocomplete = isset($field['autocomplete']) ? $field['autocomplete'] : 'current-password';
$label        = isset($field['label'])        ? $field['label']        : 'Kata Sandi';
$inputClass   = isset($field['inputClass'])   ? $field['inputClass']   : 'form-control';
$minlength    = isset($field['minlength'])    ? $field['minlength']    : false; //$this->config->item('min_password_length', 'ion_auth');
$pattern      = isset($field['pattern'])      ? $field['pattern']      : (!empty($minlength) ? '^.{' . $minlength . '}.*$' : false);
$value        = isset($field['value'])        ? htmlspecialchars($field['value'], ENT_QUOTES, 'UTF-8') : '';
$icon         = isset($field['icon'])         ? $field['icon']
	: '<svg viewBox="0 0 24 24"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 018 0v4"/></svg>';

// Collect extra attributes for input
$knownKeys = [
	'name',
	'id',
	'placeholder',
	'required',
	'autocomplete',
	'label',
	'inputClass',
	'type',
	'value',
	'help',
	'divClass',
	'visible',
	'class',
	'icon',
	'minlength',
	'pattern'
];
$extraAttrs = '';
foreach ($field as $key => $value_raw) {
	if (in_array(strtolower($key), $knownKeys)) continue;
	if (is_null($value_raw) || $value_raw === false) continue;
	if ($value_raw === true) {
		$extraAttrs .= ' ' . htmlspecialchars($key, ENT_QUOTES, 'UTF-8');
	} else {
		$extraAttrs .= ' ' . htmlspecialchars($key, ENT_QUOTES, 'UTF-8')
			. '="' . htmlspecialchars($value_raw, ENT_QUOTES, 'UTF-8') . '"';
	}
}

$inputStyle = !empty($icon) ? 'style="padding-left: 2.75rem;"' : '';
?>

<style>
	/* Form group */
	/* .form-group {
		width: 100%;
	} */

	/* Form label */
	/* .form-label {
		display: block;
		font-size: 0.8rem;
		font-weight: 600;
		color: var(--text-mid);
		letter-spacing: 0.04em;
		text-transform: uppercase;
		margin-bottom: 0.5rem;
	} */

	/* Form input */
	/* .form-input {
		width: 100%;
		border: 1.5px solid var(--border);
		border-radius: 10px;
		font-family: 'Source Sans 3', sans-serif;
		font-size: 0.95rem;
		background: var(--surface-alt);
		transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
		outline: none;
		-webkit-appearance: none;
	} */

	/* .form-input::placeholder {
		color: #a0b0a5;
		font-weight: 300;
	} */

	/* .form-input:hover {
		border-color: var(--border-strong);
		background: #fff;
	} */

	/* .form-input:focus {
		border-color: var(--green-500);
		background: #fff;
		box-shadow: 0 0 0 3px rgba(58, 130, 81, .12);
	} */

	/* .form-input.error {
		border-color: #f87171;
		background: #fff8f8;
	} */

	/* Password toggle button */
	.toggle-password {
		position: absolute;
		right: 12px;
		background: none;
		border: none;
		cursor: pointer;
		color: var(--text-muted);
		display: flex;
		align-items: center;
		padding: 4px;
		border-radius: 6px;
		transition: color 0.15s;
		min-width: 32px;
		min-height: 32px;
		justify-content: center;
		z-index: 2;
	}

	.toggle-password:hover {
		color: var(--text-mid);
	}

	.toggle-password svg {
		width: 17px;
		height: 17px;
		stroke: currentColor;
		fill: none;
		stroke-width: 1.8;
		stroke-linecap: round;
		stroke-linejoin: round;
	}

	/* CSS Variables (matching login.php) */
	:root {
		--text-dark: #0e1f13;
		--text-mid: #2e4835;
		--text-muted: #5a7a65;
		--surface: #ffffff;
		--surface-alt: #f5faf7;
		--border: rgba(35, 82, 48, 0.18);
		--border-strong: rgba(35, 82, 48, 0.35);
		--green-500: #3a8251;
	}
</style>

<div class="form-group">
	<div class="input-wrap">
		<?php if (!empty($icon)): ?>
			<span class="input-icon"><?php echo $icon; ?></span>
		<?php endif; ?>

		<input
			type="password"
			id="<?php echo htmlspecialchars($fieldId, ENT_QUOTES, 'UTF-8'); ?>"
			name="<?php echo htmlspecialchars($fieldName, ENT_QUOTES, 'UTF-8'); ?>"
			class="<?php echo htmlspecialchars($inputClass, ENT_QUOTES, 'UTF-8'); ?> form-input"
			placeholder="<?php echo htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8'); ?>"
			autocomplete="<?php echo htmlspecialchars($autocomplete, ENT_QUOTES, 'UTF-8'); ?>"
			minlength="<?php echo htmlspecialchars($minlength, ENT_QUOTES, 'UTF-8'); ?>"
			<?php if (!empty($pattern)): ?>pattern="<?php echo htmlspecialchars($pattern, ENT_QUOTES, 'UTF-8'); ?>" <?php endif; ?>
			<?php if (!empty($value)): ?>value="<?php echo $value; ?>" <?php endif; ?>
			<?php echo $required; ?>
			<?php echo $inputStyle; ?>
			<?php echo $extraAttrs; ?> />

		<button type="button" class="toggle-password"
			data-target="#<?php echo htmlspecialchars($fieldId, ENT_QUOTES, 'UTF-8'); ?>"
			aria-label="Tampilkan kata sandi"
			aria-pressed="false">
			<svg viewBox="0 0 24 24">
				<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
				<circle cx="12" cy="12" r="3" />
			</svg>
		</button>
	</div>

	<?php if (!empty($field['help'])): ?>
		<p class="field-hint mb-0 small text-muted"><?php echo htmlspecialchars($field['help'], ENT_QUOTES, 'UTF-8'); ?></p>
	<?php endif; ?>
</div>

<script>
	(function() {
		var fieldId = <?php echo json_encode($fieldId); ?>;
		var toggleBtn = document.querySelector('[data-target="#' + fieldId + '"]');
		var pwInput = document.getElementById(fieldId);
		var eyeIcon = toggleBtn ? toggleBtn.querySelector('svg') : null;

		if (!toggleBtn || !pwInput || !eyeIcon) return;

		var eyeOpen = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
		var eyeClosed = '<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19M1 1l22 22"/>';

		toggleBtn.addEventListener('click', function() {
			var show = pwInput.type === 'password';
			pwInput.type = show ? 'text' : 'password';
			eyeIcon.innerHTML = show ? eyeClosed : eyeOpen;
			toggleBtn.setAttribute('aria-label', show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
			toggleBtn.setAttribute('aria-pressed', show ? 'true' : 'false');
		});
	})();
</script>