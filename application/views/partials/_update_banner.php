<?php if (!empty($update_banner_data) && $update_banner_data['status'] === 'update_available'):
	$manifest = $update_banner_data['manifest'];
	$channel = isset($update_banner_data['channel']) ? $update_banner_data['channel'] : 'stable';
	$is_admin = isset($this->ion_auth) && $this->ion_auth->logged_in() && $this->ion_auth->is_admin();
	$is_logged_in = isset($this->ion_auth) && $this->ion_auth->logged_in();
?>
	<div id="update-banner" class="alert alert-warning">
		<strong>Tersedia pembaruan: v<span id="ub-version"><?= htmlspecialchars($manifest['version']) ?></span></strong> [<span id="ub-channel-name"><?= htmlspecialchars($channel) ?></span>]
		<p id="ub-changelog">Catatan pembaruan: <?= nl2br(htmlspecialchars(isset($manifest['changelog']) ? $manifest['changelog'] : '')) ?></p>
		<div class="alert alert-info mt-2 mb-2">
			<strong>Sebelum memperbarui, pastikan izin direktori sudah benar dengan menjalankan:</strong><br>
			<code>chown apache:apache -R /var/www/html/siaga</code>
		</div>
		<?php if ($is_admin): ?>
			<button onclick="applyUpdate(this)" class="btn btn-sm btn-primary">Perbarui sekarang</button>
		<?php elseif (!$is_logged_in): ?>
			<button type="button" onclick="openLoginModal()" class="btn btn-sm btn-danger">Login untuk mengupdate</button>
		<?php else: ?>
			<small class="text-muted">Hanya admin yang dapat melakukan pembaruan.</small>
		<?php endif; ?>
	</div>
	<div id="html5-busy-overlay"><div class="spinner"></div></div>

	<script>
		function html5BusyShow() {
			var el = document.getElementById('html5-busy-overlay');
			if (el) el.style.display = 'flex';
		}
		function html5BusyHide() {
			var el = document.getElementById('html5-busy-overlay');
			if (el) el.style.display = 'none';
		}

		function openLoginModal() {
			var modalEl = document.getElementById('modal-input');

			if (!modalEl) {
				modalEl = document.createElement('dialog');
				modalEl.id = 'modal-input';
				modalEl.style.cssText = 'padding:0;border:none;border-radius:6px;width:min(500px,92vw);max-height:85vh;box-shadow:0 10px 40px rgba(0,0,0,.25);';
				modalEl.innerHTML =
					'<div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #dee2e6;">' +
						'<h5 class="modal-title" style="margin:0;">Login</h5>' +
						'<button type="button" class="modal-close-btn" aria-label="Close" style="background:none;border:none;font-size:1.4rem;line-height:1;cursor:pointer;">&times;</button>' +
					'</div>' +
					'<div class="modal-body" style="padding:16px;overflow-y:auto;"></div>';
				document.body.appendChild(modalEl);

				// Native <dialog> closes on Escape by default (fires 'cancel').
				// Block that to mimic data-bs-keyboard="false".
				modalEl.addEventListener('cancel', function(e) {
					e.preventDefault();
				});

				modalEl.querySelector('.modal-close-btn').addEventListener('click', function() {
					modalEl.close();
				});

				// showModal() already prevents outside-click dismissal by default
				// (there's no listener closing it on backdrop click), matching
				// data-bs-backdrop="static".
			}

			var $modal = $(modalEl);
			$modal.find('.modal-title').html('Login');
			$modal.find('.modal-body').html('<div class="text-center p-4"><i class="fa fa-circle-o-notch fa-spin fa-2x"></i></div>');

			if (typeof modalEl.showModal === 'function') {
				if (!modalEl.open) {
					modalEl.showModal();
				}
			} else {
				// Extremely old browser without <dialog> support.
				console.error('Native <dialog> element is not supported in this browser.');
				modalEl.setAttribute('open', 'open');
				modalEl.style.position = 'fixed';
				modalEl.style.top = '10vh';
				modalEl.style.left = '50%';
				modalEl.style.transform = 'translateX(-50%)';
				modalEl.style.zIndex = 1050;
			}

			$.ajax({
				url: '<?= base_url('site/login') ?>',
				dataType: 'json',
				success: function(data) {
					if (data.content) {
						$modal.find('.modal-body').html(data.content);
					} else if (data.message) {
						$modal.find('.modal-body').html('<div class="alert alert-danger">' + data.message + '</div>');
					} else {
						$modal.find('.modal-body').html('<div class="alert alert-danger">Respon tidak dikenali dari server.</div>');
					}
					if (data.csrf_token_name && data.csrf_hash) {
						localStorage.setItem('csrfName', data.csrf_token_name);
						localStorage.setItem('csrfToken', data.csrf_hash);
					}
				},
				error: function(xhr) {
					$modal.find('.modal-body').html(
						'<div class="alert alert-danger">Gagal memuat form login (status ' + xhr.status + '). Silakan coba lagi.</div>'
					);
				}
			});
		}

		// The login form itself is injected dynamically into #modal-input
		// .modal-body (both on initial modal open and after a failed
		// attempt), so a static selector won't catch it — delegate the
		// submit listener from document instead. Without this, the <form>
		// does a normal native POST: the browser navigates away from the
		// page entirely and lands on site/login's raw JSON response,
		// because CodeIgniter can tell it wasn't an AJAX request.
		$(document).on('submit', '#modal-input form', function(e) {
			e.preventDefault();

			var $form = $(this);
			var $modal = $form.closest('#modal-input');
			var $submitBtn = $form.find('[type="submit"]');
			var originalBtnText = $submitBtn.html();

			$submitBtn.prop('disabled', true);

			fetch('<?= base_url('site/login') ?>', {
					method: 'POST',
					body: new FormData(this),
					headers: {
						// CodeIgniter's is_ajax_request() looks for this header.
						// fetch() doesn't send it automatically the way
						// jQuery's $.ajax does, so it must be set explicitly.
						'X-Requested-With': 'XMLHttpRequest'
					}
				})
				.then(function(r) {
					return r.json();
				})
				.then(function(data) {
					if (data.csrf_token_name && data.csrf_hash) {
						localStorage.setItem('csrfName', data.csrf_token_name);
						localStorage.setItem('csrfToken', data.csrf_hash);
					}

					if (data.status === true) {
						location.href = data.redirect || '<?= base_url() ?>';
						return;
					}

					// Failed login/validation: re-render the form (it comes
					// back with fresh CSRF field + error messages baked in).
					if (data.content) {
						$modal.find('.modal-body').html(data.content);
					} else if (data.message) {
						$modal.find('.modal-body').prepend(
							'<div class="alert alert-danger">' + data.message + '</div>'
						);
						$submitBtn.prop('disabled', false).html(originalBtnText);
					} else {
						$submitBtn.prop('disabled', false).html(originalBtnText);
					}
				})
				.catch(function() {
					$modal.find('.modal-body').prepend(
						'<div class="alert alert-danger">Gagal terhubung ke server. Silakan coba lagi.</div>'
					);
					$submitBtn.prop('disabled', false).html(originalBtnText);
				});
		});

		window._pendingManifest = <?= json_encode($manifest) ?>;

		function applyUpdate(btn) {
			if (!confirm('Sebelum memperbarui, pastikan izin direktori sudah benar dengan menjalankan:\n\nchown apache:apache -R /var/www/html/siaga\n\nIni akan membuat cadangan sistem Anda dan menerapkan pembaruan v' + window._pendingManifest.version + '. Lanjutkan?')) return;
			html5BusyShow();
			btn.disabled = true;
			btn.textContent = 'Memperbarui...';

			const params = new URLSearchParams();
			params.append('expected_version', window._pendingManifest.version);

			fetch('<?= base_url("updater/apply") ?>', {
					method: 'POST',
					body: params,
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded'
					}
				})
				.then(function(r) {
					return r.json();
				})
				.then(function(data) {
					if (data.status === 'success') {
						alert('Berhasil diperbarui ke v' + data.version + '. Halaman akan dimuat ulang.');
						location.reload();
					} else if (data.status === 'up_to_date') {
						alert('Sistem sudah up to date (v' + (data.current_version || window._pendingManifest.version) + '). Halaman akan dimuat ulang.');
						location.reload();
					} else {
						alert('Pembaruan gagal: ' + data.message + '\nSistem Anda tidak diubah — dikembalikan dari cadangan secara otomatis.');
					}
				})
				.catch(function() {
					alert('Pembaruan gagal. Sistem Anda tidak diubah — dikembalikan dari cadangan secara otomatis.');
				})
				.finally(function() {
					html5BusyHide();
				});
		}

		function rollbackUpdate() {
			if (!confirm('Ini akan mengembalikan versi sebelumnya dari cadangan. Lanjutkan?')) return;
			var btn = document.getElementById('rollback-btn');
			btn.disabled = true;
			btn.textContent = 'Mengembalikan...';
			html5BusyShow();

			fetch('<?= base_url("updater/rollback") ?>', {
					method: 'POST'
				})
				.then(function(r) {
					return r.json();
				})
				.then(function(data) {
					if (data.status === 'success') {
						alert('Rollback berhasil. Halaman akan dimuat ulang.');
						location.reload();
					} else {
						alert('Rollback gagal: ' + data.message);
					}
				})
				.catch(function() {
					alert('Rollback gagal. Terjadi kesalahan jaringan atau server.');
				})
				.finally(function() {
					html5BusyHide();
				});
		}

		function dismissUpdate() {
			document.getElementById('update-banner').style.display = 'none';

			fetch('<?= base_url("updater/dismiss") ?>', {
					method: 'POST'
				})
				.then(function(r) {
					return r.json();
				})
				.catch(function() {
					/* silent */
				})
				.finally(function() {
					/* silent */
				});
		}

		<?php if (!empty($update_banner_data['has_backup'])): ?>
			// document.getElementById('rollback-btn').style.display = 'inline-block';
		<?php endif; ?>
	</script>
<?php endif; ?>