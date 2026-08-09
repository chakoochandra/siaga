<?php $perkaraId = $this->input->get('perkara_id') ?: null; ?>
<?php $tableId = $perkaraId ? 'perkara-' . $perkaraId : $this->uri->segment(4) . '-' . $this->uri->segment(5) . '-' . $this->uri->segment(6); ?>
<div class="table-responsive">
	<table id="table-bas-<?php echo $tableId ?>" class="display"></table>
</div>

<script type="text/javascript">
	$(document).ready(function() {
		const theTime = '<?php echo time() ?>';
		const isPutusFiltered = <?php echo ($this->uri->segment(8) == 2) ? 'true' : 'false' ?>;

		// Build columns array dynamically
		let columns = [{
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
				data: "tanggal_sidang",
				title: "Tanggal Sidang",
				className: "dt-center",
				render: function(data, type, row) {
					return data ? moment(data).format('Do MMMM YYYY') : '';
				}
			},
			{
				data: "agenda",
				title: "Agenda",
				className: "text-wrap",
			}, {
				data: "hakim_nama",
				title: "KM/PP",
				className: "text-nowrap",
				render: function(data, type, row) {
					if (type === 'export') {
						return data;
					}
					return data + '<br>PP: ' + row.panitera_nama;
				},
			}, {
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
			}, {
				data: "diff_days",
				title: "Durasi Hari",
				className: "dt-center",
				render: function(data, type, row) {
					return data ? `${data} hari` : '<span class="badge badge-success"><i class="fa fa-check" aria-hidden="true"></i></span>';
				},
				createdCell: function(cell, cellData, rowData, rowIndex, colIndex) {
					if (cellData) {
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
				}
			}
		];

		// Conditionally add Tanggal Putus column after Tanggal Sidang
		if (isPutusFiltered) {
			columns.push({
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
			}, {
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
			}, {
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
			}, {
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
			}, {
				data: null,
				title: "Keterangan",
				render: function(data, type, row) {
					return row.keterangan || '';
				}
			});
		}

		<?php $perkaraId = $this->input->get('perkara_id') ?: null; ?>

		initDataTable("#table-bas-<?php echo $tableId ?>", {
			title: "<?php echo $title ?>",
			ajax: {
				url: "<?php
						if ($perkaraId) {
							echo base_url('kinerja/bas/get_list') . '?perkara_id=' . $perkaraId;
						} else {
							echo base_url("kinerja/bas/get_list/{$this->uri->segment(4)}/{$this->uri->segment(5)}/{$this->uri->segment(6)}/{$this->uri->segment(7)}");
							if ($this->uri->segment(8)) {
								echo '/' . $this->uri->segment(8);
							}
						}
						?>",
				data: function(d) {
					d[localStorage.getItem('csrfName')] = localStorage.getItem('csrfToken');
				},
				dataSrc: function(json) {
					return json.data || [];
				}
			},
			columns: columns
		});
	});
</script>