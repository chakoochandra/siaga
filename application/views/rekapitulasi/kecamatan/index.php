<div class="card leaves">
	<div class="card-header leaves align-items-center">
		<h5 class="m-0"><?php echo $title ?></h5>
	</div>
	<div class="card-body">
		<div class="table-responsive">
			<table id="table-kecamatan" class="display">
				<thead>
					<tr>
						<th class="dt-center" rowspan="2">No</th>
						<th class="dt-center" rowspan="2">Bulan</th>
						<th class="dt-center" rowspan="2">Jumlah</th>
						<th class="dt-center" colspan="<?php echo count($all_kecamatan) + 1 ?>">Kecamatan</th>
					</tr>
					<tr>
						<?php foreach ($all_kecamatan as $kec) : ?>
							<th class="text-rotate"><?php echo $kec ?></th>
						<?php endforeach ?>
						<th class="text-rotate">Luar <?php echo $kabupaten ?></th>
					</tr>
				</thead>
				<tfoot class="collapse">
					<tr>
						<td colspan="2" class="dt-right">Total</td>
						<?php for ($i = 0; $i < count($all_kecamatan) + 1; $i++): ?>
							<td class="dt-right"></td>
						<?php endfor ?>
						<td class="dt-right"></td>
					</tr>
				</tfoot>
			</table>
		</div>

		<div id="totalChart" style="height: 500px;"></div>
		<div id="monthlyCharts"></div>
	</div>
</div>

<script src="<?php echo asset_url('assets/plugins/chart.js/Chart.min.js') ?>"></script>
<script src="<?php echo asset_url('assets/plugins/chart.js/chartjs-plugin-datalabels.js') ?>"></script>
<script src="<?php echo asset_url('assets/plugins/chart.js/init.js') ?>"></script>
<script src="<?php echo asset_url('assets/plugins/html2canvas/html2canvas.min.js') ?>"></script>

