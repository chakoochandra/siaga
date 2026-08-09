<div class="card leaves">
	<div class="card-header leaves align-items-center">
		<h5 class="m-0"><?php echo $title ?></h5>
	</div>
	<div class="card-body">
		<div class="table-responsive">
			<table id="table-pk" class="display">
				<thead>
					<tr>
						<th rowspan="2">Bulan</th>
						<th class="text-rotatess" rowspan="2">Sisa bulan Lalu</th>
						<th class="text-rotatess" rowspan="2">Diterima</th>

						<th class="dt-center" colspan="6">Keadaan Perkara</th>
						<th class="text-rotatess" rowspan="2">Syarat Formil / Cabut</th>
						<th class="text-rotatess" rowspan="2">Sisa</th>
					</tr>
					<tr>
						<th class="text-rotatess">Diperbaiki</th>
						<th class="text-rotatess">Dibatalkan</th>
						<th class="text-rotatess">Tidak Diterima</th>
						<th class="text-rotatess">Ditolak</th>
						<th class="text-rotatess">Lain-lain</th>
						<th class="text-rotatess">Jumlah</th>
					</tr>
				</thead>
				<tfoot class="collapse">
					<tr>
						<td colspan="2" class="dt-right">Total</td>
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
</div>

<script type="text/javascript">
	$(document).ready(function () {
		const theTime = '<?php echo time() ?>';

		function createUrl(year, month, data, type) {
			return data <= 0 ? `<span class="text-muted">${data}</span>` : `<a href="${'<?php echo base_url('rekapitulasi/pk/perkara_list/') ?>'}${type}/${year}/${month}" class="btn-modal">${data}</a>`;
		}

		table = initDataTable('#table-pk', {
			title: "<?php echo $title ?>",
			ajax: {
				url: "<?php echo base_url("rekapitulasi/pk/get_statistic") ?>/",
				data: function (d) {
					d['selectedYear'] = $(`.table-pk_datepicker input[type="text"]`).val();
					d[localStorage.getItem('csrfName')] = localStorage.getItem('csrfToken');
				}
			},
			scroller: false,
			showSearchField: false,
			layout: {
				topStart: {
					buttons: [{
						extend: 'datepicker',
						config: {
							id: "table-pk_datepicker",
							minViewMode: 'years',
							format: 'yyyy',
							placeholder: 'Tahun',
							// value: moment().format('YYYY'),
						},
					},],
				},
			},
			columns: [{
				data: 'month',
				className: 'dt-left text-nowrap',
				render: function (data, type, row) {
					if (!data) return '';
					return moment().month(data - 1).format('MMMM') + ' ' + row.year;
				}
			},
			{
				data: 'sisa_bulan_lalu',
				className: 'dt-center',
				render: function (data, type, row) {
					return createUrl(row.year, row.month, data, 'sisa_bulan_lalu');
				}
			}, {
				data: 'terima',
				className: 'dt-center',
				render: function (data, type, row) {
					return createUrl(row.year, row.month, data, 'terima');
				}
			}, {
				data: 'diperbaiki',
				className: 'dt-center',
				render: function (data, type, row) {
					return createUrl(row.year, row.month, data, 'diperbaiki');
				}
			}, {
				data: 'dibatalkan',
				className: 'dt-center',
				render: function (data, type, row) {
					return createUrl(row.year, row.month, data, 'dibatalkan');
				}
			}, {
				data: 'tidak_diterima',
				className: 'dt-center',
				render: function (data, type, row) {
					return createUrl(row.year, row.month, data, 'tidak_diterima');
				}
			}, {
				data: 'ditolak',
				className: 'dt-center',
				render: function (data, type, row) {
					return createUrl(row.year, row.month, data, 'ditolak');
				}
			}, {
				data: 'lain_lain',
				className: 'dt-center',
				render: function (data, type, row) {
					return createUrl(row.year, row.month, data, 'lain_lain');
				}
			},
			{
				data: 'jumlah_putus',
				className: 'dt-center',
				render: function (data, type, row) {
					return createUrl(row.year, row.month, data, 'jumlah_putus');
				}
			},
			{
				data: 'syarat_formil',
				className: 'dt-center',
				render: function (data, type, row) {
					return createUrl(row.year, row.month, data, 'syarat_formil');
				}
			},
			{
				data: 'sisa_bulan_ini',
				className: 'dt-center',
				render: function (data, type, row) {
					return createUrl(row.year, row.month, data, 'sisa_bulan_ini');
				}
			},
			],
			footerCallback: function (row, data, start, end, display) {
				const typePutus = ['terima', 'diperbaiki', 'dibatalkan', 'tidak_diterima', 'ditolak', 'lain_lain', 'jumlah_putus', 'syarat_formil'];
				const api = this.api();

				let temp = 0;
				for (let i = 2; i <= 9; i++) {
					const total = api
						.column(i, {
							page: 'all'
						}) // Use page: 'all' for all rows or page: 'current' for visible rows only
						.data()
						.reduce((sum, value) => sum + parseFloat(value) || 0, 0);
					$(api.column(i).footer()).html(createUrl($(`.table-pk_datepicker input[type="text"]`).val(), 0, total, typePutus[i - 2]));
					temp += total;
				}

				if (temp > 0) {
					$('tfoot').slideDown();
				} else {
					$('tfoot').slideUp();
				}
			}
		});
	});
</script>