<div class="card leaves">
	<div class="card-header leaves align-items-center">
		<h5 class="m-0"><?php echo $title ?></h5>
	</div>
	<div class="card-body">
		<div class="card card-filter mb-2" style="display: none;">
			<?php $theTime = time() ?>

			<div class="card-body">
				<form id="filter-form" class="row g-3 align-items-center">
					<div class="col-auto">
						<label class="form-label">Tanggal Akta Cerai:</label>
						<input type="text" class="form-control daterange_akta_cerai" placeholder="Tanggal Akta Cerai"
							value="">
					</div>
					<div class="col-auto">
						<label class="form-label">Jenis Cerai:</label>
						<select class="form-select dropdown-jenis-cerai-<?php echo $theTime ?> select2-picker">
							<option value="">Pilih Jenis Cerai</option>
							<option value="346">Cerai Talak</option>
							<option value="347">Cerai Gugat</option>
						</select>
					</div>
					<div class="col-auto">
						<label class="form-label">Kriteria:</label>
						<select class="form-select dropdown-criteria-<?php echo $theTime ?> select2-picker">
							<option value="">Pilih Kriteria</option>
							<option value="usia">Usia di bawah 19 tahun</option>
							<option value="dk">Ada Dispensasi Kawin</option>
						</select>
					</div>
					<div class="col-auto">
						<label class="form-label">Agama Non Islam:</label>
						<select class="form-select dropdown-agama-<?php echo $theTime ?> select2-picker">
							<option value="">Pilih Agama</option>
							<?php foreach ($all_agama_options as $id => $nama): ?>
								<option value="<?php echo $id ?>"><?php echo htmlspecialchars($nama) ?></option>
							<?php endforeach; ?>
						</select>
					</div>

					<!-- NEW: Pendidikan filter -->
					<div class="col-auto">
						<label class="form-label">Pendidikan:</label>
						<select class="form-select dropdown-pendidikan-<?php echo $theTime ?> select2-picker">
							<option value="">Semua Pendidikan</option>
							<?php foreach ($all_pendidikan_options as $id => $nama): ?>
								<option value="<?php echo $id ?>"><?php echo htmlspecialchars($nama) ?></option>
							<?php endforeach; ?>
						</select>
					</div>

					<!-- NEW: Warga Negara filter -->
					<div class="col-auto">
						<label class="form-label">Warga Negara:</label>
						<select class="form-select dropdown-warganegara-<?php echo $theTime ?> select2-picker">
							<option value="">Semua Warga Negara</option>
							<?php foreach ($all_warganegara_options as $id => $nama): ?>
								<option value="<?php echo $id ?>"><?php echo htmlspecialchars($nama) ?></option>
							<?php endforeach; ?>
						</select>
					</div>

					<div class="col-auto">
						<label class="form-label">Pekerjaan:</label>
						<select class="form-select dropdown-pekerjaan-<?php echo $theTime ?> select2-picker">
							<option value="">Semua Pekerjaan</option>
							<?php foreach ($all_pekerjaan_options as $id => $nama): ?>
								<option value="<?php echo $id ?>"><?php echo htmlspecialchars($nama) ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</form>
			</div>
		</div>

		<!-- NEW: Ringkasan grid (cerai gugat, cerai talak, usia < 19, dispensasi kawin) -->
		<div class="card card-ringkasan mb-3">
			<div class="card-body">
				<h5 class="mb-3">Ringkasan Perkara</h5>
				<div class="row text-center" id="ringkasan-grid">
					<div class="col-md-3">
						<div class="border rounded p-3 stat-clickable" data-kind="jenis-cerai" data-value="347"
							title="Klik untuk filter Cerai Gugat">
							<div class="fs-4 fw-bold" id="ringkasan-cerai-gugat"><span
									class="spinner-border spinner-border-sm" role="status"></span></div>
							<div class="small text-muted">Cerai Gugat</div>
						</div>
					</div>
					<div class="col-md-3">
						<div class="border rounded p-3 stat-clickable" data-kind="jenis-cerai" data-value="346"
							title="Klik untuk filter Cerai Talak">
							<div class="fs-4 fw-bold" id="ringkasan-cerai-talak"><span
									class="spinner-border spinner-border-sm" role="status"></span></div>
							<div class="small text-muted">Cerai Talak</div>
						</div>
					</div>
					<div class="col-md-3">
						<div class="border rounded p-3 stat-clickable" data-kind="criteria" data-value="usia"
							title="Klik untuk filter Usia di bawah 19 tahun">
							<div class="fs-4 fw-bold" id="ringkasan-usia"><span class="spinner-border spinner-border-sm"
									role="status"></span></div>
							<div class="small text-muted">Usia &lt; 19 Tahun</div>
						</div>
					</div>
					<div class="col-md-3">
						<div class="border rounded p-3 stat-clickable" data-kind="criteria" data-value="dk"
							title="Klik untuk filter Ada Dispensasi Kawin">
							<div class="fs-4 fw-bold" id="ringkasan-dk"><span class="spinner-border spinner-border-sm"
									role="status"></span></div>
							<div class="small text-muted">Ada Dispensasi Kawin</div>
						</div>
					</div>
				</div>

				<h5 class="mt-4">Ringkasan Demografi (Penggugat + Tergugat)</h5>
				<div class="row" id="summary-grid">
					<div class="col-md-3">
						<div class="fw-bold mb-2">Agama</div>
						<div id="summary-agama" class="small text-muted" style="max-height: 250px; overflow-y: auto;">
							Memuat...</div>
					</div>
					<div class="col-md-3">
						<div class="fw-bold mb-2">Pendidikan</div>
						<div id="summary-pendidikan" class="small text-muted"
							style="max-height: 250px; overflow-y: auto;">Memuat...</div>
					</div>
					<div class="col-md-3">
						<div class="fw-bold mb-2">Pekerjaan</div>
						<div id="summary-pekerjaan" class="small text-muted"
							style="max-height: 250px; overflow-y: auto;">Memuat...</div>
					</div>
					<div class="col-md-3">
						<div class="fw-bold mb-2">Warga Negara</div>
						<div id="summary-warganegara" class="small text-muted"
							style="max-height: 250px; overflow-y: auto;">Memuat...</div>
					</div>
				</div>
			</div>
		</div>

		<div class="table-responsive">
			<table id="table-cerai" class="display"></table>
		</div>
	</div>
