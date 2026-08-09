<div class="card leaves">
	<div class="card-header leaves align-items-center">
		<h5 class="m-0"><?php echo $title ?></h5>
	</div>
	<div class="card-body">
		<div class="callout callout-info m-2 mt-0">
			<h6>Informasi</h6>
			<ul>
				<li>Klik kolom TANGGAL PENYERAHAN untuk menginput tanggal penyerahan putusan ke Panmud.</li>
				<li>Klik kolom TANGGAL RENCANA BHT untuk menentukan tanggal.</li>
			</ul>
		</div>

		<div class="card card-filter" style="display: none;">
			<div class="card-body">
				<form id="filter-form" class="row g-3 align-items-center">
					<div class="col-auto">
						<label class="form-label">Tanggal Putus:</label>
						<input type="text" class="form-control daterange_bht" placeholder="Tanggal Putus" value="">
					</div>
					<div class="col-auto">
						<label class="form-label">Tanggal Rencana BHT:</label>
						<input type="text" class="form-control daterange_rencana_bht" placeholder="Tanggal Rencana BHT" value="">
					</div>
					<div class="col-auto">
						<label class="form-label">Tanggal BHT:</label>
						<input type="text" class="form-control daterange_bht_status" placeholder="Tanggal BHT" value="">
					</div>
					<div class="col-auto">
						<label class="form-label">Status BHT:</label>
						<select class="form-select dropdown-bht-<?php echo time() ?> select2-picker">
							<option value="">Pilih Status BHT</option>
							<option value="1">Sudah BHT</option>
							<option value="2">Belum BHT</option>
						</select>
					</div>
					<!-- <div class="col-auto">
								<label class="form-label">Status AC:</label>
								<select class="form-select dropdown-ac-<php echo time() ?>">
									<option value="">Pilih Status AC</option>
									<option value="1">Sudah AC</option>
									<option value="2">Belum AC</option>
								</select>
							</div> -->
				</form>
			</div>
		</div>

		<div class="table-responsive">
			<table id="table-disposisi-<?php echo $this->uri->segment(4) ?>-<?php echo $this->uri->segment(5) ?>-<?php echo $this->uri->segment(6) ?>" class="display"></table>
		</div>
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function() {
		initDataTable("#table-disposisi-<?php echo $this->uri->segment(4) ?>-<?php echo $this->uri->segment(5) ?>-<?php echo $this->uri->segment(6) ?>", {
			title: "<?php echo $title ?>",
			ajax: {
				url: "<?php echo base_url("ck/bht/get_list") ?>",
				data: function(d) {
					d['selectedRange'] = $('.daterange_bht').val();
					d['selectedRangeBht'] = $('.daterange_bht_status').val();
					d['selectedRangeRencanaBht'] = $('.daterange_rencana_bht').val();
					d['selectedBht'] = $('.dropdown-bht-<?php echo time() ?>').val();
					d['selectedAc'] = $('.dropdown-ac-<?php echo time() ?>').val();
					d[localStorage.getItem('csrfName')] = localStorage.getItem('csrfToken');
				}
			},
			ajaxCellInput: [{
				column: 4,
				type: "datepicker",
				url: '<?php echo site_url("ck/bht/update_value_disposisi/tanggal_panmudg_terima") ?>',
				callback: '<?php echo site_url("ck/bht") ?>',
				editable: true
			}, {
				column: 5,
				type: "datepicker",
				url: '<?php echo site_url("ck/bht/update_value_disposisi/tanggal_rencana_bht") ?>',
				callback: '<?php echo site_url("ck/bht") ?>',
				editable: true
			}],
			rowCallback: function(row, data, index) {},
			columns: [{
					data: null,
					className: "dt-center",
					render: function(data, type, row, meta) {
						return meta.row + meta.settings._iDisplayStart + 1;
					}
				},
				{
					data: "nomor_perkara",
					title: "Nomor Perkara",
					className: 'dt-center text-nowrap',
					render: function(data, type, row) {
						if (type === 'export') {
							return data;
						}
						let result = `<strong>${data}</strong>`;
						if (row.jenis_perkara_nama || row.efiling_id) {
							result += '<br/>';
							if (row.jenis_perkara_nama) {
								result += `<span class="badge badge-info me-1">${row.jenis_perkara_nama}</span>`;
							}
							if (row.efiling_id) {
								result += '<span class="badge badge-success me-1">e-Court</span>';
							}
						}
						return result;
					}
				},
				{
					data: null,
					title: "Jenis Perkara",
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
					title: "e-Court",
					className: "include-export",
					visible: false,
					exportOptions: {
						visible: true
					},
					render: function(data, type, row) {
						return row.efiling_id == 1 ? 'Y' : (row.efiling_id == 0 ? 'T' : '');
					}
				},
				{
					data: "hakim_nama",
					title: "KM",
					className: "text-nowrap",
					render: function(data, type, row) {
						if (type === 'export') {
							return data;
						}
						return data + '<br>' + (row.panitera_nama || '').trim();
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
						if (!row.tanggal_panmudg_terima && !row.tanggal_bht) {
							const today = moment();
							let weekdayCount = 0;
							let current = moment(row.tanggal_putusan);
							while (current.isBefore(today)) {
								current.add(1, 'days');
								weekdayCount++;
							}
							const badgeClass = weekdayCount > 14 ? 'badge-danger' : (weekdayCount > 7 ? 'badge-warning' : 'badge-info');
							badges.push('<br><span class="badge ' + badgeClass + '">' + weekdayCount + ' hari</span>');
						}
						return dateStr + (badges.length ? '<br/>' + badges.join('') : '');
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
					render: function(data, type, row) {
						return row.status_putusan || '';
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
					title: "Verstek",
					className: "include-export",
					visible: false,
					exportOptions: {
						visible: true
					},
					render: function(data, type, row) {
						return row.putusan_verstek || 'T';
					}
				},
				{
					data: 'tanggal_panmudg_terima',
					title: 'Tanggal Penyerahan',
					className: "dt-center",
					render: function(data, type, row) {
						if (!data && row.tanggal_bht && moment(row.tanggal_bht).isBefore(moment(), 'day')) {
							return '<span class="badge badge-success"><i class="fa fa-check" aria-hidden="true"></i></span>';
						}

						if (!data) return '-';

						const tanggalPutus = moment(row.tanggal_putusan);
						const tanggalSetor = moment(data);

						if (!tanggalPutus.isValid() || !tanggalSetor.isValid()) {
							return data ? moment(data).format('Do MMMM YYYY') : '';
						}

						let weekdayCount = 0;
						let current = tanggalPutus.clone();
						const end = tanggalSetor.clone();

						while (current.isBefore(end)) {
							current.add(1, 'days');
							// if (current.day() !== 0 && current.day() !== 6) {
							weekdayCount++;
							// }
						}

						const badgeClass = weekdayCount > 14 ? 'badge-danger' : (weekdayCount > 7 ? 'badge-warning' : 'badge-info');

						if (!data) return '';
						return moment(data).format('Do MMMM YYYY') + '<br><span class="badge ' + badgeClass + '">' + weekdayCount + ' hari</span>';
					},
				},
				{
					data: 'tanggal_rencana_bht',
					title: 'Tanggal Rencana BHT',
					className: "dt-center",
					render: function(data, type, row) {
						if (!data && row.tanggal_bht && moment(row.tanggal_bht).isBefore(moment(), 'day')) {
							return '<span class="badge badge-success"><i class="fa fa-check" aria-hidden="true"></i></span>';
						}
						return data ? moment(data).format('Do MMMM YYYY') : '-';
					},
				},
				{
					data: 'tanggal_bht',
					title: 'Tanggal BHT',
					className: "dt-center",
					render: function(data, type, row) {
						return data ? moment(data).format('Do MMMM YYYY') : '<span class="badge badge-danger">Belum</span>';
					},
				},
			],
		});
	});
</script>