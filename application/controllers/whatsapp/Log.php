<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Log extends Core_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('whatsapp/Log_Model', 'whatsapp');
		$this->model = $this->whatsapp;

		$this->indexTitle = 'Log Notifikasi';
		$this->indexSubtitle = 'Modul ini menampilkan log pengiriman notifikasi WhatsApp.';
		$this->indexIcon = 'fa-brands fa-whatsapp';
		$this->indexView = 'whatsapp/log/index';
		$this->indexUrl = base_url('whatsapp');
		$this->module_id = 'whatsapp_log';
	}

	protected function prepare_index($options = [])
	{
		$this->vars['distinct_types'] = $this->model->get_distinct_types();
		parent::prepare_index($this->vars);
	}

	function view($id)
	{
		$this->viewView = 'whatsapp/log/view';
		$this->viewData = $this->model->findOne($id);

		// $this->vars['title'] = $this->viewData->name;

		parent::view($id);
	}
}
