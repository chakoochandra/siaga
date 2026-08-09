<div class="table-responsive">
	<table id="table-perkara" class="display"></table>
</div>

<script type="text/javascript">
	$(document).ready(function() {
		initDataTable("#table-perkara", {
			title: "<?php echo $title ?>",
			ajax: {
				url: "<?php echo base_url("rekapitulasi/kecamatan/get_list/{$this->uri->segment(4)}/{$this->uri->segment(5)}/{$this->uri->segment(6)}/{$this->uri->segment(7)}/{$this->uri->segment(8)}/{$this->uri->segment(9)}") ?>",
				data: function(d) {
					d[localStorage.getItem('csrfName')] = localStorage.getItem('csrfToken');
				}
			},
			columnDefs: [{
				targets: [0, 2],
				className: "dt-center"
			}],
			columns: [{
					data: null,
					title: "No.",
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
					render: function(data, type, row) {
						return data ? moment(data).format('Do MMMM YYYY') : '';
					},
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
				{
					data: "hakim_nama",
					title: "KM",
					className: "text-nowrap",
					render: function(data, type, row) {
						if (type === 'export') {
							return data;
						}
						return data + '<br>PP: ' + row.panitera_nama;
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
					data: "para_pihak",
					title: "Pihak",
					className: "text-nowrap",
				},
				{
					data: "alamat_p",
					title: "Alamat P",
				},
			]
		});
	});
</script>