<script type="text/javascript">
	$(document).ready(function() {
		const allJenisPerkara = <?php echo json_encode($all_jenis_perkara) ?>;
		const allAlurPerkara = <?php echo json_encode($all_alur_perkara) ?>;
		const allKecamatan = <?php echo json_encode($all_kecamatan) ?>;
		const allRadius = <?php echo json_encode($all_radius) ?>;
		const getYear = '<?php echo $this->uri->segment(4) ?: date('Y') ?>';
		const getEcourt = '<?php echo $this->uri->segment(5) ?: 0 ?>';
		const theTime = '<?php echo time() ?>';
		const urlStatistic = '<?php echo base_url("rekapitulasi/kecamatan/get_statistic") ?>';
		const urlList = '<?php echo base_url('rekapitulasi/kecamatan/perkara_list/') ?>';
		const title = '<?php echo $title ?>';

		function createUrl(month, data, column) {
			const selectedYear = $(`.table-kecamatan_datepicker input[type="text"]`).val() || getYear;
			const selectedEcourt = $(`.dropdown-status-${theTime} select`).val() || getEcourt;
			const selectedAlur = $(`.dropdown-alur-${theTime} select`).val() || 0;
			const selectedType = $(`.dropdown-jenis-${theTime} select`).val() || 0;
			return data <= 0 ? `<span class="text-muted">${data}</span>` : `<a href="${urlList}${column}/${selectedYear}/${month}/${selectedEcourt}/${selectedAlur}/${selectedType}" class="btn-modal">${data}</a>`;
		}

		var columns = [{
				data: null,
				className: 'dt-right',
				render: function(data, type, row, meta) {
					return meta.row + meta.settings._iDisplayStart + 1;
				}
			},
			{
				data: 'month',
				className: 'dt-right text-nowrap',
				render: function(data, type, row) {
					if (!data) return '';
					return moment().month(data - 1).format('MMMM') + ' ' + row.year;
				}
			},
			{
				data: 'total_perkara',
				className: 'dt-center',
				render: function(data, type, row) {
					return createUrl(row.month, data, 'total_perkara');
				}
			},
		];

		Object.entries(allKecamatan).forEach(([kode, nama]) => {
			const bucketKey = `kec_${nama.toLowerCase().replace(/\s+/g, '')}`;
			columns.push({
				data: bucketKey,
				className: `dt-center ${allRadius[kode] % 2 == 1?'dt-striped':''}`,
				render: function(data, type, row) {
					return createUrl(row.month, data, bucketKey);
				}
			});
		});

		columns.push({
			data: `kec_luar`,
			className: 'dt-center',
			render: function(data, type, row) {
				return createUrl(row.month, data, 'kec_luar');
			}
		});

		loadChartOverlay('totalChart');
		var table = initDataTable('#table-kecamatan', {
			title: title,
			ajax: {
				url: urlStatistic,
				data: function(d) {
					d['selectedYear'] = $(`.table-kecamatan_datepicker input[type="text"]`).val();
					d['selectedEcourt'] = $(`.dropdown-status-${theTime} select`).val();
					d['selectedAlur'] = $(`.dropdown-alur-${theTime} select`).val();
					d['selectedType'] = $(`.dropdown-jenis-${theTime} select`).val();
					d[localStorage.getItem('csrfName')] = localStorage.getItem('csrfToken');
				}
			},
			drawCallback: function(settings) {
				const json = settings.json;
				if (json) {
					drawTotalChart(json.data);
					drawAllMonthlyCharts(json.data);
				}
			},
			scroller: false,
			showSearchField: false,
			layout: {
				topStart: {
					buttons: [{
							extend: 'datepicker',
							config: {
								id: "table-kecamatan_datepicker",
								minViewMode: 'years',
								format: 'yyyy',
								placeholder: 'Tahun',
							},
						},
						{
							extend: 'dropdown',
							config: {
								id: 'dropdown-status-' + theTime,
								class: 'dropdown-status-' + theTime,
								placeholder: 'Status e-Court',
								options: {
									1: 'Ya',
									2: 'Tidak',
								},
								selected: getEcourt,
							},
						},
						{
							extend: 'dropdown',
							config: {
								id: 'dropdown-alur-' + theTime,
								class: 'dropdown-alur-' + theTime,
								placeholder: 'Alur Perkara',
								options: allAlurPerkara,
								width: 175,
							},
						},
						{
							extend: 'dropdown',
							config: {
								id: 'dropdown-jenis-' + theTime,
								class: 'dropdown-jenis-' + theTime,
								placeholder: 'Jenis Perkara',
								options: allJenisPerkara,
								width: 275,
							},
						},
					],
				},
			},
			columns: columns,
			footerCallback: function(row, data, start, end, display) {
				const api = this.api();
				for (let i = columns.findIndex(column => column.data === 'total_perkara'); i <= columns.findIndex(column => column.data === 'kec_luar'); i++) {
					const total = api
						.column(i, {
							page: 'all'
						})
						.data()
						.reduce((sum, value) => sum + parseFloat(value) || 0, 0);
					$(api.column(i).footer()).html(createUrl(0, total, columns[i].data));
				}

				const masuk = api
					.column(columns.findIndex(column => column.data === 'total_perkara'), {
						page: 'all'
					})
					.data()
					.reduce((sum, value) => sum + parseFloat(value) || 0, 0);
				if (masuk > 0) {
					$('tfoot').slideDown();
				} else {
					$('tfoot').slideUp();
				}
			}
		});

		let allMonthlyCharts = [];

		function drawAllMonthlyCharts(allData) {
			allMonthlyCharts = [];
			const container = document.getElementById('monthlyCharts');
			container.classList.add('d-flex', 'flex-column', 'justify-content-center', 'align-items-center');
			container.innerHTML = '';

			allData.forEach((monthData, index) => {
				const data = Object.entries(allKecamatan).map(([kode, kec]) => ({
					label: kec,
					value: parseInt(monthData[`kec_${kec.toLowerCase().replace(/\s+/g, '')}`] || 0)
				}));

				data.sort((a, b) => b.value - a.value);

				const chartWrapper = document.createElement('div');
				const chartId = `chartContainer_${index}`;
				chartWrapper.style.height = '500px';
				chartWrapper.id = chartId;
				container.appendChild(chartWrapper);

				const chart = initChart(chartId, {
					title: `Perkara Bulan ${moment(`${monthData.year}-${monthData.month}`, 'YYYY-M').format('MMMM YYYY')}`,
					customChartTypes: ['bar', 'pie', 'doughnut'],
					data: {
						labels: data.map(d => d.label),
						datasets: [{
							data: data.map(d => d.value),
						}]
					},
					options: {
						axisTitles: {
							x: 'Kecamatan',
							y: 'Jumlah Penerimaan Perkara'
						}
					},
				});

				allMonthlyCharts.push(chart);
			});
		}

		function drawTotalChart(allData) {
			let totalCounts = {};
			Object.keys(allKecamatan).forEach(kode => {
				totalCounts[kode] = 0;
			});
			allData.forEach(row => {
				Object.entries(allKecamatan).forEach(([kode, nama]) => {
					totalCounts[kode] += parseInt(row[`kec_${nama.toLowerCase().replace(/\s+/g, '')}`] || 0);
				});
			});

			const sorted = Object.entries(totalCounts)
				.map(([kode, value]) => ({
					label: allKecamatan[kode],
					value
				}))
				.sort((a, b) => b.value - a.value);

			initChart('totalChart', {
				title: 'Total Perkara per Kecamatan',
				customChartTypes: ['bar', 'pie', 'doughnut'],
				data: {
					labels: sorted.map(d => d.label),
					datasets: [{
						data: sorted.map(d => d.value),
					}]
				},
				options: {
					axisTitles: {
						x: 'Kecamatan',
						y: 'Jumlah Penerimaan Perkara'
					}
				}
			});
		}
	});
</script>