</div>

<style>
	.stat-clickable {
		cursor: pointer;
		transition: background-color .15s ease;
	}

	.stat-clickable:hover {
		background-color: rgba(0, 0, 0, .05);
	}

	#summary-grid tr[data-kind] {
		cursor: pointer;
	}

	#summary-grid tr[data-kind]:hover {
		background-color: rgba(0, 0, 0, .05);
	}
</style>

<script type="text/javascript">
	$(document).ready(function () {
		const theTime = '<?php echo time() ?>';
		const title = "<?php echo $title ?>";

		// Reverse-lookup tables so a clicked summary label (e.g. "SD", "Guru")
		// can be translated back into the value the matching filter dropdown
		// expects — the same value a user would pick manually.
		const agamaOptions = <?php echo json_encode($all_agama_options, JSON_UNESCAPED_UNICODE) ?>;
		const pendidikanOptions = <?php echo json_encode($all_pendidikan_options, JSON_UNESCAPED_UNICODE) ?>;
		const warganegaraOptions = <?php echo json_encode($all_warganegara_options, JSON_UNESCAPED_UNICODE) ?>;
		const pekerjaanOptions = <?php echo json_encode($all_pekerjaan_options, JSON_UNESCAPED_UNICODE) ?>;

		const filterSelectors = {
			'jenis-cerai': `.dropdown-jenis-cerai-${theTime}`,
			criteria: `.dropdown-criteria-${theTime}`,
			agama: `.dropdown-agama-${theTime}`,
			pendidikan: `.dropdown-pendidikan-${theTime}`,
			warganegara: `.dropdown-warganegara-${theTime}`,
			pekerjaan: `.dropdown-pekerjaan-${theTime}`,
		};

		// Finds the option value whose label matches the clicked text.
		// `nullValue` covers groups where the dropdown has a dedicated
		// "not set" option (only Agama does, via 'tidak_diset').
		function reverseLookup(optionsObj, label, nullValue = null) {
			for (const [value, nama] of Object.entries(optionsObj || {})) {
				if (nama === label) return value;
			}
			if (nullValue && label === 'Tidak Diisi') return nullValue;
			return null;
		}

		// Returns the dropdown value for a given summary kind/label, or null
		// if there is no corresponding filter option (e.g. "Islam" isn't in
		// the Agama dropdown since that list only offers non-Islam religions).
		// "Tidak Diisi" is handled via the nullValue fallback for every group
		// below, since each dropdown now has its own "Tidak Diset" option.
		function resolveFilterValue(kind, label) {
			switch (kind) {
				case 'agama':
					return reverseLookup(agamaOptions, label, 'tidak_diset');
				case 'pendidikan':
					return reverseLookup(pendidikanOptions, label, 'tidak_diset');
				case 'warganegara':
					return reverseLookup(warganegaraOptions, label, 'tidak_diset');
				case 'pekerjaan':
					return reverseLookup(pekerjaanOptions, label, 'tidak_diset');
				default:
					return null;
			}
		}

		// Sets a filter dropdown's value and fires 'change', exactly as if
		// the user had picked it manually — this reuses whatever binding
		// already reloads the table/summary on filter change.
		function applyFilterFromClick(kind, value) {
			const sel = filterSelectors[kind];
			if (!sel || value === null || value === undefined) return;
			$(sel).val(value).trigger('change');
		}

		function getFilterData() {
			return {
				selectedRange: $('.daterange_akta_cerai').val(),
				selectedJenisCerai: $(`.dropdown-jenis-cerai-${theTime}`).val(),
				selectedCriteria: $(`.dropdown-criteria-${theTime}`).val(),
				selectedAgama: $(`.dropdown-agama-${theTime}`).val(),
				selectedPendidikan: $(`.dropdown-pendidikan-${theTime}`).val(),
				selectedWargaNegara: $(`.dropdown-warganegara-${theTime}`).val(),
				selectedPekerjaan: $(`.dropdown-pekerjaan-${theTime}`).val(),
			};
		}

		function renderSummaryColumn(el, rows, kind) {
			if (!rows || !rows.length) {
				$(el).html('<em>Tidak ada data</em>');
				return;
			}
			const total = rows.reduce((s, r) => s + r.jumlah, 0);
			let html = '<table class="table table-sm mb-0">';
			rows.forEach(r => {
				const pct = total ? ((r.jumlah / total) * 100).toFixed(1) : 0;
				const value = resolveFilterValue(kind, r.label);
				const attrs = value !== null ?
					` data-kind="${kind}" data-value="${value}" title="Klik untuk filter ${r.label}"` :
					'';
				html += `<tr${attrs}><td>${r.label}</td><td class="text-end">${Number(r.jumlah).toLocaleString('id-ID')} <span class="text-muted">(${pct}%)</span></td></tr>`;
			});
			html += '</table>';
			$(el).html(html);
		}

		function renderRingkasan(ringkasan) {
			if (!ringkasan) return;
			$('#ringkasan-cerai-gugat').text((ringkasan.cerai_gugat ?? 0).toLocaleString('id-ID'));
			$('#ringkasan-cerai-talak').text((ringkasan.cerai_talak ?? 0).toLocaleString('id-ID'));
			$('#ringkasan-usia').text((ringkasan.usia_kurang_19 ?? 0).toLocaleString('id-ID'));
			$('#ringkasan-dk').text((ringkasan.dispensasi_kawin ?? 0).toLocaleString('id-ID'));
		}

		function loadSummary() {
			$.ajax({
				url: "<?php echo base_url("rekapitulasi/cerai/get_summary") ?>",
				method: "POST",
				dataType: "json", // NEW: force JSON parsing regardless of response Content-Type
				data: {
					...getFilterData(),
					[localStorage.getItem('csrfName')]: localStorage.getItem('csrfToken'),
				},
				success: function (res) {
					if (!res || !res.status) {
						console.error('get_summary: unexpected response', res);
						return;
					}
					renderSummaryColumn('#summary-agama', res.data['Agama'], 'agama');
					renderSummaryColumn('#summary-pendidikan', res.data['Pendidikan'], 'pendidikan');
					renderSummaryColumn('#summary-pekerjaan', res.data['Pekerjaan'], 'pekerjaan');
					renderSummaryColumn('#summary-warganegara', res.data['Warga Negara'], 'warganegara');
					renderRingkasan(res.ringkasan);
				},
				error: function (xhr, status, err) {
					console.error('get_summary failed:', status, err, xhr.responseText);
					$('#summary-agama, #summary-pendidikan, #summary-pekerjaan, #summary-warganegara')
						.html('<span class="text-danger">Gagal memuat data</span>');
					$('#ringkasan-cerai-gugat, #ringkasan-cerai-talak, #ringkasan-usia, #ringkasan-dk').text('-');
				}
			});
		}

		const table = initDataTable("#table-cerai", {
			title,
			ajax: {
				url: "<?php echo base_url("rekapitulasi/cerai/get_list") ?>",
				data: function (d) {
					Object.assign(d, getFilterData());
					d[localStorage.getItem('csrfName')] = localStorage.getItem('csrfToken');
				}
			},
			columns: [{
				data: null,
				title: "No.",
				render: function (data, type, row, meta) {
					return meta.row + meta.settings._iDisplayStart + 1;
				}
			},
			{
				data: "nomor_perkara",
				title: "Nomor Perkara",
				className: 'dt-center text-nowrap',
				render: function (data, type, row) {
					if (type === 'export') {
						return data;
					}
					let result = `<strong>${data}</strong>`;
					if (row.jenis_perkara_nama || row.efiling_id || row.ghaib) {
						result += '<br/>';
						if (row.jenis_perkara_nama) {
							result += `<span class="badge badge-info me-1">${row.jenis_perkara_nama}</span>`;
						}
						if (row.efiling_id) {
							result += '<span class="badge badge-success me-1">e-Court</span>';
						}
						if (row.ghaib == 1) {
							result += '<span class="badge badge-warning me-1">Ghaib</span>';
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
				data: null,
				title: "Ghaib",
				className: "include-export",
				visible: false,
				exportOptions: {
					visible: true
				},
				render: function (data, type, row) {
					return row.ghaib == 1 ? 'Y' : (row.ghaib == 0 ? 'T' : '');
				}
			},
			{
				data: "tanggal_pendaftaran",
				title: "Tanggal Daftar",
				className: "dt-center text-nowrap",
				render: function (data, type, row) {
					return data ? moment(data).format('Do MMMM YYYY') : '-';
				},
			},
			{
				data: "tanggal_putusan",
				title: "Tanggal Putus",
				className: "dt-center text-nowrap",
				render: function (data, type, row) {
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
				render: function (data, type, row) {
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
				render: function (data, type, row) {
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
				render: function (data, type, row) {
					return row.putusan_verstek || 'T';
				}
			},
			{
				data: 'tanggal_bht',
				title: 'Tanggal BHT',
				className: "dt-center",
				render: function (data, type, row) {
					return data ? moment(data).format('Do MMMM YYYY') : '<span class="badge badge-danger">Belum</span>';
				},
			},
			{
				data: "tgl_akta_cerai",
				title: "Tanggal Akta Cerai",
				className: "dt-center text-nowrap",
				render: function (data, type, row) {
					if (type === 'export') {
						return data ? moment(data).format('Do MMMM YYYY') : '';
					}
					return data ? moment(data).format('Do MMMM YYYY') : '<span class="badge badge-danger">Belum</span>';
				},
			},
			// {
			// 	data: 'hakim_nama',
			// 	title: "Majelis",
			// 	className: "text-nowrap",
			// 	render: function(data, type, row, meta) {
			// 		return `${data}<br/>${row.panitera_nama}`;
			// 	}
			// },
			{
				data: 'pihak1_text',
				title: "Penggugat",
				className: "text-nowrap",
				createdCell: function (td, cellData, rowData, row, col) {
					if (rowData.umur_p < 19) {
						$(td).addClass('bg-danger');
					}
				},
				render: function (data, type, row, meta) {
					if (type === 'export') {
						return data;
					}

					let usiaCerai = getAgeAt(row.ttl_p, row.tgl_akta_cerai);
					let usiaDk = getAgeAt(row.ttl_p, row.tanggal_putusan_dk);
					return `${data}` +
						`<br/><small>Agama: ${row.agama_p || '-'}</small>` +
						// `<br/><small>NIK: ${row.nik_p||'-'}</small>` +
						`<br/><small>Tanggal Lahir: ${moment(row.ttl_p).format('Do MMMM YYYY')}</small>` +
						(usiaDk ? `<br><small>Usia DK: ${usiaDk}</small>` : '') +
						`<br/><small>Usia Cerai: ${usiaCerai ? `${usiaCerai} tahun` : '-'}</small>` +
						`<br/><small>Pekerjaan: ${row.pekerjaan_p || '-'}</small>` +
						`<br/><small>Pendidikan: ${row.pendidikan_p || '-'}</small>`;
				}
			},
			{
				data: null,
				title: "Penggugat - Tanggal Lahir",
				className: "include-export",
				visible: false,
				exportOptions: {
					visible: true
				},
				defaultContent: "",
				render: function (data, type, row) {
					return row.ttl_p ? moment(row.ttl_p).format('Do MMMM YYYY') : '';
				}
			},
			{
				data: null,
				title: "Penggugat - Usia DK",
				className: "include-export",
				visible: false,
				exportOptions: {
					visible: true
				},
				defaultContent: "",
				render: function (data, type, row) {
					return getAgeAt(row.ttl_p, row.tanggal_putusan_dk) || '';
				}
			},
			{
				data: null,
				title: "Penggugat - Usia Cerai",
				className: "include-export",
				visible: false,
				exportOptions: {
					visible: true
				},
				defaultContent: "",
				render: function (data, type, row) {
					let usiaCerai = getAgeAt(row.ttl_p, row.tgl_akta_cerai);
					return usiaCerai ? `${usiaCerai} tahun` : '';
				}
			},
			{
				data: null,
				title: "Penggugat - Pekerjaan",
				className: "include-export",
				visible: false,
				exportOptions: {
					visible: true
				},
				defaultContent: "",
				render: function (data, type, row) {
					return row.pekerjaan_p || '';
				}
			},
			{
				data: null,
				title: "Penggugat - Pendidikan",
				className: "include-export",
				visible: false,
				exportOptions: {
					visible: true
				},
				defaultContent: "",
				render: function (data, type, row) {
					return row.pendidikan_p || '';
				}
			},
			{
				data: 'pihak2_text',
				title: "Tergugat",
				className: "text-nowrap",
				createdCell: function (td, cellData, rowData, row, col) {
					if (rowData.umur_t < 19) {
						$(td).addClass('bg-danger');
					}
				},
				render: function (data, type, row, meta) {
					if (type === 'export') {
						return data;
					}

					let usiaCerai = getAgeAt(row.ttl_t, row.tgl_akta_cerai);
					let usiaDk = getAgeAt(row.ttl_t, row.tanggal_putusan_dk);
					return `${data}` +
						`<br/><small>Agama: ${row.agama_t || '-'}</small>` +
						// `<br/><small>NIK: ${row.nik_t||'-'}</small>` +
						`<br/><small>Tanggal Lahir: ${moment(row.ttl_t).format('Do MMMM YYYY')}</small>` +
						(usiaDk ? `<br><small>Usia DK: ${usiaDk}</small>` : '') +
						`<br/><small>Usia Cerai: ${usiaCerai || '-'}</small>` +
						`<br/><small>Pekerjaan: ${row.pekerjaan_t || '-'}</small>` +
						`<br/><small>Pendidikan: ${row.pendidikan_t || '-'}</small>`;
				}
			},
			{
				data: null,
				title: "Tergugat - Tanggal Lahir",
				className: "include-export",
				visible: false,
				exportOptions: {
					visible: true
				},
				defaultContent: "",
				render: function (data, type, row) {
					return row.ttl_t ? moment(row.ttl_t).format('Do MMMM YYYY') : '';
				}
			},
			{
				data: null,
				title: "Tergugat - Usia DK",
				className: "include-export",
				visible: false,
				exportOptions: {
					visible: true
				},
				defaultContent: "",
				render: function (data, type, row) {
					return getAgeAt(row.ttl_t, row.tanggal_putusan_dk) || '';
				}
			},
			{
				data: null,
				title: "Tergugat - Usia Cerai",
				className: "include-export",
				visible: false,
				exportOptions: {
					visible: true
				},
				defaultContent: "",
				render: function (data, type, row) {
					let usiaCerai = getAgeAt(row.ttl_t, row.tgl_akta_cerai);
					return usiaCerai ? `${usiaCerai} tahun` : '';
				}
			},
			{
				data: null,
				title: "Tergugat - Pekerjaan",
				className: "include-export",
				visible: false,
				exportOptions: {
					visible: true
				},
				defaultContent: "",
				render: function (data, type, row) {
					return row.pekerjaan_t || '';
				}
			},
			{
				data: null,
				title: "Tergugat - Pendidikan",
				className: "include-export",
				visible: false,
				exportOptions: {
					visible: true
				},
				defaultContent: "",
				render: function (data, type, row) {
					return row.pendidikan_t || '';
				}
			},
			{
				data: 'jumlah_anak',
				title: "Jumlah Anak",
				className: 'dt-center',
			},
			{
				data: 'usia_pernikahan',
				title: 'Usia Pernikahan',
			},
			{
				data: 'kua_tempat_nikah',
				title: 'KUA Terdaftar',
			},
			{
				data: 'posita',
				title: "Gugatan",
				visible: false
			}
			]
		});

		// Refresh the summary grid every time the table reloads
		// (covers filter changes, since those already trigger table.ajax.reload()
		// via your existing global filter-change binding)
		$('#table-cerai').on('xhr.dt', function () {
			loadSummary();
		});

		// Click a demography summary row (Agama/Pendidikan/Pekerjaan/Warga Negara)
		// to apply it as a filter, same as picking it from the dropdown manually.
		$('#summary-grid').on('click', 'tr[data-kind]', function () {
			applyFilterFromClick($(this).data('kind'), $(this).data('value'));
		});

		// Click a ringkasan box (Cerai Gugat/Talak, Usia < 19, Dispensasi Kawin)
		// to apply it as a filter, same as picking it from the dropdown manually.
		$('#ringkasan-grid').on('click', '.stat-clickable', function () {
			applyFilterFromClick($(this).data('kind'), $(this).data('value'));
		});
	});
</script>