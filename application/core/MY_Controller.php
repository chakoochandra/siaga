<?php
defined('BASEPATH') or exit('No direct script access allowed');

setlocale(LC_TIME, 'id_ID');
date_default_timezone_set("Asia/Jakarta");

/**
 * Base controller exposing CodeIgniter magic properties for IDE/static analysis.
 *
 * @property CI_Loader $load
 * @property CI_URI $uri
 * @property CI_Input $input
 * @property CI_Output $output
 * @property CI_Security $security
 * @property CI_Config $config
 * @property CI_Lang $lang
 * @property CI_Session $session
 * @property CI_Form_validation $form_validation
 * @property CI_Benchmark $benchmark
 * @property CI_Profiler $profiler
 * @property CI_Utf8 $utf8
 * @property CI_User_agent $agent
 * @property CI_Encrypt $encrypt
 * @property CI_Encryption $encryption
 * @property Ion_auth|Ion_auth_model $ion_auth
 * @property CI_Pagination $pagination
 * @property Escpos $Escpos
 * @property Toastr $toastr
 * @property Utf8cleaner $Utf8cleaner
 * @property CI_DB_query_builder $db
 * @property CI_Model $model
 */
class Core_Controller extends CI_Controller
{
	/** Cache menu per session to avoid repeated DB queries */
	protected $cache_menu = TRUE;

	public $vars = [];
	public $showBreadcrumb = true;
	public $showFooter = true;
	protected $showPageHeader = true;
	protected $showNavbar = true;

	public $user;
	public $from = 'site';
	public $editable;
	public $enableRefresh = false;

	protected $isAuthorized = true;
	protected $enableTest = false;

	protected $model;
	protected $redirectUrl;
	protected $showOnModal = false;

	protected $indexTitle;
	protected $indexSubtitle;
	protected $indexView;
	protected $indexUrl;
	protected $mainBody = 'layout_content';
	protected $indexLayout = 'layout';

	protected $viewView;
	protected $viewModalSize = 'modal-xl';
	protected $viewData;

	protected $listView;

	protected $fileFolders = [];
	protected $fileAllowedTypes = [];

	// Python Runner properties
	protected $python_path;
	protected $script_path;

	/**
	 * Methods that are publicly accessible without authentication.
	 * Child controllers should declare their own $public_methods list.
	 * Core always treats the Site controller's public list as safe.
	 */
	protected $public_methods = [];

	public function __construct()
	{
		parent::__construct();

		$this->enableTest = $this->config->item('enable_test');

		$this->form_validation->set_error_delimiters(
			$this->config->item('error_start_delimiter', 'ion_auth'),
			$this->config->item('error_end_delimiter', 'ion_auth')
		);

		$this->script_path = FCPATH . 'python_scripts' . DIRECTORY_SEPARATOR;

		$this->setFrom($this->from);

		// ── Save the intended destination BEFORE any auth redirect ───────────
		// Only capture when:
		//   1. The user is NOT logged in (no point saving if already authed)
		//   2. The current request is NOT a public/auth method (those are never
		//      a meaningful "intended destination")
		//   3. The session does not already hold a saved URL (first-write wins;
		//      prevents the homepage load from overwriting the real target)
		//   4. Not an AJAX request (AJAX fragments are not navigable destinations)
		if (
			!$this->ion_auth->logged_in()
			&& !$this->input->is_ajax_request()
			&& !$this->_is_public_method()
			&& !$this->session->userdata('redirect')
		) {
			$intended = current_url();
			// Sanity-check: must be same host and not a logout URL
			if (strpos($intended, 'logout') === false && $this->_is_safe_redirect($intended)) {
				$this->session->set_userdata('redirect', $intended);
			}
		}

		// Load models needed for navigation and permissions
		// $this->load->model('settings/Menu_Model', 'menu');
		// $this->load->model('settings/Permission_Model', 'permission');

		// DEV: MOCKUP USER. Auto-login as user id 1 when in development mode and not logged in
		// if (is_development() && !$this->ion_auth->logged_in()) {
		// 	$dev_user = $this->ion_auth->user(1)->row();
		// 	if ($dev_user) {
		// 		$this->ion_auth_model->set_session($dev_user);
		// 		$this->ion_auth_model->update_last_login($dev_user->id);
		// 		log_message('info', 'DEV auto-login as user id=1 (' . $dev_user->username . ')');
		// 	}
		// }

		if ($this->ion_auth->logged_in()) {
			$this->user = $this->ion_auth->user()->row();
			$this->user->photoUrl = (function_exists('file_url') ? file_url('photo', $this->user->photo) : null) ?: asset_url('assets/images/user.png');

			$this->load->model('pegawai/Jabatan_Model', 'jabatan');
			$this->user->jabatan = ($jabatan = $this->jabatan->findOne($this->user->jabatan_id))
				? $jabatan->nama_jabatan
				: '';

			// $this->vars['nav_menu']    = $this->_get_menu();
			// $this->vars['auth_user']   = $this->ion_auth->user()->row();
			// $this->vars['auth_groups'] = $this->ion_auth->groups()->result();
		}

		$this->initFabItems();
	}

	// ---------------------------------------------------------------
	// Redirect helpers
	// ---------------------------------------------------------------

	/**
	 * Check whether the current controller+method pair is publicly accessible
	 * (i.e. does not require authentication), so we never save those URLs as
	 * the "intended destination after login".
	 *
	 * Checks:
	 *   - Site controller public_methods list (hardcoded safety net)
	 *   - The child controller's own $public_methods property
	 */
	private function _is_public_method()
	{
		$controller = strtolower($this->router->fetch_class());
		$method     = strtolower($this->router->fetch_method());

		// The Site controller's index IS the login page — never a destination
		if ($controller === 'site') {
			return true;
		}

		// Check the child controller's declared public methods
		if (!empty($this->public_methods) && in_array($method, array_map('strtolower', $this->public_methods))) {
			return true;
		}

		return false;
	}

	/**
	 * Validate that a URL is safe to redirect to (same host, no javascript:, etc.)
	 *
	 * Also rejects paths that route into the Download controller (e.g.
	 * "download/web/facebook-new.png"). That controller streams a raw file
	 * or 404s — never a page — so it's never a valid "intended destination"
	 * to save before login, nor a valid target to resume after login. This
	 * one check guards both the save (constructor, above) and the consume
	 * (_consume_redirect_session, below).
	 *
	 * @param  string $url
	 * @return bool
	 */
	protected function _is_safe_redirect($url)
	{
		if (empty($url)) {
			return false;
		}

		$parsed = parse_url($url);

		// Relative URLs are always safe
		if (empty($parsed['host'])) {
			if (isset($parsed['scheme'])) { // rule out javascript:foo
				return false;
			}
			return !$this->_targets_download_controller(isset($parsed['path']) ? $parsed['path'] : '');
		}

		$app_host     = parse_url(base_url(), PHP_URL_HOST);
		$current_host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';

		if ($parsed['host'] !== $app_host && $parsed['host'] !== $current_host) {
			return false;
		}

		return !$this->_targets_download_controller(isset($parsed['path']) ? $parsed['path'] : '');
	}

	/**
	 * Does this path's first segment (after stripping the app base path)
	 * route into the Download controller? Covers both the clean route
	 * "download/file/(:any)"/"download/(:any)/(:any)" from routes.php and
	 * the raw "download/index/..." form.
	 *
	 * @param  string $path
	 * @return bool
	 */
	private function _targets_download_controller($path)
	{
		if ($path === '' || $path === null) {
			return false;
		}

		$base_path = parse_url(base_url(), PHP_URL_PATH);
		if ($base_path && strpos($path, $base_path) === 0) {
			$path = substr($path, strlen($base_path));
		}

		$segments = array_values(array_filter(explode('/', $path), 'strlen'));

		return isset($segments[0]) && strtolower($segments[0]) === 'download';
	}

	/**
	 * Read and clear the stored redirect URL from session.
	 * Falls back to the site homepage when no redirect was stored (e.g. the
	 * user logged in straight from the public homepage), which is a safe,
	 * always-navigable URL — unlike current_url(), which in an AJAX login
	 * context returns the AJAX endpoint URL itself rather than a real page.
	 *
	 * @return string  Absolute URL to redirect to after login
	 */
	protected function _consume_redirect_session()
	{
		$stored = $this->session->userdata('redirect');
		$this->session->unset_userdata('redirect');

		if ($stored && $this->_is_safe_redirect($stored)) {
			$path = parse_url($stored, PHP_URL_PATH);
			if ($this->_targets_download_controller($path)) {
				return base_url('site');
			}

			return $stored;
		}

		return base_url('site');
	}

	// ---------------------------------------------------------------
	// Menu injection
	// ---------------------------------------------------------------

	private function _get_menu()
	{
		$user_id       = $this->ion_auth->user()->row()->id;
		$base_cache_key = 'nav_menu_base_' . $user_id;
		$old_cache_key  = 'nav_menu_' . $user_id;

		if ($this->cache_menu) {
			$this->session->unset_userdata($old_cache_key);

			$base_menu = $this->session->userdata($base_cache_key);
			// if ($base_menu === NULL || $base_menu === false || !is_array($base_menu)) {
			// 	$base_menu = $this->menu->get_menu_for_user($user_id);
			// 	$this->session->set_userdata($base_cache_key, $base_menu);
			// }
		} else {
			$base_menu = $this->menu->get_menu_for_user($user_id);
		}

		return $this->_compute_active_states($base_menu);
	}

	private function _compute_active_states($menu)
	{
		$CI      = &get_instance();
		$current = rtrim($CI->uri->uri_string(), '/');
		$base    = base_url();

		if (!is_array($menu)) {
			return [];
		}

		$result = [];

		foreach ($menu as $item) {
			$new_item = $item;
			unset($new_item['active'], $new_item['menu_open']);

			$has_children = !empty($item['children']) && is_array($item['children']);

			if ($has_children) {
				$new_item['children'] = $this->_compute_active_states($item['children']);
				if ($this->_has_active_descendant($new_item['children'])) {
					$new_item['active']    = true;
					$new_item['menu_open'] = true;
				}
			} else {
				$url = isset($item['url']) ? $item['url'] : '';
				if ($url && $url !== '#') {
					$target = rtrim(str_replace($base, '', $url), '/');
					if ($current === $target) {
						$new_item['active'] = true;
					}
				}
			}

			$result[] = $new_item;
		}

		return $result;
	}

	private function _has_active_descendant($menu)
	{
		if (!is_array($menu)) {
			return false;
		}

		foreach ($menu as $item) {
			if (!empty($item['active'])) {
				return true;
			}
			if (!empty($item['children']) && is_array($item['children'])) {
				if ($this->_has_active_descendant($item['children'])) {
					return true;
				}
			}
		}
		return false;
	}

	protected function _bust_menu_cache($user_id = NULL)
	{
		if ($user_id) {
			$this->session->unset_userdata('nav_menu_' . $user_id);
			$this->session->unset_userdata('nav_menu_base_' . $user_id);
		} else {
			$uid = $this->ion_auth->user()->row()->id;
			$this->session->unset_userdata('nav_menu_' . $uid);
			$this->session->unset_userdata('nav_menu_base_' . $uid);
		}
	}

	// ---------------------------------------------------------------
	// Standard CRUD / view actions
	// ---------------------------------------------------------------

	public function index()
	{
		$this->prepare_index($this->vars);

		if ($this->input->is_ajax_request()) {
			return $this->viewAjax($this->mainBody, [
				'showPageHeader' => $this->showPageHeader,
				'title'    => isset($this->vars['title'])    ? $this->vars['title']    : APP_NAME,
				'subtitle' => isset($this->vars['subtitle']) ? $this->vars['subtitle'] : APP_SHORT_NAME . ' (' . APP_NAME . ')',
				'icon'     => isset($this->vars['page_icon']) ? $this->vars['page_icon'] : 'fa-solid fa-layer-group',
				'message'  => isset($this->vars['message'])  ? $this->vars['message']  : '',
				'showOnModal' => $this->showOnModal,
				'showNavbar'  => $this->showNavbar,
			]);
		}

		if (is_development() && is_local_ip()) {
			$this->output->enable_profiler(TRUE);
		}

		$this->load->view($this->indexLayout, [
			'showPageHeader' => $this->showPageHeader,
			'showNavbar'     => $this->showNavbar,
		]);
	}

	protected function prepare_index($options = [])
	{
		$this->vars['title']     = isset($options['title'])    ? $options['title']    : $this->indexTitle;
		$this->vars['subtitle']  = isset($options['subtitle']) ? $options['subtitle'] : (isset($this->indexSubtitle) ? $this->indexSubtitle : null);
		$this->vars['page_icon'] = isset($options['page_icon']) ? $options['page_icon'] : (isset($this->indexIcon) ? $this->indexIcon : 'fa-solid fa-layer-group');
		$this->vars['main_body'] = isset($options['main_body']) ? $options['main_body'] : $this->mainBody;
		$this->vars['view']      = isset($options['view'])     ? $options['view']     : $this->indexView;

		if (isset($this->module_id)) {
			$this->vars['module_id'] = $this->module_id;
		}

		$this->load->vars(array_merge($this->vars, $options));
	}

	function view($id)
	{
		$this->viewData = $this->model->findOne($id);
		if (!$this->viewData) {
			show_404();
			return;
		}

		$this->vars['data']      = $this->viewData;
		$this->vars['main_body'] = 'layout_content';
		$this->vars['view']      = $this->viewView;

		$this->load->vars($this->vars);

		if ($this->input->is_ajax_request()) {
			return $this->viewAjax($this->viewView, [
				'size' => $this->viewModalSize,
			]);
		}

		$this->load->view('layout');
	}

	function delete($id)
	{
		if (!$this->ion_auth->logged_in()) {
			return $this->set_content_type(null);
		}

		$data = $this->model->findOne($id);
		if (!$data) {
			return $this->redirectAjax([
				'status'  => false,
				'message' => 'Data tidak ditemukan',
			]);
		}

		$result = $this->model->delete($id);
		if ($result) {
			foreach (array_keys($this->fileFolders) as $field) {
				if ($data->$field) {
					delete_file($this->fileFolders[$field], $data->$field);
				}
			}
		}

		return $this->redirectAjax([
			'redirect'    => $this->indexUrl ?: $this->redirectUrl,
			'status'      => $result,
			'message'     => ($result ? 'Berhasil' : 'Gagal') . ' menghapus data',
			'showOnModal' => $this->showOnModal,
		]);
	}

	public function validate_file_upload($str)
	{
		foreach (array_keys($this->fileFolders) as $field) {
			if (empty($_FILES[$field]['name'])) {
				$this->form_validation->set_message('validate_file_upload', 'File tidak boleh kosong.');
				return FALSE;
			}

			if ($_FILES[$field]['error'] != 0) {
				$this->form_validation->set_message('validate_file_upload', 'Terjadi kesalahan saat mengunggah file.');
				return FALSE;
			}

			$allowed_types = isset($this->fileAllowedTypes[$field]) ? $this->fileAllowedTypes[$field] : ['pdf'];
			if (!in_array(strtolower(pathinfo($_FILES[$field]['name'])['extension']), $allowed_types)) {
				$allowed_extensions = implode(', ', $allowed_types);
				$this->form_validation->set_message('validate_file_upload', "Tipe file salah. Hanya tipe $allowed_extensions yang diizinkan.");
				return FALSE;
			}
		}

		return TRUE;
	}

	protected function upload_file($title, $folder, $field, $prefix = '')
	{
		$upload_path = $this->config->item('folder_root_upload') . '/' . $folder;
		if (!is_dir($upload_path)) {
			mkdir($upload_path, 0755, true);
		}

		$config['upload_path']   = $upload_path;
		$config['encrypt_name']  = TRUE;
		$config['allowed_types'] = implode('|', isset($this->fileAllowedTypes[$field]) ? $this->fileAllowedTypes[$field] : ['pdf']);

		$this->load->library('upload', $config);
		$this->upload->initialize($config);
		if (!$this->upload->do_upload($field)) {
			return [
				'success' => false,
				'message' => $this->upload->display_errors('', ''),
			];
		}

		$upload_data   = $this->upload->data();
		$title_clean   = preg_replace('/_{2,}/', '_', preg_replace('/[^a-zA-Z0-9]+/', '_', strtolower(trim($title))));
		$prefix_clean  = $prefix !== '' ? preg_replace('/_{2,}/', '_', preg_replace('/[^a-zA-Z0-9]+/', '_', strtolower(trim($prefix)))) : '';
		$base_filename = strtolower(
			($prefix_clean !== '' ? "{$prefix_clean}_" : '')
				. ($title_clean ? "{$title_clean}_" : '')
				. sprintf('%03d', rand(1, 999))
				. '_' . time()
		);
		$base_filename = preg_replace('/_{2,}/', '_', $base_filename);

		$file_extension  = '.' . pathinfo($upload_data['file_name'], PATHINFO_EXTENSION);
		$max_base_length = 50 - strlen($file_extension);

		if (strlen($base_filename) > $max_base_length) {
			$base_filename = substr($base_filename, 0, $max_base_length);
		}

		$new_filename = $base_filename . $file_extension;
		$old_path     = $upload_data['full_path'];
		$new_path     = $config['upload_path'] . '/' . $new_filename;

		if (rename($old_path, $new_path)) {
			// Resize (max width, height scaled proportionally) & compress if this is an image
			if ($this->_is_image_file($new_path)) {
				$this->resize_and_compress_image($new_path);
			}

			return [
				'success'   => true,
				'filename'  => $new_filename,
				'file_size' => filesize($new_path),
			];
		}

		return [
			'success' => false,
			'message' => 'Failed to rename uploaded file',
		];
	}

