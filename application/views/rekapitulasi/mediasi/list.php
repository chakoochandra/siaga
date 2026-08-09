<div class="table-responsive">
	<table id="table-perkara" class="display"></table>
</div>

<script type="text/javascript">
	$(document).ready(function() {
		const theTime = '<?php echo time() ?>';

		initDataTable("#table-perkara", {
			title: "<?php echo $title ?>",
			ajax: {
				url: "<?php echo base_url("rekapitulasi/mediasi/get_list/{$this->uri->segment(4)}/{$this->uri->segment(5)}/{$this->uri->segment(6)}") ?>",
				data: function(d) {
					d[localStorage.getItem('csrfName')] = localStorage.getItem('csrfToken');
				}
			},
			columns: [{
					data: null,
					title: "No.",
					render: function(data, type, row, meta) {
						return meta.row + meta.settings._iDisplayStart + 1;
					}
				},
				{
					data: "nomor_perkara",
					title: 'Nomor Perkara',
					className: "dt-center text-nowrap",
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
						return result;
					},
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
						return row.efiling == 1 ? 'Y' : (row.efiling == 0 ? 'T' : '');
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
					render: function(data, type, row) {
						return row.ghaib == 1 ? 'Ghaib' : '';
					}
				},
				{
					data: "tanggal_pendaftaran",
					title: "Tanggal Pendaftaran",
					className: "text-nowrap",
					render: function(data, type, row) {
						return data ? moment(data).format('Do MMMM YYYY') : '';
					},
				},
				{
					data: "nama_gelar",
					title: "Mediator",
					className: "text-nowrap",
				},
				{
					data: "dimulai_mediasi",
					title: "Mulai Mediasi",
					className: "text-nowrap",
					render: function(data, type, row) {
						return data ? moment(data).format('Do MMMM YYYY') : '';
					},
				},
				{
					data: "keputusan_mediasi",
					title: "Keputusan Mediasi",
					className: "text-nowrap",
					render: function(data, type, row) {
						return data ? moment(data).format('Do MMMM YYYY') : '';
					},
				},
				{
					data: "hasil_mediasi",
					title: "Hasil Mediasi",
					className: "dt-center text-nowrap",
				},
				{
					data: "tanggal_cabut",
					title: "Tanggal Cabut",
					className: "text-nowrap",
					render: function(data, type, row) {
						return data ? moment(data).format('Do MMMM YYYY') : '';
					},
				}
			]
		});
	});
</script>
