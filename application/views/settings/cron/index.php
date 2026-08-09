<div class="row">
	<div class="col-12">
		<div class="card leaves">
			<div class="card-header leaves align-items-center">
				<h5 class="m-0"><?php echo $title ?></h5>
			</div>
			<div class="card-body">
				<div class="callout callout-info m-2 mt-0">
					<ul class="mb-0 ps-3">
						<!-- <li><i class="fa-solid fa-plus"></i> <strong>Tambah Cron Job</strong>, membuat job baru.</li> -->
						<li><i class="fa-solid fa-pen text-primary"></i> <strong>Ubah</strong> dan <i
								class="fa-solid fa-trash text-danger"></i> <strong>Hapus</strong>, hanya tersedia untuk
							job yang berlabel <span class="badge bg-primary">Dikelola UI</span> atau <span
								class="badge bg-success">Dari Config</span>. Job dengan badge <span
								class="badge bg-warning text-dark">Config (Belum Disinkron)</span> perlu di-Sync dulu;
							badge <span class="badge bg-danger">Akan Dihapus (Sync)</span> berarti job tersebut sudah
							dihapus dari config dan akan hilang otomatis dari crontab pada Sync berikutnya; badge <span
								class="badge bg-secondary">Sistem</span> adalah baris crontab lain yang tidak disentuh
							modul ini.</li>
						<li><i class="fa-solid fa-toggle-on text-secondary"></i> <strong>Toggle</strong>, menonaktifkan
							job sementara tanpa menghapusnya (job dinonaktifkan tidak akan berjalan, tapi tetap
							tersimpan dan bisa diaktifkan lagi kapan saja).</li>
						<li><strong>Diagnostik</strong>, jika daftar gagal termuat atau perubahan tidak tersimpan,
							gunakan tombol ini untuk memeriksa apakah <code>exec()</code> diblokir server, binary
							<code>crontab</code> tersedia, dan izin direktori temp sudah benar.
						</li>
					</ul>
				</div>

				<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
					<!-- <div>
						<button type="button" class="btn btn-primary" id="btn-add-cron">
							<i class="fa-solid fa-plus"></i> Tambah Cron Job
						</button>
					</div> -->
					<div>
						<button type="button" class="btn btn-light" id="btn-diagnose-cron"
							title="Cek exec(), binary crontab, izin, dll.">
							<i class="fa-solid fa-stethoscope"></i> Diagnostik
						</button>
						<button type="button" class="btn btn-light" id="btn-refresh-cron" title="Muat ulang daftar">
							<i class="fa-solid fa-arrows-rotate"></i>
						</button>
					</div>
				</div>

				<div id="cron-diagnose-result" class="alert alert-secondary d-none small font-monospace"
					style="white-space: pre-wrap;" role="alert"></div>
				<div id="cron-sync-result" class="alert alert-info d-none" role="alert"></div>

				<div id="cron-failed-list" class="d-none mb-3">
					<div class="d-flex justify-content-between align-items-center mb-2">
						<strong class="text-danger">
							<i class="fa-solid fa-triangle-exclamation"></i>
							Job gagal disinkronkan (<span id="cron-failed-count">0</span>)
						</strong>
					</div>
					<ul class="list-group" id="cron-failed-items"></ul>
				</div>

				<div class="table-responsive">
					<table class="table table-hover align-middle" id="table-cronjob">
						<thead>
							<tr>
								<th style="width: 18%;">Label</th>
								<th style="width: 14%;">Jadwal</th>
								<th>Command</th>
								<th style="width: 12%;">Sumber</th>
								<th style="width: 12%;" class="text-center">Aksi</th>
							</tr>
						</thead>
						<tbody id="cronjob-tbody">
							<tr>
								<td colspan="5" class="text-center text-muted py-4">Memuat data...</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="cronjobModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<form id="cronjob-form">
				<div class="modal-header">
					<h5 class="modal-title" id="cronjobModalTitle">Tambah Cron Job</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
				</div>
				<div class="modal-body">
					<input type="hidden" id="cronjob-id" value="">

					<div class="mb-3">
						<label class="form-label">Label</label>
						<input type="text" class="form-control" id="cronjob-label"
							placeholder="Contoh: Backup database harian" required>
					</div>

					<div class="mb-3">
						<label class="form-label">Preset Jadwal</label>
						<select class="form-select" id="cronjob-preset">
							<option value="">-- Pilih preset (opsional) --</option>
							<option value="* * * * *">Setiap menit</option>
							<option value="*/5 * * * *">Setiap 5 menit</option>
							<option value="*/15 * * * *">Setiap 15 menit</option>
							<option value="0 * * * *">Setiap jam (menit ke-0)</option>
							<option value="0 0 * * *">Setiap hari (00:00)</option>
							<option value="0 6 * * *">Setiap hari (06:00)</option>
							<option value="0 0 * * 0">Setiap minggu (Minggu, 00:00)</option>
							<option value="0 0 1 * *">Setiap bulan (tanggal 1, 00:00)</option>
						</select>
					</div>

					<div class="mb-3">
						<label class="form-label">Ekspresi Cron</label>
						<input type="text" class="form-control font-monospace" id="cronjob-expression"
							placeholder="* * * * *" required>
						<div class="form-text">Format: menit jam tanggal bulan hari-minggu (contoh:
							<code>0 1 * * *</code> = setiap jam 01:00).
						</div>
					</div>

					<div class="mb-1">
						<label class="form-label">Command</label>
						<textarea class="form-control font-monospace" id="cronjob-command" rows="2"
							placeholder="/usr/bin/php /var/www/html/index.php cli nama_task" required></textarea>
					</div>
				</div>
				<div class="modal-footer">
					<div class="text-danger small me-auto d-none" id="cronjob-form-error"></div>
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
					<button type="submit" class="btn btn-primary" id="cronjob-form-submit">Simpan</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- Delete Confirm Modal -->
