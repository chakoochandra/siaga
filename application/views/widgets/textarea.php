<?php

/**
 * Textarea Widget
 * Provides a textarea field that automatically handles AJAX autocomplete if the data-autocomplete-url attribute is present
 *
 * Expected field attributes:
 * - name: Field name
 * - value: Field value
 * - [other]: Other standard textarea attributes
 * - data-autocomplete-url: URL to fetch autocomplete suggestions (optional)
 * - rows: Number of rows for the textarea (default: 3)
 */

// Extract attributes for the textarea
$fieldName = isset($field['name']) ? $field['name'] : 'textarea_field';
$fieldValue = isset($field['value']) ? $field['value'] : '';
$autocompleteUrl = isset($field['data-autocomplete-url']) ? $field['data-autocomplete-url'] : '';
$placeholder = isset($field['placeholder']) ? $field['placeholder'] : '';
$classes = isset($field['class']) ? $field['class'] : '';
$rows = isset($field['rows']) ? $field['rows'] : 3;

// Add required class if not already present
if (strpos($classes, 'form-control') === false) {
	$classes .= ' form-control';
}

// Check if field is required
$is_required = isset($field['required']) && $field['required'] === true;

// Prepare attributes for the textarea
$attributes = [
	'name' => $fieldName,
	'rows' => $rows,
	'placeholder' => $placeholder,
	'class' => trim($classes),
];

// Add required attribute if field is required
if ($is_required) {
	$attributes['required'] = 'required';
}

// Add the textarea value separately (not as an attribute)
$textareaValue = $fieldValue;

// Add any additional attributes that were passed in the field array
foreach ($field as $key => $value) {
	if (!in_array($key, ['name', 'value', 'placeholder', 'class', 'rows', 'data-autocomplete-url', 'type'])) {
		$attributes[$key] = $value;
	}
}
?>

<?php if (!$autocompleteUrl): ?>
	<?php $attributes['autocomplete'] = 'on'; // Enable browser autocomplete if not using custom
	?>
	<?php echo form_textarea($attributes, $textareaValue) ?>
