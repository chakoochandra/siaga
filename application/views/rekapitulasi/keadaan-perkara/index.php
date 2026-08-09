<div class="card leaves">
	<div class="card-header leaves align-items-center">
		<h5 class="m-0"><?php echo $title ?></h5>
	</div>
	<div class="card-body">
		<div class="table-responsive">
			<table id="table-rekapitulasi" class="display">
				<thead>
					<tr>
						<th class="dt-center" rowspan="2">No.</th>
						<th rowspan="2"><?php echo isset($view_type) && $view_type == 'yearly' ? 'Tahun' : 'Bulan' ?>
						</th>
						<th class="text-rotatess" rowspan="2">
							<?php echo isset($view_type) && $view_type == 'yearly' ? 'Sisa Tahun Lalu' : 'Sisa bulan Lalu' ?>
						</th>
						<th class="text-rotatess" rowspan="2">Diterima</th>
						<th class="text-rotatess" rowspan="2">
							<?php echo isset($view_type) && $view_type == 'yearly' ? 'Jumlah Tahun Ini' : 'Jumlah Bulan Ini' ?>
						</th>

						<th class="dt-center" colspan="9">Putusan/Penetapan</th>
						<th class="text-rotatess" rowspan="2">Sisa</th>
						<th class="text-rotatess" rowspan="2">Minutasi</th>
						<th rowspan="2">Persentase e-Court</th>
						<th class="exclude-export noVis" rowspan="2"></th>
						<th class="d-none" rowspan="2">ecourt_fixed</th>
						<th class="d-none" rowspan="2">masuk_fixed</th>
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
						<td></td>
						<td class="exclude-export noVis"></td>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function () {
		const viewType = '<?php echo isset($view_type) ? $view_type : 'monthly' ?>';
		const getYear = '<?php echo $this->uri->segment(4) ?: date('Y') ?>';
		const getEcourt = '<?php echo $this->uri->segment(5) ?: 0 ?>';
		const theTime = '<?php echo time() ?>';

		function createUrl(month, data, column) {
			const selectedYear = $(`.table-rekapitulasi_datepicker input[type="text"]`).val() || getYear;
			const selectedEcourt = $(`.dropdown-status-${theTime} select`).val() || getEcourt;
			const selectedAlur = $(`.dropdown-alur-${theTime} select`).val() || 0;
			const selectedType = $(`.dropdown-jenis-${theTime} select`).val() || 0;

			// For yearly view, use the year as the month parameter and set month to 0
			const urlYear = viewType == 'yearly' ? month : selectedYear;
			const urlMonth = viewType == 'yearly' ? 0 : month;

			return data <= 0 ? `<span class="text-muted">${data}</span>` : `<a href="${'<?php echo base_url('rekapitulasi/keadaanperkara/perkara_list/') ?>'}${column}/${urlYear}/${urlMonth}/${selectedEcourt}/${selectedAlur}/${selectedType}" class="btn-modal">${data}</a>`;
		}

		var columns = [{
			data: null,
			className: 'dt-right',
			render: function (data, type, row, meta) {
				return meta.row + meta.settings._iDisplayStart + 1;
			}
		}, {
			data: viewType == 'yearly' ? 'year' : 'month',
			className: 'dt-center text-nowrap',
			render: function (data, type, row) {
				if (!data) return '';

				if (viewType == 'yearly') {
					return data;
				}

				return moment().month(data - 1).format('MMMM') + ' ' + row.year;
			}
		}, {
			data: 'sisa_bulan_lalu',
			className: 'dt-center',
			render: function (data, type, row) {
				return createUrl(viewType == 'yearly' ? row.year : row.month, data, 'sisa_bulan_lalu');
			}
		}, {
			data: 'masuk',
			className: 'dt-center',
			render: function (data, type, row) {
				return createUrl(viewType == 'yearly' ? row.year : row.month, data, 'terima');
			}
		}, {
			data: 'jumlah_bulan_ini',
			className: 'dt-center',
			render: function (data, type, row) {
				return createUrl(viewType == 'yearly' ? row.year : row.month, (parseInt(row.sisa_bulan_lalu) + parseInt(row.masuk)), 'jumlah_bulan_ini');
			}
		}, {
			data: 'dicabut',
			className: 'dt-center',
			render: function (data, type, row) {
				return createUrl(viewType == 'yearly' ? row.year : row.month, data, 'cabut');
			}
		},
		{
			data: 'dikabulkan',
			className: 'dt-center',
			render: function (data, type, row) {
				return createUrl(viewType == 'yearly' ? row.year : row.month, data, 'dikabulkan');
			}
		}, {
			data: 'ditolak',
			className: 'dt-center',
			render: function (data, type, row) {
				return createUrl(viewType == 'yearly' ? row.year : row.month, data, 'ditolak');
			}
		}, {
			data: 'tidak_diterima',
			className: 'dt-center',
			render: function (data, type, row) {
				return createUrl(viewType == 'yearly' ? row.year : row.month, data, 'tidak_diterima');
			}
		}, {
			data: 'digugurkan',
			className: 'dt-center',
			render: function (data, type, row) {
				return createUrl(viewType == 'yearly' ? row.year : row.month, data, 'digugurkan');
			}
		}, {
			data: 'dicoret',
			className: 'dt-center',
			render: function (data, type, row) {
				return createUrl(viewType == 'yearly' ? row.year : row.month, data, 'dicoret');
			}
		}, {
			data: 'damai',
			className: 'dt-center',
			render: function (data, type, row) {
				return createUrl(viewType == 'yearly' ? row.year : row.month, data, 'damai');
			}
		}, {
			data: 'lain_lain',
			className: 'dt-center',
			render: function (data, type, row) {
				return createUrl(viewType == 'yearly' ? row.year : row.month, data, 'lain_lain');
			}
		}, {
			data: 'jumlah_semua',
			className: 'dt-center',
			render: function (data, type, row) {
				return createUrl(viewType == 'yearly' ? row.year : row.month, data, 'jumlah_semua');
			}
		},
		{
			data: 'sisa_bulan_ini',
			className: 'dt-center',
			render: function (data, type, row) {
				return createUrl(viewType == 'yearly' ? row.year : row.month, data, 'sisa_bulan_ini');
			}
		},
		{
			data: 'minutasi',
			className: 'dt-center',
			render: function (data, type, row) {
				return createUrl(viewType == 'yearly' ? row.year : row.month, data, 'minutasi');
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

		initDataTable('#table-rekapitulasi', {
			title: "<?php echo $title ?>",
			scroller: false,
			ajax: {
				url: viewType == 'yearly' ? "<?php echo base_url("rekapitulasi/keadaanperkara/get_yearly") ?>" : "<?php echo base_url("rekapitulasi/keadaanperkara/get_statistic") ?>",
				data: function (d) {
					d['selectedYear'] = $(`.table-rekapitulasi_datepicker input[type="text"]`).val();
					d['selectedEcourt'] = $(`.dropdown-status-${theTime} select`).val();
					d['selectedAlur'] = $(`.dropdown-alur-${theTime} select`).val();
					d['selectedType'] = $(`.dropdown-jenis-${theTime} select`).val();
					d[localStorage.getItem('csrfName')] = localStorage.getItem('csrfToken');
				}
			},
			ordering: viewType == 'yearly' ? [
				[1, 'desc']
			] : false,
			showSearchField: false,
			layout: {
				topStart: {
					buttons: (function () {
						var buttons = [];

						// Only show datepicker in monthly view
						if (viewType !== 'yearly') {
							buttons.push({
								extend: 'datepicker',
								config: {
									id: "table-rekapitulasi_datepicker",
									minViewMode: 'years',
									format: 'yyyy',
									placeholder: 'Tahun',
									// value: getYear,
								},
							});
						}

						// Always show the other dropdowns
						buttons.push({
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
						}, {
							extend: 'dropdown',
							config: {
								id: 'dropdown-alur-' + theTime,
								class: 'dropdown-alur-' + theTime,
								placeholder: 'Alur Perkara',
								options: <?php echo json_encode($all_alur_perkara) ?>,
								width: 175,
							},
						}, {
							extend: 'dropdown',
							config: {
								id: 'dropdown-jenis-' + theTime,
								class: 'dropdown-jenis-' + theTime,
								placeholder: 'Jenis Perkara',
								options: <?php echo json_encode($all_jenis_perkara) ?>,
								width: 275,
							},
						});

						return buttons;
					})(),
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
			footerCallback: function (tfoot, data, start, end, display) {
				const typePutus = ['', 'terima', '', 'cabut', 'dikabulkan', 'ditolak', 'tidak_diterima', 'digugurkan', 'dicoret', 'damai', 'lain_lain', 'jumlah_putus', '', 'minutasi'];
				const api = this.api();

				// Set the "Total" label in the third column (Sisa bulan lalu column footer)
				$(api.column(2).footer()).html('Total');

				for (let i = columns.findIndex(column => column.data === 'masuk'); i <= columns.findIndex(column => column.data === 'minutasi'); i++) {
					if (![columns.findIndex(column => column.data === 'jumlah_bulan_ini'), columns.findIndex(column => column.data === 'sisa_bulan_ini')].includes(i)) {
						const total = api
							.column(i, {
								page: 'all'
							}) // Use page: 'all' for all rows or page: 'current' for visible rows only
							.data()
							.reduce((sum, value) => sum + parseFloat(value) || 0, 0);

						$(api.column(i).footer()).html(createUrl(0, total, typePutus[i - 2]));
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

				// For yearly view, show footer but without percentage calculation
				if (viewType == 'yearly') {
					$('tfoot').slideDown();
				} else {
					if (masuk > 0) {
						$(api.column(columns.findIndex(column => column.data === 'persentase_ecourt_fixed')).footer()).html(`${(ecourt / masuk * 100).toFixed(2)}%`);
						$('tfoot').slideDown();
					} else {
						$('tfoot').slideUp();
					}
				}
			}
		});
	});
</script>