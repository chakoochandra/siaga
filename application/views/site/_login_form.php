<div class="form-box">
	<div class="<?php echo $this->input->is_ajax_request() ? '' : 'card' ?> card-outline card-primary">

		<div class="card-body">
			<?php echo form_open('site/login', ['class' => 'form-ajax']) ?>

			<?php echo $message ? alert($message, 'Perhatian', ['class' => 'small alert-danger']) : '' ?>

			<div class="input-group mb-1 mw-100 d-flex flex-column">
				<label class="form-label" for="<?php echo htmlspecialchars($identity['name'], ENT_QUOTES, 'UTF-8'); ?>">
					Akun
				</label>
				<?php
				echo switch_input('form_textfield', $identity);
				?>
			</div>
			<div class="input-group mb-1 mw-100 d-flex flex-column">
				<label class="form-label" for="<?php echo htmlspecialchars($identity['name'], ENT_QUOTES, 'UTF-8'); ?>">
					Kata Sandi
				</label>
				<?php
				echo switch_input($password['type'], $password);
				?>
			</div>
			<?php if ($this->config->item('enable_captcha')) : ?>
				<div class="input-group mb-1">
					<?php echo form_captcha($captcha) ?>
				</div>
			<?php endif ?>
			<div class="row">
				<div class="col-7">
					<div class="icheck-primary">
						<?php echo form_checkbox('remember', '1', FALSE, 'id="remember"'); ?>
						<?php echo lang('login_remember_label', 'remember'); ?>
					</div>
				</div>

				<div class="col-5">
					<?php echo form_submit([
						'class' => 'btn btn-sm btn-outline-primary btn-block w-100',
					], lang('login_submit_btn')); ?>
				</div>

			</div>

			<?php echo form_close(); ?>
		</div>
	</div>
</div>

<script>
	(function() {
		function refreshCsrfToken() {
			$.ajax({
				url: "<?php echo base_url('site/login') ?>",
				type: "GET",
				dataType: "json",
				success: function() {
					// CSRF token refreshed via ajaxSuccess handler in main.js
				}
			});
		}

		refreshCsrfToken();

		var csrfRefreshInterval = setInterval(refreshCsrfToken, 4 * 60 * 1000);

		$(document).on('ajaxComplete', function() {
			clearInterval(csrfRefreshInterval);
		});
	})();
</script>