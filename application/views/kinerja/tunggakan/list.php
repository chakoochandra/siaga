<div class="table-responsive">
	<table id="table-tunggakan-<?php echo $this->uri->segment(4) ?>-<?php echo $this->uri->segment(5) ?>-<?php echo $this->uri->segment(6) ?>-<?php echo $this->uri->segment(7) ?>" class="display"></table>
</div>

<script type="text/javascript">
	$(document).ready(function() {
		const bucket = '<?php echo $this->uri->segment(4) ?>';
		const hakimId = '<?php echo $this->uri->segment(5) ?>';
		const selectedYear = '<?php echo $this->uri->segment(6) ?>';
		const status = '<?php echo $this->uri->segment(7) ?: 'all' ?>';

		function diffMonthsDays(fromDate) {
			if (!fromDate) return '-';
			const start = moment(fromDate).startOf('day');
			const end = moment().startOf('day');
			const months = end.diff(start, 'months');
			const days = end.diff(start.clone().add(months, 'months'), 'days');
			return `${months} bulan ${days} hari`;
		}

		const columns = [{
				data: null,
				title: "No.",
				className: 'dt-center',
				render: function(data, type, row, meta) {
					return meta.row + meta.settings._iDisplayStart + 1;
				}
			},
			{
				data: "nomor_perkara",
				title: "Nomor Perkara",
				className: "dt-center text-nowrap",
				render: function(data, type, row) {
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
				data: "hakim_nama",
				title: "KM/PP",
				className: "text-nowrap",
				render: function(data, type, row) {
					if (type === 'export') {
						return data;
					}
					const kmKode = row.hakim_kode ? ` (${row.hakim_kode})` : '';
					const ppKode = row.panitera_kode ? ` (${row.panitera_kode})` : '';
					const km = (data || '-') + kmKode;
					const pp = (row.panitera_nama || '-') + ppKode;
					return km + '<br>PP: ' + pp;
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
				data: 'pihak1_text',
				title: "Pihak",
				className: 'text-nowrap',
				render: function(data, type, row) {
					return data + (row.pihak2_text ? '<br/>' + row.pihak2_text : '');
				}
			},
			{
				data: "tanggal_pendaftaran",
				title: "Tanggal Daftar",
				className: "dt-center text-nowrap",
				render: function(data, type, row) {
					return formatDate(data);
				},
			},
			{
				data: "tanggal_sidang_terakhir",
				title: "Sidang Terakhir",
				className: "dt-center text-nowrap",
				render: function(data, type, row) {
					return formatDate(data);
				},
			},
			{
				data: "durasi_text",
				title: "Lama Proses",
				className: "dt-center",
				render: function(data) {
					return data || '-';
				},
				createdCell: function(cell, cellData, rowData) {
					const days = parseInt(rowData.durasi_hari || 0, 10);
					let color = 'dodgerblue';
					// Use day thresholds aligned to month buckets (~30 days per month):
					// >= 150 days (~>= 5 months), >= 120 days (~>= 4 months), >= 90 days (~>= 3 months)
					if (days >= 150) {
						color = 'orangered';
					} else if (days >= 120) {
						color = 'orange';
					} else if (days >= 90) {
						color = 'darkorange';
					}
					$(cell).css({
						"font-weight": "bold",
						"color": color,
					});
				}
			},
			{
				data: "agenda_sidang_terakhir",
				title: "Agenda Sidang Terakhir",
				className: "text-wrap",
				render: function(data) {
					return data || '-';
				}
			},
		];

		initDataTable("#table-tunggakan-<?php echo $this->uri->segment(4) ?>-<?php echo $this->uri->segment(5) ?>-<?php echo $this->uri->segment(6) ?>-<?php echo $this->uri->segment(7) ?>", {
			title: "<?php echo $title ?>",
			ajax: {
				url: "<?php echo base_url("kinerja/tunggakan/get_list/{$this->uri->segment(4)}/{$this->uri->segment(5)}/{$this->uri->segment(6)}/" . ($this->uri->segment(7) ?: 'all') . "") ?>",
				data: function(d) {
					d[localStorage.getItem('csrfName')] = localStorage.getItem('csrfToken');
				}
			},
			columns: columns,
			ordering: true,
		});
	});
</script>
