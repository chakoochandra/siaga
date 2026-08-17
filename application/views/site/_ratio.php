<script src="<?php echo asset_url('assets/plugins/jquery-knob/jquery.knob.min.js') ?>"></script>

<?php if (true /*!is_development()*/): ?>

	<?php
	function createUrlRekapitulasi($year, $month, $data, $type, $ecourt = 0)
	{
		return $data > 0 ? "<a href='" . base_url("rekapitulasi/keadaanperkara/perkara_list/$type/$year/$month/$ecourt") . "' class='btn-modal'>{$data}</a>" : $data;
	}

	function createUrlBas($data, $type, $status)
	{
		return $data > 0 ? "<a href='" . base_url("kinerja/bas/bas_list/$type/0/{$status}") . "' class='btn-modal'>{$data}</a>" : $data;
	}

	function createUrlMinutasi($data, $type, $status)
	{
		return $data > 0 ? "<a href='" . base_url("kinerja/minutasi/putus_list/$type/0/{$status}") . "' class='btn-modal'>{$data}</a>" : $data;
	}

	$canSee = true;
	?>

	<div class="box_ratio card card-widget widget-user-2 my-3">
		<!-- Row 1: TUNGGAKAN, PENANGANAN PERKARA, E-COURT -->
		<div class="row mx-4 mt-3 justify-content-md-center">
			<div class="col d-flex flex-column">
				<h5 align="center" style="margin: 6px auto 0px;">
					<div class="knob-label fw-bold">
						<font class="card-title text-danger" size="3" face="Segoe UI">SISA/TUNGGAKAN</font>
					</div>
				</h5>

				<div class="mx-auto my-2">
					<h5 class="mb-0">
						<font size="10" face="Bernard MT Condensed" class="badge-number">
							<?php if (isset($ratio)): ?>
								<?php $denominatorTunggakan = $ratio->tunggakan_tahun_lalu + $ratio->masuk_tahun_ini; ?>
								<?php $persentaseTunggakan = $denominatorTunggakan > 0 ? ($ratio->tunggakan_total / $denominatorTunggakan * 100) : 0; ?>
								<?php $maxTunggakan = max($ratio->tunggakan_total, $ratio->tunggakan_tahun_lalu + $ratio->masuk_tahun_ini) ?>
								<input id="knob-tunggakan-perkara" type="text" class="knob" data-min="0" data-max="<?php echo $maxTunggakan ?>" value="<?php echo $ratio->tunggakan_total ?>" data-skin="tron" data-thickness="0.2" data-width="200" data-height="200" data-fgColor="<?php echo get_percentage_color($persentaseTunggakan) ?>" data-angleOffset="<?php echo 360 * (1 - $persentaseTunggakan / 100) ?>" data-angleArc="360">
							<?php else: ?>
								<i class="fa-solid fa-circle-notch fa-spin text-danger" aria-hidden="true"></i>
							<?php endif ?>
						</font>
					</h5>
				</div>
			</div>
			<div class="col d-flex flex-column">
				<h5 align="center" style="margin: 6px auto 0px;">
					<div class="knob-label fw-bold">
						<font class="card-title" size="3" face="Segoe UI">PENANGANAN PERKARA</font>
					</div>
				</h5>

				<div class="mx-auto my-2">
					<h5 class="mb-0">
						<font size="10" face="Bernard MT Condensed" class="badge-number">
							<?php if (isset($ratio)): ?>
								<input type="text" class="knob" data-min="0" data-max="100" value="<?php echo $ratio->persentase_perkara ?>" data-skin="tron" data-thickness="0.2" data-width="200" data-height="200" data-fgColor="<?php echo get_percentage_color($ratio->persentase_perkara) ?>" data-perkara-count="<?php echo htmlspecialchars(number_format_indo($ratio->minutasi_tahun_ini)) ?> / <?php echo htmlspecialchars(number_format_indo($ratio->masuk_tahun_ini + $ratio->tunggakan_tahun_lalu)) ?>">
							<?php else: ?>
								<i class="fa-solid fa-circle-notch fa-spin text-danger" aria-hidden="true"></i>
							<?php endif ?>
						</font>
					</h5>
				</div>
			</div>
			<div class="col d-flex flex-column">
				<h5 align="center" style="margin: 6px auto 0px;">
					<div class="knob-label fw-bold">
						<font class="card-title" size="3" face="Segoe UI">PERKARA E-COURT</font>
					</div>
				</h5>
				<div class="mx-auto my-2">
					<h5 class="mb-0">
						<font size="10" face="Bernard MT Condensed" class="badge-number">
							<?php if (isset($ratio)): ?>
								<input type="text" class="knob" data-min="0" data-max="100" value="<?php echo $ratio->persentase_ecourt ?>" data-skin="tron" data-thickness="0.2" data-width="200" data-height="200" data-fgColor="<?php echo get_percentage_color($ratio->persentase_ecourt) ?>" data-ecourt-count="<?php echo htmlspecialchars(number_format_indo($ratio->ecourt)) ?> / <?php echo htmlspecialchars(number_format_indo($ratio->masuk_tahun_ini)) ?>">
							<?php else: ?>
								<i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i>
							<?php endif ?>
						</font>
					</h5>
				</div>
			</div>
		</div>

		<!-- Row 2: MINUTASI, BAS, UPLOAD PUTUSAN -->
		<div class="row mx-4 mt-2 justify-content-md-center">
			<div class="col d-flex flex-column">
				<h5 align="center" style="margin: 6px auto 0px;">
					<div class="knob-label fw-bold">
						<font class="card-title" size="3" face="Segoe UI">PUTUS SETOR PANMUD</font>
					</div>
				</h5>
				<div class="mx-auto my-2">
					<h5 class="mb-0">
						<font size="10" face="Bernard MT Condensed" class="badge-number">
							<?php if (isset($kinerja_minutasi)): ?>
								<input type="text" class="knob" data-min="0" data-max="100" value="<?php echo $kinerja_minutasi->percentage_minutasi ?>" data-skin="tron" data-thickness="0.2" data-width="200" data-height="200" data-fgColor="<?php echo get_percentage_color($kinerja_minutasi->percentage_minutasi) ?>" data-minutasi-count="<?php echo htmlspecialchars(number_format_indo($kinerja_minutasi->setor_putus_tahun_ini)) ?> / <?php echo htmlspecialchars(number_format_indo($kinerja_minutasi->jumlah_putus_tahun_ini)) ?>">
							<?php else: ?>
								<i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i>
							<?php endif ?>
						</font>
					</h5>
				</div>
			</div>
			<div class="col d-flex flex-column">
				<h5 align="center" style="margin: 6px auto 0px;">
					<div class="knob-label fw-bold">
						<font class="card-title" size="3" face="Segoe UI">BAS SUDAH UNGGAH</font>
					</div>
				</h5>
				<div class="mx-auto my-2">
					<h5 class="mb-0">
						<font size="10" face="Bernard MT Condensed" class="badge-number">
							<?php if (isset($kinerja_bas)): ?>
								<input type="text" class="knob" data-min="0" data-max="100" value="<?php echo $kinerja_bas->percentage_bas ?>" data-skin="tron" data-thickness="0.2" data-width="200" data-height="200" data-fgColor="<?php echo get_percentage_color($kinerja_bas->percentage_bas) ?>" data-bas-count="<?php echo htmlspecialchars(number_format_indo($kinerja_bas->uploaded_bas)) ?> / <?php echo htmlspecialchars(number_format_indo($kinerja_bas->jumlah_sidang)) ?>">
							<?php else: ?>
								<i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i>
							<?php endif ?>
						</font>
					</h5>
				</div>
			</div>
			<div class="col d-flex flex-column">
				<h5 align="center" style="margin: 6px auto 0px;">
					<div class="knob-label fw-bold">
						<font class="card-title" size="3" face="Segoe UI">UPLOAD PUTUSAN</font>
					</div>
				</h5>
				<div class="mx-auto my-2">
					<h5 class="mb-0">
						<font size="10" face="Bernard MT Condensed" class="badge-number">
							<?php if (isset($ratio)): ?>
								<input type="text" class="knob" data-min="0" data-max="100" value="<?php echo $ratio->persentase_edoc ?>" data-skin="tron" data-thickness="0.2" data-width="200" data-height="200" data-fgColor="<?php echo get_percentage_color($ratio->persentase_edoc) ?>" data-edoc-count="<?php echo htmlspecialchars(number_format_indo($ratio->sudah_ada_edoc_tahun_ini)) ?> / <?php echo htmlspecialchars(number_format_indo($ratio->putus_tahun_ini)) ?>">
							<?php else: ?>
								<i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i>
							<?php endif ?>
						</font>
					</h5>
				</div>
			</div>
		</div>

		<!-- Row 1: Masuk, e-Court, Putus -->
		<div class="statistic-global row row-cols-1 row-cols-md-3 g-2 m-1 justify-content-center">
			<div class="statistic-global-item col">
				<div class="info-box d-flex align-items-stretch">
					<span class="info-box-icon me-1 text-bg-primary shadow-sm d-flex align-items-center justify-content-center" style="width: 15%; min-width: 50px;">
						<i class="fa-solid fa-right-to-door mx-2"></i>
					</span>
					<div class="info-box-content px-0 d-flex flex-column">
						<span class="fw-bold ms-2">Masuk</span>
						<div class="d-flex justify-content-center flex-grow-1 w-100 gap-2">
							<div class="d-flex flex-column justify-content-center flex-fill">
								<span class="text-nowrap">Tahun Ini</span>
								<span class="badge-number badge-number-medium badge badge-primary"><?= createUrlRekapitulasi(date('Y'), 0, isset($ratio) ? number_format_indo($ratio->masuk_tahun_ini) : '<i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i>', 'terima') ?></span>
							</div>
							<div class="d-flex flex-column justify-content-center flex-fill">
								<span class="text-nowrap">Bulan Ini</span>
								<span class="badge-number badge-number-medium badge badge-primary"><?= createUrlRekapitulasi(date('Y'), date('m'), isset($ratio) ? number_format_indo($ratio->masuk_bulan_ini) : '<i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i>', 'terima') ?></span>
							</div>
							<div class="d-flex flex-column justify-content-center flex-fill">
								<span class="text-nowrap">Hari Ini</span>
								<span class="badge-number badge-number-medium badge badge-primary"><?= createUrlRekapitulasi(date('Y'), 0, isset($ratio) ? number_format_indo($ratio->masuk_hari_ini) : '<i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i>', 'masuk_hari_ini') ?></span>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="statistic-global-item col">
				<div class="info-box d-flex align-items-stretch">
					<span class="info-box-icon me-1 text-bg-info shadow-sm d-flex align-items-center justify-content-center" style="width: 15%; min-width: 50px;">
						<i class="fa-regular fa-star mx-2"></i>
					</span>
					<div class="info-box-content px-0 d-flex flex-column">
						<span class="fw-bold ms-2">e-Court</span>
						<div class="d-flex justify-content-center flex-grow-1 w-100 gap-2">
							<div class="d-flex flex-column justify-content-center flex-fill">
								<span class="text-nowrap">Tahun Ini</span>
								<span class="badge-number badge-number-medium badge badge-info"><?= createUrlRekapitulasi(date('Y'), 0, isset($ratio) ? number_format_indo($ratio->ecourt) : '<i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i>', 'terima', 1) ?></span>
							</div>
							<div class="d-flex flex-column justify-content-center flex-fill">
								<span class="text-nowrap">Bulan Ini</span>
								<span class="badge-number badge-number-medium badge badge-info"><?= createUrlRekapitulasi(date('Y'), date('m'), isset($ratio) ? number_format_indo($ratio->ecourt_bulan_ini) : '<i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i>', 'terima', 1) ?></span>
							</div>
							<div class="d-flex flex-column justify-content-center flex-fill">
								<span class="text-nowrap">Hari Ini</span>
								<span class="badge-number badge-number-medium badge badge-info"><?= createUrlRekapitulasi(date('Y'), date('m'), isset($ratio) ? number_format_indo($ratio->ecourt_hari_ini) : '<i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i>', 'masuk_hari_ini', 1) ?></span>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="statistic-global-item col">
				<div class="info-box d-flex align-items-stretch">
					<span class="info-box-icon me-1 text-bg-success shadow-sm d-flex align-items-center justify-content-center" style="width: 15%; min-width: 50px;">
						<i class="fa-solid fa-gavel mx-2"></i>
					</span>
					<div class="info-box-content px-0 d-flex flex-column">
						<span class="fw-bold ms-2">Putus</span>
						<div class="d-flex justify-content-center flex-grow-1 w-100 gap-2">
							<div class="d-flex flex-column justify-content-center flex-fill">
								<span class="text-nowrap">Tahun Ini</span>
								<span class="badge-number badge-number-medium badge badge-success"><?= createUrlRekapitulasi(date('Y'), 0, isset($ratio) ? number_format_indo($ratio->putus_tahun_ini) : '<i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i>', 'jumlah_putus') ?></span>
							</div>
							<div class="d-flex flex-column justify-content-center flex-fill">
								<span class="text-nowrap">Bulan Ini</span>
								<span class="badge-number badge-number-medium badge badge-success"><?= createUrlRekapitulasi(date('Y'), date('m'), isset($ratio) ? number_format_indo($ratio->putus_bulan_ini) : '<i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i>', 'jumlah_putus') ?></span>
							</div>
							<div class="d-flex flex-column justify-content-center flex-fill">
								<span class="text-nowrap">Hari Ini</span>
								<span class="badge-number badge-number-medium badge badge-success"><?= createUrlRekapitulasi(date('Y'), date('m'), isset($ratio) ? number_format_indo($ratio->putus_hari_ini) : '<i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i>', 'putus_hari_ini') ?></span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Row 2: Minutasi, Setor Panmud, BAS -->
		<div class="statistic-global row row-cols-1 g-2 m-1 justify-content-center">
			<div class="statistic-global-item col col-md-4">
				<div class="info-box d-flex align-items-stretch">
					<span class="info-box-icon me-1 text-bg-warning shadow-sm d-flex align-items-center justify-content-center" style="width: 15%; min-width: 50px;">
						<i class="fa-solid fa-balance-scale mx-2"></i>
					</span>
					<div class="info-box-content px-0 d-flex flex-column">
						<span class="fw-bold ms-2">Minutasi</span>
						<div class="d-flex justify-content-center flex-grow-1 w-100 gap-2">
							<div class="d-flex flex-column justify-content-center flex-fill">
								<span class="text-nowrap">Tahun Ini</span>
								<span class="badge-number badge-number-medium badge badge-warning"><?php echo createUrlRekapitulasi(date('Y'), 0, isset($ratio) ? number_format_indo($ratio->minutasi_tahun_ini) : '<i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i>', 'minutasi') ?></span>
							</div>
							<div class="d-flex flex-column justify-content-center flex-fill">
								<span class="text-nowrap">Bulan Ini</span>
								<span class="badge-number badge-number-medium badge badge-warning"><?php echo createUrlRekapitulasi(date('Y'), date('m'), isset($ratio) ? number_format_indo($ratio->minutasi_bulan_ini) : '<i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i>', 'minutasi') ?></span>
							</div>
							<div class="d-flex flex-column justify-content-center flex-fill">
								<span class="text-nowrap">Hari Ini</span>
								<span class="badge-number badge-number-medium badge badge-warning"><?php echo createUrlRekapitulasi(date('Y'), date('m'), isset($ratio) ? number_format_indo($ratio->minutasi_hari_ini) : '<i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i>', 'minutasi_hari_ini') ?></span>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="statistic-global-item col col-md-4">
				<div class="info-box d-flex align-items-stretch">
					<span class="info-box-icon me-1 text-bg-danger shadow-sm d-flex align-items-center justify-content-center" style="width: 15%; min-width: 50px;">
						<i class="fa-solid fa-upload mx-2"></i>
					</span>
					<div class="info-box-content px-0 d-flex flex-column">
						<span class="fw-bold ms-2">Setor Panmud</span>
						<div class="d-flex justify-content-center flex-grow-1 w-100 gap-2">
							<div class="d-flex flex-column justify-content-center flex-fill">
								<span class="text-nowrap">Putus</span>
								<span class="badge-number badge-number-medium badge badge-danger w-100 d-flex justify-content-center"><?php echo createUrlMinutasi(isset($kinerja_minutasi) ? number_format_indo($kinerja_minutasi->jumlah_putus_tahun_ini) : '<i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i>', 'putus', 3) ?></span>
							</div>
							<div class="d-flex flex-column justify-content-center flex-fill">
								<span class="text-nowrap">Sudah Setor</span>
								<span class="badge-number badge-number-medium badge badge-danger w-100 d-flex justify-content-center"><?php echo createUrlMinutasi(isset($kinerja_minutasi) ? number_format_indo($kinerja_minutasi->setor_putus_tahun_ini) : '<i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i>', 'setor', 3) ?></span>
							</div>
							<div class="d-flex flex-column justify-content-center flex-fill">
								<span class="text-nowrap">Belum Setor</span>
								<span class="badge-number badge-number-medium badge badge-danger w-100 d-flex justify-content-center"><?php echo createUrlMinutasi(isset($kinerja_minutasi) ? number_format_indo($kinerja_minutasi->jumlah_putus_tahun_ini - $kinerja_minutasi->setor_putus_tahun_ini) : '<i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i>', 'pending', 3) ?></span>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="statistic-global-item col col-md-4">
				<div class="info-box d-flex align-items-stretch">
					<span class="info-box-icon me-1 text-bg-secondary shadow-sm d-flex align-items-center justify-content-center" style="width: 15%; min-width: 50px;">
						<i class="fa-solid fa-clipboard-list mx-2"></i>
					</span>
					<div class="info-box-content px-0 d-flex flex-column">
						<span class="fw-bold ms-2">Bas Triwulan <?php echo ceil(date('n') / 3) ?></span>
						<div class="d-flex justify-content-center flex-grow-1 w-100 gap-2">
							<div class="d-flex flex-column justify-content-center flex-fill">
								<span class="text-nowrap">Sidang</span>
								<span class="badge-number badge-number-medium badge badge-secondary"><?php echo createUrlBas(isset($kinerja_bas) ? number_format_indo($kinerja_bas->jumlah_sidang) : '<i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i>', 'sidang', 3) ?></span>
							</div>
							<div class="d-flex flex-column justify-content-center flex-fill">
								<span class="text-nowrap">Sudah Unggah</span>
								<span class="badge-number badge-number-medium badge badge-secondary"><?php echo createUrlBas(isset($kinerja_bas) ? number_format_indo($kinerja_bas->uploaded_bas) : '<i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i>', 'uploaded', 3) ?></span>
							</div>
							<div class="d-flex flex-column justify-content-center flex-fill">
								<span class="text-nowrap">Belum Unggah</span>
								<span class="badge-number badge-number-medium badge badge-secondary"><?php echo createUrlBas(isset($kinerja_bas) ? number_format_indo($kinerja_bas->jumlah_sidang - $kinerja_bas->uploaded_bas) : '<i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i>', 'pending', 3) ?></span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="card-footer">
			<div class="statistics-item d-flex px-0 overflow-auto flex-nowrap">
				<ul class="col nav flex-column m-2 flex-shrink-0">
					<li class="nav-item p-2 d-flex flex-nowrap align-items-center justify-content-between">
						<span class="text-truncate flex-grow-1" style="max-width: 85%;">Belum Unggah Gugatan</span>
						<span class="badge-number badge-number-medium ms-2 badge badge-warning flex-shrink-0"><?= createUrlRekapitulasi(date('Y'), 0, isset($ratio) ? number_format_indo($ratio->belum_ada_gugatan) : '<i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i>', 'belum_ada_gugatan') ?></span>
					</li>
					<li class="nav-item p-2 d-flex flex-nowrap align-items-center justify-content-between">
						<span class="text-truncate flex-grow-1" style="max-width: 85%;">Belum Input PMH</span>
						<span class="badge-number badge-number-medium ms-2 badge badge-warning flex-shrink-0"><?= createUrlRekapitulasi(date('Y'), 0, isset($ratio) ? number_format_indo($ratio->belum_ada_pmh) : '<i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i>', 'belum_ada_pmh') ?></span>
					</li>
					<li class="nav-item p-2 d-flex flex-nowrap align-items-center justify-content-between">
						<span class="text-truncate flex-grow-1" style="max-width: 85%;">Belum Input PHS</span>
						<span class="badge-number badge-number-medium ms-2 badge badge-warning flex-shrink-0"><?= createUrlRekapitulasi(date('Y'), 0, (isset($count_summary) && isset($count_summary['belum_ada_phs']['jumlah'])) ? number_format_indo($count_summary['belum_ada_phs']['jumlah']) : '<i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i>', 'belum_ada_phs') ?></span>
					</li>
					<li class="nav-item p-2 d-flex flex-nowrap align-items-center justify-content-between">
						<span class="text-truncate flex-grow-1" style="max-width: 85%;">Belum Input Relaas</span>
						<span class="badge-number badge-number-medium ms-2 badge badge-warning flex-shrink-0"><a href="<?php echo base_url('kinerja/relaas') ?>" target="_blank" class="btn-external mx-auto my-2"><?= (isset($count_summary) && isset($count_summary['relaas_belum_input']['jumlah'])) ? number_format_indo($count_summary['relaas_belum_input']['jumlah']) : '<i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i>' ?></a></span>
					</li>
				</ul>
				<ul class="col nav flex-column m-2 flex-shrink-0">
					<li class="nav-item p-2 d-flex flex-nowrap align-items-center justify-content-between">
						<span class="text-truncate flex-grow-1" style="max-width: 85%;">Redaksi Hari Ini</span>
						<span class="badge-number badge-number-medium ms-2 badge badge-primary flex-shrink-0"><?= createUrlRekapitulasi(date('Y'), 0, isset($count_redaksi) ? number_format_indo($count_redaksi->redaksi_hari_ini) : '<i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i>', 'redaksi_hari_ini') ?></span>
					</li>
					<li class="nav-item p-2 d-flex flex-nowrap align-items-center justify-content-between">
						<span class="text-truncate flex-grow-1" style="max-width: 85%;">Belum Input Redaksi</span>
						<span class="badge-number badge-number-medium ms-2 badge badge-warning flex-shrink-0"><?= createUrlRekapitulasi(date('Y'), 0, isset($count_redaksi) ? number_format_indo($count_redaksi->putus_belum_redaksi) : '<i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i>', 'putus_belum_redaksi') ?></span>
					</li>
					<li class="nav-item p-2 d-flex flex-nowrap align-items-center justify-content-between">
						<span class="text-truncate flex-grow-1" style="max-width: 85%;">Redaksi & Putus Beda</span>
						<span class="badge-number badge-number-medium ms-2 badge badge-danger flex-shrink-0"><?= createUrlRekapitulasi(date('Y'), 0, isset($count_summary) ? number_format_indo($count_summary['selisih_redaksi_putus']['jumlah']) : '<i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i>', 'selisih_redaksi_putus') ?></span>
					</li>
					<li class="nav-item p-2 d-flex flex-nowrap align-items-center justify-content-between">
						<span class="text-truncate flex-grow-1" style="max-width: 85%;">Belum Anonimisasi</span>
						<span class="badge-number badge-number-medium ms-2 badge badge-warning flex-shrink-0"><?= createUrlRekapitulasi(date('Y'), 0, isset($ratio) ? number_format_indo($ratio->belum_anonimasi_tahun_ini) : '<i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i>', 'belum_anonimasi') ?></span>
					</li>
				</ul>
				<ul class="col nav flex-column m-2 flex-shrink-0">
					<li class="nav-item p-2 d-flex flex-nowrap align-items-center justify-content-between">
						<span class="text-truncate flex-grow-1" style="max-width: 85%;">Belum Input Putus</span>
						<span class="badge-number badge-number-medium ms-2 badge badge-warning flex-shrink-0"><?= createUrlRekapitulasi(date('Y'), 0, isset($count_redaksi) ? number_format_indo($count_redaksi->belum_input_putus) : '<i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i>', 'belum_input_putus') ?></span>
					</li>
					<li class="nav-item p-2 d-flex flex-nowrap align-items-center justify-content-between">
						<span class="text-truncate flex-grow-1" style="max-width: 85%;">Belum Input Pertimbangan Hukum</span>
						<span class="badge-number badge-number-medium ms-2 badge badge-warning flex-shrink-0"><?= createUrlRekapitulasi(date('Y'), 0, isset($ratio) ? number_format_indo($ratio->belum_pertimbangan_hukum_tahun_ini) : '<i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i>', 'belum_pertimbangan_hukum') ?></span>
					</li>
					<li class="nav-item p-2 d-flex flex-nowrap align-items-center justify-content-between">
						<span class="text-truncate flex-grow-1" style="max-width: 85%;">Putus Belum BAS</span>
						<span class="badge-number badge-number-medium ms-2 badge badge-warning flex-shrink-0"><?= createUrlBas(isset($kinerja_bas) ? number_format_indo($kinerja_bas->pending_bas_putus) : '<i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i>', 'pending', 4) ?></span>
					</li>
					<li class="nav-item p-2 d-flex flex-nowrap align-items-center justify-content-between">
						<span class="text-truncate flex-grow-1" style="max-width: 85%;">Belum Minutasi</span>
						<span class="badge-number badge-number-medium ms-2 badge badge-warning flex-shrink-0"><?= createUrlRekapitulasi(0, 0, isset($ratio) ? number_format_indo($ratio->belum_minutasi) : '<i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i>', 'belum_minutasi') ?></span>
					</li>
				</ul>
				<ul class="col nav flex-column m-2 flex-shrink-0">
					<li class="nav-item p-2 d-flex flex-nowrap align-items-center justify-content-between">
						<span class="text-truncate flex-grow-1" style="max-width: 85%;">BHT Belum Arsip</span>
						<span class="badge-number badge-number-medium ms-2 badge badge-warning flex-shrink-0"><?= isset($ratio) ? number_format_indo($ratio->bht_belum_arsip_tahun_ini) : '<i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i>' ?></span>
					</li>
					<li class="nav-item p-2 d-flex flex-nowrap align-items-center justify-content-between">
						<span class="text-truncate flex-grow-1" style="max-width: 85%;">Belum Unggah Putusan</span>
						<span class="badge-number badge-number-medium ms-2 badge badge-warning flex-shrink-0"><?= createUrlRekapitulasi(date('Y'), 0, isset($ratio) ? number_format_indo($ratio->belum_ada_edoc_tahun_ini) : '<i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i>', 'belum_ada_edoc') ?></span>
					</li>
					<li class="nav-item p-2 d-flex flex-nowrap align-items-center justify-content-between">
						<span class="text-truncate flex-grow-1" style="max-width: 85%;">Antrian Dirput</span>
						<span class="badge-number badge-number-medium ms-2 badge badge-primary flex-shrink-0"><?= createUrlRekapitulasi(date('Y'), 0, isset($count_dirput_antrian) ? number_format_indo($count_dirput_antrian->dirput_antrian) : '<i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i>', 'dirput_antrian') ?></span>
					</li>
					<!-- <li class="nav-item p-2">
					<span class="float-start text-truncate" style="max-width: 80%;">Semua Perkara Putus</span> <span class="badge-number badge-number-medium ms-2 badge badge-info"><= createUrlRekapitulasi(0, 0, isset($ratio) ? number_format_indo($ratio->putus) : '<i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i>', 'jumlah_putus') ?></span>
				</li> -->
				</ul>
				<?php if ($canSee): ?>
					<ul class="col nav flex-column m-2 flex-shrink-0">
						<li class="nav-item p-2 d-flex flex-nowrap align-items-center justify-content-between">
							<span class="text-truncate flex-grow-1" style="max-width: 85%;">e-Doc Putusan</span>
							<span class="badge-number badge-number-medium ms-2 badge badge-info flex-shrink-0"><?= createUrlRekapitulasi(0, 0, isset($ratio) ? number_format_indo($ratio->sudah_ada_edoc_tahun_ini) : '<i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i>', 'sudah_ada_edoc') ?></span>
						</li>
						<li class="nav-item p-2 d-flex flex-nowrap align-items-center justify-content-between">
							<span class="text-truncate flex-grow-1" style="max-width: 85%;">Putusan Terpublish</span>
							<span class="badge-number badge-number-medium ms-2 badge badge-info flex-shrink-0"><?= createUrlRekapitulasi(0, 0, isset($count_dirput_perkara) ? number_format_indo($count_dirput_perkara->published_tahun_ini) : '<i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i>', 'published') ?></span>
						</li>
						<li class="nav-item p-2 d-flex flex-nowrap align-items-center justify-content-between">
							<span class="text-truncate flex-grow-1" style="max-width: 85%;">Tidak Terpublish</span>
							<span class="badge-number badge-number-medium ms-2 badge badge-danger flex-shrink-0"><?= createUrlRekapitulasi(date('Y'), 0, isset($count_dirput_perkara) ? number_format_indo($count_dirput_perkara->not_published_tahun_ini) : '<i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i>', 'not_published') ?></span>
						</li>
						<li class="nav-item p-2 d-flex flex-nowrap align-items-center justify-content-between">
							<span class="text-truncate flex-grow-1" style="max-width: 85%;">Antrian Dirput Error</span>
							<span class="badge-number badge-number-medium ms-2 badge badge-danger flex-shrink-0"><?= createUrlRekapitulasi(date('Y'), 0, isset($count_dirput_antrian) ? number_format_indo($count_dirput_antrian->dirput_error) : '<i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i>', 'dirput_error') ?></span>
						</li>
					</ul>
				<?php endif ?>
			</div>

			<?php if (isset($ratio)): ?>
				<div class="row m-1">
					<div class="col col-sm-6">
						<div id="chart-kinerja-perkara" style="height: 350px;"></div>
					</div>
					<div class="col col-sm-6">
						<div id="chart-kinerja-perkara-realtime" style="height: 350px;"></div>
					</div>
				</div>
				<div class="row m-1">
					<div class="col col-sm-6">
						<div id="chart-sisa-perkara" style="height: 350px;"></div>
					</div>
					<div class="col col-sm-6">
						<div id="chart-sisa-perkara-realtime" style="height: 350px;"></div>
					</div>
				</div>
			<?php endif ?>
		</div>
	</div>

