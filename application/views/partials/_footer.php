<?php
$hasAssets = isset($assets) && is_array($assets);
$legacyFallback = !$hasAssets && empty($lightAssets);

$useDatepicker = ($hasAssets && !empty($assets['datepicker'])) || $legacyFallback;
$useViewer     = ($hasAssets && !empty($assets['viewer']))     || $legacyFallback;
$useDaterange  = ($hasAssets && !empty($assets['daterange']))  || $legacyFallback;
$useDataTables = ($hasAssets && !empty($assets['datatables'])) || $legacyFallback;
$useExport     = ($hasAssets && !empty($assets['export']))     || $legacyFallback; // jszip + pdfmake
$useKnob       = ($hasAssets && !empty($assets['knob']))       || $legacyFallback;
$useNprogress  = ($hasAssets && !empty($assets['nprogress']))  || $legacyFallback;
?>

<?php if ($useDatepicker): ?>
	<link rel="stylesheet" href="<?php echo asset_url('assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.css') ?>">
<?php endif; ?>

<link rel="stylesheet" href="<?php echo asset_url('assets/plugins/select2/select2.min.css') ?>">
<link rel="stylesheet" href="<?php echo asset_url('assets/plugins/select2/select2-bootstrap-5-theme.min.css') ?>">

<link rel="stylesheet" href="<?php echo asset_url('assets/css/waviy.css') ?>">

<?php if ($useNprogress): ?>
	<link rel="stylesheet" href="<?php echo asset_url('assets/plugins/nprogress/nprogress.css') ?>">
	<script src="<?php echo asset_url('assets/plugins/nprogress/nprogress.js') ?>" defer></script>
<?php endif; ?>

<?php if ($this->showFooter && is_local_ip()) : ?>
	<footer class="app-footer main-footer d-flex justify-content-between align-items-center">
		<span>
			<small>
				<a href="https://dialogwa.com" target="_blank" class="text-decoration-none" style="position: relative; z-index: 11;">CK &copy; https://dialogwa.com</a>
			</small>
		</span>

		<div class="footer-right float-end d-none d-sm-flex align-items-center" style="position: relative; z-index: 11;"></div>
	</footer>
<?php endif ?>

<!-- About Modal -->
<div class="modal fade" id="footerModal" tabindex="-1" aria-labelledby="footerModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<div class="d-flex" style="margin: 0 auto;">
					<h5 class="modal-title" id="footerModalLabel">Tentang <?php echo $this->config->item('APP_SHORT_NAME') ?: 'Aplikasi'; ?></h5>
				</div>
				<button type="button" class="btn-close ms-0" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="about-content">
					<p>Aplikasi ini lahir dari kebutuhan sederhana: membuat pekerjaan sehari-hari menjadi lebih mudah, lebih rapi, dan lebih efisien. Kami percaya bahwa sistem yang baik bukan hanya tentang fitur, tetapi tentang bagaimana ia membantu tim bekerja dengan lebih nyaman dan terarah.</p>

					<p>Dirancang khusus untuk mendukung proses internal, aplikasi ini berfokus pada kejelasan alur kerja, akurasi data, serta kemudahan penggunaan. Setiap modul dikembangkan dengan mempertimbangkan kebutuhan nyata di lapangan, sehingga solusi yang diberikan benar-benar relevan dan dapat langsung digunakan.</p>

					<p>Kami menyadari bahwa sistem yang andal adalah fondasi dari kolaborasi yang solid. Karena itu, aplikasi ini dibangun dengan perhatian pada keamanan, stabilitas, dan konsistensi performa — agar dapat diandalkan dalam setiap aktivitas kerja.</p>

					<p>Semoga aplikasi ini menjadi alat bantu yang mempermudah tugas, mempercepat proses, dan mendukung produktivitas tim setiap hari.</p>
				</div>
			</div>
			<div class="modal-footer">
			</div>
		</div>
	</div>
</div>