	/**
	 * Check whether a file is a raster image GD/image_lib can handle.
	 * Checked by actual file content (getimagesize), not just extension.
	 *
	 * @param  string $path
	 * @return bool
	 */
	protected function _is_image_file($path)
	{
		if (!is_file($path)) {
			return false;
		}

		$info = @getimagesize($path);
		if ($info === false) {
			return false;
		}

		$allowed_types = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF];
		if (defined('IMAGETYPE_WEBP')) {
			$allowed_types[] = IMAGETYPE_WEBP;
		}

		return in_array($info[2], $allowed_types, true);
	}

	/**
	 * Resize (max width, height scaled proportionally) and compress an
	 * image file in place, using CodeIgniter's image_lib (GD2 driver).
	 *
	 * Never upscales — images already narrower than $max_width are just
	 * re-compressed at $quality, not stretched. Fails silently (upload
	 * still succeeds with the original file) if the GD extension isn't
	 * available or the resize otherwise fails, so this never blocks an
	 * upload — it's a best-effort optimization.
	 *
	 * @param  string $path       Full path to the image file (overwritten in place)
	 * @param  int    $max_width  Max width in px; height is scaled to match (default: 212)
	 * @param  int    $quality    Compression quality, 1-100 (default: 80)
	 * @return bool
	 */
	protected function resize_and_compress_image($path, $max_width = 212, $quality = 80)
	{
		// Library requirement: GD is what CI's image_lib (gd2 driver) relies on
		if (!extension_loaded('gd')) {
			log_message('error', 'resize_and_compress_image: GD extension not available, skipping resize for ' . $path);
			return false;
		}

		$info = @getimagesize($path);
		if ($info === false) {
			return false;
		}

		list($original_width) = $info;

		// Never upscale — only shrink down to (or re-compress at) max_width
		$target_width = min($original_width, $max_width);

		$this->load->library('image_lib');

		$config = [
			'image_library'  => 'gd2',
			'source_image'   => $path,
			'new_image'      => $path,
			'maintain_ratio' => TRUE,
			'width'          => $target_width,
			'quality'        => $quality,
		];

		$this->image_lib->initialize($config);

		$success = $this->image_lib->resize();
		if (!$success) {
			log_message('error', 'resize_and_compress_image failed for ' . $path . ': ' . $this->image_lib->display_errors());
		}

		$this->image_lib->clear();

		return $success;
	}

	protected function handle_file_uploads($title, $existing_record = null, $prefix = '')
	{
		$result = [
			'success'        => true,
			'data'           => [],
			'uploaded_files' => [],
			'old_files'      => [],
			'message'        => '',
		];

		foreach (array_keys($this->fileFolders) as $field) {
			if (!empty($_FILES[$field]['name'])) {
				if ($existing_record && !empty($existing_record->$field)) {
					$result['old_files'][$field] = $existing_record->$field;
				}

				$upload = isset($this->fileFolders[$field])
					? $this->upload_file($title, $this->fileFolders[$field], $field, $prefix)
					: $this->upload_file($title, $field, $field, $prefix);

				if ($upload['success']) {
					$result['data'][$field]           = $upload['filename'];
					$result['uploaded_files'][$field] = $upload['filename'];
				} else {
					$this->cleanup_uploaded_files($result['uploaded_files']);
					$result['success'] = false;
					$result['message'] = $upload['message'];
					return $result;
				}
			} else if ($existing_record && !empty($existing_record->$field)) {
				$result['data'][$field] = $existing_record->$field;
			}
		}

		return $result;
	}

	protected function cleanup_uploaded_files($files)
	{
		if (empty($files)) return;

		foreach ($files as $field => $filename) {
			if (!empty($filename)) {
				$folder = isset($this->fileFolders[$field]) ? $this->fileFolders[$field] : $field;
				delete_file($folder, $filename);
			}
		}
	}

	public function profile($id = null, $title = 'Profil Pegawai')
	{
		$this->prepareProfile($id, true, $title);

		if ($this->input->is_ajax_request()) {
			return $this->viewAjax(isset($this->vars['view']) ? $this->mainBody : 'widgets/profile_full', [
				'title' => $title,
				'size'  => 'modal-xl',
			]);
		}
		$this->load->view($this->from !== 'external' ? 'layout' : 'layout_no_sidebar');
	}

	function pegawai_list($group = null, $use_thumbnails = true)
	{
		$this->load->model('pegawai/Pegawai_Model', 'pegawai');
		$this->model = $this->pegawai;
		$where = [];

		if ($group == 100) {
			$arr = [
				$this->config->item('jabatan_ketua'),
				$this->config->item('jabatan_wakil_ketua'),
				$this->config->item('jabatan_hakim'),
				$this->config->item('jabatan_honorer'),
				$this->config->item('jabatan_outsourcing'),
			];
			$where = ['jabatan_id NOT IN (' . implode(',', $arr) . ')' => NULL, 'active' => 1];
		} else if ($group) {
			$where = ['group' => $group, 'active' => 1];
		}

		if ($this->from == 'external') {
			$where['jabatan_id !='] = $this->config->item('jabatan_outsourcing');
		}

		$data = $this->model->get_list($where);

		foreach ($data as $row) {
			$row->nip         = !is_honorer($row->jabatan_id) && !is_outsourcing($row->jabatan_id) ? $row->nip : ' ';
			$row->nama_jabatan = $this->from == 'external' && is_honorer($row->jabatan_id) ? 'PPNPN' : $row->nama_jabatan;
			$row->colorClass  = !is_pegawai_invalid($row)
				? jabatan_class($row->jabatan_id)
				: ($this->ion_auth->logged_in() && (is_kepegawaian() || is_operator() || $row->id == $this->user->user_id) ? 'bg-danger' : '');
			$row->photoUrl    = $use_thumbnails
				? (get_thumbnail_url('photo', $row->photo, 50, 50) ?: asset_url('assets/images/user.png'))
				: (file_url('photo', $row->photo) ?: asset_url('assets/images/user.png'));
		}

		return $this->set_content_datatable($data, $where);
	}

	function jabatan_golongan($jabatanId = null, $golonganId = null)
	{
		$this->load->model('pegawai/Pegawai_Model', 'pegawai');
		$this->model = $this->pegawai;
		$where = ['(' . $this->config->item('tbl_ref_jabatan') . '.id NOT IN(' . implode(',', get_or_set_jabatan_honorer()) . '))' => null];

		if ($jabatanId) $where['jabatan_id'] = $jabatanId;
		if ($golonganId) $where['golongan_ruang_id'] = $golonganId;

		$data = $this->model->get_list($where);

		foreach ($data as $row) {
			$row->formattedNip     = !is_honorer($row->jabatan_id) && !is_outsourcing($row->jabatan_id) ? $row->nip : ' ';
			$row->formattedJabatan = $this->from == 'external' && is_honorer($row->jabatan_id) ? 'PPNPN' : $row->nama_jabatan;
			$row->colorClass       = !is_pegawai_invalid($row) ? jabatan_class($row->jabatan_id) : (is_kepegawaian() || is_operator() || $row->id == $this->user->user_id ? 'bg-danger' : '');
			$row->baseUrl          = base_url();
		}

		return $this->set_content_datatable($data, $where);
	}

	function jabatan_pendidikan($jabatanId = null, $pendidikanId = null)
	{
		$this->load->model('pegawai/Pegawai_Model', 'pegawai');
		$this->model = $this->pegawai;
		$where = ['(' . $this->config->item('tbl_ref_jabatan') . '.id NOT IN(' . implode(',', get_or_set_jabatan_honorer()) . '))' => null];

		if ($jabatanId) $where['jabatan_id'] = $jabatanId;
		if ($pendidikanId) $where['tingkat_pendidikan_id'] = $pendidikanId;

		$data = $this->model->get_list($where);

		foreach ($data as $row) {
			$row->formattedNip     = !is_honorer($row->jabatan_id) && !is_outsourcing($row->jabatan_id) ? $row->nip : ' ';
			$row->formattedJabatan = $this->from == 'external' && is_honorer($row->jabatan_id) ? 'PPNPN' : $row->nama_jabatan;
			$row->colorClass       = !is_pegawai_invalid($row) ? jabatan_class($row->jabatan_id) : (is_kepegawaian() || is_operator() || $row->id == $this->user->user_id ? 'bg-danger' : '');
			$row->baseUrl          = base_url();
		}

		return $this->set_content_datatable($data, $where);
	}

	function misc($id_user, $type)
	{
		$this->load->model('pegawai/Employee_Data_Model', 'misc');
		$this->model = $this->misc;
		$where = array_merge(['user_id' => $id_user], $this->misc->getWhere($type));

		switch ($type) {
			case 'lhk':
				$data = $this->misc->get_lhk_list($where);
				$recordsTotal = $this->misc->count_lhk_all($where);
				$recordsFiltered = $this->misc->count_lhk_filtered($where);
				break;
			case 'lhkpn':
				$data = $this->misc->get_lhkpn_list($where);
				$recordsTotal = $this->misc->count_lhkpn_all($where);
				$recordsFiltered = $this->misc->count_lhkpn_filtered($where);
				break;
			case 'lhkasn':
				$data = $this->misc->get_lhkasn_list($where);
				$recordsTotal = $this->misc->count_lhkasn_all($where);
				$recordsFiltered = $this->misc->count_lhkasn_filtered($where);
				break;
			default:
				$data = $this->misc->get_list($where);
				$recordsTotal = $this->misc->count_list_all($where);
				$recordsFiltered = $this->misc->count_list_filtered($where);
				break;
		}

		foreach ($data as $row) {
			$row->fileUrl = file_url($this->config->item('folder_misc_pegawai'), $row->file);
		}

		return $this->set_content_type([
			'draw'            => $this->input->post('draw'),
			'recordsTotal'    => $recordsTotal,
			'recordsFiltered' => $recordsFiltered,
			'data'            => $data,
		]);
	}

	function lhk($type = 0)
	{
		$this->load->model('pegawai/Employee_Data_Model', 'misc');
		$this->model = $this->misc;
		$data = $this->misc->get_list(['type' => $type]);

		foreach ($data as $row) {
			$row->fileUrl = file_url($this->config->item('folder_misc_pegawai'), $row->file);
		}

		return $this->set_content_datatable($data, ['type' => $type]);
	}

	function view_cuti($id)
	{
		// $this->load->model('pegawai/Cuti_Model', 'cuti');
		// $cuti = $this->cuti->findOne($id);

		$this->vars['title']     = MODULE_CUTI_SHORT . ' - Verifikasi Cuti';
		$this->vars['main_body'] = 'cuti/view';
		// $this->vars['cuti']      = $cuti;
		$this->vars['backUrl']   = base_url('kepegawaian/pegawai/profile/' . $cuti->user_id);

		$this->load->vars($this->vars);

		if ($this->input->is_ajax_request()) {
			return $this->viewAjax('cuti/view', [
				'size'      => $cuti->status == $this->config->item('status_canceled') && $this->from == 'site' ? 'modal-md' : 'modal-lg',
				'showTitle' => false,
			]);
		}

		$this->load->view('layout_plain');
	}

	protected function setFrom($from)
	{
		$this->from    = $from;
		$this->editable = in_array($from, ['profil', 'pegawai']);
	}

	public function send_stream_data($data)
	{
		echo json_encode($data) . "\n";
		if (ob_get_level() > 0) {
			ob_flush();
		}
		flush();
	}

	protected function set_content_type($data)
	{
		if (!is_array($data)) {
			$data = ['message' => 'Data harus berupa array', 'status' => false];
		}
		return $this->output->set_content_type('application/json')->set_output(json_encode(array_merge($data, [
			'csrf_token_name' => $this->security->get_csrf_token_name(),
			'csrf_hash'       => $this->security->get_csrf_hash(),
		])));
	}

	protected function set_content_datatable($data, $where = [])
	{
		return $this->set_content_type([
			'draw'            => $this->input->post('draw'),
			'recordsTotal'    => $this->model->count_list_all($where),
			'recordsFiltered' => $this->model->count_list_filtered($where),
			'data'            => $data,
		]);
	}

	function get_list()
	{
		if (!$this->input->is_ajax_request() || $this->input->method() !== 'post') {
			show_404(); // or redirect to the actual list page
		}
		return $this->set_content_datatable($this->model->get_list());
	}

	function get_statistic()
	{
		$data = $this->model->get_statistic();
		return $this->set_content_type([
			'draw'            => $this->input->post('draw'),
			'recordsTotal'    => $this->model->count_statistic_all(),
			'recordsFiltered' => $this->model->count_statistic_filtered(),
			'data'            => $data,
		]);
	}

	function perkara_list()
	{
		$this->vars['view'] = $this->listView;
		$this->load->vars($this->vars);

		if ($this->input->is_ajax_request()) {
			return $this->viewAjax($this->listView, ['size' => 'modal-xl', 'showTitle' => true]);
		}

		$this->load->view($this->indexLayout);
	}

	protected function viewAjax($view, $data = [])
	{
		if ($this->input->is_ajax_request()) {
			if ($this->isAuthorized !== true) {
				return $this->set_content_type($this->isAuthorized);
			}

			if (isset($this->vars['title'])) {
				$data['title'] = strtoupper($this->vars['title']);
			} else if (isset($data['title'])) {
				$data['title'] = strtoupper($data['title']);
			} else {
				$data['title'] = strtoupper(APP_SHORT_NAME);
			}

			if (!isset($data['subtitle']) && isset($this->vars['subtitle'])) {
				$data['subtitle'] = $this->vars['subtitle'];
			}
			if (!isset($data['icon']) && isset($this->vars['page_icon'])) {
				$data['icon'] = $this->vars['page_icon'];
			}

			return $this->set_content_type(array_merge($data, [
				'content'    => $this->load->view($view, '', true),
			]));
		}
	}

	protected function redirectAjax($data = [])
	{
		if ($this->input->is_ajax_request()) {
			return $this->set_content_type($data);
		}
	}

	protected function redirectPost($redirect = '')
	{
		if (!$redirect) {
			switch ($this->from) {
				case 'cuti':
					$redirect = 'cuti/verifikasi';
					break;
				default:
					$redirect = $this->from;
					break;
			}
		}

		if ($_SERVER['REQUEST_METHOD'] != 'POST') {
			if ($this->input->is_ajax_request()) {
				return $this->redirectAjax([
					'redirect' => base_url($redirect),
					'status'   => false,
					'message'  => 'Method tidak diizinkan',
				]);
			}

			$this->toastr->error('Method tidak diizinkan');
			return redirect($redirect);
		}
		return false;
	}

	protected function getProfileData($pegawai, $cuti = null, $options = [])
	{
		$this->vars['showProfile'] = true;

		$years = [
			date('Y', strtotime('-2 year')),
			date('Y', strtotime('-1 year')),
			date('Y'),
		];

		// $this->load->model('pegawai/Cuti_Model', 'cuti');

		$this->vars['showInfoSisaCuti'] = isset($options['showInfoSisaCuti']) ? $options['showInfoSisaCuti'] : false;
		$this->vars['showCutiDetails']  = isset($options['showCutiDetails'])  ? $options['showCutiDetails']  : false;
		$this->vars['pegawai']          = $pegawai;
		$this->vars['years']            = $years;
		// $this->vars['cuti']             = $cuti;

		if ($this->from != 'external') {
			$this->vars['data'] = $pegawai
				? $this->cuti->populateRemainder($years, null, $pegawai->id ? [$this->config->item('tbl_users') . '.id' => $pegawai->id] : null)
				: null;
		}
	}

	protected function prepareProfile($id, $showName = true, $title = '')
	{
		// if (!$this->ion_auth->logged_in()) {
		// 	if ($this->input->is_ajax_request()) {
		// 		return ['not_logged_in' => true];
		// 	}
		// 	redirect('site/login');
		// }

		$this->load->model('pegawai/Pegawai_Model', 'pegawai');
		$pegawai = $this->pegawai->findOne($id ?: $this->user->id);

		$data = [
			'main_body'    => 'widgets/profile_full',
			'pegawai'      => $pegawai,
			'groups'       => $this->ion_auth->get_users_groups($pegawai->id)->result(),
			'title'        => $title ?: $pegawai->nama_lengkap,
			'showTitle'    => $this->from == 'external',
			'showLogo'     => $this->from == 'external',
			'showName'     => $showName,
			'isOwnProfile' => $this->user && $pegawai->id == $this->user->id,
			'jobdesc'      => $this->getjobdescData($pegawai),
			// 'remainderCuti' => $this->getPersonalRemainder($pegawai->id),
			'isHonorer'    => $pegawai->is_honorer,
			'isPrivate'    => $this->from != 'external',
			'showPageHeader'    => false,
			// 'achievement'  => $this->from == 'external' || !$this->ion_auth->logged_in() ? [] : [
			// 	'total'     => ($total = $this->antrian->get_user_achievement($this->user->id))                                           ? $total->jumlah : 0,
			// 	'curMonth'  => ($cur   = $this->antrian->get_user_achievement($this->user->id, date('Y'), date('n')))                     ? $cur->jumlah   : 0,
			// 	'prevMonth' => ($prev  = $this->antrian->get_user_achievement($this->user->id, date('Y'), date('n') - 1))                 ? $prev->jumlah  : 0,
			// 	'curDay'    => ($prev  = $this->antrian->get_user_achievement($this->user->id, date('Y'), date('n'), date('j')))           ? $prev->jumlah  : 0,
			// 	'prevDay'   => ($prev  = $this->antrian->get_user_achievement($this->user->id, date('Y'), date('n'), date('j') - 1))      ? $prev->jumlah  : 0,
			// ],
			'canEdit' => $this->editable && (is_owner($pegawai->id) || is_operator() || is_kepegawaian()),
		];

		if (!$id) {
			$data['main_body'] = $this->mainBody;
			$data['view']      = 'widgets/profile_full';
		}

		$this->load->vars($data);
	}

	protected function getjobdescData($pegawai)
	{
		$this->load->model('pegawai/Jobdesc_Model', 'jobdesc');
		return [
			'title'     => 'Uraian Tugas',
			'main_body' => 'kepegawaian/jobdesc/view',
			'data'      => $this->jobdesc->findByUserId($pegawai->id) ?: $this->jobdesc->findByJabatanId($pegawai->jabatan_id),
			'id_user'   => $pegawai->id,
			'canEdit'   => $this->editable && (is_owner($pegawai->id) || is_operator() || is_kepegawaian()),
		];
	}

	protected function getPersonalRemainder($id_user)
	{
		$years = [
			date('Y', strtotime('-2 year')),
			date('Y', strtotime('-1 year')),
			date('Y'),
		];

		$this->load->model('pegawai/Cuti_Model', 'cuti');

		return [
			'years'     => $years,
			'data'      => $this->from != 'external'
				? $this->cuti->populateRemainder($years, null, [$this->config->item('tbl_users') . '.id' => $id_user])
				: null,
			'main_body' => 'cuti/sisa_personal',
		];
	}

	protected function where_by_user()
	{
		if (is_kepegawaian() || is_operator()) {
			// full access — no additional where clauses
		} else if (is_pejabat_cuti()) {
			$jabatan = $this->config->item('jabatan_ketua');
			$this->whereVerification[] = "(jabatan_atasan_id = {$jabatan} OR jabatan_pejabat_id = {$jabatan})";
			$this->whereVerification[] = '(status IN (' . $this->config->item('status_verify_pejabat') . ', ' . $this->config->item('status_verify_atasan') . '))';
		} else if (is_atasan_cuti()) {
			$temp = [];
			if (in_array($this->user->jabatan_id, jabatan_atasan_cuti())) {
				$temp[] = $this->user->jabatan_id;
			}
			if ($this->ion_auth_model->in_group([$this->config->item('group_plt_ketua')], $this->user->id)) {
				$temp[] = $this->config->item('jabatan_ketua');
			}
			if ($this->ion_auth_model->in_group([$this->config->item('group_plt_wakil_ketua')], $this->user->id)) {
				$temp[] = $this->config->item('jabatan_wakil_ketua');
			}
			if ($this->ion_auth_model->in_group([$this->config->item('group_plt_panitera')], $this->user->id)) {
				$temp[] = $this->config->item('jabatan_panitera');
			}
			if ($this->ion_auth_model->in_group([$this->config->item('group_plt_sekretaris')], $this->user->id)) {
				$temp[] = $this->config->item('jabatan_sekretaris');
			}

			$jabatan = implode(', ', $temp);
			$this->whereVerification[] = "(jabatan_atasan_id IN ({$jabatan}))";
			$this->whereVerification[] = '(status = ' . $this->config->item('status_verify_atasan') . ')';
		} else {
			$this->whereVerification[] = ['user_id' => $this->user->id];
		}
	}

	protected function isLoggedIn()
	{
		if (!$this->ion_auth->logged_in()) {
			if ($this->input->is_ajax_request()) {
				return ['not_logged_in' => true];
			}
			redirect('site/login');
		}
		return true;
	}

	public function check_gateway()
	{
		return $this->set_content_type(check_gateway());
	}

	function save($id = null)
	{
		if (($result = $this->isLoggedIn()) !== true) {
			return $this->set_content_type($result);
		}

		$this->enableTest = $this->config->item('enable_test');

		$this->vars = [
			'main_body' => $this->mainBody,
			'title'     => ($id ? 'Simpan' : 'Perbarui') . ' Data',
			'message'   => '',
		];

		$result = $this->prepare_form($id);
		if ($result && $result['status']) {
			if ($this->input->is_ajax_request()) {
				return $this->redirectAjax($result);
			}
		}

		$this->load->vars($this->vars);

		return $this->viewAjax('widgets/form', [
			'message' => $this->vars['message'],
		]);
	}

	function update_value($column)
	{
		if (!$this->ion_auth->logged_in()) {
			return $this->set_content_type(null);
		}

		if ($this->input->post('ids')) {
			$this->form_validation->set_rules('value', 'Isian', 'trim|max_length[255]');
			if ($this->form_validation->run()) {
				$value = $this->input->post('value', TRUE);
				$this->model->update_column($this->input->post('ids'), $value === '' ? null : $value, $column);
				return $this->set_content_type(['message' => 'Berhasil menyimpan data', 'status' => true]);
			} else {
				return $this->set_content_type(['message' => my_validation_errors()]);
			}
		}

		return $this->set_content_type(['message' => 'Terjadi kesalahan, silakan refresh browser dan ulangi kembali']);
	}

	function update_value_disposisi($column)
	{
		$allowEditable = $this->input->post('allowEditable') === true || $this->input->post('allowEditable') === 'true';

		if (!$this->ion_auth->logged_in() && !$allowEditable) {
			return $this->set_content_type(null);
		}

		if ($this->input->post('ids')) {
			$this->load->model('settings/Transaction_Model', 'trx');
			$this->trx->update_column($this->input->post('ids'), $this->input->post('value') ?: null, $column);
		}

		return $this->set_content_type([]);
	}

	protected function initFabItems()
	{
		$this->vars['fabItems']      = [];
		$this->vars['notifFabItems'] = [];
	}
}

