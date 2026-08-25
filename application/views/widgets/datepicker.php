<style>
	input[type="form_datepicker"] {
		cursor: pointer;
	}
</style>

<div class="w-100" style="position: relative; display: flex; align-items: center;">
	<span style="position: absolute; left: 14px; color: var(--text-muted); display: flex; align-items: center; pointer-events: none;">
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width: 17px; height: 17px;">
			<rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
			<line x1="16" y1="2" x2="16" y2="6"></line>
			<line x1="8" y1="2" x2="8" y2="6"></line>
			<line x1="3" y1="10" x2="21" y2="10"></line>
		</svg>
	</span>
	<?php
	$field['style'] = 'padding-left: 2.75rem;';
	echo form_datepicker($field);
	?>
</div>

<script>
	$(document).ready(function() {
		function initDatepicker(fieldId, configOverrides = {}) {
			let format = '<?php echo isset($field['format']) ? $field['format'] : 'yyyy-mm-dd' ?>';
			let startDate = '<?php echo isset($field['startDate']) ? $field['startDate'] : '-75y' ?>';
			let endDate = '<?php echo isset($field['endDate']) ? $field['endDate'] : '+1y' ?>';
			let viewMode = '<?php echo isset($field['viewMode']) ? $field['viewMode'] : (isset($field['minViewMode']) ? $field['minViewMode'] : 'days') ?>';
			let minViewMode = '<?php echo isset($field['minViewMode']) ? $field['minViewMode'] : 'days' ?>';
			let isSemester = '<?php echo isset($field['isSemester']) ? $field['isSemester'] : false ?>' === '1';
			let disableWeekend = configOverrides.disableWeekend !== undefined ? configOverrides.disableWeekend : '<?php echo isset($field['disableWeekend']) ? $field['disableWeekend'] : true ?>' === '1';
			let disableFriday = configOverrides.disableFriday !== undefined ? configOverrides.disableFriday : '<?php echo isset($field['disableFriday']) ? $field['disableFriday'] : true ?>' === '0';
			let excludes = configOverrides.excludes !== undefined ? configOverrides.excludes : '<?php echo isset($field['excludes']) ? $field['excludes'] : null ?>';

			switch (format) {
				case 'yyyy':
					var options = {
						format: "yyyy",
						viewMode: "years",
						minViewMode: "years",
						autoclose: true,
						clearBtn: true,
						todayBtn: 'linked',
						todayHighlight: true,
						startDate: startDate,
						endDate: endDate,
					};
					break;

				default:
					var options = {
						format: format,
						viewMode: viewMode,
						minViewMode: minViewMode,
						autoclose: true,
						clearBtn: true,
						todayHighlight: true,
						todayBtn: minViewMode === 'days' ? 'linked' : false,
						startDate: startDate,
						endDate: endDate,
						beforeShowDay: function(date) {
							const mm = String(date.getMonth() + 1).padStart(2, '0');
							const dd = String(date.getDate()).padStart(2, '0');
							const mmdd = `${mm}-${dd}`;

							const isExcluded = excludes ? JSON.parse(excludes).some(item => {
								const parts = item.split('-'); // ["yyyy", "mm", "dd"]
								return parts[1] + '-' + parts[2] === mmdd;
							}) : false;

							if (isExcluded) return false;

							const day = date.getDay(); // 0 = Sunday, 5 = Friday, 6 = Saturday

							if (disableWeekend && (day === 0 || day === 6)) return false;
							if (disableFriday && day === 5) return false;

							return true;
						},
						beforeShowMonth: function(date) {
							if (isSemester) {
								const offset = date.getTimezoneOffset();
								date = new Date(date.getTime() - (offset * 60 * 1000));
								return $.inArray(date.getMonth(), [0, 6]) > -1; // Jan & Jul
							}
							return true;
						}
					};
					break;
			}

			const $datepicker = $('#' + fieldId);

			if ($datepicker.attr('readonly')) {
				// Initialize datepicker but disable it
				$datepicker.datepicker('destroy').datepicker(options).prop('readonly', true);

				// Additional handling to prevent datepicker from opening
				$datepicker.on('focus', function() {
					$datepicker.datepicker('hide');
				});
			} else {
				$datepicker.datepicker('destroy').datepicker(options);
			}
		}

		initDatepicker('<?php echo $field['id'] ?>');
	});
</script>