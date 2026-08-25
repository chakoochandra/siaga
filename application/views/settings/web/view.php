<div class="row">
	<div class="col-12">
		<div class="card">
			<div class="card-header">
				<h5 class="card-title">Detail Web: <?php echo $data->name ?></h5>
			</div>
			<div class="card-body">
				<div class="mb-3 text-end">
					<a href="<?php echo base_url("settings/web/save/{$data->id}") ?>" class="btn btn-outline-primary btn-modal" title="Edit Web">
						<i class="fas fa-pen"></i>
					</a>
					<a href="<?php echo base_url("settings/web/delete/{$data->id}") ?>" class="btn btn-outline-danger btn-confirm" data-confirm-message="Anda yakin akan menghapus web <?php echo $data->name ?>?" title="Hapus Web">
						<i class="fas fa-trash"></i>
					</a>
				</div>
				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label>Nama:</label>
							<p class="form-control-static"><?php echo $data->name ?></p>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label>URL:</label>
							<p class="form-control-static"><?php echo $data->url ?></p>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label>Kategori:</label>
							<p class="form-control-static"><?php echo $data->category ?></p>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label>Tag:</label>
							<p class="form-control-static"><?php echo $data->tag ?></p>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label>Status:</label>
							<p class="form-control-static"><?php echo $data->is_active == 1 ? 'Aktif' : 'Tidak Aktif' ?></p>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label>Tampilkan Online:</label>
							<p class="form-control-static"><?php echo $data->show_online == 1 ? 'Ya' : 'Tidak' ?></p>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label>Urutan:</label>
							<p class="form-control-static"><?php echo isset($data->order) ? $data->order : '-' ?></p>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label>Icon:</label>
							<?php if (isset($data->icon_url) && $data->icon_url): ?>
								<div>
									<img src="<?php echo $data->icon_url ?>" alt="Icon" style="max-height: 100px;">
									<p class="form-control-static mt-2"><?php echo $data->icon ?> (<?php echo format_filesize($data->icon_size) ?>)</p>
								</div>
							<?php else: ?>
								<p class="form-control-static"><span class="badge badge-secondary">Tidak ada icon</span></p>
							<?php endif; ?>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<div class="form-group">
							<label>Deskripsi:</label>
							<p class="form-control-static"><?php echo nl2br(htmlspecialchars($data->description)) ?></p>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<div class="form-group">
							<label>Dibuat Pada:</label>
							<p class="form-control-static"><?php echo $data->created_at ? format_date($data->created_at, 'd F Y H:i:s') : '-' ?></p>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<div class="form-group">
							<label>Diubah Pada:</label>
							<p class="form-control-static"><?php echo $data->updated_at && $data->updated_at !== '0000-00-00 00:00:00' ? format_date($data->updated_at, 'd F Y H:i:s') : '-' ?></p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
