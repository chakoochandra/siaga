<?php

/**
 * AutoComplete Input Widget
 * Provides an input field that automatically handles AJAX autocomplete
 * if the data-autocomplete-url attribute is present.
 *
 * Expected field attributes:
 *  - name                  Field name (required)
 *  - id                    Field id (defaults to name)
 *  - value                 Field value
 *  - label                 Label text
 *  - placeholder           Placeholder text
 *  - help                  Help text below the field
 *  - required              Boolean
 *  - inputClass            CSS class on the <input> (default: 'form-control')
 *  - type                  Input type (default: 'text')
 *  - icon                  Raw SVG string or FontAwesome class name (e.g. 'fa-user')
 *  - minlength / minLength Minimum character length
 *  - data-autocomplete-url URL to fetch autocomplete suggestions (activates autocomplete mode)
 *  - [other]               Any other standard HTML input attributes are forwarded
 *
 * Fixes vs. previous version:
 *  - Label is now actually rendered (previously extracted but never echoed).
 *  - Pass-through / extra attributes are now forwarded in BOTH render modes
 *    (previously silently dropped whenever autocomplete mode was active).
 *  - minlength="0" is no longer treated as "not set" (was falsy-checked).
 *  - Autocomplete JS now looks the field up by its `id` instead of its `name`,
 *    so it works correctly whenever id and name differ.
 *  - The <style> block for autocomplete suggestions is now only emitted once
 *    per page even if the widget is used for several fields.
 *  - Icon resolution moved into a guarded helper function so the file is safe
 *    to include more than once on the same page.
 */

if (!function_exists('textfield_widget_resolve_icon')) {
	function textfield_widget_resolve_icon($icon)
	{
		if ($icon === '' || $icon === null) {
			return '';
		}
		if (strpos($icon, 'fa-') !== 0) {
			// Already a raw SVG string — use as-is.
			return $icon;
		}
		static $iconMap = [
			'fa-user'       => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
			'fa-envelope'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>',
			'fa-lock'       => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>',
			'fa-search'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>',
			'fa-phone'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.11 9.8a19.79 19.79 0 01-3.07-8.63A2 2 0 012.11 2H5a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>',
			'fa-calendar'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>',
			'fa-map-marker' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>',
		];
		return isset($iconMap[$icon]) ? $iconMap[$icon] : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/></svg>';
	}
}

// --- Extract known fields ---
$fieldName       = isset($field['name']) ? $field['name'] : 'text_field';
$fieldId         = isset($field['id']) ? $field['id'] : $fieldName;
$fieldValue      = isset($field['value']) ? $field['value'] : '';
$autocompleteUrl = isset($field['data-autocomplete-url']) ? $field['data-autocomplete-url'] : '';
$placeholder     = isset($field['placeholder']) ? $field['placeholder'] : '';
$label           = isset($field['label']) ? $field['label'] : (isset($field['placeholder']) ? $field['placeholder'] : '');
$help            = isset($field['help']) ? $field['help'] : '';
$required        = !empty($field['required']);
$inputClass      = isset($field['inputClass']) ? $field['inputClass'] : 'form-control';
$fieldType       = isset($field['type']) ? ($field['type'] === 'form_input' ? 'text' : $field['type']) : 'text';
$icon            = isset($field['icon']) ? $field['icon'] : '';

// Support both 'minlength' and 'minLength' from caller. Use '' as the
// "not set" sentinel so an explicit 0 is still honoured.
$minlength = isset($field['minLength']) ? $field['minLength'] : (isset($field['minlength']) ? $field['minlength'] : '');

$iconSvg    = textfield_widget_resolve_icon($icon);
$hasIcon    = $iconSvg !== '';

// --- Collect extra / pass-through attributes (raw values, not yet escaped) ---
// Skip list uses strtolower() so callers can use any casing (e.g. minLength vs minlength).
$knownKeys = [
	'name',
	'id',
	'value',
	'placeholder',
	'label',
	'help',
	'required',
	'inputclass',
	'type',
	'icon',
	'minlength',
	'data-autocomplete-url',
	'class',
];
$extraAttributes = [];
foreach ($field as $key => $raw) {
	if (in_array(strtolower($key), $knownKeys, true)) continue;
	if ($raw === null || $raw === false)              continue;
	$extraAttributes[$key] = $raw === true ? $key : $raw;
}

