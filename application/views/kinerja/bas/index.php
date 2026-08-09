<div class="callout callout-success">
	<h6>Keterangan</h6>
	<p>Upload BAS terhitung mulai Juni 2021</p>
</div>

<div class="table-responsive">
	<table id="table-performance" class="display">
		<thead>
			<tr>
				<th class="dt-center" rowspan="2">No.</th>
				<th rowspan="2">Nama</th>

				<!-- Range Group -->
				<th class="dt-center" colspan="4" id="range-header"><?php echo format_date(date('Y-m-d'), "MMMM yyyy") ?></th>

				<!-- Triwulan Ini Group -->
				<th class="dt-center" colspan="4">Triwulan <?php echo ceil(date('n') / 3) ?> <?php echo date('Y') ?></th>
			</tr>
			<tr>
				<!-- Range Columns -->
				<th class="dt-center text-rotate">Sidang</th>
				<th class="dt-center text-rotate">Sudah</th>
				<th class="dt-center text-rotate">Belum</th>
				<th class="dt-center text-rotate">Skor</th>

				<!-- Triwulan Ini Columns -->
				<th class="dt-center text-rotate">Sidang</th>
				<th class="dt-center text-rotate">Sudah</th>
				<th class="dt-center text-rotate">Belum</th>
				<th class="dt-center text-rotate">Skor</th>
			</tr>
		</thead>
		<tbody>
			<!-- Data will be loaded via AJAX -->
		</tbody>
		<tfoot class="collapse">
			<td colspan="2" class="dt-right">Total</td>
			<!-- Range Totals -->
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<!-- Triwulan Ini Totals -->
			<td></td>
			<td></td>
			<td></td>
			<td></td>
		</tfoot>
	</table>
</div>

<div id="chartRange" style="height: 500px;"></div>
<div id="chartYearToDate" style="height: 500px;"></div>

<script src="<?php echo asset_url('assets/plugins/chart.js/Chart.min.js') ?>"></script>
<script src="<?php echo asset_url('assets/plugins/chart.js/chartjs-plugin-datalabels.js') ?>"></script>
<script src="<?php echo asset_url('assets/plugins/chart.js/init.js') ?>"></script>
<script src="<?php echo asset_url('assets/plugins/html2canvas/html2canvas.min.js') ?>"></script>

