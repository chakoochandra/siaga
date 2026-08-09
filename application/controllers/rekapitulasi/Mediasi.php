<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Mediasi extends Core_Controller
{
	public function __construct()
	{
		parent::__construct();

		$this->load->model('rekapitulasi/Mediasi_Model', 'mediasi');
		$this->model = $this->mediasi;

		$this->indexTitle = 'Rekapitulasi Mediasi (LIPA 12)';
		$this->indexSubtitle = 'Modul ini menampilkan statistik rekapitulasi mediasi perkara berdasarkan bulan.';
		$this->indexIcon = 'fa-solid fa-handshake';
		$this->indexView = 'rekapitulasi/mediasi/index';
		$this->listView = 'rekapitulasi/mediasi/list';
		$this->indexUrl = base_url('rekapitulasi/mediasi');
		$this->redirectUrl = $this->indexUrl;
		$this->module_id = 'rekapitulasi_mediasi';
	}

	function perkara_list()
	{
		$year = $this->uri->segment(5);
		$month = $this->uri->segment(6);
		$type = $this->uri->segment(4);

		$typeLabels = [
			'sisa_mediasi_lalu' => 'Sisa Mediasi Lalu',
			'sisa_lalu' => 'Sisa Bulan Lalu',
			'diterima_bulan_ini' => 'Diterima',
			'perkara_mediasi' => 'Perkara Mediasi',
			'berhasil_akta' => 'Berhasil Akta',
			'berhasil_sebagian' => 'Berhasil Sebagian',
			'berhasil_cabut' => 'Berhasil Cabut',
			'tidak_berhasil' => 'Tidak Berhasil',
			'gagal' => 'Gagal',
			'perkara_proses_mediasi' => 'Perkara Proses Mediasi',
			'putus_bulan_ini' => 'Putus Bulan Ini',
			'tidak_bisa_dimediasi' => 'Tidak Bisa Dimediasi',
			'sisa_perkara' => 'Sisa Perkara',
		];

		$title = isset($typeLabels[$type]) ? $typeLabels[$type] : 'Daftar Perkara Mediasi';

		$title .= $month ? " " . format_date(date('Y') . "-$month-01", 'MMMM') : '';
		$title .= $year ? " Tahun {$year}" : '';

		$this->vars['main_body'] = 'layout_content';
		$this->vars['view'] = 'rekapitulasi/mediasi/list';
		$this->vars['title'] = $title;

		$this->load->vars($this->vars);

		if ($this->input->is_ajax_request()) {
			return $this->viewAjax('rekapitulasi/mediasi/list', ['size' => 'modal-xl', 'showTitle' => true]);
		}

		$this->load->view('layout');
	}
}