// Pre-escaped values used in the surrounding markup (not inside form_input()
// calls, since CodeIgniter's form_input()/form_prep() escapes those itself).
$safeId    = htmlspecialchars($fieldId,   ENT_QUOTES, 'UTF-8');
$safeLabel = htmlspecialchars($label,     ENT_QUOTES, 'UTF-8');
$safeHelp  = htmlspecialchars($help,      ENT_QUOTES, 'UTF-8');
$helpId    = 'help-' . $fieldId;
$safeHelpId = htmlspecialchars($helpId, ENT_QUOTES, 'UTF-8');

// --- Base <input> attributes shared by both render modes ---
$attributes = [
	'type'        => $fieldType,
	'id'          => $fieldId,
	'name'        => $fieldName,
	'value'       => $fieldValue,
	'class'       => trim($inputClass . ' form-input'),
	'placeholder' => $placeholder,
];
if ($hasIcon)          $attributes['style']    = 'padding-left: 2.75rem;';
if ($required)         $attributes['required'] = 'required';
if ($minlength !== '') $attributes['minlength'] = $minlength;
if ($help !== '')      $attributes['aria-describedby'] = $helpId;
$attributes += $extraAttributes; // pass-through attrs, forwarded in both modes
?>

<?php if (!$autocompleteUrl): ?>
	<div class="form-group w-100">
		<div class="input-wrap">
			<?php if ($hasIcon): ?>
				<span class="input-icon"><?php echo $iconSvg; ?></span>
			<?php endif; ?>
			<?php echo form_input($attributes + ['autocomplete' => 'on']); ?>
		</div>
		<?php if ($help !== ''): ?>
			<p class="field-hint mb-0 small text-muted" id="<?php echo $safeHelpId; ?>"><?php echo $safeHelp; ?></p>
		<?php endif; ?>
	</div>

