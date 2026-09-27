<?php $minut = $minut ?: $data; ?>
<div class="row">
	<div class="col-md-12">
			<div class="card leaves">
				<div class="card-header leaves">
					<h5>Detail Perkara</h5>
				</div>
				<div class="card-body">
				<div class="mb-3 text-end">
				</div>

					<table class="table table-sm table-borderless mb-0">
						<tr>
							<td>Nomor Perkara</td>
							<td><strong><?php echo $minut->nomor_perkara ?: '-' ?></strong></td>
						</tr>
						<tr>
							<td>Jenis Perkara</td>
							<td><?php echo $minut->jenis_perkara_nama ?: '-' ?></td>
						</tr>
						<tr>
							<td>Hakim</td>
							<td><?php echo $minut->hakim_nama ? ($minut->hakim_nama . '<br/>PP: ' . ($minut->panitera_nama ? str_replace('Panitera Pengganti:', '', $minut->panitera_nama) : '-')) : '-' ?></td>
						</tr>
						<tr>
							<td>Tanggal Putus</td>
							<td><?php echo $minut->tanggal_putusan ? format_date($minut->tanggal_putusan, 'd MMMM yyyy') : '-' ?></td>
						</tr>
						<tr>
							<td>Status Putusan</td>
							<td><?php echo $minut->status_putusan ?: '-' ?></td>
						</tr>
						<tr>
							<td>Verstek</td>
							<td><?php echo $minut->putusan_verstek == 'Y' ? 'Ya' : ($minut->putusan_verstek == 'T' ? 'Tidak' : '-') ?></td>
						</tr>
						<tr>
							<td>Tanggal BHT</td>
							<td><?php echo $minut->tanggal_bht ? format_date($minut->tanggal_bht, 'd MMMM yyyy') : '-' ?></td>
						</tr>
						<?php if (in_array($minut->jenis_perkara_id, [346, 347])): ?>
							<tr>
								<td>Tanggal Akta Cerai</td>
								<td><?php echo $minut->tgl_akta_cerai ? format_date($minut->tgl_akta_cerai, 'd MMMM yyyy') : '-' ?></td>
							</tr>
						<?php endif; ?>
						<tr>
							<td>e-Court</td>
							<td><?php echo $minut->efiling == 1 ? '<span class="badge badge-success">Ya</span>' : ($minut->efiling == 0 ? '<span class="badge badge-secondary">Tidak</span>' : '-') ?></td>
						</tr>
						<tr>
							<td>Ghaib</td>
							<td><?php echo $minut->ghaib == 1 ? '<span class="badge badge-warning">Ya</span>' : ($minut->ghaib == 0 ? '<span class="badge badge-secondary">Tidak</span>' : '-') ?></td>
						</tr>
					</table>
				</div>
			</div>
			</div>
		</div>