<div class="row">
	<div class="col-md-6 col-12">
		<div class="card leaves">
			<div class="card-header leaves">
				<h5 class="m-0">Detail Log Notifikasi</h5>
			</div>
			<div class="card-body">
				<table class="table table-borderless">
					<tr>
						<td><strong>Waktu Kirim</strong></td>
						<td><?php echo format_date($data->sent_time, 'DD MMM YYYY HH:mm:ss') ?></td>
					</tr>
					<tr>
						<td><strong>Nomor Tujuan</strong></td>
						<td><?php echo $data->phone_number ?></td>
					</tr>
					<tr>
						<td><strong>Jenis Pesan</strong></td>
						<td><?php echo $data->type ?></td>
					</tr>
					<tr>
						<td><strong>Referensi</strong></td>
						<td><?php echo $data->reference ?></td>
					</tr>
					<tr>
						<td><strong>Status</strong></td>
						<td>
							<?php if ($data->success == 1): ?>
								<span class="badge bg-success">Berhasil</span>
							<?php else: ?>
								<span class="badge bg-danger">Gagal</span>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<td><strong>Respon</strong></td>
						<td><?php echo $data->note ?></td>
					</tr>
				</table>
			</div>
		</div>
	</div>
	<div class="col-md-6 col-12">
		<div class="card leaves">
			<div class="card-header">
				<h5 class="m-0">Isi Pesan</h5>
			</div>
			<div class="card-body">
				<pre style="white-space: pre-wrap;"><?php echo htmlspecialchars($data->text) ?></pre>
			</div>
		</div>
	</div>
</div>