class Auth_Controller extends Core_Controller
{
	/** Set to TRUE in child controller to also check resource ownership */
	protected $check_ownership = FALSE;

	/** The resource type to check ownership for (e.g. 'posts') */
	protected $ownership_resource = NULL;

	/** Whitelist of methods that don't require permission (per controller) */
	protected $public_methods = [];

	/** Common public methods for data retrieval controllers */
	protected $common_public_methods = [
		'profile',
		'jabatan',
		'addresses',
		'misc',
		'pegawai_list',
		'perkara_list',
		'get_list',
		'get_performance',
		'get_statistic',
		'get_autocomplete_values',
		'get_reports',
		'search',
		'view',
		'save',
		'delete',
		'upload',
		'process',
		'toggle_status',
		'update_value',
		'update_value_disposisi',
		'reservation_list',
		'create_user',
		'update_profil',
		'update_group',
		'activate',
		'deactivate',
		'change_password',
	];

	protected $allowed_file_types = array('pdf');

	public function __construct()
	{
		parent::__construct();
		// $this->_check_access();
	}

	protected function _check_access()
	{
		// Get current method
		$method = $this->router->fetch_method();

		// Merge controller-specific whitelist with common methods
		$all_public_methods = array_merge($this->public_methods, $this->common_public_methods);

		$user = $this->ion_auth->user()->row();

		if (!$user) {
			// Allow through only if method is truly public (no login required)
			if (in_array($method, $all_public_methods)) {
				return;
			}

			if ($this->input->is_ajax_request()) {
				$this->output
					->set_status_header(401)
					->set_content_type('application/json')
					->set_output(json_encode(['error' => 'Unauthorized - Please login']));
				exit;
			}

			// set_toast('Silakan login terlebih dahulu untuk mengakses halaman ini.', '', 'bg-danger');
			// Redirect straight to the standalone login page rather than
			// the homepage. The intended destination is already captured
			// in session['redirect'] by Core_Controller's constructor
			// (which runs before this method, on every request) — Site::
			// login() reads it back via _consume_redirect_session() once
			// the user actually logs in.
			redirect('site/login');
		}

		// Whitelisted methods skip permission checks but still require login (checked above).
		if (in_array($method, $all_public_methods)) {
			return;
		}

		$user_id = $user->id;

		// Skip automatic permission detection for Download controller —
		// it handles its own permission checking.
		$controller = $this->router->fetch_class();
		if ($controller === 'download') {
			return;
		}

		// Auto-detect permission from URL path.
		// e.g. "settings/access/index" → "settings.access"
		$uri_string = $this->uri->uri_string();

		// Remove query string if present
		$uri_string = strtok($uri_string, '?');

		if (empty($uri_string)) {
			return;
		}

		$segments = explode('/', trim($uri_string, '/'));

		// Remove 'index' from the end if present (default method)
		if (end($segments) === 'index') {
			array_pop($segments);
		}

		$perm = implode('.', $segments);

		// 1. Group-based RBAC
		// if ($this->permission->user_has_permission($user_id, $perm)) {
		// 	return;
		// }

		// // 2. Ownership-based ReBAC
		// if ($this->check_ownership && $this->ownership_resource) {
		// 	$resource_id = $this->input->get_post('id')
		// 		? $this->input->get_post('id')
		// 		: $this->uri->segment(3);

		// 	if ($resource_id && isset($this->permission) && $this->permission->user_owns_resource(
		// 		$user_id,
		// 		$this->ownership_resource,
		// 		$resource_id
		// 	)) {
		// 		return;
		// 	}
		// }

		if ($this->input->is_ajax_request()) {
			$this->output
				->set_status_header(403)
				->set_content_type('application/json')
				->set_output(json_encode(['error' => 'Forbidden - Missing permission: ' . $perm]));
			exit;
		}

		set_toast('Anda tidak memiliki izin untuk mengakses halaman ini.', 'Akses Ditolak', 'bg-danger');
		redirect('/');
	}

	// Validation callback for file upload
	public function validate_file_upload($str)
	{
		foreach (array_keys($this->fileFolders) as $field) {
			if (empty($_FILES[$field]['name'])) {
				$this->form_validation->set_message('validate_file_upload', 'File tidak boleh kosong.');
				return FALSE;
			}

			if ($_FILES[$field]['error'] != 0) {
				$this->form_validation->set_message('validate_file_upload', 'Terjadi kesalahan saat mengunggah file.');
				return FALSE;
			}

			$allowed_types = isset($this->fileAllowedTypes[$field]) ? $this->fileAllowedTypes[$field] : ['pdf'];
			if (!in_array(strtolower(pathinfo($_FILES[$field]['name'])['extension']), $allowed_types)) {
				$allowed_extensions = implode(', ', $allowed_types);
				$this->form_validation->set_message('validate_file_upload', "Tipe file salah. Hanya tipe $allowed_extensions yang diizinkan.");
				return FALSE;
			}
		}

		return TRUE;
	}

	function view_notif()
	{
		if (!$this->uri->segment(3) || !$this->uri->segment(4)) {
			return;
		}

		// BUG FIX: pagination library was used here but its load was commented
		// out in __construct(). Load it locally so $this->pagination is available.
		$this->load->library('pagination');

		$this->load->model('whatsapp/Queue_Model', 'queue');
		$where = ['where' => [
			[
				'perkara_id' => $this->uri->segment(3),
				'target' => ENVIRONMENT == 'development' ? WA_TEST_TARGET : $this->uri->segment(4),
			]
		]];
		$config = $this->pagination->set([
			'base_url' => base_url('whatsapp/log/index'),
			'total_rows' => $this->queue->num_rows($where),
			'per_page' => 10,
		]);

		$data = $this->queue->find($where, $config['offset'], $config['per_page']);

		$this->vars = [
			'main_body' => 'notifikasi/riwayat',
			'data' => $data,
			'offset' => $config['offset'] + 1,
			'selectedId' => $this->uri->segment(5),
		];

		$this->load->vars($this->vars);

		if ($this->input->is_ajax_request()) {
			return $this->viewAjax('notifikasi/riwayat', [
				'size' => 'modal-lg',
				'title' => 'Riwayat Notifikasi',
			]);
		}

		$this->load->view('layout');
	}

	protected function set_content_type($data)
	{
		if (!$this->ion_auth->logged_in()) {
			$data = ['not_logged_in' => true];
		}

		return parent::set_content_type($data);
	}

	protected function viewAjax($view, $data = [])
	{
		if (!$this->ion_auth->logged_in()) {
			return $this->set_content_type(null);
		}

		return parent::viewAjax($view, $data);
	}

	/* modul pegawai */
	protected function prepareData()
	{
		$data = [
			'username' => $this->input->post('identity'),
			'nama_lengkap' => $this->input->post('nama_lengkap'),
			'gelar_depan' => $this->input->post('gelar_depan'),
			'gelar_belakang' => $this->input->post('gelar_belakang'),
			'birth_place_code' => $this->input->post('birth_place_code'),
			'birth_date' => $this->input->post('birth_date'),
			'jabatan_id' => $this->input->post('jabatan_id'),
			'nip' => !is_honorer($this->input->post('jabatan_id')) && !is_outsourcing($this->input->post('jabatan_id')) ? $this->input->post('nip') : (str_replace('-', '', $this->input->post('birth_date') . $this->input->post('start_date'))),
			'golongan_ruang_id' => !is_honorer($this->input->post('jabatan_id')) && !is_outsourcing($this->input->post('jabatan_id')) ? $this->input->post('golongan_ruang_id') : null,
			'start_date' => $this->input->post('start_date'),
			'struktur_organisasi_id' => isset(get_or_set_jabatan_struktur()[$this->input->post('jabatan_id')]) ? get_or_set_jabatan_struktur()[$this->input->post('jabatan_id')] : $this->input->post('struktur_organisasi_id'),
			'tingkat_pendidikan_id' => $this->input->post('tingkat_pendidikan_id'),
			'jenis_kelamin' => $this->input->post('jenis_kelamin'),
			'nik' => $this->input->post('nik'),
			'email' => $this->input->post('email'),
			'phone' => $this->input->post('phone'),
		];

		return $data;
	}

