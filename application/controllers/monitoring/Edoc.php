<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Edoc extends Core_Controller
{
	public function __construct()
	{
		parent::__construct();

		$this->load->helper('app');
		$this->load->library('encrypt');

		if (!is_local_ip()) {
			show_error('Halaman ini hanya bisa diakses menggunakan IP lokal pada jaringan kantor', $status_code = 404, $heading = 'Halaman ini hanya bisa diakses pada jaringan kantor');
			exit;
		}

		$this->load->model('monitoring/Edoc_Model', 'edoc');
		$this->model = $this->edoc;

		$this->indexTitle = 'Monitoring e-Doc';
		$this->indexSubtitle = 'Modul ini menampilkan monitoring e-doc perkara.';
		$this->indexIcon = 'fa-solid fa-file-signature';
		$this->indexView = 'monitoring/edoc/index';
		$this->module_id = 'monitoring_edoc';
	}

	public function get_list()
	{
		$data = $this->model->get_list();

		foreach ($data as $row) {
			$sippUrl = get_sipp_url();
			$row->sipp_url = $sippUrl ? $sippUrl . '/perkara_detil_agama/' . base64_encode($this->encrypt->encode($row->row_id)) : '';
		}

		return $this->set_content_datatable($data);
	}

	public function get_summary()
	{
		$summary = $this->model->get_summary();

		echo json_encode($summary);
	}
}
