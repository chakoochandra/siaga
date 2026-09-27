<div class="card leaves">
	<div class="card-header leaves align-items-center">
		<h5 class="m-0"><?php echo $title ?></h5>
	</div>
	<div class="card-body">
		<div class="card card-filter" style="display: none;">
			<div class="card-body">
				<form id="filter-form" class="row g-3 align-items-center">
					<div class="col-auto">
						<label class="form-label">Tanggal Putus:</label>
						<input type="text" class="form-control daterange_disposisi" placeholder="Tanggal Putus" value="">
					</div>
					<div class="col-auto">
						<label class="form-label">Tanggal Ke AC:</label>
						<input type="text" class="form-control daterange_ac" placeholder="Tanggal Ke AC" value="">
					</div>
					<div class="col-auto">
						<label class="form-label">Tanggal Serah Arsip:</label>
						<input type="text" class="form-control daterange_serah_arsip" placeholder="Tanggal Serah Arsip" value="">
					</div>
					<div class="col-auto">
						<label class="form-label">Tanggal Rencana BHT:</label>
						<input type="text" class="form-control daterange_rencana_bht" placeholder="Tanggal Rencana BHT" value="">
					</div>
					<div class="col-auto">
						<label class="form-label">Tanggal BHT:</label>
						<input type="text" class="form-control daterange_bht" placeholder="Tanggal BHT" value="">
					</div>
					<div class="col-auto">
						<label class="form-label">Status BHT:</label>
						<select class="form-select dropdown-bht-<?php echo time() ?> select2-picker">
							<option value="">Pilih Status BHT</option>
							<option value="1" <?php echo ($this->uri->segment(5) == '1') ? 'selected' : '' ?>>Sudah BHT</option>
							<option value="2" <?php echo ($this->uri->segment(5) == '2') ? 'selected' : '' ?>>Belum BHT</option>
						</select>
					</div>
					<div class="col-auto">
						<label class="form-label">Status AC:</label>
						<select class="form-select dropdown-ac-<?php echo time() ?> select2-picker">
							<option value="">Pilih Status AC</option>
							<option value="1">Sudah AC</option>
							<option value="2">Belum AC</option>
						</select>
					</div>
					<div class="col-auto">
						<label class="form-label">Status Arsip:</label>
						<select class="form-select dropdown-status-<?php echo time() ?> select2-picker">
							<option value="">Pilih Status Arsip</option>
							<option value="1" <?php echo ($this->uri->segment(6) == '1') ? 'selected' : '' ?>>Sudah Arsip</option>
							<option value="2" <?php echo ($this->uri->segment(6) == '2') ? 'selected' : '' ?>>Belum Arsip</option>
							<option value="3" <?php echo ($this->uri->segment(6) == '3') ? 'selected' : '' ?>>Arsip Ada</option>
							<option value="4" <?php echo ($this->uri->segment(6) == '4') ? 'selected' : '' ?>>Arsip Tidak Ada</option>
							<option value="5" <?php echo ($this->uri->segment(6) == '5') ? 'selected' : '' ?>>Arsip Lengkap</option>
							<option value="6" <?php echo ($this->uri->segment(6) == '6') ? 'selected' : '' ?>>Arsip Tidak Lengkap</option>
						</select>
					</div>
				</form>
			</div>
		</div>

		<div class="table-responsive">
			<table id="table-disposisi-<?php echo $this->uri->segment(4) ?>-<?php echo $this->uri->segment(5) ?>-<?php echo $this->uri->segment(6) ?>" class="display">
				<thead>
					<tr>
						<th rowspan="2">No.</th>

						<th rowspan="2">Nomor Perkara</th>
						<th rowspan="2">Jenis Perkara</th>
						<th rowspan="2">e-Court</th>
						<th rowspan="2">Ghaib</th>
						<th rowspan="2">KM</th>
						<th rowspan="2">PP</th>
						<th rowspan="2">Tanggal Putus</th>
						<th rowspan="2">Proses Terakhir</th>
						<th rowspan="2">Status Putusan</th>
						<th rowspan="2">Verstek</th>
						<!-- <th rowspan="2">Tanggal PBT</th> -->
						<th rowspan="2">Tanggal Terima Panmud</th>
						<th rowspan="2">Tanggal Serah Ke Minut</th>
						<th rowspan="2">Tanggal Serah Ke AC</th>
						<th rowspan="2">Tanggal Rencana BHT</th>
						<th rowspan="2">Tanggal BHT</th>
						<th rowspan="2">Tanggal Akta Cerai</th>
						<!-- <th rowspan="2">Alamat</th> -->
						<th rowspan="2">Tanggal Serah Ke Arsip</th>
						<th colspan="10">Data Arsip</th>
					</tr>
					<tr>
						<th>Tanggal Masuk</th>
						<th>No. Arsip</th>
						<th>No. Ruang</th>
						<th>No. Lemari</th>
						<th>No. Rak</th>
						<th>No. Berkas</th>
						<th>Status</th>
						<th>Register Perkara</th>
						<th>Peyerah</th>
						<th>Penerima</th>
					</tr>
				</thead>
			</table>
		</div>
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function() {
		const getYear = '<?php echo $this->uri->segment(4) ?: null ?>';
		const getStatusBht = '<?php echo $this->uri->segment(5) ?: null ?>';
		const getStatusArsip = '<?php echo $this->uri->segment(6) ?: null ?>';

		const excludes = '<?php echo json_encode($excludedDates) ?>';
		const starting_date = '2024-07-01';
		const theTime = '<?php echo time() ?>';

		// Initialize DataTable without the filter buttons in layout
		initDataTable("#table-disposisi-<?php echo $this->uri->segment(4) ?>-<?php echo $this->uri->segment(5) ?>-<?php echo $this->uri->segment(6) ?>", {
			title: "Monitoring Disposisi",
			scrollX: 3,
			ajax: {
				url: "<?php echo base_url("monitoring/disposisi/get_list") ?>",
				data: function(d) {
					d['selectedRange'] = $('.daterange_disposisi').val();
					d['selectedDateToAC'] = $('.daterange_ac').val();
					d['selectedDateSerahArsip'] = $('.daterange_serah_arsip').val();
					d['selectedDateBHT'] = $('.daterange_bht').val();
					d['selectedDateRencanaBHT'] = $('.daterange_rencana_bht').val();
					d['selectedStatus'] = $('.dropdown-status-<?php echo time() ?>').val();
					d['selectedAc'] = $('.dropdown-ac-<?php echo time() ?>').val();
					d['selectedBht'] = $('.dropdown-bht-<?php echo time() ?>').val();
					// d['selectedPbt'] = $(`.dropdown-pbt-${theTime} select`).val();
					d[localStorage.getItem('csrfName')] = localStorage.getItem('csrfToken');
				}
			},
			ajaxCellInput: [{
					column: 4,
					type: "datepicker",
					url: '<?php echo site_url("monitoring/disposisi/update_value_disposisi/tanggal_panmudg_terima") ?>',
					callback: '<?php echo site_url("monitoring/disposisi") ?>',
					editable: '<?php echo $canEdit ?>',
					endDate: '+0d',
				}, {
					column: 5,
					type: "datepicker",
					url: '<?php echo site_url("monitoring/disposisi/update_value_disposisi/tanggal_serah_ke_minut") ?>',
					callback: '<?php echo site_url("monitoring/disposisi") ?>',
					editable: '<?php echo $canEdit ?>',
					endDate: '+0d',
					// dependant: 'tanggal_panmudg_terima',
				},
				{
					column: 6,
					type: "datepicker",
					url: '<?php echo site_url("monitoring/disposisi/update_value_disposisi/tanggal_serah_ke_ac") ?>',
					callback: '<?php echo site_url("monitoring/disposisi") ?>',
					editable: '<?php echo $canEdit ?>',
					// dependant: 'tanggal_panmudg_terima',
				},
				{
					column: 7,
					type: "datepicker",
					url: '<?php echo site_url("monitoring/disposisi/update_value_disposisi/tanggal_rencana_bht") ?>',
					callback: '<?php echo site_url("monitoring/disposisi") ?>',
					editable: '<?php echo $canEdit ?>',
					// dependant: 'tanggal_panmudg_terima',
				},
				{
					column: 10,
					type: "datepicker",
					url: '<?php echo site_url("monitoring/disposisi/update_value_disposisi/tanggal_serah_ke_arsip") ?>',
					callback: '<?php echo site_url("monitoring/disposisi/index") ?>',
					editable: '<?php echo $canEdit ?>',
					// dependant: 'tanggal_panmudg_terima',
				}
			],
			rowCallback: function(row, data, index) {
				if (data.lengkap != 'Y') {
					$(row).addClass('highlight-warning');
				} else if (data.tanggal_masuk_arsip) {
					$(row).addClass('highlight');
				}
			},
			columns: [{
					data: null,
					className: "dt-center",
					render: function(data, type, row, meta) {
						return meta.row + meta.settings._iDisplayStart + 1;
					}
				},
				{
					data: "nomor_perkara",
					className: 'dt-center text-nowrap',
					render: function(data, type, row) {
						if (type === 'export') {
							return data;
						}
						let result = `<strong>${data}</strong>`;
						if (row.jenis_perkara_nama || row.efiling || row.ghaib) {
							result += '<br/>';
							if (row.jenis_perkara_nama) {
								result += `<span class="badge badge-info me-1">${row.jenis_perkara_nama}</span>`;
							}
							if (row.efiling) {
								result += '<span class="badge badge-success me-1">e-Court</span>';
							}
							if (row.ghaib == 1) {
								result += '<span class="badge badge-warning me-1">Ghaib</span>';
							}
						}

						if (row.url_putusan || row.url_anonimisasi) {
							result += '<br/>';
							result += row.url_putusan ? '<a href="' + (row.url_putusan) + '" target="_blank" class="m-2"><i class="fas fa-file-word text-info"></i> <span class="small">Putusan</span></a>' : '';
							result += row.url_anonimisasi ? '<a href="' + (row.url_anonimisasi) + '" target="_blank" class="m-2"><i class="fas fa-file-pdf"> </i> <span class="small">Anonimisasi</span></a>' : '';
						}

						return result;
					}
				},
				{
					data: null,
					className: "include-export",
					visible: false,
					exportOptions: {
						visible: true
					},
					render: function(data, type, row) {
						return row.jenis_perkara_nama || '';
					}
				},
				{
					data: null,
					className: "include-export",
					visible: false,
					exportOptions: {
						visible: true
					},
					render: function(data, type, row) {
						return row.efiling == 1 ? 'Y' : (row.efiling == 0 ? 'T' : '');
					}
				},
				{
					data: null,
					className: "include-export",
					visible: false,
					exportOptions: {
						visible: true
					},
					render: function(data, type, row) {
						return row.ghaib == 1 ? 'Y' : (row.ghaib == 0 ? 'T' : '');
					}
				},
				{
					data: "hakim_nama",
					className: "text-nowrap",
					render: function(data, type, row) {
						if (type === 'export') {
							return data;
						}
						return data + '<br>' + row.panitera_nama.replace('Panitera Pengganti:', 'PP: ').trim();
					},
				},
				{
					data: null,
					title: "PP",
					className: "include-export",
					visible: false,
					exportOptions: {
						visible: true
					},
					defaultContent: "",
					render: function(data, type, row) {
						return row.panitera_nama || '';
					}
				},
				{
					data: "tanggal_putusan",
					title: "Tanggal Putus",
					className: "dt-center text-nowrap",
					render: function(data, type, row) {
						if (type === 'export') {
							return data ? moment(data).format('Do MMMM YYYY') : '';
						}
						if (!data) {
							return '<span class="badge badge-danger">Belum Putus</span>';
						}
						const dateStr = moment(data).format('Do MMMM YYYY');
						const badges = [];
						if (row.proses_terakhir_text) badges.push(`<span class="badge badge-primary me-1">${row.proses_terakhir_text}</span>`);
						if (row.status_putusan) badges.push(`<span class="badge badge-info me-1">${row.status_putusan}</span>`);
						if (row.putusan_verstek) {
							badges.push(row.putusan_verstek == 'Y' ? '<span class="badge badge-warning me-1">Verstek</span>' : '<span class="badge badge-secondary me-1">Tidak Verstek</span>');
						}
						return dateStr + (badges.length ? '<br/>' + badges.join('') : '');
					}
				},
				{
					data: null,
					title: "Proses Terakhir",
					className: "include-export",
					visible: false,
					exportOptions: {
						visible: true
					},
					defaultContent: "",
					render: function(data, type, row) {
						return row.proses_terakhir_text || '';
					}
				},
				{
					data: null,
					title: "Status Putusan",
					className: "include-export",
					visible: false,
					exportOptions: {
						visible: true
					},
					defaultContent: "",
					render: function(data, type, row) {
						return row.status_putusan || '';
					}
				},
				{
					data: null,
					title: "Verstek",
					className: "include-export",
					visible: false,
					exportOptions: {
						visible: true
					},
					defaultContent: "",
					render: function(data, type, row) {
						return row.putusan_verstek || 'T';
					}
				},
				// {
				// 	data: 'tanggal_pemberitahuan_putusan',
				// 	className: "dt-center",
				// 	render: function(data, type, row) {
				// 		return data ? moment(data).format('Do MMMM YYYY') : '<span class="badge badge-danger">Belum</span>';
				// 	},
				// },
				{
					data: "tanggal_panmudg_terima",
					className: "dt-center",
					render: function(data, type, row) {
						if (!data && (row.tanggal_masuk_arsip || row.tanggal_putusan < starting_date)) {
							return '<span class="badge badge-success"><i class="fas fa-check" aria-hidden="true"></i></span>';
						}
						return data ? moment(data).format('Do MMMM YYYY') : '-';
					},
				},
				{
					data: "tanggal_serah_ke_minut",
					className: "dt-center",
					render: function(data, type, row) {
						if (!data && (row.tanggal_masuk_arsip || row.tanggal_serah_ke_ac || row.tanggal_serah_ke_arsip || row.tanggal_putusan < starting_date)) {
							return '<span class="badge badge-success"><i class="fas fa-check" aria-hidden="true"></i></span>';
						}
						return data ? moment(data).format('Do MMMM YYYY') : '-';
					},
				},
				{
					data: "tanggal_serah_ke_ac",
					className: "dt-center",
					render: function(data, type, row) {
						if (!data && (row.tanggal_masuk_arsip || row.tanggal_serah_ke_arsip || row.tanggal_putusan < starting_date)) {
							return '<span class="badge badge-success"><i class="fas fa-check" aria-hidden="true"></i></span>';
						}
						return data ? moment(data).format('Do MMMM YYYY') : '-';
					},
				},
				{
					data: 'tanggal_rencana_bht',
					className: "dt-center",
					render: function(data, type, row) {
						if (!data && row.tanggal_bht && moment(row.tanggal_bht).isBefore(moment(), 'day')) {
							return '<span class="badge badge-success"><i class="fas fa-check" aria-hidden="true"></i></span>';
						}
						return data ? moment(data).format('Do MMMM YYYY') : '-';
					},
				},
				{
					data: 'tanggal_bht',
					className: "dt-center",
					render: function(data, type, row) {
						return data ? moment(data).format('Do MMMM YYYY') : '<span class="badge badge-danger">Belum</span>';
					},
				},
				{
					data: 'tgl_akta_cerai',
					className: "dt-center",
					render: function(data, type, row) {
						return data ? moment(data).format('Do MMMM YYYY') : '<span class="badge badge-danger">Belum</span>';
					},
				},
				// {
				//     data: 'alamat',
				//     className: 'text-wrap',
				//     // render: function(data, type, row) {
				//     //     if (!data) {
				//     //         return data;
				//     //     }

				//     //     // Step 1: Match "Kec" or "Kecamatan" and extract the text that follows
				//     //     var match = data.match(/(?:Kec(?:amatan)?\.?\s*:?)([^,]*)/i);
				//     //     // Step 2: If a match is found, extract the Kecamatan part
				//     //     var kecamatan = match && match[1] ? match[1].trim() : data;
				//     //     // Step 3: Remove everything starting with "Kabupaten" (case insensitive)
				//     //     var result = kecamatan.replace(/(Kab.*)/i, '').trim();

				//     //     return '<span title="' + data + '">' + (result.length > 50 ? (result.substr(0, 25) + '...') : result) + '</span>';
				//     // }
				// },
				{
					data: "tanggal_serah_ke_arsip",
					className: "dt-center",
					render: function(data, type, row) {
						if (!data && (row.tanggal_masuk_arsip || row.tanggal_putusan < starting_date)) {
							return '<span class="badge badge-success"><i class="fas fa-check" aria-hidden="true"></i></span>';
						}
						return data ? moment(data).format('Do MMMM YYYY') : '-';
					},
				},
				{
					data: "tanggal_masuk_arsip",
					className: "dt-center",
					render: function(data, type, row) {
						return data ? moment(data).format('Do MMMM YYYY') : '-';
					},
				},
				{
					data: null,
					className: "dt-center",
					render: function(data, type, row, meta) {
						if (!row.tanggal_masuk_arsip) {
							return '-';
						}

						var matches = row.nomor_perkara.match(/^(\d+|[a-zA-Z]+)\//);
						if (matches) {
							return matches[1];
						}
						return data; // Return original data if pattern doesn't match
					}
				},
				{
					data: 'no_ruang',
					className: "dt-center",
					render: function(data, type, row, meta) {
						return data || '-';
					}
				},
				{
					data: 'no_lemari',
					className: "dt-center",
					render: function(data, type, row, meta) {
						return data || '-';
					}
				},
				{
					data: 'no_rak',
					className: "dt-center",
					render: function(data, type, row, meta) {
						return data || '-';
					}
				},
				{
					data: 'no_berkas',
					className: "dt-center",
					render: function(data, type, row, meta) {
						return data || '-';
					}
				},
				{
					data: 'status',
					className: "dt-center",
					render: function(data, type, row) {
						if (!row.tanggal_masuk_arsip) {
							return '-';
						}

						switch (data) {
							case '0':
								return '<span class="badge badge-danger">Tidak Ada</span>';
							case '1':
								return '<span class="badge badge-success">Ada</span>';
							default:
								return data;
						}
					},
				},
				{
					data: 'lengkap',
					className: "dt-center",
					render: function(data, type, row) {
						if (!row.tanggal_masuk_arsip) {
							return '-';
						}
						return data == 'Y' ? '<span class="badge badge-success">Lengkap</span>' : '<span class="badge badge-danger">Tidak Lengkap</span>'
					},
				},
				{
					data: 'nama_penyerah',
					render: function(data, type, row) {
						if (!row.tanggal_masuk_arsip) {
							return '-';
						}
						return data;
					},
				},
				{
					data: 'nama_penerima',
					render: function(data, type, row) {
						if (!row.tanggal_masuk_arsip) {
							return '-';
						}
						return data;
					},
				},
			],
		});
	});
</script>