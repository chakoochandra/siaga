<div class="table-responsive">
	<table id="table-perkara" class="display"></table>
</div>

<script type="text/javascript">
	$(document).ready(function() {
		const theTime = '<?php echo time() ?>';

		initDataTable("#table-perkara", {
			title: "<?php echo $title ?>",
			ajax: {
				url: "<?php echo base_url("rekapitulasi/pk/get_list/{$this->uri->segment(4)}/{$this->uri->segment(5)}/{$this->uri->segment(6)}/{$this->uri->segment(7)}/{$this->uri->segment(8)}/{$this->uri->segment(9)}") ?>",
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
					data: "nomor_perkara_pn",
					title: "Perkara",
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
						if (row.putusan_pn) {
							result += `<br/>Putus: ${moment(row.putusan_pn).format('Do MMMM YYYY')}`;
						}
						if (row.proses_terakhir_text) {
							result += `<br/><span class="badge badge-info">${row.proses_terakhir_text}</span>`;
						}
						if (row.status_putusan) {
							result += `<br/><span class="badge badge-info">${row.status_putusan}</span>`;
						}
						if (row.putusan_verstek) {
							result += row.putusan_verstek == 'Y' ? ' <span class="badge badge-warning">Verstek</span>' : ' <span class="badge badge-secondary">Tidak Verstek</span>';
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
					data: null,
					title: "Tanggal Putus",
					className: "include-export",
					visible: false,
					exportOptions: {
						visible: true
					},
					render: function(data, type, row) {
						return row.tanggal_putusan ? moment(row.tanggal_putusan).format('Do MMMM YYYY') : '';
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
					data: null,
					title: "Banding",
					className: "text-nowrap",
					render: function(data, type, row) {
						if (type === 'export') {
							return '';
						}
						let result = '';
						if (row.permohonan_banding) {
							result += `Nomor Banding: ${row.nomor_perkara_banding || '-'}<br/>Permohonan: ${moment(row.permohonan_banding).format('Do MMMM YYYY')}`;
						}
						if (row.putusan_banding) {
							result += row.permohonan_banding ? '<br/>' : '';
							result += `Putus: ${moment(row.putusan_banding).format('Do MMMM YYYY')}`;
						}
						// Add putusan signature badges inline
						if (row.status_banding_text) {
							result += `<br/><span class="badge badge-${row.status_banding_id == 491 ? 'warning' : (row.status_banding_id == 492 ? 'danger' : 'default')}">${row.status_banding_text}</span>`;
						}
						if (row.status_putusan_banding_text) {
							result += ` <span class="badge badge-primary">${row.status_putusan_banding_text}</span>`;
						}
						if (row.pemberitahuan_putusan_banding) {
							result += `<br/>Pemberitahuan: ${moment(row.pemberitahuan_putusan_banding).format('Do MMMM YYYY')}`;
						}
						return result;
					},
				},
				{
					data: null,
					title: "Tanggal Putus Banding",
					className: "include-export",
					visible: false,
					exportOptions: {
						visible: true
					},
					render: function(data, type, row) {
						return row.putusan_banding ? moment(row.putusan_banding).format('Do MMMM YYYY') : '';
					}
				},
				{
					data: null,
					title: "Status Putusan Banding",
					className: "include-export",
					visible: false,
					exportOptions: {
						visible: true
					},
					render: function(data, type, row) {
						return row.status_putusan_banding_text || '';
					}
				},
				{
					data: null,
					title: "Status Banding",
					className: "include-export",
					visible: false,
					exportOptions: {
						visible: true
					},
					render: function(data, type, row) {
						return row.status_banding_text || '';
					}
				},
				{
					data: null,
					title: "Kasasi",
					className: "text-nowrap",
					render: function(data, type, row) {
						if (type === 'export') {
							return '';
						}
						let result = '';
						if (row.permohonan_kasasi) {
							result += `Nomor Kasasi: ${row.nomor_perkara_kasasi || '-'}<br/>Permohonan: ${moment(row.permohonan_kasasi).format('Do MMMM YYYY')}`;
						}
						if (row.putusan_kasasi) {
							result += row.permohonan_kasasi ? '<br/>' : '';
							result += `Putus: ${moment(row.putusan_kasasi).format('Do MMMM YYYY')}`;
						}
						// Add putusan signature badges inline
						if (row.status_kasasi_text) {
							result += `<br/><span class="badge badge-${row.status_kasasi_id == 491 ? 'warning' : (row.status_kasasi_id == 492 ? 'danger' : 'default')}">${row.status_kasasi_text}</span>`;
						}
						if (row.status_putusan_kasasi_text) {
							result += ` <span class="badge badge-primary">${row.status_putusan_kasasi_text}</span>`;
						}
						if (row.pemberitahuan_putusan_kasasi) {
							result += `<br/>Pemberitahuan: ${moment(row.pemberitahuan_putusan_kasasi).format('Do MMMM YYYY')}`;
						}
						return result;
					},
				},
				{
					data: null,
					title: "Tanggal Putus Kasasi",
					className: "include-export",
					visible: false,
					exportOptions: {
						visible: true
					},
					render: function(data, type, row) {
						return row.putusan_kasasi ? moment(row.putusan_kasasi).format('Do MMMM YYYY') : '';
					}
				},
				{
					data: null,
					title: "Status Putusan Kasasi",
					className: "include-export",
					visible: false,
					exportOptions: {
						visible: true
					},
					render: function(data, type, row) {
						return row.status_putusan_kasasi_text || '';
					}
				},
				{
					data: null,
					title: "Status Kasasi",
					className: "include-export",
					visible: false,
					exportOptions: {
						visible: true
					},
					render: function(data, type, row) {
						return row.status_kasasi_text || '';
					}
				},
				{
					data: "permohonan_pk",
					title: "Permohonan PK",
					className: "dt-center",
					render: function(data, type, row) {
						return data ? moment(data).format('Do MMMM YYYY') : '';
					},
				},
				{
					data: 'nomor_perkara_pk',
					title: "Nomor PK",
					className: 'text-nowrap',
					render: function(data, type, row) {
						return data ? data : '-';
					},
				},
				{
					data: "penerimaan_memori_pk",
					title: "Memori PK",
					className: "text-nowrap",
					render: function(data, type, row) {
						return `
                            Tanggal Penerimaan Memori: ${row.penerimaan_memori_pk?moment(row.penerimaan_memori_pk).format('Do MMMM YYYY'):'-'}<br/>
                            Tanggal Penyerahan Memori: ${row.penyerahan_memori_pk?moment(row.penyerahan_memori_pk).format('Do MMMM YYYY'):'-'}<br/>
                            Tanggal Penerimaan Kontra Memori: ${row.penerimaan_kontra_pk?moment(row.penerimaan_kontra_pk).format('Do MMMM YYYY'):'-'}<br/>
                            Tanggal Penyerahan Kontra Memori: ${row.penyerahan_kontra_pk?moment(row.penyerahan_kontra_pk).format('Do MMMM YYYY'):'-'}<br/>
                        `;
					},
				},
				{
					data: "pengiriman_berkas_pk",
					title: "Berkas PK",
					className: "text-nowrap",
					render: function(data, type, row) {
						return `
                            Surat: ${row.nomor_surat_pengiriman_berkas_pk?row.nomor_surat_pengiriman_berkas_pk:'-'}<br/>
                            Pengiriman: ${row.pengiriman_berkas_pk?moment(row.pengiriman_berkas_pk).format('Do MMMM YYYY'):'-'}<br/>
                            Penerimaan: ${row.penerimaan_berkas_pk?moment(row.penerimaan_berkas_pk).format('Do MMMM YYYY'):'-'}
                        `;
					},
				},
				{
					data: "putusan_pk",
					title: "Putusan PK",
					className: "text-nowrap",
					render: function(data, type, row) {
						let statusClass = {
							491: 'warning',
							492: 'danger',
							500: 'success',
						};
						return (row.status_putusan_pk_text ? `<span class="badge badge-primary">${row.status_putusan_pk_text}</span><br/>` : '') +
							`<span class="badge badge-${statusClass[row.status_pk_id] !== undefined ? statusClass[row.status_pk_id] : 'default'}">${row.status_pk_text?row.status_pk_text:'-'}</span><br/>` +
							(row.putusan_pk ? `Putus: ${row.putusan_pk ? moment(row.putusan_pk).format('Do MMMM YYYY') : '-'}<br/>` : '') +
							`Pemberitahuan Putusan: ${row.pemberitahuan_putusan_pk ? moment(row.pemberitahuan_putusan_pk).format('Do MMMM YYYY') : '-'}`;
					},
				},
				{
					data: "majelis_hakim_pk",
					title: "Majelis Hakim",
					className: "text-nowrap",
					render: function(data, type, row) {
						return data && data.trim() ? (data + `<br/>Panitera Pengganti: ${row.panitera_pengganti_pk?row.panitera_pengganti_pk:'-'}`) : '-';
					},
				},
				{
					data: "pemohon_pk",
					title: "Pemohon PK",
					className: "text-nowrap",
				},
			]
		});
	});
</script>