	protected function prepareUserValidation($user = null)
	{
		$tables = $this->config->item('tables', 'ion_auth');
		if (!$user) {
			$identity_column = $this->config->item('identity', 'ion_auth');

			if ($identity_column !== 'email') {
				$this->form_validation->set_rules('identity', $this->lang->line('create_user_validation_identity_label'), 'required|verify_identity|is_unique[' . $tables['users'] . '.' . $identity_column . ']');
			}

			$this->form_validation->set_rules('password', $this->lang->line('create_user_validation_password_label'), 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|matches[password_confirm]');
			$this->form_validation->set_rules('password_confirm', $this->lang->line('create_user_validation_password_confirm_label'), 'required');
		} else {
			$this->form_validation->set_rules('identity', $this->lang->line('create_user_validation_identity_label'), 'required|verify_identity');
		}

		$this->form_validation->set_rules('nama_lengkap', $this->lang->line('create_user_validation_fname_label'), 'trim|required');
		$this->form_validation->set_rules('gelar_depan', 'Gelar Depan', 'trim');
		$this->form_validation->set_rules('gelar_belakang', 'Gelar Belakang', 'trim');
		$this->form_validation->set_rules('birth_place_code', 'Tempat Lahir', ($user ? 'required|' : '') . 'trim');
		$this->form_validation->set_rules('birth_date', 'Tanggal Lahir', ($user ? 'required|' : '') . 'trim');
		$this->form_validation->set_rules('jabatan_id', 'Jabatan', 'required');

		// BUG FIX: $jabatan was only set inside the if-block but $jabatan->struktur_organisasi_id
		// was referenced inside it too, so this was actually fine — but initialise $jabatan to
		// null before the block to make the scope explicit and prevent any edge-case undefined
		// variable notice if the block is ever refactored.
		$jabatan = null;
		if ($this->input->post('jabatan_id')) {
			$this->load->model('pegawai/Jabatan_Model', 'jabatan');
			$jabatan = $this->jabatan->findOne($this->input->post('jabatan_id'));
			$this->form_validation->set_rules(
				'struktur_organisasi_id',
				'Struktur Organisasi',
				($user && $jabatan && empty($jabatan->struktur_organisasi_id) ? 'required|' : '') . 'trim'
			);
		}

		if (!is_honorer($this->input->post('jabatan_id')) && !is_outsourcing($this->input->post('jabatan_id'))) {
			$this->form_validation->set_rules('nip', 'NIP', ($user ? 'required|' : '') . 'trim|verify_nip');
		}
		if (!is_honorer($this->input->post('jabatan_id')) && !is_outsourcing($this->input->post('jabatan_id'))) {
			$this->form_validation->set_rules('golongan_ruang_id', 'Golongan Ruang', ($user ? 'required|' : '') . 'trim');
		}
		$this->form_validation->set_rules('tingkat_pendidikan_id', 'Pendidikan Terakhir', ($user ? 'required|' : '') . 'trim');
		$this->form_validation->set_rules('nik', 'NIK', 'trim|verify_nik');
		$this->form_validation->set_rules('email', $this->lang->line('create_user_validation_email_label'), ($user ? '' : 'is_unique[' . $tables['users'] . '.email]|') . 'trim|required|valid_email');
		$this->form_validation->set_rules('phone', $this->lang->line('create_user_validation_phone_label'), ($user ? 'required|' : '') . 'trim');

		if ($user && (!$user->photo || !file_exists($this->config->item('folder_root_upload')  . '/photo/' . $user->photo)) && empty($_FILES['photo']['name'])) {
			$this->form_validation->set_rules('photo', 'Foto', 'required');
		}

		if (!$this->config->item('allow_empty_ttd') && $user && (!$user->ttd || !file_exists($this->config->item('folder_root_upload')  . '/ttd/' . $user->ttd)) && empty($_FILES['ttd']['name'])) {
			$this->form_validation->set_rules('ttd', 'File Ttd', 'required');
		}

		if ($this->input->post('status_p3k')) {
			$this->form_validation->set_rules('tmt_p3k', 'TMT P3K', 'required');
		}
	}

	protected function prepareUserForm($user = null)
	{
		$this->load->model('pegawai/P3k_Model', 'p3k');
		$p3kData = $user ? $this->p3k->findOne($user->id, true) : null;

		$this->vars['form']['identity'] = [
			'name' => 'identity',
			'value' => $this->form_validation->set_value('identity', $user ? $user->username : ($this->config->item('enable_test') ? 'user' . time() : null)),
			'placeholder' => lang('create_user_identity_label'),
			'label' => lang('create_user_identity_label') . ' (huruf kecil tanpa spasi)',
		];

		if (!$user) {
			$min_pwd_length = $this->config->item('min_password_length', 'ion_auth');
			$this->vars['form']['password'] = [
				'type' => 'form_password',
				'name' => 'password',
				'pattern' => '^.{' . $min_pwd_length . '}.*$',
				'placeholder' => lang('edit_user_password_label'),
				'value' => $this->config->item('enable_test') ? '12345678' : null,
			];

			$this->vars['form']['password_confirm'] = [
				'type' => 'form_password',
				'name' => 'password_confirm',
				'pattern' => '^.{' . $min_pwd_length . '}.*$',
				'class' => 'form-control',
				'placeholder' => lang('edit_user_password_confirm_label'),
				'value' => $this->config->item('enable_test') ? '12345678' : null,
			];
		}

		$this->vars['form']['nama_lengkap'] = [
			'icon'  => 'user',
			'name'  => 'nama_lengkap',
			'value' => $this->form_validation->set_value('nama_lengkap', $user ? $user->nama_lengkap : ($this->config->item('enable_test') ? 'Akun ' . time() : null)),
			'placeholder' => lang('edit_user_fname_label'),
		];

		$this->vars['form']['gelar_depan'] = [
			'name'  => 'gelar_depan',
			'value' => $this->form_validation->set_value('gelar_depan', $user ? $user->gelar_depan : ($this->config->item('enable_test') ? 'H.' : null)),
			'placeholder' => 'Gelar Depan',
		];

		$this->vars['form']['gelar_belakang'] = [
			'name'  => 'gelar_belakang',
			'value' => $this->form_validation->set_value('gelar_belakang', $user ? $user->gelar_belakang : ($this->config->item('enable_test') ? 'SH.' : null)),
			'placeholder' => 'Gelar Belakang',
		];

		$this->load->model('Wilayah_Model', 'wilayah');
		$this->vars['form']['birth_place_code'] = [
			'type' => 'form_dropdown',
			'name' => 'birth_place_code',
			'ajaxUrl' => base_url('site/get_wilayah'),
			'selectedValue' => $birth_place_code = $this->form_validation->set_value('birth_place_code', $user ? $user->birth_place_code : ($this->config->item('enable_test') ? '11.01.17' : null)),
			'selectedText' => $birth_place_code && ($wilayah = $this->wilayah->findOne($birth_place_code)) ? $wilayah->nama : null,
			'placeholder' => 'Tempat Lahir',
		];

		$this->vars['form']['birth_date'] = [
			'type' => 'form_datepicker',
			'name' => 'birth_date',
			'value' => $this->form_validation->set_value('birth_date', $user && $user->birth_date != '0000-00-00' ? $user->birth_date : ($this->config->item('enable_test') ? random_date_between('1965-01-01', '1999-01-01') : null)),
			'placeholder' => 'Tanggal Lahir',
			'disableWeekend' => false,
		];

		$this->load->model('settings/Ref_Model', 'ref');
		$this->vars['form']['jabatan_id'] = [
			'type' => 'form_dropdown',
			'name' => 'jabatan_id',
			'options' => $this->ref->findJabatan(),
			'selected' => ($jabatan_id = $this->form_validation->set_value('jabatan_id', $user ? $user->jabatan_id : ($this->config->item('enable_test') ? $this->config->item('jabatan_honorer') : null))),
			'placeholder' => 'Jabatan',
		];

		$this->vars['form']['status_p3k'] = [
			'type' => 'form_switcher',
			'name' => 'status_p3k',
			'label' => 'P3K?',
			'sublabel' => 'Ya',
			'value' => $this->form_validation->set_value('status_p3k', $this->input->post() ? ($this->input->post('status_p3k') === 'on' ? 1 : 0) : ($p3kData ? 1 : 0)),
		];
		$this->vars['form']['tmt_p3k'] = [
			'type' => 'form_datepicker',
			'name' => 'tmt_p3k',
			'value' => $this->form_validation->set_value('tmt_p3k', $p3kData ? $p3kData->tmt : null),
			'placeholder' => 'TMT P3K',
			'disableWeekend' => false,
			'visible' => ($this->input->post() ? $this->input->post('status_p3k') === 'on' : ($p3kData ? true : false)) || !empty($p3kData),
			'divClass' => 'p3k-only',
		];

		$showAtasan = false;
		if ($jabatan_id) {
			$this->load->model('pegawai/Jabatan_Model', 'jabatan');
			$jabatan = $this->jabatan->findOne($jabatan_id);
			$showAtasan = empty($jabatan->struktur_organisasi_id);
		}

		$this->vars['form']['struktur_organisasi_id'] = [
			'type' => 'form_dropdown',
			'name' => 'struktur_organisasi_id',
			'options' => $this->ref->findJabatan(),
			'selected' => $this->form_validation->set_value('struktur_organisasi_id', $user ? $user->struktur_organisasi_id : ($this->enableTest ? 11 : null)),
			'label' => 'Struktur Organisasi',
			'visible' => $showAtasan,
			'divClass' => 'dependant-jabatan need-atasan',
			'placeholder' => 'Struktur Organisasi',
		];

		$this->vars['form']['start_date'] = [
			'type' => 'form_datepicker',
			'name' => 'start_date',
			'value' => $this->form_validation->set_value('start_date', $user ? $user->start_date : ($this->config->item('enable_test') ? random_date_between('2000-01-01', '2020-01-01') : null)),
			'placeholder' => 'TMT Golongan / Tanggal Mulai Bekerja',
		];

		$this->vars['form']['nip'] = [
			'type' => 'number',
			'icon' => 'id-card',
			'name' => 'nip',
			'placeholder' => 'NIP',
			'value' => $this->form_validation->set_value('nip', $user ? $user->nip : ($this->config->item('enable_test') ? str_replace('-', '', random_date_between('1965-01-01', '1999-01-01') . random_date_between('2000-01-01', '2020-01-01') . '01') : null)),
			'visible' => !is_honorer($jabatan_id) && !is_outsourcing($jabatan_id),
			'divClass' => 'dependant-jabatan pegawai-only',
		];

		$this->vars['form']['golongan_ruang_id'] = [
			'type' => 'form_dropdown',
			'name' => 'golongan_ruang_id',
			'options' => $this->ref->findGolonganRuang(),
			'selected' => $this->form_validation->set_value('golongan_ruang_id', $user ? $user->golongan_ruang_id : ($this->config->item('enable_test') ? 9 : null)),
			'visible' => !is_honorer($jabatan_id) && !is_outsourcing($jabatan_id),
			'divClass' => 'dependant-jabatan pegawai-only',
			'placeholder' => 'Golongan Ruang',
		];

		$this->vars['form']['tingkat_pendidikan_id'] = [
			'type' => 'form_dropdown',
			'name' => 'tingkat_pendidikan_id',
			'options' => $this->ref->findTingkatPendidikan(),
			'selected' => $this->form_validation->set_value('tingkat_pendidikan_id', $user ? $user->tingkat_pendidikan_id : ($this->config->item('enable_test') ? 8 : null)),
			'placeholder' => 'Pendidikan Terakhir',
		];

		$this->vars['form']['jenis_kelamin'] = [
			'type' => 'form_dropdown',
			'name' => 'jenis_kelamin',
			'options' => [null => 'Jenis Kelamin', 'L' => 'Laki-Laki', 'P' => 'Perempuan'],
			'selected' => $this->form_validation->set_value('jenis_kelamin', $user ? $user->jenis_kelamin : ($this->config->item('enable_test') ? 'L' : null)),
			'placeholder' => 'Jenis Kelamin',
		];

		$this->vars['form']['nik'] = [
			'type' => 'number',
			'icon' => 'id-card',
			'name' => 'nik',
			'placeholder' => 'NIK',
			'value' => $this->form_validation->set_value('nik', $user ? $user->nik : ($this->config->item('enable_test') ? random_int_length(16) : null)),
		];

		$this->vars['form']['email'] = [
			'icon'  => 'at',
			'name' => 'email',
			'value' => $this->form_validation->set_value('email', $user ? $user->email : ($this->config->item('enable_test') ? 'user_' . time() . '@gmail.com' : null)),
			'placeholder' => lang('create_user_email_label'),
		];

		$this->vars['form']['phone'] = [
			'icon'  => 'phone',
			'name'  => 'phone',
			'value' => $this->form_validation->set_value('phone', $user ? $user->phone : ($this->config->item('enable_test') ? '089' . random_int_length(8) : null)),
			'placeholder' => lang('edit_user_phone_label'),
		];

		$this->vars['form']['photo'] = [
			'type' => 'form_dropzone',
			'name' => 'photo',
			'label' => 'Foto Pegawai (jpg, png)',
			'accept' => '.jpg,.png'
		];

		$this->vars['form']['existing_photo'] = [
			'type' => 'form_info',
			'title' => 'Foto Saat Ini',
			'info' => isset($user->photo) && $user->photo ? '<a href="' . file_url('photo', $user->photo) . '" target="_blank">' . $user->photo . '</a> (' . format_filesize(isset($user->photo_size) ? $user->photo_size : 0) . ')' : '',
			'visible' => isset($user->photo) && $user->photo
		];

		$this->vars['form']['ttd'] = [
			'type' => 'form_dropzone',
			'name' => 'ttd',
			'label' => 'Unggah Foto Ttd',
			'accept' => '.jpg,.png',
			'visible' => !$this->config->item('allow_empty_ttd'),
		];
	}

	protected function prepareRedirectView($id_user, $data)
	{
		$this->prepareProfile($id_user);
		return $this->viewAjax('widgets/profile_full', array_merge($data, [
			'showOnModal' => $this->from !== 'profil',
			'title' => 'Profil Pegawai',
			'size' => 'modal-lg',
			'redirect' => base_url('kepegawaian/pegawai/profile/' . $id_user),
		]));
	}

	/* modul password */
	function change_password($id = null)
	{
		if ($this->from != 'admin') {
			$this->form_validation->set_rules('old', $this->lang->line('change_password_validation_old_password_label'), 'required');
		}
		$this->form_validation->set_rules('new', $this->lang->line('change_password_validation_new_password_label'), 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|matches[new_confirm]');
		$this->form_validation->set_rules('new_confirm', $this->lang->line('change_password_validation_new_password_confirm_label'), 'required');

		$this->load->model('pegawai/Pegawai_Model', 'pegawai');
		$pegawai = $this->pegawai->findOne($id);

		$this->vars = [
			'title' => $this->lang->line('reset_password_heading'),
			'main_body' => 'widgets/form',
			'message' => '',
			'backUrl' => $this->from != 'admin' ? base_url('kepegawaian/pegawai/profile/' . $pegawai->id) : '',
		];

		$this->getProfileData($pegawai);

		if (isset($_POST) && !empty($_POST)) {
			if ($this->form_validation->run() === TRUE) {
				if ($this->from != 'admin' ? $this->ion_auth->change_password($pegawai->username, $this->input->post('old'), $this->input->post('new')) : $this->ion_auth->update($id, ['password' => $this->input->post('new')])) {
					return $this->prepareRedirectView($id, [
						'status' => true,
						'message' => "Berhasil mengubah kata sandi {$pegawai->nama_pegawai}",
					]);
				}
			}

			$this->vars['message'] = my_validation_errors();
		}

		$min_pwd_length = $this->config->item('min_password_length', 'ion_auth');
		$this->vars['form']['old_password'] = [
			'type' => 'form_password',
			'name' => 'old',
			'label' => 'Kata Sandi Lama',
			'placeholder' => lang('change_password_old_password_label'),
			'visible' => $this->from != 'admin'
		];
		$this->vars['form']['new_password'] = [
			'type' => 'form_password',
			'name' => 'new',
			'label' => 'Kata Sandi Baru',
			'placeholder' => sprintf(lang('change_password_new_password_label'), $min_pwd_length),
		];
		$this->vars['form']['new_password_confirm'] = [
			'type' => 'form_password',
			'name' => 'new_confirm',
			'placeholder' => lang('change_password_new_password_confirm_label'),
		];

		$this->load->vars($this->vars);

		return $this->viewAjax('widgets/form', [
			'status' => false,
			'message' => $this->vars['message'],
		]);
	}

	/**
	 * Generic method to get distinct values for any column for autocomplete.
	 * Supports both query parameters and URI segments.
	 */
	public function get_autocomplete_values($column = null)
	{
		$term = $this->input->get('term') ? trim($this->input->get('term')) : '';

		if ($column === null) {
			$column = $this->input->get('column') ? trim($this->input->get('column')) : '';
		}

		if (empty($column)) {
			$this->output
				->set_content_type('application/json')
				->set_output(json_encode([]));
			return;
		}

		$result = $this->model->getDistinctColumnValues($column, $term);

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($result));
	}

	/** Modul Jobdesc */
	function save_jobdesc_pegawai($id_user = null)
	{
		return $this->prepareFormJobdesc($id_user, 'pegawai');
	}

	protected function prepareFormJobdesc($id, $type = 'jabatan')
	{
		if ($type == 'pegawai') {
			$jobdesc = $this->jobdesc->findOneByUser($id);
			$user_id = $id;
			$id = $jobdesc ? $jobdesc->id : null;

			$this->form_validation->set_rules('user_id', 'Pegawai', 'required|trim');

			$this->getProfileData($this->pegawai->findOne($user_id));
		} else {
			$jobdesc = $id ? $this->jobdesc->findOne($id) : null;
			$user_id = null;

			$this->form_validation->set_rules('jabatan_id', 'Jabatan', 'required|trim|check_jabatan_jobdesc_exists');
		}

		$actionText = $id ? 'memperbarui' : 'menambah';

		$this->form_validation->set_rules('description', 'Uraian Tugas', 'required|trim');

		$this->vars['title'] = ($id ? 'Update' : 'Tambah') . ' Uraian Tugas';
		$this->vars['main_body'] = 'widgets/form';
		$this->vars['message'] = '';

		if (isset($_POST) && !empty($_POST)) {
			if ($this->form_validation->run() === TRUE) {
				$data = [
					'description' => $this->input->post('description'),
					'jabatan_id' => $this->input->post('jabatan_id') ?: null,
					'user_id' => $this->input->post('user_id') ?: null,
				];

				if ($id ? $this->jobdesc->update($id, $data) : $this->jobdesc->insert($data)) {
					if ($type == 'pegawai') {
						$this->prepareProfile($user_id);
						return $this->viewAjax('widgets/profile_full', [
							'showOnModal' => true,
							'title' => 'Profil Pegawai',
							'size' => 'modal-lg',
							'status' => true,
							'message' => "Berhasil {$actionText} uraian tugas",
						]);
					}

					return $this->redirectAjax([
						'redirect' => base_url('kepegawaian/jobdesc/list_jobdesc/' . $data['jabatan_id']),
						'status' => true,
						'message' => "Berhasil {$actionText} uraian tugas",
					]);
				}

				$this->vars['message'] = 'Terjadi Kesalahan';
			}
			$this->vars['message'] = my_validation_errors();
		}

		$this->vars['form']['user_id'] = [
			'type' => 'form_hidden',
			'id_jobdesc' => $jobdesc ? $jobdesc->id : null,
			'user_id' => $user_id
		];

		$this->load->model('settings/Ref_Model', 'ref');
		$this->vars['form']['jabatan_id'] = [
			'type' => 'form_dropdown',
			'name' => 'jabatan_id',
			'options' => array_remove_duplicate($this->ref->findJabatan('id NOT IN (' . $this->config->item('jabatan_honorer') . ', ' . $this->config->item('jabatan_outsourcing') . ')'), array_remove_duplicate($this->jobdesc->getExistedJabatan(), ($jobdesc ? [$jobdesc->jabatan_id => $jobdesc->nama_jabatan] : []))),
			'selected' => $this->form_validation->set_value('jabatan_id', $jobdesc ? $jobdesc->jabatan_id : ($this->enableTest ? $this->config->item('jabatan_ketua') : null)),
			'placeholder' => 'Jabatan',
			'visible' => $type != 'pegawai',
		];

		$this->vars['form']['description'] = [
			'type' => 'form_summernote',
			'name' => 'description',
			'placeholder' => 'Uraian Tugas',
			'value' => $this->form_validation->set_value('description', $jobdesc ? $jobdesc->description : null),
		];

		$this->load->vars($this->vars);
		return $this->viewAjax('widgets/form');
	}
}

class Admin_Controller extends Auth_Controller
{
	public function __construct()
	{
		parent::__construct();

		$this->isAuthorized = check_auth(is_operator());
	}
}

class Profile_Controller extends Auth_Controller
{
	public $checkSisaCuti = true;
	protected $whereVerification = [];

