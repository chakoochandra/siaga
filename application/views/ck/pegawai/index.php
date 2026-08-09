<div class="card leaves">
	<div class="card-header leaves align-items-center">
		<h5 class="m-0"><?php echo $title ?></h5>
	</div>
	<div class="card-body">
		<div class="table-responsive">
			<table id="table-pegawai" class="display table-striped table-hover"></table>
		</div>
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function() {
		const theTime = '<?php echo time() ?>';

		function escapeHtml(str) {
			return $('<div>').text(str == null ? '' : String(str)).html();
		}

		initDataTable("#table-pegawai", {
			title: "<?php echo $title ?>",
			ajax: {
				url: "<?php echo base_url('ck/pegawai/get_list') ?>",
				data: function(d) {
					d['selectedJabatan'] = $(`.dropdown-jabatan-${theTime} select`).val();
					d['selectedAktif'] = $(`.dropdown-aktif-${theTime} select`).val();
					d[localStorage.getItem('csrfName')] = localStorage.getItem('csrfToken');
				}
			},
			layout: {
				topStart: {
					buttons: [{
							extend: 'dropdown',
							config: {
								id: 'dropdown-jabatan-' + theTime,
								class: 'dropdown-jabatan-' + theTime,
								placeholder: 'Jabatan',
								options: {
									'Hakim': 'Hakim',
									'Panitera Pengganti': 'Panitera Pengganti',
									'Jurusita': 'Jurusita',
									'Jurusita Pengganti': 'Jurusita Pengganti',
								},
								width: 250,
							},
						},
						{
							extend: 'dropdown',
							config: {
								id: 'dropdown-aktif-' + theTime,
								class: 'dropdown-aktif-' + theTime,
								placeholder: 'Status',
								options: {
									1: 'Aktif',
									2: 'Non Aktif',
								},
							},
						},
					]
				},
				topEnd: {
					buttons: [{
						extend: 'customButton',
						text: '<span class="fa fa-plus" aria-hidden="true"></span> Tambah Pegawai',
						url: '<?php echo base_url('ck/pegawai/save') ?>',
						className: 'btn btn-sm btn-outline-success btn-modal',
					}]
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
					data: "nip",
					title: "NIP",
					className: "dt-center text-nowrap",
				},
				{
					data: "nama_gelar",
					title: "Nama",
					className: "text-nowrap",
					render: function(data, type, row) {
						if (type !== 'display') {
							return (data && data.trim() !== '') ? data : (row.nama_lengkap || '-');
						}
						return escapeHtml((data && data.trim() !== '') ? data : (row.nama_lengkap || '-'));
					},
				},
				{
					data: "jabatan",
					title: "Jabatan",
					className: "text-nowrap",
				},
				{
					data: "phone",
					title: "Phone",
					className: "text-nowrap",
				},
				{
					data: "aktif",
					title: "Aktif",
					className: "text-nowrap dt-center",
					render: function(data, type, row) {
						if (!row.nip || row.nip === '') {
							return '<span class="badge bg-primary">Aktif</span>';
						}
						if (data === 'Y') {
							return '<span class="badge bg-success">Aktif</span>';
						} else if (data === 'T') {
							return '<span class="badge bg-danger">Non Aktif</span>';
						}
						return '<span class="badge bg-secondary">Tidak Diketahui</span>';
					},
				},
				{
					data: "username",
					title: "Username",
					className: "text-nowrap",
				},
				{
					data: null,
					title: "",
					orderable: false,
					reorderable: false,
					className: "dt-right",
					render: function(data, type, row) {
						const rowId = encodeURIComponent(row.id);
						const displayName = escapeHtml(row.nama_gelar || row.nama_lengkap || '');
						return `<span><a class='btn btn-sm btn-outline-info btn-modal' href='<?php echo base_url("ck/pegawai/save") ?>/${rowId}' title="Perbarui"><i class="fa fa-pencil"></i></a></span>` +
							`<span><a class='btn btn-sm btn-outline-warning btn-modal' href='<?php echo base_url("ck/pegawai/change_password") ?>/${rowId}' title="Ganti Kata Sandi"><i class="fa-solid fa-key"></i></a></span>` +
							`<span><a class='btn btn-sm btn-outline-danger btn-confirm' data-confirm-message='Anda yakin akan menghapus data ${displayName}?' href='<?php echo base_url('ck/pegawai/delete') ?>/${rowId}'><i class="fa fa-trash"></i></a></span>`;
					}
				},
			],
			rowCallback: function(row, data) {
				if (!data.nip || data.nip === '') {
					$(row).css('background-color', '#fff3cd');
				} else if (data.aktif === 'T') {
					$(row).css('background-color', '#fff3cd');
				} else if (data.aktif && data.aktif !== 'Y') {
					$(row).css('background-color', '#e2e3e5');
				}
			},
		});
	});
</script>