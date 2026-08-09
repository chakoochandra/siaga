<?php
// Determine if this is a multiple select
$is_multiple = isset($field['multiple']) && $field['multiple'];
$field['placeholder'] = isset($field['options'][null]) ? $field['options'][null] : (isset($field['placeholder']) ? $field['placeholder'] : '');
?>

<?php
// Sanitize the field ID to remove special characters that are invalid in CSS class names
$sanitized_field_id = preg_replace('/[^a-zA-Z0-9_-]/', '_', $field['id']);
$unique = $sanitized_field_id . time();
?>
<?php $field['class'] .= ' myselect2_' . $unique ?>

<?php
// Add multiple attribute if needed
if ($is_multiple) {
	$field['multiple'] = 'multiple';
}
echo form_dropdown($field);
?>

<script type="text/javascript">
	$(function() {
		let isAjax = <?php echo isset($field['ajaxUrl']) ? 1 : 0 ?>;
		let isMultiple = <?php echo $is_multiple ? 1 : 0 ?>;
		let uniqueId = 'myselect2_<?php echo $unique ?>';
		let optionsSelect = {
			theme: 'bootstrap-5',
			dropdownParent: $('.' + uniqueId).closest('.modal').length ? $('.' + uniqueId).closest('.modal') : $(document.body),
			allowClear: <?php echo !isset($field['allowClear']) || $field['allowClear'] ? 1 : 0 ?>,
			placeholder: '<?php echo isset($field['placeholder']) ? $field['placeholder'] : 'Pilih' ?>',
			multiple: isMultiple,
			templateResult: function(data) {
				if (!data.id) {
					return data.text; // Placeholder
				}

				// Replace literal '\n' or '\\n' with <br>
				var text = '';
				if (typeof data.text === 'string' && data.text.trim() !== '') {
					text = data.text.replace(/\\n/g, '<br>').replace(/\n/g, '<br>');
				}

				return $('<span>' + text + '</span>');
			},
			templateSelection: function(data) {
				if (!data.id) {
					return data.text;
				}

				var text = '';
				if (typeof data.text === 'string' && data.text.trim() !== '') {
					text = data.text.replace(/\\n/g, '. ').replace(/\n/g, '. ');
				}
				return $('<span>' + text + '</span>');
			},
			escapeMarkup: function(markup) {
				return markup; // Allow HTML (br) inside select2
			},
			minimumResultsForSearch: 1 // Show search box when there's at least 1 option
		};

		if (isAjax) {
			$('.' + uniqueId + ' + span.select2').ready(function() {
				$(this).addClass('form-control');
			})

			optionsSelect = Object.assign(optionsSelect, {
				ajax: {
					url: '<?php echo isset($field['ajaxUrl']) ? $field['ajaxUrl'] : '#' ?>',
					dataType: 'json',
					delay: 250,
					data: function(params) {
						return {
							keyword: params.term
						};
					},
					processResults: function(response) {
						return {
							results: response.data
						};
					},
					cache: true
				}
			});
		}

		$('.' + uniqueId).select2(optionsSelect);

		// Add scroll event listener to close dropdown if user scrolls while it's open
		// This prevents positioning issues after scrolling in modals or main content
		let scrollTimer;
		$(window).on('scroll.select2_' + uniqueId, function() {
			if ($('.' + uniqueId).data('select2') && $('.' + uniqueId).data('select2').isOpen()) {
				// Use a timer to avoid closing immediately during scroll
				clearTimeout(scrollTimer);
				scrollTimer = setTimeout(function() {
					$('.' + uniqueId).select2('close');
				}, 150);
			}
		});

		// Also close dropdown when modal scrolls
		$('#modal-input .modal-body').on('scroll.select2_' + uniqueId, function() {
			if ($('.' + uniqueId).data('select2') && $('.' + uniqueId).data('select2').isOpen()) {
				clearTimeout(scrollTimer);
				scrollTimer = setTimeout(function() {
					$('.' + uniqueId).select2('close');
				}, 150);
			}
		});

		// Clean up event listeners when element is removed or select2 is destroyed
		$('.' + uniqueId).on('select2:unselecting select2:destroy', function() {
			$(window).off('scroll.select2_' + uniqueId);
			$('#modal-input .modal-body').off('scroll.select2_' + uniqueId);
		});

		if (isAjax) {
			let selectedValue = '<?php echo isset($field['selectedValue']) ? $field['selectedValue'] : '' ?>';
			let selectedText = '<?php echo isset($field['selectedText']) ? $field['selectedText'] : '' ?>';

			var $newOption = $("<option selected='selected'></option>").val(selectedValue).text(selectedText);
			$('.' + uniqueId).append($newOption).trigger('change');
		}
	})
</script>