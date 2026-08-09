<div class="card leaves">
	<div class="card-header leaves align-items-center">
		<h5 class="m-0"><?php echo $title ?></h5>
	</div>
	<div class="card-body">
		<div class="table-responsive">
			<table id="table-mediasi" class="display">
				<tfoot class="collapse">
					<tr>
						<th class="dt-right" colspan="2">Total</th> <!-- covers col 0 (Bulan) + col 1 (sisa_lalu) -->
						<th></th> <!-- pos 2: sisa_lalu -->
						<th></th> <!-- pos 3: diterima_bulan_ini -->
						<th></th> <!-- pos 4: tidak_bisa_dimediasi -->
						<th></th> <!-- pos 5: perkara_mediasi -->
						<th></th> <!-- pos 6: tidak_berhasil -->
						<th></th> <!-- pos 7: berhasil_akta -->
						<th></th> <!-- pos 8: berhasil_cabut -->
						<th></th> <!-- pos 9: berhasil_sebagian -->
						<th></th> <!-- pos 10: gagal -->
						<th></th> <!-- pos 11: perkara_proses_mediasi (includes sisa_mediasi_lalu) -->
						<th></th> <!-- pos 12: sisa_perkara -->
					</tr>
				</tfoot>
			</table>
		</div>
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function() {
		function createUrl(year, month, data, type) {
			if (data <= 0) return `<span class="text-muted">${data}</span>`;
			const url = `<?php echo base_url('rekapitulasi/mediasi/perkara_list/') ?>${type}/${year}` + (month ? `/${month}` : '');
			return `<a href="${url}" class="btn-modal">${(data || 0).toLocaleString('id-ID')}</a>`;
		}

		const showSisaLalu = false;

		initDataTable('#table-mediasi', {
			title: "<?php echo $title ?>",
			showSearchField: false,
			scroller: false,
			ajax: {
				url: "<?php echo base_url('rekapitulasi/mediasi/get_statistic') ?>",
				data: function(d) {
					d.selectedYear = $('.table-mediasi_datepicker input[type="text"]').val() || <?php echo date('Y') ?>;
					d[localStorage.getItem('csrfName')] = localStorage.getItem('csrfToken');
				},
				dataSrc: function(json) {
					if (json.csrf_hash) {
						localStorage.setItem('csrfToken', json.csrf_hash);
					}
					return json.data;
				}
			},
			layout: {
				topStart: {
					buttons: [{
						extend: 'datepicker',
						config: {
							id: "table-mediasi_datepicker",
							minViewMode: 'years',
							format: 'yyyy',
							placeholder: 'Tahun',
							value: <?php echo date('Y') ?>,
							cardTitleSelector: '#mediasi-title'
						},
					}]
				},
			},
			columns: [{
					data: 'period',
					title: 'Bulan',
					className: 'dt-right text-nowrap',
					render: function(data, type, row) {
						return data ? moment(data, 'YYYY-MM').format('MMMM YYYY') : '';
					}
				},
				{
					data: 'sisa_lalu',
					title: 'Sisa Perkara Lalu',
					className: 'dt-center',
					visible: showSisaLalu,
					render: function(data, type, row) {
						const [year, month] = row.period.split('-');
						return createUrl(year, month, data, 'sisa_lalu', type);
					}
				},
				{
					// col 2: sisa_mediasi_lalu — moved here, after sisa_lalu
					data: 'sisa_mediasi_lalu',
					title: 'Sisa Mediasi Bulan Lalu',
					className: 'dt-center',
					// visible: showSisaLalu,
					render: function(data, type, row) {
						const [year, month] = row.period.split('-');
						return createUrl(year, month, data, 'sisa_mediasi_lalu', type);
					}
				},
				{
					// col 3: diterima_bulan_ini
					data: 'diterima_bulan_ini',
					title: 'Perkara Diterima Bulan Ini',
					className: 'dt-center',
					visible: showSisaLalu,
					render: function(data, type, row) {
						const [year, month] = row.period.split('-');
						return createUrl(year, month, data, 'diterima_bulan_ini', type);
					}
				},
				{
					// col 4: tidak_bisa_dimediasi — computed, hidden
					data: null,
					title: 'Jumlah Perkara Tidak Bisa Dimediasi',
					className: 'dt-center',
					visible: false,
					render: function(data, type, row) {
						const val = (parseInt(row.sisa_lalu) + parseInt(row.diterima_bulan_ini)) - parseInt(row.perkara_mediasi);
						const [year, month] = row.period.split('-');
						return createUrl(year, month, val, 'tidak_bisa_dimediasi', type);
					}
				},
				{
					// col 5: perkara_mediasi
					data: 'perkara_mediasi',
					title: 'Jumlah Perkara Yang Dimediasi',
					className: 'dt-center',
					render: function(data, type, row) {
						const [year, month] = row.period.split('-');
						return createUrl(year, month, data, 'perkara_mediasi', type);
					}
				},
				{
					// col 6: tidak_berhasil
					data: 'tidak_berhasil',
					title: 'Tidak Berhasil',
					className: 'dt-center',
					render: function(data, type, row) {
						const [year, month] = row.period.split('-');
						return createUrl(year, month, data, 'tidak_berhasil', type);
					}
				},
				{
					// col 7: berhasil_akta
					data: 'berhasil_akta',
					title: 'Berhasil Akta',
					className: 'dt-center',
					render: function(data, type, row) {
						const [year, month] = row.period.split('-');
						return createUrl(year, month, data, 'berhasil_akta', type);
					}
				},
				{
					// col 8: berhasil_cabut
					data: 'berhasil_cabut',
					title: 'Berhasil Cabut',
					className: 'dt-center',
					render: function(data, type, row) {
						const [year, month] = row.period.split('-');
						return createUrl(year, month, data, 'berhasil_cabut', type);
					}
				},
				{
					// col 9: berhasil_sebagian
					data: 'berhasil_sebagian',
					title: 'Berhasil Sebagian',
					className: 'dt-center',
					render: function(data, type, row) {
						const [year, month] = row.period.split('-');
						return createUrl(year, month, data, 'berhasil_sebagian', type);
					}
				},
				{
					// col 10: gagal
					data: 'gagal',
					title: 'Gagal',
					className: 'dt-center',
					render: function(data, type, row) {
						const [year, month] = row.period.split('-');
						return createUrl(year, month, data, 'gagal', type);
					}
				},
				{
					// col 11: perkara_proses_mediasi
					data: 'perkara_proses_mediasi',
					title: 'Masih Dalam Proses Mediasi',
					className: 'dt-center',
					render: function(data, type, row) {
						const [year, month] = row.period.split('-');
						return createUrl(year, month, data, 'perkara_proses_mediasi', type);
					}
				},
				{
					// col 12: sisa_perkara — computed
					data: null,
					title: 'Sisa Perkara',
					className: 'dt-center',
					visible: showSisaLalu,
					render: function(data, type, row) {
						const val = (parseInt(row.sisa_lalu) + parseInt(row.diterima_bulan_ini)) - parseInt(row.putus_bulan_ini);
						const [year, month] = row.period.split('-');
						return createUrl(year, month, val, 'sisa_perkara', type);
					}
				}
			],
			drawCallback: function(settings) {
				const selectedYear = $('.table-mediasi_datepicker input[type="text"]').val();
				if (selectedYear) {
					$('#mediasi-title').text('<?php echo $title ?> Tahun ' + selectedYear);
				}
			},
			footerCallback: function(row, data, start, end, display) {
				var api = this.api();
				const selectedYear = $('.table-mediasi_datepicker input[type="text"]').val();
				const allRows = api.rows({
					page: 'all'
				}).data();

				const footerEntries = [
					[0, 'label'],
					[2, '-'],
					[3, 'diterima_bulan_ini'],
					[4, '-'],
					[5, 'perkara_mediasi'],
					[6, 'tidak_berhasil'],
					[7, 'berhasil_akta'],
					[8, 'berhasil_cabut'],
					[9, 'berhasil_sebagian'],
					[10, 'gagal'],
					[11, '-'], // perkara_proses_mediasi — snapshot, not summable
					[12, '-'],
				];

				footerEntries.forEach(function([colIdx, entryType]) {
					const footerCell = api.column(colIdx).footer();
					if (!footerCell) return;

					if (entryType === 'label') {
						$(footerCell).html('<span class="text-muted">Total</span>');
						return;
					}
					if (entryType === '-') {
						$(footerCell).html('<span class="text-muted"></span>');
						return;
					}

					// field-name entries — aggregate all rows
					let total = 0;
					allRows.each(function(r) {
						total += parseInt(r[entryType]) || 0;
					});
					$(footerCell).html(createUrl(selectedYear, '', total, entryType));
				});

				if (selectedYear) {
					$('tfoot').slideDown();
				} else {
					$('tfoot').slideUp();
				}
			}
		});
	});
</script>