<script type="text/javascript">
	$(document).ready(function() {
		<?php
		$triwulan = get_current_triwulan_range();
		$triwulanRange = $triwulan['start'] . '|' . $triwulan['end'];
		?>

		const title = 'Kinerja BAS';
		const theTime = '<?php echo time() ?>';
		const isDevelopment = <?php echo is_development() ? 1 : 0 ?>;
		const parse = (val) => parseInt(val || 0);
		let chartInstances = {};
		let tableData = [];

		function prepareData(pendingKey, submittedKey, skorKey, filters = {}) {
			const searchTerm = (filters.search || '').toString().toLowerCase();

			// Convert DataTable API objects to plain objects
			const plainData = [];
			for (let i = 0; i < tableData.length; i++) {
				const item = tableData[i];
				// Handle both plain objects and DataTable API objects
				const plainItem = {};

				// Try to get data using different methods
				if (typeof item === 'object' && item !== null) {
					// If it's a DataTable API object, extract properties
					const keys = ['panitera_nama', pendingKey, submittedKey, skorKey];
					keys.forEach(key => {
						if (item[key] !== undefined) {
							plainItem[key] = item[key];
						}
					});
				}

				plainData.push(plainItem);
			}

			const combined = plainData
				.filter(item => {
					const searchMatch = item.panitera_nama && item.panitera_nama.toLowerCase().includes(searchTerm);
					return searchMatch;
				})
				.map(item => {
					const pending = parseInt(item[pendingKey] ?? 0);
					const submitted = parseInt(item[submittedKey] ?? 0);
					const skor = parseFloat(item[skorKey] ?? '0');
					const entry = {
						name: item.panitera_nama,
						pending,
						submitted,
						total: pending + submitted,
						skor: skor.toFixed(2)
					};
					return entry;
				});

			// Sort descending by total
			combined.sort((a, b) => b.total - a.total);

			// Create skorMap manually to avoid Object.fromEntries issue
			const skorMap = {};
			combined.forEach(item => {
				skorMap[item.name] = item.skor;
			});

			return {
				labels: combined.map(item => item.name),
				pending: combined.map(item => item.pending),
				submitted: combined.map(item => item.submitted),
				skorMap: skorMap
			};
		}

		// 🔁 Reusable chart render with destroy
		function createChart(canvasId, title, dataSet, stacked = true) {
			// Check if container exists
			const container = $('#' + canvasId);
			if (container.length === 0) {
				return;
			}

			if (chartInstances[canvasId]) {
				chartInstances[canvasId].destroy();
			}

			// Check if initChart function exists
			if (typeof initChart === 'undefined') {
				// Try to create chart manually as fallback
				return createManualChart(canvasId, title, dataSet, stacked);
			}

			try {
				chartInstances[canvasId] = initChart(canvasId, {
					title: title,
					customChartTypes: ['bar', 'pie', 'doughnut'],
					data: {
						labels: dataSet.labels,
						datasets: [{
								label: 'Sudah Input/Unggah',
								data: dataSet.submitted,
								backgroundColor: '#34d399',
								...(stacked && {
									stack: 'stack1'
								})
							},
							{
								label: 'Belum Input/Unggah',
								data: dataSet.pending,
								backgroundColor: '#f87171',
								...(stacked && {
									stack: 'stack1'
								})
							}
						]
					},
					options: {
						axisTitles: {
							x: 'Panitera Pengganti',
							y: 'Jumlah BAS'
						},
						plugins: {
							tooltip: {
								callbacks: {
									title: function(context) {
										const name = context[0].label;
										const skor = dataSet.skorMap[name] || '0';
										return `${name} (Skor: ${skor})`;
									},
									label: function(context) {
										const pending = context.chart.data.datasets[1].data[context.dataIndex];
										const submitted = context.chart.data.datasets[0].data[context.dataIndex];
										const total = pending + submitted;
										const value = context.raw;
										const percentage = total > 0 ? ((value / total) * 100).toFixed(1) + '%' : '0%';
										return `${context.dataset.label}: ${value} (${percentage})`;
									}
								}
							}
						},
					}
				});
			} catch (error) {
				// Try fallback method
				createManualChart(canvasId, title, dataSet, stacked);
			}
		}

		// Fallback chart creation method
		function createManualChart(canvasId, title, dataSet, stacked = true) {
			try {
				const ctx = document.getElementById(canvasId);
				if (!ctx) {
					return;
				}

				// Destroy existing chart if any
				if (chartInstances[canvasId]) {
					chartInstances[canvasId].destroy();
				}

				chartInstances[canvasId] = new Chart(ctx, {
					type: 'bar',
					data: {
						labels: dataSet.labels,
						datasets: [{
							label: 'Sudah Input/Unggah',
							data: dataSet.submitted,
							backgroundColor: '#34d399',
							...(stacked && {
								stack: 'stack1'
							})
						}, {
							label: 'Belum Input/Unggah',
							data: dataSet.pending,
							backgroundColor: '#f87171',
							...(stacked && {
								stack: 'stack1'
							})
						}]
					},
					options: {
						responsive: true,
						maintainAspectRatio: false,
						plugins: {
							title: {
								display: true,
								text: title,
								font: {
									size: 16
								}
							},
							tooltip: {
								callbacks: {
									title: function(context) {
										const name = context[0].label;
										const skor = dataSet.skorMap[name] || '0';
										return `${name} (Skor: ${skor})`;
									},
									label: function(context) {
										const pending = context.chart.data.datasets[1].data[context.dataIndex];
										const submitted = context.chart.data.datasets[0].data[context.dataIndex];
										const total = pending + submitted;
										const value = context.raw;
										const percentage = total > 0 ? ((value / total) * 100).toFixed(1) + '%' : '0%';
										return `${context.dataset.label}: ${value} (${percentage})`;
									}
								}
							}
						},
						scales: {
							x: {
								title: {
									display: true,
									text: 'Panitera Pengganti'
								}
							},
							y: {
								title: {
									display: true,
									text: 'Jumlah BAS'
								},
								beginAtZero: true
							}
						}
					}
				});

			} catch (error) {
				// Error creating manual chart
			}
		}

		function renderCharts(filters = {}) {
			if (!tableData || tableData.length === 0) {
				return;
			}

			try {
				// Get the current range header text for chart title
				const rangeHeaderText = $('#range-header').text();

				createChart("chartRange", "Kinerja BAS " + rangeHeaderText, prepareData(
					'pending_bas',
					'uploaded_bas',
					'percentage_skor',
					filters
				));

				createChart("chartYearToDate", "Kinerja BAS Triwulan <?php echo ceil(date('n') / 3) ?> <?php echo date('Y') ?>", prepareData(
					'pending_bas_triwulan_ini',
					'uploaded_bas_triwulan_ini',
					'percentage_skor_triwulan_ini',
					filters
				));
			} catch (error) {
				// Error rendering charts
			}
		}

		function createUrl(type, ppId, data, month) {
			const selectedPutusStatus = $(`.dropdown-putus-status-${theTime} select`).val() || '';
			const baseUrl = '<?php echo base_url('kinerja/bas/bas_list/') ?>';
			const statusParam = selectedPutusStatus ? `/${selectedPutusStatus}` : '';
			const url = `${baseUrl}${type}/${ppId}/${month}${statusParam}`.replace(/\|/g, "%7C");
			return data <= 0 ? `<span class="text-muted">${data}</span>` : `<a href="${url}" class="btn-modal">${data}</a>`;
		}

		var columns = [{
				data: null,
				title: 'No.',
				className: 'dt-right',
				render: function(data, type, row, meta) {
					return meta.row + meta.settings._iDisplayStart + 1;
				}
			}, {
				data: 'panitera_nama',
				title: 'Panitera Pengganti',
				className: 'text-nowrap',
			},
			// Range columns
			{
				data: 'jumlah_sidang',
				title: 'Sidang',
				className: 'dt-center text-rotate',
				render: function(data, type, row) {
					return createUrl('sidang', row.panitera_id, data, 'range/' + ($('.daterange_bas input.form-control').val() || '<?php echo date('Y-m-01') . '|' . date('Y-m-d') ?>'));
				}
			},
			{
				data: 'uploaded_bas',
				title: 'Input/Unggah',
				className: 'dt-center text-rotate',
				render: function(data, type, row) {
					return createUrl('uploaded', row.panitera_id, data, 'range/' + ($('.daterange_bas input.form-control').val() || '<?php echo date('Y-m-01') . '|' . date('Y-m-d') ?>'));
				}
			}, {
				data: 'pending_bas',
				title: 'Sisa',
				className: 'dt-center text-rotate',
				render: function(data, type, row) {
					return createUrl('pending', row.panitera_id, data, 'range/' + ($('.daterange_bas input.form-control').val() || '<?php echo date('Y-m-01') . '|' . date('Y-m-d') ?>'));
				}
			}, {
				data: 'percentage_skor',
				title: 'Skor',
				className: 'dt-center text-rotate',
				render: function(data, type, row, meta) {
					var colorClass = 'text-danger';
					if (data >= 100) colorClass = 'text-success';
					else if (data >= 90) colorClass = 'text-primary';
					else if (data >= 75) colorClass = 'text-info';
					else if (data >= 50) colorClass = 'text-warning';
					return '<span class="fw-bold h5 ' + colorClass + '">' + data + '%</span>';
				}
			},
			// Triwulan ini columns
			{
				data: 'jumlah_sidang_triwulan_ini',
				title: 'Sidang',
				className: 'dt-center text-rotate',
				render: function(data, type, row) {
					return createUrl('sidang', row.panitera_id, data, 'range/' + ($('.daterange_bas input.form-control').val() || '<?php echo $triwulanRange ?>'));
				}
			},
			{
				data: 'uploaded_bas_triwulan_ini',
				title: 'Input/Unggah',
				className: 'dt-center text-rotate',
				render: function(data, type, row) {
					return createUrl('uploaded', row.panitera_id, data, 'range/' + ($('.daterange_bas input.form-control').val() || '<?php echo $triwulanRange ?>'));
				}
			}, {
				data: 'pending_bas_triwulan_ini',
				title: 'Sisa',
				className: 'dt-center text-rotate',
				render: function(data, type, row) {
					return createUrl('pending', row.panitera_id, data, 'range/' + ($('.daterange_bas input.form-control').val() || '<?php echo $triwulanRange ?>'));
				}
			}, {
				data: 'percentage_skor_triwulan_ini',
				title: 'Skor',
				className: 'dt-center text-rotate',
				render: function(data, type, row, meta) {
					var colorClass = 'text-danger';
					if (data >= 100) colorClass = 'text-success';
					else if (data >= 90) colorClass = 'text-primary';
					else if (data >= 75) colorClass = 'text-info';
					else if (data >= 50) colorClass = 'text-warning';
					return '<span class="fw-bold h5 ' + colorClass + '">' + data + '%</span>';
				}
			},
		];

		let table = initDataTable('#table-performance', {
			title: "<?php echo $title ?>",
			scroller: false,
			showSearchField: false,
			columns: columns,
			ajax: {
				url: "<?php echo base_url("kinerja/bas/get_performance") ?>",
				type: "POST",
				data: function(d) {
					d['selectedRange'] = $('.daterange_bas input.form-control').val();
					d['putusStatus'] = $(`.dropdown-putus-status-${theTime} select`).val();
					d[localStorage.getItem('csrfName')] = localStorage.getItem('csrfToken');
					return d;
				},
				dataSrc: function(json) {
					if (json.data && Array.isArray(json.data)) {
						tableData = json.data;
					} else {
						tableData = [];
					}
					setTimeout(function() {
						renderCharts({
							search: $('.dt-search input').val() || ''
						});
					}, 500);
					return json.data || [];
				}
			},
			layout: {
				topStart: {
					buttons: [{
							extend: 'daterange',
							config: {
								id: "daterange_bas",
								colSearch: 2,
								format: 'YYYY-MM-DD',
								opens: 'right',
								placeholder: 'Tanggal Sidang',
								refreshCharts: true
							}
						},
						{
							extend: 'dropdown',
							config: {
								id: 'dropdown-putus-status-' + theTime,
								class: 'dropdown-putus-status-' + theTime,
								placeholder: 'Status Putusan',
								options: {
									1: 'Belum Putus',
									2: 'Sudah Putus',
								},
							},
						}
					]
				}
			},
			drawCallback: function(settings) {
				setTimeout(function() {
					let selectedRange = $('.daterange_bas input.form-control').val();
					if (selectedRange) {
						let dates = selectedRange.split('|');
						if (dates.length === 2) {
							let startDate = new Date(dates[0]);
							let endDate = new Date(dates[1]);
							let formattedStart = ('0' + startDate.getDate()).slice(-2) + '/' +
								('0' + (startDate.getMonth() + 1)).slice(-2) + '/' + startDate.getFullYear();
							let formattedEnd = ('0' + endDate.getDate()).slice(-2) + '/' +
								('0' + (endDate.getMonth() + 1)).slice(-2) + '/' + endDate.getFullYear();
							$('#range-header').text((formattedStart === formattedEnd) ? formattedStart : formattedStart + ' - ' + formattedEnd);
						}
					}
					setTimeout(function() {
						try {
							const table = $('#table-performance').DataTable();
							const freshData = table.rows().data().toArray();
							if (freshData && freshData.length > 0) {
								tableData = freshData;
								renderCharts({
									search: $('.dt-search input').val() || ''
								});
							}
						} catch (error) {
							// Error in drawCallback chart update
						}
					}, 300);
				}, 50);
			},
			footerCallback: function(row, data, start, end, display) {
				const api = this.api();

				const parseNumber = (val) => {
					if (!val) return 0;
					let cleaned = val.toString().replace(/<[^>]*>/g, '');
					cleaned = cleaned.replace(/[^\d.-]/g, '');
					return parseFloat(cleaned) || 0;
				};

				const types = ['sidang', 'uploaded', 'pending', ''];
				const totals = {};

				// Range columns (2-5)
				for (let i = 2; i <= 5; i++) {
					totals[i] = api.column(i, {
							page: 'all'
						}).data()
						.reduce((sum, value) => sum + parseNumber(value), 0);
					const selectedRange = $('.daterange_bas input.form-control').val() || '<?php echo date('Y-m-01') . '|' . date('Y-m-d') ?>';
					const url = createUrl(types[(i - 2) % 4], '0', totals[i], 'range/' + selectedRange);
					$(api.column(i).footer()).html(url);
				}

				// Triwulan ini columns (6-9)
				for (let i = 6; i <= 9; i++) {
					totals[i] = api.column(i, {
							page: 'all'
						}).data()
						.reduce((sum, value) => sum + parseNumber(value), 0);
					const url = createUrl(types[(i - 6) % 4], '0', totals[i], 'range/<?php echo $triwulanRange ?>');
					$(api.column(i).footer()).html(url);
				}

				// Skor columns (overwrite with computed percentage)
				if (totals[2] > 0) $(api.column(5).footer()).html(((totals[3] / totals[2]) * 100).toFixed(2) + '%');
				if (totals[6] > 0) $(api.column(9).footer()).html(((totals[7] / totals[6]) * 100).toFixed(2) + '%');

				if (totals[2] > 0 || totals[6] > 0) {
					$('tfoot').slideDown();
				} else {
					$('tfoot').slideUp();
				}
			},
			columnDefs: [{
				"targets": [0, 2, 3, 4, 5, 6, 7, 8, 9],
				"className": "dt-center"
			}, {
				"targets": [2, 3, 4, 5, 6, 7, 8, 9],
				"className": "dt-striped"
			}, {
				"targets": [1],
				"className": "text-nowrap"
			}, {
				targets: [5, 9],
				render: function(data, type, row, meta) {
					var colorClass = 'text-danger';
					if (data >= 100) colorClass = 'text-success';
					else if (data >= 90) colorClass = 'text-primary';
					else if (data >= 75) colorClass = 'text-info';
					else if (data >= 50) colorClass = 'text-warning';
					return '<span class="fw-bold h5 ' + colorClass + '">' + data + '%</span>';
				}
			}],
		});

		// Initial chart render after table is loaded
		setTimeout(function() {
			if (tableData && tableData.length > 0) {
				renderCharts({
					search: ''
				});
			}
		}, 2000); // Increased timeout to ensure data is loaded

		// Also try to get data directly from DataTable after initialization
		setTimeout(function() {
			try {
				const table = $('#table-performance').DataTable();
				const data = table.data();

				if (data && data.length > 0) {
					tableData = data;
					renderCharts({
						search: $('.dt-search input').val() || ''
					});
				}
			} catch (error) {
				// Error getting data from DataTable
			}
		}, 3000);
	});
</script>