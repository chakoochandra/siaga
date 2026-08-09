<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Minutasi extends Core_Controller
{
	private $_canEdit;

	protected $public_methods = [
		'putus_list',
	];

	public function __construct()
	{
		parent::__construct();

		$this->load->helper('auth');

		$this->_canEdit = is_panmud() || is_operator();
		$this->load->model('kinerja/Minutasi_Model', 'minutasi');
		$this->model = $this->minutasi;

		$this->indexTitle = 'Kinerja Setor Panmud';
		$this->indexSubtitle = 'Modul ini menampilkan statistik dan rekapitulasi Kinerja Setor Panmud (penyetoran berkas perkara yang telah diputus) panitera pengganti secara periodik.';
		$this->indexIcon = 'fa-solid fa-file-circle-check';
		$this->indexView = 'kinerja/minutasi/index';
		$this->mainBody = $this->input->is_ajax_request() ? 'kinerja/minutasi/index' : 'layout_content';
		$this->module_id = 'kinerja_minutasi';
	}

	function putus_list()
	{
		if ($this->uri->segment(5)) {
			$this->load->model('settings/Ref_Model', 'ref');
			$pp = $this->ref->findPpById($this->uri->segment(5));
		}

		switch ($this->uri->segment(4)) {
			case 'pending':
				$title = 'Pending';
				break;
			case 'setor':
				$title = 'Setor';
				break;
			default:
				$title = 'putus';
				break;
		}

		switch ($this->uri->segment(6)) {
			case 1:
				$title = "Daftar $title Bulan Ini";
				break;
			case 2:
				$title = "Daftar $title Bulan Kemarin";
				break;
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
		}

		$this->load->model('settings/Holiday_Model', 'holiday');
		$this->vars['main_body'] = 'layout_content';
		$this->vars['view'] = 'kinerja/minutasi/list';
		$this->vars['title'] = $title . (isset($pp) ? " | {$pp->nama_gelar}" : '');
		$this->vars['canEdit'] = $this->_canEdit;
		$this->vars['excludedDates'] = $this->holiday->get_holidays(null, null);

		$this->load->vars($this->vars);

		if ($this->input->is_ajax_request()) {
			return $this->viewAjax('kinerja/minutasi/list', ['size' => 'modal-xl', 'showTitle' => true]);
		}

		$this->load->view('layout');
	}
}
