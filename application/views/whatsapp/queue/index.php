<div class="card leaves">
	<div class="card-header leaves align-items-center">
		<h5 class="m-0"><?php echo $title ?></h5>
	</div>
	<div class="card-body">
		<div class="table-responsive">
			<table id="waqueueTable" class="display"></table>
		</div>
	</div>
</div>

<!-- Status Change Confirmation Modal -->
<div class="modal fade" id="statusChangeModal" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Konfirmasi Perubahan Status</h5>
				<button type="button" class="btn-close ms-0" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<p>Apakah Anda yakin ingin mengubah status item ini?</p>
				<p>Status baru: <span id="newStatusText"></span></p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
				<button type="button" class="btn btn-primary" id="confirmStatusChange">Ya, Ubah</button>
			</div>
		</div>
	</div>
</div>

<!-- Other Confirmation Modals -->
<div class="modal fade" id="actionModal" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Konfirmasi Aksi</h5>
				<button type="button" class="btn-close ms-0" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<p id="actionModalMessage">Apakah Anda yakin ingin melakukan aksi ini?</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
				<button type="button" class="btn btn-danger" id="confirmAction">Ya, Lanjutkan</button>
			</div>
		</div>
	</div>
</div>

<!-- Webhook Information Modal -->
<div class="modal fade" id="webhookInfoModal" tabindex="-1" role="dialog" aria-labelledby="webhookInfoModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<div class="d-flex" style="margin: 0 auto;">
					<h5 class="modal-title" id="webhookInfoModalLabel">Informasi Webhook WhatsApp</h5>
				</div>
				<button type="button" class="btn-close ms-0" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="about-content">
					<p>Endpoint yang tersedia untuk Antrian Notifikasi:</p>

					<div class="endpoint mb-3 p-3 border rounded">
						<strong>Webhook (untuk cron):</strong><br>
						<code id="webhookEndpoint"><?php echo $_SERVER['HTTP_HOST'] . $scriptName; ?>?action=webhook&amp;limit=10</code>
						<div class="param mt-2">Proses hingga 'limit' pesan - dirancang untuk pekerjaan cron.</div>
					</div>

					<h5 class="mt-4">Cara Menggunakan</h5>
					<ul>
						<li>Atur pekerjaan cron untuk memanggil endpoint webhook setiap beberapa menit:
							<pre class="p-2 rounded">*/5 * * * * curl -s "<?php echo 'http://' . $_SERVER['HTTP_HOST'] . $scriptName; ?>?action=webhook&limit=10"</pre>
						</li>
					</ul>
				</div>
			</div>
			<div class="modal-footer">
			</div>
		</div>
	</div>
</div>

