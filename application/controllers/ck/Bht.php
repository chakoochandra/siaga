<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Bht extends Core_Controller
{
	public function __construct()
	{
		parent::__construct();

		$this->load->helper('auth');

		$this->load->model('ck/Bht_Model', 'disposisi');
		$this->model = $this->disposisi;

		$this->indexTitle = 'Monitoring Tanggal BHT';
		$this->indexSubtitle = 'Modul ini menampilkan progress rencana BHT.';
		$this->indexIcon = 'fa-solid fa-clipboard-check';
		$this->indexView = 'ck/bht/index';
		$this->module_id = 'monitoring_rencana_bht';

		$this->vars['canEdit'] = $this->ion_auth->logged_in();
	}
}