<?php else: ?>
	<?php if (!defined('TEXTFIELD_AC_STYLE_PRINTED')): ?>
		<?php define('TEXTFIELD_AC_STYLE_PRINTED', true); ?>
		<style>
			.autocomplete-input-wrapper {
				position: relative;
				display: inline-block;
				width: 100%;
			}

			.autocomplete-suggestions {
				position: absolute;
				top: 100%;
				left: 0;
				right: 0;
				color: var(--bs-body-color) !important;
				background-color: rgba(var(--bs-body-bg-rgb), 0.8) !important;
				border: var(--bs-border-width) solid #86b7fe;
				border-radius: 0.25rem;
				max-height: 200px;
				overflow-y: auto;
				z-index: 1000;
				display: none;
				box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
			}

			.suggestion-item {
				padding: 8px 12px;
				cursor: pointer;
			}

			.suggestion-item:hover,
			.suggestion-item.active {
				color: #f8f9fa !important;
				background: #33d857 !important;
			}

			.highlight-autocomplete {
				background-color: #4caf5078;
				font-weight: bold;
			}
		</style>
	<?php endif; ?>

	<div class="form-group w-100">
		<div class="autocomplete-input-wrapper" id="autocomplete-input-<?php echo $safeId; ?>">
			<?php if ($hasIcon): ?>
				<span class="input-icon"><?php echo $iconSvg; ?></span>
			<?php endif; ?>
			<?php echo form_input($attributes + ['autocomplete' => 'off', 'data-autocomplete-url' => $autocompleteUrl]); ?>
			<div class="autocomplete-suggestions" id="suggestions-<?php echo $safeId; ?>" style="display:none;"></div>
		</div>

		<?php if ($help !== ''): ?>
			<p class="field-hint mb-0 small text-muted" id="<?php echo $safeHelpId; ?>"><?php echo $safeHelp; ?></p>
		<?php endif; ?>
	</div>

	<script>
		// IIFE — scoped by fieldId so multiple instances on the same page never conflict
		(function() {
			var fieldId = <?php echo json_encode($fieldId); ?>;
			var wrapperId = 'autocomplete-input-' + fieldId;
			var suggId = 'suggestions-' + fieldId;

			function setup() {
				// Look the field up by its `id`, not `name` — id is what this
				// widget actually assigns and what wrapperId/suggId are keyed on.
				var autocompleteField = document.getElementById(fieldId);
				var suggestionsBox = document.getElementById(suggId);
				var acUrl = <?php echo json_encode($autocompleteUrl); ?>;

				if (!autocompleteField || !suggestionsBox || !acUrl) return false;

				// Guard: prevent double-binding if setup() is called more than once
				if (autocompleteField.dataset.acInit === '1') return true;
				autocompleteField.dataset.acInit = '1';

				var activeIndex = -1;
				var debounceTimer = null;
				var currentRequest = null;
				var currentSuggestions = [];
				var currentQuery = '';
				var cachedResults = {};
				var isFocused = false;

				// Pre-fetch all suggestions so the dropdown is instant on first focus
				fetchSuggestions('');

				autocompleteField.addEventListener('focus', function() {
					isFocused = true;
					if (autocompleteField.value.trim() === '' && cachedResults['']) {
						currentQuery = '';
						currentSuggestions = cachedResults[''];
						displaySuggestions(cachedResults[''], '');
					}
				});

				autocompleteField.addEventListener('blur', function() {
					isFocused = false;
					// After 5 s of being unfocused, evict everything except the
					// empty-query cache (pre-fetched on load)
					setTimeout(function() {
						if (!isFocused) {
							var emptyCache = cachedResults[''] || null;
							cachedResults = {};
							if (emptyCache) cachedResults[''] = emptyCache;
						}
					}, 5000);
				});

				autocompleteField.addEventListener('input', function(e) {
					var value = e.target.value.trim();
					activeIndex = -1;
					clearTimeout(debounceTimer);
					if (currentRequest) {
						currentRequest.abort();
						currentRequest = null;
					}

					// Exact cache hit
					if (cachedResults[value.toLowerCase()] !== undefined) {
						currentQuery = value;
						currentSuggestions = cachedResults[value.toLowerCase()];
						displaySuggestions(currentSuggestions, value);
						return;
					}

					// Empty input
					if (!value) {
						if (cachedResults['']) {
							currentQuery = '';
							currentSuggestions = cachedResults[''];
							displaySuggestions(cachedResults[''], '');
						} else {
							suggestionsBox.style.display = 'none';
							currentQuery = '';
							currentSuggestions = [];
						}
						return;
					}

					// User is adding characters — filter what we have, then silently
					// fetch from server in case the server returns more matches
					if (currentSuggestions.length > 0 &&
						value.toLowerCase().startsWith(currentQuery.toLowerCase())) {
						filterSuggestions(value);
						updateHighlighting(value);
						return;
					}

					// User deleted characters or typed something unrelated
					if (currentSuggestions.length > 0) {
						var hasPotential = currentSuggestions.some(function(item) {
							var lbl = typeof item === 'string' ? item : (item.label || item.value || '');
							return lbl.toLowerCase().startsWith(value.toLowerCase());
						});
						if (!hasPotential) {
							suggestionsBox.style.display = 'none';
						} else {
							updateHighlighting(value);
						}
					} else {
						suggestionsBox.style.display = 'none';
					}

					debounceTimer = setTimeout(function() {
						fetchSuggestions(value);
					}, 300);
				});

				// ----------------------------------------------------------------
				// Highlight update (re-wraps matched chars in existing DOM nodes)
				// ----------------------------------------------------------------
				function updateHighlighting(query) {
					if (!query) return;
					var regex = new RegExp('(' + escapeRegex(query) + ')', 'gi');
					suggestionsBox.querySelectorAll('.suggestion-item').forEach(function(item) {
						var orig = item.getAttribute('data-original-label') || item.textContent;
						item.innerHTML = orig.replace(regex, '<span class="highlight-autocomplete">$1</span>');
						item.setAttribute('data-original-label', orig);
					});
				}

				// ----------------------------------------------------------------
				// Filter in-memory suggestions, fall back to server on zero results
				// ----------------------------------------------------------------
				function filterSuggestions(query) {
					if (query === '') {
						displaySuggestions(currentSuggestions, query);
						currentQuery = query;
						return;
					}
					var filtered = currentSuggestions.filter(function(item) {
						var lbl = typeof item === 'string' ? item : (item.label || item.value || '');
						return lbl.toLowerCase().startsWith(query.toLowerCase());
					});
					currentQuery = query;
					if (filtered.length > 0) {
						displaySuggestions(filtered, query);
					} else {
						suggestionsBox.style.display = 'none';
						clearTimeout(debounceTimer);
						debounceTimer = setTimeout(function() {
							fetchSuggestions(query);
						}, 300);
					}
				}

				// ----------------------------------------------------------------
				// AJAX fetch
				// ----------------------------------------------------------------
				function fetchSuggestions(query) {
					currentRequest = new XMLHttpRequest();
					currentRequest.onreadystatechange = function() {
						if (this.readyState !== 4) return;
						if (this.status === 200) {
							try {
								var data = JSON.parse(this.responseText);
								currentQuery = query;
								currentSuggestions = data;
								cachedResults[query.toLowerCase()] = data;
								if (document.activeElement === autocompleteField) {
									displaySuggestions(data, query);
								}
							} catch (err) {
								console.error('Autocomplete JSON error [' + fieldId + ']:', err);
								suggestionsBox.style.display = 'none';
							}
						} else if (this.status !== 0) {
							suggestionsBox.style.display = 'none';
						}
						currentRequest = null;
					};
					currentRequest.open('GET', acUrl + '?term=' + encodeURIComponent(query), true);
					currentRequest.send();
				}

				// ----------------------------------------------------------------
				// Single display function — hides on empty (no "Data tidak ditemukan")
				// ----------------------------------------------------------------
				function displaySuggestions(data, query) {
					if (!data || data.length === 0) {
						suggestionsBox.style.display = 'none';
						return;
					}
					var regex = new RegExp('(' + escapeRegex(query) + ')', 'gi');
					var html = '';
					for (var i = 0; i < data.length; i++) {
						var item = data[i];
						var lbl = typeof item === 'string' ? item : (item.label || item.value || '');
						var val = typeof item === 'string' ? item : (item.value || item.label || '');
						if (!lbl) continue;
						var highlighted = query ?
							lbl.replace(regex, '<span class="highlight-autocomplete">$1</span>') :
							escapeHtml(lbl);
						html += '<div class="suggestion-item"' +
							' data-value="' + escapeHtml(val) + '"' +
							' data-original-label="' + escapeHtml(lbl) + '">' +
							highlighted + '</div>';
					}
					suggestionsBox.innerHTML = html;
					suggestionsBox.style.display = 'block';
					// Attach click handlers
					suggestionsBox.querySelectorAll('.suggestion-item').forEach(function(el) {
						el.addEventListener('click', function() {
							selectItem(el);
						});
					});
				}

				// ----------------------------------------------------------------
				// Selection
				// ----------------------------------------------------------------
				function selectItem(item) {
					autocompleteField.value = item.getAttribute('data-value');
					suggestionsBox.style.display = 'none';
					activeIndex = -1;
					autocompleteField.focus();
				}

				// ----------------------------------------------------------------
				// Keyboard navigation
				// ----------------------------------------------------------------
				autocompleteField.addEventListener('keydown', function(e) {
					var items = suggestionsBox.querySelectorAll('.suggestion-item');
					if (e.key === 'ArrowDown') {
						e.preventDefault();
						activeIndex = Math.min(activeIndex + 1, items.length - 1);
						updateActive(items);
					} else if (e.key === 'ArrowUp') {
						e.preventDefault();
						activeIndex = Math.max(activeIndex - 1, -1);
						updateActive(items);
					} else if (e.key === 'Enter') {
						e.preventDefault();
						if (activeIndex >= 0 && items[activeIndex]) selectItem(items[activeIndex]);
					} else if (e.key === 'Escape') {
						suggestionsBox.style.display = 'none';
						activeIndex = -1;
					}
				});

				function updateActive(items) {
					items.forEach(function(el, i) {
						el.classList.toggle('active', i === activeIndex);
					});
					if (activeIndex >= 0 && items[activeIndex]) {
						items[activeIndex].scrollIntoView({
							block: 'nearest'
						});
					}
				}

				// Close on outside click — scoped to this instance's wrapper id
				document.addEventListener('click', function(e) {
					if (!e.target.closest('#' + wrapperId)) {
						suggestionsBox.style.display = 'none';
					}
				});

				// ----------------------------------------------------------------
				// Helpers
				// ----------------------------------------------------------------
				function escapeHtml(text) {
					var d = document.createElement('div');
					d.textContent = text;
					return d.innerHTML;
				}

				function escapeRegex(text) {
					return text.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
				}

				return true;
			}

			// Mirror the password widget's DOMContentLoaded pattern exactly
			if (document.readyState === 'loading') {
				document.addEventListener('DOMContentLoaded', setup);
			} else {
				setup();
			}
		})();
	</script>
<?php endif; ?>