<div class="modal fade" id="cronjobDeleteModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Hapus Cron Job</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
			</div>
			<div class="modal-body">
				Yakin ingin menghapus cron job <strong id="cronjob-delete-label"></strong>? Tindakan ini akan langsung
				menghapusnya dari crontab server.
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
				<button type="button" class="btn btn-danger" id="cronjob-delete-confirm">Hapus</button>
			</div>
		</div>
	</div>
</div>

<!-- Generic Run Confirm Modal (replaces window.confirm, which gets clipped in some WebViews) -->
<div class="modal fade" id="cronjobRunConfirmModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Konfirmasi</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
			</div>
			<div class="modal-body" id="cronjob-run-confirm-message"></div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
				<button type="button" class="btn btn-success" id="cronjob-run-confirm-ok">Jalankan</button>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function () {
		const urlList = "<?php echo base_url('settings/cron/get_list') ?>";
		const urlStore = "<?php echo base_url('settings/cron/store') ?>";
		const urlUpdate = "<?php echo base_url('settings/cron/update') ?>";
		const urlDestroy = "<?php echo base_url('settings/cron/destroy') ?>";
		const urlToggle = "<?php echo base_url('settings/cron/toggle') ?>";
		const urlSync = "<?php echo base_url('settings/cron/sync') ?>";
		const urlDiagnose = "<?php echo base_url('settings/cron/diagnose') ?>";

		const cronjobModal = new bootstrap.Modal(document.getElementById('cronjobModal'));
		const cronjobDeleteModal = new bootstrap.Modal(document.getElementById('cronjobDeleteModal'));
		const cronjobRunConfirmModal = new bootstrap.Modal(document.getElementById('cronjobRunConfirmModal'));
		let deleteTargetId = null;
		let runConfirmCallback = null;

		function csrfPayload(extra) {
			const payload = Object.assign({}, extra);
			payload[localStorage.getItem('csrfName')] = localStorage.getItem('csrfToken');
			return payload;
		}

		function escapeHtml(str) {
			return $('<div>').text(str == null ? '' : str).html();
		}

		// escapeHtml() alone is NOT safe inside a "..."-delimited HTML
		// attribute — jQuery's text()/html() round-trip escapes &, <, >
		// but leaves literal " and ' untouched, since those are only
		// special in attribute-value context, not as text content. Any
		// dynamic value placed inside data-x="${...}" needs this instead,
		// or an embedded quote silently truncates the attribute (that's
		// what was cutting off the manual-run confirm message).
		function escapeAttr(str) {
			return String(str == null ? '' : str)
				.replace(/&/g, '&amp;')
				.replace(/"/g, '&quot;')
				.replace(/'/g, '&#39;')
				.replace(/</g, '&lt;')
				.replace(/>/g, '&gt;');
		}

		function sourceBadge(source) {
			if (source === 'managed') {
				return '<span class="badge bg-primary">Dikelola UI</span>';
			}
			if (source === 'config') {
				return '<span class="badge bg-success">Dari Config</span>';
			}
			if (source === 'config_pending') {
				return '<span class="badge bg-warning text-dark">Config (Belum Disinkron)</span>';
			}
			if (source === 'config_removed_pending') {
				return '<span class="badge bg-danger">Akan Dihapus (Sync)</span>';
			}
			return '<span class="badge bg-secondary">Sistem</span>';
		}

		function sortJobs(jobs) {
			return jobs
				.map(function (job, index) {
					return {
						job: job,
						index: index
					};
				})
				.sort(function (a, b) {
					const aRank = a.job.source === 'unmanaged' ? 2 : (a.job.enabled ? 0 : 1);
					const bRank = b.job.source === 'unmanaged' ? 2 : (b.job.enabled ? 0 : 1);
					return aRank - bRank || a.index - b.index;
				})
				.map(function (entry) {
					return entry.job;
				});
		}

		<?php
		// Build the manual-action lookup from cronjobs.php's config instead
		// of hand-duplicating each job's url/confirm/title here — config
		// now supplies only the url (manual_action), everything else is
		// derived: icon/label are constant (all WhatsApp sends), and the
		// button's tooltip/progress-title and confirm message both reuse
		// the job's own human-readable 'title' rather than needing a
		// second bespoke string per job.
		$this->config->load('cronjobs');
		$cronConfigJobs = $this->config->item('cronjobs') ?: [];
		$manualActionMap = [];
		foreach ($cronConfigJobs as $cronJob) {
			if (!empty($cronJob['manual_action']) && isset($cronJob['label'])) {
				$jobTitle = !empty($cronJob['title']) ? $cronJob['title'] : $cronJob['label'];

				$manualActionMap[$cronJob['label']] = [
					'icon' => 'fa-brands fa-whatsapp',
					'label' => 'Kirim',
					'url' => $cronJob['manual_action'],
					'title' => $jobTitle,
					'confirm' => "Anda yakin akan menjalankan '" . $jobTitle . "' secara manual?",
				];
			}
		}
		?>
		const manualActionMap = <?php echo json_encode($manualActionMap, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;

		function renderRows(jobs) {
			const tbody = $('#cronjob-tbody');
			tbody.empty();

			jobs = sortJobs(jobs);

			if (!jobs.length) {
				tbody.append('<tr><td colspan="5" class="text-center text-muted py-4">Belum ada cron job.</td></tr>');
				return;
			}

			jobs.forEach(function (job) {
				let actions;

				if (job.editable) {
					const toggleIcon = job.enabled ? 'fa-toggle-on' : 'fa-toggle-off';
					const toggleTitle = job.enabled ? 'Nonaktifkan' : 'Aktifkan';

					const manualAction = manualActionMap[job.label];
					actions = manualAction ? `
						<button type="button" class="btn btn-sm btn-outline-success btn-run-cron" title="${manualAction.label}" data-url="${manualAction.url}" data-title="${manualAction.title}" data-confirm="${manualAction.confirm}">
							<i class="${manualAction.icon}"></i>
						</button>` : '';

					actions += `
						<button type="button" class="btn btn-sm btn-outline-secondary btn-toggle-cron" title="${toggleTitle}">
							<i class="fa-solid ${toggleIcon}"></i>
						</button>
						<button type="button" class="btn btn-sm btn-outline-primary btn-edit-cron" title="Ubah">
							<i class="fa-solid fa-pen"></i>
						</button>
						<button type="button" class="btn btn-sm btn-outline-danger btn-delete-cron" title="Hapus">
							<i class="fa-solid fa-trash"></i>
						</button>`;
				} else {
					actions = `<span class="text-muted small" title="Baris sistem, tidak dikelola di sini">
						<i class="fa-solid fa-lock"></i>
					</span>`;
				}

				const rowClass = job.enabled ? '' : 'table-secondary text-muted';
				const statusBadge = job.editable && !job.enabled ?
					' <span class="badge bg-secondary">Nonaktif</span>' : '';

				const displayName = job.title || job.label;

				const row = $(`
					<tr class="${rowClass}" data-id="${job.id == null ? '' : job.id}" data-label="${escapeHtml(job.label)}" data-expression="${escapeHtml(job.expression)}" data-command="${escapeHtml(job.command)}">
						<td>${escapeHtml(displayName)}${statusBadge}${job.title ? `<br><span class="text-muted small">${escapeHtml(job.label)}</span>` : ''}</td>
						<td><code>${escapeHtml(job.expression)}</code></td>
						<td class="text-break"><code>${escapeHtml(job.command)}</code></td>
						<td>${sourceBadge(job.source)}</td>
						<td class="text-center text-nowrap">${actions}</td>
					</tr>
				`);

				tbody.append(row);
			});
		}

		function showListError(message, xhr) {
			console.error('[cronjob] gagal memuat daftar:', message, xhr);

			let detail = message;

			if (xhr && xhr.responseJSON) {
				if (xhr.responseJSON.message) {
					detail = xhr.responseJSON.message;
				}
				if (xhr.responseJSON.debug) {
					detail += ',  ' + JSON.stringify(xhr.responseJSON.debug);
					console.error('[cronjob] debug detail:', xhr.responseJSON.debug);
				}
			} else if (xhr && xhr.responseText) {
				console.error('[cronjob] raw response:', xhr.responseText);
				detail += ' (status ' + xhr.status + ', lihat console untuk isi respons mentah)';
			}

			$('#cronjob-tbody').html(
				'<tr><td colspan="5" class="text-center text-danger py-4">' + escapeHtml(detail) + '</td></tr>'
			);
		}

		function loadList() {
			$('#cronjob-tbody').html('<tr><td colspan="5" class="text-center text-muted py-4">Memuat data...</td></tr>');

			$.getJSON(urlList)
				.done(function (res) {
					if (!res || res.success === false) {
						showListError((res && res.message) || 'Gagal memuat daftar cron job.', {
							responseJSON: res
						});
						return;
					}
					renderRows(res.data || []);
				})
				.fail(function (xhr) {
					showListError('Gagal memuat daftar cron job (HTTP ' + xhr.status + ').', xhr);
				});
		}

		function resetForm() {
			$('#cronjob-id').val('');
			$('#cronjob-label').val('');
			$('#cronjob-preset').val('');
			$('#cronjob-expression').val('');
			$('#cronjob-command').val('');
			$('#cronjob-form-error').addClass('d-none').text('');
		}

		$('#cronjob-preset').on('change', function () {
			const val = $(this).val();
			if (val) {
				$('#cronjob-expression').val(val);
			}
		});

		$('#btn-add-cron').on('click', function () {
			resetForm();
			$('#cronjobModalTitle').text('Tambah Cron Job');
			cronjobModal.show();
		});

		$('#btn-refresh-cron').on('click', loadList);

		function runDiagnose() {
			const btn = $('#btn-diagnose-cron, #btn-diagnose-from-failed');
			const box = $('#cron-diagnose-result');
			btn.prop('disabled', true);
			box.removeClass('d-none alert-danger alert-secondary').addClass('alert-secondary').text('Menjalankan diagnostik...').get(0).scrollIntoView({
				behavior: 'smooth',
				block: 'center'
			});

			$.getJSON(urlDiagnose)
				.done(function (res) {
					if (!res || res.success === false) {
						console.error('[cronjob] diagnostik gagal:', res);
						box.removeClass('alert-secondary').addClass('alert-danger').text((res && res.message) || 'Diagnostik gagal.');
						return;
					}

					const d = res.data;
					const lines = [
						'PHP version        : ' + d.php_version,
						'exec() tersedia     : ' + (d.exec_available ? 'YA' : 'TIDAK (diblokir disable_functions di php.ini)'),
						'Temp dir            : ' + d.tmp_dir,
						'Temp dir writable   : ' + (d.tmp_writable ? 'YA' : 'TIDAK'),
						'whoami              : ' + (d.whoami ?? '-'),
						'Binary crontab      : ' + (d.crontab_binary ?? '-'),
						'crontab -l berhasil : ' + (d.crontab_readable ? 'YA' : 'TIDAK'),
						'Output crontab -l   :',
						d.crontab_raw_output || '(kosong)'
					];

					box.removeClass('alert-danger').addClass('alert-secondary').text(lines.join('\n'));
				})
				.fail(function (xhr) {
					console.error('[cronjob] diagnostik gagal (request):', xhr.status, xhr.responseText);
					box.removeClass('alert-secondary').addClass('alert-danger').text('Diagnostik gagal (HTTP ' + xhr.status + '). Lihat console untuk detail.');
				})
				.always(function () {
					btn.prop('disabled', false);
				});
		}

		$('#btn-diagnose-cron, #btn-diagnose-from-failed').on('click', runDiagnose);

		$(document).on('click', '.btn-toggle-cron', function () {
			const row = $(this).closest('tr');
			const id = row.data('id');
			const btn = $(this);

			btn.prop('disabled', true);

			$.post(urlToggle + '/' + id, csrfPayload({
				id: id
			}), function (res) {
				if (res.success) {
					loadList();
				} else {
					console.error('[cronjob] gagal mengubah status:', res);
					alert(res.message || 'Gagal mengubah status cron job.');
					btn.prop('disabled', false);
				}
			}, 'json').fail(function (xhr) {
				console.error('[cronjob] gagal mengubah status (request):', xhr.status, xhr.responseText);
				alert('Gagal mengubah status cron job (HTTP ' + xhr.status + '). Lihat console untuk detail.');
				btn.prop('disabled', false);
			});
		});

		$(document).on('click', '.btn-edit-cron', function () {
			const row = $(this).closest('tr');
			resetForm();
			$('#cronjob-id').val(row.data('id'));
			$('#cronjob-label').val(row.data('label'));
			$('#cronjob-expression').val(row.data('expression'));
			$('#cronjob-command').val(row.data('command'));
			$('#cronjobModalTitle').text('Ubah Cron Job');
			cronjobModal.show();
		});

		$(document).on('click', '.btn-delete-cron', function () {
			const row = $(this).closest('tr');
			deleteTargetId = row.data('id');
			$('#cronjob-delete-label').text(row.data('label'));
			cronjobDeleteModal.show();
		});

		$(document).on('click', '.btn-run-cron', function () {
			const btn = $(this);
			const url = btn.data('url');
			const title = btn.data('title');
			const confirm = btn.data('confirm');

			const doRun = function () {
				btn.prop('disabled', true);
				startProgress(btn[0], url, title, '', '');
			};

			if (!confirm) {
				doRun();
				return;
			}

			$('#cronjob-run-confirm-message').text(confirm);
			runConfirmCallback = doRun;
			cronjobRunConfirmModal.show();
		});

		$('#cronjob-run-confirm-ok').on('click', function () {
			cronjobRunConfirmModal.hide();

			if (runConfirmCallback) {
				runConfirmCallback();
				runConfirmCallback = null;
			}
		});

		$('#cronjob-delete-confirm').on('click', function () {
			if (!deleteTargetId) {
				return;
			}

			$(this).prop('disabled', true);

			$.post(urlDestroy + '/' + deleteTargetId, csrfPayload({
				id: deleteTargetId
			}), function (res) {
				cronjobDeleteModal.hide();
				if (res.success) {
					loadList();
				} else {
					console.error('[cronjob] gagal menghapus:', res);
					alert(res.message || 'Gagal menghapus cron job.');
				}
			}, 'json').fail(function (xhr) {
				console.error('[cronjob] gagal menghapus (request):', xhr.status, xhr.responseText);
				alert('Gagal menghapus cron job (HTTP ' + xhr.status + '). Lihat console untuk detail.');
			}).always(function () {
				$('#cronjob-delete-confirm').prop('disabled', false);
			});
		});

		$('#cronjob-form').on('submit', function (e) {
			e.preventDefault();

			const id = $('#cronjob-id').val();
			const label = $('#cronjob-label').val().trim();
			const expression = $('#cronjob-expression').val().trim();
			const command = $('#cronjob-command').val().trim();

			const errorBox = $('#cronjob-form-error');
			errorBox.addClass('d-none').text('');

			if (!label || !expression || !command) {
				errorBox.removeClass('d-none').text('Semua field wajib diisi.');
				return;
			}

			const payload = csrfPayload({
				label: label,
				expression: expression,
				command: command
			});

			const submitBtn = $('#cronjob-form-submit');
			submitBtn.prop('disabled', true);

			const request = id ?
				$.post(urlUpdate + '/' + id, Object.assign({}, payload, {
					id: id
				}), null, 'json') :
				$.post(urlStore, payload, null, 'json');

			request.done(function (res) {
				if (res.success) {
					cronjobModal.hide();
					loadList();
				} else {
					console.error('[cronjob] gagal menyimpan:', res);
					let msg = res.message || 'Gagal menyimpan cron job.';
					if (res.debug) {
						msg += ',  ' + JSON.stringify(res.debug);
					}
					errorBox.removeClass('d-none').text(msg);
				}
			}).fail(function (xhr) {
				console.error('[cronjob] gagal menyimpan (request):', xhr.status, xhr.responseText);
				errorBox.removeClass('d-none').text('Gagal menyimpan cron job (HTTP ' + xhr.status + '). Lihat console untuk detail.');
			}).always(function () {
				submitBtn.prop('disabled', false);
			});
		});

		function renderFailedList(failed) {
			const listBox = $('#cron-failed-list');
			const itemsBox = $('#cron-failed-items');
			itemsBox.empty();

			failed = failed || [];

			if (!failed.length) {
				listBox.addClass('d-none');
				return;
			}

			$('#cron-failed-count').text(failed.length);

			failed.forEach(function (label) {
				const manualAction = manualActionMap[label];
				let actionBtn = '';

				if (manualAction) {
					actionBtn = `
						<button type="button" class="btn btn-sm btn-outline-success btn-run-cron" title="${manualAction.title}" data-url="${manualAction.url}" data-title="${manualAction.title}" data-confirm="${manualAction.confirm}">
							<i class="${manualAction.icon}"></i> ${manualAction.label} Manual
						</button>`;
				}

				itemsBox.append(
					'<li class="list-group-item d-flex align-items-center justify-content-between flex-wrap gap-2">' +
					'<span class="d-flex align-items-center gap-2">' +
					'<i class="fa-solid fa-xmark text-danger"></i>' +
					'<code>' + escapeHtml(label) + '</code>' +
					'</span>' +
					actionBtn +
					'</li>'
				);
			});

			listBox.removeClass('d-none');
		}

		function autoSync() {
			const resultBox = $('#cron-sync-result');
			resultBox.removeClass('d-none alert-success alert-warning alert-danger').addClass('alert-info').text('Menyinkronkan job dari config...');

			$.post(urlSync, csrfPayload({}), function (res) {
				const removed = res.removed || [];
				const total = res.ok.length + res.failed.length;

				renderFailedList(res.failed);

				if (total === 0 && removed.length === 0) {
					resultBox.addClass('d-none');
					return;
				}

				let html = `<strong>${res.ok.length}/${total}</strong> job dari config tersinkron.`;

				if (removed.length) {
					html += '<br><span class="text-danger"><i class="fa-solid fa-trash"></i> Dihapus dari crontab: ' + removed.map(escapeHtml).join(', ') + '</span>';
				}

				if (res.failed.length) {
					html += '<br><span class="text-danger"><i class="fa-solid fa-xmark"></i> Gagal: ' + res.failed.map(escapeHtml).join(', ') + '</span>';
				}

				resultBox.removeClass('alert-info').addClass(res.failed.length ? 'alert-warning' : 'alert-success').html(html);
			}, 'json').fail(function (xhr) {
				console.error('[cronjob] auto-sync gagal (request):', xhr.status, xhr.responseText);
				resultBox.removeClass('alert-info').addClass('alert-danger').text('Auto-sync gagal (HTTP ' + xhr.status + '). Lihat console untuk detail.');
			}).always(function () {
				loadList();
			});
		}

		autoSync();
	});
</script>