	public function __construct()
	{
		parent::__construct();

		$this->load->model('pegawai/Alamat_Model', 'alamat');
		$this->load->model('pegawai/Employee_Data_Model', 'misc');
		// $this->load->model('pegawai/Cuti_Model', 'cuti');
		$this->load->model('pegawai/Pegawai_Model', 'pegawai');
		$this->load->model('pegawai/Jobdesc_Model', 'jobdesc');

		$this->setFrom('profil');
	}

	/** Modul Pegawai */
	function update_profil($id)
	{
		$this->indexUrl = base_url('kepegawaian/pegawai/profile');

		$this->isAuthorized = check_auth($this->user->id == $id, is_operator(), is_kepegawaian());

		$pegawai = $this->pegawai->findOne($id);

		$this->prepareUserValidation($pegawai);

		$this->vars['message'] = '';
		if (isset($_POST) && !empty($_POST)) {
			$valid = true;
			$filettd = '';
			if (!empty($_FILES['ttd']['name']) && delete_file('ttd', $pegawai->ttd)) {
				$upload = do_upload('ttd', 'ttd', $id, 'png|jpeg|jpg');
				if (($valid = $upload['success'])) {
					$filettd = $upload['filename'];
				} else {
					$this->vars['message'] = $upload['message'];
				}
			}

			$filephoto = '';
			if (!empty($_FILES['photo']['name']) && delete_file('photo', $pegawai->photo)) {
				$upload = do_upload('photo', 'photo', $id, 'png|jpeg|jpg');
				if (($valid = $upload['success'])) {
					$filephoto = $upload['filename'];
				} else {
					$this->vars['message'] = $upload['message'];
				}
			}

			if ($this->form_validation->run() === TRUE && $valid) {
				$data = $this->prepareData();
				if ($filettd) {
					$data['ttd'] = $filettd;
				}
				if ($filephoto) {
					$data['photo'] = $filephoto;
				}

				if ($this->pegawai->update($id ?: $this->user->id, $data)) {
					$this->processP3kData($id, $this->input->post('tmt_p3k'));
					return $this->prepareRedirectView($id, [
						'status' => true,
						'message' => "Berhasil memperbarui data {$pegawai->nama_pegawai}",
					]);
				}
			}

			$this->vars['message'] = my_validation_errors();
		}

		$this->vars['backUrl'] = base_url('kepegawaian/pegawai/profile/' . $pegawai->id);
		$this->vars['title'] = 'Update Data';
		$this->vars['pegawai'] = $pegawai;

		$this->prepareUserForm($pegawai);

		$this->load->vars($this->vars);

		return $this->viewAjax('kepegawaian/pegawai/form', [
			'status' => false,
			'message' => $this->vars['message'],
			'size' => 'modal-md',
		]);
	}

	/** Modul Misc */
	function misc_add($type, $id_user = null)
	{
		$types = ['lhkpn', 'lhkasn', 'penghargaan', 'pendidikan', 'jabatan'];
		if (in_array($type, $types)) {
			$this->_prepareMiscForm(null, $type, $id_user);
		}
	}

	function misc_update($id)
	{
		$misc  = $this->misc->findOne($id);
		$types = ['lhkpn', 'lhkasn', 'penghargaan', 'pendidikan', 'jabatan'];
		$this->_prepareMiscForm($misc, $types[$misc->type], $misc->user_id);
	}

	function misc_delete($id)
	{
		$misc  = $this->misc->findOne($id);

		delete_file($this->config->item('folder_misc_pegawai'), $misc->file);

		$status = $this->misc->delete($id);

		$this->isAuthorized = check_auth($misc->user_id == $this->user->id, is_operator(), is_kepegawaian());

		$this->prepareProfile($misc->user_id);

		return $this->viewAjax('widgets/profile_full', [
			'showOnModal' => true,
			'title' => 'Profil Pegawai',
			'size' => 'modal-lg',
			'status' => $status,
			'message' => ($status ? "Berhasil" : "Gagal") . " menghapus {$misc->title} ({$misc->year})",
		]);
	}

	protected function processP3kData($id, $tmt)
	{
		$this->load->model('pegawai/P3k_Model', 'p3k');
		$p3kData = $this->p3k->findOne($id, true);

		if ($this->input->post('status_p3k') === 'on') {
			if ($p3kData) {
				$this->p3k->update($id, [
					'tmt' => $tmt,
				]);
			} else {
				$this->p3k->insert([
					'id' => $id,
					'tmt' => $tmt,
				]);
			}
		} else if ($p3kData) {
			$this->p3k->delete($id);
		}
	}

	private function _prepareMiscForm($misc, $type, $id_user = null)
	{
		$this->indexUrl = base_url('kepegawaian/pegawai/profile');

		// $this->isAuthorized = check_auth($id_user == $this->user->id, is_operator(), is_kepegawaian());

		$id_user = $id_user ?: $this->user->id;

		$titles = [
			'lhkpn' => null,
			'lhkasn' => null,
			'penghargaan' => 'Nama Penghargaan',
			'pendidikan' => 'Tingkat Pendidikan',
			'jabatan' => 'Nama Jabatan',
		];
		$descriptions = [
			'lhkpn' => null,
			'lhkasn' => null,
			'penghargaan' => null,
			'pendidikan' => 'Nama Sekolah / Universitas',
			'jabatan' => 'Satuan Kerja',
		];

		if (in_array($type, ['lhkpn', 'lhkasn'])) {
			$this->form_validation->set_rules('user_id', 'Pegawai', 'required|check_misc_year_exists');
		}
		$this->form_validation->set_rules('type', 'Tipe', 'required|trim');
		if (in_array($type, ['pendidikan', 'penghargaan', 'jabatan'])) {
			$this->form_validation->set_rules('title', $titles[$type], 'required|trim');
		}
		if (in_array($type, ['pendidikan', 'jabatan'])) {
			$this->form_validation->set_rules('description', $descriptions[$type], 'required|trim');
		}
		$this->form_validation->set_rules('year', 'Tahun', 'required|trim');

		$this->vars = [
			'title' => ($misc ? 'Update' : 'Tambah') . ' Data',
			'main_body' => 'widgets/form',
			'message' => '',
			'backUrl' => base_url('kepegawaian/pegawai/profile/' . ($id_user ?: $this->user->id)),
		];

		$this->getProfileData($this->pegawai->findOne($id_user));

		// Configure file folder for misc file field
		$this->fileFolders = ['file' => $this->config->item('folder_misc_pegawai')];

		if (isset($_POST) && !empty($_POST)) {
			if (in_array($type, ['lhkpn', 'lhkasn', 'penghargaan']) && ((!$misc || (!$misc->file || !file_url($this->config->item('folder_misc_pegawai'), $misc->file))) && empty($_FILES['file']['name']))) {
				$this->form_validation->set_rules('file', 'File', 'required|trim');
			}

			$valid = true;
			$filename = null;
			if (in_array($type, ['lhkpn', 'lhkasn', 'penghargaan']) && !empty($_FILES['file']['name'])) {
				if ($misc && $misc->file) {
					delete_file($this->config->item('folder_misc_pegawai'), $misc->file);
				}

				$upload = do_upload('file', $this->config->item('folder_misc_pegawai'), $type . '_' . $id_user . '_' . time());
				if (($valid = $upload['success'])) {
					$filename = $upload['filename'];
				} else {
					$this->vars['message'] = $upload['message'];
				}
			}

			if ($this->form_validation->run() === TRUE && $valid) {
				$data = [
					'user_id' => $id_user,
					'type' => $this->input->post('type'),
					'year' => $this->input->post('year'),
					'title' => $this->input->post('title') ?: getMiscType($this->input->post('type')),
					'description' => $this->input->post('description'),
				];

				if ($filename) {
					$data['file'] = $filename;
				}

				if ($misc ? $this->misc->update($misc->id, $data) : $this->misc->insert($data)) {
					// $this->prepareProfile($id_user);
					// return $this->viewAjax('widgets/profile_full', [
					// 	'showOnModal' => true,
					// 	'title' => 'Profil Pegawai',
					// 	'size' => 'modal-lg',
					// 	'status' => true,
					// 	'message' => "Berhasil menyimpan data",
					// ]);
					return $this->redirectAjax([
						'redirect' => $this->indexUrl,
						'status' => true,
						'message' => "Berhasil menyimpan data",
					]);
				}
			}

			$this->vars['message'] = my_validation_errors();
		}

		$this->vars['form']['user_id'] = [
			'type' => 'form_hidden',
			'user_id' => $id_user,
			'id_misc' => $misc ? $misc->id : null,
			'misc_type' => $type,
		];

		$options = [
			'lhkpn' => [0 => 'LHKPN'],
			'lhkasn' => [1 => 'SPT'],
			'penghargaan' => [2 => 'Penghargaan'],
			'pendidikan' => [3 => 'Pendidikan'],
			'jabatan' => [4 => 'Jabatan'],
		];

		$this->vars['form']['type'] = [
			'type' => 'form_dropdown',
			'name' => 'type',
			'options' => $options[$type],
			'selected' => $this->form_validation->set_value('type', $misc ? $misc->type : ($this->enableTest ? 1 : null)),
			'placeholder' => 'Tipe',
			'allowClear' => $type == 'lhkpn' || $type == 'lhkasn',
		];

		switch ($type) {
			case 'penghargaan':
			case 'jabatan':
				$this->vars['form']['title'] = [
					'name' => 'title',
					'placeholder' => $titles[$type],
					'value' => $this->form_validation->set_value('title', $misc ? $misc->title : null),
				];
				break;
			case 'pendidikan':
				$this->load->model('settings/Ref_Model', 'ref');
				$this->vars['form']['title'] = [
					'type' => 'form_dropdown',
					'name' => 'title',
					'options' => [null => 'Pilih Tingkat Pendidikan'] + array_to_assoc($this->ref->findTingkatPendidikan()),
					'selected' => $this->form_validation->set_value('title', $misc ? $misc->title : null),
					'placeholder' => 'Tingkat Pendidikan',
				];
				break;
		}

		$this->vars['form']['description'] = [
			'name' => 'description',
			'placeholder' => $descriptions[$type],
			'value' => $this->form_validation->set_value('description', $misc ? $misc->description : null),
			'visible' => in_array($type, ['pendidikan', 'jabatan']),
		];

		$this->vars['form']['year'] = [
			'type' => 'form_datepicker',
			'format' => 'yyyy',
			'name' => 'year',
			'value' => $this->form_validation->set_value('year', $misc ? $misc->year : date("Y", strtotime("-1 year"))),
			'placeholder' => 'Tahun',
		];

		$this->vars['form']['file'] = [
			'type' => 'form_dropzone',
			'name' => 'file',
			'label' => 'File (pdf)',
			'visible' => in_array($type, ['lhkpn', 'lhkasn', 'penghargaan']),
		];

		$this->vars['form']['existing_file'] = [
			'type' => 'form_info',
			'title' => 'File Saat Ini',
			'info' => isset($misc->file) && $misc->file ? '<a href="' . file_url($this->config->item('folder_misc_pegawai'), $misc->file) . '" target="_blank">' . $misc->file . '</a> (' . format_filesize(isset($misc->file_size) ? $misc->file_size : 0) . ')' : '',
			'visible' => isset($misc->file) && $misc->file
		];

		$this->load->vars($this->vars);

		if ($this->input->is_ajax_request()) {
			return $this->viewAjax('widgets/form', [
				'status' => false,
				'message' => $this->vars['message'],
			]);
		}

		$this->load->view('layout');
	}

	/** Modul Alamat */
	function address_add($id_user = null)
	{
		$this->isAuthorized = check_auth($id_user == $this->user->id, is_operator(), is_kepegawaian());

		$this->_prepareAddressForm(null, $id_user);
	}

	function address_update($id)
	{
		$alamat  = $this->alamat->findOne($id);

		$this->isAuthorized = check_auth($alamat->user_id == $this->user->id, is_operator(), is_kepegawaian());

		$this->_prepareAddressForm($alamat, $alamat->user_id);
	}

	function address_delete($id)
	{
		$alamat  = $this->alamat->findOne($id);
		$this->isAuthorized = check_auth($alamat->user_id == $this->user->id, is_operator(), is_kepegawaian());
		$status = $this->alamat->delete($id);

		$this->prepareProfile($alamat->user_id);

		return $this->viewAjax('widgets/profile_full', [
			'showOnModal' => true,
			'title' => 'Profil Pegawai',
			'size' => 'modal-lg',
			'status' => $status,
			'message' => ($status ? "Berhasil" : "Gagal") . " menghapus {$alamat->alamat_lengkap}",
		]);
	}

	private function _cutiNeedDataDukung($jenis_cuti_id)
	{
		return $jenis_cuti_id && $jenis_cuti_id != $this->config->item('cuti_tahunan');
	}

	private function _prepareAddressForm($alamat = null, $id_user = null)
	{
		$id_user = $id_user ?: $this->user->id;

		$this->form_validation->set_rules('kode_wilayah', 'Kelurahan/Kecamatan/Kabupaten/Kota', 'required');
		$this->form_validation->set_rules('alamat_lengkap', 'Alamat Lengkap', 'required|max_length[150]');
		$this->form_validation->set_rules('rt', 'RT', 'trim');
		$this->form_validation->set_rules('rw', 'RW', 'trim');
		$this->form_validation->set_rules('kode_pos', 'kode_pos', 'trim|max_length[5]');

		$this->vars = [
			'title' => ($alamat ? 'Update' : 'Tambah') . ' Alamat',
			'main_body' => 'widgets/form',
			'message' => '',
			'backUrl' => base_url('kepegawaian/pegawai/profile/' . ($id_user ?: $this->user->id)),
		];

		$this->getProfileData($this->pegawai->findOne($id_user));

		if (isset($_POST) && !empty($_POST)) {
			if ($this->form_validation->run() === TRUE) {
				$data = [
					'user_id' => $id_user,
					'kode_wilayah' => $this->input->post('kode_wilayah'),
					'rt' => $this->input->post('rt'),
					'rw' => $this->input->post('rw'),
					'kode_pos' => $this->input->post('kode_pos'),
					'alamat_lengkap' => $this->input->post('alamat_lengkap'),
				];

				if ($alamat ? $this->alamat->update($alamat->id, $data) : $this->alamat->insert($data)) {
					$this->prepareProfile($id_user);
					return $this->viewAjax('widgets/profile_full', [
						'showOnModal' => true,
						'title' => 'Profil Pegawai',
						'size' => 'modal-lg',
						'status' => true,
						'message' => "Berhasil menyimpan {$data['alamat_lengkap']}",
					]);
				}
			}

			$this->vars['message'] = my_validation_errors();
		}

		$this->load->model('Wilayah_Model', 'wilayah');
		$this->vars['form']['kode_wilayah'] = [
			'type' => 'form_dropdown',
			'name' => 'kode_wilayah',
			'ajaxUrl' => base_url('site/get_wilayah'),
			'placeholder' => 'Kelurahan/Kecamatan / Kabupaten / Kota',
			'selectedValue' => $place_code = $this->form_validation->set_value('kode_wilayah', $alamat ? $alamat->kode_wilayah : ($this->enableTest ? '11.01.17' : null)),
			'selectedText' => $place_code && ($wilayah = $this->wilayah->findOne($place_code)) ? $wilayah->nama : null,
		];

		$this->vars['form']['rt'] = [
			'type' => 'number',
			'name' => 'rt',
			'placeholder' => 'RT',
			'value' => $this->form_validation->set_value('rt', $alamat ? $alamat->rt : ($this->enableTest ? '04' : null)),
		];

		$this->vars['form']['rw'] = [
			'type' => 'number',
			'name' => 'rw',
			'placeholder' => 'RW',
			'value' => $this->form_validation->set_value('rw', $alamat ? $alamat->rw : ($this->enableTest ? '09' : null)),
		];

		$this->vars['form']['kode_pos'] = [
			'type' => 'number',
			'name' => 'kode_pos',
			'placeholder' => 'Kode Pos',
			'value' => $this->form_validation->set_value('kode_pos',  $alamat ? $alamat->kode_pos : ($this->enableTest ? '40611' : null)),
		];

		$this->vars['form']['alamat_lengkap'] = [
			'type' => 'form_textarea',
			'name' => 'alamat_lengkap',
			'placeholder' => 'Alamat Lengkap',
			'value' => $this->form_validation->set_value('alamat_lengkap', $alamat ? $alamat->alamat_lengkap : ($this->enableTest ? 'Ketintang baru' : null)),
		];

		$this->load->vars($this->vars);

		if ($this->input->is_ajax_request()) {
			return $this->viewAjax('widgets/form', [
				'status' => false,
				'message' => $this->vars['message'],
			]);
		}

		$this->load->view('layout');
	}

