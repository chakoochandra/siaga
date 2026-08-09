<div class="card leaves">
	<div class="card-header leaves align-items-center">
		<h5 class="m-0"><?php echo $title ?></h5>
	</div>
	<div class="card-body">
		<div class="table-responsive">
			<table id="table-ecourt" class="display">
				<thead>
					<tr>
						<th class="dt-center" rowspan="2">No.</th>
						<th rowspan="2">Bulan</th>
						<th class="text-rotatess" rowspan="2">Sisa bulan Lalu</th>
						<th class="text-rotatess" rowspan="2">Diterima</th>
						<th class="text-rotatess" rowspan="2">Jumlah Bulan Ini</th>

						<th class="dt-center" colspan="9">Putusan/Penetapan</th>
						<th class="text-rotatess" rowspan="2">Sisa</th>
						<th class="text-rotatess" rowspan="2">Minutasi</th>
						<th rowspan="2">Persentase e-Court</th>
						<th class="exclude-export noVis" rowspan="2"></th>
					</tr>
					<tr>
						<th class="text-rotatess">Dicabut</th>
						<th class="text-rotatess">Dikabulkan</th>
						<th class="text-rotatess">Ditolak</th>
						<th class="text-rotatess">Tidak Diterima</th>
						<th class="text-rotatess">Digugurkan</th>
						<th class="text-rotatess">Dicoret Dari Register</th>
						<th class="text-rotatess">Perdamaian</th>
						<th class="text-rotatess">Lain-lain</th>
						<th class="text-rotatess">Jumlah</th>
					</tr>
				</thead>
				<tfoot class="collapse">
					<tr>
						<td colspan="3" class="dt-right">Total</td>
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
						<td class="exclude-export noVis"></td>
					</tr>
				</tfoot>
			</table>
		</div>

		<div id="stackedBarChart" style="height: 350px;"></div>
		<div id="lineChart" style="height: 350px;"></div>
	</div>
</div>

<script src="<?php echo asset_url('assets/plugins/chart.js/Chart.min.js') ?>"></script>
<script src="<?php echo asset_url('assets/plugins/chart.js/chartjs-plugin-datalabels.js') ?>"></script>
<script src="<?php echo asset_url('assets/plugins/chart.js/init.js') ?>"></script>
<script src="<?php echo asset_url('assets/plugins/html2canvas/html2canvas.min.js') ?>"></script>