<?php endif; ?>

<script src="<?php echo asset_url('assets/plugins/chart.js/Chart.min.js') ?>"></script>
<script src="<?php echo asset_url('assets/plugins/chart.js/chartjs-plugin-datalabels.js') ?>"></script>
<script src="<?php echo asset_url('assets/plugins/chart.js/init.js') ?>"></script>
<script src="<?php echo asset_url('assets/plugins/html2canvas/html2canvas.min.js') ?>"></script>

<script>
	$(function() {
		if (<?php echo isset($ratio) ? 'true' : 'false' ?> == true) {
			// Kinerja Penanganan Perkara Chart Realtime
			const sisaTahunLaluTotal = <?php echo isset($ratio) ? $ratio->tunggakan_tahun_lalu : 0 ?>;
			const masukTotal = <?php echo isset($ratio) ? $ratio->masuk_tahun_ini : 0 ?>;
			const minutasiTotal = <?php echo isset($ratio) ? $ratio->minutasi_tahun_ini : 0 ?>;
			const cabutTotal = <?php echo isset($ratio) ? $ratio->cabut_tahun_ini : 0 ?>;
			const sisaAkhirTotal = <?php echo isset($ratio) ? $ratio->tunggakan_total : 0 ?>;

			generatePenangananPerkaraChart('chart-kinerja-perkara-realtime', 'Kinerja Penanganan Perkara Sampai ' + moment().format('MMMM YYYY'), [
				[0, sisaTahunLaluTotal],
				[sisaTahunLaluTotal, sisaTahunLaluTotal + masukTotal],
				[sisaAkhirTotal, sisaAkhirTotal + minutasiTotal],
				[0, sisaAkhirTotal]
			]);

			// Kinerja Penanganan Perkara Chart Last Month
			const sisaTahunLalu = <?php echo isset($ratio) ? $ratio->tunggakan_tahun_lalu : 0 ?>;
			const masuk = <?php echo isset($ratio) ? $ratio->masuk : 0 ?>;
			const minutasi = <?php echo isset($ratio) ? $ratio->minutasi : 0 ?>;
			const cabut = <?php echo isset($ratio) ? $ratio->cabut : 0 ?>;
			const sisaAkhir = <?php echo isset($ratio) ? $ratio->tunggakan : 0 ?>;

			generatePenangananPerkaraChart('chart-kinerja-perkara', 'Kinerja Penanganan Perkara Sampai ' + (moment().startOf('month').subtract(1, 'months').format('MMMM YYYY')), [
				[0, sisaTahunLalu],
				[sisaTahunLalu, sisaTahunLalu + masuk],
				[sisaAkhir, sisaAkhir + minutasi],
				[0, sisaAkhir]
			]);

			// Sisa Perkara
			const perkaraLebih5Bulan = <?php echo isset($ratio) ? $ratio->perkara_lebih_5_bulan : 0 ?>;
			const perkaraGhaib = <?php echo isset($ratio) ? $ratio->perkara_ghaib : 0 ?>;
			const perkaraNonGhaib = <?php echo isset($ratio) ? $ratio->perkara_non_ghaib : 0 ?>;
			const perkaraLebih5BulanTotal = <?php echo isset($ratio) ? $ratio->perkara_lebih_5_bulan_total : 0 ?>;
			const perkaraGhaibTotal = <?php echo isset($ratio) ? $ratio->perkara_ghaib_total : 0 ?>;
			const perkaraNonGhaibTotal = <?php echo isset($ratio) ? $ratio->perkara_non_ghaib_total : 0 ?>;

			generateSisaPerkaraChart('chart-sisa-perkara', 'Sisa Perkara Sampai ' + (moment().startOf('month').subtract(1, 'months').format('MMMM YYYY')), [{
					label: 'Perkara ≥ 5 Bulan',
					value: perkaraLebih5Bulan
				},
				{
					label: 'Perkara Ghaib',
					value: perkaraGhaib
				},
				{
					label: 'Perkara Non Ghaib',
					value: perkaraNonGhaib
				}
			]);

			generateSisaPerkaraChart('chart-sisa-perkara-realtime', 'Sisa Perkara Sampai ' + moment().format('MMMM YYYY'), [{
					label: 'Perkara ≥ 5 Bulan',
					value: perkaraLebih5BulanTotal
				},
				{
					label: 'Perkara Ghaib',
					value: perkaraGhaibTotal
				},
				{
					label: 'Perkara Non Ghaib',
					value: perkaraNonGhaibTotal
				}
			]);
		}

		// Initialize all knobs with default settings (except tunggakan)
		$('.knob').not('#knob-tunggakan-perkara').knob({
			step: 0.01,
			readOnly: true,
			format: function(value) {
				return parseFloat(value).toFixed(2) + '%';
			},
			draw: function() {
				// Dynamically adjust font size
				let fontSize = Math.max(this.$.width() / 5, 24); // Calculate based on knob size
				this.i.css('font-size', fontSize + 'px'); // Apply font size to the inner text

				// "tron" case
				if (this.$.data('skin') == 'tron') {
					var a = this.angle(this.cv) // Angle
						,
						sa = this.startAngle // Previous start angle
						,
						sat = this.startAngle // Start angle
						,
						ea // Previous end angle
						,
						eat = sat + a // End angle
						,
						r = true

					this.g.lineWidth = this.lineWidth

					this.o.cursor &&
						(sat = eat - 0.3) &&
						(eat = eat + 0.3)

					if (this.o.displayPrevious) {
						ea = this.startAngle + this.angle(this.value)
						this.o.cursor &&
							(sa = ea - 0.3) &&
							(ea = ea + 0.3)
						this.g.beginPath()
						this.g.strokeStyle = this.previousColor
						this.g.arc(this.xy, this.xy, this.radius - this.lineWidth, sa, ea, false)
						this.g.stroke()
					}

					this.g.beginPath()
					this.g.strokeStyle = r ? this.o.fgColor : this.fgColor
					this.g.arc(this.xy, this.xy, this.radius - this.lineWidth, sat, eat, false)
					this.g.stroke()

					this.g.lineWidth = 2
					this.g.beginPath()
					this.g.strokeStyle = this.o.fgColor
					this.g.arc(this.xy, this.xy, this.radius - this.lineWidth + 1 + this.lineWidth * 2 / 3, 0, 2 * Math.PI, false)
					this.g.stroke()

					// Draw count below percentage for percentage knobs (ecourt, perkara, bas, edoc, minutasi)
					let countKeys = ['ecourt-count', 'perkara-count', 'bas-count', 'edoc-count', 'minutasi-count'];
					for (let i = 0; i < countKeys.length; i++) {
						let count = this.$.data(countKeys[i]);
						if (count !== undefined && count !== null && count !== '') {
							this.g.save();
							this.g.textAlign = 'center';
							this.g.textBaseline = 'middle';
							this.g.fillStyle = this.o.fgColor;
							let countFontSize = Math.max(this.$.width() / 6, 10);
							this.g.font = countFontSize + "px " + (this.o.fontFamily || "Arial");
							let yOffset = this.radius * 0.35;
							this.g.fillText(count, this.xy, this.xy + yOffset);
							this.g.restore();
							break; // Only draw one count per knob
						}
					}

					this.$.closest('.col-knob').slideDown();

					return false
				}
			},
			/*change : function (value) {
			 //console.log("change : " + value);
			 },
			 release : function (value) {
			 console.log("release : " + value);
			 },
			 cancel : function () {
			 console.log("cancel : " + this.value);
			 },*/
		})

		// Initialize tunggakan knob with special settings to show count instead of percentage
		$('#knob-tunggakan-perkara').knob({
			step: 1,
			readOnly: true,
			format: function(value) {
				// Display the raw count without percentage
				return Math.round(value);
			},
			draw: function() {
				// Dynamically adjust font size
				let fontSize = Math.max(this.$.width() / 3, 24); // Calculate based on knob size
				this.i.css('font-size', fontSize + 'px'); // Apply font size to the inner text

				// "tron" case
				if (this.$.data('skin') == 'tron') {
					var a = this.angle(this.cv) // Angle
						,
						sa = this.startAngle // Previous start angle
						,
						sat = this.startAngle // Start angle
						,
						ea // Previous end angle
						,
						eat = sat + a // End angle
						,
						r = true

					this.g.lineWidth = this.lineWidth

					this.o.cursor &&
						(sat = eat - 0.3) &&
						(eat = eat + 0.3)

					if (this.o.displayPrevious) {
						ea = this.startAngle + this.angle(this.value)
						this.o.cursor &&
							(sa = ea - 0.3) &&
							(ea = ea + 0.3)
						this.g.beginPath()
						this.g.strokeStyle = this.previousColor
						this.g.arc(this.xy, this.xy, this.radius - this.lineWidth, sa, ea, false)
						this.g.stroke()
					}

					this.g.beginPath()
					this.g.strokeStyle = r ? this.o.fgColor : this.fgColor
					this.g.arc(this.xy, this.xy, this.radius - this.lineWidth, sat, eat, false)
					this.g.stroke()

					this.g.lineWidth = 2
					this.g.beginPath()
					this.g.strokeStyle = this.o.fgColor
					this.g.arc(this.xy, this.xy, this.radius - this.lineWidth + 1 + this.lineWidth * 2 / 3, 0, 2 * Math.PI, false)
					this.g.stroke()

					this.$.closest('.col-knob').slideDown();

					return false
				}
			},
		})

		function generateSisaPerkaraChart(id, title, data) {
			initChart(id, {
				type: 'bar',
				title: title,
				customChartTypes: ['bar', 'pie', 'doughnut'],
				data: {
					labels: data.map(d => d.label),
					datasets: [{
						data: data.map(d => d.value),
					}]
				},
				options: {
					axisTitles: {
						x: 'Kategori',
						y: 'Jumlah Sisa Perkara'
					},
					// plugins: {
					// 	datalabels: datalabelsConfig
					// }
				}
			});
		}

		function generatePenangananPerkaraChart(id, title, data) {
			initChart(id, {
				title,
				type: 'bar',
				showToggleAxis: false,
				data: {
					labels: ['Sisa Tahun Lalu', 'Perkara Masuk', 'Perkara Putus', 'Tunggakan (Sisa Perkara)'],
					// labels: ['Sisa Tahun Lalu', 'Perkara Masuk', 'Perkara Putus', 'Perkara Cabut', 'Tunggakan (Sisa Perkara)'],
					datasets: [{
						label: 'Jumlah Perkara',
						data,
						backgroundColor: [
							'rgba(255, 99, 132, 0.7)', // Merah - Sisa Tahun Lalu
							'rgba(54, 162, 235, 0.7)', // Biru - Perkara Masuk
							'rgba(75, 192, 192, 0.7)', // Hijau - Perkara Putus
							// 'rgba(191, 107, 212, 0.7)', // Ungu - Perkara Cabut
							'rgba(255, 206, 86, 0.7)' // Kuning - Tunggakan (Sisa Perkara)
						],
						borderColor: [
							'rgba(255, 99, 132, 1)',
							'rgba(54, 162, 235, 1)',
							'rgba(75, 192, 192, 1)',
							// 'rgba(191, 107, 212, 1)',
							'rgba(255, 206, 86, 1)'
						],
						borderWidth: 2
					}]
				},
				options: {
					indexAxis: 'y',
					axisTitles: {
						x: 'Kategori',
						y: 'Jumlah Perkara',
					},
					plugins: {
						legend: {
							display: false
						},
						tooltip: {
							callbacks: {
								label: function(context) {
									const value = context.raw;
									const range = value[1] - value[0];
									// Show percentage for "Perkara Putus" only (index 2)
									if (context.dataIndex === 2) {
										const dataArr = context.dataset.data || [];
										const getRangeAt = (i) => {
											const v = dataArr[i];
											return Array.isArray(v) && v.length >= 2 ? (v[1] - v[0]) : 0;
										};
										const sisaTahunLalu = getRangeAt(0);
										const perkaraMasuk = getRangeAt(1);
										const numerator = range; // Perkara Putus
										const denominator = sisaTahunLalu + perkaraMasuk;
										const percent = denominator > 0 ? (numerator / denominator * 100) : 0;
										return `Jumlah: ${range.toLocaleString('id-ID')} perkara (${percent.toFixed(2)}%)`;
									}
									return `Jumlah: ${range.toLocaleString('id-ID')} perkara`;
								}
							}
						},
						datalabels: {
							formatter: function(value, context) {
								const range = value[1] - value[0];
								// Show percentage for "Perkara Putus" only (index 2)
								if (context.dataIndex === 2) {
									const dataArr = context.dataset.data || [];
									const getRangeAt = (i) => {
										const v = dataArr[i];
										return Array.isArray(v) && v.length >= 2 ? (v[1] - v[0]) : 0;
									};
									const sisaTahunLalu = getRangeAt(0);
									const perkaraMasuk = getRangeAt(1);
									const numerator = range; // Perkara Putus
									const denominator = sisaTahunLalu + perkaraMasuk;
									const percent = denominator > 0 ? (numerator / denominator * 100) : 0;
									return `${range.toLocaleString('id-ID')} (${percent.toFixed(2)}%)`;
								}
								return range.toLocaleString('id-ID');
							}
						}
					},
				}
			})
		}
	})
</script>