	/* Modul Cuti */
	function list_cuti($id_user)
	{
		$pegawai = $this->pegawai->findOne($id_user);

		$where = [['user_id' => $id_user]];

		$config = $this->pagination->set([
			'base_url' => base_url("{$this->from}/list_cuti/{$id_user}"),
			'total_rows' => $this->cuti->num_rows($where),
			'per_page' => 3,
			'cur_page_segment' => 4,
			'pagination_class' => 'pagination-partial my-0',
		]);
		$cuti = $this->cuti->find($where, 'tanggal_awal DESC', [], $config['offset'], $config['per_page']);

		$this->vars = [
			'title' => 'Riwayat Cuti',
			'showTitle' => false,
			'main_body' => 'cuti/index',
			'all' => $cuti,
			'pegawai' => $pegawai,
			'offset' => $config['offset'] + 1,
			'isValidForCuti' => !is_pegawai_invalid($pegawai),
			'id_user' => $id_user,
		];

		$this->load->vars($this->vars);

		if ($this->input->is_ajax_request()) {
			return $this->viewAjax('cuti/index');
		}

		$this->load->view('layout');
	}

	function add_cuti($id_user = null)
	{
		$this->isAuthorized = check_auth($id_user == $this->user->id, is_operator(), is_kepegawaian());

		$this->vars['title'] = 'Tambah Pengajuan Cuti';
		$this->vars['showTitle'] = false;
		$this->vars['main_body'] = 'cuti/form';
		$this->vars['message'] = '';
		$this->vars['backUrl'] = $id_user ? base_url('kepegawaian/pegawai/profile/' . $id_user) : '';

		$this->prepareCutiValidation();

		if (isset($_POST) && !empty($_POST)) {
			if ($this->form_validation->run() === TRUE) {
				$pegawai = $this->pegawai->findOne($this->input->post('user_id'));

				$data = [
					'user_id' => $pegawai->id,
					'jenis_cuti_id' => $this->input->post('jenis_cuti_id'),
					'tanggal_pengajuan' => $this->input->post('tanggal_pengajuan'),
					'tanggal_awal' => $this->input->post('tanggal_awal'),
					'tanggal_akhir' => $this->input->post('tanggal_akhir'),
					'lama_cuti' => count_working_days($this->input->post('tanggal_awal'), $this->input->post('tanggal_akhir')),
					'alasan_cuti' => $this->input->post('alasan_cuti'),
					'alamat_id' => $this->input->post('alamat_id'),
					'status' => $pegawai->jabatan_id == $this->config->item('jabatan_ketua') ? $this->config->item('status_validasi_kepegawaian') : $this->config->item('status_verify_kepegawaian'),
				];

				$valid = true;
				if (!empty($_FILES['data_dukung_cuti']['name'])) {
					$upload = do_upload('data_dukung_cuti', 'data_dukung_cuti', 'data_dukung_cuti_' . time() . '_' . $pegawai->nip);
					if (($valid = $upload['success'])) {
						$data['data_dukung_cuti'] = $upload['filename'];
					} else {
						$this->vars['message'] = $upload['message'];
					}
				}

				if ($valid && $this->cuti->insert($data)) {
					return $this->prepareRedirectView($pegawai->id, [
						'status' => true,
						'message' => "Berhasil menambahkan pengajuan cuti {$pegawai->nama_pegawai}",
					]);
				}
			}

			$this->vars['message'] = my_validation_errors();
		}

		$this->prepareCutiFields(null, $id_user);

		$this->getProfileData($this->vars['selected_user_id'] ? $this->pegawai->findOne($this->vars['selected_user_id']) : null, null, ['showInfoSisaCuti' => true]);

		$this->load->vars($this->vars);

		if ($this->input->is_ajax_request()) {
			return $this->viewAjax('cuti/form', [
				'status' => false,
				'message' => $this->vars['message'],
			]);
		}

		$this->load->view('layout');
	}

	function update_cuti($id)
	{
		$cuti = $this->cuti->findOne($id);

		$this->isAuthorized = check_auth($cuti->user_id == $this->user->id, is_operator(), is_kepegawaian());

		$pegawai = $this->pegawai->findOne($cuti->user_id);

		$this->vars['title'] = 'Update Pengajuan Cuti';
		$this->vars['showTitle'] = false;
		$this->vars['main_body'] = 'cuti/form';
		$this->vars['message'] = '';
		$this->vars['backUrl'] = base_url('kepegawaian/pegawai/profile/' . $cuti->user_id);

		$this->prepareCutiValidation($cuti);

		if (isset($_POST) && !empty($_POST)) {
			$data = [
				'jenis_cuti_id' => $this->input->post('jenis_cuti_id'),
				'tanggal_pengajuan' => $this->input->post('tanggal_pengajuan'),
				'tanggal_awal' => $this->input->post('tanggal_awal'),
				'tanggal_akhir' => $this->input->post('tanggal_akhir'),
				'lama_cuti' => count_working_days($this->input->post('tanggal_awal'), $this->input->post('tanggal_akhir')),
				'alasan_cuti' => $this->input->post('alasan_cuti'),
				'alamat_id' => $this->input->post('alamat_id'),
			];

			//cuti yang ditolak, kalau diedit ubah kembali status menjadi pengajuan
			if ($cuti->status == $this->config->item('status_rejected')) {
				$data['status'] = $pegawai->jabatan_id == $this->config->item('jabatan_ketua') ? $this->config->item('status_validasi_kepegawaian') : $this->config->item('status_verify_kepegawaian');
			}

			//kalau ubah jenis cuti dan tidak butuh data dukung
			if ($this->_cutiNeedDataDukung($cuti->jenis_cuti_id) && !$this->_cutiNeedDataDukung($this->input->post('jenis_cuti_id'))) {
				if (delete_file('data_dukung_cuti', $cuti->data_dukung_cuti)) {
					$data['data_dukung_cuti'] = '';
				}
			}

			$valid = true;
			if (!empty($_FILES['data_dukung_cuti']['name']) && delete_file('data_dukung_cuti', $cuti->data_dukung_cuti)) {
				$upload = do_upload('data_dukung_cuti', 'data_dukung_cuti', 'data_dukung_cuti_' . time() . '_' . $cuti->nip);
				if (($valid = $upload['success'])) {
					$data['data_dukung_cuti'] = $upload['filename'];
				} else {
					$this->vars['message'] = $upload['message'];
				}
			}

			if ($valid && $this->form_validation->run() === TRUE) {
				if ($this->cuti->update($cuti->id, $data)) {
					return $this->prepareRedirectView($cuti->user_id, [
						'status' => true,
						'message' => "Berhasil mengupdate pengajuan cuti {$pegawai->nama_pegawai}",
					]);
				}
			}

			$this->vars['message'] = my_validation_errors();
		}

		$this->prepareCutiFields($cuti);

		$this->getProfileData($pegawai, $cuti, ['showInfoSisaCuti' => true]);

		$this->load->vars($this->vars);

		if ($this->input->is_ajax_request()) {
			return $this->viewAjax('cuti/form', [
				'status' => false,
				'message' => $this->vars['message'],
			]);
		}

		$this->load->view('layout');
	}

	function update_manual($id)
	{
		$this->isAuthorized = check_auth(is_operator(), is_kepegawaian());

		$title = 'Update Cuti Manual';

		$cuti = $this->cuti->findOne($id);
		$pegawai = $this->pegawai->findOne($cuti->user_id);

		$this->prepareValidationManual();

		$this->vars['message'] = '';
		if (isset($_POST) && !empty($_POST)) {
			if ($this->input->post("pertimbangan_atasan_id") && $this->input->post("pertimbangan_atasan_id") != $this->config->item('cuti_disetujui')) {
				$this->form_validation->set_rules("pertimbangan_atasan", 'Keterangan Atasan', 'required');
			}
			if ($this->input->post("pertimbangan_pejabat_id") && $this->input->post("pertimbangan_pejabat_id") != $this->config->item('cuti_disetujui')) {
				$this->form_validation->set_rules("pertimbangan_pejabat", 'Keterangan Pejabat', 'required');
			}
			if ($this->input->post('jenis_cuti_id') != $this->config->item('cuti_tahunan') && (!$cuti->data_dukung_cuti || !file_exists($this->config->item('folder_root_upload')  . '/data_dukung_cuti/' . $cuti->data_dukung_cuti)) && empty($_FILES['data_dukung_cuti']['name'])) {
				$this->form_validation->set_rules('data_dukung_cuti', 'Data Dukung', 'required');
			}
			if ((!$cuti->pdf_surat_cuti || !file_exists($this->config->item('folder_root_upload')  . '/surat_cuti/' . $cuti->pdf_surat_cuti)) && empty($_FILES['pdf_surat_cuti']['name'])) {
				$this->form_validation->set_rules('pdf_surat_cuti', 'Dokumen Surat Cuti', 'required');
			}

			$valid = true;
			$filename = '';
			if (!empty($_FILES['data_dukung_cuti']['name']) && delete_file('data_dukung_cuti', $cuti->data_dukung_cuti)) {
				$upload = do_upload('data_dukung_cuti', 'data_dukung_cuti', 'data_dukung_cuti_' . time() . '_' . $cuti->nip);
				if (($valid = $upload['success'])) {
					$filename = $upload['filename'];
				} else {
					$this->vars['message'] = $upload['message'];
				}
			}

			$fileCuti = '';
			if (!empty($_FILES['pdf_surat_cuti']['name'])) {
				$upload = do_upload('pdf_surat_cuti', 'surat_cuti', 'surat_cuti_' . time() . '_' . $cuti->nip);
				if (($valid = $upload['success'])) {
					$fileCuti = $upload['filename'];
				} else {
					$this->vars['message'] = $upload['message'];
				}
			}

			if ($this->form_validation->run() === TRUE && $valid) {
				$data = [
					'jenis_cuti_id' => $this->input->post('jenis_cuti_id'),
					'tanggal_pengajuan' => $this->input->post('tanggal_pengajuan'),
					'tanggal_awal' => $this->input->post('tanggal_awal'),
					'tanggal_akhir' => $this->input->post('tanggal_akhir'),
					'lama_cuti' => count_working_days($this->input->post('tanggal_awal'), $this->input->post('tanggal_akhir')),
					'alasan_cuti' => $this->input->post('alasan_cuti'),
					'alamat_id' => $this->input->post('alamat_id'),
					'user_petugas_id' => $this->input->post('user_petugas_id'),
					'user_atasan_id' => $this->input->post('user_atasan_id'),
					'user_pejabat_id' => $this->input->post('user_pejabat_id'),
					'pertimbangan_atasan_id' => $this->input->post('pertimbangan_atasan_id'),
					'pertimbangan_pejabat_id' => $this->input->post('pertimbangan_pejabat_id'),
					'pertimbangan_atasan' => $this->input->post('pertimbangan_atasan'),
					'pertimbangan_pejabat' => $this->input->post('pertimbangan_pejabat'),
					'jabatan_atasan_id' => $this->input->post('jabatan_atasan_id'),
					'jabatan_pejabat_id' => $this->config->item('jabatan_ketua'),
					'tanggal_paraf' => $this->input->post('tanggal_paraf'),
					'tanggal_verifikasi_atasan' => $this->input->post('tanggal_verifikasi_atasan'),
					'tanggal_verifikasi_pejabat' => $this->input->post('tanggal_verifikasi_pejabat'),
					'tanggal_validasi' => $this->input->post('tanggal_verifikasi_pejabat'),
				];

				if ($filename) {
					$data['data_dukung_cuti'] = $filename;
				}
				if ($fileCuti) {
					$data['pdf_surat_cuti'] = $fileCuti;
				}

				if ($this->cuti->update($cuti->id, $data)) {
					return $this->prepareRedirectView($cuti->user_id, [
						'status' => true,
						'message' => "Berhasil mengupdate data cuti {$cuti->nama_pegawai}",
					]);
				}
			}

			$this->vars['message'] = my_validation_errors();
		}

		$this->prepareFieldsManual($cuti);

		$this->vars['title'] = $title;
		$this->vars['main_body'] = 'cuti/form';
		$this->vars['isRiwayat'] = false;
		$this->vars['isManual'] = true;
		$this->vars['backUrl'] = base_url('kepegawaian/pegawai/profile/' . $pegawai->id);

		$this->getProfileData($pegawai, $cuti, ['showInfoSisaCuti' => true]);

		$this->load->vars($this->vars);

		if ($this->input->is_ajax_request()) {
			return $this->viewAjax('cuti/form', [
				'status' => false,
				'message' => $this->vars['message'],
			]);
		}

		$this->load->view('layout');
	}

	function delete_cuti($id)
	{
		if (($redirect = $this->redirectPost()) !== false) {
			return $redirect;
		}

		$cuti = $this->cuti->findOne($id);

		$this->isAuthorized = check_auth($cuti->user_id == $this->user->id, is_operator(), is_kepegawaian());

		return $this->prepareRedirectView($cuti->user_id, [
			'status' => true,
			'message' => ($this->cuti->delete($id) ? 'Berhasil menghapus pengajuan cuti' : 'Gagal menghapus pengajuan cuti') . ' ' . $cuti->nama_pegawai,
		]);
	}

	protected function prepareCutiValidation($cuti = null)
	{
		if ($this->checkSisaCuti) {
			$this->form_validation->set_rules('user_id', 'Pegawai', 'required|check_overlap_cuti|check_pending_cuti');
			$this->form_validation->set_rules('tanggal_akhir', 'Tanggal Akhir Cuti', 'required|compare_date|check_sisa_cuti');
		} else {
			$this->form_validation->set_rules('user_id', 'Pegawai', 'required');
			$this->form_validation->set_rules('tanggal_akhir', 'Tanggal Akhir Cuti', 'required|compare_date');
		}

		$this->form_validation->set_rules('tanggal_awal', 'Tanggal Awal Cuti', 'required');
		$this->form_validation->set_rules('tanggal_pengajuan', 'Tanggal Pengajuan', 'required');
		$this->form_validation->set_rules('jenis_cuti_id', 'Jenis Cuti', 'required');
		$this->form_validation->set_rules('alasan_cuti', 'Alasan Cuti', 'required');
		$this->form_validation->set_rules('alamat_id', 'Alamat Cuti', 'required');

		if ($this->input->post('jenis_cuti_id') && empty($_FILES['data_dukung_cuti']['name']) && $this->_cutiNeedDataDukung($this->input->post('jenis_cuti_id'))) {
			if (!$cuti || !file_url('data_dukung_cuti', $cuti->data_dukung_cuti)) {
				$this->form_validation->set_rules('data_dukung_cuti', 'Data Dukung', 'required');
			}
		}

		if ($this->input->post('pertimbangan_id') && $this->input->post('pertimbangan_id') != $this->config->item('cuti_disetujui')) {
			$this->form_validation->set_rules('pertimbangan', 'Keterangan', 'required');
		}
	}

