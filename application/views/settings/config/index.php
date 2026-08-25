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
						<li>Klik kolom <code>VALUE</code> untuk mengubah data.</li>
					</ul>
				</div>

				<div class="table-responsive">
					<table id="table-configs" class="display"></table>
				</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function() {
		initDataTable("#table-configs", {
			ajax: {
				url: "<?php echo base_url("settings/config/get_list") ?>",
			},
			// layout: {
			// 	topEnd: {
			// 		buttons: [{
			// 			extend: 'customButton',
			// 			text: '<span class="fas fa-plus" aria-hidden="true"></span> Tambah Konfigurasi',
			// 			url: '<?php echo base_url('settings/config/save') ?>',
			// 			className: 'btn btn-sm btn-outline-success btn-modal',
			// 		}]
			// 	}
			// },
			ajaxCellInput: [{
					column: 2,
					type: function(row) {
						if (row.key === 'ID_WILAYAH') return 'dropdown';
						if (row.category == 3) return "number";
						if (row.category == 4) return "dropdown";
						return row.category == 8 ? "datepicker" : "textfield";
					},
					options: function(row) {
						if (row.key === 'ID_WILAYAH') {
							return [{
								value: '',
								label: 'Pilih Kota/Kabupaten'
							}, <?php foreach ($this->kota_options as $kode => $nama): ?> {
								value: '<?php echo $kode ?>',
								label: '<?php echo addslashes($nama) ?>'
							}, <?php endforeach; ?>];
						}
						if (row.category == 4) {
							return [{
								value: '1',
								label: 'Ya'
							}, {
								value: '0',
								label: 'Tidak'
							}];
						}
						return [];
					},
					url: "<?php echo base_url('settings/config/update_value/value') ?>",
					editable: 1,
				},
				// {
				// 	column: 3,
				// 	type: "textfield",
				// 	url: "<php echo base_url('settings/config/update_value/note') ?>",
				// 	editable: 1,
				// },
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
					data: "key",
					title: "Key",
					className: "text-nowrap",
				},
			{
				data: "value",
				title: "Value",
				className: "text-break-all",
				render: function(data, type, row) {
					if (row.category == 4 && (data === '1' || data === '0')) {
						return data === '1' ? '<span class="badge bg-success">Ya</span>' : '<span class="badge bg-secondary">Tidak</span>';
					}
					return data;
				}
			},
				{
					data: "note",
					title: "Keterangan",
				},
			]
		});
	});
</script>