<script>
	// Helper function to escape HTML special characters
	function escapeHtml(text) {
		if (!text) return '';
		var map = {
			'&': '&amp;',
			'<': '&lt;',
			'>': '&gt;',
			'"': '&quot;',
			"'": '&#039;'
		};
		return text.toString().replace(/[&<>"']/g, function(m) {
			return map[m];
		});
	}

	$(document).ready(function() {
		var table = initDataTable("#waqueueTable", {
			title: "<?php echo $title ?>",
			ajax: {
				url: "<?php echo base_url('whatsapp/queue/get_list'); ?>",
				type: "POST"
			},
			layout: {
				topEnd: {
					buttons: [{
							extend: 'customButton',
							text: '<span class="fa fa-play" aria-hidden="true"></span> Kirim Antrian',
							url: '<?php echo base_url('whatsapp/queue/process'); ?>',
							className: 'btn btn-sm btn-outline-primary btn-progress btn-notification disabled',
							confirm: `Kirim antrian notifikasi WhatsApp?`,
							title: 'Sedang mengirimkan notifikasi',
							redirect: '<?php echo base_url('whatsapp/queue'); ?>',
						},
						// Info button is now in the header, so we'll remove this button from the layout
					]
				}
			},
			// dropdownButton: [{
			// 	column: 8,
			// 	label: 'Aksi',
			// 	options: [{
			// 			label: 'Jadikan Pending',
			// 			className: 'action-change-status',
			// 			url: '<php echo base_url('whatsapp/queue/change_status'); ?>',
			// 			data: {
			// 				id: '',
			// 				status: 'pending'
			// 			},
			// 			successMessage: 'Status berhasil diubah ke Pending'
			// 		},
			// 		{
			// 			label: 'Jadikan Completed',
			// 			className: 'action-change-status',
			// 			url: '<php echo base_url('whatsapp/queue/change_status'); ?>',
			// 			data: {
			// 				id: '',
			// 				status: 'completed'
			// 			},
			// 			successMessage: 'Status berhasil diubah ke Completed'
			// 		},
			// 		{
			// 			label: 'Reset Percobaan',
			// 			className: 'action-reset-attempts',
			// 			url: '<php echo base_url('whatsapp/queue/reset_attempts'); ?>',
			// 			data: {
			// 				id: ''
			// 			},
			// 			successMessage: 'Jumlah percobaan berhasil diatur ulang'
			// 		},
			// 		{
			// 			label: 'Hapus',
			// 			className: 'action-delete text-danger',
			// 			confirm: 'Apakah Anda yakin ingin menghapus item ini dari antrian?',
			// 			url: '<php echo base_url('whatsapp/queue/delete'); ?>',
			// 			data: {
			// 				id: ''
			// 			},
			// 			successMessage: 'Item berhasil dihapus dari antrian'
			// 		}
			// 	]
			// }],
			columns: [{
					data: null,
					title: 'No',
					render: function(data, type, row, meta) {
						return meta.row + meta.settings._iDisplayStart + 1;
					}
				},
				{
					data: 'target',
					title: 'Target'
				},
				{
					data: 'type_formatted',
					title: 'Tipe',
					className: 'dt-center',
				},
				{
					data: 'text_formatted',
					title: 'Teks',
					render: function(data, type, row) {
						if (type === 'display') {
							return '<span class="text-truncate d-inline-block" style="max-width: 300px;" title="' + escapeHtml(row.text) + '">' + escapeHtml(data) + '</span>';
						}
						return data;
					}
				},
				{
					data: 'status_formatted',
					title: 'Status',
					className: 'dt-center',
				},
				{
					data: 'priority_formatted',
					title: 'Prioritas',
					className: 'dt-center',
					visible: false
				},
				{
					data: 'attempts_formatted',
					title: 'Percobaan',
					className: 'dt-center',
				},
				{
					data: 'created_at',
					title: 'Dibuat',
					className: 'dt-center',
					render: function(data, type, row) {
						return formatDate(data, 'DD MMM YYYY HH:mm:ss');
					}
				},
				{
					data: 'processed_at',
					title: 'Proses',
					className: 'dt-center',
				},
				{
					data: 'sent_response',
					title: 'Respons Kirim',
					render: function(data, type, row) {
						if (!data) {
							return '<span class="text-muted">Belum diproses</span>';
						}
						if (type === 'display') {
							var displayText = data.length > 50 ? data.substring(0, 50) + '...' : data;
							return '<span class="text-truncate d-inline-block" style="max-width: 300px;" title="' + escapeHtml(data) + '">' + escapeHtml(displayText) + '</span>';
						}
						return data;
					}
				},
				{
					data: null,
					title: 'Aksi',
					orderable: false,
					className: 'dt-right hide-on-print text-nowrap',
					render: function(data, type, row) {
						return '<a class="btn btn-sm btn-outline-danger btn-confirm" href="<?php echo base_url('whatsapp/queue/delete/') ?>' + row.id + '" data-confirm-message="Apakah Anda yakin ingin menghapus item ini dari antrian?" title="Hapus"><i class="fa fa-trash"></i></a>';
					}
				}
			],
		});

		// Modal functionality
		document.addEventListener('click', function(e) {
			if (e.target.closest('#webhookInfoModalBtn')) {
				e.preventDefault();
				var modal = document.getElementById('webhookInfoModal');
				if (modal) {
					// Using vanilla JavaScript to trigger Bootstrap modal
					if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
						var modalInstance = new bootstrap.Modal(modal);
						modalInstance.show();
					} else {
						// Fallback if Bootstrap JS is not available
						modal.classList.add('show');
						modal.style.display = 'block';
						document.body.classList.add('modal-open');
					}
				}
			}
		});
	});
</script>