	protected function prepareValidationManual()
	{
		$this->checkSisaCuti = false;

		$this->prepareCutiValidation();

		$this->form_validation->set_rules('user_petugas_id', 'Petugas Cuti (Kepala Sub Bagian Kepegawaian, Organisasi, Dan Tata Laksana)', 'required');
		$this->form_validation->set_rules('user_atasan_id', 'Atasan Langsung', 'required');
		$this->form_validation->set_rules('user_pejabat_id', 'Pejabat Cuti', 'required');
		$this->form_validation->set_rules('pertimbangan_atasan_id', 'Pertimbangan Atasan', 'required');
		$this->form_validation->set_rules('pertimbangan_pejabat_id', 'Pertimbangan Pejabat', 'required');
		// $this->form_validation->set_rules('pertimbangan_atasan', 'Keterangan Pertimbangan Atasan', 'required');
		// $this->form_validation->set_rules('pertimbangan_pejabat', 'Keterangan Pertimbangan Pejabat', 'required');
		$this->form_validation->set_rules('tanggal_paraf', 'Tanggal Paraf', 'required');
		$this->form_validation->set_rules('tanggal_verifikasi_atasan', 'Tanggal Ttd Atasan', 'required');
		$this->form_validation->set_rules('tanggal_verifikasi_pejabat', 'Tanggal Ttd Pejabat', 'required');
	}

	protected function prepareCutiFields($cuti = null, $id_user = null, $showInfo = true, $isManual = false)
	{
		$this->load->model('settings/Ref_Model', 'ref');

		$this->vars['selected_user_id'] = $this->form_validation->set_value('user_id', $cuti ? $cuti->user_id : ($id_user ? $id_user : ($this->enableTest ? 1 : null)));

		$this->vars['form']['hidden_field'] = [
			'type' => 'form_hidden',
			'id_cuti' => $cuti ? $cuti->id : null,
			'selected_alamat_id' => $this->form_validation->set_value('alamat_id', $cuti ? $cuti->alamat_id : ($this->enableTest ? 1 : null)),
		];

		if ($cuti || $id_user) {
			$this->vars['form']['user_id'] = [
				'type' => 'form_hidden',
				'user_id' => $this->vars['selected_user_id'],
			];
		} else {
			if ($showInfo) {
				$this->vars['form']['form_info'] = [
					'type' => 'form_info',
					'title' => 'PERHATIAN!',
					'info' => 'Apabila nama pegawai tidak muncul, pastikan data yang bersangkutan sudah lengkap.',
				];
			}

			$this->vars['form']['user_id'] = [
				'type' => 'form_dropdown',
				'icon' => 'user-circle',
				'name' => 'user_id',
				'options' => $this->pegawai->find(null, true),
				'selected' => $this->vars['selected_user_id'],
				'placeholder' => 'Pegawai',
				'divClass' => 'dependent-sisa-cuti',
			];
		}

		$this->vars['form']['tanggal_pengajuan'] = [
			'type' => 'form_datepicker',
			'name' => 'tanggal_pengajuan',
			'value' => $this->form_validation->set_value('tanggal_pengajuan', $cuti ? $cuti->tanggal_pengajuan : ($this->enableTest ? date('Y-m-d') : null)),
			'placeholder' => 'Tanggal Pengajuan',
			'divClass' => 'data-cuti' . (!$this->vars['selected_user_id'] ? ' collapse' : ''),
		];

		$this->vars['selected_jenis_cuti'] = $this->form_validation->set_value('jenis_cuti_id', $cuti ? $cuti->jenis_cuti_id : ($this->enableTest ? 1 : null));
		$this->vars['form']['jenis_cuti_id'] = [
			'type' => 'form_dropdown',
			'name' => 'jenis_cuti_id',
			'options' => $this->ref->findJenisCuti(),
			'selected' => $this->vars['selected_jenis_cuti'],
			'divClass' => 'data-cuti dependent-sisa-cuti' . (!$this->vars['selected_user_id'] ? ' collapse' : ''),
			'placeholder' => 'Jenis Cuti',
		];
		// if ($cuti) {
		//     $this->vars['form']['jenis_cuti_id']['disabled'] = true;
		// }

		if (!$isManual) {
			$this->vars['form']['sisa_cuti'] = [
				'name' => 'sisa_cuti',
				'placeholder' => 'Sisa Cuti',
				'disabled' => true,
				'divClass' => 'data-cuti sisa-cuti' . (!$this->vars['selected_user_id'] || !has_jatah_cuti_tahunan($this->vars['selected_jenis_cuti']) ? ' collapse' : ''),
				'value' => (($sisa = $this->cuti->countRemainder($this->vars['selected_user_id'], $this->vars['selected_jenis_cuti'])) ? $sisa : 0) . ' hari',
			];
		}

		$this->vars['form']['tanggal_awal'] = [
			'type' => 'form_datepicker',
			'name' => 'tanggal_awal',
			'value' => $this->form_validation->set_value('tanggal_awal', $cuti ? $cuti->tanggal_awal : ($this->enableTest ? random_date_between(date('Y-m-d', strtotime('+1 days')), date('Y-m-d', strtotime('+5 days'))) : null)),
			'placeholder' => 'Tanggal Awal Cuti',
			'divClass' => 'data-cuti dependent-sisa-cuti' . (!$this->vars['selected_user_id'] ? ' collapse' : ''),
		];

		$this->vars['form']['tanggal_akhir'] = [
			'type' => 'form_datepicker',
			'name' => 'tanggal_akhir',
			'value' => $this->form_validation->set_value('tanggal_akhir', $cuti ? $cuti->tanggal_akhir : ($this->enableTest ? random_date_between(date('Y-m-d', strtotime('+6 days')), date('Y-m-d', strtotime('+10 days'))) : null)),
			'placeholder' => 'Tanggal Akhir Cuti',
			'divClass' => 'data-cuti dependent-sisa-cuti' . (!$this->vars['selected_user_id'] ? ' collapse' : ''),
		];

		$this->vars['form']['lama_cuti'] = [
			'name' => 'lama_cuti',
			'placeholder' => 'Lama Cuti',
			'disabled' => true,
			'divClass' => 'data-cuti' . (!$this->vars['selected_user_id'] ? ' collapse' : ''),
			'value' => ($lama_cuti = $this->form_validation->set_value('lama_cuti', count_working_days($this->vars['form']['tanggal_awal']['value'], $this->vars['form']['tanggal_akhir']['value']))) ? $lama_cuti . ' hari' : '',
		];

		if (!$isManual) {
			$this->vars['form']['sisa_setelah_cuti'] = [
				'name' => 'sisa_setelah_cuti',
				'placeholder' => 'Sisa setelah cuti',
				'disabled' => true,
				'divClass' =>  $this->vars['form']['sisa_cuti']['divClass'],
				'value' => $sisa !== '' && $lama_cuti !== '' ? ($sisa - $lama_cuti) . ' hari' : null,
			];
		}

		$this->vars['form']['alamat_id'] = [
			'type' => 'form_dropdown',
			'name' => 'alamat_id',
			'divClass' => 'data-cuti' . (!$this->vars['selected_user_id'] ? ' collapse' : ''),
			'placeholder' => 'Alamat',
			'options' => $this->vars['selected_user_id'] ? $this->alamat->getList(['where' => [['user_id' => $this->vars['selected_user_id']]]]) : null,
			'selected' => $this->form_validation->set_value('alamat_id', $this->vars['form']['hidden_field']['selected_alamat_id']),
		];

		$this->vars['form']['alasan_cuti'] = [
			'type' => 'form_textarea',
			'name' => 'alasan_cuti',
			'placeholder' => 'Alasan Cuti',
			'value' => $this->form_validation->set_value('alasan_cuti', $cuti ? $cuti->alasan_cuti : ($this->enableTest ? 'keperluan keluarga' : null)),
			'divClass' => 'data-cuti' . (!$this->vars['selected_user_id'] ? ' collapse' : ''),
		];

		$this->vars['form']['data_dukung_cuti'] = [
			'type' => 'form_dropzone',
			'name' => 'data_dukung_cuti',
			'label' => 'Data Dukung (pdf)',
			'divClass' => 'input-data-dukung' . (!$this->vars['selected_user_id'] || !$this->vars['selected_jenis_cuti'] || $this->vars['selected_jenis_cuti'] == $this->config->item('cuti_tahunan') ? ' collapse' : ''),
		];
	}

	protected function prepareFieldsManual($cuti = null)
	{
		$this->vars['form']['form_info_manual'] = [
			'type' => 'form_info',
			'title' => 'PERHATIAN!',
			'info' => '<ul>
                        <li>Ini adalah form untuk mengupload surat cuti yang sudah sah dan diterbitkan secara manual (tanpa melalui aplikasi)</li>
                        ' . (!$cuti ? '<li>Apabila nama pegawai tidak muncul, pastikan data yang bersangkutan sudah lengkap.</li>' : '') . '
                    </ul>',
		];

		$this->prepareCutiFields($cuti, null, false, true);

		$this->vars['form']['separator_petugas'] = [
			'type' => 'form_separator',
			'divClass' => 'data-cuti' . (!$this->vars['selected_user_id'] ? ' collapse' : ''),
		];

		$this->vars['form']['user_petugas_id'] = [
			'type' => 'form_dropdown',
			'name' => 'user_petugas_id',
			'options' => $this->pegawai->find('(' . $this->config->item('tbl_ref_jabatan') . '.id IN(' . implode(',', [$this->config->item('jabatan_kasub_kepegawaian')]) . '))'),
			'selected' => $this->form_validation->set_value('user_petugas_id', $cuti ? $cuti->user_petugas_id : ($this->enableTest ? 15 : null)),
			'divClass' => 'data-cuti' . (!$this->vars['selected_user_id'] ? ' collapse' : ''),
			'placeholder' => 'Petugas Cuti',
		];

		$this->vars['form']['tanggal_paraf'] = [
			'type' => 'form_datepicker',
			'name' => 'tanggal_paraf',
			'value' => $this->form_validation->set_value('tanggal_paraf', $cuti ? $cuti->tanggal_paraf : ($this->enableTest ? date('Y-m-d') : null)),
			'placeholder' => 'Tanggal Paraf',
			'divClass' => 'data-cuti' . (!$this->vars['selected_user_id'] ? ' collapse' : ''),
		];

		$this->vars['form']['separator_atasan'] = [
			'type' => 'form_separator',
			'divClass' => 'data-cuti' . (!$this->vars['selected_user_id'] ? ' collapse' : ''),
		];

		$this->vars['form']['jabatan_atasan_id'] = [
			'type' => 'form_dropdown',
			'name' => 'jabatan_atasan_id',
			// 'options' => $this->ref->findJabatan('(id IN (' . implode(', ', jabatan_atasan_cuti()) . '))'),
			// 'options' => $this->ref->findJabatan(['is_struktural' => 1], 'Pilih Atasan Cuti'),
			'options' => $this->ref->findJabatan(['group IN (' . implode(',', get_structural_group()) . ')'], 'Pilih Atasan Cuti'),
			'selected' => $this->form_validation->set_value('jabatan_atasan_id', $cuti ? $cuti->jabatan_atasan_id : ($this->enableTest ? 5 : null)),
			'divClass' => 'data-cuti' . (!$this->vars['selected_user_id'] ? ' collapse' : ''),
			'placeholder' => 'Jabatan Atasan Cuti',
		];

		$this->vars['form']['user_atasan_id'] = [
			'type' => 'form_dropdown',
			'name' => 'user_atasan_id',
			'options' => $this->pegawai->find('(' . $this->config->item('tbl_ref_jabatan') . '.id IN(' . implode(', ', jabatan_atasan_cuti()) . '))'),
			'selected' => $this->form_validation->set_value('user_atasan_id', $cuti ? $cuti->user_atasan_id : ($this->enableTest ? 6 : null)),
			'divClass' => 'data-cuti' . (!$this->vars['selected_user_id'] ? ' collapse' : ''),
			'placeholder' => 'Atasan Cuti',
		];

		$this->vars['form']['tanggal_verifikasi_atasan'] = [
			'type' => 'form_datepicker',
			'name' => 'tanggal_verifikasi_atasan',
			'value' => $this->form_validation->set_value('tanggal_verifikasi_atasan', $cuti ? $cuti->tanggal_verifikasi_atasan : ($this->enableTest ? date('Y-m-d') : null)),
			'placeholder' => 'Tanggal Ttd Atasan',
			'divClass' => 'data-cuti' . (!$this->vars['selected_user_id'] ? ' collapse' : ''),
		];

		$this->vars['selected_pertimbangan_atasan_id'] = $this->form_validation->set_value("pertimbangan_atasan_id", $cuti ? $cuti->pertimbangan_atasan_id : ($this->enableTest ? 1 : null));
		$this->vars['form']["pertimbangan_atasan_id"] = [
			'type' => 'form_dropdown',
			'name' => "pertimbangan_atasan_id",
			'options' => $this->ref->findJenisPertimbangan(),
			'selected' => $this->vars['selected_pertimbangan_atasan_id'],
			'divClass' => 'data-cuti' . (!$this->vars['selected_user_id'] ? ' collapse' : ''),
			'placeholder' => 'Pertimbangan Atasan',
		];

		$this->vars['form']["pertimbangan_atasan"] = [
			'type' => 'form_textarea',
			'name' => "pertimbangan_atasan",
			'placeholder' => 'Keterangan Atasan',
			'value' => $this->form_validation->set_value('pertimbangan_atasan', $cuti ? $cuti->pertimbangan_atasan : ($this->enableTest ? 'ini pertimbangan atasan' : null)),
			'divClass' => 'data-cuti' . (!$this->vars['selected_user_id'] ? ' collapse' : ''),
		];

		$this->vars['form']['separator_pejabat'] = [
			'type' => 'form_separator',
			'divClass' => 'data-cuti' . (!$this->vars['selected_user_id'] ? ' collapse' : ''),
		];

		$this->vars['form']['user_pejabat_id'] = [
			'type' => 'form_dropdown',
			'name' => 'user_pejabat_id',
			'options' => $this->pegawai->find('(' . $this->config->item('tbl_ref_jabatan') . '.id IN(' . implode(',', [$this->config->item('jabatan_ketua'), $this->config->item('jabatan_wakil_ketua')]) . '))'),
			'selected' => $this->form_validation->set_value('user_pejabat_id', $cuti ? $cuti->user_pejabat_id : ($this->enableTest ? 2 : null)),
			'divClass' => 'data-cuti' . (!$this->vars['selected_user_id'] ? ' collapse' : ''),
			'placeholder' => 'Pejabat Cuti',
		];

		$this->vars['form']['tanggal_verifikasi_pejabat'] = [
			'type' => 'form_datepicker',
			'name' => 'tanggal_verifikasi_pejabat',
			'value' => $this->form_validation->set_value('tanggal_verifikasi_pejabat', $cuti ? $cuti->tanggal_verifikasi_pejabat : ($this->enableTest ? date('Y-m-d') : null)),
			'placeholder' => 'Tanggal Ttd Pejabat',
			'divClass' => 'data-cuti' . (!$this->vars['selected_user_id'] ? ' collapse' : ''),
		];

		$this->vars['selected_pertimbangan_pejabat_id'] = $this->form_validation->set_value("pertimbangan_pejabat_id", $cuti ? $cuti->pertimbangan_pejabat_id : ($this->enableTest ? 1 : null));
		$this->vars['form']["pertimbangan_pejabat_id"] = [
			'type' => 'form_dropdown',
			'name' => "pertimbangan_pejabat_id",
			'options' => $this->ref->findJenisPertimbangan(),
			'selected' => $this->vars['selected_pertimbangan_pejabat_id'],
			'divClass' => 'data-cuti' . (!$this->vars['selected_user_id'] ? ' collapse' : ''),
			'placeholder' => 'Pertimbangan Pejabat',
		];

		$this->vars['form']["pertimbangan_pejabat"] = [
			'type' => 'form_textarea',
			'name' => "pertimbangan_pejabat",
			'placeholder' => 'Keterangan Pejabat',
			'value' => $this->form_validation->set_value('pertimbangan_pejabat', $cuti ? $cuti->pertimbangan_pejabat : ($this->enableTest ? 'ini pertimbangan pejabat' : null)),
			'divClass' => 'data-cuti' . (!$this->vars['selected_user_id'] ? ' collapse' : ''),
		];

		$this->vars['form']['separator_file'] = [
			'type' => 'form_separator',
			'divClass' => 'data-cuti' . (!$this->vars['selected_user_id'] ? ' collapse' : ''),
		];

		$this->vars['form']['pdf_surat_cuti'] = [
			'type' => 'form_dropzone',
			'name' => 'pdf_surat_cuti',
			'label' => 'Surat Cuti (pdf)',
			'divClass' => 'data-cuti' . (!$this->vars['selected_user_id'] ? 'collapse' : ''),
		];
	}
}

class BaseEmployeeData extends Auth_Controller
{
	// These should be overridden by child classes
	protected $_type_id;
	protected $_type_text;
	protected $_index_title;
	protected $_index_view;
	protected $_index_url;

	// Different user filters for each type
	protected $_user_filter_condition;

