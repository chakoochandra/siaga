<div class="card leaves">
	<div class="card-header leaves align-items-center">
		<h5 class="m-0"><?php echo $title ?></h5>
	</div>
	<div class="card-body">
		<div class="table-responsive">
			<table id="table-dk" class="display">
				<thead>
					<tr>
						<th class="dt-center" rowspan="2">No.</th>
						<th rowspan="2">Bulan</th>
						<th class="text-rotatess" rowspan="2">Sisa bulan Lalu</th>
						<th class="text-rotatess" rowspan="2">Diterima</th>
						<th class="text-rotatess" rowspan="2">Jumlah Bulan Ini</th>

						<th class="dt-center" colspan="9">Penetapan</th>
						<th class="text-rotatess" rowspan="2">Sisa</th>
						<th class="text-rotatess" rowspan="2">Minutasi</th>
						<th class="dt-center" colspan="2">Umur</th>
						<th class="dt-center" colspan="2">Jenis Kelamin</th>
						<th class="dt-center" colspan="13">Pendidikan</th>
						<th class="dt-center" colspan="8">Pekerjaan</th>
						<th class="dt-center" colspan="5">Alasan Menikah</th>
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
						<th class="text-rotatess">
							< 15 Tahun</th>
						<th class="text-rotatess">15 s.d 19 Tahun</th>
						<th class="text-rotatess">Pria</th>
						<th class="text-rotatess">Wanita</th>
						<th class="text-rotatess">Tidak Ada Pendidkan</th>
						<th class="text-rotatess">Belum Sekolah</th>
						<th class="text-rotatess">TK</th>
						<th class="text-rotatess">SD</th>
						<th class="text-rotatess">SMP</th>
						<th class="text-rotatess">SMA</th>
						<th class="text-rotatess">D1</th>
						<th class="text-rotatess">D2</th>
						<th class="text-rotatess">D3</th>
						<th class="text-rotatess">D4</th>
						<th class="text-rotatess">S1</th>
						<th class="text-rotatess">S2</th>
						<th class="text-rotatess">S3</th>
						<th class="text-rotatess">Belum Bekerja</th>
						<th class="text-rotatess">PNS</th>
						<th class="text-rotatess">TNI</th>
						<th class="text-rotatess">Polisi</th>
						<th class="text-rotatess">Swasta</th>
						<th class="text-rotatess">BUMN</th>
						<th class="text-rotatess">BUMD</th>
						<th class="text-rotatess">Lain-Lain</th>
						<th class="text-rotatess">Hamil</th>
						<th class="text-rotatess">Pergaulan Bebas</th>
						<th class="text-rotatess">Budaya/Adat</th>
						<th class="text-rotatess">Menghindari Zina</th>
						<th class="text-rotatess">Lain-Lain</th>
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
						<td class="exclude-export noVis"></td>
					</tr>
				</tfoot>
			</table>
		</div>

		<div class="row mt-5">
			<div class="col-md-6">
				<div id="chartUmur" style="height: 350px;"></div>
			</div>
			<div class="col-md-6">
				<div id="chartKelamin" style="height: 350px;"></div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6">
				<div id="chartPendidikan" style="height: 350px;"></div>
			</div>
			<div class="col-md-6">
				<div id="chartPekerjaan" style="height: 350px;"></div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6">
				<div id="chartAlasan" style="height: 350px;"></div>
			</div>
		</div>
	</div>
</div>

<script src="<?php echo asset_url('assets/plugins/chart.js/Chart.min.js') ?>"></script>
<script src="<?php echo asset_url('assets/plugins/chart.js/chartjs-plugin-datalabels.js') ?>"></script>
<script src="<?php echo asset_url('assets/plugins/chart.js/init.js') ?>"></script>
<script src="<?php echo asset_url('assets/plugins/html2canvas/html2canvas.min.js') ?>"></script>

