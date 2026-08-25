<div class="row">
	<div class="col-12">
		<div class="card leaves">
			<div class="card-header leaves align-items-center">
				<h5 class="m-0"><?php echo $title ?></h5>
			</div>
			<div class="card-body">
				<div class="callout callout-info m-2 mt-0">
					<h6>Informasi</h6>
					<ul>
						<li><strong>Notifikasi tidak akan dikirimkan</strong> pada hari libur yang telah diset</li>
						<li>Gunakan filter tahun untuk melihat libur pada tahun tertentu.</li>
						<li><strong>Libur Nasional Tanggal Tetap</strong> akan otomatis berlaku setiap tahun.</li>
					</ul>
				</div>

				<div class="table-responsive">
					<table id="table-libur-list"></table>
				</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function() {
		table = initDataTable("#table-libur-list", {
			title: "<?php echo $title ?>",
			ajax: {
				url: "<?php echo base_url("settings/holiday/get_list") ?>",
				data: function(d) {
					d['selectedYear'] = $(`.table-libur_datepicker input[type="text"]`).val();
					d[localStorage.getItem('csrfName')] = localStorage.getItem('csrfToken');
				}
			},
			layout: {
				topStart: {
					buttons: [{
						extend: 'datepicker',
						config: {
							id: "table-libur_datepicker",
							minViewMode: 'years',
							format: 'yyyy',
							placeholder: 'Tahun',
							value: moment().year(),
						},
					}, ]
				},
				topEnd: {
					buttons: [{
						extend: 'customButton',
						text: '<span class="fa fa-plus" aria-hidden="true"></span> Tambah',
						url: '<?php echo base_url('settings/holiday/save') ?>',
						className: 'btn btn-outline-primary btn-modal',
					}],
				}
			},
			columns: [{
					data: null,
					title: "No",
					render: function(data, type, row, meta) {
						return meta.row + meta.settings._iDisplayStart + 1;
					}
				},
				{
					data: 'tanggal',
					title: "Tanggal",
					render: function(data, type, row) {
						return data ? (row.jenis_libur_id == 1 ? moment(data).format('Do MMMM') : moment(data).format('Do MMMM YYYY')) : '';
					}
				},
				{
					data: 'nama',
					title: "Nama Libur",
				},
				{
					data: 'jenis_libur',
					title: "Jenis Libur",
					className: 'dt-center',
					render: function(data, type, row) {
						const badge = {
							'1': 'success',
							'2': 'default',
							'3': 'info',
							'4': 'warning'
						};
						return data ? `<span class="badge badge-${badge[row.jenis_libur_id]}">${data}</span>` : '';
					}
				},
				{
					data: null,
					title: "",
					orderable: false,
					reorderable: false,
					className: 'dt-right',
					render: function(data, type, row) {
						return '<a href="<?php echo base_url("settings/holiday/save/") ?>' + row.id + '" title="Update Data" class="btn btn-xs btn-outline-success btn-modal"><i class="fa fa-pencil"></i></a>' +
							'<a href="<?php echo base_url("settings/holiday/delete/") ?>' + row.id + '" title="Hapus Data" class="btn btn-xs btn-outline-danger btn-confirm" data-confirm-message="Anda yakin akan menghapus ' + row.nama + '"><i class="fa fa-trash"></i></a>';
					}
				},
			]
		});
	});
</script>