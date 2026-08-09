<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Site extends Core_Controller
{
	// Whitelist: Public authentication methods (no permission required)
	protected $public_methods = [
		'index',             // Homepage
		'login',             // Login form
		'logout',            // Logout
		'forgot_password',   // Password reset request
		'activate',          // Account activation
		'reset_password',    // Password reset
		'error',             // Error page
		'check_gateway',     // Gateway status check
		'etamu',             // eTamu public form
		'get_ratio',
	];

	function index()
	{
		$this->load->library('migration');

		$classes = [
			'Manu' => 'primary',
			'Web' => 'success',
			'Monitoring' => 'danger',
			'MA' => 'warning',
			'Eselon1' => 'info',
			'Banding' => 'light text-dark',
			'Lain-lain' => 'secondary',
		];

		$this->load->model('app/Web_Model', 'web');
		$this->vars['main_body'] = 'layout_content';
		$this->vars['view'] = 'site/one';
		$this->vars['classes'] = $classes;
		$this->vars['apps'] = $this->_transform_data($this->web->find());

		$result = $this->_login_form();

		if ($this->input->is_ajax_request() && is_array($result) && isset($result['status'])) {
			return $this->set_content_type($result);
		}
		if (!$this->ion_auth->logged_in()) {
			$this->vars['lightAssets'] = true;
			// $this->vars['showParticles'] = true;
		}
		$this->load->vars($this->vars);

		if ($this->input->is_ajax_request()) {
			return $this->viewAjax('layout_content', [
				'title' => APP_NAME,
				'showPageHeader' => false,
			]);
		}

		if ($this->ion_auth->logged_in()) {
			return $this->load->view('layout');
		}
		return $this->load->view('layout');
	}

	/**
	 * Standalone login page (direct visits / modal fallback).
	 */
	public function login()
	{
		if ($this->ion_auth->logged_in()) {
			redirect($this->_consume_redirect_session());
		}

		$is_submission = $this->input->method() === 'post';

		// Bare GET, non-AJAX: render the standalone login page in place.
		// This must NOT redirect away — that would silently defeat every
		// _check_access() redirect into this method, bouncing the user
		// straight back out (previously to '/') instead of ever showing
		// them a login form.
		if (!$is_submission && !$this->input->is_ajax_request()) {
			$this->_login_form();
			$this->vars['main_body'] = 'layout_content';
			$this->vars['view']      = 'site/login';
			$this->vars['title']     = $this->lang->line('login_heading');
			$this->vars['showPageHeader']     = false;
			$this->load->vars($this->vars);
			return $this->load->view('layout');
		}

		$result = $this->_login_form();
		$this->load->vars($this->vars);

		// If this wasn't an AJAX request, _login_form() has already either
		// redirect()'d (on successful login) or set flash toast data (on
		// failure/validation error) and returned null. In that case we must
		// redirect back rather than fall through to JSON-encoding a bare
		// fallback array at the browser — that's what was producing a raw
		// JSON blob on screen for a plain (non-JS) form submission.
		if (!$this->input->is_ajax_request()) {
			redirect('site/login');
		}

		if (!is_array($result)) {
			$result = [
				'title'           => $this->lang->line('login_heading'),
				// 'hideCloseButton' => true,
			];
		}

		return $this->set_content_type(array_merge($result, [
			'title'           => $this->lang->line('login_heading'),
			// 'hideCloseButton' => true,
		]));
	}

	private function _login_form()
	{
		$canAccessLive = true;

		$this->form_validation->set_rules('identity', str_replace(':', '', $this->lang->line('login_identity_label')), 'required');
		$this->form_validation->set_rules('password', str_replace(':', '', $this->lang->line('login_password_label')), 'required');

		if ($this->config->item('enable_captcha')) {
			$this->form_validation->set_rules('captcha', 'Kode Captcha', 'required|verify_captcha');
		}

		$this->vars['identity'] = [
			'name'         => 'identity',
			'id'           => 'identity',
			'value'        => $this->form_validation->set_value('identity'),
			'class'        => 'form-control',
			'placeholder'  => lang('login_identity_label'),
			'autocomplete' => 'username',
			'icon'         => 'fa-user',
			'required'     => true,
		];

		$this->vars['password'] = [
			'type'        => 'form_password',
			'name'        => 'password',
			'placeholder' => 'Kata Sandi',
			'required'    => true,
		];

		if ($this->config->item('enable_captcha')) {
			$this->vars['captcha'] = [
				'name'        => 'captcha',
				'id'          => 'captcha',
				'class'       => 'form-control',
				'placeholder' => 'Ketikkan kode captcha...',
			];
		}

		$this->vars['message'] = '';

		// Distinguish "opening the form" (GET, no data yet) from "submitting
		// the form" (POST). Without this, form_validation->run() evaluates
		// the required rules against an empty $_POST on every GET load of
		// the modal, and the fresh form comes back showing "field required"
		// errors before the user has typed anything.
		$is_submission = $this->input->method() === 'post';

		if ($is_submission && $this->form_validation->run() === TRUE && ($canAccessLive || is_local_ip())) {
			$identity = $this->input->post('identity');
			$password = $this->input->post('password');
			$remember = (bool) $this->input->post('remember');

			$logged_in = false;

			// if (is_localhost()) {
			// 	$this->load->model('ion_auth_model');
			// 	$user = $this->db
			// 		->where($this->config->item('identity', 'ion_auth'), $identity)
			// 		->limit(1)
			// 		->order_by('id', 'desc')
			// 		->get($this->ion_auth_model->tables['users'])
			// 		->row();

			// 	if ($user && $user->active) {
			// 		$this->ion_auth_model->set_session($user);
			// 		$this->ion_auth_model->update_last_login($user->id);
			// 		$this->session->sess_regenerate(FALSE);
			// 		$logged_in = true;
			// 	} else {
			// 		$this->ion_auth->set_error('login_unsuccessful');
			// 	}
			// } else {
			$logged_in = $this->ion_auth->login($identity, $password, $remember);
			// }

			if ($logged_in) {
				$this->user = $this->ion_auth->user()->row();

				// Bust menu cache
				$this->session->unset_userdata('nav_menu_' . $this->user->id);
				$this->session->unset_userdata('nav_menu_base_' . $this->user->id);

				// set_toast('Selamat datang di ' . APP_SHORT_NAME . ', ' . $this->user->nama_lengkap . '!');

				// _consume_redirect_session() is defined in Core_Controller.
				// It reads+clears session['redirect'] and returns a safe URL.
				$redirect_url = $this->_consume_redirect_session();

				if ($this->input->is_ajax_request()) {
					return [
						'status'            => true,
						'message'           => 'Login berhasil',
						'redirect'          => $redirect_url,
						'csrf_token_name'   => $this->security->get_csrf_token_name(),
						'csrf_hash'         => $this->security->get_csrf_hash(),
					];
				}

				redirect($redirect_url);
			}

			// Login failed
			$this->vars['message'] = $this->ion_auth->errors();

			if ($this->input->is_ajax_request()) {
				return [
					'status'            => false,
					'message'           => $this->ion_auth->errors(),
					'content'           => $this->load->view('site/_login_form', $this->vars, TRUE),
					'csrf_token_name'   => $this->security->get_csrf_token_name(),
					'csrf_hash'         => $this->security->get_csrf_hash(),
				];
			}

			set_toast($this->ion_auth->errors(), '', 'bg-danger');
		} elseif ($is_submission) {
			$this->vars['message'] = (validation_errors()) ? validation_errors() : '';
		}
		// else: bare GET to open the modal — leave $this->vars['message'] empty,
		// no validation has actually been attempted yet.

		if ($this->input->is_ajax_request()) {
			return [
				'status'            => false,
				'message'           => $this->vars['message'] ?: '',
				'content'           => $this->load->view('site/_login_form', $this->vars, TRUE),
				'csrf_token_name'   => $this->security->get_csrf_token_name(),
				'csrf_hash'         => $this->security->get_csrf_hash(),
			];
		}

		if ($this->vars['message']) {
			set_toast($this->vars['message'], '', 'bg-danger');
		}
	}

	function dashboard()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('site/login');
		}

		$year  = intval($this->uri->segment(3)) ? $this->uri->segment(3) : date('Y');
		$month = intval($this->uri->segment(4)) ? $this->uri->segment(4) : date('n');

		$this->load->vars([
			'main_body'     => 'layout_content',
			'view'          => 'site/index',
			'title'         => 'Statistik Antrian',
			'datasets'      => $this->_get_statistics($year, $month),
			'selectedMonth' => $month,
			'selectedYear'  => $year,
		]);

		$this->vars['assets'] = array_merge(
			isset($this->vars['assets']) && is_array($this->vars['assets']) ? $this->vars['assets'] : [],
			['busy_load' => true, 'moment' => true]
		);
		$this->load->vars($this->vars);

		if ($this->input->is_ajax_request()) {
			return $this->viewAjax('layout_content', [
				'title' => ' Beranda',
			]);
		}

		$this->load->view('layout');
	}

	public function etamu()
	{
		redirect('app/etamu');
	}

	function get_ratio()
	{
		$this->load->model('rekapitulasi/Ratio_Model', 'ratio');

		$this->vars['main_body'] = 'layout_content';
		$this->vars['view'] = 'site/_ratio';
		$this->vars['count_summary'] = $this->ratio->get_count_summary(date('Y'));
		$this->vars['count_dirput_antrian'] = $this->ratio->get_count_dirput_antrian();
		$this->vars['ratio'] = $this->ratio->get_ratio();
		$this->vars['count_redaksi'] = $this->ratio->get_count_redaksi();
		$this->vars['count_dirput_perkara'] = $this->ratio->get_count_dirput_perkara();
		$this->vars['kinerja_bas'] = $this->ratio->kinerja_bas();
		$this->vars['kinerja_minutasi'] = $this->ratio->kinerja_minutasi();

		$this->load->vars($this->vars);

		if ($this->input->is_ajax_request()) {
			return $this->viewAjax('site/_ratio');
		}

		$this->load->view('layout');
	}

	public function error()
	{
		$this->load->vars([
			'main_body' => 'layout_content',
			'view'      => 'site/error',
		]);

		$this->vars['lightAssets'] = true;
		$this->load->vars($this->vars);

		if ($this->input->is_ajax_request()) {
			return $this->viewAjax('layout_content', ['showOnModal' => true]);
		}
		$this->load->view('layout');
	}

	public function logout()
	{
		$this->vars['title'] = 'Logout';
		$this->ion_auth->logout();
		redirect('/');
	}

	public function forgot_password()
	{
		$this->vars['title']       = $this->lang->line('forgot_password_heading');
		$this->vars['lightAssets'] = true;

		if ($this->config->item('identity', 'ion_auth') != 'email') {
			$this->form_validation->set_rules('identity', $this->lang->line('forgot_password_identity_label'), 'required');
		} else {
			$this->form_validation->set_rules('identity', $this->lang->line('forgot_password_validation_email_label'), 'required|valid_email');
		}

		if ($this->form_validation->run() === FALSE) {
			$this->vars['type'] = $this->config->item('identity', 'ion_auth');
			$this->vars['identity_label'] = $this->config->item('identity', 'ion_auth') != 'email'
				? $this->lang->line('forgot_password_identity_label')
				: $this->lang->line('forgot_password_email_identity_label');

			$this->vars['identity'] = [
				'name'        => 'identity',
				'id'          => 'identity',
				'class'       => 'form-control',
				'placeholder' => $this->vars['type'] == 'email'
					? sprintf(lang('forgot_password_email_label'), $this->vars['identity_label'])
					: sprintf(lang('forgot_password_identity_label'), $this->vars['identity_label']),
			];

			$this->vars['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('toast');
			$this->_render_page('site' . DIRECTORY_SEPARATOR . 'forgot_password', $this->vars);
		} else {
			$identity_column = $this->config->item('identity', 'ion_auth');
			$identity        = $this->ion_auth->where($identity_column, $this->input->post('identity'))->users()->row();

			if (empty($identity)) {
				$this->ion_auth->set_error($identity_column != 'email' ? 'forgot_password_identity_not_found' : 'forgot_password_email_not_found');
				set_toast($this->ion_auth->errors(), '', 'bg-danger');
				redirect('site/forgot_password');
			}

			$forgotten = $this->ion_auth->forgotten_password($identity->{$identity_column});

			if ($forgotten) {
				set_toast($this->ion_auth->messages());
				redirect('site/login');
			} else {
				set_toast($this->ion_auth->errors(), '', 'bg-danger');
				redirect('site/forgot_password');
			}
		}
	}

	public function activate($id, $code = FALSE)
	{
		$activation = FALSE;

		if ($code !== FALSE) {
			$activation = $this->ion_auth->activate($id, $code);
		} else if ($this->ion_auth->is_admin()) {
			$activation = $this->ion_auth->activate($id);
		}

		if ($activation) {
			set_toast($this->ion_auth->messages());
			redirect($this->ion_auth->is_admin() ? 'admin' : '/');
		} else {
			set_toast($this->ion_auth->errors(), '', 'bg-danger');
			redirect('site/forgot_password');
		}
	}

	public function reset_password($code = NULL)
	{
		if (!$code) {
			show_404();
		}

		$this->vars['title']       = $this->lang->line('reset_password_heading');
		$this->vars['lightAssets'] = true;

		$user = $this->ion_auth->forgotten_password_check($code);

		if ($user) {
			$this->form_validation->set_rules('new', $this->lang->line('reset_password_validation_new_password_label'), 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|matches[new_confirm]');
			$this->form_validation->set_rules('new_confirm', $this->lang->line('reset_password_validation_new_password_confirm_label'), 'required');

			if ($this->form_validation->run() === FALSE) {
				$this->vars['message']            = (validation_errors()) ? validation_errors() : $this->session->flashdata('toast');
				$this->vars['min_password_length'] = $this->config->item('min_password_length', 'ion_auth');
				$this->vars['new_password'] = [
					'name'        => 'new',
					'id'          => 'new',
					'type'        => 'password',
					'pattern'     => '^.{' . $this->vars['min_password_length'] . '}.*$',
					'class'       => 'form-control',
					'placeholder' => sprintf(lang('reset_password_new_password_label'), $this->vars['min_password_length']),
				];
				$this->vars['new_password_confirm'] = [
					'name'        => 'new_confirm',
					'id'          => 'new_confirm',
					'type'        => 'password',
					'pattern'     => '^.{' . $this->vars['min_password_length'] . '}.*$',
					'class'       => 'form-control',
					'placeholder' => lang('reset_password_new_password_confirm_label'),
				];
				$this->vars['user_id'] = ['name' => 'user_id', 'id' => 'user_id', 'type' => 'hidden', 'value' => $user->id];
				$this->vars['code']    = $code;
				$this->_render_page('site' . DIRECTORY_SEPARATOR . 'reset_password', $this->vars);
			} else {
				$identity = $user->{$this->config->item('identity', 'ion_auth')};
				if ($user->id != $this->input->post('user_id')) {
					$this->ion_auth->clear_forgotten_password_code($identity);
					show_error($this->lang->line('error_csrf'));
				} else {
					$change = $this->ion_auth->reset_password($identity, $this->input->post('new'));
					if ($change) {
						set_toast($this->ion_auth->messages());
						redirect('site/login');
					} else {
						set_toast($this->ion_auth->errors(), '', 'bg-danger');
						redirect('site/reset_password/' . $code);
					}
				}
			}
		} else {
			set_toast($this->ion_auth->errors(), '', 'bg-danger');
			redirect('site/forgot_password');
		}
	}

	function view_detail()
	{
		$this->vars['url']         = $this->input->get('url');
		$this->vars['type']        = $this->input->get('type') ?: 'image';
		$this->vars['lightAssets'] = true;
		$this->load->vars($this->vars);

		if ($this->input->is_ajax_request()) {
			return $this->viewAjax('site/view_detail', ['title' => 'Halaman Pratinjau', 'size' => 'modal-lg']);
		}

		$this->load->view('layout');
	}

	function get_nomor_perkara()
	{
		$arr_result = [];
		foreach ($this->sipp->get_nomor_perkara($this->input->post('no_perkara'), ($this->input->post('today_only') == 1 ? date('Y-m-d') : null)) as $row) {
			$arr_result[] = $row->nomor_perkara;
		}
		return $this->set_content_type($arr_result);
	}

	private function _transform_data($data)
	{
		$customCategories = [SATKER_BANDING => SATKER_BANDING, SATKER_ESELON_1 => SATKER_ESELON_1];

		$result = [];
		foreach ($data as $item) {
			$icon = null;
			if ($item->icon) {
				$icon = strpos($item->icon, 'fa-') !== false
					? $item->icon
					: file_url('web', $item->icon);
			}

			$category = isset($customCategories[$item->category]) ? $customCategories[$item->category] : $item->category;

			$result[$category][] = [
				$item->name,
				filter_var($item->url, FILTER_VALIDATE_URL) ? $item->url : base_url($item->url),
				$icon,
				$category,
				$item->tag,
				$item->icon_width,
				$item->icon_height,
				isset($item->description) ? $item->description : '',
			];
		}
		return $result;
	}

	public function _render_page($view, $data = NULL, $returnhtml = FALSE)
	{
		$viewdata = (empty($data)) ? $this->vars : $data;

		$this->load->vars([
			'main_body' => 'layout_content',
			'view'      => $view,
		]);

		$view_html = $this->load->view('layout', $viewdata, $returnhtml);

		if ($returnhtml) {
			return $view_html;
		}
	}

	function get_wilayah()
	{
		$this->load->model('Wilayah_Model', 'wilayah');

		$data = [];
		foreach ($this->wilayah->findWilayah(['a.nama' => $this->input->get('keyword')]) as $id => $text) {
			$data[] = ['id' => $id, 'text' => $text];
		}

		echo json_encode(['data' => $data]);
	}

	function get_alamat()
	{
		if ($this->input->get('user_id')) {
			$this->load->model('pegawai/Alamat_Model', 'alamat');
			$addresses = $this->alamat->get_list(['user_id' => $this->input->get('user_id')]);

			$data = [];
			foreach ($addresses as $address) {
				$data[] = ['id' => $address->id, 'text' => $address->alamat_lengkap];
			}

			return $this->output->set_content_type('application/json')->set_output(json_encode(['data' => $data]));
		}
	}

	function pdf()
	{
		$this->vars['main_body'] = 'widgets/pdf_viewer';
		$this->vars['view']      = 'widgets/pdf_viewer';
		$this->load->vars($this->vars);
		$this->load->view('layout');
	}

	function simtepa($type)
	{
		$simtepa = [
			'struktur'        => ['title' => 'Struktur Organisasi',  'url' => 'https://simtepa.mahkamahagung.go.id/share/struktur_organisasi/a31f737046b650c2ed878eb996edd685'],
			'statistik'       => ['title' => 'Statistik',            'url' => 'https://simtepa.mahkamahagung.go.id/share/statistik_ttntt/a31f737046b650c2ed878eb996edd685'],
			'ketua'           => ['title' => 'Ketua',                'url' => 'https://simtepa.mahkamahagung.go.id/share/profil_ketua/html/a31f737046b650c2ed878eb996edd685'],
			'waka'            => ['title' => 'Wakil Ketua',          'url' => 'https://simtepa.mahkamahagung.go.id/share/profil_wakil/html/a31f737046b650c2ed878eb996edd685'],
			'hakim'           => ['title' => 'Hakim',                'url' => 'https://simtepa.mahkamahagung.go.id/share/profil_hakim/html/a31f737046b650c2ed878eb996edd685'],
			'kepaniteraan'    => ['title' => 'Kepaniteraan',         'url' => 'https://simtepa.mahkamahagung.go.id/share/profil_kepaniteraan/html/a31f737046b650c2ed878eb996edd685'],
			'kesekretariatan' => ['title' => 'Kesekretariatan',      'url' => 'https://simtepa.mahkamahagung.go.id/share/profil_kesekretariatan/html/a31f737046b650c2ed878eb996edd685'],
			'fungsional'      => ['title' => 'Pejabat Fungsional',   'url' => 'https://simtepa.mahkamahagung.go.id/share/profil_fungsional/html/a31f737046b650c2ed878eb996edd685'],
			'pelaksana'       => ['title' => 'Staf Pelaksana',       'url' => 'https://simtepa.mahkamahagung.go.id/share/profil_pelaksana/html/a31f737046b650c2ed878eb996edd685'],
		];

		if (!isset($simtepa[$type])) {
			$this->load->vars(['error' => 'Data tidak ditemukan']);
			return $this->viewAjax('site/modal', ['size' => 'modal-sm', 'title' => 'Data Tidak ditemukan']);
		}

		$this->load->vars($simtepa[$type]);
		return $this->viewAjax('site/modal', ['size' => 'modal-lg', 'title' => strtoupper($simtepa[$type]['title'])]);
	}

	function _get_statistics($year, $month)
	{
		$dates    = get_dates($month, $year);
		$antrians = $this->antrian->get_month_data($year, $month);

		$data = [];
		foreach ($dates as $date) {
			$daily      = ['x' => date('d M Y', strtotime($date))];
			$empty      = true;
			foreach ($antrians as $row) {
				if ($row->tanggal_antrian == $date) {
					$daily[$row->tipe] = $row->jumlah;
					$empty = false;
				}
			}
			if (!$empty) {
				$data[] = $daily;
			}
		}

		$config   = get_queue_config();
		$datasets = [];
		foreach (get_queue_type() as $type) {
			$datasets[] = [
				'label'           => $config[$type]['title_list'],
				'data'            => $data,
				'parsing'         => ['yAxisKey' => $type],
				'fill'            => false,
				'borderColor'     => $config[$type]['color'],
				'tension'         => 0.1,
				'backgroundColor' => str_replace('0.9', '0.2', $config[$type]['color']),
				'borderWidth'     => 1,
			];
		}

		return $datasets;
	}
}