<style>
	/* ============================================================
			FAB — Shared base + per-instance overrides via CSS variables

			Position is driven by --fab-slot (set inline per element by
			PHP, based on which FAB groups actually have content — see
			the PHP block below). Previously each container had a fixed
			right:Npx plus a `.fab-no-notif` hack to shift .fab-settings-
			container left when notif was hidden — that only ever
			accounted for ONE possible gap. With 5 groups now (main,
			rekap, notif, settings, auth) any subset could be hidden, so
			positions are computed from a contiguous slot index instead.
			============================================================ */
	/* --- Containers --- */
	.fab-container,
	.fab-notif-container,
	.fab-settings-container,
	.fab-rekap-container,
	.fab-auth-container {
		position: fixed;
		bottom: 30px;
		right: calc(var(--fab-base, 30px) + (var(--fab-slot, 0) * var(--fab-gap, 70px)));
		z-index: calc(9999 - var(--fab-slot, 0));
		font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
	}

	/* --- Main buttons --- */
	.fab-main,
	.fab-notif-main,
	.fab-settings-main,
	.fab-rekap-main,
	.fab-auth-main {
		width: 60px;
		height: 60px;
		border-radius: 50%;
		color: white;
		display: flex;
		align-items: center;
		justify-content: center;
		font-size: 24px;
		cursor: pointer;
		transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
		border: none;
		outline: none;
		background: var(--fab-gradient);
		box-shadow: var(--fab-shadow);
	}

	.fab-main {
		--fab-gradient: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
		--fab-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);
		--fab-shadow-hover: 0 6px 20px rgba(99, 102, 241, 0.6);
	}

	.fab-notif-main {
		--fab-gradient: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%);
		--fab-shadow: 0 4px 15px rgba(14, 165, 233, 0.4);
		--fab-shadow-hover: 0 6px 20px rgba(14, 165, 233, 0.6);
	}

	.fab-settings-main {
		--fab-gradient: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%);
		--fab-shadow: 0 4px 15px rgba(139, 92, 246, 0.4);
		--fab-shadow-hover: 0 6px 20px rgba(139, 92, 246, 0.6);
	}

	.fab-rekap-main {
		--fab-gradient: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
		--fab-shadow: 0 4px 15px rgba(20, 184, 166, 0.4);
		--fab-shadow-hover: 0 6px 20px rgba(20, 184, 166, 0.6);
	}

	/* Auth FAB is a direct link (login/logout), not a toggle group — its
	   gradient reflects which action it currently performs. */
	.fab-auth-main.is-login {
		--fab-gradient: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
		--fab-shadow: 0 4px 15px rgba(34, 197, 94, 0.4);
		--fab-shadow-hover: 0 6px 20px rgba(34, 197, 94, 0.6);
	}

	.fab-auth-main.is-logout {
		--fab-gradient: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
		--fab-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
		--fab-shadow-hover: 0 6px 20px rgba(239, 68, 68, 0.6);
	}

	.fab-auth-main:hover {
		transform: scale(1.1);
		box-shadow: var(--fab-shadow-hover);
	}

	/* --- Hover states --- */
	.fab-main:hover,
	.fab-notif-main:hover,
	.fab-settings-main:hover,
	.fab-rekap-main:hover {
		transform: scale(1.1);
		box-shadow: var(--fab-shadow-hover);
	}

	.fab-main:hover i,
	.fab-notif-main:hover i,
	.fab-settings-main:hover i,
	.fab-rekap-main:hover i,
	.fab-auth-main:hover i {
		animation: fab-icon-rotate 0.8s ease-in-out;
	}

	@keyframes fab-icon-rotate {
		0% {
			transform: rotate(0deg);
		}

		50% {
			transform: rotate(90deg);
		}

		100% {
			transform: rotate(0deg);
		}
	}

	/* --- Active (open) states --- */
	.fab-main.active {
		transform: rotate(45deg);
		background: linear-gradient(135deg, #ef4444 0%, #f97316 100%);
	}

	.fab-notif-main.active {
		transform: rotate(135deg);
		background: linear-gradient(135deg, #ef4444 0%, #f97316 100%);
	}

	.fab-settings-main.active {
		transform: rotate(225deg);
		background: linear-gradient(135deg, #ef4444 0%, #f97316 100%);
	}

	.fab-rekap-main.active {
		transform: rotate(315deg);
		background: linear-gradient(135deg, #ef4444 0%, #f97316 100%);
	}

	/* --- Options panels --- */
	.fab-options,
	.fab-notif-options,
	.fab-settings-options,
	.fab-rekap-options {
		position: absolute;
		bottom: 70px;
		right: 0;
		display: flex;
		flex-direction: column;
		gap: 5px;
		opacity: 0;
		visibility: hidden;
		transition: all 0.3s ease;
		transform: translateY(20px);
		max-height: calc(100vh - 100px);
		overflow-y: auto;
		scrollbar-width: thin;
		scrollbar-color: rgba(99, 102, 241, 0.5) transparent;
	}

	.fab-options::-webkit-scrollbar,
	.fab-notif-options::-webkit-scrollbar,
	.fab-settings-options::-webkit-scrollbar,
	.fab-rekap-options::-webkit-scrollbar {
		width: 6px;
	}

	.fab-options::-webkit-scrollbar-thumb,
	.fab-notif-options::-webkit-scrollbar-thumb,
	.fab-settings-options::-webkit-scrollbar-thumb,
	.fab-rekap-options::-webkit-scrollbar-thumb {
		background-color: rgba(99, 102, 241, 0.5);
		border-radius: 3px;
	}

	.fab-options.active,
	.fab-notif-options.active,
	.fab-settings-options.active,
	.fab-rekap-options.active {
		padding-left: 1.5rem;
		padding-bottom: .5rem;
		opacity: 1;
		visibility: visible;
		transform: translateY(0);
	}

	/* --- Option items --- */
	.fab-option {
		display: flex;
		align-items: center;
		gap: 5px;
		background: white;
		color: #333;
		padding: 12px 20px;
		border-radius: 30px;
		text-decoration: none;
		font-size: 14px;
		font-weight: 500;
		box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
		transition: all 0.2s ease;
		white-space: nowrap;
		transform-origin: right center;
	}

	.fab-option:hover {
		background: #f3f4f6;
		transform: translateX(-5px) scale(1.02);
		box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
	}

	/* --- Icons (color varies per FAB) --- */
	.fab-option i {
		width: 20px;
		text-align: center;
		font-size: 16px;
		color: var(--fab-icon-color, #6366f1);
	}

	.fab-option:hover i {
		color: var(--fab-icon-hover-color, #8b5cf6);
	}

	.fab-options .fab-option {
		--fab-icon-color: #6366f1;
		--fab-icon-hover-color: #8b5cf6;
	}

	.fab-notif-options .fab-option {
		--fab-icon-color: #0ea5e9;
		--fab-icon-hover-color: #2563eb;
	}

	.fab-settings-options .fab-option {
		--fab-icon-color: #8b5cf6;
		--fab-icon-hover-color: #6366f1;
	}

	.fab-rekap-options .fab-option {
		--fab-icon-color: #14b8a6;
		--fab-icon-hover-color: #0d9488;
	}

	/* --- Responsive --- */
	@media (max-width: 768px) {

		.fab-container,
		.fab-notif-container,
		.fab-settings-container,
		.fab-rekap-container,
		.fab-auth-container {
			--fab-base: 20px;
			--fab-gap: 65px;
			bottom: 20px;
		}

		.fab-main,
		.fab-notif-main,
		.fab-settings-main,
		.fab-rekap-main,
		.fab-auth-main {
			width: 55px;
			height: 55px;
			font-size: 20px;
		}

		.fab-option {
			padding: 10px 16px;
			font-size: 13px;
		}

		.fab-options,
		.fab-notif-options,
		.fab-settings-options,
		.fab-rekap-options {
			bottom: 65px;
			gap: 10px;
		}
	}
</style>

<!-- FAB (Floating Action Button) -->
<?php
$CI = &get_instance();
$CI->load->library('updater_lib');
$currentUri = uri_string();
$is_logged_in = isset($this->ion_auth) && $this->ion_auth->logged_in();
$is_admin = $is_logged_in && $this->ion_auth->is_admin();

$fabItems = [
	[
		'url' => base_url(),
		'icon' => 'fas fa-chart-pie',
		'label' => 'Dashboard',
		'title' => 'Dashboard',
	],
	[
		'url' => base_url('monitoring/edoc'),
		'icon' => 'fa-solid fa-file-signature',
		'label' => 'Monitoring E-doc',
		'title' => 'Monitoring E-doc',
	],
	[
		'url' => base_url('ck/sidang'),
		'icon' => 'fas fa-gavel',
		'label' => 'Jadwal Sidang',
		'title' => 'Jadwal Sidang',
	],
	[
		'url' => base_url('kinerja/relaas'),
		'icon' => 'fas fa-file-alt',
		'label' => 'Monitoring Relaas',
		'title' => 'Monitoring Relaas',
	],
	[
		'url' => base_url('kinerja/hakim'),
		'icon' => 'fa-solid fa-gavel',
		'label' => 'Kinerja Hakim',
		'title' => 'Kinerja Hakim',
	],
	[
		'url' => base_url('ck/bht'),
		'icon' => 'fas fa-clipboard-check',
		'label' => 'Monitoring BHT',
		'title' => 'Monitoring BHT',
	],
];

// Moved out of $fabItems into their own "Rekapitulasi" group.
$rekapItems = [
	[
		'url' => base_url('rekapitulasi/keadaanperkara'),
		'icon' => 'fas fa-building-columns',
		'label' => 'Keadaan Perkara',
		'title' => 'Keadaan Perkara',
	],
	[
		'url' => base_url('rekapitulasi/keadaanperkara/ecourt'),
		'icon' => 'fas fa-building-columns',
		'label' => 'Perkara e-Court',
		'title' => 'Perkara e-Court',
	],
	// [
	// 	'url' => base_url('rekapitulasi/kecamatan'),
	// 	'icon' => 'fas fa-map-marker-alt',
	// 	'label' => 'Perkara Kecamatan',
	// 	'title' => 'Perkara Kecamatan',
	// ],
	[
		'url' => base_url('rekapitulasi/mediasi'),
		'icon' => 'fas fa-users',
		'label' => 'Rekapitulasi Mediasi',
		'title' => 'Rekapitulasi Mediasi',
	],
	[
		'url' => base_url('rekapitulasi/cerai'),
		'icon' => 'fas fa-heart-crack',
		'label' => 'Perkara Cerai',
		'title' => 'Perkara Cerai',
	],
	[
		'url' => base_url('rekapitulasi/keadaanperkara/dk'),
		'icon' => 'fas fa-file-signature',
		'label' => 'Dispensasi Kawin',
		'title' => 'Dispensasi Kawin',
	],
	[
		'url' => base_url('rekapitulasi/banding'),
		'icon' => 'fas fa-scale-balanced',
		'label' => 'Perkara Banding',
		'title' => 'Perkara Banding',
	],
	[
		'url' => base_url('rekapitulasi/kasasi'),
		'icon' => 'fas fa-balance-scale',
		'label' => 'Perkara Kasasi',
		'title' => 'Perkara Kasasi',
	],
	[
		'url' => base_url('rekapitulasi/pk'),
		'icon' => 'fas fa-scale-balanced',
		'label' => 'Perkara PK',
		'title' => 'Perkara PK',
	],
];

$settingsFabItems = [
	[
		'url' => base_url('app/blangko'),
		'icon' => 'fas fa-file-invoice',
		'label' => 'Manajemen Blangko ABT',
		'title' => 'Manajemen Blangko ABT',
		'logged_in_only' => true,
	],
	[
		'url' => base_url('ck/pegawai'),
		'icon' => 'fas fa-user-tag',
		'label' => 'Pegawai',
		'title' => 'Pegawai',
		'admin_only' => true,
	],
	[
		'url' => base_url('settings/holiday'),
		'icon' => 'fa-solid fa-calendar-xmark',
		'label' => 'Hari Libur',
		'title' => 'Hari Libur',
		'admin_only' => true,
	],
	[
		'url' => base_url('settings/web'),
		'icon' => 'fas fa-globe',
		'label' => 'Daftar Web',
		'title' => 'Daftar Web',
		'admin_only' => true,
	],
	[
		'url' => base_url('whatsapp/queue'),
		'icon' => 'fa-brands fa-whatsapp',
		'label' => 'Antrian WhatsApp',
		'title' => 'Antrian WhatsApp',
		'admin_only' => true,
	],
	[
		'url' => base_url('whatsapp/log'),
		'icon' => 'fa-brands fa-whatsapp',
		'label' => 'Log WhatsApp',
		'title' => 'Log WhatsApp',
		'admin_only' => true,
	],
	[
		'url' => base_url('settings/cron'),
		'icon' => 'fas fa-clock',
		'label' => 'Pengelolaan Otomatisasi',
		'title' => 'Pengelolaan Otomatisasi',
		'admin_only' => true,
	],
	[
		'url' => base_url('settings/config'),
		'icon' => 'fas fa-sliders-h',
		'label' => 'Konfigurasi',
		'title' => 'Konfigurasi',
		'admin_only' => true,
	],
	[
		'url' => 'javascript:void(0)',
		'icon' => 'fas fa-circle-question',
		'label' => 'Panduan',
		'title' => 'Panduan Penggunaan',
		'class' => 'btn-faq',
		'id' => 'fabFaq',
	],
	[
		'url' => '#',
		'icon' => 'fas fa-code-branch',
		'label' => '<small>Versi ' . ($CI->updater_lib->get_current_version() ?: '1.0') . '<br>Load Time : ' . $this->benchmark->elapsed_time('code_start', 'code_end') . 'Sec.&nbsp;</br>Memory Usage : ' . round(memory_get_peak_usage(false) / 1048576, 2) . '/' . ini_get('memory_limit') . '</small>',
		'title' => 'Versi Aplikasi',
		'raw_label' => true,
	],
];

// Moved out of $settingsFabItems into its own auth FAB — a single direct
// link (not a toggle group with an options panel), since there's only
// ever exactly one action available at a time.
if (!$is_logged_in) {
	$authFabItems = [
		[
			'url' => base_url('site/login'),
			'icon' => 'fas fa-right-to-bracket',
			'label' => 'Login',
			'title' => 'Login',
			'class' => 'btn-modal is-login',
			'id' => 'fabLogin',
		],
	];
} else {
	$authFabItems = [
		[
			'url' => base_url('site/logout'),
			'icon' => 'fas fa-right-from-bracket',
			'label' => 'Keluar',
			'title' => 'Keluar',
			'class' => 'btn-confirm is-logout',
			'confirm_message' => 'Anda yakin ingin keluar?',
			'id' => 'fabLogout',
		],
	];
}

/**
 * Shared renderer for one FAB option link (used by every group with an
 * options panel — main/rekap/settings; auth is a direct link and
 * doesn't go through this). PHP 5.6-compatible: ternaries/isset(), no ??.
 */
$renderFabOption = function ($item) use ($currentUri, $is_admin, $is_logged_in) {
	$hidden = false;
	if (!empty($item['admin_only']) && !$is_admin) {
		$hidden = true;
	}
	if (!$hidden && !empty($item['logged_in_only']) && !$is_logged_in) {
		$hidden = true;
	}
	$classes = 'fab-option' . (isset($item['class']) ? ' ' . $item['class'] : '') . ($hidden ? ' d-none' : '');
	$attrs = '';
	if (isset($item['id'])) $attrs .= " id=\"{$item['id']}\"";
	if (isset($item['confirm_message'])) $attrs .= " data-confirm-message=\"{$item['confirm_message']}\"";
	?>
	<a href="<?php echo $item['url'] ?>" class="<?php echo $classes ?>" data-title="<?php echo $item['title'] ?>" <?php echo $attrs ?>>
		<i class="<?php echo $item['icon'] ?>"></i>
		<?php if (isset($item['raw_label']) && $item['raw_label']): ?>
			<?php echo $item['label'] ?>
		<?php else: ?>
			<span><?php echo $item['label'] ?></span>
		<?php endif; ?>
	</a>
<?php
};

// Determine which groups actually have content, right-to-left (slot 0 =
// closest to the screen edge). Only groups with something to show get a
// slot, and slots are assigned in this order so a hidden group never
// leaves a gap — whichever group is next in line just becomes slot 0.
// Visual left→right order requested: main, rekap, settings, auth
// — so right→left (slot 0 upward) is the reverse: auth, settings, rekap,
// main.
$fabGroupsPresent = [
	'auth'     => !empty($authFabItems),
	'settings' => !empty($settingsFabItems),
	'rekap'    => !empty($rekapItems),
	'main'     => !empty($fabItems),
];
$fabSlots = [];
$slot = 0;
foreach ($fabGroupsPresent as $key => $present) {
	if ($present) {
		$fabSlots[$key] = $slot++;
	}
}
?>

<?php if ($fabGroupsPresent['main']): ?>
	<div class="fab-container" id="fabContainer" style="--fab-slot: <?php echo $fabSlots['main'] ?>;">
		<div class="fab-main" id="fabMain">
			<i class="fas fa-list"></i>
		</div>
		<div class="fab-options" id="fabOptions">
			<?php foreach ($fabItems as $item): $renderFabOption($item); ?>
			<?php endforeach; ?>
		</div>
	</div>
<?php endif ?>

<!-- FAB Rekapitulasi (Floating Action Button) -->
<?php if ($fabGroupsPresent['rekap']): ?>
	<div class="fab-container fab-rekap-container" id="fabRekapContainer" style="--fab-slot: <?php echo $fabSlots['rekap'] ?>;">
		<div class="fab-main fab-rekap-main" id="fabRekapMain">
			<i class="fas fa-chart-column"></i>
		</div>
		<div class="fab-options fab-rekap-options" id="fabRekapOptions">
			<?php foreach ($rekapItems as $item): $renderFabOption($item); ?>
			<?php endforeach; ?>
		</div>
	</div>
<?php endif ?>

<!-- FAB Settings (Floating Action Button) -->
<?php if ($fabGroupsPresent['settings']): ?>
	<div class="fab-container fab-settings-container" id="fabSettingsContainer" style="--fab-slot: <?php echo $fabSlots['settings'] ?>;">
		<div class="fab-main fab-settings-main" id="fabSettingsMain">
			<i class="fas fa-cog"></i>
		</div>
		<div class="fab-options fab-settings-options" id="fabSettingsOptions">
			<?php foreach ($settingsFabItems as $item): $renderFabOption($item); ?>
			<?php endforeach; ?>
		</div>
	</div>
<?php endif ?>

<!-- FAB Auth (Login/Logout) — direct link, no options panel -->
<?php if ($fabGroupsPresent['auth']): $authItem = $authFabItems[0]; ?>
	<div class="fab-container fab-auth-container" id="fabAuthContainer" style="--fab-slot: <?php echo $fabSlots['auth'] ?>;">
		<a href="<?php echo $authItem['url'] ?>"
			class="fab-main fab-auth-main <?php echo isset($authItem['class']) ? $authItem['class'] : '' ?>"
			id="<?php echo isset($authItem['id']) ? $authItem['id'] : 'fabAuthMain' ?>"
			data-title="<?php echo $authItem['title'] ?>"
			<?php if (isset($authItem['confirm_message'])): ?>data-confirm-message="<?php echo $authItem['confirm_message'] ?>"<?php endif; ?>>
			<i class="<?php echo $authItem['icon'] ?>"></i>
		</a>
	</div>
<?php endif ?>

<!-- Modal -->
<div class="modal fade" id="modal-input" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
	<div id="modal-input-dialog" class="modal-dialog modal-dialog-scrollable modal-md"> <!-- modal-dialog-centered -->
		<div class="modal-content">
			<div class="modal-header">
				<div class="d-flex" style="margin: 0 auto;">
					<h5 class="modal-title" id="staticBackdropLabel"></h5>
				</div>
				<button type="button" class="btn-close ms-0 collapse" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body"></div>
			<big>
				<h1 id="counter" class="mb-3" style="color: red; text-align: center; margin-top: 0; display: none;"></h1>
			</big>
		</div>
	</div>
</div>

<!-- FAQ Modal -->
<div class="modal fade" id="faqModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="faqModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-scrollable modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<div class="d-flex" style="margin: 0 auto;">
					<h5 class="modal-title" id="faqModalLabel">Panduan Penggunaan</h5>
				</div>
				<button type="button" class="btn-close ms-0" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="accordion" id="faqAccordion">
					<div class="accordion-item">
						<h2 class="accordion-header" id="faq1"><button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse1" aria-expanded="true" aria-controls="faqCollapse1">Bagaimana cara menggunakan nomor sendiri sebagai bot WhatsApp?</button></h2>
						<div id="faqCollapse1" class="accordion-collapse collapse show" aria-labelledby="faq1" data-bs-parent="#faqAccordion">
							<div class="accordion-body">Registrasi nomor bot di <a href="https://dialogwa.com" target="_blank">dialogwa.com</a> untuk mendapatkan kredensial API.</div>
						</div>
					</div>
					<div class="accordion-item">
						<h2 class="accordion-header" id="faq2"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse2" aria-expanded="false" aria-controls="faqCollapse2">Bagaimana cara mengatur jadwal automatisasi pengiriman pesan?</button></h2>
						<div id="faqCollapse2" class="accordion-collapse collapse" aria-labelledby="faq2" data-bs-parent="#faqAccordion">
							<div class="accordion-body">Buka menu <strong>Pengelolaan Otomatisasi</strong> (Settings &gt; Cron) untuk mengatur cron job pengiriman notifikasi secara otomatis.</div>
						</div>
					</div>
					<div class="accordion-item">
						<h2 class="accordion-header" id="faq3"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse3" aria-expanded="false" aria-controls="faqCollapse3">Apakah testing notifikasi hanya dapat dilakukan di mode development?</button></h2>
						<div id="faqCollapse3" class="accordion-collapse collapse" aria-labelledby="faq3" data-bs-parent="#faqAccordion">
							<div class="accordion-body">Ya, uji coba pengiriman notifikasi hanya aman dilakukan pada mode <strong>development</strong>. Pastikan <code>WA_TEST_TARGET</code> sudah diisi di Settings &gt; Config untuk menerima pesan uji.</div>
						</div>
					</div>
					<div class="accordion-item">
						<h2 class="accordion-header" id="faq4"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse4" aria-expanded="false" aria-controls="faqCollapse4">Bagaimana cara mengatur nomor HP pegawai agar menerima notifikasi?</button></h2>
						<div id="faqCollapse4" class="accordion-collapse collapse" aria-labelledby="faq4" data-bs-parent="#faqAccordion">
							<div class="accordion-body">Buka menu <strong>Pegawai</strong>, lalu isi kolom <strong>Phone / nomor HP</strong> untuk setiap pegawai yang akan menerima notifikasi.</div>
						</div>
					</div>
					<div class="accordion-item">
						<h2 class="accordion-header" id="faq5"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse5" aria-expanded="false" aria-controls="faqCollapse5">Apa saja ID group yang harus diisi untuk menerima notifikasi?</button></h2>
						<div id="faqCollapse5" class="accordion-collapse collapse" aria-labelledby="faq5" data-bs-parent="#faqAccordion">
							<div class="accordion-body">Isi konstanta berikut di <strong>Settings &gt; Config</strong>: <code>WA_BAS_TARGET</code>, <code>WA_BHT_TARGET</code>, <code>WA_KINERJA_TARGET</code>, <code>WA_PRESENSI_TARGET</code>, <code>WA_SIDANG_TARGET</code>.</div>
						</div>
					</div>
					<div class="accordion-item">
						<h2 class="accordion-header" id="faq6"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse6" aria-expanded="false" aria-controls="faqCollapse6">Bagaimana cara mengisi konfigurasi DialogWA?</button></h2>
						<div id="faqCollapse6" class="accordion-collapse collapse" aria-labelledby="faq6" data-bs-parent="#faqAccordion">
							<div class="accordion-body">Isi <code>DIALOGWA_API_URL</code>, <code>DIALOGWA_TOKEN</code>, dan <code>DIALOGWA_SESSION</code> di menu <strong>Settings &gt; Config</strong>. Konfigurasi ini wajib diisi agar aplikasi dapat mengirim notifikasi WhatsApp.</div>
						</div>
					</div>
					<div class="accordion-item">
						<h2 class="accordion-header" id="faq8"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse8" aria-expanded="false" aria-controls="faqCollapse8">Apa yang perlu diperiksa sebelum melakukan update aplikasi?</button></h2>
						<div id="faqCollapse8" class="accordion-collapse collapse" aria-labelledby="faq8" data-bs-parent="#faqAccordion">
							<div class="accordion-body">Sebelum melakukan update, pastikan izin direktori aplikasi sudah benar dengan menjalankan perintah berikut pada server:<br><code>chown apache:apache -R /var/www/html/siaga</code><br>Pastikan juga telah membaca changelog dan membuat cadangan data jika diperlukan.</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Toast -->
<div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3">
	<div id="globalToast" class="toast opacity-0" role="alert" aria-live="assertive" aria-atomic="true" style="--bs-bg-opacity: 0.6;">
		<div id="toastHeader" class="toast-header">
			<!-- <img src="..." class="rounded me-2" alt="..."> -->
			<strong id="toastTitle" class="me-auto"></strong>
			<!-- <small class="text-muted">11 mins ago</small> -->
		</div>
		<div id="toastBodyContainer" class="d-flex justify-content-center">
			<div id="toastBody" class="toast-body text-center w-100"></div>
		</div>
	</div>
</div>

<script src="<?php echo asset_url('assets/plugins/js-cookie/js.cookie.min.js') ?>"></script>

<?php if ($useDatepicker): ?>
	<!-- bootstrap datepicker -->
	<script src="<?php echo asset_url('assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.js') ?>" defer></script>
<?php endif; ?>

<?php if ($legacyFallback): ?>
	<script src="<?php echo asset_url('assets/plugins/jquery-history/jquery.history.min.js') ?>" defer></script>
<?php endif; ?>

<script>
	window.ASSET_URL_PREFIX = "<?php echo rtrim(asset_url(''), '/') . '/'; ?>";
</script>
<script src="<?php echo asset_url('assets/js/main.js'); ?>"></script>
<script src="<?php echo asset_url('assets/js/color.js'); ?>"></script>

<script src="<?php echo asset_url('assets/plugins/popperjs/popper.min.js') ?>"></script>
<script src="<?php echo asset_url('assets/plugins/bootstrap/bootstrap.min.js') ?>"></script>
<script src="<?php echo asset_url('assets/plugins/adminlte4/dist/js/adminlte.js') ?>"></script>

<script src="<?php echo asset_url('assets/plugins/select2/select2.min.js') ?>"></script>

<?php if ($useKnob): ?>
	<script src="<?php echo asset_url('assets/plugins/jquery-knob/jquery.knob.min.js') ?>" defer></script>
<?php endif; ?>

<?php if ($useViewer): ?>
	<!-- Viewer.js -->
	<link rel="stylesheet" href="<?php echo asset_url('assets/plugins/viewerjs/viewer.min.css') ?>">
	<script src="<?php echo asset_url('assets/plugins/viewerjs/viewer.min.js') ?>" defer></script>
<?php endif; ?>

<?php if ($useDaterange): ?>
	<!-- Daterangepicker -->
	<link rel="stylesheet" href="<?php echo asset_url('assets/plugins/daterange/daterangepicker.css') ?>">
	<script src="<?php echo asset_url('assets/plugins/daterange/daterangepicker.js') ?>" defer></script>
<?php endif; ?>

<?php if ($useDataTables): ?>
	<!-- DataTables -->
	<link rel="stylesheet" href="<?php echo asset_url('assets/plugins/datatables/2.1.2/dataTables.dataTables.css') ?>">
	<script src="<?php echo asset_url('assets/plugins/datatables/init.js') ?>" defer></script>
	<script src="<?php echo asset_url('assets/plugins/datatables/2.1.2/dataTables.js') ?>" defer></script>

	<!-- DataTables Plugins -->
	<link rel="stylesheet" href="<?php echo asset_url('assets/plugins/datatables/dataTables.dataTables.plugins.css') ?>">
	<script src="<?php echo asset_url('assets/plugins/datatables/dataTables.plugins.min.js') ?>" defer></script>
<?php endif; ?>

<?php if ($useExport): ?>
	<script src="<?php echo asset_url('assets/plugins/jszip/jszip.min.js') ?>" defer></script>
	<script src="<?php echo asset_url('assets/plugins/pdfmake/pdfmake.min.js') ?>" defer></script>
	<script src="<?php echo asset_url('assets/plugins/pdfmake/vfs_fonts.js') ?>" defer></script>
<?php endif; ?>

<?php
// Prepare toast data before opening the script block to avoid nested <script> tags.
$toastData = null;
if ($this->session->flashdata('toast')) {
	$toastData = $this->session->flashdata('toast');
	$this->session->unset_userdata('toast');
}
?>

<script type='text/javascript'>
	var languageUrl = "<?php echo asset_url('assets/plugins/datatables/Indonesian.json') ?>";
	var hideLoader = true;

	localStorage.setItem('csrfName', '<?php echo $this->security->get_csrf_token_name() ?>');
	localStorage.setItem('csrfToken', '<?php echo $this->security->get_csrf_hash() ?>');
	localStorage.setItem('isLoggedin', '<?php echo $this->ion_auth->logged_in() ? 1 : 0 ?>');

	<?php if ($toastData): ?>
		document.addEventListener('DOMContentLoaded', function() {
			showToast(
				<?php echo json_encode(isset($toastData['message']) ? $toastData['message'] : '... message belum diset ...') ?>,
				<?php echo json_encode(isset($toastData['title'])    ? $toastData['title']   : '') ?>,
				<?php echo json_encode(isset($toastData['type'])     ? $toastData['type']    : 'bg-primary') ?>,
				<?php echo json_encode(isset($toastData['autohide']) ? $toastData['autohide'] : false) ?>,
				<?php echo json_encode(isset($toastData['delay'])    ? $toastData['delay']   : 5000) ?>
			);
		});
	<?php endif; ?>

	$(document).ready(function($) {
		/* ── Gateway check ────────────────────────────────────────────── */
		const gatewayUrl = '<?php echo base_url('site/check_gateway') ?>';
		$('#summary-info').html('');
		checkGateway(gatewayUrl);

		/* ── Auto-refresh ─────────────────────────────────────────────── */
		intervalRefresh = <?php echo isset($refresh_interval) ? $refresh_interval : ($this->config->item('interval_refresh_list_antrian') ?: 10000) ?>;
		enableRefresh = '<?php echo $this->enableRefresh ?>' == 1;

		if (enableRefresh) {
			const worker = new Worker('<?php echo asset_url('assets/js/worker.js') ?>');
			worker.postMessage(intervalRefresh);
			worker.onmessage = function() {
				if (!$('#modal-input.show').length) {
					callAjax({
						url: window.location.href,
						showBreadcrumb: true,
						showLoadingBar: false
					});
				}
			};
		}

		/* ── Datepicker ───────────────────────────────────────────────── */
		if ($.fn && $.fn.datepicker) {
			$('#datepicker').datepicker({
				format: 'yyyy',
				viewMode: 'years',
				minViewMode: 'years',
				autoclose: true,
				clearBtn: true,
				todayBtn: 'linked',
				todayHighlight: true,
			});
		}

		/* ── FAB (Floating Action Button) ─────────────────────────────── */
		const $fabGroups = {
			main: {
				$btn: $('#fabMain'),
				$opts: $('#fabOptions')
			},
		rekap: {
			$btn: $('#fabRekapMain'),
			$opts: $('#fabRekapOptions')
		},
		settings: {
			$btn: $('#fabSettingsMain'),
			$opts: $('#fabSettingsOptions')
		},
	};

		/**
		 * Open one FAB group and close all others.
		 * Passing null closes every group.
		 */
		function openFab(activeKey) {
			$.each($fabGroups, function(key, group) {
				const isActive = key === activeKey;
				group.$btn.toggleClass('active', isActive);
				group.$opts.toggleClass('active', isActive);
			});
		}

		$.each($fabGroups, function(key, group) {
			group.$btn.on('click', function(e) {
				e.stopPropagation();
				// If already open, clicking again closes everything.
				const alreadyOpen = group.$btn.hasClass('active');
				openFab(alreadyOpen ? null : key);
			});
		});

		// Close all FABs when clicking outside any fab-container.
		$(document).on('click', function(e) {
			if (!$(e.target).closest('.fab-container').length) {
				openFab(null);
			}
		});

		// Close all FABs when any option is clicked.
		$(document).on('click', '.fab-option', function() {
			openFab(null);
		});

		$('#fabFaq').on('click', function(e) {
			e.preventDefault();
			$('#faqModal').modal('show');
		});
	});

	/* ── Helpers ──────────────────────────────────────────────────────── */
	function callPrint(url) {
		busyShow();
		$.ajax({
			url: url,
			success: function(data) {
				busyHide();
				if (data.msg) {
					if (data.st == 1) printTicket(data.print_data);
					showToast(data.msg, '', (data.st == 1 || data.st === undefined) ? 'bg-primary' : 'bg-danger');
				}
				if (data.redirect) {
					callAjax({
						url: data.redirect,
						showBreadcrumb: false,
						showLoadingBar: false
					});
				} else {
					if (data.content && data.refresh == 1) $('.container-main').html(data.content);
					if (data.control) $('.antrian-control').html(data.control);
				}
			},
			error: function() {
				busyHide();
				$('#modal-input').modal('hide');
				alert('Terjadi Kesalahan');
			}
		});
	}

	function checkGateway(url) {
		fetch(url)
			.then(function(response) {
				if (!response.ok) throw new Error('Network response was not ok: ' + response.statusText);
				return response.text(); // ← read as text first
			})
			.then(function(text) {
				if (!text || !text.trim()) {
					throw new Error('Empty response from server');
				}
				let data;
				try {
					data = JSON.parse(text); // ← then parse safely
				} catch (e) {
					throw new Error('Invalid JSON response: ' + text.substring(0, 100));
				}

				if (!data.status) {
					$('#summary-info').html('');
					$('.footer-right').html(makeCallout('danger', 'Sesi DialogWA tidak terhubung'));
					return;
				}

				const result = JSON.parse(data.response);

				if (!result.name) {
					$('#summary-info').html('');
					$('.footer-right').html(makeCallout('danger', result.message));
					return;
				}

				const healthy = !result.is_expired && !result.is_out_of_limit && result.status;
				if (healthy) $('.btn-notification').removeClass('disabled');

				const fmt = new Intl.NumberFormat('id-ID');
				const expiry = window.moment ?
					moment(result.expires).format('dddd, Do MMMM YYYY h:mm') :
					new Date(result.expires).toLocaleString('id-ID', {
						weekday: 'long',
						day: '2-digit',
						month: 'long',
						year: 'numeric',
						hour: '2-digit',
						minute: '2-digit',
					});

				$('#summary-info').html(`
					<div class="alert alert-primary py-4" role="alert">
						<h5>Pesan Terkirim</h5>
						<div class="align-items-center d-flex flex-wrap gap-2">
							${summaryBadge('Hari ini',        result.summary.today,      'success')}
							${summaryBadge('Kemarin',         result.summary.yesterday,   'secondary')}
							${summaryBadge('Minggu ini',      result.summary.cur_week,    'success')}
							${summaryBadge('Minggu Kemarin',  result.summary.prev_week,   'secondary')}
							${summaryBadge('Bulan ini',       result.summary.cur_month,   'success')}
							${summaryBadge('Bulan Kemarin',   result.summary.prev_month,  'secondary')}
							${summaryBadge('Semua',           result.summary.all,         'success')}
						</div>
					</div>`);

				$('.footer-right').html(makeCallout(
					healthy ? 'success' : 'danger',
					`${result.name} (${result.number}) | ` +
					`${result.status ? 'Aktif' : 'Tidak Aktif'} | ` +
					`Limit Pesan: ${fmt.format(result.limit_message)} | ` +
					`Expired: ${expiry}`
				));
			})
			.catch(function(error) {
				console.error('Fetch error:', error);
				$('#summary-info').html('');
				$('.footer-right').html(makeCallout('danger', 'Tidak dapat terhubung ke server'));
			});
	}

	function makeCallout(type, html) {
		return `<div class="callout callout-${type} d-flex align-items-center mb-0 ms-4 py-1" role="alert"><small>${html}</small></div>`;
	}

	function summaryBadge(label, value, variant) {
		return `<span>${label} &nbsp;<span class="badge badge-${variant}">${value}</span></span>`;
	}
</script>