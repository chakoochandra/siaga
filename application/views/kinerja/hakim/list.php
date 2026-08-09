<div class="table-responsive">
	<table id="table-perkara" class="display"></table>
</div>

<script type="text/javascript">
	$(document).ready(function() {
		const excludes = '<?php echo json_encode($excludedDates) ?>';
		const theTime = '<?php echo time() ?>';
		const status = '<?php echo $this->uri->segment(4) ?>';
		const show_km = '<?php echo json_encode(['belum_ada_edoc', 'tunggakan', 'tunggakan_bulan_lalu', 'sisa_bulan_lalu', 'sudah_ada_edoc', 'belum_anonimasi', 'belum_minutasi', 'cabut', 'dikabulkan', 'ditolak', 'tidak_diterima', 'digugurkan', 'dicoret', 'sisa_bulan_ini',  'jumlah_semua',  'lain_lain',  'damai', 'minutasi', 'belum_pertimbangan_hukum', 'jumlah_bulan_ini', 'belum_ada_phs', 'sisa_bulan_kohort', 'sisa_sebelumnya_tahun']) ?>';
		const show_putusan = '<?php echo json_encode(['belum_ada_edoc', 'sudah_ada_edoc', 'belum_anonimasi', 'belum_minutasi', 'cabut', 'dikabulkan', 'ditolak', 'tidak_diterima', 'digugurkan', 'dicoret',  'jumlah_semua',  'lain_lain',  'damai', 'minutasi', 'belum_pertimbangan_hukum']) ?>';
		const show_pendaftaran = '<?php echo json_encode(['sisa_bulan_lalu', 'terima', 'jumlah_bulan_ini', 'sisa_bulan_ini', 'tunggakan', 'tunggakan_bulan_lalu', 'masuk_hari_ini', 'belum_ada_gugatan', 'ecourt_hari_ini', 'belum_ada_pmh', 'belum_ada_phs', 'sisa_bulan_kohort', 'sisa_sebelumnya_tahun']) ?>';
		<?php
		$seg4 = $this->uri->segment(4);
		$seg5 = $this->uri->segment(5);
		$seg6 = $this->uri->segment(6);
		$seg7 = $this->uri->segment(7);
		$seg8 = $this->uri->segment(8);
		$seg9 = $this->uri->segment(9);
		$seg10 = $this->uri->segment(10);
		$urlList = base_url("kinerja/hakim/get_list/{$seg4}/{$seg5}/{$seg6}/{$seg7}/{$seg8}/{$seg9}" . ($seg10 !== null && $seg10 !== '' ? "/{$seg10}" : ""));
		?>
		const urlList = '<?php echo $urlList ?>';

		initDataTable("#table-perkara", {
			title: "<?php echo $title ?>",
			ajax: {
				url: urlList,
				data: function(d) {
					d[localStorage.getItem('csrfName')] = localStorage.getItem('csrfToken');
				}
			},
			columns: [{
					data: null,
					title: "No.",
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
					data: "tanggal_pendaftaran",
					title: "Tanggal Pendaftaran",
					className: "dt-center",
					visible: show_pendaftaran.includes(status),
					render: function(data, type, row) {
						return data ? moment(data).format('Do MMMM YYYY') : '';
					},
				},
				{
					data: "tanggal_putusan",
					title: "Tanggal Putus",
					className: "dt-center text-nowrap",
					visible: (show_putusan.includes(status) || status.includes("putus") || status.includes("redaksi") || status.includes("published") || status.startsWith("dk_") || status.startsWith("alasan_") || status.includes("dirput")) && !show_pendaftaran.includes(status),
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
					defaultContent: "",
					exportOptions: {
						visible: true
					},
					render: function(data, type, row) {
						return row.proses_terakhir_text || '';
					}
				},
				{
					data: null,
					title: "Status Putusan",
					className: "include-export",
					visible: false,
					defaultContent: "",
					exportOptions: {
						visible: true
					},
					render: function(data, type, row) {
						return row.status_putusan || '';
					}
				},
				{
					data: null,
					title: "Verstek",
					className: "include-export",
					visible: false,
					defaultContent: "",
					exportOptions: {
						visible: true
					},
					render: function(data, type, row) {
						return row.putusan_verstek;
					}
				},
				{
					data: "tanggal_minutasi",
					title: "Tanggal Minutasi",
					className: "dt-center",
					visible: (show_putusan.includes(status) || status.includes("putus") || status.includes("redaksi") || status.includes("dirput") || status.includes("published")) && !show_pendaftaran.includes(status),
					render: function(data, type, row) {
						let start = moment(row.tanggal_putusan).startOf('day');
						let end = data ? moment(data).startOf('day') : moment().startOf('day');
						let diffDays = diffDatesExcludeHoliday(start, end, excludes);
						let statusClass = diffDays <= 1 ? 'success' : 'warning';
						return data ?
							`${moment(data).format('Do MMMM YYYY')}<br/><span class="badge badge-${statusClass}">${Math.abs(diffDays)} hari</span>` :
							'<span class="badge badge-danger">Belum</span>';
					},
				},
				{
					data: "tanggal_transaksi",
					title: "Tanggal Redaksi",
					className: "dt-center",
					visible: status.includes("putus") || status.includes("redaksi"),
					render: function(data, type, row) {
						return data ? moment(data).format('Do MMMM YYYY') : '-';
					},
				},
				{
					data: "tanggal_sidang",
					title: "Tanggal Sidang",
					className: "dt-center",
					visible: status.includes("relaas"),
					render: function(data, type, row) {
						return data ? moment(data).format('Do MMMM YYYY') : '-';
					},
				},
				{
					data: "tanggal_relaas",
					title: "Tanggal Relaas",
					className: "dt-center",
					visible: status.includes("relaas"),
					render: function(data, type, row) {
						return data ? moment(data).format('Do MMMM YYYY') : '-';
					},
				},
				{
					data: "jurusita_nama",
					title: "Jurusita",
					visible: status.includes("relaas"),
					defaultContent: "",
					render: function(data, type, row) {
						return data || "";
					}
				},
				{
					data: "pihak_nama",
					title: "Pihak",
					visible: status.includes("relaas"),
					render: function(data, type, row) {
						return data + ' <span class="badge badge-info">' + (row.pihak_ket == 'pengacara' ? 'Kuasa Hukum ' + (row.pihak_ke == 1 ? 'P' : (row.pihak_ke == 2 ? 'T' : '-')) : (row.pihak_ke == 1 ? 'P' : (row.pihak_ke == 2 ? 'T' : '-'))) + '</span>';
					},
				},
				{
					data: "alasan_menikah",
					title: "Alasan Menikah",
					className: "dt-center",
					visible: status.startsWith("dk_"),
				},
				{
					data: "nama_mempelai",
					title: "Nama Mempelai",
					visible: status.startsWith("dk_"),
				},
				{
					data: "jenis_mempelai",
					title: "Jenis Kelamin",
					className: "text-nowrap dt-center",
					visible: status.startsWith("dk_"),
					render: function(data, type, row) {
						return data == 1 ? 'Pria' : 'Wanita';
					},
				},
				{
					data: "tanggal_lahir_mempelai",
					title: "Tanggal Lahir Mempelai",
					className: "text-nowrap dt-center",
					visible: status.startsWith("dk_"),
					render: function(data, type, row) {
						return data ? moment(data).format('Do MMMM YYYY') : '';
					},
				},
				{
					data: "umur_mempelai",
					title: "Umur Mempelai",
					className: "text-nowrap dt-center",
					visible: status.startsWith("dk_"),
					render: function(data, type, row) {
						return data + ' tahun';
					},
				},
				{
					data: "pendidikan_mempelai",
					title: "Pendidikan Mempelai",
					className: "dt-center",
					visible: status.startsWith("dk_"),
				},
				{
					data: "pekerjaan_mempelai",
					title: "Pekerjaan Mempelai",
					className: "dt-center",
					visible: status.startsWith("dk_"),
				},
				{
					data: "hakim_nama",
					title: "KM",
					className: "text-nowrap",
					visible: show_km.includes(status) || status.includes("putus") || status.includes("redaksi") || status.includes("dirput") || status.includes("published"),
					render: function(data, type, row) {
						const namaHakim = (row.hakim_kode ? '[' + row.hakim_kode + '] ' : '') + data + '/' + (row.panitera_nama || '');
						return namaHakim;
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
					data: "doc_url",
					title: "e-Doc Putusan",
					className: "text-wrap dt-center",
					defaultContent: null,
					// visible: !status.startsWith("dk_") && !status.includes("relaas"),
					visible: (show_putusan.includes(status) || status.includes("putus") || status.includes("redaksi") || status.includes("published") || status.startsWith("dk_") || status.startsWith("alasan_") || status.includes("dirput")) && !show_pendaftaran.includes(status),
					render: function(data, type, row) {
						return data ? '<a href="' + (data) + '" target="_blank"><i class="fa fa-file-pdf-o"></i></a>' : '';
					},
				},
				{
					data: "filename",
					title: "Nama File",
					className: "text-wrap",
					visible: false,
					defaultContent: "",
					// visible: status.includes("published"),
				},
				{
					data: "link_dirput",
					title: "Link Dirput",
					className: "dt-center",
					visible: status.includes("published"),
					defaultContent: "",
					render: function(data, type, row) {
						return !data ? '<span class="badge badge-danger"><i class="fa fa-times" aria-hidden="true"></i></span>' : `<a href="${data}" target="_blank"><span class="badge badge-success"><i class="fa fa-link" aria-hidden="true"></i></span></a>`;
					},
				},
				{
					data: "keterangan",
					title: "Keterangan",
					visible: status.includes("dirput_error"),
				},
			]
		});
	});
</script>