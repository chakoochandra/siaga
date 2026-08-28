<style>
	/* Modern Card Styling - Scoped to app boxes */
	#box-apps .card,
	#box-recommendation .card,
	#box-result .card {
		cursor: pointer;
		transition: transform 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
	}

	#box-apps .card::before,
	#box-recommendation .card::before,
	#box-result .card::before {
		content: '';
		position: absolute;
		inset: 0;
		background: linear-gradient(135deg, transparent 60%, var(--card-glow, rgba(99, 179, 237, 0.04)) 100%);
		opacity: 0;
		transition: opacity 0.3s;
	}

	#box-apps .card:hover,
	#box-recommendation .card:hover,
	#box-result .card:hover {
		transform: translateY(-3px);
		border-color: var(--card-accent, var(--bs-purple));
		box-shadow: 0 8px 32px var(--card-shadow, rgba(99, 179, 237, 0.12)), 0 0 0 1px var(--card-accent, var(--bs-purple));
	}

	#box-apps .card:hover::before,
	#box-recommendation .card:hover::before,
	#box-result .card:hover::before {
		opacity: 1;
	}

	/* Card accent bar */
	#box-apps .card .card-bar,
	#box-recommendation .card .card-bar,
	#box-result .card .card-bar {
		position: absolute;
		top: 0;
		left: 0;
		right: 0;
		height: 3px;
		background: var(--card-accent, var(--bs-purple));
		opacity: 0;
		transition: opacity 0.25s;
	}

	#box-apps .card:hover .card-bar,
	#box-recommendation .card:hover .card-bar,
	#box-result .card:hover .card-bar {
		opacity: 1;
	}

	/* Status dot */
	#box-apps .card .dot,
	#box-recommendation .card .dot,
	#box-result .card .dot {
		position: absolute;
		top: 14px;
		right: 14px;
		width: 8px;
		height: 8px;
		background: var(--card-accent, var(--bs-purple));
		animation: pulse 2s infinite;
	}

	@keyframes pulse {

		0%,
		100% {
			opacity: 1;
			transform: scale(1);
		}

		50% {
			opacity: 0.4;
			transform: scale(0.95);
		}
	}

	/* Card icon */
	#box-apps .card .card-icon,
	#box-recommendation .card .card-icon,
	#box-result .card .card-icon {
		width: 44px;
		height: 44px;
		background: var(--card-icon-bg, rgba(99, 179, 237, 0.1));
		transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
		overflow: hidden;
		z-index: 3;
	}

	#box-apps .card .card-icon i,
	#box-recommendation .card .card-icon i,
	#box-result .card .card-icon i {
		font-size: 20px;
		line-height: 1;
		transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
	}

	#box-apps .card .card-icon img,
	#box-recommendation .card .card-icon img,
	#box-result .card .card-icon img {
		transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
	}

	/* Large background icon/image on hover */
	#box-apps .card .card-bg-icon,
	#box-recommendation .card .card-bg-icon,
	#box-result .card .card-bg-icon {
		position: absolute;
		inset: 0;
		z-index: 1;
		pointer-events: none;
		opacity: 1;
		transition: opacity 0.6s cubic-bezier(0.4, 0, 0.2, 1);
	}

	#box-apps .card:hover .card-bg-icon,
	#box-recommendation .card:hover .card-bg-icon,
	#box-result .card:hover .card-bg-icon {
		opacity: 0.9;
	}

	/* FontAwesome background icon */
	#box-apps .card .card-bg-icon i,
	#box-recommendation .card .card-bg-icon i,
	#box-result .card .card-bg-icon i {
		font-size: 180px;
		color: var(--card-accent, var(--bs-purple));
		opacity: 1;
		transform: scale(1) rotate(-20deg);
		transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
	}

	#box-apps .card:hover .card-bg-icon i,
	#box-recommendation .card:hover .card-bg-icon i,
	#box-result .card:hover .card-bg-icon i {
		transform: scale(1) rotate(-20deg);
		opacity: 1;
	}

	/* Image background */
	#box-apps .card .card-bg-icon img,
	#box-recommendation .card .card-bg-icon img,
	#box-result .card .card-bg-icon img {
		opacity: 1;
		transform: scale(1);
		transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
	}

	#box-apps .card:hover .card-bg-icon img,
	#box-recommendation .card:hover .card-bg-icon img,
	#box-result .card:hover .card-bg-icon img {
		opacity: 1;
		transform: scale(1);
	}

	/* Circular glow effect */
	#box-apps .card::after,
	#box-recommendation .card::after,
	#box-result .card::after {
		content: '';
		position: absolute;
		top: 50%;
		left: 50%;
		transform: translate(-50%, -50%) scale(0);
		width: 100px;
		height: 100px;
		background: var(--card-accent, var(--bs-purple));
		opacity: 0;
		transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
		z-index: 2;
	}

	#box-apps .card:hover::after,
	#box-recommendation .card:hover::after,
	#box-result .card:hover::after {
		width: 350px;
		height: 350px;
		opacity: 0.2;
		transform: translate(-50%, -50%) scale(1);
	}

	#box-apps .card:hover .card-icon,
	#box-recommendation .card:hover .card-icon,
	#box-result .card:hover .card-icon {
		transform: scale(1.15) rotate(-5deg);
		background: var(--card-accent, var(--bs-purple));
		color: #fff;
	}

	#box-apps .card:hover .card-icon i,
	#box-recommendation .card:hover .card-icon i,
	#box-result .card:hover .card-icon i {
		font-size: 24px;
	}

	/* Card content */
	#box-apps .card .card-desc,
	#box-recommendation .card .card-desc,
	#box-result .card .card-desc {
		font-size: 15px;
	}

	/* Category badge (shown when apps are merged across categories) */
	#box-apps .card .card-category-badge {
		display: inline-block;
		align-self: flex-start;
		width: fit-content;
		font-size: 12px;
		font-family: 'DM Mono', monospace;
		text-transform: uppercase;
		letter-spacing: 0.4px;
		font-weight: 700;
		line-height: 1.4;
		padding: 3px 9px;
		border-radius: 999px;
		background: var(--card-icon-bg, rgba(99, 179, 237, 0.1));
		color: var(--card-accent, var(--bs-purple));
	}

	/* Color variants */
	#box-apps .card.c-blue,
	#box-recommendation .card.c-blue,
	#box-result .card.c-blue {
		--card-accent: #63b3ed;
		--card-icon-bg: rgba(99, 179, 237, 0.1);
		--card-glow: rgba(99, 179, 237, 0.08);
		--card-shadow: rgba(99, 179, 237, 0.15);
	}

	#box-apps .card.c-green,
	#box-recommendation .card.c-green,
	#box-result .card.c-green {
		--card-accent: #68d391;
		--card-icon-bg: rgba(104, 211, 145, 0.1);
		--card-glow: rgba(104, 211, 145, 0.08);
		--card-shadow: rgba(104, 211, 145, 0.15);
	}

	#box-apps .card.c-amber,
	#box-recommendation .card.c-amber,
	#box-result .card.c-amber {
		--card-accent: #f6ad55;
		--card-icon-bg: rgba(246, 173, 85, 0.1);
		--card-glow: rgba(246, 173, 85, 0.08);
		--card-shadow: rgba(246, 173, 85, 0.15);
	}

	#box-apps .card.c-red,
	#box-recommendation .card.c-red,
	#box-result .card.c-red {
		--card-accent: #fc8181;
		--card-icon-bg: rgba(252, 129, 129, 0.1);
		--card-glow: rgba(252, 129, 129, 0.08);
		--card-shadow: rgba(252, 129, 129, 0.15);
	}

	#box-apps .card.c-purple,
	#box-recommendation .card.c-purple,
	#box-result .card.c-purple {
		--card-accent: #b794f4;
		--card-icon-bg: rgba(183, 148, 244, 0.1);
		--card-glow: rgba(183, 148, 244, 0.08);
		--card-shadow: rgba(183, 148, 244, 0.15);
	}

	#box-apps .card.c-pink,
	#box-recommendation .card.c-pink,
	#box-result .card.c-pink {
		--card-accent: #f687b3;
		--card-icon-bg: rgba(246, 135, 179, 0.1);
		--card-glow: rgba(246, 135, 179, 0.08);
		--card-shadow: rgba(246, 135, 179, 0.15);
	}

	#box-apps .card.c-teal,
	#box-recommendation .card.c-teal,
	#box-result .card.c-teal {
		--card-accent: #4fd1c5;
		--card-icon-bg: rgba(79, 209, 197, 0.1);
		--card-glow: rgba(79, 209, 197, 0.08);
		--card-shadow: rgba(79, 209, 197, 0.15);
	}

	#box-apps .card.c-orange,
	#box-recommendation .card.c-orange,
	#box-result .card.c-orange {
		--card-accent: #f6ad55;
		--card-icon-bg: rgba(246, 173, 85, 0.1);
		--card-glow: rgba(246, 173, 85, 0.08);
		--card-shadow: rgba(246, 173, 85, 0.15);
	}

	#box-apps .my-apps .col,
	#box-recommendation .my-apps .col,
	#box-result .my-apps .col {
		box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
		transition: box-shadow 0.25s ease, transform 0.25s ease;
	}

	#box-apps .my-apps .col:hover,
	#box-recommendation .my-apps .col:hover,
	#box-result .my-apps .col:hover {
		box-shadow: 0 8px 24px rgba(0, 0, 0, 0.10);
	}

	.input-wrap {
		max-width: 100%;
	}

	@media (max-width: 575.98px) {
		.input-wrap {
			max-width: 92%;
		}
	}

	#box-logo .brand-link {
		max-width: 100%;
		flex-wrap: nowrap;
	}

	#box-logo .brand-link .logo-img {
		max-width: 100%;
	}

	#box-logo p {
		font-size: clamp(0.9rem, 2.5vw, 1.3rem);
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
	}

	.fixed-bottom-right {
		position: fixed;
		bottom: 50px;
		right: 10px;
		z-index: 9999;
		display: none;
		width: 40px;
		height: 40px;
		padding: 6px;
		background: rgba(255, 255, 255, 0.9);
		border-radius: 50%;
		box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
		text-align: center;
		line-height: 28px;
		transition: opacity 0.3s, visibility 0.3s;
	}

	.fixed-bottom-right.visible {
		display: block;
	}

	.figure-img {
		border-top-left-radius: 1.5rem !important;
	}

	/* Custom default button */
	.btn-secondary,
	.btn-secondary:hover,
	.btn-secondary:focus {
		color: #333;
		text-shadow: none;
	}

	.nav-masthead .nav-link:hover,
	.nav-masthead .nav-link:focus {
		border-bottom-color: rgba(0, 0, 0, .25);
	}

	.text-bg-dark .nav-masthead .nav-link:hover,
	.text-bg-dark .nav-masthead .nav-link:focus {
		border-bottom-color: rgba(255, 255, 255, .25);
	}

	/* z-index: foreground content above bg layer */
	#box-apps .card .card-bar,
	#box-recommendation .card .card-bar,
	#box-result .card .card-bar,
	#box-apps .card .dot,
	#box-recommendation .card .dot,
	#box-result .card .dot,
	#box-apps .card .card-icon,
	#box-recommendation .card .card-icon,
	#box-result .card .card-icon,
	#box-apps .card .card-content,
	#box-recommendation .card .card-content,
	#box-result .card .card-content {
		position: relative;
		z-index: 3;
	}

	/* More visible bg image */
	#box-apps .card .card-bg-icon img,
	#box-recommendation .card .card-bg-icon img,
	#box-result .card .card-bg-icon img {
		opacity: 0.95 !important;
		filter: blur(0.5px) saturate(0.7);
	}

	#box-apps .card:hover .card-bg-icon img,
	#box-recommendation .card:hover .card-bg-icon img,
	#box-result .card:hover .card-bg-icon img {
		opacity: 0.6 !important;
	}

	/* Dim bg FA icon */
	#box-apps .card .card-bg-icon i,
	#box-recommendation .card .card-bg-icon i,
	#box-result .card .card-bg-icon i {
		opacity: 0.08 !important;
	}

	#box-apps .card:hover .card-bg-icon i,
	#box-recommendation .card:hover .card-bg-icon i,
	#box-result .card:hover .card-bg-icon i {
		opacity: 0.12 !important;
	}

	/* Stronger scrim to keep text readable despite more visible image */
	#box-apps .card .card-bg-icon::after,
	#box-recommendation .card .card-bg-icon::after,
	#box-result .card .card-bg-icon::after {
		content: '';
		position: absolute;
		inset: 0;
		background: var(--bs-body-bg, #fff);
		opacity: 0.45;
		pointer-events: none;
	}

	/* Text contrast */
	#box-apps .card .card-title,
	#box-recommendation .card .card-title,
	#box-result .card .card-title {
		color: var(--bs-body-color);
		text-shadow: 0 1px 6px rgba(0, 0, 0, 0.5), 0 0 2px rgba(0, 0, 0, 0.3);
	}

	#box-apps .card .card-desc,
	#box-recommendation .card .card-desc,
	#box-result .card .card-desc {
		text-shadow: 0 1px 4px rgba(0, 0, 0, 0.4);
	}

	/* ===== Linktr.ee-style app cards on mobile ===== */
	@media (max-width: 575.98px) {

		#box-apps .my-apps,
		#box-recommendation .my-apps,
		#box-result .my-apps {
			row-gap: 0.85rem !important;
		}

		#box-apps .card,
		#box-recommendation .card,
		#box-result .card {
			flex-direction: row !important;
			align-items: center !important;
			gap: 0.85rem !important;
			padding: 0.5rem 0.5rem !important;
			border-radius: 1rem !important;
			background: var(--bs-secondary-bg, #e2e8f0) !important;
			box-shadow: 0 1px 4px rgba(0, 0, 0, 0.12);
			border-color: transparent !important;
		}

		#box-apps .card:hover,
		#box-recommendation .card:hover,
		#box-result .card:hover {
			transform: none !important;
			box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1), 0 0 0 1px var(--card-accent, var(--bs-purple)) !important;
		}

		/* drop the decorative big bg icon, accent bar and status dot */
		#box-apps .card .card-bg-icon,
		#box-recommendation .card .card-bg-icon,
		#box-result .card .card-bg-icon,
		#box-apps .card .card-bar,
		#box-recommendation .card .card-bar,
		#box-result .card .card-bar,
		#box-apps .card .dot,
		#box-recommendation .card .dot,
		#box-result .card .dot {
			display: none !important;
		}

		/* small round icon on the left, like a linktr.ee avatar */
		#box-apps .card .card-icon,
		#box-recommendation .card .card-icon,
		#box-result .card .card-icon {
			width: 50px !important;
			height: 50px !important;
			border-radius: 50% !important;
			flex-shrink: 0 !important;
			margin: 0 !important;
			overflow: hidden !important;
		}

		#box-apps .card .card-icon i,
		#box-recommendation .card .card-icon i,
		#box-result .card .card-icon i {
			font-size: 17px !important;
		}

		/* centered single-line title, description hidden for a clean link-button look */
		#box-apps .card .card-content,
		#box-recommendation .card .card-content,
		#box-result .card .card-content {
			flex: 1 1 auto !important;
			text-align: center !important;
			gap: 0 !important;
			min-width: 0;
		}

		#box-apps .card .card-title,
		#box-recommendation .card .card-title,
		#box-result .card .card-title {
			font-size: 0.95rem !important;
			white-space: nowrap;
			overflow: hidden;
			text-overflow: ellipsis;
		}

		#box-apps .card .card-desc,
		#box-recommendation .card .card-desc,
		#box-result .card .card-desc {
			display: none !important;
		}
	}