	public function __construct($options = [])
	{
		// Extract parameters from options array with defaults
		$type_id = isset($options['type_id']) ? $options['type_id'] : null;
		$type_text = isset($options['type_text']) ? $options['type_text'] : '';
		$index_title = isset($options['index_title']) ? $options['index_title'] : '';
		$index_subtitle = isset($options['index_subtitle']) ? $options['index_subtitle'] : '';
		$index_icon = isset($options['index_icon']) ? $options['index_icon'] : '';
		$index_view = isset($options['index_view']) ? $options['index_view'] : '';
		$user_filter_condition = isset($options['user_filter_condition']) ? $options['user_filter_condition'] : '';
		$module_id = isset($options['module_id']) ? $options['module_id'] : '';

		parent::__construct();

		$this->load->model('pegawai/Employee_Data_Model', 'misc');
		$this->model = $this->misc;
		// Set the type filter based on the type
		$this->model->typeFilter = $type_id;

		$this->indexTitle = $index_title;
		$this->indexSubtitle = $index_subtitle;
		$this->indexIcon = $index_icon;
		$this->indexView = $index_view;
		$this->module_id = $module_id;

		$this->fileFolders = ['file' => $this->config->item('folder_misc_pegawai')];

		// Store the values for use in methods
		$this->_type_id = $type_id;
		$this->_type_text = $type_text;
		$this->_user_filter_condition = $user_filter_condition;
	}

	public function index()
	{
		// Call parent prepare_index with module_id and page_icon
		$this->prepare_index([
			'module_id' => $this->module_id,
			'page_icon' => isset($this->indexIcon) ? $this->indexIcon : 'fa-solid fa-layer-group'
		]);

		if ($this->input->is_ajax_request()) {
			return $this->viewAjax($this->mainBody, [
				'title' => isset($this->vars['title']) ? $this->vars['title'] : APP_NAME,
				'message' => isset($this->vars['message']) ? $this->vars['message'] : '',
				'showOnModal' => $this->showOnModal,
				'showPageHeader' => $this->showPageHeader
			]);
		}

		// Enable CI Profiler locally to validate DB/benchmarks
		if (is_development() && is_local_ip()) {
			$this->output->enable_profiler(TRUE);
		}

		$this->load->view($this->indexLayout, [
			'showPageHeader' => $this->showPageHeader
		]);
	}

	public function save($id = null)
	{
		if (($result = $this->isLoggedIn()) !== true) {
			return $this->set_content_type($result);
		}

		$record = $id ? $this->model->findOne($id, true) : null;
		$actionText = $id ? 'memperbarui' : 'menambah';

		// Set form validation rules
		$this->form_validation->set_rules('user_id', 'Pegawai', 'required|trim');
		$this->form_validation->set_rules('year', 'Tahun', 'required|trim');

		$this->vars['title'] = ($id ? 'Perbarui' : 'Tambah') . ' Data ' . $this->_type_text;
		$this->vars['message'] = '';

		if (isset($_POST) && !empty($_POST)) {
			// Dynamic file validation - both LHKPN and SPT always require a file
			foreach (array_keys($this->fileFolders) as $field) {
				if (!empty($_FILES[$field]['name'])) {
					$this->form_validation->set_rules($field, ucwords(str_replace('_', ' ', $field)), 'callback_validate_file_upload');
				} else if (!$record) {
					// File is required when creating new record
					$this->form_validation->set_rules($field, ucwords(str_replace('_', ' ', $field)), 'required');
				}
			}

			if ($this->form_validation->run() === TRUE) {
				$data = [
					'user_id' => $this->input->post('user_id'),
					'title' => $this->input->post('title'), // Use sent data from hidden field
					'type' => $this->input->post('field_type'), // Use sent data from hidden field (changed from 'type' to 'field_type')
					'year' => $this->input->post('year'),
					'description' => $this->input->post('description'),
				];

				// Check for unique combination of user_id, title, type, and year
				$existingRecord = $this->model->isUniqueCombinationExists(
					$data['user_id'],
					$data['title'],
					$data['type'],
					$data['year'],
					$id // Exclude current record when updating
				);

				if ($existingRecord) {
					// Load user model to get employee name
					$this->load->model('pegawai/Pegawai_Model', 'pegawai_model');
					$user = $this->pegawai_model->findOne($data['user_id']);
					$employee_name = $user ? $user->nama_lengkap : 'Unknown';

					$this->vars['message'] = "Data {$this->_type_text} tahun {$data['year']} atas nama {$employee_name} sudah ada.";
				} else {
					// Handle file uploads
					$upload_result = $this->handle_file_uploads($data['year'], $record, $data['title']);
					if ($upload_result['success']) {
						// Merge uploaded files data
						$data = array_merge($data, $upload_result['data']);

						if ($id ? $this->model->update($id, $data) : $this->model->insert($data)) {
							// Delete old files only after successful database update
							$this->cleanup_uploaded_files($upload_result['old_files']);

							return $this->redirectAjax([
								'redirect' => $this->indexUrl,
								'status' => true,
								'message' => "Berhasil {$actionText} data pegawai",
							]);
						}

						// Database operation failed - cleanup uploaded files
						$this->cleanup_uploaded_files($upload_result['uploaded_files']);
					} else {
						// File upload failed
						$this->vars['message'] = $upload_result['message'];
					}
				}
			} else {
				$this->vars['message'] = my_validation_errors();
			}
		}

		// Load user model for dropdown
		$this->load->model('pegawai/Pegawai_Model', 'pegawai');

		// Transform user data for dropdown options
		$userOptions = ['' => 'Pilih Pegawai']; // Default option
		foreach ($this->pegawai->getUsersForDropdown($this->_user_filter_condition) as $user) {
			$userOptions[$user->id] = $user->nama_lengkap;
		}

		$this->vars['form'] = [
			'user_id' => [
				'type' => 'form_dropdown',
				'name' => 'user_id',
				'label' => 'Pegawai',
				'options' => $userOptions,
				'selected' => set_value('user_id', $record ? $record->user_id : ''),
				'placeholder' => 'Pilih Pegawai',
				'required' => true,
			],
			'hidden_fields' => [
				'type' => 'form_hidden',
				'title' => $this->_type_text,
				'field_type' => $this->_type_id,
			],
			'year' => [
				'type' => 'form_datepicker',
				'format' => 'yyyy',
				'name' => 'year',
				'value' => set_value('year', $record ? $record->year : date('Y')),
				'label' => 'Tahun',
				'placeholder' => 'Pilih Tahun',
				'required' => true,
			],
			'description' => [
				'type' => 'form_textarea',
				'name' => 'description',
				'value' => set_value('description', $record ? $record->description : ''),
				'label' => 'Deskripsi',
				'placeholder' => 'Masukkan Deskripsi (opsional)',
				'rows' => 3,
			],
			'file' => [
				'type' => 'form_dropzone',
				'name' => 'file',
				'label' => 'Dokumen ' . $this->_type_text . ' (pdf)',
				'required' => !$record,
			],
			'existing_file' => [
				'type' => 'form_info',
				'title' => 'Dokumen Saat Ini',
				'info' => $record && $record->file ? '<a href="' . file_url($this->config->item('folder_misc_pegawai'), $record->file) . '" target="_blank">' . $record->file . '</a> (' . format_filesize(isset($record->file_size) ? $record->file_size : 0) . ')' : '',
				'visible' => $record && $record->file,
			],
		];

		$this->load->vars($this->vars);

		if ($this->input->is_ajax_request()) {
			return $this->viewAjax('widgets/form', [
				'status' => false,
				'message' => $this->vars['message'],
			]);
		}

		$this->load->view('layout');
	}
}

// Load batch file processing trait
require_once APPPATH . 'core/Batch_File_Processing.php';

class Python_Controller extends Core_Controller
{
	use Batch_File_Processing;

	// ========================================================================
	// Python Runner Methods
	// ========================================================================

	// Temporary file paths
	protected $upload_path;
	protected $output_path;
	protected $temp_folder_name; // Should be set by child controllers

	public function __construct()
	{
		parent::__construct();

		// Initialize Python path
		$this->init_python_path();

		// Initialize temporary file paths
		$this->init_temp_paths();
	}

	/**
	 * Initialize temporary file paths based on environment
	 * Uses centralized uploads folder in development, system temp folder in production
	 * Each module gets its own sub-folder within the centralized location
	 * 
	 * @return void
	 */
	protected function init_temp_paths()
	{
		if (!isset($this->temp_folder_name)) {
			log_message('error', 'Python_Controller: temp_folder_name not set in child controller');
			return;
		}

		if (is_development()) {
			// Development: Use centralized uploads/python_temp folder
			$base_path = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'python_temp';
			if (!is_dir($base_path)) {
				mkdir($base_path, 0777, true);
			}
			if (!is_dir($base_path . DIRECTORY_SEPARATOR . $this->temp_folder_name)) {
				mkdir($base_path . DIRECTORY_SEPARATOR . $this->temp_folder_name, 0777, true);
			}
			if (!is_dir($base_path . DIRECTORY_SEPARATOR . $this->temp_folder_name . DIRECTORY_SEPARATOR . 'output')) {
				mkdir($base_path . DIRECTORY_SEPARATOR . $this->temp_folder_name . DIRECTORY_SEPARATOR . 'output', 0777, true);
			}
			$this->upload_path = $base_path . DIRECTORY_SEPARATOR . $this->temp_folder_name;
			$this->output_path = $base_path . DIRECTORY_SEPARATOR . $this->temp_folder_name . DIRECTORY_SEPARATOR . 'output';
		} else {
			// Production: Use centralized system temp folder
			$base_path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'pasda_python_temp';
			if (!is_dir($base_path)) {
				mkdir($base_path, 0777, true);
			}
			if (!is_dir($base_path . DIRECTORY_SEPARATOR . $this->temp_folder_name)) {
				mkdir($base_path . DIRECTORY_SEPARATOR . $this->temp_folder_name, 0777, true);
			}
			if (!is_dir($base_path . DIRECTORY_SEPARATOR . $this->temp_folder_name . DIRECTORY_SEPARATOR . 'output')) {
				mkdir($base_path . DIRECTORY_SEPARATOR . $this->temp_folder_name . DIRECTORY_SEPARATOR . 'output', 0777, true);
			}
			$this->upload_path = $base_path . DIRECTORY_SEPARATOR . $this->temp_folder_name;
			$this->output_path = $base_path . DIRECTORY_SEPARATOR . $this->temp_folder_name . DIRECTORY_SEPARATOR . 'output';

			// Clean old files on startup (older than 1 hour)
			$this->cleanup_old_files(3600);
		}
	}

	/**
	 * Clean up old temporary files
	 * 
	 * @param int $max_age Maximum age in seconds before files are deleted (default: 1 hour)
	 * @return void
	 */
	protected function cleanup_old_files($max_age = 3600)
	{
		$now = time();

		// Clean upload directory
		if (is_dir($this->upload_path)) {
			$files = scandir($this->upload_path);
			foreach ($files as $file) {
				if ($file === '.' || $file === '..' || is_dir($this->upload_path . DIRECTORY_SEPARATOR . $file)) {
					continue;
				}
				$file_path = $this->upload_path . DIRECTORY_SEPARATOR . $file;
				if (file_exists($file_path) && (filemtime($file_path) + $max_age) < $now) {
					@unlink($file_path);
				}
			}
		}

		// Clean output directory
		if (is_dir($this->output_path)) {
			$files = scandir($this->output_path);
			foreach ($files as $file) {
				if ($file === '.' || $file === '..' || is_dir($this->output_path . DIRECTORY_SEPARATOR . $file)) {
					continue;
				}
				$file_path = $this->output_path . DIRECTORY_SEPARATOR . $file;
				if (file_exists($file_path) && (filemtime($file_path) + $max_age) < $now) {
					@unlink($file_path);
				}
			}
		}
	}

	/**
	 * Initialize Python path based on environment
	 * Automatically detects development vs production environment
	 *
	 * @param string $dev_path Development Python path (default: Windows)
	 * @param array $prod_paths Production Python paths to check (default: Linux paths)
	 * @param string $default_path Default path if none found (default: /usr/bin/python3)
	 * @return void
	 */
	protected function init_python_path(
		$dev_path = 'C:\\Python313\\python.exe',
		$prod_paths = null,
		$default_path = '/usr/bin/python3'
	) {
		// Default production paths
		if ($prod_paths === null) {
			$prod_paths = [
				'/var/www/html/joss/ocr_env/bin/python',  // Recommended venv location
				'/var/www/html/joss/venv/bin/python',     // Alternative venv location
				'/usr/bin/python3',
				'/usr/local/bin/python3',
				'/opt/python3/bin/python3',
				'/usr/bin/python',
				'/usr/local/bin/python'
			];
		}

		// Set Python path based on environment
		if (is_development()) {
			$this->python_path = $dev_path;
		} else {
			// In production, check for common Python installation paths
			$found_path = false;
			foreach ($prod_paths as $path) {
				if (is_executable($path)) {
					$this->python_path = $path;
					$found_path = true;
					break;
				}
			}

			// If no system Python found, use default path
			if (!$found_path) {
				$this->python_path = $default_path;
			}
		}
	}

	/**
	 * Run a Python script with arguments
	 *
	 * @param string $script Script name (relative to script_path)
	 * @param array $args List of arguments to pass to the script
	 * @param bool $capture_output Whether to capture output (default: true)
	 * @return array Result array with 'success', 'output', 'return_var' keys
	 */
	protected function run_python_script($script, $args = [], $capture_output = true)
	{
		$script_path = $this->script_path . $script;

		// Build command
		$cmd = escapeshellcmd($this->python_path) . ' ' . escapeshellarg($script_path);

		// Add arguments
		foreach ($args as $arg) {
			$cmd .= ' ' . escapeshellarg($arg);
		}

		// Execute command
		$output = [];
		$return_var = 0;

		if ($capture_output) {
			exec($cmd . ' 2>&1', $output, $return_var);
		} else {
			exec($cmd . ' > /dev/null 2>&1 &');
			return [
				'success' => true,
				'output' => '',
				'return_var' => 0
			];
		}

		// Check if successful
		if ($return_var === 0) {
			return [
				'success' => true,
				'output' => implode("\n", $output),
				'return_var' => $return_var
			];
		} else {
			return [
				'success' => false,
				'error' => 'Script execution failed: ' . implode("\n", $output),
				'return_var' => $return_var,
				'output' => implode("\n", $output)
			];
		}
	}

	/**
	 * Run Python script and check if output file was created
	 *
	 * @param string $script Script name (relative to script_path)
	 * @param array $args List of arguments to pass to the script
	 * @param string $expected_output_file Expected output file path
	 * @return array Result array with 'success', 'output_path', 'error' keys
	 */
	protected function run_python_script_with_output($script, $args, $expected_output_file)
	{
		// Delete output file if it already exists to ensure fresh creation
		if (file_exists($expected_output_file)) {
			@unlink($expected_output_file);
		}

		$result = $this->run_python_script($script, $args);

		// Give filesystem a moment to sync (Windows can be slow)
		if (!file_exists($expected_output_file)) {
			usleep(100000); // 100ms delay
			clearstatcache();
		}

		if ($result['success'] && file_exists($expected_output_file)) {
			return [
				'success' => true,
				'output_path' => $expected_output_file,
				'output' => $result['output']
			];
		} else {
			$error_msg = isset($result['error']) ? $result['error'] : 'Output file not created';
			if (!$result['success']) {
				$error_msg .= ' | Return code: ' . $result['return_var'];
			}
			if (!file_exists($expected_output_file)) {
				$error_msg .= ' | File path: ' . $expected_output_file;
				$output_dir = dirname($expected_output_file);
				$error_msg .= ' | Output dir exists: ' . (is_dir($output_dir) ? 'YES' : 'NO');
				$error_msg .= ' | Output dir writable: ' . (is_writable($output_dir) ? 'YES' : 'NO');
			}

			return [
				'success' => false,
				'error' => $error_msg,
				'output' => isset($result['output']) ? $result['output'] : ''
			];
		}
	}

	/**
	 * Run a Python processing script for a single file
	 * This is a convenience wrapper for module-specific processors
	 *
	 * @param string $script Script name (relative to script_path)
	 * @param string $input_file Input file path
	 * @param string $output_path Output file path
	 * @param string $file_ext File extension for message formatting
	 * @param array $error_messages Error message mappings
	 * @param string $success_message_template Success message template (use %s for file ext)
	 * @return array Result array with 'success', 'message'/'error' keys
	 */
	protected function run_python_processor(
		$script,
		$input_file,
		$output_path,
		$file_ext = '',
		$error_messages = [],
		$success_message_template = 'File %s berhasil diproses'
	) {
		$result = $this->run_python_script_with_output(
			$script,
			[$input_file, $output_path],
			$output_path
		);

		if ($result['success']) {
			// Clean up input file
			if (file_exists($input_file)) {
				unlink($input_file);
			}

			$result['message'] = sprintf(
				$success_message_template,
				strtoupper($file_ext)
			);
		} else {
			// Clean up input file
			if (file_exists($input_file)) {
				unlink($input_file);
			}

			// Format error using Batch_File_Processing trait if available
			if (in_array('Batch_File_Processing', class_uses_recursive($this))) {
				$result['error'] = $this->format_process_error($result['error'], $error_messages, $file_ext);
			}
		}

		return $result;
	}
}
