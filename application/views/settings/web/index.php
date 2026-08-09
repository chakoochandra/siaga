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
						<li>Taruh icon/thumbnail untuk web di folder assets\images</li>
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
		initDataTable("#table-web", {
			title: "<?php echo $title ?>",
			ajax: {
				url: "<?php echo base_url('settings/web/get_list') ?>",
			},
			layout: {
				topEnd: {
					buttons: [{
						extend: 'customButton',
						text: '<span class="fa fa-plus" aria-hidden="true"></span> Tambah Web',
						url: '<?php echo base_url('settings/web/save') ?>',
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
					data: "name",
					title: "Nama",
				},
				{
					data: "url",
					title: "URL",
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
					data: null,
					title: "Aksi",
					className: "dt-center text-nowrap",
					orderable: false,
					searchable: false,
					render: function(data, type, row) {
						return '<a class="btn btn-sm btn-outline-primary btn-modal" href="' + '<?php echo base_url("settings/web/save/") ?>' + row.id + '" title="Edit Web"><i class="fa fa-pencil"></i></a> ' +
							'<a class="btn btn-sm btn-outline-danger btn-confirm" href="' + '<?php echo base_url("settings/web/delete/") ?>' + row.id + '" data-confirm-message="Anda yakin akan menghapus web ' + row.name + '?" title="Hapus Web"><i class="fa fa-trash"></i></a>';
					}
				},
			]
		});
	});
</script>