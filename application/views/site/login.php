<div class="login-page login-box">
	<div id="box-logo" class="col<?php if ($this->ion_auth->logged_in()): ?> col-sm-8<?php endif; ?> d-flex flex-column align-items-center justify-content-center">
		<div class="text-nowrap mb-3">
			<a href="<?php echo base_url('/') ?>" class="brand-link d-flex justify-content-center">
				<img src="<?php echo asset_url('assets/images/joss.png') ?>" alt="Logo JOSS" class="logo-img" style="height: calc(2rem + 4.5vw);">
				<span class="display-2 fw-bold"><?php echo APP_SHORT_NAME ?></span>
			</a>
			<span class="text-center fw-bold h5 m-0"><?php echo APP_NAME ?></span>
		</div>
		<?php $this->load->view('site/_login_form') ?>
	</div>
</div>