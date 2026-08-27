<div class="card leaves">
	<div class="card-header leaves align-items-center">
		<h5 class="m-0"><?php echo $title ?></h5>
	</div>
	<div class="card-body">
		<div class="table-responsive">
			<table id="table-wa-message-log" class="display"></table>
		</div>
	</div>
</div>

<script>
	$(document).ready(function() {
		const theTime = '<?php echo time() ?>';

		var table = initDataTable("#table-wa-message-log", {
			title: "<?php echo $title ?>",
			ajax: {
				url: "<?php echo base_url('whatsapp/log/get_list') ?>",
				data: function(d) {
					d.type_filter = $(`.dropdown-type-${theTime} select`).val();
					d.sent_time_range = $('.daterange_wa_log input.form-control').val();
					d.success_filter = $(`.dropdown-success-${theTime} select`).val();
					d[localStorage.getItem('csrfName')] = localStorage.getItem('csrfToken');
				}
			},
			layout: {
				topStart: {
					buttons: [{
							extend: 'daterange',
							config: {
								id: "daterange_wa_log",
								colSearch: 1,
								format: 'YYYY-MM-DD',
								opens: 'right',
								placeholder: 'Tanggal Kirim'
							}
						},
						{
							extend: 'dropdown',
							config: {
								id: 'dropdown-type-' + theTime,
								class: 'dropdown-type-' + theTime,
								placeholder: 'Jenis Pesan',
								options: {
									'': 'Semua Jenis',
									<?php foreach ($distinct_types as $type): ?> '<?php echo addslashes($type->type) ?>': '<?php echo addslashes($type->type) ?>',
									<?php endforeach; ?>
								},
							},
						},
						{
							extend: 'dropdown',
							config: {
								id: 'dropdown-success-' + theTime,
								class: 'dropdown-success-' + theTime,
								placeholder: 'Status Pengiriman',
								options: {
									'': 'Semua Status',
									'1': 'Berhasil',
									'0': 'Gagal'
								},
							},
						},
					],
				}
			},
			columns: [{
					data: null,
					title: 'No',
					render: function(data, type, row, meta) {
						return meta.row + meta.settings._iDisplayStart + 1;
					}
				},
				{
					data: 'sent_time',
					title: 'Waktu Kirim',
					render: function(data, type, row) {
						return formatDate(data, 'DD MMM YYYY HH:mm:ss');
					}
				},
				{
					data: 'phone_number',
					title: 'Nomor Tujuan'
				},
				{
					data: 'type',
					title: 'Jenis Pesan'
				},
				{
					data: 'text',
					title: 'Teks Pesan',
					render: function(data, type, row) {
						return data ? (data.length > 50 ? data.substring(0, 50) + '...' : data) : '';
					}
				},
				{
					data: 'success',
					title: 'Status',
					render: function(data, type, row) {
						if (data == 1) {
							return row.note + '<br/><span class="badge bg-success">Berhasil</span>';
						} else {
							return row.note + '<br/><span class="badge bg-danger">Gagal</span>';
						}
					}
				},
				{
					data: null,
					className: 'dt-right hide-on-print text-nowrap',
					orderable: false,
					render: function(data, type, row) {
						return '<a class="btn btn-sm btn-outline-primary btn-modal" href="<?php echo base_url("whatsapp/log/view/") ?>' + row.id + '" title="Lihat Detail"><i class="fa fa-eye"></i></a>';
					}
				}
			],
			order: [
				[1, 'desc']
			]
		});
	});
</script>