<script type="text/javascript">
	$(document).ready(function () {
		const getYear = '<?php echo $this->uri->segment(4) ?: date('Y') ?>';
		const getEcourt = '<?php echo $this->uri->segment(5) ?: 0 ?>';
		const theTime = '<?php echo time() ?>';

		const baseUrl = '<?php echo base_url() ?>';

		function createUrl(month, data, column) {
			const selectedYear = $(`.table-dk_datepicker input[type="text"]`).val() || getYear;
			const selectedEcourt = $(`.dropdown-status-${theTime} select`).val() || getEcourt;
			return data <= 0 ? `<span class="text-muted">${data}</span>` : `<a href="${baseUrl + 'rekapitulasi/keadaanperkara/perkara_list/'}${column}/${selectedYear}/${month}/${selectedEcourt}/16/362" class="btn-modal">${data}</a>`;
		}



		loadChartOverlay('chartUmur');
		loadChartOverlay('chartKelamin');

		// Define columns configuration that matches the HTML structure and AJAX response
		var columns = [{
			data: null,
			className: 'dt-right',
			render: function (data, type, row, meta) {
				return meta.row + meta.settings._iDisplayStart + 1;
			}
		},
		{
			data: 'month',
			className: 'dt-center text-nowrap',
			render: function (data, type, row) {
				if (!data) return '';
				return moment().month(data - 1).format('MMMM') + ' ' + row.year;
			}
		},
		{
			data: 'sisa_bulan_lalu',
			className: 'dt-center',
			render: function (data, type, row) {
				return createUrl(row.month, data, 'sisa_bulan_lalu');
			}
		},
		{
			data: 'masuk',
			className: 'dt-center',
			render: function (data, type, row) {
				return createUrl(row.month, data, 'terima');
			}
		},
		{
			data: 'jumlah_bulan_ini',
			className: 'dt-center',
			render: function (data, type, row) {
				return createUrl(row.month, (parseInt(row.sisa_bulan_lalu) + parseInt(row.masuk)), 'jumlah_bulan_ini');
			}
		},
		{
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
		},
		{
			data: 'ditolak',
			className: 'dt-center',
			render: function (data, type, row) {
				return createUrl(row.month, data, 'ditolak');
			}
		},
		{
			data: 'tidak_diterima',
			className: 'dt-center',
			render: function (data, type, row) {
				return createUrl(row.month, data, 'tidak_diterima');
			}
		},
		{
			data: 'digugurkan',
			className: 'dt-center',
			render: function (data, type, row) {
				return createUrl(row.month, data, 'digugurkan');
			}
		},
		{
			data: 'dicoret',
			className: 'dt-center',
			render: function (data, type, row) {
				return createUrl(row.month, data, 'dicoret');
			}
		},
		{
			data: 'damai',
			className: 'dt-center',
			render: function (data, type, row) {
				return createUrl(row.month, data, 'damai');
			}
		},
		{
			data: 'lain_lain',
			className: 'dt-center',
			render: function (data, type, row) {
				return createUrl(row.month, data, 'lain_lain');
			}
		},
		{
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
			data: 'dk_umur_under_15',
			className: 'dt-center dt-striped',
			render: function (data, type, row) {
				return createUrl(row.month, data, 'dk_umur_under_15');
			}
		},
		{
			data: 'dk_umur_15_to_19',
			className: 'dt-center dt-striped',
			render: function (data, type, row) {
				return createUrl(row.month, data, 'dk_umur_15_to_19');
			}
		},
		{
			data: 'dk_jenis_laki_laki',
			className: 'dt-center',
			render: function (data, type, row) {
				return createUrl(row.month, data, 'dk_jenis_laki_laki');
			}
		},
		{
			data: 'dk_jenis_perempuan',
			className: 'dt-center',
			render: function (data, type, row) {
				return createUrl(row.month, data, 'dk_jenis_perempuan');
			}
		},
		{
			data: 'dk_pendidikan_tidak_ada',
			className: 'dt-center dt-striped',
			render: function (data, type, row) {
				return createUrl(row.month, data, 'dk_pendidikan_tidak_ada');
			}
		},
		{
			data: 'dk_pendidikan_belum_sekolah',
			className: 'dt-center dt-striped',
			render: function (data, type, row) {
				return createUrl(row.month, data, 'dk_pendidikan_belum_sekolah');
			}
		},
		{
			data: 'dk_pendidikan_tk',
			className: 'dt-center dt-striped',
			render: function (data, type, row) {
				return createUrl(row.month, data, 'dk_pendidikan_tk');
			}
		},
		{
			data: 'dk_pendidikan_sd',
			className: 'dt-center dt-striped',
			render: function (data, type, row) {
				return createUrl(row.month, data, 'dk_pendidikan_sd');
			}
		},
		{
			data: 'dk_pendidikan_sltp',
			className: 'dt-center dt-striped',
			render: function (data, type, row) {
				return createUrl(row.month, data, 'dk_pendidikan_sltp');
			}
		},
		{
			data: 'dk_pendidikan_slta',
			className: 'dt-center dt-striped',
			render: function (data, type, row) {
				return createUrl(row.month, data, 'dk_pendidikan_slta');
			}
		},
		{
			data: 'dk_pendidikan_d1',
			className: 'dt-center dt-striped',
			render: function (data, type, row) {
				return createUrl(row.month, data, 'dk_pendidikan_d1');
			}
		},
		{
			data: 'dk_pendidikan_d2',
			className: 'dt-center dt-striped',
			render: function (data, type, row) {
				return createUrl(row.month, data, 'dk_pendidikan_d2');
			}
		},
		{
			data: 'dk_pendidikan_d3',
			className: 'dt-center dt-striped',
			render: function (data, type, row) {
				return createUrl(row.month, data, 'dk_pendidikan_d3');
			}
		},
		{
			data: 'dk_pendidikan_d4',
			className: 'dt-center dt-striped',
			render: function (data, type, row) {
				return createUrl(row.month, data, 'dk_pendidikan_d4');
			}
		},
		{
			data: 'dk_pendidikan_s1',
			className: 'dt-center dt-striped',
			render: function (data, type, row) {
				return createUrl(row.month, data, 'dk_pendidikan_s1');
			}
		},
		{
			data: 'dk_pendidikan_s2',
			className: 'dt-center dt-striped',
			render: function (data, type, row) {
				return createUrl(row.month, data, 'dk_pendidikan_s2');
			}
		},
		{
			data: 'dk_pendidikan_s3',
			className: 'dt-center dt-striped',
			render: function (data, type, row) {
				return createUrl(row.month, data, 'dk_pendidikan_s3');
			}
		},
		{
			data: 'dk_pekerjaan_belum_bekerja',
			className: 'dt-center',
			render: function (data, type, row) {
				return createUrl(row.month, data, 'dk_pekerjaan_belum_bekerja');
			}
		},
		{
			data: 'dk_pekerjaan_pns',
			className: 'dt-center',
			render: function (data, type, row) {
				return createUrl(row.month, data, 'dk_pekerjaan_pns');
			}
		},
		{
			data: 'dk_pekerjaan_tni',
			className: 'dt-center',
			render: function (data, type, row) {
				return createUrl(row.month, data, 'dk_pekerjaan_tni');
			}
		},
		{
			data: 'dk_pekerjaan_polisi',
			className: 'dt-center',
			render: function (data, type, row) {
				return createUrl(row.month, data, 'dk_pekerjaan_polisi');
			}
		},
		{
			data: 'dk_pekerjaan_swasta',
			className: 'dt-center',
			render: function (data, type, row) {
				return createUrl(row.month, data, 'dk_pekerjaan_swasta');
			}
		},
		{
			data: 'dk_pekerjaan_bumn',
			className: 'dt-center',
			render: function (data, type, row) {
				return createUrl(row.month, data, 'dk_pekerjaan_bumn');
			}
		},
		{
			data: 'dk_pekerjaan_bumd',
			className: 'dt-center',
			render: function (data, type, row) {
				return createUrl(row.month, data, 'dk_pekerjaan_bumd');
			}
		},
		{
			data: 'dk_pekerjaan_lain_lain',
			className: 'dt-center',
			render: function (data, type, row) {
				return createUrl(row.month, data, 'dk_pekerjaan_lain_lain');
			}
		},
		{
			data: 'alasan_hamil',
			className: 'dt-center dt-striped',
			render: function (data, type, row) {
				return createUrl(row.month, data, 'alasan_hamil');
			}
		},
		{
			data: 'alasan_pergaulan_bebas',
			className: 'dt-center dt-striped',
			render: function (data, type, row) {
				return createUrl(row.month, data, 'alasan_pergaulan_bebas');
			}
		},
		{
			data: 'alasan_budaya_adat',
			className: 'dt-center dt-striped',
			render: function (data, type, row) {
				return createUrl(row.month, data, 'alasan_budaya_adat');
			}
		},
		{
			data: 'alasan_menghindari_zina',
			className: 'dt-center dt-striped',
			render: function (data, type, row) {
				return createUrl(row.month, data, 'alasan_menghindari_zina');
			}
		},
		{
			data: 'alasan_lain_lain',
			className: 'dt-center dt-striped',
			render: function (data, type, row) {
				return createUrl(row.month, data, 'alasan_lain_lain');
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
			data: null,
			defaultContent: '',
			className: 'exclude-export noVis',
			orderable: false,
			searchable: false,
		}
		];

		initDataTable('#table-dk', {
			title: "<?php echo $title ?>",
			ajax: {
				url: "<?php echo base_url("rekapitulasi/keadaanperkara/get_statistic/dk") ?>",
				data: function (d) {
					d['selectedYear'] = $(`.table-dk_datepicker input[type="text"]`).val();
					d['selectedEcourt'] = $(`.dropdown-status-${theTime} select`).val();
					d[localStorage.getItem('csrfName')] = localStorage.getItem('csrfToken');
				}
			},
			drawCallback: function (settings) {
				const json = settings.json;
				if (json) {
					buildPieChart('chartUmur', 'Umur', extractGroupedData(json.data, 'dk_umur_'));
					buildPieChart('chartKelamin', 'Jenis Kelamin', extractGroupedData(json.data, 'dk_jenis_'));
					buildPieChart('chartAlasan', 'Alasan Menikah', extractGroupedData(json.data, 'alasan_'));
					buildPieChart('chartPendidikan', 'Pendidikan', extractGroupedData(json.data, 'dk_pendidikan_'));
					buildPieChart('chartPekerjaan', 'Pekerjaan', extractGroupedData(json.data, 'dk_pekerjaan_'));
				}
			},
			scroller: false,
			showSearchField: false,
			layout: {
				topStart: {
					buttons: [{
						extend: 'datepicker',
						config: {
							id: "table-dk_datepicker",
							minViewMode: 'years',
							format: 'yyyy',
							placeholder: 'Tahun',
							// value: getYear,
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
				const api = this.api();

				// Only calculate footer if we have data
				if (data.length === 0) {
					$('tfoot').slideUp();
					return;
				}

				// Calculate totals for each column
				const columnTotals = {};

				// Initialize totals for all numeric columns
				api.columns().every(function (index) {
					// Skip non-numeric columns (No., Bulan, Action column)
					if (index === 0 || index === 1 || index === columns.length - 1) {
						return;
					}

					const columnData = this.data();
					let total = 0;

					columnData.each(function (value) {
						// Convert to number and add to total
						const numValue = parseFloat(value) || 0;
						total += numValue;
					});

					columnTotals[index] = total;
				});

				// Update footer cells with totals using the createUrl function for clickable totals
				const selectedYear = $(`.table-dk_datepicker input[type="text"]`).val() || getYear;
				const selectedEcourt = $(`.dropdown-status-${theTime} select`).val() || getEcourt;

				// Define column mappings for clickable footer links (excluding sisa_bulan_lalu and jumlah_bulan_ini)
				const columnMappings = {
					3: 'terima', // masuk
					// 4: 'jumlah_bulan_ini', // jumlah_bulan_ini - excluded from footer
					5: 'cabut', // dicabut
					6: 'dikabulkan', // dikabulkan
					7: 'ditolak', // ditolak
					8: 'tidak_diterima', // tidak_diterima
					9: 'digugurkan', // digugurkan
					10: 'dicoret', // dicoret
					11: 'damai', // damai
					12: 'lain_lain', // lain_lain
					13: 'jumlah_semua', // jumlah_semua
					14: 'sisa_bulan_ini', // sisa_bulan_ini
					15: 'minutasi', // minutasi
					16: 'dk_umur_under_15', // dk_umur_under_15
					17: 'dk_umur_15_to_19', // dk_umur_15_to_19
					18: 'dk_jenis_laki_laki', // dk_jenis_laki_laki
					19: 'dk_jenis_perempuan', // dk_jenis_perempuan
					20: 'dk_pendidikan_tidak_ada', // dk_pendidikan_tidak_ada
					21: 'dk_pendidikan_belum_sekolah', // dk_pendidikan_belum_sekolah
					22: 'dk_pendidikan_tk', // dk_pendidikan_tk
					23: 'dk_pendidikan_sd', // dk_pendidikan_sd
					24: 'dk_pendidikan_sltp', // dk_pendidikan_sltp
					25: 'dk_pendidikan_slta', // dk_pendidikan_slta
					26: 'dk_pendidikan_d1', // dk_pendidikan_d1
					27: 'dk_pendidikan_d2', // dk_pendidikan_d2
					28: 'dk_pendidikan_d3', // dk_pendidikan_d3
					29: 'dk_pendidikan_d4', // dk_pendidikan_d4
					30: 'dk_pendidikan_s1', // dk_pendidikan_s1
					31: 'dk_pendidikan_s2', // dk_pendidikan_s2
					32: 'dk_pendidikan_s3', // dk_pendidikan_s3
					33: 'dk_pekerjaan_belum_bekerja', // dk_pekerjaan_belum_bekerja
					34: 'dk_pekerjaan_pns', // dk_pekerjaan_pns
					35: 'dk_pekerjaan_tni', // dk_pekerjaan_tni
					36: 'dk_pekerjaan_polisi', // dk_pekerjaan_polisi
					37: 'dk_pekerjaan_swasta', // dk_pekerjaan_swasta
					38: 'dk_pekerjaan_bumn', // dk_pekerjaan_bumn
					39: 'dk_pekerjaan_bumd', // dk_pekerjaan_bumd
					40: 'dk_pekerjaan_lain_lain', // dk_pekerjaan_lain_lain
					41: 'alasan_hamil', // alasan_hamil
					42: 'alasan_pergaulan_bebas', // alasan_pergaulan_bebas
					43: 'alasan_budaya_adat', // alasan_budaya_adat
					44: 'alasan_menghindari_zina', // alasan_menghindari_zina
					45: 'alasan_lain_lain' // alasan_lain_lain
				};

				// Update footer cells with clickable totals (excluding sisa_bulan_lalu and jumlah_bulan_ini)
				for (let i = 2; i <= 45; i++) {
					if (i === 2 || i === 4) {
						// Skip footer for sisa_bulan_lalu (column 2) and jumlah_bulan_ini (column 4)
						continue;
					} else if (columnMappings[i]) {
						const total = columnTotals[i] || 0;
						const url = total <= 0 ?
							`<span class="text-muted">${total}</span>` :
							`<a href="${baseUrl}rekapitulasi/keadaanperkara/perkara_list/${columnMappings[i]}/${selectedYear}/0/${selectedEcourt}/16/362" class="btn-modal">${total}</a>`;
						$(api.column(i).footer()).html(url);
					} else {
						$(api.column(i).footer()).html(columnTotals[i] || 0);
					}
				}

				// Show the footer
				$('tfoot').slideDown();
			}
		});

		function extractGroupedData(arr, prefix) {
			const totals = {};

			arr.forEach(item => {
				for (const key in item) {
					if (key.startsWith(prefix)) {
						const label = key.replace(prefix, '').replace(/_/g, ' ').toUpperCase();
						const value = parseInt(item[key]);

						if (!isNaN(value) && value > 0) {
							totals[label] = (totals[label] || 0) + value;
						}
					}
				}
			});

			const labels = Object.keys(totals);
			const data = Object.values(totals);

			return {
				labels,
				data
			};
		}

		function buildPieChart(containerId, title, obj) {
			initChart(containerId, {
				title,
				type: 'pie',
				customChartTypes: ['pie', 'doughnut'],
				data: {
					labels: obj.labels,
					datasets: [{
						data: obj.data,
					}]
				},
			})
		}
	});
</script>