</style>



<?php
$appShortName = is_local_ip() ? APP_SHORT_NAME : 'DELTA';
$appName = is_local_ip() ? APP_NAME : 'DERETAN APLIKASI LAYANAN TERINTEGRASI';
$appLogo = is_local_ip() ? asset_url('assets/images/joss.png') : asset_url('assets/images/delta.png');
?>

<section>
	<div class="card-body py-0">
		<div class="d-flex justify-content-center mx-auto p-4">
			<div id="box-logo-login" class="row row-cols-1 <?php if ($this->ion_auth->logged_in() && is_local_ip()): ?>row-cols-sm-1 <?php else: ?> row-cols-sm-2<?php endif; ?> justify-content-center g-3 mt-2 w-100">
				<div id="box-logo" class="col<?php if ($this->ion_auth->logged_in() && is_local_ip()): ?> col-sm-8<?php endif; ?> d-flex flex-column align-items-center justify-content-center mt-4 text-center">
					<div>
						<a href="<?php echo base_url('/') ?>" class="brand-link d-flex flex-nowrap justify-content-center align-items-center gap-2">
							<?php if ($appLogo): ?>
								<img src="<?php echo $appLogo ?>" alt="Logo <?php echo $appShortName ?>" class="logo-img" style="height: calc(2rem + 4.5vw);">
							<?php endif; ?>
							<?php if ($appShortName): ?>
								<span class="display-2 fw-bold m-0 font-saved-by-zero"><?php echo $appShortName ?></span>
							<?php endif; ?>
						</a>
						<?php if ($appName): ?>
							<p class="text-center fw-bold h5 m-0 mt-2"><?php echo $appName ?></p>
						<?php endif; ?>
					</div>
					<div class="text-center p-2">
						<?php foreach ($apps as $category => $socmed) : ?>
							<?php if ($category == 'Socmed') : ?>
								<?php foreach ($socmed as $s) : ?>
									<?php if ($s[2]) : ?>
										<a href="<?php echo $s[1] ?>" rel="noopener noreferrer"><img src="<?php echo $s[2] ?>" loading="lazy" class="transform-scale" alt="<?php echo $s[0] ?>" width="<?php echo $s[3] ?: 30 ?>" height="<?php echo $s[4] ?: 30 ?>"></a>
									<?php else : ?>
										<a href="<?php echo $s[1] ?>" rel="noopener noreferrer"><?php echo $s[0] ?></a>
									<?php endif ?>
								<?php endforeach ?>
							<?php endif ?>
						<?php endforeach ?>
					</div>
				</div>
				<!-- <php if (!$this->ion_auth->logged_in() && is_local_ip()): ?>
					<php $this->load->view('site/_login_form') ?>
				<php endif ?> -->
			</div>
		</div>

		<div class="input-wrap d-flex col-lg-6 col-md-8 mx-auto mb-3">
			<span class="input-icon">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
					<circle cx="11" cy="11" r="8" />
					<line x1="21" y1="21" x2="16.65" y2="16.65" />
				</svg>
			</span>
			<input type="text" id="textfield-search" class="form-control form-input" placeholder="Cari aplikasi" aria-label="Cari aplikasi" aria-describedby="button-addon2" style="background: rgba(var(--bs-body-bg),.1); padding-left: 2.75rem;">
			<button class="btn btn-xs btn-clear icon-end inner-btn text-red m-0 collapse" onClick="$('#textfield-search').val('').trigger('input');"><i class="fa-solid fa-xmark"></i></button>
		</div>

		<?php if (is_local_ip()): ?>
			<div id="box-ratio">
				<div class="container-ratio d-flex flex-row justify-content-center">
					<?php $this->load->view('site/_ratio') ?>
				</div>
			</div>
		<?php endif ?>
	</div>

	<div class="card-body album pt-0">
		<div id="box-result" class="collapse"></div>

		<div id="box-recommendation" class="collapse"></div>

		<div id="box-apps" class="collapse"></div>
	</div>