<?php else: ?>
	<?php
	$attributes['autocomplete'] = 'off'; // Disable browser autocomplete if using custom
	if ($autocompleteUrl) {
		$attributes['data-autocomplete-url'] = $autocompleteUrl;
	}
	// Add required attribute if field is required (for autocomplete case)
	if ($is_required) {
		$attributes['required'] = 'required';
	}
	?>
	<style>
		.autocomplete-textarea-wrapper {
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

	<?php
	$attributes['autocomplete'] = 'off'; // Disable browser autocomplete if using custom
	if ($autocompleteUrl) {
		$attributes['data-autocomplete-url'] = $autocompleteUrl;
	}
	?>

	<div class="autocomplete-textarea-wrapper" id="autocomplete-textarea-<?php echo $fieldName; ?>">
		<?php echo form_textarea($attributes, $textareaValue) ?>
		<?php if ($autocompleteUrl): ?>
			<div class="autocomplete-suggestions" id="suggestions-<?php echo $fieldName; ?>" style="display: none;"></div>
		<?php endif; ?>
	</div>

	<script>
		function setupAutocomplete_<?php echo str_replace('-', '_', $fieldName); ?>() {
			// Wait for the element to be available in the DOM
			const autocompleteTextarea = document.querySelector('[name="<?php echo $fieldName; ?>"]');
			const suggestionsBox = document.getElementById('suggestions-<?php echo $fieldName; ?>');
			const autocompleteUrl = '<?php echo addslashes($autocompleteUrl); ?>';

			let activeIndex = -1;
			let debounceTimer;
			let currentRequest = null;
			let currentSuggestions = []; // Store current suggestions
			let currentQuery = ''; // Store the query that generated current suggestions
			let cachedResults = {}; // Cache to store results for different queries
			let isFocused = true; // Track if the field is currently focused (default to true initially)

			if (!autocompleteTextarea || !autocompleteUrl) {
				console.log('Autocomplete textarea or URL not found for <?php echo $fieldName; ?>');
				return false;
			}

			// Fetch suggestions on initial load
			if (autocompleteUrl) {
				// Fetch all suggestions initially (empty query)
				// This will cache the suggestions but only display them if the input is focused
				fetchAutocompleteSuggestions('');
			}

			// Set focus state when field receives focus
			autocompleteTextarea.addEventListener('focus', function() {
				isFocused = true;

				// If the field is empty and we have cached suggestions, show them
				if (autocompleteTextarea.value.trim() === '' && cachedResults['']) {
					currentQuery = '';
					currentSuggestions = cachedResults[''];
					displayAutocompleteSuggestions(cachedResults[''], '');
				}
			});

			// Clear cache when field loses focus for a period of time
			autocompleteTextarea.addEventListener('blur', function() {
				isFocused = false;
				// Clear cache after a delay if field remains unfocused
				// Don't clear the empty query cache which was loaded on initial load
				setTimeout(() => {
					if (!isFocused) {
						// Keep the empty query cache, clear other cached results
						const emptyQueryCache = cachedResults[''] || null;
						cachedResults = {};
						if (emptyQueryCache) {
							cachedResults[''] = emptyQueryCache;
						}
					}
				}, 5000); // Clear cache after 5 seconds of being unfocused
			});

			// Add event listeners
			autocompleteTextarea.addEventListener('input', function(e) {
				const value = e.target.value.trim();
				activeIndex = -1;

				// Clear previous timer
				clearTimeout(debounceTimer);

				// Cancel previous AJAX request if still pending
				if (currentRequest) {
					currentRequest.abort();
				}

				// Check if we have cached results for this exact query
				if (cachedResults[value.toLowerCase()]) {
					// Use cached results directly
					currentQuery = value;
					currentSuggestions = cachedResults[value.toLowerCase()];
					displayAutocompleteSuggestions(currentSuggestions, value);
					return;
				}

				// If the input is empty, show cached initial suggestions instead of hiding
				if (!value && cachedResults['']) {
					currentQuery = '';
					currentSuggestions = cachedResults[''];
					displayAutocompleteSuggestions(cachedResults[''], '');
					return;
				} else if (!value) {
					// If input is empty but no cached results, hide suggestions
					suggestionsBox.style.display = 'none';
					currentQuery = '';
					currentSuggestions = [];
					return;
				}

				// Function to update highlighting of existing suggestions based on current query
				function updateHighlighting(query) {
					if (!query) return;

					const regex = new RegExp('(' + escapeRegex(query) + ')', 'gi');
					const items = suggestionsBox.querySelectorAll('.suggestion-item');

					for (let i = 0; i < items.length; i++) {
						const item = items[i];
						const originalLabel = item.getAttribute('data-original-label') || item.textContent;
						const highlightedLabel = originalLabel.replace(regex, '<span class="highlight-autocomplete">$1</span>');
						item.innerHTML = highlightedLabel;
						// Store the original label for future highlighting updates
						item.setAttribute('data-original-label', originalLabel);
					}
				}

				// Determine whether to filter cached results or fetch new ones
				// We filter when user is adding characters (currentQuery is prefix of new value)
				if (currentSuggestions.length > 0 && value.toLowerCase().startsWith(currentQuery.toLowerCase())) {
					// User is adding characters, filter the cached results
					filterSuggestions(value);
					// Update highlighting in existing suggestions
					updateHighlighting(value);
					return;
				}

				// For all other cases (deleting characters, completely different query, etc.), fetch new data
				// This ensures we get all possible matches when user removes characters

				// Keep showing suggestions while fetching new data if the new query might have matches in the current list
				// This happens when deleting characters (e.g. from "Ket" to "Ke")
				// Don't hide if we have current suggestions that might match the new query
				if (currentSuggestions.length > 0) {
					// Check if any of the current suggestions start with the new query
					const hasPotentialMatches = currentSuggestions.some(item => {
						const label = typeof item === 'string' ? item.label : (item.label || item.value || '');
						return label.toLowerCase().startsWith(value.toLowerCase());
					});
					if (!hasPotentialMatches) {
						// No potential matches in current list, hide suggestions while fetching
						suggestionsBox.style.display = 'none';
					} else {
						// Update highlighting in existing suggestions
						updateHighlighting(value);
					}
					// If there are potential matches, keep suggestions visible while fetching new data
				} else {
					// No current suggestions, hide while fetching
					suggestionsBox.style.display = 'none';
				}

				// Debounce AJAX call (wait 300ms after user stops typing)
				debounceTimer = setTimeout(() => {
					fetchAutocompleteSuggestions(value);
				}, 300);
			});

			function filterSuggestions(query) {
				// Filter current suggestions based on the new query
				// Show all suggestions if query is empty
				let filteredSuggestions = [];
				if (query === '') {
					displayFilteredSuggestions(currentSuggestions, query);
					// Update currentQuery when query is empty
					currentQuery = query;
				} else {
					filteredSuggestions = currentSuggestions.filter(item => {
						const label = typeof item === 'string' ? item.label : (item.label || item.value || '');
						return label.toLowerCase().startsWith(query.toLowerCase());
					});

					if (filteredSuggestions.length > 0) {
						displayFilteredSuggestions(filteredSuggestions, query);
					} else {
						// If filter results in no matches, hide the suggestions and fetch new data from the server silently
						suggestionsBox.style.display = 'none';
						// Clear previous timer
						clearTimeout(debounceTimer);
						// Debounce AJAX call (wait 300ms after user stops typing)
						debounceTimer = setTimeout(() => {
							fetchAutocompleteSuggestions(query);
						}, 300);
					}
					// Update currentQuery when filtering
					currentQuery = query;
				}
			}

			function fetchAutocompleteSuggestions(query) {
				currentRequest = new XMLHttpRequest();

				currentRequest.onreadystatechange = function() {
					if (this.readyState === 4) {
						if (this.status === 200) {
							try {
								const data = JSON.parse(this.responseText);
								// Store the original query and suggestions
								currentQuery = query;
								currentSuggestions = data;
								// Cache the results for this query but only display if it's not the initial empty query
								cachedResults[query.toLowerCase()] = data;

								// Check if the input field is currently focused before displaying suggestions
								if (document.activeElement === autocompleteTextarea) {
									// Display suggestions if input is focused (for both initial load and regular queries)
									displayAutocompleteSuggestions(data, query);
								}
							} catch (e) {
								console.error('Error parsing autocomplete JSON for <?php echo $fieldName; ?>:', e);
								// Hide the suggestions box on error instead of showing error message
								suggestionsBox.style.display = 'none';
							}
						} else if (this.status !== 0) { // 0 means aborted
							// Hide the suggestions box on error instead of showing error message
							suggestionsBox.style.display = 'none';
						}
						currentRequest = null;
					}
				};

				currentRequest.open('GET', autocompleteUrl + '?term=' + encodeURIComponent(query), true);
				currentRequest.send();
			}

			function displayAutocompleteSuggestions(data, query) {
				if (!data || data.length === 0) {
					// Hide the suggestions box when there are no results instead of showing "Data tidak ditemukan"
					suggestionsBox.style.display = 'none';
					return;
				}

				const regex = new RegExp('(' + escapeRegex(query) + ')', 'gi');

				let html = '';
				for (let i = 0; i < data.length; i++) {
					const item = data[i];
					// Handle both string items and object items with label/value properties
					const label = typeof item === 'string' ? item : (item.label || item.value || '');
					const value = typeof item === 'string' ? item : (item.value || item.label || '');

					if (!label) continue; // Skip items without a label

					const highlightedLabel = label.replace(regex, '<span class="highlight-autocomplete">$1</span>');
					html += `<div class="suggestion-item" data-value="${escapeHtml(value)}" data-original-label="${escapeHtml(label)}">${highlightedLabel}</div>`;
				}

				suggestionsBox.innerHTML = html;
				suggestionsBox.style.display = 'block'; // Make sure the suggestions are visible

				// Add click events to suggestions
				const items = suggestionsBox.querySelectorAll('.suggestion-item');
				for (let i = 0; i < items.length; i++) {
					items[i].addEventListener('click', function() {
						selectAutocompleteItem(items[i]);
					});
				}
			}

			function displayFilteredSuggestions(data, query) {
				if (!data || data.length === 0) {
					suggestionsBox.innerHTML = '<div class="suggestion-item">Data tidak ditemukan</div>';
					suggestionsBox.style.display = 'block'; // Make sure it's visible even with no results
					return;
				}

				const regex = new RegExp('(' + escapeRegex(query) + ')', 'gi');

				let html = '';
				for (let i = 0; i < data.length; i++) {
					const item = data[i];
					// Handle both string items and object items with label/value properties
					const label = typeof item === 'string' ? item : (item.label || item.value || '');
					const value = typeof item === 'string' ? item : (item.value || item.label || '');

					if (!label) continue; // Skip items without a label

					const highlightedLabel = label.replace(regex, '<span class="highlight-autocomplete">$1</span>');
					html += `<div class="suggestion-item" data-value="${escapeHtml(value)}" data-original-label="${escapeHtml(label)}">${highlightedLabel}</div>`;
				}

				suggestionsBox.innerHTML = html;
				suggestionsBox.style.display = 'block'; // Make sure the suggestions are visible

				// Add click events to suggestions
				const items = suggestionsBox.querySelectorAll('.suggestion-item');
				for (let i = 0; i < items.length; i++) {
					items[i].addEventListener('click', function() {
						selectAutocompleteItem(items[i]);
					});
				}
			}

			function selectAutocompleteItem(item) {
				const value = item.getAttribute('data-value');
				autocompleteTextarea.value = value;
				suggestionsBox.style.display = 'none';
				autocompleteTextarea.focus(); // Keep focus on the element
			}

			// Keyboard navigation
			autocompleteTextarea.addEventListener('keydown', function(e) {
				const items = suggestionsBox.querySelectorAll('.suggestion-item');

				if (e.key === 'ArrowDown') {
					e.preventDefault();
					activeIndex = Math.min(activeIndex + 1, items.length - 1);
					updateActiveAutocompleteItem(items);
				} else if (e.key === 'ArrowUp') {
					e.preventDefault();
					activeIndex = Math.max(activeIndex - 1, -1);
					updateActiveAutocompleteItem(items);
				} else if (e.key === 'Enter') {
					e.preventDefault();
					if (activeIndex >= 0 && items[activeIndex]) {
						selectAutocompleteItem(items[activeIndex]);
					}
				} else if (e.key === 'Escape') {
					suggestionsBox.style.display = 'none';
					activeIndex = -1;
				}
			});

			function updateActiveAutocompleteItem(items) {
				for (let i = 0; i < items.length; i++) {
					items[i].classList.toggle('active', i === activeIndex);
				}
				if (items[activeIndex]) {
					items[activeIndex].scrollIntoView({
						block: 'nearest'
					});
				}
			}

			// Close suggestions when clicking outside
			document.addEventListener('click', function(e) {
				if (!e.target.closest('#autocomplete-textarea-<?php echo $fieldName; ?>')) {
					suggestionsBox.style.display = 'none';
				}
			});

			// Helper functions
			function escapeHtml(text) {
				const div = document.createElement('div');
				div.textContent = text;
				return div.innerHTML;
			}

			function escapeRegex(text) {
				return text.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
			}

			return true;
		}

		// Execute the setup immediately
		setupAutocomplete_<?php echo str_replace('-', '_', $fieldName); ?>();

		// Also set up to run when DOM is loaded in case script executes before element is rendered
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', function() {
				setupAutocomplete_<?php echo str_replace('-', '_', $fieldName); ?>();
			});
		}
	</script>
<?php endif ?>