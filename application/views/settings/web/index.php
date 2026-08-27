<div class="row">
	<div class="col-12">
		<div class="card leaves">
			<div class="card-header leaves align-items-center">
				<h5 class="m-0"><?php echo $title ?></h5>
			</div>
			<div class="card-body">
<div class="callout callout-info m-4 mt-3">
	<h6>Informasi</h6>
	<ul>
		<li>Klik kolom <code>Nama</code>, <code>URL</code>, <code>Urutan</code>, <code>Kategori</code>, <code>Status</code>, atau <code>Tampilkan Online</code> untuk mengubah data.</li>
	</ul>
</div>

<div class="table-responsive">
	<table id="table-web" class="display table-striped table-hover"></table>
</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function() {
		const theTime = '<?php echo time() ?>';
		initDataTable("#table-web", {
			title: "<?php echo $title ?>",
			ajax: {
				url: "<?php echo base_url('settings/web/get_list') ?>",
				data: function(d) {
					d['selectedCategory'] = $(`.dropdown-category-${theTime} select`).val();
					d['selectedStatus'] = $(`.dropdown-status-${theTime} select`).val();
					d['selectedShowOnline'] = $(`.dropdown-show-online-${theTime} select`).val();
					d[localStorage.getItem('csrfName')] = localStorage.getItem('csrfToken');
				}
			},
			layout: {
				topStart: {
					buttons: [{
						extend: 'dropdown',
						config: {
							id: 'dropdown-category-' + theTime,
							class: 'dropdown-category-' + theTime,
							placeholder: 'Kategori',
							allowClear: true,
							options: {
								'Socmed': 'Socmed',
								'Lokal': 'Lokal',
								'Web': 'Web',
								'MA': 'MA',
								'<?php echo SATKER_ESELON_1 ?>': '<?php echo SATKER_ESELON_1 ?>',
								'<?php echo SATKER_BANDING ?>': '<?php echo SATKER_BANDING ?>',
								'Lain-lain': 'Lain-lain',
							},
						},
					}, {
						extend: 'dropdown',
						config: {
							id: 'dropdown-status-' + theTime,
							class: 'dropdown-status-' + theTime,
							placeholder: 'Status',
							allowClear: true,
							options: {
								1: 'Aktif',
								0: 'Tidak Aktif',
							},
						},
					}, {
						extend: 'dropdown',
						config: {
							id: 'dropdown-show-online-' + theTime,
							class: 'dropdown-show-online-' + theTime,
							placeholder: 'Tampilkan Online',
							allowClear: true,
							options: {
								1: 'Ya',
								0: 'Tidak',
							},
						},
					}, {
						extend: 'customButton',
						text: '<span class="fas fa-plus" aria-hidden="true"></span> Tambah Web',
						url: '<?php echo base_url('settings/web/save') ?>',
						className: 'btn btn-sm btn-outline-success btn-modal',
					}]
				},
				topEnd: {
					buttons: []
				}
			},
			ajaxCellInput: [{
					column: 1,
					type: "textfield",
					url: "<?php echo base_url('settings/web/update_value/name') ?>",
					editable: 1,
				},
				{
					column: 2,
					type: "textfield",
					url: "<?php echo base_url('settings/web/update_value/url') ?>",
					editable: 1,
				},
				{
					column: 3,
					type: "number",
					url: "<?php echo base_url('settings/web/update_value/order') ?>",
					editable: 1,
				},
				{
					column: 4,
					type: function(row) {
						return 'dropdown';
					},
					options: [{
							value: 'Socmed',
							label: 'Socmed'
						},
						{
							value: 'Lokal',
							label: 'Lokal'
						},
						{
							value: 'Web',
							label: 'Web'
						},
						{
							value: 'MA',
							label: 'MA'
						},
						{
							value: '<?php echo SATKER_ESELON_1 ?>',
							label: '<?php echo SATKER_ESELON_1 ?>'
						},
						{
							value: '<?php echo SATKER_BANDING ?>',
							label: '<?php echo SATKER_BANDING ?>'
						},
						{
							value: 'Lain-lain',
							label: 'Lain-lain'
						},
					],
					url: "<?php echo base_url('settings/web/update_value/category') ?>",
					editable: 1,
				},
				{
					column: 5,
					type: function(row) {
						return 'dropdown';
					},
					options: [{
							value: '1',
							label: 'Aktif'
						},
						{
							value: '0',
							label: 'Tidak Aktif'
						},
					],
					url: "<?php echo base_url('settings/web/update_value/is_active') ?>",
					editable: 1,
				},
				{
					column: 6,
					type: function(row) {
						return 'dropdown';
					},
					options: [{
							value: '1',
							label: 'Ya'
						},
						{
							value: '0',
							label: 'Tidak'
						},
					],
					url: "<?php echo base_url('settings/web/update_value/show_online') ?>",
					editable: 1,
				},
			],
			columns: [{
					data: null,
					title: "No.",
					className: "dt-center",
					render: function(data, type, row, meta) {
						return meta.row + meta.settings._iDisplayStart + 1;
					}
				},
				{
					data: "name",
					title: "Nama",
				},
				{
					data: "url",
					title: "URL",
				},
				{
					data: "order",
					title: "Urutan",
					className: "dt-center",
				},
				{
					data: "category",
					title: "Kategori",
					className: "dt-center",
				},
				{
					data: "is_active",
					title: "Status",
					className: "dt-center",
					render: function(data) {
						return data == 1 ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-danger">Tidak Aktif</span>';
					}
				},
				{
					data: "show_online",
					title: "Tampilkan Online",
					className: "dt-center",
					render: function(data) {
						return data == 1 ? '<span class="badge bg-success">Ya</span>' : '<span class="badge bg-secondary">Tidak</span>';
					}
				},
				{
					data: null,
					title: "Aksi",
					className: "dt-center text-nowrap",
					orderable: false,
					searchable: false,
					render: function(data, type, row) {
						return '<a class="btn btn-sm btn-outline-success btn-modal" href="' + '<?php echo base_url("settings/web/view/") ?>' + row.id + '" title="Detail Web"><i class="fas fa-eye"></i></a> ' +
							'<a class="btn btn-sm btn-outline-primary btn-modal" href="' + '<?php echo base_url("settings/web/save/") ?>' + row.id + '" title="Edit Web"><i class="fas fa-pen"></i></a> ' +
							'<a class="btn btn-sm btn-outline-danger btn-confirm" href="' + '<?php echo base_url("settings/web/delete/") ?>' + row.id + '" data-confirm-message="Anda yakin akan menghapus web ' + row.name + '?" title="Hapus Web"><i class="fas fa-trash"></i></a>';
					}
				},
			]
		});
	});
</script>