<script type="text/javascript">
	$(document).ready(function () {
		const getYear = '<?php echo $this->uri->segment(4) ?: date('Y') ?>';
		const theTime = '<?php echo time() ?>';
		const urlList = '<?php echo base_url('rekapitulasi/keadaanperkara/perkara_list/') ?>';
		const urlStat = '<?php echo base_url("rekapitulasi/keadaanperkara/get_statistic/ecourt") ?>';
		const title = '<?php echo $title ?>';
		const alurPerkara = <?php echo json_encode($all_alur_perkara) ?>;
		const jenisPerkara = <?php echo json_encode($all_jenis_perkara) ?>;

		function createUrl(month, data, column) {
			const selectedYear = $(`.table-ecourt_datepicker input[type="text"]`).val() || getYear;
			const selectedAlur = $(`.dropdown-alur-${theTime} select`).val() || 0;
			const selectedType = $(`.dropdown-jenis-${theTime} select`).val() || 0;
			return data <= 0 ? `<span class="text-muted">${data}</span>` : `<a href="${urlList}${column}/${selectedYear}/${month}/1/${selectedAlur}/${selectedType}" class="btn-modal">${data}</a>`;
		}

		var columns = [{
			data: null,
			className: 'dt-right',
			render: function (data, type, row, meta) {
				return meta.row + meta.settings._iDisplayStart + 1;
			}
		}, {
			data: 'month',
			className: 'dt-center text-nowrap',
			render: function (data, type, row) {
				if (!data) return '';
				return moment().month(data - 1).format('MMMM') + ' ' + row.year;
			}
		}, {
			data: 'sisa_bulan_lalu',
			className: 'dt-center',
			render: function (data, type, row) {
				return createUrl(row.month, data, 'sisa_bulan_lalu');
			}
		}, {
			data: 'masuk',
			className: 'dt-center',
			render: function (data, type, row) {
				return createUrl(row.month, data, 'terima');
			}
		}, {
			data: 'jumlah_bulan_ini',
			className: 'dt-center',
			render: function (data, type, row) {
				return createUrl(row.month, (parseInt(row.sisa_bulan_lalu) + parseInt(row.masuk)), 'jumlah_bulan_ini');
			}
		}, {
			data: 'dicabut',
			className: 'dt-center',
			render: function (data, type, row) {
				return createUrl(row.month, data, 'cabut');
			}
		},
		{
			data: 'dikabulkan',
			className: 'dt-center',
			render: function (data, type, row) {
				return createUrl(row.month, data, 'dikabulkan');
			}
		}, {
			data: 'ditolak',
			className: 'dt-center',
			render: function (data, type, row) {
				return createUrl(row.month, data, 'ditolak');
			}
		}, {
			data: 'tidak_diterima',
			className: 'dt-center',
			render: function (data, type, row) {
				return createUrl(row.month, data, 'tidak_diterima');
			}
		}, {
			data: 'digugurkan',
			className: 'dt-center',
			render: function (data, type, row) {
				return createUrl(row.month, data, 'digugurkan');
			}
		}, {
			data: 'dicoret',
			className: 'dt-center',
			render: function (data, type, row) {
				return createUrl(row.month, data, 'dicoret');
			}
		}, {
			data: 'damai',
			className: 'dt-center',
			render: function (data, type, row) {
				return createUrl(row.month, data, 'damai');
			}
		}, {
			data: 'lain_lain',
			className: 'dt-center',
			render: function (data, type, row) {
				return createUrl(row.month, data, 'lain_lain');
			}
		}, {
			data: 'jumlah_semua',
			className: 'dt-center',
			render: function (data, type, row) {
				return createUrl(row.month, data, 'jumlah_semua');
			}
		},
		{
			data: 'sisa_bulan_ini',
			className: 'dt-center',
			render: function (data, type, row) {
				return createUrl(row.month, data, 'sisa_bulan_ini');
			}
		},
		{
			data: 'minutasi',
			className: 'dt-center',
			render: function (data, type, row) {
				return createUrl(row.month, data, 'minutasi');
			}
		},
		{
			data: 'persentase_ecourt_fixed',
			className: 'dt-center',
			render: function (data, type, row) {
				return `${parseFloat(data).toFixed(2)}%`;
			}
		},
		{
			data: 'ecourt_fixed',
			visible: false,
		},
		{
			data: 'masuk_fixed',
			visible: false,
		}
		];

		initDataTable('#table-ecourt', {
			title: "<?php echo $title ?>",
			title,
			ajax: {
				url: urlStat,
				data: function (d) {
					d['selectedYear'] = $(`.table-ecourt_datepicker input[type="text"]`).val();
					d['selectedAlur'] = $(`.dropdown-alur-${theTime} select`).val();
					d['selectedType'] = $(`.dropdown-jenis-${theTime} select`).val();
					d[localStorage.getItem('csrfName')] = localStorage.getItem('csrfToken');
				}
			},
			drawCallback: function (settings) {
				const json = settings.json;
				if (json) {
					generateBarChart('stackedBarChart', json.data);
					generateLineChart('lineChart', json.data);
				}
			},
			scroller: false,
			showSearchField: false,
			layout: {
				topStart: {
					buttons: [{
						extend: 'datepicker',
						config: {
							id: "table-ecourt_datepicker",
							minViewMode: 'years',
							format: 'yyyy',
							placeholder: 'Tahun',
							// value: getYear,
						},
					},
					{
						extend: 'dropdown',
						config: {
							id: 'dropdown-alur-' + theTime,
							class: 'dropdown-alur-' + theTime,
							placeholder: 'Alur Perkara',
							options: alurPerkara,
							width: 175,
						},
					},
					{
						extend: 'dropdown',
						config: {
							id: 'dropdown-jenis-' + theTime,
							class: 'dropdown-jenis-' + theTime,
							placeholder: 'Jenis Perkara',
							options: jenisPerkara,
							width: 275,
						},
					},
					],
				},
			},
			columnDefs: [{
				"targets": [0],
				"className": "dt-right"
			}, {
				"targets": [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14],
				"className": "dt-center"
			}],
			columns: columns,
			footerCallback: function (row, data, start, end, display) {
				const typePutus = ['terima', '', 'cabut', 'dikabulkan', 'ditolak', 'tidak_diterima', 'digugurkan', 'dicoret', 'damai', 'jumlah_putus', 'jumlah_semua', '', 'minutasi'];
				const api = this.api();

				for (let i = columns.findIndex(column => column.data === 'masuk'); i <= columns.findIndex(column => column.data === 'minutasi'); i++) {
					if (![columns.findIndex(column => column.data === 'jumlah_bulan_ini'), columns.findIndex(column => column.data === 'sisa_bulan_ini')].includes(i)) {
						const total = api
							.column(i, {
								page: 'all'
							}) // Use page: 'all' for all rows or page: 'current' for visible rows only
							.data()
							.reduce((sum, value) => sum + parseFloat(value) || 0, 0);
						$(api.column(i).footer()).html(createUrl(0, total, typePutus[i - 3]));
					}
				}

				const masuk = api
					.column(columns.findIndex(column => column.data === 'masuk_fixed'), {
						page: 'all'
					})
					.data()
					.reduce((sum, value) => sum + parseFloat(value) || 0, 0);
				const ecourt = api
					.column(columns.findIndex(column => column.data === 'ecourt_fixed'), {
						page: 'all'
					})
					.data()
					.reduce((sum, value) => sum + parseFloat(value) || 0, 0);

				if (masuk > 0) {
					$(api.column(columns.findIndex(column => column.data === 'persentase_ecourt_fixed')).footer()).html(`${(ecourt / masuk * 100).toFixed(2)}%`);
					$('tfoot').slideDown();
				} else {
					$('tfoot').slideUp();
				}
			}
		});

		function generateBarChart(id, data) {
			initChart(id, {
				title,
				customChartTypes: ['bar', 'doughnut'],
				data: {
					labels: data.map(item =>
						moment(`${item.year}-${item.month.padStart(2, '0')}`, 'YYYY-MM').format('MMM YYYY')
					),
					datasets: Object.keys(data[0])
						.filter(key => ['masuk', 'dicabut', 'jumlah_putus', 'dikabulkan'].includes(key)).map(key => ({
							label: key.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()), // Pretty label
							data: data.map(item => parseInt(item[key]) || 0),
							// ...(['dikabulkan', 'ditolak', 'tidak_diterima', 'digugurkan', 'dicoret', 'damai', 'lain_lain'].includes(key)) && {
							//     stack: 'stack1'
							// }
						}))
				},
				options: {
					axisTitles: {
						x: 'Bulan',
						y: 'Jumlah Perkara'
					},
				}
			});
		}

		function generateLineChart(id, data) {
			initChart(id, {
				title: `${title} Putus`,
				type: 'line',
				customChartTypes: ['bar', 'line', 'doughnut'],
				data: {
					labels: data.map(item =>
						moment(`${item.year}-${item.month.padStart(2, '0')}`, 'YYYY-MM').format('MMM YYYY')
					),
					datasets: Object.keys(data[0])
						.filter(key => ['ditolak', 'tidak_diterima', 'digugurkan', 'dicoret', 'damai', 'lain_lain'].includes(key)).map(key => ({
							label: key.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()),
							data: data.map(item => parseInt(item[key]) || 0),
						}))
				},
				options: {
					axisTitles: {
						x: 'Bulan',
						y: 'Jumlah Perkara'
					},
				}
			})
		}
	});
</script>