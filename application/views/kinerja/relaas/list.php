<?php if (!defined('ID_WILAYAH') || ID_WILAYAH === '' || ID_WILAYAH === null): ?>
	<div class="callout callout-warning my-2">
		<p>Konfigurasi <code>ID_WILAYAH</code> (kode kabupaten) belum diatur. Silakan atur di <a
				href="<?php echo base_url('settings/config') ?>">Settings > Konfigurasi</a>.</p>
	</div>
<?php else: ?>
	<?php $theTime = time() ?>

	<div class="card leaves">
		<div class="card-header leaves align-items-center">
			<h5 id="sidang-title" class="m-0"><?php echo $title ?></h5>
		</div>

		<div class="callout callout-danger">
			<h6>PERHATIAN</h6>
			<ul>
				<li>Daftar berikut adalah hasil prediksi dari pola yang diformulasikan sedemikian rupa untuk mendeteksi
					ketidaklengkapan relaas.</li>
				<li>Apabila sidang memang tidak ada panggilannya, silakan diabaikan saja.</li>
			</ul>
		</div>

		<div class="card-body mt-2">
			<?php if (!isset($show_filter_form) || $show_filter_form === true): ?>
				<div class="card card-filter" style="display: none;">
					<div class="card-body">
						<form id="filter-form" class="row g-3 align-items-center">
							<div class="col-auto">
								<label class="form-label">Tanggal Sidang:</label>
								<input type="text" class="form-control daterange_relaas" placeholder="Tanggal sidang" value="">
							</div>
							<div class="col-auto">
								<label class="form-label">Jurusita:</label>
								<select class="form-select dropdown-jurusita-<?php echo $theTime ?> select2-picker">
									<option value="">Pilih Jurusita</option>
									<?php foreach ($all_jurusita as $jurusita): ?>
										<option value="<?php echo $jurusita->id ?>"><?php echo $jurusita->nama_gelar ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="col-auto">
								<label class="form-label">Kecamatan:</label>
								<select class="form-select dropdown-kecamatan-<?php echo $theTime ?> select2-picker">
									<?php foreach ($all_kecamatan as $kode => $nama): ?>
										<option value="<?php echo $kode ?>"><?php echo $nama ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="col-auto">
								<label class="form-label">Status Input:</label>
								<select class="form-select dropdown-input-<?php echo $theTime ?> select2-picker">
									<option value="">Pilih Status Input</option>
									<option value="Sudah">Sudah Input</option>
									<option value="Belum">Belum Input</option>
								</select>
							</div>
							<div class="col-auto">
								<label class="form-label">Status Unggah:</label>
								<select class="form-select dropdown-upload-<?php echo $theTime ?> select2-picker">
									<option value="">Pilih Status Unggah</option>
									<option value="Sudah">Sudah Unggah</option>
									<option value="Belum">Belum Unggah</option>
								</select>
							</div>
							<div class="col-auto">
								<label class="form-label">e-Summon:</label>
								<select class="form-select dropdown-esummon-<?php echo $theTime ?> select2-picker">
									<option value="">Pilih e-Summon</option>
									<option value="Ya">Ya</option>
									<option value="Bukan">Bukan</option>
								</select>
							</div>
						</form>
					</div>
				</div>
			<?php endif; ?>

			<div class="table-responsive">
				<table id="table-relaas" class="display"></table>
			</div>
		</div>
	</div>

	<script type="text/javascript">
		$(document).ready(function () {
			const theTime = '<?php echo $theTime ?>';
			const sippUrl = '<?php echo get_sipp_url() ?>';

			initDataTable("#table-relaas", {
				title: "<?php echo $title ?>",
				ajax: {
					url: "<?php echo rtrim(base_url("kinerja/relaas/get_list/{$this->uri->segment(4)}/{$this->uri->segment(5)}/{$this->uri->segment(6)}/{$this->uri->segment(7)}"), '/') ?>",
					data: function (d) {
						d['selectedRange'] = $('.daterange_relaas').val();
						d['selectedKecamatan'] = $(`.dropdown-kecamatan-${theTime}`).val();
						d['selectedJurusita'] = $(`.dropdown-jurusita-${theTime}`).val();
						d['selectedEsummon'] = $(`.dropdown-esummon-${theTime}`).val();
						d['selectedInput'] = $(`.dropdown-input-${theTime}`).val();
						d['selectedUpload'] = $(`.dropdown-upload-${theTime}`).val();
						d['perkara_id'] = '<?php echo $this->input->get('perkara_id') ?>';
						d[localStorage.getItem('csrfName')] = localStorage.getItem('csrfToken');
					}
				},
				rowCallback: function (row, data, index) {
					if (
						!data.tanggal_relaas ||
						(
							!data.doc_relaas ||
							(typeof data.doc_relaas === 'string' && !data.doc_relaas.includes('resources'))
						)
					) {
						$(row).addClass('highlight-warning');
					}
				},
				columns: [{
					data: null,
					title: "No.",
					className: "dt-center",
					render: function (data, type, row, meta) {
						return meta.row + meta.settings._iDisplayStart + 1;
					}
				},
				{
					data: "nomor_perkara",
					title: "Nomor Perkara",
					className: "dt-center text-nowrap",
					render: function (data, type, row) {
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
								result += `<span class="badge badge-success me-1">e-Court</span>`;
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
					render: function (data, type, row) {
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
					render: function (data, type, row) {
						return row.efiling_id == 1 ? 'Y' : (row.efiling_id == 0 ? 'T' : '');
					}
				},
				{
					data: "tanggal_sidang",
					title: "Tanggal Sidang",
					className: "dt-center",
					render: function (data, type, row) {
						return data ? moment(data).format('Do MMMM YYYY') : '-';
					},
				},
				{
					data: "jurusita_nama",
					title: "Jurusita",
				},
				{
					data: "kecamatan_nama",
					title: "Kecamatan",
					render: function (data, type, row) {
						return (row.kecamatan_kode == '11.11.11' ? 'e-Summon' : data);
					},
				},
				{
					data: "pihak_nama",
					title: "Pihak",
					render: function (data, type, row) {
						return data + ' <span class="badge badge-info">' + (row.pihak_ket == 'pengacara' ? 'Kuasa Hukum ' + (row.pihak_ke == 1 ? 'P' : (row.pihak_ke == 2 ? 'T' : '-')) : (row.pihak_ke == 1 ? 'P' : (row.pihak_ke == 2 ? 'T' : '-'))) + '</span>';
					},
				},
				{
					data: "agenda",
					title: "Agenda",
				},
				{
					data: "tanggal_relaas",
					title: "Tanggal Relaas",
					className: "dt-center",
					render: function (data, type, row) {
						return data ? moment(data).format('Do MMMM YYYY') : '<span class="badge badge-danger">Belum Input</span>';
					},
				},
				{
					data: "doc_relaas",
					title: "Relaas",
					className: "dt-center",
					render: function (data, type, row) {
						const url = sippUrl ? '<a href="' + (sippUrl + '/' + data) + '" target="_blank"><i class="fa fa-file-pdf-o"></i></a>' : '<span class="badge badge-info">Ada</span>';
						const uploadUrl = row.sipp_detail_url ? '<a href="' + row.sipp_detail_url + '" target="_blank"><i class="fa fa-upload text-danger"></i></a>' : '<span class="badge badge-danger">Belum Unggah</span>';
						return data && data.includes('resources') ? url : uploadUrl;
					},
				},
				]
			});

		});
	</script>
<?php endif; ?>