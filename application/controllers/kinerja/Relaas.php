<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Relaas extends Core_Controller
{
	public function __construct()
	{
		parent::__construct();

		$this->load->helper('app');

		if (!is_local_ip()) {
			show_error('Halaman ini hanya bisa diakses menggunakan IP lokal pada jaringan kantor', $status_code = 404, $heading = 'Halaman ini hanya bisa diakses pada jaringan kantor');
			exit;
		}

		$this->load->library('encrypt');
		$this->load->model('kinerja/Relaas_Model', 'relaas');
		$this->load->helper('about_modal');
		$this->model = $this->relaas;

		$this->indexTitle = 'Monitoring Input/Unggah Relaas';
		$this->indexIcon = 'fa-solid fa-truck-ramp-box';
		$this->indexView = 'kinerja/relaas/list';
		$this->module_id = 'kinerja_relaas';
	}

	protected function prepare_index($options = [])
	{
		if (!defined('ID_WILAYAH') || ID_WILAYAH === '' || ID_WILAYAH === null) {
			$this->id_wilayah = null;
		} else {
			$this->id_wilayah = ID_WILAYAH;
		}

		$this->load->model('Wilayah_Model', 'wilayah');
		$this->load->model('settings/Ref_Model', 'ref');
		$this->vars['all_kecamatan'] = $this->id_wilayah ? $this->wilayah->findKecamatanByKabupaten($this->id_wilayah, true) : [];
		$this->vars['all_jurusita'] = $this->ref->findJs();

		parent::prepare_index($this->vars);
	}

	function index()
	{
		$perkara_id = $this->input->get('perkara_id');

		$this->load->model('Wilayah_Model', 'wilayah');
		$this->load->model('settings/Ref_Model', 'ref');

		if (!defined('ID_WILAYAH') || ID_WILAYAH === '' || ID_WILAYAH === null) {
			$this->id_wilayah = null;
		} else {
			$this->id_wilayah = ID_WILAYAH;
		}

		$this->vars['main_body'] = 'layout_content';
		$this->vars['view'] = 'kinerja/relaas/list';
		$this->vars['title'] = 'Monitoring Input/Unggah Relaas';
		$aboutContent = get_default_about_content('relaas');
		$this->vars['subtitle'] = isset($aboutContent['description']) ? $aboutContent['description'] : null;
		$this->vars['all_kecamatan'] = $this->id_wilayah ? $this->wilayah->findKecamatanByKabupaten($this->id_wilayah, true) : [];
		$this->vars['all_jurusita'] = $this->ref->findJs();
		$this->vars['show_filter_form'] = $perkara_id ? false : true;

		$this->load->vars($this->vars);

		if ($this->input->is_ajax_request()) {
			return $this->viewAjax($this->vars['view'], ['size' => 'modal-xl', 'showTitle' => true]);
		}

		$this->load->view('layout');
	}

	function relaas_list()
	{
		$this->load->model('Wilayah_Model', 'wilayah');
		$this->load->model('settings/Ref_Model', 'ref');

		$perkaraId = $this->input->get('perkara_id') ?: null;

		if ($perkaraId) {
			$this->relaas->perkara_id = $perkaraId;
			$this->vars['main_body'] = 'layout_content';
			$this->vars['view'] = 'kinerja/relaas/list';
			$this->vars['title'] = 'Daftar Relaas Perkara ' . $perkaraId;
			$aboutContent = get_default_about_content('relaas');
			$this->vars['subtitle'] = isset($aboutContent['description']) ? $aboutContent['description'] : null;

			$this->load->vars($this->vars);

			if ($this->input->is_ajax_request()) {
				return $this->viewAjax($this->vars['view'], ['size' => 'modal-xl', 'showTitle' => true]);
			}

			$this->load->view('layout');
			return;
		}

		switch ($this->uri->segment(4)) {
			case 'jumlah_input':
				$title = 'Relaas Sudah Input/Unggah';
				break;
			case 'sisa_sekarang':
				$title = 'Relaas Belum Input/Unggah';
				break;
			default:
				$title = 'Relaas';
				break;
		}

		switch ($this->uri->segment(6)) {
			case 3:
				$title = "Daftar $title Tahun Ini";
				break;
			case 'year':
				$title = "Daftar $title Tahun " . $this->uri->segment(7);
				break;
			case 'month':
				$title = "Daftar $title Bulan " . format_date($this->uri->segment(7), "MMMM yyyy");
				break;
			case 'range':
				$selectedRange = $this->uri->segment(7) ?: null;
				if ($selectedRange) {
					$parts = explode('|', urldecode($selectedRange));
					if (count($parts) === 2) {
						$startDate = date('d/m/Y', strtotime(trim($parts[0])));
						$endDate   = date('d/m/Y', strtotime(trim($parts[1])));
						$title = "Daftar $title " . $startDate . " - " . $endDate;
					} else {
						$title = "Daftar $title Bulan Ini";
					}
				} else {
					$title = "Daftar $title Bulan Ini";
				}
				break;
			default:
				if ($this->uri->segment(6)) {
					$title = "Daftar $title Bulan " . format_date($this->uri->segment(6), "MMMM yyyy");
				}
				break;
		}

		$kode_wilayah = $this->uri->segment(5);
		if ($kode_wilayah) {
			if ($kode_wilayah === '11.11.11') {
				$wilayah_nama = 'e-Summon';
			} else {
				$wilayah_nama = $this->wilayah->getNamaByKode($kode_wilayah);
			}

			if (!empty($wilayah_nama)) {
				$title .= " | {$wilayah_nama}";
			}
		}

		if (!defined('ID_WILAYAH') || ID_WILAYAH === '' || ID_WILAYAH === null) {
			$this->id_wilayah = null;
		} else {
			$this->id_wilayah = ID_WILAYAH;
		}

		$this->vars['main_body'] = 'layout_content';
		$this->vars['view'] = 'kinerja/relaas/list';
		$this->vars['title'] = $title;
		$aboutContent = get_default_about_content('relaas');
		$this->vars['subtitle'] = isset($aboutContent['description']) ? $aboutContent['description'] : null;
		$this->vars['all_kecamatan'] = $this->id_wilayah ? $this->wilayah->findKecamatanByKabupaten($this->id_wilayah, true) : [];
		$this->vars['all_jurusita'] = $this->ref->findJs();
		$this->vars['show_filter_form'] = false;

		$this->load->vars($this->vars);

		if ($this->input->is_ajax_request()) {
			return $this->viewAjax($this->vars['view'], ['size' => 'modal-xl', 'showTitle' => true]);
		}

		$this->load->view('layout');
	}

	/**
	 * Forward ?perkara_id=... into the model for the DataTables AJAX call
	 * used by kinerja/relaas/list, mirroring Bas::get_list().
	 */
	function get_list($where = [])
	{
		$perkaraId = $this->input->post('perkara_id') ?: null;
		if ($perkaraId) {
			$this->model->perkara_id = $perkaraId;
		}

		return parent::get_list($where);
	}

	function performance()
	{
		$this->vars['main_body'] = 'layout_content';
		$this->vars['view'] = 'kinerja/relaas/performance';
		$this->vars['title'] = 'Kinerja Relaas';
		$this->vars['subtitle'] = 'Modul ini menampilkan statistik dan rekapitulasi kinerja Relaas per kecamatan secara periodik.';
		$this->vars['module_id'] = 'kinerja_relaas';

		$this->load->vars($this->vars);

		if ($this->input->is_ajax_request()) {
			return $this->viewAjax('kinerja/relaas/performance');
		}

		$this->load->view('layout');
	}

	function get_performance()
	{
		// Early release PHP session to avoid CI database session GET_LOCK contention during long queries
		if (function_exists('session_write_close')) {
			session_write_close();
		}

		$selectedRange = $this->input->post('selectedRange');

		// Pass selectedRange to model for filtering
		$data = $this->model->get_performance($selectedRange);

		return $this->set_content_type([
			"draw" => $this->input->post('draw'),
			"recordsTotal" => $this->model->count_performance_all($selectedRange),
			"recordsFiltered" => $this->model->count_performance_filtered($selectedRange),
			"data" => $data,
		]);
	}
}
