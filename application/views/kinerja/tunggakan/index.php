<div class="card leaves">
	<div class="card-header leaves align-items-center">
		<h5 class="m-0"><?php echo $title ?></h5>
	</div>
	<div class="card-body">
		<div class="table-responsive">
			<table id="table-tunggakan" class="display">
				<thead>
					<tr>
						<th rowspan="2">No.</th>
						<th rowspan="2">Ketua Majelis</th>
						<th rowspan="1" colspan="3" class="text-sm">&ge; 5 Bulan</th>
						<th rowspan="1" colspan="3" class="text-sm">&ge; 4 Bulan & < 5 Bulan</th>
						<th rowspan="1" colspan="3" class="text-sm">&ge; 3 Bulan & < 4 Bulan</th>
						<th rowspan="1" colspan="3" class="text-sm">
							< 3 Bulan</th>
						<th rowspan="1" colspan="3" class="text-sm">Total</th>
					</tr>
					<tr>
						<th class="text-sm">Ghaib</th>
						<th class="text-sm">Non Ghaib</th>
						<th class="text-sm">Semua</th>
						<th class="text-sm">Ghaib</th>
						<th class="text-sm">Non Ghaib</th>
						<th class="text-sm">Semua</th>
						<th class="text-sm">Ghaib</th>
						<th class="text-sm">Non Ghaib</th>
						<th class="text-sm">Semua</th>
						<th class="text-sm">Ghaib</th>
						<th class="text-sm">Non Ghaib</th>
						<th class="text-sm">Semua</th>
						<th class="text-sm">Ghaib</th>
						<th class="text-sm">Non Ghaib</th>
						<th class="text-sm">Semua</th>
					</tr>
				</thead>
				<tfoot class="collapse">
					<tr>
						<td></td>
						<td class="dt-right"></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>

	<script type="text/javascript">
		$(document).ready(function() {
			const constTime = '<?php echo time() ?>';
			const initialYear = '<?php echo date('Y') ?>';

			function createUrl(bucket, hakimId, data, year, status = 'all') {
				const url = status !== 'all' ?
					`<?php echo base_url('kinerja/tunggakan/perkara_list/') ?>${bucket}/${hakimId}/${year}/${status}` :
					`<?php echo base_url('kinerja/tunggakan/perkara_list/') ?>${bucket}/${hakimId}/${year}`;

				return data <= 0 ?
					`<span class="text-muted">${data}</span>` :
					`<a href="${url}" class="btn-modal">${data}</a>`;
			}

			var columns = [{
					data: null,
					className: 'dt-right',
					render: function(data, type, row, meta) {
						return meta.row + meta.settings._iDisplayStart + 1;
					}
				},
				{
					data: 'hakim_nama',
					className: 'text-nowrap',
					render: function(data, type, row) {
						const kode = row.hakim_kode ? ` (${row.hakim_kode})` : '';
						return `${data || '-'}` + kode;
					}
				},
				{
					data: 'ge_5_ghaib',
					className: 'dt-center',
					render: function(data, type, row) {
						const selectedYear = $(`.table-tunggakan_yearpicker input[type="text"]`).val() || initialYear;
						return createUrl('ge_5', row.hakim_id, parseInt(data || 0), selectedYear, 'ghaib');
					}
				},
				{
					data: 'ge_5_non_ghaib',
					className: 'dt-center',
					render: function(data, type, row) {
						const selectedYear = $(`.table-tunggakan_yearpicker input[type="text"]`).val() || initialYear;
						return createUrl('ge_5', row.hakim_id, parseInt(data || 0), selectedYear, 'non_ghaib');
					}
				},
				{
					data: 'ge_5',
					className: 'dt-center dt-striped',
					render: function(data, type, row) {
						const selectedYear = $(`.table-tunggakan_yearpicker input[type="text"]`).val() || initialYear;
						return createUrl('ge_5', row.hakim_id, parseInt(data || 0), selectedYear);
					}
				},
				{
					data: 'gt_4_ghaib',
					className: 'dt-center',
					render: function(data, type, row) {
						const selectedYear = $(`.table-tunggakan_yearpicker input[type="text"]`).val() || initialYear;
						return createUrl('gt_4', row.hakim_id, parseInt(data || 0), selectedYear, 'ghaib');
					}
				},
				{
					data: 'gt_4_non_ghaib',
					className: 'dt-center',
					render: function(data, type, row) {
						const selectedYear = $(`.table-tunggakan_yearpicker input[type="text"]`).val() || initialYear;
						return createUrl('gt_4', row.hakim_id, parseInt(data || 0), selectedYear, 'non_ghaib');
					}
				},
				{
					data: 'gt_4',
					className: 'dt-center dt-striped',
					render: function(data, type, row) {
						const selectedYear = $(`.table-tunggakan_yearpicker input[type="text"]`).val() || initialYear;
						return createUrl('gt_4', row.hakim_id, parseInt(data || 0), selectedYear);
					}
				},
				{
					data: 'ge_3_ghaib',
					className: 'dt-center',
					render: function(data, type, row) {
						const selectedYear = $(`.table-tunggakan_yearpicker input[type="text"]`).val() || initialYear;
						return createUrl('ge_3', row.hakim_id, parseInt(data || 0), selectedYear, 'ghaib');
					}
				},
				{
					data: 'ge_3_non_ghaib',
					className: 'dt-center',
					render: function(data, type, row) {
						const selectedYear = $(`.table-tunggakan_yearpicker input[type="text"]`).val() || initialYear;
						return createUrl('ge_3', row.hakim_id, parseInt(data || 0), selectedYear, 'non_ghaib');
					}
				},
				{
					data: 'ge_3',
					className: 'dt-center dt-striped',
					render: function(data, type, row) {
						const selectedYear = $(`.table-tunggakan_yearpicker input[type="text"]`).val() || initialYear;
						return createUrl('ge_3', row.hakim_id, parseInt(data || 0), selectedYear);
					}
				},
				{
					data: 'lt_3_ghaib',
					className: 'dt-center',
					render: function(data, type, row) {
						const selectedYear = $(`.table-tunggakan_yearpicker input[type="text"]`).val() || initialYear;
						return createUrl('lt_3', row.hakim_id, parseInt(data || 0), selectedYear, 'ghaib');
					}
				},
				{
					data: 'lt_3_non_ghaib',
					className: 'dt-center',
					render: function(data, type, row) {
						const selectedYear = $(`.table-tunggakan_yearpicker input[type="text"]`).val() || initialYear;
						return createUrl('lt_3', row.hakim_id, parseInt(data || 0), selectedYear, 'non_ghaib');
					}
				},
				{
					data: 'lt_3',
					className: 'dt-center dt-striped',
					render: function(data, type, row) {
						const selectedYear = $(`.table-tunggakan_yearpicker input[type="text"]`).val() || initialYear;
						return createUrl('lt_3', row.hakim_id, parseInt(data || 0), selectedYear);
					}
				},
				{
					data: 'total_ghaib',
					className: 'dt-center',
					render: function(data, type, row) {
						const selectedYear = $(`.table-tunggakan_yearpicker input[type="text"]`).val() || initialYear;
						return createUrl('all', row.hakim_id, parseInt(data || 0), selectedYear, 'ghaib');
					}
				},
				{
					data: 'total_non_ghaib',
					className: 'dt-center',
					render: function(data, type, row) {
						const selectedYear = $(`.table-tunggakan_yearpicker input[type="text"]`).val() || initialYear;
						return createUrl('all', row.hakim_id, parseInt(data || 0), selectedYear, 'non_ghaib');
					}
				},
				{
					data: 'total',
					className: 'dt-center dt-striped',
					render: function(data, type, row) {
						const selectedYear = $(`.table-tunggakan_yearpicker input[type="text"]`).val() || initialYear;
						return createUrl('all', row.hakim_id, parseInt(data || 0), selectedYear);
					}
				},
			];

			initDataTable('#table-tunggakan', {
				title: '<?php echo $title ?>',
				ajax: {
					url: "<?php echo base_url('kinerja/tunggakan/get_statistic') ?>",
					data: function(d) {
						d['selectedYear'] = $(`.table-tunggakan_yearpicker input[type="text"]`).val() || initialYear;
						d[localStorage.getItem('csrfName')] = localStorage.getItem('csrfToken');
					}
				},
				scroller: false,
				showSearchField: false,
				columns: columns,
				footerCallback: function(tfoot, data, start, end, display) {
					const api = this.api();

					const parseNumber = (val) => {
						if (val === null || val === undefined) return 0;
						if (typeof val === 'number') return val;
						let cleaned = val.toString().replace(/<[^>]*>/g, '');
						cleaned = cleaned.replace(/[^\d.-]/g, '');
						return parseFloat(cleaned) || 0;
					};

					// Check if table is properly initialized and has data
					if (!api || !api.columns || !data || data.length === 0) {
						console.warn('WARNING: Table not properly initialized or no data available');
						return;
					}

					// Get total number of columns and validate
					const totalColumns = api.columns().header().length;
					if (totalColumns === 0) {
						console.warn('WARNING: No columns found in table');
						return;
					}

					// Use direct DOM manipulation instead of api.column().footer() to avoid the .cell property error
					const footerCells = $(tfoot).find('th');
					if (footerCells.length > 1) {
						$(footerCells[1]).html('Total');
					}

					// Calculate totals for each bucket column
					const totals = [];
					for (let i = 2; i < totalColumns; i++) {
						let total = 0;
						try {
							const columnApi = api.column(i, {
								page: 'all'
							});
							if (columnApi && columnApi.data) {
								const columnData = columnApi.data().toArray();
								if (Array.isArray(columnData)) {
									const parsedValues = columnData.map(value => {
										const parsed = parseNumber(value);
										return parsed;
									});
									total = parsedValues.reduce((sum, value) => sum + value, 0);
								}
							}
						} catch (error) {
							console.error(`ERROR: Failed to calculate total for column ${i}`, {
								error: error,
								columnIndex: i,
								totalColumns: totalColumns,
								api: api,
								columnExists: i < totalColumns
							});
							total = 0;
						}
						totals[i] = total;
					}

					// Update footer cells with calculated totals and links
					const selectedYear = $(`.table-tunggakan_yearpicker input[type="text"]`).val() || initialYear;
					const bucketMap = {
						2: 'ge_5',
						3: 'ge_5',
						4: 'ge_5',
						5: 'gt_4',
						6: 'gt_4',
						7: 'gt_4',
						8: 'ge_3',
						9: 'ge_3',
						10: 'ge_3',
						11: 'lt_3',
						12: 'lt_3',
						13: 'lt_3',
						14: 'all',
						15: 'all',
						16: 'all'
					};
					const statusMap = {
						2: 'ghaib', // ge_5_ghaib
						3: 'non_ghaib', // ge_5_non_ghaib
						4: 'all', // ge_5
						5: 'ghaib', // gt_4_ghaib
						6: 'non_ghaib', // gt_4_non_ghaib
						7: 'all', // gt_4
						8: 'ghaib', // ge_3_ghaib
						9: 'non_ghaib', // ge_3_non_ghaib
						10: 'all', // ge_3
						11: 'ghaib', // lt_3_ghaib
						12: 'non_ghaib', // lt_3_non_ghaib
						13: 'all', // lt_3
						14: 'ghaib', // total_ghaib
						15: 'non_ghaib', // total_non_ghaib
						16: 'all' // total
					};

					for (let i = 2; i < totalColumns; i++) {
						const total = totals[i] || 0;
						const bucket = bucketMap[i] || 'all';
						const status = statusMap[i] || 'all';

						// Update footer cell directly using DOM manipulation
						if (footerCells.length > i) {
							// Use createUrl function for consistency
							const cellContent = createUrl(bucket, 0, total, selectedYear, status);
							$(footerCells[i]).html(cellContent);
						}
					}

					// Show footer if table has data or if any column has a total > 0
					const hasData = api.rows({
						page: 'current'
					}).data().length > 0 || totals.some(total => total > 0);
					if (hasData) {
						$('tfoot').slideDown();
					} else {
						$('tfoot').slideUp();
					}
				},
				columnDefs: [{
						"targets": [0, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16],
						"className": "dt-center"
					},
					{
						"targets": [1],
						"className": "text-nowrap"
					},
				],
			});

			// Reload on year change to refresh counts and links
			$(document).on('change', '.table-tunggakan_yearpicker input[type="text"]', function() {
				const table = $('#table-tunggakan').DataTable();
				table.ajax.reload(null, false);
			});
		});
	</script>