</section>

<a href="#" class="fixed-bottom-right"><img src="<?php echo asset_url('assets/images/arrow_up.svg') ?>" width="30" height="30" alt="Kembali ke atas" /></a>

<script>
	var is_local_ip = '<?php echo is_local_ip() ?>' == true;
	if (is_local_ip) {
		$(document).ready(function() {

			function loadRatio() {
				hideLoader = true;
				$('.badge-number').html('<i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i>');
				loadPartial('<?php echo base_url('site/get_ratio') ?>', '.container-ratio');
			}

			loadRatio();
		});
	}

	var numOfRecommendation = 4;
	var allApps = [];

	function sortFunction(a, b) {
		if (a[4] === b[4]) {
			return 0;
		} else {
			return (a[4] > b[4]) ? -1 : 1;
		}
	}

	function getPlaceholderImg(placeholder) {
		return 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 300" preserveAspectRatio="none">
            <defs>
                <style type="text/css">
                    #holder_190d4a343a8 text { fill:rgba(255,255,255,.75);font-weight:normal;font-family:Helvetica, monospace;font-size:20pt }
                </style>
            </defs>
            <g id="holder_190d4a343a8">
                <rect width="100%" height="100%" fill="#777"></rect>
                <g>
                    <text x="50%" y="50%" text-anchor="middle" dominant-baseline="middle">` + placeholder + `</text>
                </g>
            </g>
        </svg>`);
	}

	function getColorClass(name) {
		var colors = ['c-blue', 'c-green', 'c-amber', 'c-red', 'c-purple', 'c-pink', 'c-teal'];
		var sum = 0;
		for (var i = 0; i < name.length; i++) {
			sum += name.charCodeAt(i);
		}
		return colors[sum % colors.length];
	}

	function getIconForApp(appName) {
		var icons = [
			'fa-solid fa-folder',
			'fa-solid fa-file',
			'fa-solid fa-box',
			'fa-solid fa-cube',
			'fa-solid fa-layer-group',
			'fa-solid fa-shapes'
		];
		var sum = 0;
		for (var i = 0; i < appName.length; i++) {
			sum += appName.charCodeAt(i);
		}
		return icons[sum % icons.length];
	}

	function getIconHTML(iconUrl, appName) {
		if (iconUrl && (iconUrl.includes('.png') || iconUrl.includes('.jpg') || iconUrl.includes('.jpeg') || iconUrl.includes('.svg'))) {
			var fallbackIcon = getIconForApp(appName);
			return {
				type: 'image',
				html: '<i class="' + fallbackIcon + '"></i>',
				bgHtml: '<img src="' + iconUrl + '" loading="lazy" alt="' + appName + '" class="w-100 h-100 object-fit-cover" style="opacity:1;">'
			};
		} else if (iconUrl && typeof iconUrl === 'string' && iconUrl.includes('fa-')) {
			return {
				type: 'icon',
				html: '<i class="' + iconUrl + '"></i>',
				bgHtml: '<i class="' + iconUrl + '"></i>'
			};
		} else {
			var fallbackIcon = getIconForApp(appName);
			return {
				type: 'icon',
				html: '<i class="' + fallbackIcon + '"></i>',
				bgHtml: '<i class="' + fallbackIcon + '"></i>'
			};
		}
	}

	function showApps(target, data, category, count = null, isReset = false) {
		if (isReset) {
			$(target).html('');
		}

		var elId = category.replace(/[^a-zA-Z0-9]/g, '').toLowerCase();

		$(target).append('<div id="' + elId + '" class="leaves border my-1 p-3"></div>');
		$(target).find('#' + elId).append('<span class="category h4">' + category + '</span>');
		$(target).find('#' + elId).append('<div class="my-apps row row-cols-1 row-cols-sm-2 row-cols-md-4 g-3 mt-2"></div>');

		if (data.length > 0) {
			$.each(count ? data.slice(0, count) : data, function(index, value) {
				// Only add to allApps from regular category rendering (not from Rekomendasi/Hasil Pencarian)
				if (!['Rekomendasi', 'Hasil Pencarian'].includes(category)) {
					allApps.push(value);
				}

				var iconData = getIconHTML(value[2], value[0]);

				$(target).find('#' + elId).find('.my-apps').append(
					'<div class="col mt-0">' +
					' <a href="' + value[1] + '" target="_blank" style="text-decoration:none;color:inherit;">' +
					'   <div class="card position-relative overflow-hidden border rounded-4 p-3 h-100 d-flex flex-column gap-3 ' + getColorClass(value[0]) + '">' +
					'     <div class="card-bar rounded-top-4"></div>' +
					(value[4] ? '     <div class="dot rounded-circle"></div>' : '') +
					'     <div class="card-bg-icon d-flex align-items-center justify-content-center overflow-hidden">' + iconData.bgHtml + '</div>' +
					'     <div class="card-icon d-flex align-items-center justify-content-center flex-shrink-0 rounded-3 position-relative">' + iconData.bgHtml + '</div>' +
					'     <div class="card-content flex-fill d-flex flex-column gap-2">' +
					'       <div class="card-title fs-5 fw-semibold lh-sm text-start">' + value[0] + '</div>' +
					(value[5] ? '       <div class="card-desc lh-base">' + value[5] + '</div>' : '') +
					'     </div>' +
					'   </div>' +
					' </a>' +
					'</div>'
				);
			});
		} else {
			$(target).find('#' + elId).find('.my-apps').append('<span class="text-muted m-0"><i class="fa-solid fa-magnifying-glass text-danger" aria-hidden="true"></i> Web tidak ditemukan</span>');
		}

		$(target).show();
	}

	function showFlatApps(target, items) {
		$(target).html('');
		$(target).append('<div id="all-apps" class="leaves border my-1 p-3"></div>');
		$(target).find('#all-apps').append('<div class="my-apps row row-cols-1 row-cols-sm-2 row-cols-md-4 g-3 mt-2"></div>');

		if (items.length > 0) {
			$.each(items, function(index, entry) {
				var value = entry.value;
				var category = entry.category;

				allApps.push(value);

				var iconData = getIconHTML(value[2], value[0]);

				$(target).find('#all-apps').find('.my-apps').append(
					'<div class="col mt-0">' +
					' <a href="' + value[1] + '" target="_blank" style="text-decoration:none;color:inherit;">' +
					'   <div class="card position-relative overflow-hidden border rounded-4 p-3 h-100 d-flex flex-column gap-3 ' + getColorClass(value[0]) + '">' +
					'     <div class="card-bar rounded-top-4"></div>' +
					(value[4] ? '     <div class="dot rounded-circle"></div>' : '') +
					'     <div class="card-bg-icon d-flex align-items-center justify-content-center overflow-hidden">' + iconData.bgHtml + '</div>' +
					'     <div class="card-icon d-flex align-items-center justify-content-center flex-shrink-0 rounded-3 position-relative">' + iconData.bgHtml + '</div>' +
					'     <div class="card-content flex-fill d-flex flex-column gap-2">' +
					'       <div class="card-title fs-5 fw-semibold lh-sm text-start">' + value[0] + '</div>' +
					'       <span class="card-category-badge">' + category + '</span>' +
					(value[5] ? '       <div class="card-desc lh-base">' + value[5] + '</div>' : '') +
					'     </div>' +
					'   </div>' +
					' </a>' +
					'</div>'
				);
			});
		} else {
			$(target).find('#all-apps').find('.my-apps').append('<span class="text-muted m-0"><i class="fa-solid fa-magnifying-glass text-danger" aria-hidden="true"></i> Web tidak ditemukan</span>');
		}

		$(target).show();
	}

	function getUniqueApps(arr) {
		var uniques = [];
		var itemsFound = {};
		for (var i = 0, l = arr.length; i < l; i++) {
			var temp = [arr[i][0], arr[i][1], arr[i][2]];
			var stringified = JSON.stringify(temp);
			if (itemsFound[stringified]) {
				continue;
			}
			uniques.push(arr[i]);
			itemsFound[stringified] = true;
		}
		return uniques;
	}

	var mycookie;
	if (!(mycookie = localStorage.getItem('joss-favs1'))) {
		var my_recommendation = [];
		localStorage.setItem('joss-favs1', JSON.stringify(my_recommendation));
	} else {
		var my_recommendation = JSON.parse(mycookie);
	}

	// Populate recommendations
	var classes = <?php echo json_encode($classes) ?>;
	if (my_recommendation.length > 0)
		showApps('#box-recommendation', my_recommendation, 'Rekomendasi', numOfRecommendation);

	// Populate all apps — merged into a single flat grid, in the exact global order
	// (order nulls-last, then name) returned by the backend — category no longer affects sort order.
	// Each card still carries its own category as a badge. 'Menu' category is skipped so it doesn't show in box-apps
	var mergedAppsList = [];
	$.each(<?php echo json_encode($appsFlat) ?>, function(index, value) {
		var category = value[3];

		if (category == 'Lokal') {
			if (!is_local_ip) {
				return;
			}
		} else if (category == 'Socmed' || category == 'Menu') {
			return;
		}

		mergedAppsList.push({
			value: value,
			category: category
		});
	});
	showFlatApps('#box-apps', mergedAppsList);

	// Add 'Menu' apps directly to allApps (once) so they appear in search results
	// but are NOT rendered in box-apps
	var allAppsData = <?php echo json_encode($apps) ?>;
	if (allAppsData['Menu']) {
		$.each(allAppsData['Menu'], function(index, value) {
			allApps.push(value);
		});
	}

	// Logic on card click
	$('body').on('click', '.card', function(e) {
		var isExistBefore = false;
		var clickedIndex = null;

		var clickedWebName = $(this).find('.card-title').text();
		var clickedWebUrl = $(this).parent('a').attr('href');
		var clickedWebIcon = $(this).find('.card-icon img').length ? $(this).find('.card-icon img').attr('src') : $(this).find('.card-icon i').attr('class');

		my_recommendation.sort(sortFunction);

		my_recommendation.forEach((item, index) => {
			if (item[0] == clickedWebName) {
				isExistBefore = true;
				clickedIndex = index;
				my_recommendation[index][4] += 1;
			}
		});

		if (!isExistBefore) {
			my_recommendation.unshift([
				clickedWebName,
				clickedWebUrl,
				clickedWebIcon,
				'',
				1,
			]);
		} else {
			my_recommendation.unshift(my_recommendation.splice(clickedIndex, 1)[0]);
		}

		localStorage.setItem('joss-favs1', JSON.stringify(my_recommendation));

		showApps('#box-recommendation', my_recommendation, 'Rekomendasi', numOfRecommendation, true);
	});

	$('#textfield-search').focus();

	// Search logic
	$('#textfield-search').on('input', function(e) {
		if ($(this).val()) {
			var filterApps = [];
			var keyword = $(this).val();
			$.each(getUniqueApps(allApps), function(index, item) {
				var appName = item[0] || '';
				var appUrl = item[1] || '';
				var appTags = item[3] || '';
				if (appName.toLowerCase().includes(keyword.toLowerCase()) || appTags.toLowerCase().includes(keyword.toLowerCase()) || appUrl.toLowerCase().includes(keyword.toLowerCase())) {
					filterApps.push(item);
				}
			});

			showApps('#box-result', filterApps, 'Hasil Pencarian', null, true);

			$('.btn-clear').show();
			$('#box-result').slideDown();
			$('#box-ratio').slideUp();
		} else {
			$('.btn-clear').hide();
			$('#box-result').slideUp();
			$('#box-ratio').slideDown();
		}
	});

	var $backToTop = $('.fixed-bottom-right');
	if ($backToTop.length) {
		$(window).on('scroll', function() {
			if ($(this).scrollTop() > 300) {
				$backToTop.addClass('visible');
			} else {
				$backToTop.removeClass('visible');
			}
		});

		$backToTop.on('click', function(e) {
			e.preventDefault();
			$('html, body').animate({
				scrollTop: 0
			}, 300);
		});
	}
</script>