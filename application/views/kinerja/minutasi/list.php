<div class="table-responsive">
	<table id="table-minutasi-<?php echo $this->uri->segment(4) ?>-<?php echo $this->uri->segment(5) ?>-<?php echo $this->uri->segment(6) ?>" class="display"></table>
</div>

<script type="text/javascript">
	$(document).ready(function() {
		const excludes = '<?php echo json_encode($excludedDates) ?>';
		const starting_date = '2024-07-01';

		table = initDataTable("#table-minutasi-<?php echo $this->uri->segment(4) ?>-<?php echo $this->uri->segment(5) ?>-<?php echo $this->uri->segment(6) ?>", {
			title: "<?php echo $title ?>",
			ajax: {
				url: "<?php echo base_url("kinerja/minutasi/get_list/{$this->uri->segment(4)}/{$this->uri->segment(5)}/{$this->uri->segment(6)}/{$this->uri->segment(7)}") ?>",
				data: function(d) {
					d[localStorage.getItem('csrfName')] = localStorage.getItem('csrfToken');
				}
			},
			select: {
				style: 'multi'
			},
			ajaxCellInput: [{
				column: 4,
				type: "datepicker",
				url: '<?php echo site_url("kinerja/minutasi/update_value_disposisi/tanggal_panmudg_terima") ?>',
				redirect: '<?php echo site_url("kinerja/minutasi/index") ?>',
				editable: '<?php echo $canEdit ?>',
				endDate: '+0d',
			}],
			rowCallback: function(row, data, index) {
				if (data.tanggal_panmudg_terima) {
					$(row).addClass('highlight');
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
					title: "Nomor Perkara",
					className: 'text-nowrap dt-center',
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
						return row.efiling_id == 1 ? 'Y' : (row.efiling_id == 0 ? 'T' : '');
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
						if (row.keterangan) badges.push(`<i>"${row.keterangan}"</i>`);
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
					data: null,
					title: "Keterangan",
					className: "include-export",
					visible: false,
					exportOptions: {
						visible: true
					},
					defaultContent: "",
					render: function(data, type, row) {
						return row.keterangan || '';
					}
				},
				{
					data: "tanggal_minutasi",
					title: "Tanggal Minutasi",
					className: "dt-center",
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
					data: "tanggal_panmudg_terima",
					title: "Tanggal Setor",
					className: "dt-center",
					render: function(data, type, row) {
						if (row.tanggal_putusan < starting_date) {
							return '<span class="badge badge-success"><i class="fa fa-check" aria-hidden="true"></i></span>';
						}
						return data ? moment(data).format('Do MMMM YYYY') : '-';
						// return type === 'export' ? data.replace(/[$,]/g, '') : data;
					},
				},
				{
					data: "diff_days",
					title: "Durasi Hari",
					className: "dt-center",
					render: function(data, type, row) {
						if (row.tanggal_putusan < starting_date) {
							return '<span class="badge badge-success"><i class="fa fa-check" aria-hidden="true"></i></span>';
						}
						return `${data} hari`;
					},
					createdCell: function(cell, cellData, rowData, rowIndex, colIndex) {
						var color = 'dodgerblue';
						if (cellData > 13) {
							color = 'orangered';
						} else if (cellData > 7) {
							color = 'darkorange';
						} else if (cellData > 1) {
							color = '#98980c';
						}
						$(cell).css({
							"font-weight": "bold",
							"font-size": "1.2em",
							"color": color,
						});
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
			]
		});
	});
</script>
