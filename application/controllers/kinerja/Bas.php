<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Bas extends Core_Controller
{
	public function __construct()
	{
		parent::__construct();

		$this->load->helper('app');

		if (!is_local_ip()) {
			show_error('Halaman ini hanya bisa diakses menggunakan IP lokal pada jaringan kantor', $status_code = 404, $heading = 'Halaman ini hanya bisa diakses pada jaringan kantor');
			exit;
		}

		$this->load->model('kinerja/Bas_Model', 'bas');
		$this->model = $this->bas;

		$this->indexTitle = 'Kinerja BAS';
		$this->indexSubtitle = 'Menampilkan statistik dan rekapitulasi kinerja Berita Acara Sidang (BAS) panitera pengganti secara periodik.';
		$this->indexIcon = 'fa-solid fa-file-signature';
		$this->indexView = 'kinerja/bas/index';
		$this->mainBody = $this->input->is_ajax_request() ? 'kinerja/bas/index' : 'layout_content';
		$this->module_id = 'kinerja_bas';
	}

	function performance()
	{
		redirect('kinerja/bas');
	}

	function bas_list()
	{
		$this->load->model('settings/Ref_Model', 'ref');

		$perkaraId = $this->input->get('perkara_id') ?: null;

		if ($perkaraId) {
			$this->bas->perkara_id = $perkaraId;
			$this->vars['main_body'] = 'layout_content';
			$this->vars['view'] = 'kinerja/bas/list';
			$this->vars['title'] = 'Daftar Sidang';
			$this->vars['all_hakim_aktif'] = [];
			$this->vars['all_pp_aktif'] = [];

			$this->load->vars($this->vars);

			if ($this->input->is_ajax_request()) {
				return $this->viewAjax($this->vars['view'], ['size' => 'modal-xl', 'showTitle' => true]);
			}

			$this->load->view('layout');
			return;
		}

		switch ($this->uri->segment(4)) {
			case 'pending':
				$title = 'Belum Unggah';
				break;
			case 'uploaded':
				$title = 'Unggah BAS';
				break;
			default:
				$title = 'Sidang';
				break;
		}

		if ($this->uri->segment(5)) {
			$pp = $this->ref->findPpById($this->uri->segment(5));
		}

		switch ($this->uri->segment(6)) {
			case 3:
			case 4:
				$title = "Daftar $title Triwulan Ini";
				break;
			case 6:
				$title = "Daftar $title Hari Ini";
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
				$title = "Daftar $title Bulan " . format_date($this->uri->segment(6), "MMMM yyyy");
				break;
		}

		$this->vars['main_body'] = 'layout_content';
		$this->vars['view'] = 'kinerja/bas/list';
		$this->vars['title'] = $title . (isset($pp) ? " | {$pp->nama_gelar}" : '');
		$this->vars['all_hakim_aktif'] = $this->ref->findHakim();
		$this->vars['all_pp_aktif'] = $this->ref->findPp();

		$this->load->vars($this->vars);

		if ($this->input->is_ajax_request()) {
			return $this->viewAjax($this->vars['view'], ['size' => 'modal-xl', 'showTitle' => true]);
		}

		$this->load->view('layout');
	}

	function get_list($where = [])
	{
		$perkaraId = $this->input->get('perkara_id') ?: null;
		if ($perkaraId) {
			$this->model->perkara_id = $perkaraId;
		}

		return parent::get_list($where);
	}

	function get_performance()
	{
		// Early release PHP session to avoid CI database session GET_LOCK contention during long queries
		if (function_exists('session_write_close')) {
			session_write_close();
		}

		// DEBUG: Log AJAX endpoint call and timing
		$selectedRange = $this->input->post('selectedRange');
		$t0 = microtime(true);
		error_log('DEBUG: Bas::get_performance() - AJAX called, selectedRange=' . $selectedRange);

		// Pass selectedRange to model for filtering
		$data = $this->model->get_performance($selectedRange);

		$t1 = microtime(true);
		error_log(sprintf('DEBUG: Bas::get_performance() - rows=%d, query_time=%.3fs', count($data), ($t1 - $t0)));

		return $this->set_content_type([
			"draw" => $this->input->post('draw'),
			// recordsTotal should be the total records BEFORE filtering
			"recordsTotal" => $this->model->count_performance_all($selectedRange),
			// recordsFiltered should be the total records AFTER filtering
			"recordsFiltered" => $this->model->count_performance_filtered($selectedRange),
			"data" => $data,
		]);
	}
}
